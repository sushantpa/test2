<?php

class MSDoNotPay
{
    public static function selectMany($conn){
        $needed_columns_arr = ['ik_number'];
        $cols_string = join(',', $needed_columns_arr);
        try{
            // $stmt = $conn->prepare("SELECT {$cols_string} FROM ms_playbook_donotpay_table WHERE ik_number IN ({$placeholders})");
            $stmt = $conn->prepare("SELECT {$cols_string} FROM ms_playbook_donotpay_table");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_column($result, 'ik_number');
            }
            catch(Exception $err){
                throw new Exception("Ooops!!! Looks like we couldn't fetch records from clearance_tgs ". $err->getMessage());
            }
    }
}

?>