<?php

namespace App\Http\Controllers;

use App\Actions\GenerateAiSeoReportPdf;
use App\Enums\PaymentStatus;
use App\Models\AiSeoCheck;
use App\Models\PaymentSetting;
use App\Services\AiSeoAnalyzer;
use App\Services\SafeUrlFetcher;
use App\Support\SeoData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AiSeoCheckController extends Controller
{
    public function index(): View
    {
        return view('pages.ai-seo-checker.index', [
            'seo' => SeoData::forAiSeoChecker(),
            'engines' => config('ai_seo_checker.engines'),
            'price' => (float) config('ai_seo_checker.price'),
            'paypal' => PaymentSetting::paypal(),
        ]);
    }

    public function check(Request $request, SafeUrlFetcher $fetcher, AiSeoAnalyzer $analyzer): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        $url = trim($validated['url']);

        // Reject an explicit non-http(s) scheme outright rather than
        // blindly prepending "https://" to it, which would otherwise turn
        // e.g. "javascript:..." into a technically-parseable but garbage URL.
        if (preg_match('#^([a-z][a-z0-9+.\-]*):#i', $url, $m) && ! in_array(strtolower($m[1]), ['http', 'https'], true)) {
            return response()->json(['error' => 'Please enter a valid website URL.'], 422);
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Please enter a valid website URL.'], 422);
        }

        try {
            $fetched = $fetcher->fetch($url);
            $robotsTxt = $fetcher->fetchRobotsTxt($fetched['url']);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $result = $analyzer->analyze($fetched['html'], $robotsTxt, $fetched['url']);

        $check = AiSeoCheck::create([
            'user_id' => Auth::id(),
            'url' => $fetched['url'],
            'host' => parse_url($fetched['url'], PHP_URL_HOST),
            'overall_score' => $result['overall_score'],
            'engine_scores' => $result['engines'],
            'findings' => $result['findings'],
            'access_token' => Str::random(40),
            'currency' => config('ai_seo_checker.currency'),
            'amount' => config('ai_seo_checker.price'),
        ]);

        return response()->json([
            'check_id' => $check->id,
            'access_token' => $check->access_token,
            'url' => $check->url,
            'overall_score' => $check->overall_score,
            'engines' => $check->engine_scores,
            'findings' => $this->teaseFindings($check->findings),
            'issue_count' => count($check->findings),
            'price' => (float) $check->amount,
            'currency' => $check->currency,
        ]);
    }

    public function downloadReportPdf(AiSeoCheck $aiSeoCheck, GenerateAiSeoReportPdf $generator): Response
    {
        abort_unless($aiSeoCheck->payment_status === PaymentStatus::Paid, 403);

        return response($generator->handle($aiSeoCheck), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="inzra-ai-seo-report-'.$aiSeoCheck->id.'.pdf"',
        ]);
    }

    /**
     * Only the first 2 findings are returned in full — enough to prove the
     * audit is real without giving away the whole paid report for free.
     */
    private function teaseFindings(array $findings): array
    {
        return collect($findings)->values()->map(function (array $finding, int $i) {
            $unlocked = $i < 2;

            return [
                'severity' => $finding['severity'],
                'title' => $finding['title'],
                'detail' => $unlocked ? $finding['detail'] : null,
                'fix' => $unlocked ? $finding['fix'] : null,
                'locked' => ! $unlocked,
            ];
        })->all();
    }
}
