<?php
function getProratedWeight($record){
    if($record['moisture_percentage'][1] < 12) return Round($record['total_weight'][1],2);
    $proratedWeight =  $record['total_weight'][1]  *  (1 - (($record['moisture_percentage'][1]/100) - 0.12));
    return Round($proratedWeight,2);
}

function getNetWeight($record){
    $netWeight =  getProratedWeight($record) - (61/500 * $record["bags_marketed"][1]);
    return Round($netWeight,2);
}

function calculate_prorated_total_weight($total_weight, $moisture_percentage)
{
    if ($moisture_percentage > 12){
        return $total_weight * (1 - (($moisture_percentage / 100) - 0.12));
    }
    return $total_weight;
}

function calculate_weight_of_empty_bag($bags_marketed)
{
    return (61 / 500) * $bags_marketed;
}

function calculate_net_weight_marketed($empty_bag_weight, $prorated_total_weight)
{
    return $prorated_total_weight - $empty_bag_weight;
}

function getTransportCost($record, $config){
if($record['transportation_flag'] === 0) return 0;
 return $record['bags_marketed'][1] * $config->transport_config;
}

function getProcessingCost($record, $config){
 return $record['bags_marketed'][1] * $config->process_config;
}

function getThreshingCost($record, $config){
if($record['threshing_flag'] === 0) return 0;
 return $record['bags_marketed'][1] * $config->threshing_config;
}
function getTotalCost($record,$config){
    return getTransportCost($record,$config) + getProcessingCost($record,$config) + getThreshingCost($record,$config);
}

function calculate_cost($cost_per_bag, $no_of_bags_transported)
{
    return $cost_per_bag * $no_of_bags_transported;
}

?>