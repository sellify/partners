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
