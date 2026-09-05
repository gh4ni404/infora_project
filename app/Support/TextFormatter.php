<?php

namespace App\Support;

class TextFormatter
{
    /**
     * Common Indonesian educational, governmental, and technical acronyms that should remain UPPERCASE.
     *
     * @var list<string>
     */
    protected static array $acronyms = [
        'SMK', 'SMA', 'SMP', 'SD', 'SIM', 'PKL', 'KBM', 'GTK',
        'BAN-SM', 'RPP', 'IT', 'TU', 'UKS', 'OSIS', 'BK', 'BKK',
        'ID', 'API', 'CRUD', 'UI', 'UX', 'IPK', 'SK', 'KD', 'CP', 'TP', 'ATP', 'P5',
        'NIP', 'NISN', 'NIS', 'NUPTK',
    ];

    /**
     * Convert text to standard UPPERCASE.
     */
    public static function upper(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? '' : mb_strtoupper($trimmed, 'UTF-8');
    }

    /**
     * Convert text to Capitalize Each Word (Title Case) while preserving registered acronyms.
     */
    public static function titleCase(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        $acronymMap = [];
        foreach (self::$acronyms as $acronym) {
            $acronymMap[mb_strtolower($acronym, 'UTF-8')] = $acronym;
        }

        // Split text by whitespace, preserving whitespace delimiters
        $parts = preg_split('/(\s+)/u', $trimmed, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $trimmed;
        }

        $formatted = '';
        foreach ($parts as $part) {
            if (trim($part) === '') {
                $formatted .= $part;

                continue;
            }

            $lowerPart = mb_strtolower($part, 'UTF-8');
            if (isset($acronymMap[$lowerPart])) {
                $formatted .= $acronymMap[$lowerPart];

                continue;
            }

            // Handle hyphen-connected words such as "Sub-Menu"
            $subParts = explode('-', $part);
            $casedSubParts = array_map(function (string $sub) use ($acronymMap): string {
                if ($sub === '') {
                    return '';
                }

                $lower = mb_strtolower($sub, 'UTF-8');
                if (isset($acronymMap[$lower])) {
                    return $acronymMap[$lower];
                }

                return mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8'), 'UTF-8')
                    .mb_substr($lower, 1, null, 'UTF-8');
            }, $subParts);

            $formatted .= implode('-', $casedSubParts);
        }

        return $formatted;
    }
}
