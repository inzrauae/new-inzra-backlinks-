<?php

namespace App\Actions;

use App\Models\AiSeoCheck;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Renders the paid "how to reach 100%" PDF straight from the stored
 * check/findings JSON — nothing external is re-fetched, so this is cheap
 * enough to regenerate on every download rather than caching a file.
 */
class GenerateAiSeoReportPdf
{
    public function handle(AiSeoCheck $check): string
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('reports.ai-seo-checker-pdf', [
            'check' => $check,
        ])->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
