<?php

require_once(__DIR__ . "/../app/db/cleared/ClearedDbOperations.php");
require_once(__DIR__ . "/../app/db/config/ConfigDbOperations.php");
require_once(__DIR__ . "/../app/models/ClearedData.php");
require_once(__DIR__ . "/../app/controllers/Cast.php");
require_once(__DIR__ . "/../app/controllers/Logger.php");
require_once(__DIR__ . "/../app/models/ConfigModel.php");
require_once(__DIR__ . "/../app/controllers/__calculations.php");
require_once(__DIR__ . "/../app/db/receiving/ReceivingDbOperations.php");


class ValidateClearedTableJob
{
    private ClearedDbOperations $clearedDbOperations;
    private ReceivingDbOperations $receivingDbOperations;

    private array $logs;

    function __construct($pg_con)
    {
        $this->clearedDbOperations = new ClearedDbOperations($pg_con);
        $this->receivingDbOperations = new ReceivingDbOperations($pg_con);

        $this->logs = array();
    }

    function validate_cleared_data($config): array
    {
        echo  date('Y-m-d H:i:s.u')."=====Cron 1 Started==== <br /> \n";
        $startTime = microtime(true);
        try {
            $this->logs = array();
            $this->logs[] = $this->getLogString("Fetching Data from harvest cleared records table");
            $records = $this->clearedDbOperations->get_data();
            $this->logs[] = $this->getLogString(count($records) . " data fetched successfully");

            $receiving_records = $this->receivingDbOperations->get_data();
            $this->logs[] = $this->getLogString("Fetching Data from receiving records table");
            $this->logs[] = $this->getLogString(count($receiving_records) . " records fetched successfully");

            $flaggedRecords = array();
            $unFlaggedRecords = array();

            foreach ($records as $record) {
                $data = castClearedData($record);
                $this->logs[] = $this->getLogString("Checking HSF: {$data->getHsfId()}");

                $hasFlags = $this->check_quality_metric($data, $config);
                $this->compute_data($data);
                $this->compute_cost($data, $receiving_records, $config);
                $hasFlags = $this->check_average_weight($data, $config) || $hasFlags;

                if ($hasFlags) {
                    $this->logs[] = $this->getLogString("HSF: {$data->getHsfId()} has flags");
                    $flaggedRecords[] = $data;
                } else {
                    $unFlaggedRecords[] = $data;
                    $this->logs[] = $this->getLogString("HSF: {$data->getHsfId()} has no flags");
                }
                $this->logs[] = "<br>";
            }

            $this->logs[] = $this->getLogString("Flagged Records: " . count($flaggedRecords));
            $this->logs[] = $this->getLogString("UnFlagged Records: " . count($unFlaggedRecords));

            $this->logs[] = $this->getLogString("Flagging records to verifier");
            $this->clearedDbOperations->flag_records_to_verifier($flaggedRecords, 2000);

            $this->logs[] = $this->getLogString("Deleting Flagged Records from cleared table");
            $this->clearedDbOperations->delete_records($flaggedRecords, 2000);

            $this->logs[] = $this->getLogString("Updating data for unFlagged Records");
            $this->clearedDbOperations->update_records($unFlaggedRecords);

            $this->logs[] = $this->getLogString("End of job");

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
        return getLogString("CRON 1", $message);
    }

    private function check_quality_metric(ClearedData $data,  $config): bool
    {
        // $this->logs[] = $this->getLogString("Checking Quality Metric for HSF: {$data->getHsfId()}");

        $hasFlags = false;

        if ($data->getMoldyGrainscount() > 0) {
            $hasFlags = true;
            // $this->logs[] = $this->getLogString("Moldy grain count flagged for HSF: {$data->getHsfId()}");
            $data->setMoldyGrainscountflag(1);
        }

        if ($data->getCleanlinessPercentage() < $config->cleanliness_config) {
            // $this->logs[] = $this->getLogString("Cleanliness percentage flagged for HSF: {$data->getHsfId()}; Cleanliness percentage: {$data->getCleanlinessPercentage()}; Cleanliness config: $config->cleanliness_config;");
            $hasFlags = true;
        }

        if ($data->getMoisturePercentage() > $config->moisture_config) {
            // $this->logs[] = $this->getLogString("Moisture percentage flagged for HSF: {$data->getHsfId()}; Moisture percentage: {$data->getMoisturePercentage()}; Moisture config: {$config->moisture_config};");
            $hasFlags = true;
        }

        if (!$hasFlags) {
            $this->logs[] = $this->getLogString("No Flags for HSF: {$data->getHsfId()}");
        }

        return $hasFlags;
    }

    private function check_average_weight(ClearedData $data, $config): bool
    {
        // $this->logs[] = $this->getLogString("Checking Net Weight and Bags Marketed relationship for HSF: {$data->getHsfId()}");

        $hasFlags = false;

        try {
            if ($data->getBagsMarketed() == 0) {
                throw new Exception("Division by zero error");
            }
            $average_weight = $data->getNetWeight() / $data->getBagsMarketed();
            $data->setAverageWeight($this->round_num($average_weight));

            $min_config = 0;
            $max_config = 0;
            switch ($data->getProductType()) {
                case "maize":
                    $min_config = $config->average_weight_min_maize_config;
                    $max_config = $config->average_weight_max_maize_config;
                    break;
                case "soy":
                    $min_config = $config->average_weight_min_soy_config;
                    $max_config = $config->average_weight_max_soy_config;
                    break;
                case "rice":
                    $min_config = $config->average_weight_min_rice_config;
                    $max_config = $config->average_weight_max_rice_config;
                    break;
                default:
                    break;
            }

            if ($average_weight < $min_config ||
                $average_weight > $max_config) {
                // $this->logs[] = $this->getLogString("Net Weight and Bags Marketed relationship flagged for : {$data->getHsfId()}");
                $hasFlags = true;
            }
            // $this->logs[] = $this->getLogString("Net Weight for : {$data->getHsfId()} - {$data->getNetWeight()}");
            // $this->logs[] = $this->getLogString("Bags Marketed for : {$data->getHsfId()} - {$data->getBagsMarketed()}");
            // $this->logs[] = $this->getLogString("Average Weight for : {$data->getHsfId()} - {$average_weight}");
            // $this->logs[] = $this->getLogString("Average Weight Min Config - {$min_config}");
            // $this->logs[] = $this->getLogString("Average Weight Max Config - {$max_config}");
            // $this->logs[] = $this->getLogString("Product type - {$data->getProductType()}");


            if (!$hasFlags) {
                $this->logs[] = $this->getLogString("No Net Weight/Bags Marketed Flags for HSF: {$data->getHsfId()}");
            }
            return $hasFlags;
        } catch (Exception $exception) {
            $this->logs[] = $this->getLogString("An exception occurred: {$exception->getMessage()}");
            return true;
        }

    }

    private function round_num($num): float
    {
        return round($num, 2);
    }

    private function compute_data(ClearedData $data)
    {
        // $this->logs[] = $this->getLogString("Computing weight data for HSF: {$data->getHsfId()}");

        $prorated_weight = calculate_prorated_total_weight($data->getTotalWeight(), $data->getMoisturePercentage());
        $empty_bag = calculate_weight_of_empty_bag($data->getBagsMarketed());
        $net_weight_marketed = calculate_net_weight_marketed($empty_bag, $prorated_weight);
        $data->setProratedTotalweight($this->round_num($prorated_weight));
        $data->setEmptyBagweight($this->round_num($empty_bag));
        $data->setNetWeight($this->round_num($net_weight_marketed));
        // $this->logs[] = $this->getLogString("Prorated Total Weight for HSF: {$data->getHsfId()}; Prorated Total weight: {$data->getProratedTotalweight()}");
        // $this->logs[] = $this->getLogString("Empty Bag Weight for HSF: {$data->getHsfId()}; Empty Bag weight: {$data->getEmptyBagweight()}");
        // $this->logs[] = $this->getLogString("Net Weight for HSF: {$data->getHsfId()}; Net weight: {$data->getNetWeight()}");
    }

    private function compute_cost(ClearedData $data, $receiving_records, $config)
    {
        $transport_cost = $data->getTransporterCost();
        $threshing_cost = $data->getThreshingCost();

        if (isset($receiving_records[$data->getHsfId()])) {
            $receiving_data = $receiving_records[$data->getHsfId()];
            if($receiving_data->getTransportationFlag() == 1){
                $cost_per_bag = 0;
                switch ($data->getProductType()) {
                    case "maize":
                        $cost_per_bag = $config->transporting_maize_config;
                        break;
                    case "soy":
                        $cost_per_bag = $config->transporting_soy_config;
                        break;
                    case "rice":
                        $cost_per_bag = $config->transporting_rice_config;
                        break;
                    default:
                        break;
                }
                // $this->logs[] = $this->getLogString("Transport Cost per bag: ". $cost_per_bag . "; Total bag received:".$receiving_data->getBagsReceived());

                $transport_cost = calculate_cost($cost_per_bag, $receiving_data->getBagsReceived());
            }

            if($receiving_data->getThreshingFlag() == 1){
                $cost_per_hectare = 0;
                switch ($data->getProductType()) {
                    case "maize":
                        $cost_per_hectare = $config->threshing_maize_config;
                        break;
                    case "soy":
                        $cost_per_hectare = $config->threshing_soy_config;
                        break;
                    case "rice":
                        $cost_per_hectare = $config->threshing_rice_config;
                        break;
                    default:
                        break;
                }
                // $this->logs[] = $this->getLogString("Threshing Cost per hectare: ". $cost_per_hectare . "; Total field size:".$receiving_data->getTotalFieldSizeThreshed());

                $threshing_cost = calculate_cost($cost_per_hectare, $receiving_data->getTotalFieldSizeThreshed());
            }
        }

        $cost_per_bag = 0;
        switch ($data->getProductType()) {
            case "maize":
                $cost_per_bag = $config->processing_maize_config;
                break;
            case "soy":
                $cost_per_bag = $config->processing_soy_config;
                break;
            case "rice":
                $cost_per_bag = $config->processing_rice_config;
                break;
            default:
                break;
        }
        // $this->logs[] = $this->getLogString("Processing Cost per bag: ". $cost_per_bag. "; Total bag marketed:".$data->getBagsMarketed());

        $processing_cost = calculate_cost($cost_per_bag, $data->getBagsMarketed());
        $cost = $transport_cost + $threshing_cost + $processing_cost;

        // $this->logs[] = $this->getLogString("Transportation Cost: ". $transport_cost);
        // $this->logs[] = $this->getLogString("Threshing Cost: ". $threshing_cost);
        // $this->logs[] = $this->getLogString("Processing Cost: ". $processing_cost);

        // $this->logs[] = $this->getLogString("Total Cost: ". $cost);



        $data->setThreshingCost($threshing_cost);
        $data->setTransporterCost($transport_cost);
        $data->setCcProcessingcost($processing_cost);
        $data->setCosts($cost);
    }
}