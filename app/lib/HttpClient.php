<?php
class HttpClient
{
    /**
     * Hent og dekode JSON fra URL (file_get_contents -> cURL fallback).
     *
     * @param string $url
     * @param int $timeout
     * @return array|null
     */
    public function fetchJson(string $url, int $timeout = 10): ?array
    {
        $ctx = stream_context_create([
            'http' => ['timeout' => $timeout, 'user_agent' => 'PHP/' . PHP_VERSION],
            'https' => ['timeout' => $timeout, 'user_agent' => 'PHP/' . PHP_VERSION],
        ]);
        $json = @file_get_contents($url, false, $ctx);
        if ($json === false) {
            if (!function_exists('curl_version')) {
                error_log("HttpClient::fetchJson: unable to fetch {$url} - no cURL and file_get_contents failed");
                return null;
            }
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => 'PHP/' . PHP_VERSION,
            ]);
            $json = curl_exec($ch);
            $errno = curl_errno($ch);
            $err = curl_error($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($json === false || $errno || ($http >= 400 && $http !== 0)) {
                error_log("HttpClient::fetchJson: cURL error fetching {$url} - err={$errno}, http={$http}, msg={$err}");
                return null;
            }
        }

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("HttpClient::fetchJson: json decode error for {$url}: " . json_last_error_msg());
            return null;
        }
        return $data;
    }
}