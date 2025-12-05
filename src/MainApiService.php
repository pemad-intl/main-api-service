<?php

namespace Pemad\MainApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use RuntimeException;

class MainApiService
{
    protected string $baseUrl;
    protected string $appCode;
    protected string $secret;
    protected string $apikey;
    protected array $httpOptions;
    protected int $retryAttempts;
    protected int $retrySleep;
    protected bool $cacheEnabled;
    protected int $cacheTtl;
    protected bool $rateLimitEnabled;
    protected int $rateLimitAttempts;
    protected int $rateLimitDecay;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('mainapi.base_url', ''), '/');
        $this->appCode = config('mainapi.app_code', '');
        $this->secret  = config('mainapi.secret', '');
        $this->apikey  = config('mainapi.apikey', '');
        $this->httpOptions = config('mainapi.http', ['verify' => true, 'timeout' => 10]);

        $this->retryAttempts = config('mainapi.retry.attempts', 3);
        $this->retrySleep = config('mainapi.retry.sleep', 500);

        $this->cacheEnabled = config('mainapi.cache.enabled', true);
        $this->cacheTtl = config('mainapi.cache.ttl', 60);

        $this->rateLimitEnabled = config('mainapi.rate_limit.enabled', true);
        $this->rateLimitAttempts = config('mainapi.rate_limit.max_attempts', 60);
        $this->rateLimitDecay = config('mainapi.rate_limit.decay_seconds', 60);
    }

    protected function generateSignature(int $timestamp, string $body): string
    {
        return hash_hmac('sha256', $timestamp . '.' . $body, $this->secret);
    }

    protected function headers(string $body = ''): array
    {
        $timestamp = time();
        return [
            'X-App-Code'  => $this->appCode,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $this->generateSignature($timestamp, $body),
            'apikey'      => $this->apikey,
            'Accept'      => 'application/json',
        ];
    }

    protected function applyRateLimit(string $key): void
    {
        if (! $this->rateLimitEnabled) return;

        $limiterKey = "mainapi:{$key}";
        if (RateLimiter::tooManyAttempts($limiterKey, $this->rateLimitAttempts)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            throw new RuntimeException("Rate limit exceeded. Try again in {$seconds} seconds.");
        }
        RateLimiter::hit($limiterKey, $this->rateLimitDecay);
    }

    /**
     * GET with cache, rate-limit and retry.
     */
    public function get(string $endpoint, array $query = [], array $options = []): Response
    {
        $endpoint = '/' . ltrim($endpoint, '/');
        $url = $this->baseUrl . $endpoint;

        // build cache key
        $cacheKey = 'mainapi:get:' . md5($url . '?' . http_build_query($query));

        // rate limiting by endpoint
        $this->applyRateLimit(str_replace('/', '.', trim($endpoint, '/')));

        // caching
        if ($this->cacheEnabled && $this->cacheTtl > 0) {
            $cached = Cache::get($cacheKey);
            if ($cached instanceof Response) {
                return $cached;
            }
        }

        $bodyForSignature = "";
        $headers = $this->headers($bodyForSignature);

        $http = Http::withHeaders($headers);

        // ⬅️ Accept JSON only if needed
        if (($options['accept_json'] ?? false) === true) {
            $http = $http->acceptJson();
        }

        // only valid cURL options passed here
        unset($options['accept_json']);

        $http = $http->withOptions(array_merge($this->httpOptions, $options));

        $response = $http->retry($this->retryAttempts, $this->retrySleep)
            ->get($url, $query);

        if ($this->cacheEnabled && $this->cacheTtl > 0 && $response->successful()) {
            Cache::put($cacheKey, $response, $this->cacheTtl);
        }

        return $response;
    }

    /**
     * POST with retry & optional options.
     */
    public function post(string $endpoint, array $payload = [], array $options = []): Response
    {
        $endpoint = '/' . ltrim($endpoint, '/');
        $url = $this->baseUrl . $endpoint;

        $this->applyRateLimit(str_replace('/', '.', trim($endpoint, '/')));

        $body = json_encode($payload);
        $headers = $this->headers($body);
        $headers['Content-Type'] = 'application/json';

        $http = Http::withHeaders($headers)->withOptions(array_merge($this->httpOptions, $options));

        $response = $http->retry($this->retryAttempts, $this->retrySleep)
            ->post($url, $payload);

        return $response;
    }

    /**
     * Convenience: invalidate cache for an endpoint (useful after POST/PUT)
     */
    public function invalidateCache(string $endpoint, array $query = []): void
    {
        $endpoint = '/' . ltrim($endpoint, '/');
        $url = $this->baseUrl . $endpoint;
        $cacheKey = 'mainapi:get:' . md5($url . '?' . http_build_query($query));
        Cache::forget($cacheKey);
    }
}
