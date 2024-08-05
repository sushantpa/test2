<?php
ini_set('memory_limit', '2048M');

require_once(__DIR__ . "/connect_db.php");
require_once(__DIR__ . "/constants.php");
require_once(__DIR__ . "/jobs/cron_0.php");
require_once(__DIR__ . "/jobs/cron_3.php");
require_once(__DIR__ . "/jobs/validate_cleared_table_job.php");
require_once(__DIR__ . "/jobs/cron_2.php");


$driver = new DBDriver;
$conn_inventory = $driver->connectPgSql(PG_INVENTORY_DB_NAME);
$conn_recruitment = $driver->connectPgSql(PG_RECRUITMENT_DB_NAME);
$conn_finance = $driver->connectPgSql(PG_FINANCE_DB_NAME);
$conn_mkt = $driver->connectMKT();
date_default_timezone_set('Africa/Lagos');
echo "Version: v0.0.2 \n";


// Fetch Config Values from the Database
$configDbOperations = new ConfigDbOperations($conn_inventory);
$configModel = $configDbOperations->get_data();


// Execute cron functions one after the other
executeCron0($conn_inventory, $configModel);
$cron1Job = new ValidateClearedTableJob($conn_inventory);
$logs = $cron1Job->validate_cleared_data($configModel);
foreach ($logs as $log) {
   echo $log;
}
$cron2Job = new Cron2($conn_finance, $conn_inventory);
$cron2logs = $cron2Job->startCron($configModel);
foreach ($cron2logs as $log) {
    echo $log;
}
executeCron3($conn_inventory, $conn_recruitment, $conn_finance, $conn_mkt, $configModel);


// Close db connections
$conn_inventory = null;
$conn_recruitment = null;
$conn_finance = null;
$conn_mkt = null;
?>