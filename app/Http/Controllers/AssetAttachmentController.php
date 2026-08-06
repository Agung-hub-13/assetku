<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetAttachmentController extends Controller
{
    /**
     * Tampilkan daftar lampiran untuk aset tertentu.
     */
    public function index(Asset $asset)
    {
        $attachments = $asset->attachments()->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $attachments
        ]);
    }

    /**
     * Simpan lampiran baru yang diunggah.
     */
    public function store(Request $request, Asset $asset)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'file_type' => 'required|in:photo,invoice,warranty,manual,other',
            'caption' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store("assets/{$asset->id}", 'public');

        $attachment = DB::transaction(function () use ($request, $asset, $file, $path) {
            $isPrimary = $request->boolean('is_primary');

            // Jika foto diset sebagai foto utama, reset foto utama lainnya pada aset yang sama
            if ($isPrimary && $request->file_type === 'photo') {
                AssetAttachment::where('asset_id', $asset->id)
                    ->where('file_type', 'photo')
                    ->update(['is_primary' => false]);
            }

            return AssetAttachment::create([
                'asset_id' => $asset->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $request->file_type,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'is_primary' => $isPrimary && $request->file_type === 'photo',
                'caption' => $request->caption,
                'uploaded_by' => auth()->id(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Lampiran berhasil diunggah.',
                'data' => $attachment
            ], 201);
        }

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    /**
     * Unduh file lampiran.
     */
    public function download(AssetAttachment $attachment): StreamedResponse
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File tidak ditemukan pada storage.');
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    /**
     * Perbarui metadata lampiran (caption, tipe file, foto utama).
     */
    public function update(Request $request, AssetAttachment $attachment)
    {
        $request->validate([
            'file_type' => 'required|in:photo,invoice,warranty,manual,other',
            'caption' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $attachment) {
            $isPrimary = $request->boolean('is_primary');

            // Jika status foto utama diubah menjadi true
            if ($isPrimary && $request->file_type === 'photo') {
                AssetAttachment::where('asset_id', $attachment->asset_id)
                    ->where('file_type', 'photo')
                    ->update(['is_primary' => false]);
            }

            $attachment->update([
                'file_type' => $request->file_type,
                'caption' => $request->caption,
                'is_primary' => $isPrimary && $request->file_type === 'photo',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Detail lampiran berhasil diperbarui.',
                'data' => $attachment->fresh()
            ]);
        }

        return back()->with('success', 'Detail lampiran berhasil diperbarui.');
    }

    /**
     * Set lampiran foto secara cepat sebagai foto utama.
     */
    public function setPrimary(AssetAttachment $attachment)
    {
        if ($attachment->file_type !== 'photo') {
            return back()->with('error', 'Hanya file jenis foto yang dapat dijadikan foto utama.');
        }

        DB::transaction(function () use ($attachment) {
            AssetAttachment::where('asset_id', $attachment->asset_id)
                ->where('file_type', 'photo')
                ->update(['is_primary' => false]);

            $attachment->update(['is_primary' => true]);
        });

        return back()->with('success', 'Foto utama berhasil diperbarui.');
    }

    /**
     * Hapus lampiran beserta file fisiknya.
     */
    public function destroy(Request $request, AssetAttachment $attachment)
    {
        // Hapus file fisik jika ada
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Lampiran berhasil dihapus.'
            ]);
        }

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}