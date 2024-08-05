<?php

class CheckingRecords
{
    public static function getAllRecords($con)
    {
        try {
            $stmt = $con->prepare("SELECT * FROM harvest_checking_record");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception("Ooops!!! Looks like we couldn't fetch all checking records - " . $err->getMessage());
        }
    }


    public static function getFlaggedRecords($con)
    {
        try {
            $stmt = $con->prepare("SELECT * FROM harvest_checking_record WHERE verfifier_flag = 1");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception("Ooops!!! Looks like we couldn't fetch all flagged checking records - " . $err->getMessage());
        }
    }

        /* getDualRecords : fetches all records on checking and clearing officer records but not on cleared records
         * @param $con : database connection object
         * returns - an associatic=ve array of hsf records
         */
    public static function getDualRecords($con)
    {
        try {
            // $hsf_columns =
            //     'a.hsf_id, a.hub, a.unique_member_id,a.ik_number, a.empty_bag_weight,a.product_type,a.variety,a.verifier_flag,a.moldy_grains_count, a.collection_center_id,
            //  a.total_weight,a.individual_weight,a.bags_marketed,a.moisture_percentage,a.cleanliness_percentage, b.unique_member_id, 
            // b.total_weight,b.individual_weight, b.empty_bag_weight,b.bags_marketed,b.comment, b.verifier_flag, b.moisture_percentage,
            // b.cleanliness_percentage,b.moldy_grains_count,c.transportation_flag,c.threshing_flag';

            $hsf_columns =
                'a.hsf_id, a.unique_member_id,a.ik_number, a.empty_bag_weight,a.product_type,a.variety,a.verifier_flag,a.moldy_grains_count, a.collection_center_id,
             a.total_weight,a.individual_weight,a.bags_marketed,a.moisture_percentage,a.cleanliness_percentage, b.unique_member_id, b.hub_id, b.hub_name,
            b.total_weight,a.average_weight,b.average_weight, b.empty_bag_weight,b.bags_marketed,b.comment, b.verifier_flag, b.moisture_percentage,
            b.cleanliness_percentage,b.moldy_grains_count,b.imei,b.app_version';

            // $query = "SELECT {$hsf_columns} FROM harvest_scaling_record a 
            //           JOIN harvest_checking_record b USING(hsf_id) JOIN harvest_receiving_record c USING (hsf_id)
            //           WHERE a.hsf_id NOT IN (SELECT hsf_id FROM harvest_cleared_record)";

            $query = "SELECT {$hsf_columns} FROM harvest_scaling_record a 
                      JOIN harvest_checking_record b USING(hsf_id)
                      WHERE a.hsf_id NOT IN (SELECT hsf_id FROM harvest_cleared_record)";

            $stmt = $con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_NAMED);
        } catch (Exception $err) {
            throw new Exception("Ooops!!! Looks like we couldn't fetch all pending dual records - " . $err->getMessage());
        }
    }

        /* updateMany : updates specified columns for many records on the checking records table
         * @param $con : database connection object
         * @param $hsf_array : an array of HSF ids
         * @param $update_pairs : an associative array of column names and values to be updated to
         * returns - null
         */
    public static function updateMany($con, $hsf_array, $update_pairs ){
        $hsf_ids = array_column($hsf_array, 'hsf_id');
        $update_string = '';

        try{
        // build up the update string
        foreach ($update_pairs as $key => $value) {
            $update_string .= "{$key}='{$value}',";
            }
        $chunks = array_chunk($hsf_ids, 65000);
        foreach($chunks as $eachChunk){
        $placeholder_string = join(",", array_fill(0,count($eachChunk), "?"));
        $query = "UPDATE harvest_checking_record SET ${update_string} updated_at = NOW() WHERE hsf_id IN ({$placeholder_string})";
        $stmt = $con->prepare($query);
        $stmt->execute(array_values($eachChunk));
        }
       }
    catch(Exception $err){
        throw new Exception("Ooops!!! Looks like we couldn't update all checker flags - ". $err->getMessage());
                }
    }
}

?>