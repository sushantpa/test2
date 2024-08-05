<?php

require_once(__DIR__ . "/../../models/ClearedData.php");

class ClearedDbOperations
{

    private ?PDO $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function get_data(): array
    {
        $query = "select * from public.harvest_cleared_record where update_flag = 0";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();
        while ($data = $stmt->fetchObject('ClearedData')) {
            $result[$data->getHsfId()] = $data;
        }
        return $result;
    }

    public function get_data_all($harvest_members): array
    {
        $member_ids  = array_map(array($this, "getUniqueMemberId"), $harvest_members);
        $member_ids_str = join( ",", $member_ids);

        $query = "select * from public.harvest_cleared_record where unique_member_id in (". $member_ids_str .")";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$data["hsf_id"]] = $data;
        }
        return $result;
    }

    public function update_data(ClearedData $clearedData): bool
    {
        $query = "UPDATE public.harvest_cleared_record SET prorated_total_weight = {$clearedData->getProratedTotalweight()}, net_weight = {$clearedData->getNetWeight()},
                                         average_weight = {$clearedData->getAverageWeight()}, empty_bag_weight = {$clearedData->getEmptyBagweight()},
                                         transporter_cost = {$clearedData->getTransporterCost()}, threshing_cost = {$clearedData->getThreshingCost()},
                                         cc_processing_cost = {$clearedData->getCcProcessingcost()}, costs = {$clearedData->getCosts()},
                                         update_flag = 1, updated_at = NOW() WHERE hsf_id = '{$clearedData->getHsfId()}'";

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

    public function flag_records_to_verifier($records, $chunk_size): bool
    {
        $hsfIds = array_map(array($this, "getHsfId"), $records);
        try {
            $chunks = array_chunk($hsfIds, $chunk_size);
            foreach ($chunks as $eachChunk) {
                $hsfs = join(",", $eachChunk);
                $query = "UPDATE public.harvest_checking_record set verifier_flag = 1 WHERE hsf_id in ({$hsfs})";
                // echo $query;
                $stmt = $this->connection->prepare($query);
                $stmt->execute();
            }
        } catch (Exception $err) {
            throw new Exception("Ooops!!! Looks like we couldn't delete all cleared records - " . $err->getMessage());
        }
        return true;
    }

    public function delete_records($records, $chunk_size): bool
    {
        $hsfIds = array_map(array($this, "getHsfId"), $records);

        try {
            $chunks = array_chunk($hsfIds, $chunk_size);
            foreach ($chunks as $eachChunk) {
                $hsfs = join(",", $eachChunk);
                $query = "DELETE FROM public.harvest_cleared_record WHERE hsf_id in ({$hsfs})";
                // echo $query;
                $stmt = $this->connection->prepare($query);
                $stmt->execute();
            }
        } catch (Exception $err) {
            throw new Exception("Ooops!!! Looks like we couldn't delete all cleared records - " . $err->getMessage());
        }
        return true;
    }

    private function getHsfId(ClearedData $record)
    {
        $hsf_id = $record->getHsfId();
        return "'$hsf_id'";
    }

    private function getUniqueMemberId($record)
    {
        $unique_member_id = $record["unique_member_id"];
        return "'$unique_member_id'";
    }
}