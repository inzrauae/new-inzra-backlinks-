<?php

namespace App\Support;

class UrlNormalizer
{
    /**
     * Lowercase the scheme/host and drop a trailing slash-only path, so the
     * same URL entered with different casing is stored consistently.
     */
    public static function normalize(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);

        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return "{$scheme}://{$host}{$port}{$path}{$query}{$fragment}";
    }
}
