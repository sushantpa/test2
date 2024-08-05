<?php

class ScalingRecords{
    public static function getAllRecords($con){
        try{
            $stmt = $con->prepare("SELECT * FROM harvest_scaling_record");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldnt fetch all scaling records - ". $err->getMessage());
        }
    }
    
    public static function getFlaggedRecords($con){
        try{
            $stmt = $con->prepare("SELECT * FROM harvest_scaling_record WHERE verfifier_flag = 1");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $err){
            throw new Exception("Ooops!!! Looks like we couldnt fetch all flagged scaling records - ". $err->getMessage());
        }
    }

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
        $query = "UPDATE harvest_scaling_record SET ${update_string} updated_at = NOW() WHERE hsf_id IN ({$placeholder_string})";
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