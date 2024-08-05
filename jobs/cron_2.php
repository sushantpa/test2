<?php
require_once(__DIR__ . "/../app/models/harvest_member_payment.php");
require_once(__DIR__ . "/../app/models/clearance_members.php");
require_once(__DIR__ . "/../app/controllers/Logger.php");
require_once(__DIR__ . "/../app/controllers/_group_by.php");
require_once(__DIR__ . "/../app/db/cleared/ClearedDbOperations.php");
require_once(__DIR__ . "/../app/models/cc_farm_loan.php");
require_once(__DIR__ . "/../app/controllers/__fetch_sum.php");
require_once(__DIR__ . "/../app/controllers/__array_key_concat.php");
require_once(__DIR__ . "/../app/models/grain_hub_price.php");
require_once(__DIR__ . "/../app/controllers/_get_or_default.php");


class Cron2
{
    private HarvestMemberPayment $harvestMemberPayment;
    private ClearedDbOperations $clearedDbOperations;
    private GrainHubPrice $grainHubPrice;


    private array $logs;
    private CcFarmLoan $ccFarmLoan;

    function __construct($trans_pg_con, $pg_con)
    {
        $this->harvestMemberPayment = new HarvestMemberPayment($trans_pg_con);
        $this->clearedDbOperations = new ClearedDbOperations($pg_con);
        $this->grainHubPrice = new GrainHubPrice($trans_pg_con);
        $this->ccFarmLoan = new CcFarmLoan($trans_pg_con);

        $this->logs = array();
    }

    function startCron($config)
    {
        $startTime = microtime(true);

        try {
            $this->logs[] = $this->getLogString("Fetching Data from harvest_members_payment table");
            $harvest_members = $this->harvestMemberPayment->get_data($config->season_config);
            $this->logs[] = $this->getLogString(count($harvest_members) . " data fetched successfully");

            $this->logs[] = $this->getLogString("Fetching Data from cleared records table");
            $cleared_data = $this->clearedDbOperations->get_data_all($harvest_members);
            $this->logs[] = $this->getLogString(count($cleared_data) . " data fetched successfully");

            $this->logs[] = $this->getLogString("Fetching Grain Prices from grain_price_hub table");
            $grain_prices = $this->grainHubPrice->get_data();
            $this->logs[] = $this->getLogString(count($grain_prices) . " data fetched successfully");

            $this->logs[] = $this->getLogString("Fetching Data from cc_farm_loan table");
            $cc_farm_loan = $this->ccFarmLoan->get_data($harvest_members);
            $this->logs[] = $this->getLogString(count($cc_farm_loan) . " data fetched successfully");

            $this->logs[] = $this->getLogString("Grouping data based on unique_member_id and product_type");
            $grouped_data = _group_by_two($cleared_data, "unique_member_id", "product_type");
            $this->logs[] = $this->getLogString(count($grouped_data) . " data grouped.");

            $members_to_update = array();

            foreach ($grouped_data as $member) {
                $unique_member_id = $member[0]["unique_member_id"];
                $product = $member[0]["product_type"];
                $hub_name = $member[0]["hub_name"];

                $this->logs[] = $this->getLogString("Computing Data for Unique Member ID {$unique_member_id} and Product Type {$product}");


                list($processing_cost, $threshing_cost, $transporter_cost, $costs, $bags_marketed, $net_weight, $total_weight) =
                    __fetch_multiple_sum($member, [
                        "cc_processing_cost",
                        "threshing_cost",
                        "transporter_cost",
                        "costs",
                        "bags_marketed",
                        "net_weight",
                        "total_weight"
                    ]);

                if (isset($harvest_members[get_key_concat($unique_member_id, $product)])) {
                    $cc_payment_member = $harvest_members[get_key_concat($unique_member_id, $product)];

                    $cc_payment_member["no_of_bags_marketed"] = $bags_marketed;
                    $cc_payment_member["net_weight_marketed"] = $net_weight;
                    $cc_payment_member["threshing_cost"] = $threshing_cost;
                    $cc_payment_member["transport_cost"] = $transporter_cost;
                    $cc_payment_member["processing_cost"] = $processing_cost;
                    $cc_payment_member["total_cost"] = $costs;
                    $cc_payment_member["updated_flag"] = 1;
                    $cc_payment_member["misc_account"] = get_or_default($cc_farm_loan, get_key_concat($unique_member_id, $product), array())["misc_account"] ?? 0;
                    $cc_payment_member["loan_before_harvest"] = get_or_default($cc_farm_loan, get_key_concat($unique_member_id, $product), array())["loan_before_harvest"] ?? 0;

                    $this->logs[] = $this->getLogString("Total computed data:");

                    $this->logs[] = $this->getLogString("Bags marketed: {$bags_marketed}");
                    $this->logs[] = $this->getLogString("Net Weight marketed: {$net_weight}");
                    $this->logs[] = $this->getLogString("Threshing cost: {$threshing_cost}");
                    $this->logs[] = $this->getLogString("Transporter cost: {$transporter_cost}");
                    $this->logs[] = $this->getLogString("Processing cost: {$processing_cost}");
                    $this->logs[] = $this->getLogString("Misc Amount: {$cc_payment_member["misc_account"]}");
                    $this->logs[] = $this->getLogString("Loan before harvest: {$cc_payment_member["loan_before_harvest"]}");

                    $contractual_threshold = $cc_payment_member["no_of_bags_marketed"] / $cc_payment_member["field_size"];

                    if ($contractual_threshold != $config->contractual_threshold_config) {
                        $cc_payment_member["contractual_flag"] = 1;
                    }

                    if (isset($grain_prices[get_key_concat($product, $hub_name)])) {
                        $price_per_kg = $grain_prices[get_key_concat($product, $hub_name)]["price"];
                        $value_of_grain = $price_per_kg * $net_weight;

                        $cc_payment_member["grain_value"] = $value_of_grain;

                        $this->logs[] = $this->getLogString("Grain price per kg: {$price_per_kg}");

                        $this->logs[] = $this->getLogString("Value of grain: {$value_of_grain}");

                        $harvest_advance = $value_of_grain - ($cc_payment_member["loan_before_harvest"] + $costs ) + $cc_payment_member["misc_account"];

                        $cc_payment_member["harvest_advance"] = $harvest_advance;

                        $members_to_update[] = $cc_payment_member;
                    } else {
                        $this->logs[] = $this->getLogString("Grain Price not found for Grain: {$product} and Hub: {$hub_name}");
                    }
                } else {
                    $this->logs[] = $this->getLogString("Member with product type: {$product} not on harvest member payment");
                }


            }


            $this->logs[] = $this->getLogString("Updating " . count($members_to_update) . " data into harvest members payment");

            $this->harvestMemberPayment->update_records($members_to_update);

            $this->logs[] = $this->getLogString(count($members_to_update) . " data has been updated in harvest members payment");

            $ik_numbers = array_map(array($this, "getIkNumber"), $members_to_update);

            if (count($ik_numbers) > 0){
                $harvest_iks = $this->harvestMemberPayment->get_harvest_advance($ik_numbers);

                $grouped_data = $this->group_harvest_advance($harvest_iks);

                $this->logs[] = $this->getLogString("Computing harvest advance for updated data");

                $harvest_advance_update = array();
                foreach ($members_to_update as $member) {
                    $key = $member["ik_number"];
                    $harvest_advance = $member["harvest_advance"];
                    if ($harvest_advance > 0){
                        $percentage_ownership = round($harvest_advance / $grouped_data[$key]["positive"], 2);
                        $shared_debt = round($percentage_ownership * $grouped_data[$key]["negative"], 2);
                        $net_harvest_advance = round($harvest_advance + $shared_debt, 2);
                        $member["percentage_ownership"] = $percentage_ownership;
                        $member["shared_debt"] = $shared_debt;
                        $member["net_harvest_advance"] = $net_harvest_advance;
                        $this->logs[] = $this->getLogString($member["unique_member_id"] . " Percentage Ownership: {$percentage_ownership}");
                        $this->logs[] = $this->getLogString($member["unique_member_id"] . " Shared Debt: {$shared_debt}");
                        $this->logs[] = $this->getLogString($member["unique_member_id"] . " Net Harvest Advance: {$net_harvest_advance}");
                    } else {
                        $member["shared_debt"] = 0;
                        $member["net_harvest_advance"] = 0;
                        $member["percentage_ownership"] = 0;
                        $this->logs[] = $this->getLogString($member["unique_member_id"] . " has a negative harvest advance");
                    }
                    $harvest_advance_update[] = $member;

                }

                $this->logs[] = $this->getLogString("Updating " . count($harvest_advance_update) . " data into harvest members payment");

                $this->harvestMemberPayment->update_records($harvest_advance_update);

                $this->logs[] = $this->getLogString(count($harvest_advance_update) . " data has been updated in harvest members payment");
            }

        } catch (Exception $exception) {
            $this->logs[] = $this->getLogString("Error occurred: {$exception->getMessage()}");
        }

        $endTime = microtime(true);

        $timeElapsed = ($endTime - $startTime);
        $this->logs[] = $this->getLogString("Job ran in {$timeElapsed}s");

        return $this->logs;
    }

    private function getLogString($message): string
    {
        return getLogString("CRON 2", $message);
    }

    private function getIkNumber($record)
    {
        $ik_number = $record["ik_number"];
        return "'$ik_number'";
    }

    private function group_harvest_advance(array $harvest_iks): array
    {
        $grouped = array();

        foreach ($harvest_iks as $member) {
            $key = $member["ik_number"];
            if (!isset($grouped[$key])) {
                $grouped[$key]["positive"] = 0;
                $grouped[$key]["negative"] = 0;
            }
            $harvest_advance = $member["harvest_advance"];
            if ($harvest_advance < 0) {
                $grouped[$key]["negative"] += $harvest_advance;
            } else {
                $grouped[$key]["positive"] += $harvest_advance;
            }
        }

        return $grouped;
    }
}