<?php

    include_once('set_error_log.php');
    include_once "configuration.php";
    include_once ("error_message.php");
    include_once "gcm_server_php/GCM.php";
    
    $connectDb = new Configuration();
    $gcm = new GCM();
    $connectDb->dbConnect();
    
    class Event
    {
        public function get($arr){
            $event_name = $arr[0];
                
            $query      =   "SELECT * FROM `events` WHERE LOWER(`link_name`) like '".trim(strtolower($event_name))."'";
                
            $array      =   array();
            
            $rs         =   mysql_query($query);
                
            while($resp     =   mysql_fetch_assoc($rs)){
                $array[]    =   $resp;
            }
                
            $respArr    = array();
                
            $respArr['respStatus']      = 200;
            $respArr['respMsg']         = $array;
                
            return $respArr;
        }
        
        public function push($arr){
            $event_name         = $arr[0];
            $event_description  = $arr[1];
            $event_latitude     = $arr[2];
            $event_longitude    = $arr[3];
            $event_address      = $arr[4];
            $event_vips         = $arr[5];
            $event_date         = $arr[6];
            $event_start_time   = $arr[7];
            $event_end_time     = $arr[8];
            $event_banner       = $arr[9];
            $link_name          = $arr[10];
            
            $link_name = str_replace(" ","-",strtolower($link_name));
            $link_name = preg_replace('/[^A-Za-z0-9\-]/', '', $link_name);
            
            $query  =   "INSERT INTO `events`(`event_name`, `event_description`, `event_address`, `event_latitude`, `event_longitude`, `event_vips`, `event_date`, `event_start_time`, `event_end_time`, `event_banner`,`link_name`)
                                      values ('".$event_name."','".$event_description."','".$event_address."','".$event_latitude."','".$event_longitude."','".$event_vips."','".$event_date."','".$event_start_time."','".$event_end_time."','".$event_banner."','".$link_name."')";
                
            $resp = mysql_query($query);
                
            $respArr = array();
                
            if($resp){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = mysql_insert_id();
                
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "Event not added";
            }
            return $respArr;
        }
    }
?>