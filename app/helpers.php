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
