<?php
function __fetch_sum($data, $key)
{
    $result = 0;

    foreach ($data as $res) {
        $result += $res[$key];
    }

    return $result;
}

function __fetch_multiple_sum($data, $keys)
{

    $results = array();

    for ($x = 0; $x < count($keys); $x++) {
        $results[] = 0;
    }

    foreach ($data as $res) {
        for ($x = 0; $x < count($keys); $x++) {
            $results[$x] = $results[$x] + $res[$keys[$x]];
        }
    }

    return $results;
}
