<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        // Sesuaikan withCount hanya pada relasi yang benar-benar ada kolom penghubungnya
        $query = Department::withCount(['users', 'assets']);

        // Fitur Pencarian Data
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $departments = $query->latest()->paginate(10)->withQueryString();

        return view('admin.asset_departments.index', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            // 1. Buat Kode Departemen Otomatis Sepenuhnya (DPT-01, DPT-02, dst.)
            $code = $this->generateDepartmentCode();
            $request->merge(['code' => $code]);

            Log::info('Proses simpan departemen dimulai.', [
                'generated_code' => $code,
                'input_payload'  => $request->except(['_token'])
            ]);

            // 2. Validasi Input
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'code'        => ['required', 'string', 'max:50', Rule::unique('departments', 'code')],
            ], [
                'code.unique' => 'Kode departemen "' . $code . '" sudah terdaftar. Silakan coba lagi.',
            ]);

            // 3. Simpan Data
            $department = Department::create($validated);

            Log::info('Berhasil menyimpan departemen baru.', [
                'id'   => $department->id,
                'code' => $department->code
            ]);

            return redirect()->back()->with('success', 'Departemen berhasil ditambahkan dengan kode: ' . $department->code);
        } catch (ValidationException $e) {
            Log::warning('Gagal simpan departemen karena validasi.', [
                'errors' => $e->errors(),
                'input'  => $request->except(['_token'])
            ]);

            throw $e;
        } catch (Throwable $e) {
            Log::error('Error sistem saat menyimpan departemen: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Department $assetDepartment): RedirectResponse
    {
        try {
            // Jika kode tidak diisi saat update, pertahankan kode yang sudah ada
            if ($request->filled('code')) {
                $request->merge(['code' => strtoupper(trim($request->code))]);
            } else {
                $request->merge(['code' => $assetDepartment->code]);
            }

            Log::info('Proses update departemen dimulai.', [
                'id' => $assetDepartment->id,
                'input_payload' => $request->except(['_token', '_method'])
            ]);

            // Validasi Input
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'code'        => ['required', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($assetDepartment->id)],
            ], [
                'code.unique' => 'Kode departemen sudah digunakan oleh departemen lain.',
            ]);

            $assetDepartment->update($validated);

            Log::info('Berhasil memperbarui departemen.', ['id' => $assetDepartment->id]);

            return redirect()->back()->with('success', 'Departemen berhasil diperbarui.');
        } catch (ValidationException $e) {
            Log::warning('Gagal update departemen karena validasi.', [
                'id'     => $assetDepartment->id,
                'errors' => $e->errors()
            ]);

            throw $e;
        } catch (Throwable $e) {
            Log::error('Error sistem saat memperbarui departemen: ' . $e->getMessage(), [
                'id'   => $assetDepartment->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Department $assetDepartment): RedirectResponse
    {
        try {
            // Proteksi jika departemen masih terikat dengan User atau Aset
            if ($assetDepartment->users()->exists() || $assetDepartment->assets()->exists()) {
                return redirect()->back()->with('error', 'Departemen tidak dapat dihapus karena masih terikat dengan User atau Aset.');
            }

            $assetDepartment->delete();

            Log::info('Berhasil menghapus departemen.', ['id' => $assetDepartment->id]);

            return redirect()->back()->with('success', 'Departemen berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('Error sistem saat menghapus departemen: ' . $e->getMessage(), [
                'id'   => $assetDepartment->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Generasi Kode Departemen Otomatis (DPT-01, DPT-02, dst.)
     */
    private function generateDepartmentCode(): string
    {
        $prefix = 'DPT-';

        // Memilih angka tertinggi/jumlah record untuk urutan berikutnya
        $count = Department::count();
        $nextNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        $code = $prefix . $nextNumber;

        // Mencegah duplikasi kode jika ada record yang melompati urutan
        while (Department::where('code', $code)->exists()) {
            $count++;
            $nextNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
            $code = $prefix . $nextNumber;
        }

        return $code;
    }
}
