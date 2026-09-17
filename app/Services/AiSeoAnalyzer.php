<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

/**
 * Scores a fetched page for AI-engine ("GEO") visibility. This is a
 * deterministic on-page audit, not a live query against any AI product:
 * each engine gets its own weighted blend of the same underlying signals
 * (crawler access, structured data, answer-readiness, technical clarity,
 * authority/freshness), reflecting what's publicly documented about how
 * that engine's crawler and answer style behave.
 */
class AiSeoAnalyzer
{
    public function analyze(string $html, string $robotsTxt, string $finalUrl): array
    {
        $facts = $this->extractFacts($html, $robotsTxt, $finalUrl);
        $subscores = $this->computeSubscores($facts);
        $engines = $this->computeEngineScores($subscores, $facts);
        $overall = (int) round(collect($engines)->avg('score'));

        return [
            'overall_score' => $overall,
            'engines' => $engines,
            'findings' => $this->buildFindings($facts, $subscores),
        ];
    }

    private function extractFacts(string $html, string $robotsTxt, string $finalUrl): array
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        if (trim($html) !== '') {
            $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        }
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $title = trim($xpath->query('//title')->item(0)?->textContent ?? '');
        $metaDescription = $this->metaContent($xpath, 'description');
        $robotsMeta = strtolower($this->metaContent($xpath, 'robots'));
        $canonical = $xpath->query('//link[@rel="canonical"]/@href')->item(0)?->textContent;
        $viewport = $this->metaContent($xpath, 'viewport');

        $h1Count = $xpath->query('//h1')->length;
        $h2h3Count = $xpath->query('//h2 | //h3')->length;

        $bodyText = trim(preg_replace('/\s+/', ' ', (string) $xpath->evaluate('string(//body)')) ?? '');
        $wordCount = $bodyText === '' ? 0 : str_word_count($bodyText);

        $jsonLdTypes = $this->extractJsonLdTypes($xpath);

        $listTableCount = $xpath->query('//ul | //ol | //table')->length;

        $questionHeadings = 0;
        foreach ($xpath->query('//h2 | //h3') as $node) {
            if (str_contains($node->textContent, '?')) {
                $questionHeadings++;
            }
        }

        $imgNodes = $xpath->query('//img');
        $imgTotal = $imgNodes->length;
        $imgWithAlt = 0;
        foreach ($imgNodes as $img) {
            if (trim($img->getAttribute('alt')) !== '') {
                $imgWithAlt++;
            }
        }

        $host = parse_url($finalUrl, PHP_URL_HOST);
        $internalLinks = 0;
        $externalLinks = 0;
        foreach ($xpath->query('//a[@href]') as $a) {
            $href = $a->getAttribute('href');
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) {
                continue;
            }
            $linkHost = parse_url($href, PHP_URL_HOST);
            if (! $linkHost || strtolower($linkHost) === strtolower((string) $host)) {
                $internalLinks++;
            } else {
                $externalLinks++;
            }
        }

        $hasAuthor = $xpath->query('//*[@rel="author"] | //*[contains(@class,"author")] | //meta[@name="author"]')->length > 0
            || in_array('Person', $jsonLdTypes, true);

        $hasDate = $xpath->query('//time | //meta[@property="article:published_time"] | //meta[@name="date"]')->length > 0
            || (bool) preg_match('/"datePublished"|"dateModified"/', $html);

        return [
            'title' => $title,
            'metaDescription' => $metaDescription,
            'robotsMeta' => $robotsMeta,
            'canonical' => $canonical,
            'viewport' => $viewport,
            'h1Count' => $h1Count,
            'h2h3Count' => $h2h3Count,
            'wordCount' => $wordCount,
            'jsonLdTypes' => $jsonLdTypes,
            'listTableCount' => $listTableCount,
            'questionHeadings' => $questionHeadings,
            'imgTotal' => $imgTotal,
            'imgWithAlt' => $imgWithAlt,
            'internalLinks' => $internalLinks,
            'externalLinks' => $externalLinks,
            'hasAuthor' => $hasAuthor,
            'hasDate' => $hasDate,
            'https' => str_starts_with(strtolower($finalUrl), 'https://'),
            'botAccess' => $this->parseRobotsTxt($robotsTxt),
        ];
    }

    private function metaContent(DOMXPath $xpath, string $name): string
    {
        return trim($xpath->query("//meta[translate(@name,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')=\"{$name}\"]/@content")->item(0)?->textContent ?? '');
    }

    private function extractJsonLdTypes(DOMXPath $xpath): array
    {
        $types = [];

        foreach ($xpath->query('//script[@type="application/ld+json"]') as $node) {
            $decoded = json_decode($node->textContent, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                continue;
            }

            $items = array_is_list($decoded) ? $decoded : [$decoded];
            foreach ($items as $item) {
                $this->collectJsonLdTypes($item, $types);
            }
        }

        return array_values(array_unique($types));
    }

    private function collectJsonLdTypes($item, array &$types): void
    {
        if (! is_array($item)) {
            return;
        }

        $type = $item['@type'] ?? null;
        if (is_array($type)) {
            foreach ($type as $t) {
                $types[] = (string) $t;
            }
        } elseif ($type) {
            $types[] = (string) $type;
        }

        if (isset($item['@graph']) && is_array($item['@graph'])) {
            foreach ($item['@graph'] as $node) {
                $this->collectJsonLdTypes($node, $types);
            }
        }
    }

    /**
     * A deliberately simplified robots.txt reader: it only judges whether a
     * bot is blocked from the site as a whole (a root "Disallow: /"), which
     * is the signal that matters for a summary GEO score — not full
     * path-by-path precedence matching.
     */
    private function parseRobotsTxt(string $robotsTxt): array
    {
        $groups = [];
        $currentAgents = [];
        $lastField = null;

        foreach (preg_split('/\r\n|\r|\n/', $robotsTxt) as $line) {
            $line = trim(preg_replace('/#.*$/', '', $line));
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$field, $value] = array_map('trim', explode(':', $line, 2));
            $field = strtolower($field);

            if ($field === 'user-agent') {
                if ($lastField !== 'user-agent') {
                    $currentAgents = [];
                }
                $currentAgents[] = strtolower($value);
                $lastField = 'user-agent';

                continue;
            }

            if (in_array($field, ['disallow', 'allow'], true)) {
                foreach ($currentAgents as $agent) {
                    $groups[$agent][] = ['directive' => $field, 'path' => $value];
                }
            }

            $lastField = $field;
        }

        return $groups;
    }

    private function isBotAllowed(array $robotsGroups, array $botTokens): bool
    {
        foreach ($botTokens as $token) {
            if (isset($robotsGroups[strtolower($token)])) {
                return $this->groupAllowsRoot($robotsGroups[strtolower($token)]);
            }
        }

        if (isset($robotsGroups['*'])) {
            return $this->groupAllowsRoot($robotsGroups['*']);
        }

        return true;
    }

    private function groupAllowsRoot(array $directives): bool
    {
        $blocked = false;

        foreach ($directives as $directive) {
            if (! in_array($directive['path'], ['/', ''], true)) {
                continue;
            }

            $blocked = $directive['directive'] === 'disallow';
        }

        return ! $blocked;
    }

    private function computeSubscores(array $facts): array
    {
        $titleLen = mb_strlen($facts['title']);
        $descLen = mb_strlen($facts['metaDescription']);

        $technical = 0;
        $technical += $titleLen >= 10 && $titleLen <= 65 ? 20 : ($titleLen > 0 ? 8 : 0);
        $technical += $descLen >= 50 && $descLen <= 165 ? 20 : ($descLen > 0 ? 8 : 0);
        $technical += $facts['canonical'] ? 15 : 0;
        $technical += $facts['https'] ? 15 : 0;
        $technical += $facts['viewport'] ? 10 : 0;
        $technical += ! str_contains($facts['robotsMeta'], 'noindex') ? 20 : 0;

        $typeCount = count($facts['jsonLdTypes']);
        $structured = match (true) {
            $typeCount >= 3 => 55,
            $typeCount === 2 => 40,
            $typeCount === 1 => 25,
            default => 0,
        };
        $structured += $this->hasAny($facts['jsonLdTypes'], ['FAQPage']) ? 20 : 0;
        $structured += $this->hasAny($facts['jsonLdTypes'], ['Article', 'BlogPosting', 'Product', 'Service', 'WebPage']) ? 15 : 0;
        $structured += $this->hasAny($facts['jsonLdTypes'], ['Organization', 'LocalBusiness', 'BreadcrumbList']) ? 10 : 0;

        $answer = 0;
        $answer += $facts['h1Count'] === 1 ? 20 : ($facts['h1Count'] > 1 ? 10 : 0);
        $answer += min(20, $facts['h2h3Count'] * 4);
        $answer += min(20, $facts['listTableCount'] * 5);
        $answer += $facts['questionHeadings'] > 0 ? 20 : 0;
        $answer += $facts['wordCount'] >= 300 ? 20 : (int) round($facts['wordCount'] / 300 * 20);

        $altCoverage = $facts['imgTotal'] > 0 ? (int) round($facts['imgWithAlt'] / $facts['imgTotal'] * 100) : 100;

        $authority = 0;
        $authority += $facts['hasAuthor'] ? 30 : 0;
        $authority += $facts['hasDate'] ? 30 : 0;
        $authority += $facts['externalLinks'] >= 2 ? 25 : ($facts['externalLinks'] === 1 ? 12 : 0);
        $authority += $altCoverage >= 80 ? 15 : (int) round($altCoverage / 80 * 15);

        return [
            'technical_clarity' => min(100, $technical),
            'structured_data' => min(100, $structured),
            'answer_readiness' => min(100, $answer),
            'authority_freshness' => min(100, $authority),
            'alt_coverage' => $altCoverage,
        ];
    }

    private function hasAny(array $haystack, array $needles): bool
    {
        return count(array_intersect($haystack, $needles)) > 0;
    }

    private function computeEngineScores(array $subscores, array $facts): array
    {
        $engines = [];

        foreach (config('ai_seo_checker.engines') as $key => $engine) {
            $crawlerAllowed = $this->isBotAllowed($facts['botAccess'], $engine['bots']);
            $crawlerScore = $crawlerAllowed ? 100 : 0;
            $weights = $engine['weights'];

            $score = $weights['crawler_access'] * $crawlerScore
                + $weights['structured_data'] * $subscores['structured_data']
                + $weights['answer_readiness'] * $subscores['answer_readiness']
                + $weights['technical_clarity'] * $subscores['technical_clarity']
                + $weights['authority_freshness'] * $subscores['authority_freshness'];

            $engines[] = [
                'key' => $key,
                'name' => $engine['name'],
                'icon' => $engine['icon'],
                'score' => (int) round(max(0, min(100, $score))),
                'crawler_allowed' => $crawlerAllowed,
            ];
        }

        return $engines;
    }

    private function buildFindings(array $facts, array $subscores): array
    {
        $findings = [];

        if (str_contains($facts['robotsMeta'], 'noindex')) {
            $findings[] = $this->finding(
                'critical',
                'Page is set to "noindex"',
                'A robots meta tag tells every crawler — including AI engines — not to index this page, so it cannot appear in any AI-generated answer no matter how good the content is.',
                'Remove the noindex directive from the page\'s <meta name="robots"> tag (or X-Robots-Tag header) if you want this page to be discoverable.'
            );
        }

        foreach (config('ai_seo_checker.engines') as $engine) {
            if (! $this->isBotAllowed($facts['botAccess'], $engine['bots'])) {
                $findings[] = $this->finding(
                    'critical',
                    "{$engine['name']} crawler is blocked",
                    "robots.txt disallows {$engine['bots'][0]} from crawling this site, so {$engine['name']} cannot read or cite this page at all.",
                    'Edit robots.txt to remove the blanket Disallow rule for '.implode(' / ', $engine['bots']).', or add an explicit "Allow: /" for it.'
                );
            }
        }

        if (empty($facts['jsonLdTypes'])) {
            $findings[] = $this->finding(
                'high',
                'No structured data (JSON-LD) found',
                'Structured data gives AI engines clear, unambiguous facts about the page instead of forcing them to guess from prose. Pages with none score lower across every engine.',
                'Add JSON-LD schema — start with Organization/WebSite site-wide, and Article, Product, Service or FAQPage on this page depending on its content.'
            );
        } elseif (! in_array('FAQPage', $facts['jsonLdTypes'], true) && $facts['questionHeadings'] === 0) {
            $findings[] = $this->finding(
                'medium',
                'No FAQ-style content',
                'Question-and-answer content is one of the most directly reusable formats for AI-generated answers.',
                'Add a short FAQ section covering the 3-5 questions readers most often ask, marked up with FAQPage schema.'
            );
        }

        if (mb_strlen($facts['metaDescription']) === 0) {
            $findings[] = $this->finding(
                'medium',
                'Missing meta description',
                'Several AI engines use the meta description as a fallback summary when generating citations.',
                'Add a unique 50-165 character meta description that accurately summarizes the page.'
            );
        }

        if ($facts['h1Count'] !== 1) {
            $findings[] = $this->finding(
                'low',
                $facts['h1Count'] === 0 ? 'No H1 heading found' : 'Multiple H1 headings found',
                'A single, clear H1 helps both search and AI crawlers identify the page\'s main topic.',
                'Use exactly one <h1> that states the page\'s primary topic.'
            );
        }

        if (! $facts['hasDate']) {
            $findings[] = $this->finding(
                'medium',
                'No visible publish/update date',
                'AI engines weigh freshness heavily when choosing which sources to cite, especially for time-sensitive topics.',
                'Show a visible "Last updated" date and add datePublished/dateModified to your structured data.'
            );
        }

        if (! $facts['hasAuthor']) {
            $findings[] = $this->finding(
                'low',
                'No author byline',
                'Author and expertise signals feed into the E-E-A-T signals AI engines use to judge trustworthiness.',
                'Add a visible author name/bio, and mark it up with Person schema.'
            );
        }

        if ($facts['externalLinks'] < 2) {
            $findings[] = $this->finding(
                'low',
                'Few or no outbound citations',
                'Pages that cite other authoritative sources are treated as more trustworthy references by AI engines.',
                'Link out to 2-3 credible, relevant sources that support the claims on this page.'
            );
        }

        if ($facts['wordCount'] < 300) {
            $findings[] = $this->finding(
                'medium',
                "Thin content ({$facts['wordCount']} words)",
                'Very short pages rarely contain enough self-contained information for an AI engine to safely quote or summarize.',
                'Expand the page to at least 500-800 words of substantive, original content.'
            );
        }

        if ($facts['imgTotal'] > 0 && $subscores['alt_coverage'] < 80) {
            $findings[] = $this->finding(
                'low',
                "Low image alt-text coverage ({$subscores['alt_coverage']}%)",
                'Alt text is one of the few ways AI engines "see" images, and feeds accessibility and image-answer features.',
                'Add descriptive alt text to every meaningful image.'
            );
        }

        if (! $facts['canonical']) {
            $findings[] = $this->finding(
                'low',
                'No canonical tag',
                'A canonical tag removes ambiguity about which URL is the authoritative version of this content.',
                'Add a <link rel="canonical"> tag pointing to this page\'s preferred URL.'
            );
        }

        return $findings;
    }

    private function finding(string $severity, string $title, string $detail, string $fix): array
    {
        return compact('severity', 'title', 'detail', 'fix');
    }
}
