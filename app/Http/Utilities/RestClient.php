<?php

namespace App\Http\Utilities;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class RestClient
{
    /**
     * API Url
     * @var string $apiUrl
     */
    private $apiUrl;

    /**
     * The Guzzle client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Constructor
     */
    public function __construct($apiUrl = null)
    {
        $this->apiUrl = $apiUrl;

        // Create a default Guzzle client
        $this->client = new Client();
    }

    /**
     * REST request
     *
     * @param string     $type
     * @param string     $path
     * @param array|null $params
     *
     * @return object
     */
    public function request(string $type, string $path, array $params = null)
    {
        // Build the request parameters for Guzzle
        $guzzleParams = [];
        $guzzleParams[strtoupper($type) === 'GET' ? 'query' : 'json'] = $params;

        // Create the request, pass the access token and optional parameters
        $uri = Str::contains($path, '://') ? $path : "{$this->apiUrl}{$path}";

        try {
            $response = $this->client->request($type, $uri, $guzzleParams);

            // Return Guzzle response and JSON-decoded body
            return (object)[
                'response' => $response,
                'body'     => $this->jsonDecode($response->getBody()),
            ];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }

        // Return Guzzle response and JSON-decoded body
        return (object)[
            'response' => null,
            'body'     => null,
        ];
    }

    /**
     * Decodes the JSON body.
     *
     * @param string $json The JSON body
     *
     * @return object The decoded JSON
     */
    protected function jsonDecode($json)
    {
        // From firebase/php-jwt
        if (!(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            /**
             * In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
             * to specify that large ints (like Steam Transaction IDs) should be treated as
             * strings, rather than the PHP default behaviour of converting them to floats.
             */
            $obj = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            // @codeCoverageIgnoreStart
            /**
             * Not all servers will support that, however, so for older versions we must
             * manually detect large ints in the JSON string and quote them (thus converting
             * them to strings) before decoding, hence the preg_replace() call.
             * Currently not sure how to test this so I ignored it for now.
             */
            $maxIntLength = strlen((string)PHP_INT_MAX) - 1;
            $jsonWithoutBigints = preg_replace('/:\s*(-?\d{' . $maxIntLength . ',})/', ': "$1"', $json);
            $obj = json_decode($jsonWithoutBigints);
            // @codeCoverageIgnoreEnd
        }

        return $obj;
    }
}
