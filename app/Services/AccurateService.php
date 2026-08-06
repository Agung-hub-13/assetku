<?php

namespace App\Services;

use App\Models\AccurateToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AccurateService
{
    public function getToken()
    {
        return AccurateToken::first();
    }

    /**
     * Selalu panggil ini untuk dapatkan access token yang valid —
     * otomatis refresh kalau sudah/hampir expired.
     */
    public function getValidAccessToken(): string
    {
        $token = $this->getToken();

        if (!$token) {
            throw new \Exception('Token Accurate belum terkonfigurasi');
        }

        if (!$token->expired_at || now()->addMinutes(5)->gte($token->expired_at)) {
            return $this->refreshToken($token);
        }

        return $token->access_token;
    }

    public function refreshToken(AccurateToken $token): string
    {
        if (!$token->refresh_token) {
            throw new \Exception('Refresh token tidak tersedia, perlu re-connect manual ke Accurate');
        }

        $response = Http::asForm()
            ->withBasicAuth(config('services.accurate.client_id'), config('services.accurate.client_secret'))
            ->post('https://account.accurate.id/oauth/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $token->refresh_token,
            ]);

        if (!$response->successful()) {
            logger()->error('ACCURATE REFRESH TOKEN FAILED', ['body' => $response->body()]);
            throw new \Exception('Gagal Refresh Token Accurate: ' . $response->body());
        }

        $data = $response->json();

        $token->update([
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token,
            'expired_at'    => now()->addSeconds($data['expires_in'] ?? 3600),
        ]);

        logger()->info('ACCURATE TOKEN REFRESHED', ['expired_at' => $token->expired_at]);

        return $token->access_token;
    }

    public function getDbList($accessToken)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ])->get('https://account.accurate.id/api/db-list.do');

        $data = $response->json();

        if (!isset($data['s']) || !$data['s']) {
            throw new \Exception('Gagal ambil DB list');
        }

        return $data['d'];
    }

    public function openDb($accessToken, $dbId)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ])->asForm()->post('https://account.accurate.id/api/open-db.do', [
            'id' => (string) $dbId
        ]);

        $data = $response->json();

        if (!isset($data['s']) || !$data['s']) {
            throw new \Exception('Gagal open DB: ' . json_encode($data));
        }

        return [
            'session' => $data['session'] ?? $data['d']['session'] ?? null,
            'host'    => $data['host'] ?? $data['d']['host'] ?? 'https://public.accurate.id',
        ];
    }

    public function get(string $endpoint, array $queryParams = [])
    {
        return $this->makeRequest('GET', $endpoint, $queryParams);
    }

    public function post(string $endpoint, array $queryParams = [])
    {
        return $this->makeRequest('POST', $endpoint, $queryParams);
    }

    public function makeRequest(string $method, string $endpoint, array $queryParams = [])
    {
        $accessToken = $this->getValidAccessToken();
        $token = $this->getToken();

        if (!$token || !$token->session || !$token->host) {
            throw new \Exception('Session Accurate belum lengkap atau tidak valid');
        }

        $url = rtrim($token->host, '/') . '/accurate/api' . '/' . ltrim($endpoint, '/');

        $request = Http::timeout(30)->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID'  => $token->session,
            'Accept'        => 'application/json',
        ]);

        $response = strtolower($method) === 'post'
            ? $request->post($url, $queryParams)
            : $request->get($url, $queryParams);

        if (!$response->successful()) {
            logger()->warning('ACCURATE REQUEST FAILED', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    public function getItems()
    {
        $token = $this->getToken();

        if (!$token || !$token->db_id) {
            throw new \Exception('DB ID belum ada');
        }

        $accessToken = $this->getValidAccessToken();
        $db = $this->openDb($accessToken, $token->db_id);

        if (empty($db['session']) || empty($db['host'])) {
            throw new \Exception('Session/Host tidak valid dari Accurate');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Session-ID'  => $db['session'],
        ])->get($db['host'] . '/accurate/api/item/list.do');

        return [
            'status' => $response->status(),
            'body'   => $response->json(),
        ];
    }

    public function getAllWarehouses()
    {
        $accessToken = $this->getValidAccessToken();
        $token = $this->getToken();

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Session-ID'  => $token->session,
                'Accept'        => 'application/json',
            ])
            ->get($token->host . '/accurate/api/warehouse/list.do', [
                'page' => 1,
                'sp.pageSize' => 100,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Gagal request data Warehouse ke Accurate: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['s']) || !$data['s']) {
            throw new \Exception('Respon Accurate menyatakan gagal: ' . json_encode($data));
        }

        return $data['d'] ?? [];
    }

    public function getSingleFixedAssetDetail(string $id): ?array
    {
        $response = $this->makeRequest('GET', '/fixed-asset/detail.do', ['id' => $id]);

        if (!$response || empty($response['d'])) {
            $response = $this->makeRequest('GET', '/item/detail.do', ['id' => $id]);
        }

        if (!$response || empty($response['d'])) {
            return null;
        }

        $asset = $response['d'][0] ?? $response['d'] ?? [];

        return $this->mapAssetDetail($asset, $id);
    }

    /**
     * Normalisasi 1 record detail asset dari response Accurate ke struktur internal.
     */
    protected function mapAssetDetail(array $asset, $fallbackId = null): array
    {
        $name = $asset['name'] ?? $asset['assetName'] ?? $asset['itemName'] ?? $asset['description'] ?? 'Unknown Asset';

        return [
            'id'                 => $asset['id'] ?? $fallbackId,
            'name'               => $name,
            'description'        => $asset['description'] ?? $asset['notes'] ?? $name,
            'number'             => $asset['no'] ?? $asset['number'] ?? $asset['itemNo'] ?? $asset['code'] ?? null,
            'notes'              => $asset['notes'] ?? null,
            'purchasePrice'      => $asset['assetCost'] ?? $asset['unitPrice'] ?? 0,
            'assetCost'          => $asset['assetCost'] ?? $asset['unitPrice'] ?? 0,
            'bookValue'          => $asset['bookValue'] ?? 0,
            'depreciationAmount' => $asset['depreciationAmount'] ?? 0,
            'estimatedLife'      => $asset['estimatedLife'] ?? null,
            'quantity'           => $asset['quantity'] ?? 1,
            'departmentName'     => $asset['department']['name'] ?? null,
            'locationName'       => $asset['location']['name'] ?? $asset['warehouse']['name'] ?? null,
            'departmentId'       => $asset['department']['id'] ?? null,
            'categoryName'       => $asset['faType']['name'] ?? $asset['itemCategory']['name'] ?? null,
            'purchaseDate'       => $asset['transDate'] ?? null,
            'raw'                => $asset,
        ];
    }

    /**
     * FULL SYNC — STREAMING & RESUMABLE.
     *
     * Menarik SELURUH fixed asset dari Accurate, halaman demi halaman, dan
     * langsung memanggil $onItem(...) untuk tiap item begitu detail-nya berhasil
     * diambil — sehingga item yang sudah diproses TIDAK hilang meskipun proses
     * berhenti di tengah jalan (misalnya karena API Accurate error atau command
     * di-kill). Progres halaman disimpan ke cache sehingga run berikutnya otomatis
     * melanjutkan dari halaman terakhir yang belum selesai, bukan mengulang dari awal.
     *
     * @param  callable  $onItem  function(array $item): void — dipanggil per item
     * @param  bool  $fresh  true = paksa mulai dari page 1, abaikan checkpoint lama
     * @return array{pages_fetched:int, items_fetched:int, items_failed:int}
     */
    public function syncAllFixedAssets(callable $onItem, bool $fresh = false): array
    {
        $checkpointKey = 'accurate_sync_last_page';

        if ($fresh) {
            Cache::forget($checkpointKey);
        }

        $accessToken = $this->getValidAccessToken();
        $token = $this->getToken();

        $page = (int) Cache::get($checkpointKey, 1);
        $pageSize = 100;
        $maxPages = 1000; // safety net — cukup untuk ±100.000 aset
        $maxRetriesPerPage = 3;
        $maxRetriesPerDetail = 3;

        $itemsFetched = 0;
        $itemsFailed = 0;
        $pagesFetched = 0;

        while ($page <= $maxPages) {
            $listData = $this->fetchListPageWithRetry($accessToken, $token, $page, $pageSize, $maxRetriesPerPage);

            if ($listData === null) {
                // Halaman ini gagal total setelah retry — STOP di sini, jangan lompat/skip diam-diam.
                Cache::forever($checkpointKey, $page);
                logger()->error("ACCURATE SYNC BERHENTI di page {$page} setelah {$maxRetriesPerPage}x percobaan gagal.");
                throw new \Exception("Gagal mengambil data page {$page} dari Accurate. Sync dihentikan — jalankan ulang command, akan otomatis lanjut dari page {$page}.");
            }

            $items = $listData['d'] ?? [];

            if (empty($items)) {
                break; // sudah tidak ada halaman lagi
            }

            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if (!$id) {
                    continue;
                }

                $detail = $this->fetchAssetDetailWithRetry($accessToken, $token, $id, $maxRetriesPerDetail);

                if (!$detail) {
                    $itemsFailed++;
                    logger()->error("SKIP PERMANEN: detail asset ID {$id} gagal diambil setelah {$maxRetriesPerDetail}x percobaan.");
                    continue;
                }

                try {
                    $onItem($detail);
                    $itemsFetched++;
                } catch (\Throwable $e) {
                    $itemsFailed++;
                    logger()->error("GAGAL memproses item ID {$id} ke DB: " . $e->getMessage());
                }

                usleep(20000); // jaga rate limit Accurate
            }

            $pagesFetched++;

            logger()->info('ACCURATE SYNC PROGRESS', [
                'page'           => $page,
                'items_di_page'  => count($items),
                'total_diproses' => $itemsFetched,
                'total_gagal'    => $itemsFailed,
            ]);

            // Checkpoint disimpan SETELAH halaman ini selesai diproses penuh
            Cache::forever($checkpointKey, $page + 1);

            if (count($items) < $pageSize) {
                break; // halaman terakhir
            }

            $page++;
        }

        // Selesai total tanpa error fatal → reset checkpoint, run berikutnya mulai dari page 1
        Cache::forget($checkpointKey);

        return [
            'pages_fetched' => $pagesFetched,
            'items_fetched' => $itemsFetched,
            'items_failed'  => $itemsFailed,
        ];
    }

    protected function fetchListPageWithRetry($accessToken, $token, int $page, int $pageSize, int $maxRetries): ?array
    {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'X-Session-ID'  => $token->session,
                        'Accept'        => 'application/json',
                    ])
                    ->get($token->host . '/accurate/api/fixed-asset/list.do', [
                        'page'        => $page,
                        'sp.pageSize' => $pageSize,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                logger()->warning("ACCURATE LIST GAGAL (page {$page}, attempt {$attempt})", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            } catch (\Exception $e) {
                logger()->warning("ACCURATE LIST EXCEPTION (page {$page}, attempt {$attempt}): " . $e->getMessage());
            }

            if ($attempt < $maxRetries) {
                sleep($attempt * 3); // backoff: 3s, 6s
            }
        }

        return null;
    }

    protected function fetchAssetDetailWithRetry($accessToken, $token, $id, int $maxRetries): ?array
    {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $detailResponse = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'X-Session-ID'  => $token->session,
                        'Accept'        => 'application/json',
                    ])
                    ->get($token->host . '/accurate/api/fixed-asset/detail.do', ['id' => $id]);

                if ($detailResponse->successful()) {
                    $asset = $detailResponse->json()['d'] ?? [];
                    return $this->mapAssetDetail($asset, $id);
                }

                logger()->warning("ACCURATE DETAIL GAGAL (id {$id}, attempt {$attempt})", [
                    'status' => $detailResponse->status(),
                ]);
            } catch (\Exception $e) {
                logger()->warning("ACCURATE DETAIL EXCEPTION (id {$id}, attempt {$attempt}): " . $e->getMessage());
            }

            if ($attempt < $maxRetries) {
                sleep($attempt * 2); // backoff: 2s, 4s
            }
        }

        return null;
    }
}