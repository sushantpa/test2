<?php

/*getHSFId
    * extracts an HSF id from an assoc array of a single table record or row
    * args : $record - an associative array
    * returns null
*/
function getHSFId($record){
    return $record["hsf_id"];
} 

function getIkNumber($record){
    return $record['ik_number'];
}

function generateDuplicateString($arr){
    try{
        $duplicate_string = '';
        foreach($arr as $key=>$value){
           $duplicate_string .= $value."=VALUES(".$value.")";
           if($key < count($arr) - 1) $duplicate_string .= ',';
        }
        return $duplicate_string;
    }
    catch(Exception $e){
        throw new Exception("Error, could not generate duplicate String - ". $e->getMessage());
    }

}

function generateDuplicateStringIN($arr){
    try{
        $duplicate_string = '';
        $first_half ='(';
        $second_half = '(';

        foreach($arr as $key=>$value){
           $first_half  .= $value."=VALUES(".$value.")";
           if($key < count($arr) - 1) $duplicate_string .= ',';
        }
        return $duplicate_string;
    }
    catch(Exception $e){
        throw new Exception("Error, could not generate duplicate String - ". $e->getMessage());
    }

}

function generateDuplicateStringPG($arr, $excludedArr){
    try{
        $duplicate_string = '';
        $first_half ='(';
        $second_half = '(';

        foreach($arr as $key=>$value){
        if(in_array($value, $excludedArr)) continue;
           $first_half  .= $value;
           $second_half .= 'EXCLUDED.'.$value;

           if($key == count($arr) - 1){
                    $first_half  .= ')';
                    $second_half .= ')'; 
           }
           else{
                    $first_half  .= ',';
                    $second_half .= ',';
           }
        }
        $duplicate_string = $first_half. " = ". $second_half;
        return $duplicate_string;
    }
    catch(Exception $e){
        throw new Exception("Error, could not generate duplicate String - ". $e->getMessage());
    }
}

function onDNPList($dnp_arr, $record ){
    try{
       return in_array($record['ik_number'], $dnp_arr) ;
    }
    catch(Exception $e){
        throw new Exception($e->getMessage());
    }  
}