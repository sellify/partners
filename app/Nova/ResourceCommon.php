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
            $title = [];
            $titleFields = self::$title;

            $titleFields = is_array($titleFields) ? $titleFields : [$titleFields];

            foreach ($titleFields as $titleField) {
                $title[] = $this->get_value($this, $titleField, $titleField);
            }

            return implode('', $title);
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
            $title = [];
            $titleFields = self::$subtitle;

            $titleFields = is_array($titleFields) ? $titleFields : [$titleFields];

            foreach ($titleFields as $titleField) {
                $title[] = $this->get_value($this, $titleField, $titleField);
            }

            return implode('', $title);
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

        return $value ? $value : ($default ? $default : $value);
    }

    /**
     * Apply any applicable orderings to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $orderings
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applyOrderings($query, array $orderings)
    {
        if (empty($orderings) && property_exists(static::class, 'orderBy')) {
            $orderings = static::$orderBy;
        }

        return parent::applyOrderings($query, $orderings);
    }
}
