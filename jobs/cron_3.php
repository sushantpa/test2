<?php
require_once(__DIR__ ."/../app//controllers/__data_helpers.php");
require_once(__DIR__ ."/../app/controllers/Logger.php");
require_once(__DIR__ ."/../app/models/harvest_tg_payments.php");
require_once(__DIR__ ."/../app/models/ms_playbook_dnp.php");
require_once(__DIR__ ."/../app/models/clearance_tgs.php");
require_once(__DIR__ ."/../app/models/tg_inventory_distributed.php");
require_once(__DIR__ ."/../app/models/harvest_member_payments.php");


function executeCron3($conn_1, $conn_2, $conn_3, $conn_4, $config){
    $start = microtime(true);
    $logArray = [];

    echo  date('Y-m-d H:i:s.u')."=====CRON 3 STARTED==== <br /> \n";
      try{
        echo  date('Y-m-d H:i:s.u')."=====Now fetching Initial Data==== <br /> \n";
        $tgs_from_members = MemberPayments::selectGrouped($conn_3);
        $trust_groups = TGPayments::selectMany($conn_3, array_column($tgs_from_members, 'ik_number'));
        $do_not_pay_list = MSDoNotPay::selectMany($conn_4);
       $insertArray = [];
       $memberUpdates = [];

       foreach($trust_groups as $thisGroup){
        $thisGroup['season'] = $config->season_config;
        if(onDNPList($do_not_pay_list, $thisGroup)) $thisGroup['shp_dnp'] = 1;

       if(isset($tgs_from_members[$thisGroup['ik_number']])){
        $memberUpdates[] = $thisGroup['ik_number'] ;
            $foundObject = $tgs_from_members[$thisGroup['ik_number']];
            $calc_contractual = $foundObject->bags / ($foundObject->field_size == 0 ? 1 : $foundObject->field_size)
                                == $config->contractual_threshold_config ? 0 : 1;
            $thisGroup['id_loan_size'] = $foundObject->field_size;
            $thisGroup['no_of_bags_marketed'] = $foundObject->bags;
            $thisGroup['contractual_flag'] = $calc_contractual;
            $thisGroup['failed_expectation'] = $foundObject->contractual;
            $thisGroup['total_harvest_advance'] = $foundObject->harvest_advance ;
            $thisGroup['net_harvest_advance'] = $foundObject->net_harvest_advance;
            $thisGroup['updated_flag'] = 1;
       }
       $insertArray[] = $thisGroup;
       }
        TGPayments::insertMany($conn_3, $insertArray, 2700);
        MemberPayments::updateMany($conn_3, $memberUpdates, ["transferred_flag" => 1]);

      echo  date('Y-m-d H:i:s.u')."=====CRON 3 DONE ==== <br /> \n";
      $stop = microtime(true);
      $elapsed = $stop - $start;
      $logArray[] = getLogString("CRON 3",  "###Done!!!! Job ran successfully in ". $elapsed. "seconds...");
  
      foreach( $logArray as $log){
       echo $log;
      }
      // END OF LOGS
  
       }
        catch(Exception $err){
            foreach($logArray as $log){
            echo $log;
            }
            echo "Something went wrong. CRON 3 failed: ". $err->getMessage();
        }
}
?>