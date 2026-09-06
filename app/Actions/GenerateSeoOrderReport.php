<?php

namespace App\Actions;

use App\Enums\PublicationStatus;
use App\Models\SeoOrder;
use App\Models\SeoReport;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

/**
 * Builds the customer-facing CSV + PDF report from verified publication
 * records only — nothing here fabricates progress that admin hasn't
 * confirmed. Safe to call repeatedly (e.g. after a late correction);
 * it overwrites the previous files for the same order.
 */
class GenerateSeoOrderReport
{
    public function handle(SeoOrder $order): SeoReport
    {
        $order->loadMissing(['user', 'service', 'country']);

        $dir = "seo-reports/{$order->order_number}";
        Storage::disk('local')->makeDirectory($dir);

        $csvPath = "{$dir}/report.csv";
        $count = $this->writeCsv($csvPath, $order);

        $pdfPath = "{$dir}/report.pdf";
        $this->writePdf($pdfPath, $order);

        return SeoReport::updateOrCreate(
            ['seo_order_id' => $order->id],
            [
                'status' => 'ready',
                'pdf_path' => $pdfPath,
                'csv_path' => $csvPath,
                'publication_count' => $count,
                'generated_at' => now(),
            ]
        );
    }

    /**
     * Streams rows in chunks so a 5,000+ placement order never holds the
     * full result set in memory at once.
     */
    private function writeCsv(string $path, SeoOrder $order): int
    {
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['No.', 'Publisher', 'Publisher URL', 'Published URL', 'Target URL', 'Anchor/Text', 'Country', 'Publication Date', 'Status']);

        $rowNumber = 0;

        $order->publications()
            ->where('status', PublicationStatus::Verified->value)
            ->orderBy('publication_date')
            ->orderBy('id')
            ->chunk(500, function ($chunk) use ($handle, $order, &$rowNumber) {
                foreach ($chunk as $publication) {
                    $rowNumber++;
                    fputcsv($handle, [
                        $rowNumber,
                        $publication->publisher_name,
                        $publication->publisher_url,
                        $publication->published_url,
                        $publication->target_url ?: $order->target_url,
                        $publication->anchor_text,
                        $publication->country,
                        optional($publication->publication_date)->toDateString(),
                        $publication->status->label(),
                    ]);
                }
            });

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        Storage::disk('local')->put($path, $csv);

        return $rowNumber;
    }

    private function writePdf(string $path, SeoOrder $order): void
    {
        $publications = $order->publications()
            ->where('status', PublicationStatus::Verified->value)
            ->orderBy('publication_date')
            ->orderBy('id')
            ->get();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('reports.seo-order-pdf', [
            'order' => $order,
            'publications' => $publications,
        ])->render());
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        Storage::disk('local')->put($path, $dompdf->output());
    }
}
