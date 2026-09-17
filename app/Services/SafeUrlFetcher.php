<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Fetches a user-supplied URL for the AI SEO checker. Since the target is
 * arbitrary input from anonymous visitors, every hop (including redirects)
 * is re-validated against private/reserved IP ranges before the request is
 * made — trusting only the original hostname would let a public host
 * redirect the fetch into an internal address (SSRF).
 */
class SafeUrlFetcher
{
    private const MAX_REDIRECTS = 3;

    private const TIMEOUT_SECONDS = 8;

    private const MAX_BYTES = 3_000_000;

    private const USER_AGENT = 'INZRA-AI-SEO-Checker/1.0 (+https://inzra.com/ai-seo-checker)';

    /**
     * DNS resolution is pulled behind a closure (rather than calling
     * gethostbynamel()/dns_get_record() inline) purely so tests can inject
     * fake IPs instead of depending on real, outbound DNS lookups.
     *
     * @var \Closure(string): array<int, string>
     */
    private \Closure $resolver;

    public function __construct(?\Closure $resolver = null)
    {
        $this->resolver = $resolver ?? function (string $host): array {
            $ips = gethostbynamel($host) ?: [];

            try {
                foreach (dns_get_record($host, DNS_AAAA) ?: [] as $record) {
                    if (! empty($record['ipv6'])) {
                        $ips[] = $record['ipv6'];
                    }
                }
            } catch (Throwable) {
                // Best-effort only — absence of AAAA records isn't fatal.
            }

            return $ips;
        };
    }

    /**
     * @return array{url: string, html: string, status: int}
     */
    public function fetch(string $url): array
    {
        $current = $url;

        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            $this->assertPubliclyRoutable($current);

            try {
                $response = Http::withHeaders([
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                    ->timeout(self::TIMEOUT_SECONDS)
                    ->withOptions(['allow_redirects' => false])
                    ->get($current);
            } catch (ConnectionException) {
                throw new RuntimeException("Couldn't connect to that URL — please check it's correct and publicly reachable.");
            }

            $location = $response->header('Location');

            if (in_array($response->status(), [301, 302, 303, 307, 308], true) && $location) {
                $current = $this->resolveRedirectTarget($current, $location);

                continue;
            }

            if ($response->serverError()) {
                throw new RuntimeException("That site returned an error (HTTP {$response->status()}) and couldn't be checked.");
            }

            return [
                'url' => $current,
                'html' => substr($response->body(), 0, self::MAX_BYTES),
                'status' => $response->status(),
            ];
        }

        throw new RuntimeException('That URL redirected too many times.');
    }

    public function fetchRobotsTxt(string $pageUrl): string
    {
        $parts = parse_url($pageUrl);

        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $robotsUrl = "{$parts['scheme']}://{$parts['host']}".(isset($parts['port']) ? ":{$parts['port']}" : '').'/robots.txt';

        try {
            $this->assertPubliclyRoutable($robotsUrl);

            $response = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(5)
                ->withOptions(['allow_redirects' => false])
                ->get($robotsUrl);

            return $response->successful() ? substr($response->body(), 0, 200_000) : '';
        } catch (Throwable) {
            return '';
        }
    }

    private function resolveRedirectTarget(string $currentUrl, string $location): string
    {
        if (filter_var($location, FILTER_VALIDATE_URL)) {
            return $location;
        }

        $base = parse_url($currentUrl);
        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'] ?? '';
        $port = isset($base['port']) ? ":{$base['port']}" : '';

        if (str_starts_with($location, '//')) {
            return "{$scheme}:{$location}";
        }

        if (str_starts_with($location, '/')) {
            return "{$scheme}://{$host}{$port}{$location}";
        }

        $basePath = rtrim(dirname($base['path'] ?? '/'), '/');

        return "{$scheme}://{$host}{$port}{$basePath}/{$location}";
    }

    private function assertPubliclyRoutable(string $url): void
    {
        $parts = parse_url($url);

        if (! $parts || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || empty($parts['host'])) {
            throw new RuntimeException('Only http:// and https:// URLs are supported.');
        }

        $host = strtolower($parts['host']);

        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            throw new RuntimeException('That URL points to a local address, which cannot be checked.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : ($this->resolver)($host);

        if (empty($ips)) {
            throw new RuntimeException('Could not resolve that domain.');
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new RuntimeException('That URL points to a private or reserved network, which cannot be checked.');
            }
        }
    }
}
