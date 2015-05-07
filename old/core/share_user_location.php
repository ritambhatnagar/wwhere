<?php
    include_once "configuration.php";
    include_once "gcm_server_php/GCM.php";
    include_once ("error_message.php");
    $connectDb = new Configuration();
    $gcm = new GCM();
    
    $connectDb->dbConnect();
    
    class ShareUserLocation{
         
        public function push($arr){
            global $connectDb,$gcm,$message;
            $respArr = array();
            
            $receiverInfo = $this->get_receiver_info($arr[2]);
            //print_r($receiverInfo);
            if($receiverInfo === false){
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["share_location"]["inv_receiver"];
                return $respArr;
            }
            $receiver_dcm_key   = $receiverInfo['dcm_code'];
            $sender_id      =   $arr[0];
            $device_id      =   $arr[1];
            $receiver_id    =   $arr[2];
            $msg_id         =   $arr[3];
            $msg_type       =   $arr[4];
            $temp_code      =   $arr[5];
            $latitude       =   $arr[6];
            $longitude      =   $arr[7];
            $altitude       =   $arr[8];
            $capture_image  =   $arr[9];
            $is_group       =   $arr[10];
            $group_id       =   $arr[11];
            $reference_id   =   $arr[12];
            $note           =   $arr[13];
            $template_message = $arr[14];
                
            if($connectDb->verify_user($sender_id,$device_id)!=0){
                
                $message_id  = $connectDb->create_message_id($sender_id,$receiver_id);
                
                if($msg_type==0){
                    $temp_code  = "";
                }
                
                if($temp_code != 800){
                    $template_message = "";
                }
                
                if($msg_type==1 || $temp_code==800){
                    $gcm_notification = array();
                    $gcm_notification['GCM_NOTI_TYPE'] = 0;
                    $gcm_notification['GCM_DATA_PACKET']= array(
                                                                "temp_code"=>$temp_code,
                                                                "sender_id"=>$sender_id,
                                                                "user_id"=>$receiver_id,
                                                                "msg_id"=>$message_id,
                                                                "temp_msg"=>$template_message
                                                            );
                        
                    $gcm->send_notification(array($receiver_dcm_key),$gcm_notification);
                    $respArr['respStatus']  = 200;
                    $respArr['respMsg']     = "Notification sent";
                    $respArr['respMsg_id']  = $message_id;
                    return $respArr;
                }
                
                $created_date   =   date ("Y-m-d H:i:s");
                $location_id    =   $this->find_location_id($latitude,$longitude);
                if($location_id==0){
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = "Error finding location. It cannot be shared";
                    return $respArr;
                }
                $query          =   "INSERT INTO
                                        `share_user_location`(`sender_user_id`, `receiver_user_id`,`msg_id`,`msg_type`,`temp_code`,`location_id`,`altitude`,`capture_map`,`created_date`,`is_group`, `group_id`,`reference_id`,`note`)
                                                       VALUES('".$sender_id."','".$receiver_id."','".$message_id."','".$msg_type."','".$temp_code."','".$location_id."','".$altitude."','".$capture_image."','".$created_date."','".$is_group."','".$group_id."','".$reference_id."','".$note."')";
                mysql_query($query);
                    
                if(mysql_affected_rows()>0){
                    $respArr['respStatus']      = 200;
                    $respArr['respMsg']         = $message["share_location"]["shareSucc"];
                    $respArr['shareLocationID'] = mysql_insert_id();
                    $respArr['respMsg_id'] = $message_id;
                    
                    $gcm_notification = array();
                    $gcm_notification['GCM_NOTI_TYPE'] = 0;
                    $gcm_notification['GCM_DATA_PACKET']= array(
                                                                "share_location_id"=>$respArr['shareLocationID'],
                                                                "sender_id"=>$sender_id,
                                                                "user_id"=>$receiver_id,
                                                                "msg_id"=>$message_id,
                                                                "temp_msg"=>$template_message
                                                                );
                    $gcm->send_notification(array($receiver_dcm_key),$gcm_notification);
                    
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = $message["share_location"]["shareFail"];
                }
                return $respArr;
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
        }
        
        public function get($arr){
            global $connectDb,$gcm;
            $respArr = array();
            $share_location_id  =   $arr[0];
            $sender_id          =   $arr[1];
            $device_id          =   $arr[2];
            $msg_id             =   $arr[3];
                
            if($connectDb->verify_user($sender_id,$device_id)!=0){
                
                $query          =   "SELECT `share_user_location`.*, `location`.`latitude`,`location`.`longitude`
                                     FROM `share_user_location`,`location`
                                     WHERE `share_user_location`.`location_id`=`location`.`location_id` and `share_id`=".$share_location_id;
                    
                $resultSet  = mysql_query($query);
                    
                $resultArray = array();
                    
                while($row = mysql_fetch_assoc($resultSet)){
                    $resultArray[] = $row;
                }
                if(count($resultArray)>0){
                    $respArr['respStatus']      = 200;
                    $respArr['respMsg']         = $resultArray;
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "Invalid request";
                }
                return $respArr;
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
        }
        
        public function gettry($arr){
            $query = "SELECT `share_id`, `sender_user_id`, `receiver_user_id`, `msg_id`, `msg_type`, `temp_code`, `location_id`, `altitude`, `capture_map`, `created_date`, `is_group`, `group_id`, `reference_id`, `note`
                      FROM `share_user_location`
                      ORDER BY `share_id` limit 10";
                
            $recordset = mysql_query($query);
            $arr = array();
            while($user_record = mysql_fetch_assoc($recordset)){
                $arr[] = $user_record;
            }
            
            return $arr;
        }
        
        public function acknowledge_notification($arr){
            global $connectDb,$gcm;
            $sender_id  =   $arr[0];
            $device_id  =   $arr[1];
            $msg_id     =   $arr[2];
            $receiver_id =  $arr[3];
            if($connectDb->verify_user($sender_id,$device_id)!=0){
                $receiverInfo = $connectDb->get_receiver_info($receiver_id);
                    
                if($receiverInfo === false){
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = $message["share_location"]["inv_receiver"];
                    return $respArr;
                }
                    
                $receiver_dcm_key   = $receiverInfo['dcm_code'];
                    
                $gcm_notification = array();
                $gcm_notification['GCM_NOTI_TYPE'] = 1;
                $gcm_notification['GCM_DATA_PACKET']= array("msg_id"=>$msg_id,"user_id"=>$sender_id);
                $gcm->send_notification(array($receiver_dcm_key),$gcm_notification);
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = "Notification";
                return $respArr;
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
            
        }
        protected function find_location_id($latitude,$longitude){
            $latitude  = round($latitude,6);
            $longitude = round($longitude,6);
            
            $sql = "SELECT `location_id`, `latitude`,`longitude`
                    FROM `location`
                    WHERE round(`latitude`,6)=round(".$latitude.",6) and round(`longitude`,6)=round(".$longitude.",6)";
                
            $resultSet  = mysql_query($sql);
            $resultArray = array();
            while($row = mysql_fetch_assoc($resultSet)){
                $resultArray[] = $row;
            }
            if(count($resultArray)>0){
                $location_id = $resultArray[0]['location_id'];
            }else{
                $created_date = $modified_date = date("Y-m-d H:i:s");
                    
                $actual_address = $this->get_actual_address($latitude,$longitude);
                    
                $query = "INSERT INTO `location`(`latitude`,`longitude`,`actual_address`,`created_date`,`modified_date`,`geo_fencing`,`note`)
                                          values('".$latitude."','".$longitude."','".$actual_address."','".$created_date."','".$modified_date."','10','')";
                    
                mysql_query($query);
                $location_id = mysql_insert_id();
            }
            return $location_id;
        }
        protected function get_actual_address($latitude,$longitude){
            $xml = file_get_contents("http://nominatim.openstreetmap.org/reverse?format=json&lat=".$latitude."&lon=".$longitude."&zoom=18&addressdetails=1");
            $array = (array)json_decode($xml);
            $address = (array)$array['address'];
            $string = $address['road'].", ".$address['suburb'].", ".$address['city'].", ".$address['state_district'].", ".$address['state']."-".$address['postcode'].", ".$address['country_code'];
            return $string;
        }
        protected function get_receiver_info($user_id){
            $query = "SELECT count(*) as `count`,`dcm_code`,`user_id` FROM `user` WHERE `user_id`='".$user_id."' and `reg_status`=1";
                
            $respArray = mysql_fetch_assoc(mysql_query($query));
                
            if ($respArray['count']>0){
                return $respArray;
            }else{
                return false;
            }
        }
        public function updateReadStatus($jsonData){
            $arr = explode(",",$jsonData);
            $share_id = $arr[0];
            global $connectDb;
            $receiver_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[1]);
            if($receiver_id=="null"){
                $respArr['resp']['msg'] = "Request not sent";
                $respArr['resp']['id']  = "Receiver Mobile Number not registered";
                return $respArr;
            }
            $query = "SELECT count(*) as `count` FROM `share_user_location` WHERE `receiver_user_id`=".$receiver_id. " and `share_id`=".$share_id;
            $record = mysql_fetch_assoc(mysql_query($query));
                
            if($record['count']==0){
                    $respArr['resp']['msg'] = "Read status not updated";
                    $respArr['resp']['id']  = "You are not the receiver of this share location";
            }else{
                $query = "UPDATE `share_user_location` SET `read_status`=1 WHERE `share_id`=".$share_id. " and `receiver_user_id`=".$receiver_id;
                if(mysql_query($query)){
                    $respArr['resp']['msg'] = "Read status updated";
                    $respArr['resp']['id']  = $share_id;
                }else{
                    $respArr['resp']['msg'] = "Read status already updated";
                    $respArr['resp']['id']  = $share_id;
                }                   
            }
            return $respArr;
        }
        public function getSentShareIndividualList($jsonData)
        {
            //sender mobile number is passed
            global $connectDb;
            
            $arr = explode(",",$jsonData);
            $sender_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[0]);;
            if($sender_id=="null"){
                $respArr['resp']['msg'] = "No data found";
                $respArr['resp']['id']  = "Sender Mobile Number not registered";
                return $respArr;
            }
            $query = "SELECT `share_id`, `mobile_number` as `receiver_mobile_number`, `location`.`location_id`,`latitude`,`longitude`,
                             `share_user_location`.`created_date`, `reference_id`,`read_status`
                      FROM   `share_user_location`,`user`,`location`
                      WHERE  `location`.`location_id`=`share_user_location`.`location_id` and
                             `share_user_location`.`receiver_user_id`=`user`.`user_id` and
                             `is_group`=0 and `group_id`=0 and `sender_user_id` =".$sender_id;
                
            $recordset = mysql_query($query);
            
            $request_array = array();
            
            while($request_record = mysql_fetch_assoc($recordset))
            {
                $request_array[] = $request_record;
            }
            
            return $request_array;
        }
        
        public function getSentShareGroupList($jsonData)
        {
            global $connectDb;
            
            $arr = explode(",",$jsonData);
            $sender_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[0]);;
            if($sender_id=="null"){
                $respArr['resp']['msg'] = "No data found";
                $respArr['resp']['id']  = "Sender Mobile Number not registered";
                return $respArr;
            }
            $query = "SELECT `share_id`, `mobile_number` as `receiver_mobile_number`,`share_user_location`.`group_id`, `user_group`.`group_name`,
                             `location`.`location_id`,`latitude`,`longitude`,
                             `share_user_location`.`created_date`, `reference_id`,`read_status`
                      FROM   `share_user_location`,`user`,`location`,`user_group`
                      WHERE  `location`.`location_id`=`share_user_location`.`location_id` and
                             `share_user_location`.`receiver_user_id`=`user`.`user_id` and
                             `user_group`.`group_id` = `request_user_location`.`group_id` and
                             `is_group`=1 and `sender_user_id`=".$sender_id;
                    
            $recordset = mysql_query($query);
            
            $request_array = array();
            while($request_record = mysql_fetch_assoc($recordset))
            {
                 $request_array[] = $request_record;
            }
            
            return $request_array;
        }
        
        public function getReceiveShareIndividualList($jsonData)
        {
            global $connectDb;
            
            $arr = explode(",",$jsonData);
            $receiver_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[0]);
            if($receiver_id=="null"){
                $respArr['resp']['msg'] = "No data found";
                $respArr['resp']['id']  = "Sender Mobile Number not registered";
                return $respArr;
            }            
            $query = "SELECT `share_id`, `mobile_number` as `sender_mobile_number`, `location`.`location_id`,`latitude`,`longitude`,
                             `share_user_location`.`created_date`, `reference_id`,`read_status`
                      FROM   `share_user_location`,`user`,`location`
                      WHERE  `location`.`location_id`=`share_user_location`.`location_id` and
                             `share_user_location`.`sender_user_id`=`user`.`user_id` and
                             `is_group`=0 and `group_id`=0 and `receiver_user_id` =".$receiver_id;
                
            $recordset = mysql_query($query);
                
            $request_array = array();
                
            while($request_record = mysql_fetch_assoc($recordset))
            {
                $request_array[] = $request_record;
            }
            
            return $request_array;
        }
        
        public function getReceiveShareGroupList($jsonData)
        {
            global $connectDb;
            
            $arr = explode(",",$jsonData);
            $receiver_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[0]);
            if($receiver_id=="null"){
                $respArr['resp']['msg'] = "No data found";
                $respArr['resp']['id']  = "Receiver Mobile Number not registered";
                return $respArr;
            }
            $query = "SELECT `share_id`, `mobile_number` as `sender_mobile_number`, `location`.`location_id`,`latitude`,`longitude`,`share_user_location`.`group_id`, `user_group`.`group_name`,
                             `share_user_location`.`created_date`, `reference_id`,`read_status`
                      FROM   `share_user_location`,`user`,`location`,`user_group`
                      WHERE  `location`.`location_id`=`share_user_location`.`location_id` and
                             `user_group`.`group_id` = `request_user_location`.`group_id` and
                             `share_user_location`.`sender_user_id`=`user`.`user_id` and
                             `is_group`=1 and `receiver_user_id` =".$receiver_id;
            
            $recordset = mysql_query($query);
            
            $request_array = array();
            while($request_record = mysql_fetch_assoc($recordset))
            {
                $request_array[] = $request_record;
            }
            
            return $request_array;
        }

    }
?>
