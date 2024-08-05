<?php
require_once(__DIR__ ."/../controllers/__calculations.php");
require_once(__DIR__ ."/../controllers/__data_helpers.php");

class MemberPayments{
    public static function selectMany($con){
        try{
            $stmt = $con->prepare("SELECT * FROM harvest_member_payments");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't fetch all member records - ". $err->getMessage());
        }
    }

    public static function selectGrouped($con){
        try{
            $query = "WITH pending AS (SELECT * FROM harvest_member_payments WHERE transferred_flag = 0) 
                      SELECT ik_number, ik_number, SUM(field_size) AS field_size, SUM(no_of_bags_marketed) AS bags, COUNT(1) AS members_count,
                         MAX(contractual_flag) AS contractual, SUM(harvest_advance) AS harvest_advance,
                          SUM(net_harvest_advance) AS net_harvest_advance
                           FROM pending GROUP BY ik_number,season";
            $stmt = $con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_UNIQUE);
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't fetch all member records - ". $err->getMessage());
        }
    }

    public static function updateMany($con, $ik_num_array, $update_pairs ){
        $ik_numbers = $ik_num_array;
        $update_string = '';
        try{
        // build up the update string
        foreach ($update_pairs as $key => $value) {
            $update_string .= "{$key} ='{$value}',";
            }
        $chunks = array_chunk($ik_numbers, 65000);
        foreach($chunks as $eachChunk){
        $placeholder_string = join(",", array_fill(0,count($eachChunk), "?"));
        $query = "UPDATE harvest_member_payments SET ${update_string} updated_at = NOW() WHERE ik_number IN ({$placeholder_string})";
        $stmt = $con->prepare($query);
        $stmt->execute(array_values($eachChunk));
        }
       }
    catch(Exception $err){
        throw new Exception("Ooops!!! Looks like we couldn't update all member records - ". $err->getMessage());
                }
    }

        /* insertMany : inserts records into harvest member repaymentscords table using prepared statements and parameters
         * @param $con : database connection object
         * @param $insertArray : an array of member records
         * @param $length : specifies how many records to be inserted at once
         * returns - null
         */
    public static function insertMany($con, $insertArray, $length){
        $member_payments_cols_arr = [
            'unique_member_id', 'ik_number', 'product','season','field_size','member_status','percentage_ownership','no_of_bags_marketed','net_weight_marketed',
            'grain_value','failed_expectation','harvest_advance','loan_before_harvest','net_harvest_advance','misc_account',
            'threshing_cost','transport_cost','processing_cost','total_cost','shared_debt', 'payment_ready_date',
            'updated_flag', 'contractual_flag', 'created_at','updated_at'
           ];
        
        $duplicate_string = generateDuplicateStringPG($member_payments_cols_arr, ['unique_member_id','product','season']);

        $member_payments_cols_str = join(",", $member_payments_cols_arr);

        try{
            $chunks = array_chunk($insertArray, $length );
            foreach($chunks as $eachChunk){
                $placeholder_array = [];
                for($i=0; $i < count($eachChunk); $i++){
                    $placeholder_array[] = "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                }
                $placeholder_string = join(",", $placeholder_array);
                $query = "INSERT INTO harvest_member_payments ({$tg_payments_cols_str}) VALUES {$placeholder_string} ON CONFLICT(ik_number,product,season) DO UPDATE SET {$duplicate_string}";                $stmt = $con->prepare($query);
                $oneMultiInsertArray = [];
                foreach($eachChunk as $eachRecord){
                }
                $stmt->execute($oneMultiInsertArray);
            }
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't insert all harvest TG records - ". $err->getMessage());
        }
    }}

?>