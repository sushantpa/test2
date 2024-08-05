<?php

require_once(__DIR__ . "/../../models/ReceivingRecord.php");

class ReceivingDbOperations
{

    private ?PDO $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function get_data(): array
    {
        $query = "select hsf_id, transportation_flag, threshing_flag, total_field_size_threshed, bags_received from public.harvest_receiving_record";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();
        while ($data = $stmt->fetchObject('ReceivingRecord')) {
            $result[$data->getHsfId()] = $data;
        }
        return $result;
    }
}