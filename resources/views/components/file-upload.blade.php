@props([
    'name' => 'file',
    'id' => null,
    'label' => null,
    'required' => false,
    'accept' => '.xlsx,.xls,.csv',
    'hint' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $inputId }}" class="form-label font-semibold">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="file-dropzone {{ $errors->has($name) ? 'has-error' : '' }}">
        <input
            type="file"
            name="{{ $name }}"
            id="{{ $inputId }}"
            accept="{{ $accept }}"
            class="hidden file-dropzone-input"
            @if ($required) required @endif
        >

        {{-- State: Belum ada berkas --}}
        <div class="file-dropzone-prompt">
            <div class="file-dropzone-icon">
                <x-icon name="upload" size="24" />
            </div>
            <div>
                <span>Tarik & letakkan berkas ke sini, atau</span>
                <span class="file-dropzone-browse">Pilih Berkas</span>
            </div>
            @if ($hint)
                <div class="file-dropzone-hint">{{ $hint }}</div>
            @endif
        </div>

        {{-- State: Berkas terpilih --}}
        <div class="file-dropzone-selected hidden">
            <div class="file-dropzone-file-info">
                <div class="file-dropzone-file-icon">
                    <x-icon name="table" size="20" />
                </div>
                <div class="file-dropzone-file-meta">
                    <span class="file-dropzone-filename">-</span>
                    <span class="file-dropzone-filesize">-</span>
                </div>
            </div>
            <div class="file-dropzone-actions">
                <span class="badge badge-success">Siap Diimpor</span>
                <button type="button" class="file-dropzone-remove" title="Hapus berkas">
                    <x-icon name="close" size="14" />
                </button>
            </div>
        </div>
    </div>

    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>

@once
    <script>
        document.addEventListener('change', (e) => {
            const input = e.target.closest('.file-dropzone-input');
            if (!input) return;

            const dropzone = input.closest('.file-dropzone');
            const file = input.files[0];
            if (!file) return;

            dropzone.querySelector('.file-dropzone-filename').textContent = file.name;
            dropzone.querySelector('.file-dropzone-filesize').textContent = (file.size / 1048576).toFixed(2) + ' MB';
            dropzone.querySelector('.file-dropzone-prompt').classList.add('hidden');
            dropzone.querySelector('.file-dropzone-selected').classList.remove('hidden');
        });

        document.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.file-dropzone-remove');
            if (removeBtn) {
                e.stopPropagation();
                const dropzone = removeBtn.closest('.file-dropzone');
                dropzone.querySelector('.file-dropzone-input').value = '';
                dropzone.querySelector('.file-dropzone-selected').classList.add('hidden');
                dropzone.querySelector('.file-dropzone-prompt').classList.remove('hidden');
                return;
            }

            const dropzone = e.target.closest('.file-dropzone');
            if (dropzone && !e.target.closest('.file-dropzone-selected')) {
                dropzone.querySelector('.file-dropzone-input')?.click();
            }
        });

        document.addEventListener('dragover', (e) => {
            const dropzone = e.target.closest('.file-dropzone');
            if (dropzone) {
                e.preventDefault();
                dropzone.classList.add('is-dragover');
            }
        });

        document.addEventListener('dragleave', (e) => {
            const dropzone = e.target.closest('.file-dropzone');
            if (dropzone) dropzone.classList.remove('is-dragover');
        });

        document.addEventListener('drop', (e) => {
            const dropzone = e.target.closest('.file-dropzone');
            if (!dropzone) return;

            e.preventDefault();
            dropzone.classList.remove('is-dragover');
            const file = e.dataTransfer?.files?.[0];
            if (file) {
                const input = dropzone.querySelector('.file-dropzone-input');
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    </script>
@endonce
