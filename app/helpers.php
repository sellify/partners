<?php

/**
 * Add parameter to url
 * @param $url
 * @param $key
 * @param $value
 *
 * @return string
 */
function addQueryParam($url, $key, $value)
{
    $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
    $url = substr($url, 0, -1);

    if (strpos($url, '?') === false) {
        return ($url . '?' . $key . '=' . $value);
    } else {
        return ($url . '&' . $key . '=' . $value);
    }
}

/**
 * Print data
 * @param mixed ...$arguments
 */
function pr(...$arguments)
{
    echo '<pre>';
    foreach ($arguments as $i => $argument) {
        if ($i < count($arguments)) {
            print_r($argument);
        }
    }
    echo '</pre>';

    if ($arguments[count($arguments) - 1] !== false) {
        exit;
    }
}

function ellipsis($text, $length, $ellipsis = '...')
{
    return \Illuminate\Support\Str::limit($text, $length, $ellipsis);
}

if (!function_exists('app_setting')) {
    /**
     * Get app setting
     *
     * @param $settings
     * @param $key
     * @param $default
     * @param $return
     *
     * @return mixed
     */
    function app_setting($settings, $key, $defaultIfEmpty = false, $default = null)
    {
        if (array_key_exists($key, $settings) && array_key_exists('value', $settings[$key])) {
            $type = strtoupper(isset($settings[$key]['type']) ? $settings[$key]['type'] : 'TEXT');
            $value = $settings[$key]['user_setting_id'] ? $settings[$key]['value'] : $settings[$key]['default_value'];
            $value = $value != '' ? $value : ($defaultIfEmpty ? $settings[$key]['default_value'] : '');
            $value = $value != '' ? $value : ($default ? $default : '');

            $value = format_value($value, $type);

            return $value ? $value : ($defaultIfEmpty ? format_value($settings[$key]['default_value'], $type) : '');
        } else {
            return $default;
        }
    }

    function format_value($value, $type)
    {
        if ($type === 'INT' || $type === 'NUMBER') {
            $value = (int)$value;
        } elseif ($type == 'BOOLEAN') {
            $value = (int)$value;
            $value = $value ? true : false;
        } elseif ($type == 'JSON') {
            $value = json_decode($value, true);
        } elseif (in_array($type, ['TEXT', 'TEXTAREA', 'STRING', 'CODE', 'COUNTRY', 'CURRENCY', 'SELECT'])) {
            $value = (string)$value;
        }

        return $value;
    }
}

/**
 * Runs a request to the Shopify API.
 *
 * @param string     $type The type of request... GET, POST, PUT, DELETE
 * @param string     $uri The Shopify API path... /admin/xxxx/xxxx.json
 * @param array|null $params Optional parameters to send with the request
 *
 * @return array|object An array of the Guzzle response, and JSON-decoded body
 */
function rest(string $type, string $uri, array $params = null, $headers = [])
{
    $client = new \GuzzleHttp\Client();
    // Build the request parameters for Guzzle
    $guzzleParams = [];
    $guzzleParams[strtoupper($type) === 'GET' ? 'query' : 'json'] = $params;
    $guzzleParams['headers'] = $headers;

    $response = $client->request($type, $uri, $guzzleParams);

    // Return Guzzle response and JSON-decoded body
    return (object)[
        'response' => $response,
        'body'     => jsonDecode($response->getBody()),
    ];
}

/**
 * Decodes the JSON body.
 *
 * @param string $json The JSON body
 *
 * @return object The decoded JSON
 */
function jsonDecode($json)
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
