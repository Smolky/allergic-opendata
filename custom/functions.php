<?php
/**
 * Pluck an array of values from an array. (Only for PHP 5.3+)
 *
 * @param  $array - data
 * @param  $key - value you want to pluck from array
 *
 * @return plucked array only with key data
 */
function pluck ($array, $key) {
    return array_map (function ($v) use ($key) {
        return is_object ($v) ? $v->$key : $v[$key];
    }, $array);
}


/**
 * generate_password
 *
 * @url https://stackoverflow.com/questions/6101956/generating-a-random-password-in-php
 *
 * @param $length int
 *
 * @return String
 */
function generate_password ($length = 8) {

    // Source
    $source = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
    
    // Return shuffle string
    return substr (str_shuffle ($source), 0, $length);

}
