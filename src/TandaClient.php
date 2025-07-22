<?php

namespace EdLugz\Tanda;

use EdLugz\Tanda\Exceptions\TandaException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TandaClient
{
    protected readonly string $clientId;
    protected readonly string $clientSecret;
    protected readonly string $authBaseUrl;
    protected readonly string $apiBaseUrl;
    protected string $accessToken;
    protected Client $httpClient;

    /**
     * Initialize TandaClient with required configurations.
     *
     * @throws TandaException
     */
    public function __construct()
    {
        $this->validateConfigurations();

        $mode = Config::get('tanda.mode', 'uat');

        $this->authBaseUrl = $mode === 'uat'
            ? 'https://identity-uat.tanda.africa'
            : Config::get('tanda.auth_base_url');

        $this->apiBaseUrl = $mode === 'uat'
            ? 'https://api-v3-uat.tanda.africa'
            : Config::get('tanda.api_base_url');

        $this->clientId = Config::get('tanda.client_id');
        $this->clientSecret = Config::get('tanda.client_secret');

        $this->httpClient = new Client(['timeout' => 30]);

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
                'v1/oauth2/token',
                [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
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
     * Make API calls to Tanda.
     *
     * @param string $endpoint
     * @param array $options
     * @param string $method
     * @param bool $useAuthUrl
     * @return array
     * @throws TandaException
     */
    protected function call(string $endpoint, array $options = [], string $method = 'POST', bool $useAuthUrl = false): array
    {
        $baseUrl = rtrim($useAuthUrl ? $this->authBaseUrl : $this->apiBaseUrl, '/');
        $url = "$baseUrl/$endpoint";

        $headers = [];

        if (!$useAuthUrl) {
            $headers = [
                'Accept' => 'application/json',
            ];
            $headers['Authorization'] = "Bearer $this->accessToken";
        }

        $guzzleOptions = [
            'headers' => $headers,
        ];

        if (isset($options['form_params'])) {
            $guzzleOptions['form_params'] = $options['form_params'];
        } elseif (!empty($options)) {
            $guzzleOptions['json'] = $options;
        }

        try {
            $response = $this->httpClient->request($method, $url, $guzzleOptions);
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException|ServerException|ConnectException $e) {
            $this->handleError($e);
        } catch (GuzzleException $e) {
            Log::error("Tanda GuzzleException: {$e->getMessage()}");
            throw new TandaException("Unexpected Guzzle error: {$e->getMessage()}", $e->getCode());
        }
    }

    /**
     * Handle API errors from Guzzle exceptions.
     *
     * @param ClientException|ServerException|ConnectException $e
     * @return never
     * @throws TandaException
     */
    protected function handleError(ClientException|ServerException|ConnectException $e): never
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 0;
        $body = $response ? json_decode($response->getBody()->getContents(), true) : null;

        $message = $body['message'] ?? $body['error_description'] ?? $e->getMessage();

        Log::error("Tanda API Error ($statusCode): $message", ['exception' => $e]);

        throw new TandaException("Tanda API Error ($statusCode): $message", $statusCode);
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
}
