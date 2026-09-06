<?php

namespace App\Services;

use App\Enums\PublicationStatus;
use App\Models\SeoOrder;
use App\Models\SeoPublication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

/**
 * Two-step admin CSV import: preview() parses + validates + de-duplicates
 * without touching the database, caching the valid rows under a token;
 * import() then inserts exactly those rows once the admin confirms.
 */
class SeoPublicationCsvImporter
{
    private const REQUIRED_HEADERS = [
        'publisher_name', 'publisher_url', 'published_url', 'target_url',
        'anchor_text', 'country', 'publication_date', 'status',
    ];

    public function preview(UploadedFile $file, SeoOrder $order): array
    {
        $parsed = $this->parse($file);

        if ($parsed === null) {
            return ['error' => 'The CSV file has no rows or is missing a header row.'];
        }

        [$header, $dataRows] = $parsed;

        $existingUrls = $order->publications()
            ->whereNotNull('published_url')
            ->pluck('published_url')
            ->map(fn ($url) => strtolower(trim((string) $url)))
            ->flip()
            ->all();

        $seenInFile = [];
        $valid = [];
        $invalidSamples = [];
        $duplicateCount = 0;
        $invalidCount = 0;

        foreach ($dataRows as $lineNumber => $row) {
            $data = $this->mapRow($header, $row);
            $errors = $this->validateAndNormalize($data);

            if ($errors) {
                $invalidCount++;
                if (count($invalidSamples) < 20) {
                    $invalidSamples[] = ['line' => $lineNumber, 'errors' => $errors];
                }

                continue;
            }

            $publishedUrlKey = $data['published_url'] !== '' ? strtolower($data['published_url']) : null;

            if ($publishedUrlKey !== null && (isset($existingUrls[$publishedUrlKey]) || isset($seenInFile[$publishedUrlKey]))) {
                $duplicateCount++;

                continue;
            }

            if ($publishedUrlKey !== null) {
                $seenInFile[$publishedUrlKey] = true;
            }

            $valid[] = $data;
        }

        $token = (string) Str::uuid();
        Cache::put($this->cacheKey($token), ['seo_order_id' => $order->id, 'rows' => $valid], now()->addMinutes(30));

        return [
            'token' => $token,
            'total_rows' => count($dataRows),
            'valid_count' => count($valid),
            'duplicate_count' => $duplicateCount,
            'invalid_count' => $invalidCount,
            'invalid_samples' => $invalidSamples,
        ];
    }

    public function import(string $token, SeoOrder $order, ?int $adminId): int
    {
        $cached = Cache::get($this->cacheKey($token));

        if (! $cached || (int) $cached['seo_order_id'] !== $order->id || empty($cached['rows'])) {
            return 0;
        }

        $batch = 'csv-'.now()->format('YmdHis').'-'.Str::random(6);
        $now = now();

        $records = collect($cached['rows'])->map(fn (array $row) => [
            'seo_order_id' => $order->id,
            'publisher_name' => $row['publisher_name'] ?: null,
            'publisher_url' => $row['publisher_url'] ?: null,
            'published_url' => $row['published_url'] ?: null,
            'target_url' => $row['target_url'] ?: null,
            'anchor_text' => $row['anchor_text'] ?: null,
            'country' => $row['country'] ?: null,
            'publication_date' => $row['publication_date'] ?: null,
            'status' => $row['status'],
            'added_by' => $adminId,
            'import_batch' => $batch,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $records->chunk(500)->each(fn ($chunk) => SeoPublication::insert($chunk->all()));

        Cache::forget($this->cacheKey($token));

        return $records->count();
    }

    private function cacheKey(string $token): string
    {
        return "seo-publication-import:{$token}";
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, string>>}|null
     */
    private function parse(UploadedFile $file): ?array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return null;
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return null;
        }

        $header = array_map(
            fn ($column) => Str::of((string) $column)->trim()->lower()->snake()->toString(),
            $header
        );

        $rows = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[$lineNumber] = $row;
        }

        fclose($handle);

        return [$header, $rows];
    }

    /**
     * @param  array<int, string>  $header
     * @param  array<int, string>  $row
     * @return array<string, string>
     */
    private function mapRow(array $header, array $row): array
    {
        $data = [];

        foreach ($header as $i => $key) {
            if (in_array($key, self::REQUIRED_HEADERS, true)) {
                $data[$key] = trim((string) ($row[$i] ?? ''));
            }
        }

        foreach (self::REQUIRED_HEADERS as $key) {
            $data[$key] ??= '';
        }

        return $data;
    }

    /**
     * Validates the row and normalizes status/date in place.
     *
     * @return array<int, string> error messages; empty means the row is valid
     */
    private function validateAndNormalize(array &$data): array
    {
        $errors = [];

        if ($data['publisher_name'] === '' && $data['published_url'] === '') {
            $errors[] = 'Needs at least a publisher name or a published URL.';
        }

        foreach (['publisher_url', 'published_url', 'target_url'] as $urlField) {
            if ($data[$urlField] !== '' && ! $this->isSafeUrl($data[$urlField])) {
                $errors[] = "Invalid {$urlField}.";
            }
        }

        if ($data['status'] === '') {
            $data['status'] = PublicationStatus::Submitted->value;
        } else {
            $normalizedStatus = Str::of($data['status'])->trim()->lower()->replace(' ', '_')->toString();
            $match = collect(PublicationStatus::cases())->first(fn ($case) => $case->value === $normalizedStatus);

            if (! $match) {
                $errors[] = "Unknown status '{$data['status']}'.";
            } else {
                $data['status'] = $match->value;
            }
        }

        if ($data['publication_date'] !== '') {
            try {
                $data['publication_date'] = Carbon::parse($data['publication_date'])->toDateString();
            } catch (Throwable) {
                $errors[] = 'Invalid publication_date.';
            }
        }

        return $errors;
    }

    private function isSafeUrl(string $value): bool
    {
        $parts = parse_url($value);

        return (bool) $parts
            && ! empty($parts['scheme'])
            && ! empty($parts['host'])
            && in_array(strtolower($parts['scheme']), ['http', 'https'], true);
    }
}
