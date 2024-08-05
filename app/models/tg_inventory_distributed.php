<?php
require_once(__DIR__ ."/../controllers/__calculations.php");
require_once(__DIR__.'/../controllers/__data_helpers.php');

class TgInventory{
    
    public static function getTgs($con){
        try{
        $stmt = $con->prepare("SELECT ik_number, MAX(package_name) AS package_name FROM tgetg_inventory_distributed_entity GROUP BY ik_number");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't fetch records from tg inventorydistributed- ". $err->getMessage());
        }
    }
}

?>