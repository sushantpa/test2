<?php
require_once(__DIR__ ."/../controllers/__calculations.php");
require_once(__DIR__ ."/../controllers/__data_helpers.php");

class TGPayments{
    public static function selectMany($con, $ik_arr){
        try{
            $placeholder_string = join(",", array_fill(0,count($ik_arr), "?"));
            $query = "SELECT * FROM harvest_trust_group_payments WHERE updated_flag = 0 OR ik_number IN ({$placeholder_string})";
            $stmt = $con->prepare($query);
            $stmt->execute(array_values($ik_arr));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

            }
        
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't fetch all cleared records - ". $err->getMessage());
        }
    }

    public static function updateOne($con, $ik_number, $update_pairs ){
        $update_string = '';
        try{
        // build up the update string
        foreach ($update_pairs as $key => $value) {
            $update_string .= "{$key} ='{$value}',";
            }
        $stmt = $con->prepare("UPDATE harvest_trust_group_payments SET ${update_string} updated_at = NOW() WHERE ik_number = ?");
        $stmt->execute([$ik_number]);
        }
    catch(Exception $err){
        throw new Exception("Ooops!!! Looks like we couldn't update all checker flags - ". $err->getMessage());
                }
    }

    public static function updateMany($con, $ik_num_array, $update_pairs ){
        $ik_numbers = array_column($ik_num_array, 'ik_number');
        $update_string = '';
        try{
        // build up the update string
        foreach ($update_pairs as $key => $value) {
            $update_string .= "{$key} ='{$value}',";
            }
        $chunks = array_chunk($ik_numbers, 65000);
        foreach($chunks as $eachChunk){
        $placeholder_string = join(",", array_fill(0,count($eachChunk), "?"));
        $query = "UPDATE harvest_trust_group_payments SET ${update_string} updated_at = NOW() WHERE ik_number IN ({$placeholder_string})";
        $stmt = $con->prepare($query);
        $stmt->execute(array_values($eachChunk));
        }
       }
    catch(Exception $err){
        throw new Exception("Ooops!!! Looks like we couldn't update all checker flags - ". $err->getMessage());
                }
    }

        /* insertMany : inserts records into cleared records table using prepared statements and parameters
         * @param $con : database connection object
         * @param $insertArray : an array of HSF records
         * @param $length : specifies how many records to be inserted at once
         * returns - null
         */
    public static function insertMany($con, $insertArray, $length){
        $tg_payments_cols_arr = [
            'ik_number','product','season','id_package','id_loan_size','no_of_bags_marketed','shp_dnp','shp_dnp_override',
            'finance_dnp','contractual_flag','financial_default','max_payout_flag','max_payout_size_flag','override_max_payout',
            'failed_expectation','override_failed_expectation','total_harvest_advance','net_harvest_advance','payment_ready',
            'updated_flag','created_at','updated_at'
           ];
        
        $duplicate_string = generateDuplicateStringPG($tg_payments_cols_arr, ['ik_number','product','season']);

        $tg_payments_cols_str = join(",", $tg_payments_cols_arr);

        try{
            $chunks = array_chunk($insertArray, $length );
            foreach($chunks as $eachChunk){
                $placeholder_array = [];
                for($i=0; $i < count($eachChunk); $i++){
                    $placeholder_array[] = "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                }
                $placeholder_string = join(",", $placeholder_array);
                $query = "INSERT INTO harvest_trust_group_payments ({$tg_payments_cols_str}) VALUES {$placeholder_string} ON CONFLICT(ik_number,product,season) DO UPDATE SET {$duplicate_string}";                $stmt = $con->prepare($query);
                $oneMultiInsertArray = [];
                foreach($eachChunk as $eachRecord){
                    $oneMultiInsertArray[] = $eachRecord["ik_number"]; //  ik_number
                    $oneMultiInsertArray[] = $eachRecord["product"]; // product
                    $oneMultiInsertArray[] = $eachRecord["season"]; // season
                    $oneMultiInsertArray[] = $eachRecord["id_package"];// id_package
                    $oneMultiInsertArray[] = $eachRecord["id_loan_size"] ?? 'loan'; // id_loan_size
                    $oneMultiInsertArray[] = $eachRecord["no_of_bags_marketed"] ?? 0; // no_of_bags_marketed
                    $oneMultiInsertArray[] = $eachRecord["shp_dnp"] ?? 0; // shp_dnp
                    $oneMultiInsertArray[] = $eachRecord["shp_dnp_override"] ?? 0; //shp_dnp_override
                    $oneMultiInsertArray[] = $eachRecord["financial_dnp"] ?? 0; // finance_dnp
                    $oneMultiInsertArray[] = $eachRecord["contractual_flag"] ?? 0; // contractual_flag
                    $oneMultiInsertArray[] = $eachRecord["financial_default"] ?? 0; // financial_default
                    $oneMultiInsertArray[] = $eachRecord["max_payout_flag"] ?? 0;// max_payout_flag
                    $oneMultiInsertArray[] = $eachRecord["max_payout_size_flag"] ?? 0; // max_payout_size_flag
                    $oneMultiInsertArray[] = $eachRecord["override_max_payout"] ?? 0; // override_max_payout
                    $oneMultiInsertArray[] = $eachRecord["failed_expectation"] ?? 0; // failed_expectation;
                    $oneMultiInsertArray[] = $eachRecord["override_failed_expectation"] ?? 0; // override_failed_expectation
                    $oneMultiInsertArray[] = $eachRecord["total_harvest_advance"] ?? 0; // total_harvest_advance
                    $oneMultiInsertArray[] = $eachRecord["net_harvest_advance"] ?? 0; // net_harvest_advance
                    $oneMultiInsertArray[] = $eachRecord["payment_ready"] ?? 0; // payment_ready;
                    $oneMultiInsertArray[] = $eachRecord["updated_flag"] ?? 0; // updated_flag;
                    $oneMultiInsertArray[] = $eachRecord["created_at"] ?? date('Y-m-d H:i:s'); // created_at;
                    $oneMultiInsertArray[] = date('Y-m-d H:i:s'); // updated_at
                }
                $stmt->execute($oneMultiInsertArray);
            }
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't insert all harvest TG records - ". $err->getMessage());
        }
    }}

?>