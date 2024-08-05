<?php
require_once(__DIR__ ."/../app//controllers/__checks.php");
require_once(__DIR__ ."/../app//controllers/__data_helpers.php");
require_once(__DIR__ ."/../app/models/harvest_checking_records.php");
require_once(__DIR__ ."/../app/models/harvest_scaling_records.php");
require_once(__DIR__ ."/../app/models/harvest_cleared_records.php");
require_once(__DIR__ ."/../app/controllers/Logger.php");


function executeCron0($conn_1, $config){
  $start = microtime(true);
  echo  date('Y-m-d H:i:s.u')."=====Cron 0 Started==== <br /> \n";
  $logArray = [];

    try{
      echo  date('Y-m-d H:i:s.u')."=====Now fetching Initial Data==== <br /> \n";
    $pending_records = CheckingRecords::getDualRecords($conn_1);
    echo  date('Y-m-d H:i:s.u')."=====Initial Data Successfully fetched==== <br /> \n";

    
    $passed_disparity_check = [];
    $failed_disparity_check = [];
    $passed_quality_check = [];
    $failed_quality_check = [];


    foreach($pending_records as $thisRecord){

       if(__passedDisparityCheck($thisRecord)){
         array_push($passed_disparity_check , $thisRecord);

         if(__passedQualityCheck($thisRecord, $config)){
            array_push($passed_quality_check , $thisRecord);
         }
         else{
            array_push($failed_quality_check , $thisRecord); 
         }
       }
       else {
        array_push($failed_disparity_check, $thisRecord);
       }
    }

    echo  date('Y-m-d H:i:s.u')."=== LOOP DONE.  NOW RUNNING DB UPDATES AND INSERTS ==== <br /> \n";

    // DATABASE UPDATES AND INSERTS
          CheckingRecords::updateMany( $conn_1, $failed_disparity_check, [ "verifier_flag" => 1]);
          ScalingRecords::updateMany( $conn_1, $passed_quality_check, ["passed_quality_check" => 1]);
          CheckingRecords::updateMany( $conn_1, $passed_quality_check, ["passed_quality_check" => 1]);
          ClearedRecords::insertMany($conn_1,$passed_quality_check,1600,$config);

    echo  date('Y-m-d H:i:s.u')."=== DB UDATES AND INSERTS DONE ==== <br /> \n";




    // STRICTLY LOGS
    //LOG 10 PENDING RECORDS FOR EACH CATEGORY
    // TODO - USE TEXT EMOJIS
    for($i=0; $i < 10; $i++){
      if($i >= count($pending_records)) break;
      $logArray[] = getLogString("Cron 0",  $pending_records[$i]['hsf_id']. " - Was initially fetched");
    }

    for($i=0; $i < 10; $i++){
      if($i >= count($passed_disparity_check)) break;
      $logArray[] = getLogString("Cron 0",  $passed_disparity_check[$i]['hsf_id']. " - Passed Disparity Check");
    }

    for($i=0; $i < 10; $i++){
      if($i >= count($failed_disparity_check)) break;
      $logArray[] = getLogString("Cron 0",  $failed_disparity_check[$i]['hsf_id']. " - Failed Disparity Check");
    }

    for($i=0; $i < 10; $i++){
      if($i >= count($passed_quality_check)) break;
      $logArray[] = getLogString("Cron 0",  $passed_quality_check[$i]['hsf_id']. " - Passed Quality Check");
    }

    for($i=0; $i < 10; $i++){
      if($i >= count($failed_quality_check)) break;
      $logArray[] = getLogString("Cron 0",  $failed_quality_check[$i]['hsf_id']. " - Failed Quality Check");
    }


    $logArray[] = getLogString("Cron 0",  count($pending_records). " - Total initial records===");
    $logArray[] = getLogString("Cron 0",  count($passed_disparity_check). " - records passed Disparity Check===");
    $logArray[] = getLogString("Cron 0",  count($failed_disparity_check). " - records failed Disparity Check===");
    $logArray[] = getLogString("Cron 0",  count($passed_quality_check). " - records passed Quality Check===");
    $logArray[] = getLogString("Cron 0",  count($failed_quality_check). " - records failed Quality Check===");


    $end = microtime(true);
    $elapsed = $end - $start;
    $logArray[] = getLogString("Cron 0",  "###Done!!!! Job ran successfully in ". $elapsed. "seconds...");

    foreach( $logArray as $log){
     echo $log;
    }
    // END OF LOGS
}
    catch(Exception $err){
      foreach($logArray as $log){
        echo $log;
       }
      echo "Something went wrong. Cron 0 failed: ". $err->getMessage();
    }
       }
?>