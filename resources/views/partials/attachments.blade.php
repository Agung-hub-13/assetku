<div class="space-y-4 bg-white/40 dark:bg-slate-900/40 border border-slate-200/50 dark:border-slate-800/60 p-5 rounded-2xl">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="paperclip" class="w-4 h-4 text-blue-500"></i>
            Lampiran / Berkas Aset
        </h3>
        <!-- Upload Form trigger -->
        <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold transition-all">
            <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
            <span>Upload File</span>
            <input type="file" class="hidden" name="attachment" />
        </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @forelse($attachments ?? [] as $file)
        <div class="flex items-center justify-between p-3 bg-white/80 dark:bg-slate-800/60 rounded-xl border border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-2 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">{{ $file->filename }}</p>
                    <p class="text-[10px] text-slate-400">{{ $file->size_formatted ?? '1 MB' }}</p>
                </div>
            </div>
            <a href="#" class="p-1.5 text-slate-400 hover:text-blue-500 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
            </a>
        </div>
        @empty
        <div class="col-span-full py-4 text-center text-xs text-slate-400">
            Belum ada berkas terlampir.
        </div>
        @endforelse
    </div>
</div>