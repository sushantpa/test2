<?php

class CcFarmLoan
{

    private ?PDO $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function get_data($harvest_members): array
    {
        $member_ids  = array_map(array($this, "getUniqueMemberId"), $harvest_members);
        $member_ids_str = join( ",", $member_ids);

        $query = "SELECT * FROM public.cc_farm_loans where unique_member_id in (". $member_ids_str .")";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[get_key_concat($data["unique_member_id"], $data["product"])] = $data;
        }

        return $result;
    }

    private function getUniqueMemberId($record)
    {
        $unique_member_id = $record["unique_member_id"];
        return "'$unique_member_id'";
    }
}