<?php

namespace App\Http\Requests\Concerns;

trait ValidatesDevelopmentProgress
{
    /**
     * Normalisasi input progres pengembangan sebelum validasi.
     */
    protected function prepareDevelopmentProgress(int $fallbackProgress = 0): void
    {
        $progress = $this->filled('dev_progress')
            ? (int) $this->input('dev_progress')
            : $fallbackProgress;

        $this->merge([
            'dev_progress' => $progress,
            'dev_stage' => $this->filled('dev_stage') ? trim((string) $this->input('dev_stage')) : null,
            'dev_note' => $this->filled('dev_note') ? trim((string) $this->input('dev_note')) : null,
            'dev_version' => $this->filled('dev_version') ? trim((string) $this->input('dev_version')) : null,
        ]);
    }

    /**
     * Aturan validasi untuk kolom progres pengembangan navigasi.
     *
     * @return array<string, array<int, string>>
     */
    protected function developmentProgressRules(): array
    {
        return [
            'dev_progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'dev_stage' => ['nullable', 'string', 'max:120'],
            'dev_note' => ['nullable', 'string', 'max:255'],
            'dev_version' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * Nama atribut berbahasa Indonesia untuk kolom progres pengembangan.
     *
     * @return array<string, string>
     */
    protected function developmentProgressAttributes(): array
    {
        return [
            'dev_progress' => 'Progres Pengembangan',
            'dev_stage' => 'Tahap Pengembangan',
            'dev_note' => 'Catatan Progres',
            'dev_version' => 'Target Versi',
        ];
    }

    /**
     * Pesan kesalahan validasi untuk kolom progres pengembangan.
     *
     * @return array<string, string>
     */
    protected function developmentProgressMessages(): array
    {
        return [
            'dev_progress.integer' => 'Progres pengembangan harus berupa angka bulat.',
            'dev_progress.min' => 'Progres pengembangan minimal 0%.',
            'dev_progress.max' => 'Progres pengembangan maksimal 100%.',
            'dev_stage.max' => 'Tahap pengembangan maksimal 120 karakter.',
            'dev_note.max' => 'Catatan progres maksimal 255 karakter.',
            'dev_version.max' => 'Target versi maksimal 30 karakter.',
        ];
    }
}
