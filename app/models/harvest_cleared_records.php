<?php
require_once(__DIR__ ."/../controllers/__calculations.php");

class ClearedRecords{
    public static function getAllRecords($con){
        try{
            $stmt = $con->prepare("SELECT * FROM harvest_cleared_record");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't fetch all cleared records - ". $err->getMessage());
        }
    }

    public static function getFlaggedRecords($con){
        try{
            $stmt = $con->prepare("SELECT * FROM harvest_cleared_record WHERE verifier_flag = 1");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't fetch all flagged cleared records - ". $err->getMessage());
        }
    }
        /* insertMany : inserts records into cleared records table using prepared statements and parameters
         * @param $con : database connection object
         * @param $insertArray : an array of HSF records
         * @param $length : specifies how many records to be inserted at once
         * returns - null
         */
    public static function insertMany($con, $insertArray, $length,$config){
        $cleared_record_cols_arr = [
            "hsf_id","collection_center_id","hub_id", "hub_name","new_hsf","verifier_id","unique_member_id","ik_number","empty_bag_weight",
            "total_weight","prorated_total_weight","net_weight","product_type","variety","thresher_id","threshing_date",
            "threshing_cost","transporter_id","transporter_cost","cc_processing_cost","costs","bags_marketed",
            "moldy_grains_count_flag","verifier_flag","verifier_comment", "voucher_edit_comment","cleared_flag","created_at",
            "updated_at","transaction_date","moisture_percentage", "cleanliness_percentage", "moldy_grains_count"
            ,"average_weight","imei","app_version"
        ];
        $duplicate_string = generateDuplicateStringPG($cleared_record_cols_arr, ['hsf_id']);


        $cleared_record_cols_str = join(",", $cleared_record_cols_arr);

        try{
            $chunks = array_chunk($insertArray, $length );
            foreach($chunks as $eachChunk){
                $placeholder_array = [];
                for($i=0; $i < count($eachChunk); $i++){
                    $placeholder_array[] = "(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                }
                $placeholder_string = join(",", $placeholder_array);
                $query = "INSERT INTO harvest_cleared_record ({$cleared_record_cols_str}) VALUES {$placeholder_string} ON CONFLICT(hsf_id) DO UPDATE SET {$duplicate_string}
                ";
                $stmt = $con->prepare($query);
    
                $oneMultiInsertArray = [];
                foreach($eachChunk as $eachRecord){
                    $oneMultiInsertArray[] = $eachRecord["hsf_id"]; //  hsf_id
                    $oneMultiInsertArray[] = $eachRecord["collection_center_id"]; // collection_center_id
                    $oneMultiInsertArray[] = $eachRecord["hub_id"]; // hub id
                    $oneMultiInsertArray[] = $eachRecord["hub_name"]; // hub name
                    $oneMultiInsertArray[] = 1; // new_hsf
                    $oneMultiInsertArray[] = null; // verifier_id
                    $oneMultiInsertArray[] = $eachRecord["unique_member_id"][1]; // unique_member_id
                    $oneMultiInsertArray[] = $eachRecord["ik_number"]; //ik_number
                    $oneMultiInsertArray[] = calculate_weight_of_empty_bag($eachRecord["bags_marketed"][1]); // empty_bag_weight
                    $oneMultiInsertArray[] = $eachRecord["total_weight"][1]; // total_weight
                    $oneMultiInsertArray[] = getProratedWeight($eachRecord); // protated_weight
                    $oneMultiInsertArray[] =  getNetWeight($eachRecord);// net weight
                    $oneMultiInsertArray[] = $eachRecord["product_type"]; // product_type
                    $oneMultiInsertArray[] = $eachRecord["variety"]; // variety
                    $oneMultiInsertArray[] = 'null'; // $eachRecord["thresher_id"][1];
                    $oneMultiInsertArray[] = 'null'; // $eachRecord["threshing_date"][1];
                    $oneMultiInsertArray[] = 0; // threshing_cost
                    $oneMultiInsertArray[] = 'null'; // $transporter_id;
                    $oneMultiInsertArray[] = 0; // $transporter_cost;
                    $oneMultiInsertArray[] = 0; // $cc_processing_cost;
                    $oneMultiInsertArray[] = 0; // costs;
                    $oneMultiInsertArray[] = $eachRecord["bags_marketed"][1]; // bags_marketed
                    $oneMultiInsertArray[] = 0; // moldy_grains_count_flag
                    // $oneMultiInsertArray[] = $eachRecord["moldy_grains_count_flag"][1]; // moldy_grains_count
                    $oneMultiInsertArray[] = $eachRecord["verifier_flag"][1]; // verifier_flag
                    $oneMultiInsertArray[] = ''; // verifier_comment
                    $oneMultiInsertArray[] = null; // voucher_edit_comment;
                    $oneMultiInsertArray[] = 1; // cleared_flag;
                    $oneMultiInsertArray[] = date("Y-m-d H:i:s"); // created_at;
                    $oneMultiInsertArray[] = date("Y-m-d H:i:s"); // updated_at;
                    $oneMultiInsertArray[] = date("Y-m-d H:i:s"); // transaction_date;
                    $oneMultiInsertArray[] = $eachRecord["moisture_percentage"][1]; // moisture_percentage
                    $oneMultiInsertArray[] = $eachRecord["cleanliness_percentage"][1]; // cleanliness_percentage
                    $oneMultiInsertArray[] = $eachRecord["moldy_grains_count"][1]; // moldy_grains_count;
                    $oneMultiInsertArray[] = $eachRecord["average_weight"][1]; // average_weight;
                    $oneMultiInsertArray[] = $eachRecord["imei"]; // imei;
                    $oneMultiInsertArray[] = $eachRecord["app_version"]; // app_version;
                }
                $stmt->execute($oneMultiInsertArray);
            }
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldn't insert all cleared records - ". $err->getMessage());
        }
    }}

?>