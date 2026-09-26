@props([
    'name' => 'image',
    'id' => null,
    'base64Id' => null,
    'fileId' => null,
    'label' => null,
    'value' => null,
    'placeholder' => 'Klik untuk unggah foto',
    'hint' => 'Format: PNG, JPG, WebP, GIF. Maks 1MB.',
    'removeName' => null,
    'removeId' => null,
    'maxSize' => 1048576,
    'maxSizeText' => '1MB',
    'accept' => 'image/png,image/jpeg,image/webp,image/gif',
    'icon' => 'upload-image',
    'previewShape' => 'circle',
    'groupClass' => '',
    'required' => false,
    'asFile' => false,
])

@php
    $errors = $errors ?? view()->shared('errors', new \Illuminate\Support\ViewErrorBag);
    $inputId = $id ?? $name;
    $base64InputId = $base64Id ?? ($inputId . '_base64');
    $fileInputId = $fileId ?? ($inputId . '_file');
    $removeInputId = $removeId ?? ($removeName ? ($inputId . '_remove') : null);
    $currentValue = old($name, $value);
    $hasInitialImage = !empty($currentValue);
@endphp

<div
    class="image-upload-component form-group {{ $groupClass }}"
    id="imageUpload_{{ $inputId }}"
    data-image-upload-id="{{ $inputId }}"
    data-max-size="{{ $maxSize }}"
    data-max-size-text="{{ $maxSizeText }}"
    data-has-remove="{{ $removeName ? '1' : '0' }}"
>
    @if ($label)
        <label for="{{ $fileInputId }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="dropzone-upload" id="{{ $inputId }}_area">
        @if (! $asFile)
            <input
                type="hidden"
                name="{{ $name }}"
                id="{{ $base64InputId }}"
                value="{{ $currentValue }}"
                class="image-upload-base64"
            >
        @endif

        @if ($removeName)
            <input
                type="hidden"
                name="{{ $removeName }}"
                id="{{ $removeInputId }}"
                value="0"
                class="image-upload-remove"
            >
        @endif

        <input
            type="file"
            @if ($asFile) name="{{ $name }}" @endif
            id="{{ $fileInputId }}"
            accept="{{ $accept }}"
            class="hidden image-upload-file"
            @if ($required && ! $hasInitialImage) required @endif
        >

        <div class="dropzone-upload-content {{ $hasInitialImage ? 'hidden' : '' }}" id="{{ $inputId }}_placeholder">
            <x-icon :name="$icon" size="32" />
            <span class="image-upload-prompt">{{ $placeholder }}</span>
            @if ($hint)
                <span class="form-hint">{{ $hint }}</span>
            @endif
        </div>

        <div class="dropzone-upload-preview {{ $hasInitialImage ? '' : 'hidden' }}" id="{{ $inputId }}_preview">
            <img
                id="{{ $inputId }}_preview_img"
                src="{{ $currentValue ?? '' }}"
                alt="Pratinjau Foto"
                class="{{ $previewShape === 'rounded' ? 'rounded-lg!' : 'rounded-full' }}"
            >
            <button
                type="button"
                class="dropzone-remove-btn"
                id="{{ $inputId }}_remove_btn"
                title="Hapus Foto"
            >
                <x-icon name="close" size="16" />
            </button>
        </div>
    </div>

    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>

@once
    <script>
        class ImageUpload {
            static instances = new Map();

            static getInstance(id) {
                return this.instances.get(id) || null;
            }

            static init(root = document) {
                root.querySelectorAll('[data-image-upload-id]').forEach(el => {
                    const id = el.dataset.imageUploadId;
                    if (!this.instances.has(id)) {
                        this.instances.set(id, new ImageUpload(el));
                    }
                });
            }

            constructor(container) {
                this.container = container;
                this.id = container.dataset.imageUploadId;
                this.maxSize = parseInt(container.dataset.maxSize || '1048576', 10);
                this.maxSizeText = container.dataset.maxSizeText || '1MB';

                this.base64Input = container.querySelector('.image-upload-base64');
                this.removeInput = container.querySelector('.image-upload-remove');
                this.fileInput = container.querySelector('.image-upload-file');
                this.placeholder = container.querySelector('.dropzone-upload-content');
                this.preview = container.querySelector('.dropzone-upload-preview');
                this.previewImg = container.querySelector('.dropzone-upload-preview img');
                this.removeBtn = container.querySelector('.dropzone-remove-btn');
                this.dropzoneArea = container.querySelector('.dropzone-upload');

                this.bindEvents();
            }

            bindEvents() {
                if (!this.dropzoneArea || !this.fileInput) return;

                this.dropzoneArea.addEventListener('click', (e) => {
                    if (e.target.closest('.dropzone-remove-btn')) return;
                    this.fileInput.click();
                });

                this.fileInput.addEventListener('change', () => {
                    const file = this.fileInput.files[0];
                    if (!file) return;

                    if (file.size > this.maxSize) {
                        alert(`Ukuran berkas melebihi ${this.maxSizeText}. Silakan pilih berkas yang lebih kecil.`);
                        this.fileInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const base64Data = e.target.result;
                        if (this.base64Input) this.base64Input.value = base64Data;
                        if (this.removeInput) this.removeInput.value = '0';
                        if (this.previewImg) this.previewImg.src = base64Data;
                        if (this.placeholder) this.placeholder.classList.add('hidden');
                        if (this.preview) this.preview.classList.remove('hidden');

                        this.container.dispatchEvent(new CustomEvent('image-upload:change', {
                            bubbles: true,
                            detail: { id: this.id, base64: base64Data, file: file }
                        }));
                    };
                    reader.readAsDataURL(file);
                });

                if (this.removeBtn) {
                    this.removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.clear();
                    });
                }
            }

            setPreview(url) {
                if (this.base64Input) this.base64Input.value = '';
                if (this.fileInput) this.fileInput.value = '';
                if (this.removeInput) this.removeInput.value = '0';

                if (url) {
                    if (this.previewImg) this.previewImg.src = url;
                    if (this.placeholder) this.placeholder.classList.add('hidden');
                    if (this.preview) this.preview.classList.remove('hidden');
                } else {
                    this.clear();
                }
            }

            clear() {
                if (this.base64Input) this.base64Input.value = '';
                if (this.fileInput) this.fileInput.value = '';
                if (this.removeInput) this.removeInput.value = '1';
                if (this.previewImg) this.previewImg.src = '';
                if (this.preview) this.preview.classList.add('hidden');
                if (this.placeholder) this.placeholder.classList.remove('hidden');

                this.container.dispatchEvent(new CustomEvent('image-upload:clear', {
                    bubbles: true,
                    detail: { id: this.id }
                }));
            }

            getValue() {
                return this.base64Input ? this.base64Input.value : '';
            }
        }

        window.ImageUpload = ImageUpload;
        window.InforaImageUpload = ImageUpload;

        document.addEventListener('DOMContentLoaded', () => {
            ImageUpload.init();
        });
    </script>
@endonce
