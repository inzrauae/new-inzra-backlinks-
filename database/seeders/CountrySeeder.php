<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * A practical list covering the countries INZRA actively markets to
     * (see config/markets.php) plus the rest of the world, alphabetically.
     * Featured markets are pinned to the top of the dropdown via sort_order.
     */
    public function run(): void
    {
        $featured = [
            'United States' => 'US',
            'United Kingdom' => 'GB',
            'United Arab Emirates' => 'AE',
            'Saudi Arabia' => 'SA',
            'Sri Lanka' => 'LK',
            'India' => 'IN',
            'Australia' => 'AU',
            'Canada' => 'CA',
            'Germany' => 'DE',
            'Netherlands' => 'NL',
            'Sweden' => 'SE',
            'Norway' => 'NO',
            'Denmark' => 'DK',
            'France' => 'FR',
            'Spain' => 'ES',
            'Italy' => 'IT',
            'Switzerland' => 'CH',
            'Austria' => 'AT',
            'Poland' => 'PL',
            'Czechia' => 'CZ',
            'Hungary' => 'HU',
            'Romania' => 'RO',
            'Israel' => 'IL',
            'Japan' => 'JP',
            'South Korea' => 'KR',
        ];

        $rest = [
            'Afghanistan' => 'AF', 'Albania' => 'AL', 'Algeria' => 'DZ', 'Angola' => 'AO',
            'Argentina' => 'AR', 'Armenia' => 'AM', 'Azerbaijan' => 'AZ', 'Bahrain' => 'BH',
            'Bangladesh' => 'BD', 'Belarus' => 'BY', 'Belgium' => 'BE', 'Belize' => 'BZ',
            'Bolivia' => 'BO', 'Bosnia and Herzegovina' => 'BA', 'Botswana' => 'BW', 'Brazil' => 'BR',
            'Brunei' => 'BN', 'Bulgaria' => 'BG', 'Cambodia' => 'KH', 'Cameroon' => 'CM',
            'Chile' => 'CL', 'China' => 'CN', 'Colombia' => 'CO', 'Costa Rica' => 'CR',
            'Croatia' => 'HR', 'Cyprus' => 'CY', 'Dominican Republic' => 'DO', 'Ecuador' => 'EC',
            'Egypt' => 'EG', 'El Salvador' => 'SV', 'Estonia' => 'EE', 'Ethiopia' => 'ET',
            'Fiji' => 'FJ', 'Finland' => 'FI', 'Georgia' => 'GE', 'Ghana' => 'GH',
            'Greece' => 'GR', 'Guatemala' => 'GT', 'Honduras' => 'HN', 'Hong Kong' => 'HK',
            'Iceland' => 'IS', 'Indonesia' => 'ID', 'Iraq' => 'IQ', 'Ireland' => 'IE',
            'Jamaica' => 'JM', 'Jordan' => 'JO', 'Kazakhstan' => 'KZ', 'Kenya' => 'KE',
            'Kuwait' => 'KW', 'Latvia' => 'LV', 'Lebanon' => 'LB', 'Libya' => 'LY',
            'Liechtenstein' => 'LI', 'Lithuania' => 'LT', 'Luxembourg' => 'LU', 'Malaysia' => 'MY',
            'Maldives' => 'MV', 'Malta' => 'MT', 'Mauritius' => 'MU', 'Mexico' => 'MX',
            'Moldova' => 'MD', 'Monaco' => 'MC', 'Mongolia' => 'MN', 'Montenegro' => 'ME',
            'Morocco' => 'MA', 'Myanmar' => 'MM', 'Namibia' => 'NA', 'Nepal' => 'NP',
            'New Zealand' => 'NZ', 'Nicaragua' => 'NI', 'Nigeria' => 'NG', 'North Macedonia' => 'MK',
            'Oman' => 'OM', 'Pakistan' => 'PK', 'Panama' => 'PA', 'Paraguay' => 'PY',
            'Peru' => 'PE', 'Philippines' => 'PH', 'Portugal' => 'PT', 'Qatar' => 'QA',
            'Russia' => 'RU', 'Rwanda' => 'RW', 'Senegal' => 'SN', 'Serbia' => 'RS',
            'Singapore' => 'SG', 'Slovakia' => 'SK', 'Slovenia' => 'SI', 'South Africa' => 'ZA',
            'Taiwan' => 'TW', 'Tanzania' => 'TZ', 'Thailand' => 'TH', 'Tunisia' => 'TN',
            'Turkey' => 'TR', 'Uganda' => 'UG', 'Ukraine' => 'UA', 'Uruguay' => 'UY',
            'Uzbekistan' => 'UZ', 'Venezuela' => 'VE', 'Vietnam' => 'VN', 'Yemen' => 'YE',
            'Zambia' => 'ZM', 'Zimbabwe' => 'ZW',
        ];

        $now = now();
        $rows = [];
        $sort = 0;

        foreach ($featured as $name => $code) {
            $rows[] = ['name' => $name, 'code' => $code, 'sort_order' => $sort++, 'created_at' => $now, 'updated_at' => $now];
        }

        $sort = 1000;
        foreach ($rest as $name => $code) {
            $rows[] = ['name' => $name, 'code' => $code, 'sort_order' => $sort++, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('countries')->upsert($rows, ['code'], ['name', 'sort_order', 'updated_at']);
    }
}
