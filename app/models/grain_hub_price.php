<?php
require_once(__DIR__ . "/../../app/controllers/__array_key_concat.php");

class GrainHubPrice
{

    private ?PDO $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function get_data(): array
    {
        $query = "SELECT * FROM public.cc_hub_to_grain_price";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[get_key_concat($data["grain"], $data["hub"])] = $data;
        }

        return $result;
    }

    private function getUniqueMemberId($record)
    {
        $unique_member_id = $record["unique_member_id"];
        return "'$unique_member_id'";
    }
}