<?php

namespace App\Services;

use App\Models\AccurateToken;
use Illuminate\Support\Facades\Http;

class AccurateService
{
    public function getToken()
    {
        return AccurateToken::first();
    }

    /**
     * 🔧 FIX: kolom yang benar adalah `expired_at`, bukan `expires_at`.
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
            'expired_at'    => now()->addSeconds($data['expires_in'] ?? 3600), // 🔧 fix nama kolom
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

    /**
     * 🔧 FIX: sekarang pakai getValidAccessToken() — auto-refresh sebelum request.
     */
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

        $accessToken = $this->getValidAccessToken(); // 🔧 fix
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
        $accessToken = $this->getValidAccessToken(); // 🔧 fix
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

        $name = $asset['name'] ?? $asset['assetName'] ?? $asset['itemName'] ?? $asset['description'] ?? 'Unknown Asset';
        $number = $asset['no'] ?? $asset['number'] ?? $asset['itemNo'] ?? $asset['code'] ?? null;

        return [
            'id'                 => $asset['id'] ?? $id,
            'name'               => $name,
            'description'        => $asset['description'] ?? $asset['notes'] ?? $name,
            'number'             => $number,
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

    public function getAllFixedAssets()
    {
        $accessToken = $this->getValidAccessToken(); // 🔧 fix
        $token = $this->getToken();

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Session-ID'  => $token->session,
                'Accept'        => 'application/json',
            ])
            ->get($token->host . '/accurate/api/fixed-asset/list.do', [
                'page' => 1,
                'sp.pageSize' => 100,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Gagal request Accurate: ' . $response->body());
        }

        $listData = $response->json();
        $items = $listData['d'] ?? [];
        $results = [];

        foreach ($items as $item) {
            if (count($results) >= 100) {
                break;
            }

            $id = $item['id'] ?? null;
            $number = $item['number'] ?? null;

            if (!$id) {
                continue;
            }

            if ($number && !str_starts_with(strtoupper($number), 'FAA')) {
                continue;
            }

            try {
                // 🔧 pakai access token yang sudah pasti valid, bukan langsung $token->access_token
                $detailResponse = Http::timeout(30)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'X-Session-ID'  => $token->session,
                        'Accept'        => 'application/json',
                    ])
                    ->get($token->host . '/accurate/api/fixed-asset/detail.do', ['id' => $id]);

                if (!$detailResponse->successful()) {
                    continue;
                }

                $detail = $detailResponse->json();
                $asset = $detail['d'] ?? [];

                logger()->info('DETAIL ASSET SYNC', ['number' => $asset['number'] ?? 'No-Number']);

                $results[] = [
                    'id'                 => $asset['id'] ?? null,
                    'name'               => $asset['name'] ?? $asset['assetName'] ?? $asset['description'] ?? 'Unknown Asset',
                    'description'        => $asset['description'] ?? null,
                    'number'             => $asset['number'] ?? null,
                    'notes'              => $asset['notes'] ?? null,
                    'purchasePrice'      => $asset['assetCost'] ?? 0,
                    'assetCost'          => $asset['assetCost'] ?? 0,
                    'bookValue'          => $asset['bookValue'] ?? 0,
                    'depreciationAmount' => $asset['depreciationAmount'] ?? 0,
                    'estimatedLife'      => $asset['estimatedLife'] ?? null,
                    'quantity'           => $asset['quantity'] ?? 1,
                    'departmentName'     => $asset['department']['name'] ?? null,
                    'locationName'       => $asset['location']['name'] ?? null,
                    'departmentId'       => $asset['department']['id'] ?? null,
                    'categoryName'       => $asset['faType']['name'] ?? null,
                    'purchaseDate'       => $asset['transDate'] ?? null,
                    'raw'                => $asset,
                ];

                usleep(20000);
            } catch (\Exception $e) {
                logger()->error("Gagal memuat detail asset ID {$id}: " . $e->getMessage());
                continue;
            }
        }

        return $results;
    }
}