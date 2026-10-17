<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EmailExtractor
{
    private const SKIP_DOMAINS = [
        'google.com',
        'facebook.com',
        'instagram.com',
        'twitter.com',
        'x.com',
        'linkedin.com',
        'youtube.com',
        'tiktok.com',
        'wa.me',
        'whatsapp.com',
        'sentry.io',
        'wixpress.com',
        'schema.org',
        'example.com',
        'domain.com',
        'email.com',
        'yoursite.com',
    ];

    public function fromWebsite(?string $website): ?string
    {
        if (! $website) {
            return null;
        }

        $url = $this->normalizeUrl($website);
        if (! $url) {
            return null;
        }

        // Homepage only (fast path). Contact pages made scrapes hang for hundreds of sites.
        $html = $this->fetchHtml($url);
        if (! $html) {
            return null;
        }

        $emails = $this->extractEmails($html);

        return $emails[0] ?? null;
    }

    /**
     * @return list<string>
     */
    public function extractEmails(string $html): array
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
        $html = preg_replace('/\[at\]|\(at\)/i', '@', $html) ?? $html;
        $html = preg_replace('/\[dot\]|\(dot\)/i', '.', $html) ?? $html;

        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $html, $matches);

        $valid = [];
        foreach ($matches[0] ?? [] as $email) {
            $email = strtolower(trim($email));
            if ($this->isUsefulEmail($email)) {
                $valid[$email] = true;
            }
        }

        return array_keys($valid);
    }

    private function isUsefulEmail(string $email): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = substr(strrchr($email, '@') ?: '', 1);
        foreach (self::SKIP_DOMAINS as $skip) {
            if ($domain === $skip || str_ends_with($domain, '.'.$skip)) {
                return false;
            }
        }

        foreach (['.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.css', '.js'] as $ext) {
            if (str_ends_with($email, $ext)) {
                return false;
            }
        }

        return true;
    }

    private function normalizeUrl(string $website): ?string
    {
        $website = trim($website);
        if ($website === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $website)) {
            $website = 'https://'.$website;
        }

        return filter_var($website, FILTER_VALIDATE_URL) ?: null;
    }

    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::timeout(4)
                ->connectTimeout(3)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; ScrapDataBot/1.0)',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            return mb_substr($response->body(), 0, 200000);
        } catch (\Throwable) {
            return null;
        }
    }
}
