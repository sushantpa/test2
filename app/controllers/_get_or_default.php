<?php
function get_or_default($array, $key, $default){
    if (isset($array[$key]) && $array[$key] != ''){
        return $array[$key];
    } else {
        return $default;
    }
}
