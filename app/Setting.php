<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected static $firstSetting = null;

    /**
     * Get value of first setting by key
     * @param      $key
     * @param null $default
     *
     * @return |null
     */
    public static function value($key, $default = null)
    {
        if (!self::$firstSetting) {
            self::$firstSetting = self::first();
        }

        return self::$firstSetting->$key ?? $default;
    }
}
