<?php

class ClearanceMembers
{

    private ?PDO $connection;

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function get_data($last_run): array
    {
        $query = "SELECT unique_member_id, ik_number, first_name, last_name, staff_id, msb_id, clearance_staff_id, clearance_field_size, latitude, longitude, location_id, clearance_date, app_version, date_updated, location_id_ptg FROM public.clearance_members where date_updated >= '{$last_run}' and clearance_field_size > '0' ";

        $stmt = $this->connection->prepare($query);

        $stmt->execute();

        $result = array();

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$data["unique_member_id"]] = $data;
        }

        return $result;
    }
}