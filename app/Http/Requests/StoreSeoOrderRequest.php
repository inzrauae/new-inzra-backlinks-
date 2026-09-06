<?php

namespace App\Http\Requests;

use App\Models\SeoService;
use App\Rules\SafeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSeoOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_url' => ['required', 'string', 'max:500', new SafeUrl],
            'keyword_1' => ['required', 'string', 'max:255'],
            'keyword_2' => ['nullable', 'string', 'max:255'],
            'keyword_3' => ['nullable', 'string', 'max:255'],
            'keyword_4' => ['nullable', 'string', 'max:255'],
            'keyword_5' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'article' => ['nullable', 'string', 'max:20000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:1'],
            'terms_accepted' => ['accepted'],
        ];
    }

    /**
     * Never trust the browser's quantity bounds — re-check against the
     * service's current min/max, which may differ from what the page
     * rendered if an admin changed them after the page loaded.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var SeoService|null $service */
            $service = $this->route('service');

            if (! $service instanceof SeoService) {
                return;
            }

            if (! $service->is_active) {
                $validator->errors()->add('service', 'This service is not currently available.');

                return;
            }

            $quantity = (int) $this->input('quantity');

            if ($quantity < $service->min_quantity || $quantity > $service->max_quantity) {
                $validator->errors()->add(
                    'quantity',
                    "Quantity must be between {$service->min_quantity} and {$service->max_quantity} for this service."
                );
            }
        });
    }

    /**
     * @return array<int, string>
     */
    public function keywords(): array
    {
        return collect([1, 2, 3, 4, 5])
            ->map(fn (int $position) => trim((string) $this->input("keyword_{$position}", '')))
            ->filter(fn (string $keyword) => $keyword !== '')
            ->values()
            ->all();
    }
}
