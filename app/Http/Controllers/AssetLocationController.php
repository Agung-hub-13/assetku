<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AssetLocationController extends Controller
{
    public function index(Request $request): View
    {
        // Tanpa eager loading parent, department, atau children karena berdiri sendiri
        $query = AssetLocation::query();

        // Fitur Pencarian Data
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('building', 'like', "%{$search}%");
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $locations = $query->latest()->paginate(10)->withQueryString();

        return view('admin.asset_locations.index', compact('locations'));
    }

    public function create(): View
    {
        // Form berdiri sendiri tanpa parent_locations & departments
        return view('admin.asset_locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // 1. Generate Kode Lokasi Otomatis saat Simpan (Berdiri sendiri / flat)
                $code = $this->generateLocationCode();

                $request->merge([
                    'code' => $code,
                ]);

                Log::info('Proses simpan lokasi aset dimulai.', [
                    'generated_code' => $code,
                    'input_payload'  => $request->except(['_token'])
                ]);

                // 2. Validasi Input (disesuaikan dengan kolom database murni)
                $validated = $request->validate([
                    'name'     => 'required|string|max:255',
                    'code'     => ['required', 'string', 'max:50', Rule::unique('asset_locations', 'code')],
                    'building' => 'nullable|string|max:255',
                    'floor'    => 'nullable|string|max:255',
                    'room'     => 'nullable|string|max:255',
                    'address'  => 'nullable|string',
                    'status'   => 'required|in:active,inactive',
                ], [
                    'code.unique' => 'Kode lokasi "' . $code . '" sudah terdaftar. Silakan coba lagi.',
                ]);

                // 3. Simpan Data
                $location = AssetLocation::create($validated);

                Log::info('Berhasil menyimpan lokasi aset baru.', [
                    'id'   => $location->id,
                    'code' => $location->code
                ]);

                return redirect()->route('admin.asset_locations.index')
                    ->with('success', 'Lokasi aset berhasil ditambahkan dengan kode otomatis: ' . $location->code);
            });
        } catch (ValidationException $e) {
            Log::warning('Gagal simpan lokasi aset karena validasi tidak lolos.', [
                'errors' => $e->errors(),
                'input'  => $request->except(['_token'])
            ]);

            throw $e;
        } catch (Throwable $e) {
            Log::error('Error sistem saat menyimpan lokasi aset: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function show(AssetLocation $assetLocation): View
    {
        // Hanya memuat relasi assets jika ada, tanpa parent/children/department
        if (method_exists($assetLocation, 'assets')) {
            $assetLocation->load(['assets']);
        }

        return view('admin.asset_locations.show', compact('assetLocation'));
    }

    public function edit(AssetLocation $assetLocation): View
    {
        // Form edit bersih tanpa parentLocations & departments
        return view('admin.asset_locations.edit', compact('assetLocation'));
    }

    public function update(Request $request, AssetLocation $assetLocation): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request, $assetLocation) {
                Log::info('Proses update lokasi aset dimulai.', [
                    'id'            => $assetLocation->id,
                    'input_payload' => $request->except(['_token', '_method'])
                ]);

                // Jika kode belum ada, generate baru
                if (empty($assetLocation->code)) {
                    $code = $this->generateLocationCode();
                } else {
                    $code = $assetLocation->code;
                }

                $request->merge([
                    'code' => $code,
                ]);

                $validated = $request->validate([
                    'code'     => ['required', 'string', 'max:50', Rule::unique('asset_locations', 'code')->ignore($assetLocation->id)],
                    'name'     => 'required|string|max:255',
                    'building' => 'nullable|string|max:255',
                    'floor'    => 'nullable|string|max:255',
                    'room'     => 'nullable|string|max:255',
                    'address'  => 'nullable|string',
                    'status'   => 'required|in:active,inactive',
                ], [
                    'code.unique' => 'Kode lokasi ini sudah digunakan oleh lokasi lain.',
                ]);

                $assetLocation->update($validated);

                Log::info('Berhasil memperbarui lokasi aset.', [
                    'id'   => $assetLocation->id,
                    'code' => $assetLocation->code
                ]);

                return redirect()->route('admin.asset_locations.index')
                    ->with('success', 'Lokasi Aset berhasil diperbarui dengan kode: ' . $assetLocation->code);
            });
        } catch (ValidationException $e) {
            Log::warning('Gagal update lokasi aset karena validasi.', [
                'id'     => $assetLocation->id,
                'errors' => $e->errors()
            ]);

            throw $e;
        } catch (Throwable $e) {
            Log::error('Error sistem saat memperbarui lokasi aset: ' . $e->getMessage(), [
                'id'   => $assetLocation->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(AssetLocation $assetLocation): RedirectResponse
    {
        try {
            // Pengecekan sub-lokasi dihapus karena tabel berdiri sendiri (tidak ada parent-child)
            if (method_exists($assetLocation, 'assets') && $assetLocation->assets()->exists()) {
                return back()->with('error', 'Gagal menghapus! Lokasi ini masih terikat dengan aset terdaftar.');
            }

            $assetLocation->delete();

            Log::info('Berhasil menghapus lokasi aset.', ['id' => $assetLocation->id]);

            return redirect()->route('admin.asset_locations.index')
                ->with('success', 'Lokasi Aset berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('Error sistem saat menghapus lokasi aset: ' . $e->getMessage(), [
                'id'   => $assetLocation->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Generasi Kode Lokasi Otomatis (LOC-01, LOC-02, dst.)
     */
    private function generateLocationCode(): string
    {
        $prefix = 'LOC-';
        $hasSoftDeletes = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(AssetLocation::class));

        $query = AssetLocation::where('code', 'like', $prefix . '%');

        if ($hasSoftDeletes) {
            $query->withTrashed();
        }

        $latestMain = $query->orderBy('id', 'desc')->first();

        $nextNumber = 1;
        if ($latestMain) {
            $lastNum = (int) str_replace($prefix, '', $latestMain->code);
            $nextNumber = $lastNum + 1;
        }

        $code = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

        while ($this->codeExists($code, $hasSoftDeletes)) {
            $nextNumber++;
            $code = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    private function codeExists(string $code, bool $hasSoftDeletes): bool
    {
        $query = AssetLocation::where('code', $code);

        if ($hasSoftDeletes) {
            $query->withTrashed();
        }

        return $query->exists();
    }
}