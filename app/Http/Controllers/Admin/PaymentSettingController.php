<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentSettingRequest;
use App\Models\PaymentSetting;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.payment', [
            'seo' => SeoData::forNoIndex('Payment Settings | INZRA', route('admin.settings.payment.edit')),
            'paypal' => PaymentSetting::paypal(),
        ]);
    }

    public function update(UpdatePaymentSettingRequest $request): RedirectResponse
    {
        $paypal = PaymentSetting::paypal();

        $data = $request->safe()->only(['mode', 'client_id', 'webhook_id']);
        $data['enabled'] = $request->boolean('enabled');

        // Leave the stored secret untouched if the admin didn't type a new one
        // (the field is never pre-filled with the real value, only a placeholder).
        if ($request->filled('client_secret')) {
            $data['client_secret'] = $request->string('client_secret');
        }

        $paypal->update($data);

        return redirect()->route('admin.settings.payment.edit')->with('status', 'Payment settings saved.');
    }
}
