<?php

require_once("app/models/ClearedData.php");

class ConfigDbOperations
{

    private ?PDO $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function get_data()
    {

        $stmt = $this->connection->prepare("select * from public.collection_center_config");
        $stmt->execute();
        $allConfigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = new stdClass();
        foreach($allConfigs as $eachConfig)
        {
            $result->{$eachConfig['config_id']} = $eachConfig['value'];
        }
        return $result;
    }
}