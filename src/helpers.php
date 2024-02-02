<?php

if (!function_exists('ddd')) {
    /**
     * Laravel's dd function with additional capabilities
     *
     * @param $key
     * @param $number
     * @param array $replace
     * @param null $locale
     * @return string
     */
    function ddd()
    {
        if(request()->has('debug')) {
            dd();
        }
    }
}