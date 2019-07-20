<?php

namespace App\Nova;

use Illuminate\Support\Arr;

trait ResourceCommon
{
    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        if (property_exists(self::class, 'title')) {
            return $this->get_value($this, self::$title);
        }
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        if (property_exists(self::class, 'subtitle')) {
            return $this->get_value($this, self::$subtitle);
        }
    }

    /**
     * Get a value from an object or an array.  Allows the ability to fetch a nested value from a
     * heterogeneous multidimensional collection using dot notation.
     *
     * @param array|object $data
     * @param string       $key
     * @param mixed        $default
     *
     * @return mixed
     */
    private function get_value($data, $key, $default = null)
    {
        $value = $default;

        if (is_array($data)) {
            $value = Arr::get($data, $key);
        } else {
            $segments = explode('.', $key);
            foreach ($segments as $segment) {
                if (!$data) {
                    break;
                }

                if (is_array($data) && array_key_exists($segment, $data)) {
                    $value = $data = $data[$segment];
                } elseif ($data && is_object($data)) {
                    $value = $data = $data->$segment ?? null;
                } else {
                    $value = $default;
                }
            }
        }

        return $value;
    }
}
