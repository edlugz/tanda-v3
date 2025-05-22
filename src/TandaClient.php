<?php

namespace EdLugz\Tanda;

use EdLugz\Tanda\Exceptions\TandaException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TandaClient
{
    protected readonly string $clientId;
    protected readonly string $clientSecret;
    protected readonly string $baseUrl;
    protected string $accessToken;
    protected readonly string $authBaseUrl;
    protected readonly string $apiBaseUrl;

    /**
     * Initialize TandaClient with required configurations.
     *
     * @throws TandaException
     */
    public function __construct()
    {
        $this->validateConfigurations();

        $mode = Config::get('tanda.mode', 'uat');

        // Set base URLs for authentication and API requests
        if ($mode === 'uat') {
            $this->authBaseUrl = 'https://auth-uat.tanda.co.ke';
            $this->apiBaseUrl = 'https://tandaio-api-uats.tanda.co.ke';
        } else {
            $this->authBaseUrl = Config::get('tanda.auth_base_url');
            $this->apiBaseUrl = Config::get('tanda.api_base_url');
        }

        $this->clientId = Config::get('tanda.client_id');
        $this->clientSecret = Config::get('tanda.client_secret');

        $this->accessToken = $this->getAccessToken();
    }


    /**
     * Get access token from Tanda API.
     *
     * @return string
     * @throws TandaException
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('tanda_token', now()->addMinutes(58), function () {
            $response = $this->call(
                'accounts/v1/oauth/token',
                [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                    ]
                ],
                'POST',
                true
            );

            if (!isset($response['access_token'])) {
                throw new TandaException('Failed to retrieve access token.');
            }

            return $response['access_token'];
        });
    }


    /**
     * Validate required configurations.
     *
     * @throws InvalidArgumentException
     */
    protected function validateConfigurations(): void
    {
        foreach (['client_id', 'client_secret', 'auth_base_url', 'api_base_url'] as $configKey) {
            if (empty(Config::get("tanda.$configKey"))) {
                throw new InvalidArgumentException("Tanda config: '$configKey' is not set.");
            }
        }
    }

    /**
     * Make API calls to Tanda.
     *
     * @param string $endpoint
     * @param array $options
     * @param string $method
     * @param bool $useAuthUrl
     * @return array
     */
    protected function call(string $endpoint, array $options = [], string $method = 'POST', bool $useAuthUrl = false): array
    {
        $baseUrl = $useAuthUrl ? $this->authBaseUrl : $this->apiBaseUrl;
        $url = "$baseUrl/$endpoint";

        $headers = [
            'Accept' => 'application/json',
        ];

        // Only attach Authorization header if not using authBaseUrl (i.e., it's not a token request)
        if (!$useAuthUrl) {
            $headers['Authorization'] = "Bearer $this->accessToken";
        }

        $request = Http::withHeaders($headers);

        if (isset($options['form_params'])) {
            $request = $request->asForm();
            $payload = $options['form_params'];
        } else {
            $payload = $options;
        }

        $response = $request->{$method}($url, $payload);

        return $response->successful()
            ? $response->json()
            : $this->handleError($response);
    }


    /**
     * Handle API errors.
     *
     * @param Response $response
     * @throws TandaException
     */
    protected function handleError(Response $response): never
    {
        $statusCode = $response->status();
        $message = $response->json()['message'] ?? 'Unknown error occurred.';

        Log::error("Tanda API Error ($statusCode): $message");

        throw new TandaException("Tanda API Error ($statusCode): $message", $statusCode);
    }
}
