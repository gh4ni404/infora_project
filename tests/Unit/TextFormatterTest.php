<?php

use App\Support\TextFormatter;

test('text formatter upper converts text to uppercase cleanly', function () {
    expect(TextFormatter::upper(null))->toBeNull();
    expect(TextFormatter::upper(''))->toBe('');
    expect(TextFormatter::upper('   '))->toBe('');
    expect(TextFormatter::upper('pengaturan sistem'))->toBe('PENGATURAN SISTEM');
    expect(TextFormatter::upper('  navigasi utama  '))->toBe('NAVIGASI UTAMA');
    expect(TextFormatter::upper('akademik & kesiswaan smk'))->toBe('AKADEMIK & KESISWAAN SMK');
});

test('text formatter titleCase capitalizes each word cleanly', function () {
    expect(TextFormatter::titleCase(null))->toBeNull();
    expect(TextFormatter::titleCase(''))->toBe('');
    expect(TextFormatter::titleCase('   '))->toBe('');
    expect(TextFormatter::titleCase('dashboard'))->toBe('Dashboard');
    expect(TextFormatter::titleCase('tata kelola sistem'))->toBe('Tata Kelola Sistem');
    expect(TextFormatter::titleCase('sub-menu'))->toBe('Sub-Menu');
    expect(TextFormatter::titleCase('sub-menu manajemen'))->toBe('Sub-Menu Manajemen');
});

test('text formatter titleCase preserves educational and technical acronyms', function () {
    expect(TextFormatter::titleCase('jurnal kbm guru'))->toBe('Jurnal KBM Guru');
    expect(TextFormatter::titleCase('rekam jejak pkl smk'))->toBe('Rekam Jejak PKL SMK');
    expect(TextFormatter::titleCase('portal sim sma negeri'))->toBe('Portal SIM SMA Negeri');
    expect(TextFormatter::titleCase('manajemen gtk dan tu'))->toBe('Manajemen GTK Dan TU');
    expect(TextFormatter::titleCase('integrasi api dan crud'))->toBe('Integrasi API Dan CRUD');
    expect(TextFormatter::titleCase('akreditasi ban-sm'))->toBe('Akreditasi BAN-SM');
});
