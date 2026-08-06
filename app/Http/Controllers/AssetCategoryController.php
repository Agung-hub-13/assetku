<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetCategoryController extends Controller
{
    /**
     * Tampilkan daftar kategori aset dengan filter pencarian dan eager loading.
     */
    public function index(Request $request)
    {
        $query = AssetCategory::with(['parent', 'children']);

        // Fitur pencarian berdasarkan nama atau code_prefix
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code_prefix', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan Tipe (Parent saja / Child saja)
        if ($request->filled('type')) {
            if ($request->type === 'parent') {
                $query->whereNull('parent_id');
            } elseif ($request->type === 'child') {
                $query->whereNotNull('parent_id');
            }
        }

        $categories = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        // Mengambil data kategori utama untuk dropdown/modal form
        $parentCategories = AssetCategory::onlyParents()->orderBy('name', 'asc')->get();

        return view('admin.asset_categories.index', compact('categories', 'parentCategories'));
    }

    /**
     * Simpan data kategori/sub-kategori baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id'     => ['nullable', 'exists:asset_categories,id'],
            'name'          => ['required', 'string', 'max:255', 'unique:asset_categories,name'],
            'code_prefix'   => ['nullable', 'string', 'max:10', 'unique:asset_categories,code_prefix'],
        ], [
            'name.required'        => 'Nama kategori wajib diisi.',
            'name.unique'          => 'Nama kategori sudah digunakan.',
            'parent_id.exists'     => 'Induk kategori tidak valid.',
            'code_prefix.unique'   => 'Prefix kode sudah digunakan.',
            'code_prefix.max'      => 'Prefix kode maksimal 10 karakter.',
        ]);

        // Format prefix kode menjadi huruf kapital jika diisi
        if (!empty($validated['code_prefix'])) {
            $validated['code_prefix'] = strtoupper($validated['code_prefix']);
        }

        AssetCategory::create($validated);

        return redirect()->back()->with('success', 'Kategori aset berhasil ditambahkan!');
    }

    /**
     * Perbarui data kategori/sub-kategori aset.
     */
    public function update(Request $request, $id)
    {
        $category = AssetCategory::findOrFail($id);

        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'exists:asset_categories,id',
                // Mencegah kategori menjadikan dirinya sendiri sebagai parent
                Rule::notIn([$category->id]),
            ],
            'name'          => ['required', 'string', 'max:255', 'unique:asset_categories,name,' . $category->id],
            'code_prefix'   => ['nullable', 'string', 'max:10', 'unique:asset_categories,code_prefix,' . $category->id],
        ], [
            'name.required'        => 'Nama kategori wajib diisi.',
            'name.unique'          => 'Nama kategori sudah digunakan.',
            'parent_id.not_in'     => 'Kategori tidak dapat menjadi induk dari dirinya sendiri.',
            'parent_id.exists'     => 'Induk kategori tidak valid.',
            'code_prefix.unique'   => 'Prefix kode sudah digunakan.',
            'code_prefix.max'      => 'Prefix kode maksimal 10 karakter.',
        ]);

        // Format prefix kode menjadi huruf kapital jika diisi
        if (!empty($validated['code_prefix'])) {
            $validated['code_prefix'] = strtoupper($validated['code_prefix']);
        }

        $category->update($validated);

        return redirect()->back()->with('success', 'Data kategori berhasil diperbarui!');
    }

    /**
     * Hapus data kategori/sub-kategori aset.
     */
    public function destroy($id)
    {
        $category = AssetCategory::findOrFail($id);

        // 1. Proteksi: Cegah hapus jika kategori ini masih punya sub-kategori
        if ($category->children()->exists()) {
            return redirect()->back()->with('error', 'Gagal menghapus! Kategori ini masih memiliki sub-kategori di bawahnya.');
        }

        // 2. Proteksi: Cegah hapus jika masih terikat dengan data aset
        if (method_exists($category, 'assets') && $category->assets()->exists()) {
            return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data aset!');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Kategori aset berhasil dihapus!');
    }
}