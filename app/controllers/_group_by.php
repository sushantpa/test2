<?php
require_once(__DIR__ . "/../../app/controllers/__array_key_concat.php");

function _group_by($array, $key)
{
    $result = array();
    foreach ($array as $val) {
        $result[$val[$key]][] = $val;
    }
    return $result;
}

function _group_by_two($array, $key1, $key2)
{
    $result = array();
    foreach ($array as $val) {
        $result[get_key_concat($val[$key1], $val[$key2])][] = $val;
    }
    return $result;
}
