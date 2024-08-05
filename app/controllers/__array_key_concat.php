<?php
function get_key_concat($key1, $key2): string
{
    return strtolower($key1) . "|" . strtolower($key2);
}
