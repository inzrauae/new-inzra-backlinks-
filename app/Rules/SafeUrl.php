<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Requires an http/https URL with a host, rejecting javascript:, data:,
 * and any other scheme that could be used for an XSS/open-redirect payload.
 */
class SafeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        if (str_contains($value, "\0") || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            $fail('The :attribute is invalid.');

            return;
        }

        $parts = parse_url(trim($value));

        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            $fail('The :attribute must be a valid URL, e.g. https://example.com');

            return;
        }

        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            $fail('The :attribute must start with http:// or https://');
        }
    }
}
