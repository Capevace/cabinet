<?php

namespace Cabinet\RollingSignature;

class Signature
{
    public const ALGORITHM = 'sha256';

    protected function __construct(
        protected readonly Window $window,
        protected readonly string $url,
        protected readonly array $parameters,
    ) {
    }

    /**
     * Create a signed URL.
     */
    public static function url(string $url): static
    {
        $currentWindow = Window::now();

        if (strpos($url, '?') === false) {
            return new static($currentWindow, $url, []);
        }

        [$url, $query] = explode('?', $url, 2);

        parse_str($query, $parameters);

        return new static($currentWindow, $url, $parameters);
    }

    /**
     * Create a signed URL for a route.
     */
    public static function route(string $route, array $parameters): static
    {
        $url = route($route, $parameters);

        return static::url($url);
    }

    /**
     * Validate a signed URL.
     */
    public static function validate(string $url): bool
    {
        $currentWindow = Window::now();

        $parsedUrl = static::parseUrl($url);

        if ($parsedUrl === null) {
            return false;
        }

        [$url, $parameters, $signature, $window, $salt] = $parsedUrl;

        // If any of the required parameters are missing, the URL is invalid.
        if ($window === null || $salt === null || $signature === null) {
            return false;
        }

        // If the salt has changed, the URL is invalid.
        $hashedSalt = static::hashedSalt();

        if ($salt !== $hashedSalt) {
            return false;
        }

        // If the window is invalid, the URL is invalid.
        if ($window !== $currentWindow->index) {
            return false;
        }

        $currentSignatureForGivenParameters = new static($currentWindow, $url, $parameters);

        // If the signature is invalid, the URL is invalid.
        if ($currentSignatureForGivenParameters->signature() !== $signature) {
            return false;
        }

        return true;
    }

    /**
     * @return array{string, array, string, int, string}|null
     */
    protected static function parseUrl(string $url): ?array
    {
        $parameters = [];

        if (strpos($url, '?') === false) {
            return null;
        }

        [$url, $query] = explode('?', $url, 2);

        parse_str($query, $parameters);

        $window = $parameters['w'] ?? null;
        $salt = $parameters['s'] ?? null;
        $signature = $parameters['signature'] ?? null;

        if ($window === null || $salt === null || $signature === null) {
            return null;
        }

        $window = intval($window);
        $salt = strval($salt);
        $signature = strval($signature);

        unset($parameters['w'], $parameters['s'], $parameters['signature']);

        return [$url, $parameters, $signature, $window, $salt];
    }

    /**
     * This salt is used to invalidate all rolling signatures for cache busting purposes.
     * It is not necessarily secret or cryptographically secure, it only needs to cause the signature to change.
     */
    protected static function hashedSalt(): string
    {
        return hash('sha256', config('app.key'));
    }

    /**
     * The key used to sign the URL. This should be kept secret and is usually the Laravel app key.
     */
    protected static function key(): string
    {
        return config('app.key');
    }

    public function parameters(?string $signature = null): array
    {
        $parameters = [
            ...$this->parameters,
            'expires' => $this->window->end->getTimestamp(),
            's' => static::hashedSalt(),
            'w' => $this->window->index,
        ];

        if ($signature !== null) {
            $parameters['signature'] = $signature;
        }

        // We choose ksort() to ensure that the parameters are sorted by key.
        ksort($parameters);

        return $parameters;
    }

    /**
     * Get the URL without the signature.
     */
    public function unsignedUrl(): string
    {
        $parameters = $this->parameters();
        $query = http_build_query($parameters);

        return "{$this->url}?{$query}";
    }

    /**
     * Get the signature for the URL.
     */
    public function signature(): string
    {
        $unsignedUrl = $this->unsignedUrl();

        return hash_hmac(
            algo: static::ALGORITHM,
            data: $unsignedUrl,
            key: static::key()
        );
    }

    /**
     * Get the signed URL.
     */
    public function signedUrl(): string
    {
        $signature = $this->signature();
        $parameters = $this->parameters(signature: $signature);

        $query = http_build_query($parameters);

        return "{$this->url}?{$query}";
    }

    public function window(): Window
    {
        return $this->window;
    }
}
