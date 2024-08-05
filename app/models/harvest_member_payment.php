<?php

require_once(__DIR__ . "/../../app/controllers/__array_key_concat.php");


class HarvestMemberPayment
{

    private ?PDO $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function get_data($season): array
    {
        $query = "select * from public.harvest_member_payments where updated_flag = 0 and season = '{$season}'";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[get_key_concat($data["unique_member_id"], $data["product"])] = $data;
        }
        return $result;
    }

    public function get_harvest_advance($ikNumbers): array
    {
        $ikNumbers_str = join(",", $ikNumbers);
        $query = "select * from public.harvest_member_payments where ik_number in ( {$ikNumbers_str} )";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $data;
        }
        return $result;
    }

    public function insert_many($records, $chunk_size): bool
    {
        $records_cols_arr = [
            "unique_member_id", "ik_number", "product", "season", "field_size", "member_status", "percentage_ownership",
            "no_of_bags_marketed", "net_weight_marketed", "grain_value", "failed_expectation",
            "harvest_advance", "loan_before_harvest", "net_harvest_advance",
            "misc_account", "threshing_cost", "transport_cost", "processing_cost", "total_cost", "shared_debt",
            "payment_ready_date", "updated_flag", "contractual_flag", "created_at", "updated_at",
        ];


        $record_cols_str = join(",", $records_cols_arr);

        try {
            $chunks = array_chunk($records, $chunk_size);
            foreach ($chunks as $eachChunk) {
                $placeholder_array = [];
                for ($i = 0; $i < count($eachChunk); $i++) {
                    $placeholder_array[] = "(" . "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?" . ")";
                }
                $placeholder_string = join(",", $placeholder_array);
                $query = "INSERT INTO public.harvest_member_payments ({$record_cols_str}) VALUES {$placeholder_string}";
                // echo $query;
                $stmt = $this->connection->prepare($query);

                $oneMultiInsertArray = [];

                foreach ($eachChunk as $data) {
                    $oneMultiInsertArray[] = $data["unique_member_id"];
                    $oneMultiInsertArray[] = $data["ik_number"];
                    $oneMultiInsertArray[] = $data["product"];
                    $oneMultiInsertArray[] = $data["season"];
                    $oneMultiInsertArray[] = $data["field_size"];
                    $oneMultiInsertArray[] = $data["member_status"];
                    $oneMultiInsertArray[] = $data["percentage_ownership"];
                    $oneMultiInsertArray[] = $data["no_of_bags_marketed"];
                    $oneMultiInsertArray[] = $data["net_weight_marketed"];
                    $oneMultiInsertArray[] = $data["grain_value"];
                    $oneMultiInsertArray[] = $data["failed_expectation"];
                    $oneMultiInsertArray[] = $data["harvest_advance"];
                    $oneMultiInsertArray[] = $data["loan_before_harvest"];
                    $oneMultiInsertArray[] = $data["net_harvest_advance"];
                    $oneMultiInsertArray[] = $data["misc_account"];
                    $oneMultiInsertArray[] = $data["threshing_cost"];
                    $oneMultiInsertArray[] = $data["transport_cost"];
                    $oneMultiInsertArray[] = $data["processing_cost"];
                    $oneMultiInsertArray[] = $data["total_cost"];
                    $oneMultiInsertArray[] = $data["shared_debt"];
                    $oneMultiInsertArray[] = $data["payment_ready_date"];
                    $oneMultiInsertArray[] = $data["updated_flag"];
                    $oneMultiInsertArray[] = $data["contractual_flag"];
                    $oneMultiInsertArray[] = date("Y-m-d H:i:s"); // created_at;
                    $oneMultiInsertArray[] = date("Y-m-d H:i:s"); // updated_at;
                }
                $stmt->execute($oneMultiInsertArray);
            }
        } catch (Exception $err) {
            throw new Exception("Ooops!!! Looks like we couldn't insert all cleared records - " . $err->getMessage());
        }
        return true;
    }

    public function update_data($data): bool
    {
        $query = "UPDATE public.harvest_member_payments SET no_of_bags_marketed = {$data["no_of_bags_marketed"]}, net_weight_marketed = {$data["net_weight_marketed"]},
                                         threshing_cost = {$data["threshing_cost"]}, transport_cost = {$data["transport_cost"]},
                                         processing_cost = {$data["processing_cost"]}, total_cost = {$data["total_cost"]}, contractual_flag = {$data["contractual_flag"]},
                                         misc_account = {$data["misc_account"]}, grain_value = {$data["grain_value"]}, shared_debt = {$data["shared_debt"]},
                                         loan_before_harvest = {$data["loan_before_harvest"]}, harvest_advance = {$data["harvest_advance"]},
                                         percentage_ownership = {$data["percentage_ownership"]}, net_harvest_advance = {$data["net_harvest_advance"]},
                                         updated_flag = {$data["updated_flag"]}, updated_at = NOW() WHERE unique_member_id = '{$data["unique_member_id"]}' 
                                                                               and season = '{$data["season"]}'
                                                                               and product = '{$data["product"]}'";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        return true;
    }

    public function update_records($records): bool
    {
        foreach ($records as $record) {
            $this->update_data($record);
        }
        return true;
    }

}