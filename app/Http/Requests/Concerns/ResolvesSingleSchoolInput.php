<?php

namespace App\Http\Requests\Concerns;

use App\Models\School;

trait ResolvesSingleSchoolInput
{
    /**
     * Resolve single-school context into the request payload before validation.
     */
    protected function mergeSingleSchoolId(?int $fallbackSchoolId = null): void
    {
        $userSchoolId = $this->user()?->school_id;

        if ($userSchoolId) {
            $this->merge([
                'school_id' => (int) $userSchoolId,
            ]);

            return;
        }

        if (! $this->filled('school_id')) {
            $resolvedSchoolId = $fallbackSchoolId ?: School::currentId();

            if ($resolvedSchoolId > 0) {
                $this->merge([
                    'school_id' => $resolvedSchoolId,
                ]);
            }
        }
    }
}
