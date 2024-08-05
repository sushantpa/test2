<?php

function getLogString($job, $message){
    date_default_timezone_set('Africa/Lagos');
    $current_time = date('Y-m-d H:i:s.u');
    return "[{$job}][{$current_time}] - {$message} <br/> \n";
}

function printTen($job,$logArray, $hsfArray, $message){
    date_default_timezone_set('Africa/Lagos');
    $current_time = date('Y-m-d H:i:s.u');

    if(count($hsfArray) > 20){
        $hsfArray = array_values($hsfArray);
        date_default_timezone_set('Africa/Lagos');
        $current_time = date('Y-m-d H:i:s.u');    
        for($i = 10; $i < 20; $i++){
            echo ("[{$job}] [{$current_time}]" . "===". $hsfArray[$i]["hsf_id"]. " :". $message . "=== <br /> \n");
            // $logArray[] = "[{$job}] [{$current_time}]" . "===". $hsfArray[$i]["hsf_id"]. " :". $message . "=== <br /> \n";
        }
    }
}

function _printTen($job,$logArray, $hsfArray, $message){
    date_default_timezone_set('Africa/Lagos');
    $current_time = date('Y-m-d H:i:s.u');
    if(count($hsfArray) > 20){
        $hsfArray = array_values($hsfArray);
        date_default_timezone_set('Africa/Lagos');
        $current_time = date('Y-m-d H:i:s.u');    
        for($i = 10; $i < 20; $i++){
            echo("[{$job}] [{$current_time}]" . "===". $hsfArray[$i]. " :". $message . "=== <br /> \n");
            // $logArray[] = "[{$job}] [{$current_time}]" . "===". $hsfArray[$i]. " :". $message . "=== <br /> \n";
        }
    }
}
