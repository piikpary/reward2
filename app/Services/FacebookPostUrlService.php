<?php

namespace App\Services;

class FacebookPostUrlService
{
    public function normalize(string $url): string
    {
        $url = trim($url);

        $parts = parse_url($url);

        if ($parts === false) {
            return $url;
        }

        $host = strtolower(
            $parts['host'] ?? 'facebook.com'
        );

        if (
            in_array(
                $host,
                [
                    'www.facebook.com',
                    'm.facebook.com',
                    'mbasic.facebook.com',
                    'web.facebook.com',
                ],
                true
            )
        ) {
            $host = 'facebook.com';
        }

        $path = '/' . ltrim(
            $parts['path'] ?? '',
            '/'
        );

        $path = rtrim($path, '/');

        $queryString = '';

        /*
         * Keep required query values for old Facebook
         * permalink URLs.
         */
        if (
            str_ends_with(
                strtolower($path),
                '/permalink.php'
            )
        ) {
            parse_str(
                $parts['query'] ?? '',
                $query
            );

            $allowedQuery = array_filter([
                'story_fbid' =>
                    $query['story_fbid'] ?? null,

                'id' =>
                    $query['id'] ?? null,
            ]);

            if ($allowedQuery !== []) {
                $queryString =
                    '?' . http_build_query($allowedQuery);
            }
        }

        return "https://{$host}{$path}{$queryString}";
    }

    public function hash(string $url): string
    {
        return hash('sha256', $url);
    }
}