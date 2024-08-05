<?php
require_once(__DIR__ ."/../controllers/__calculations.php");
require_once(__DIR__.'/../controllers/__data_helpers.php');

class ClearanceTgs{
    public static function selectMany($conn, $ik_array){
        $needed_columns_arr = ['ik_number', 'crop', 'season'];
        $cols_string = join(',', $needed_columns_arr);
        $placeholders = join(',', array_fill(0,count($ik_array),'?'));
        try{
            $stmt = $conn->prepare("SELECT {$cols_string} FROM clearance_tgs WHERE ik_number IN ({$placeholders})");
            $stmt->execute(array_values($ik_array));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
            }
            catch(Exception $err){
                throw new Exception("Ooops!!! Looks like we couldn't fetch records from clearance_tgs ". $err->getMessage());
            }

    }
}
 ?>