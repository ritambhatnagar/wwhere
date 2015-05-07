<?php
    //include_once('set_error_log.php');
    
    include_once "configuration.php";
    include_once "gcm_server_php/GCM.php";
    include_once ("error_message.php");
    
    $connectDb = new Configuration();
    $gcm = new GCM();
    $connectDb->dbConnect();
    
    class RequestUserLocation
    {
        public function push($arr)
        {
            global $connectDb, $gcm;
	    
	    //$query = "ALTER TABLE `request_user_location` ADD `is_autolocated` tinyint(1)";
	    //mysql_query($query);
	    //exit;
            $sender_user_id 	    = $arr[0];
            $sender_device_id 	    = $arr[1];
	    $receiver_user_id 	    = $arr[2];
	    $request_type 	    = $arr[3];				//0 - receiver_id's current location, 1 - any saved location in receiver's directory, 2 - tracking receiver
	    $req_loc_friend 	    = 0;
	    if($request_type==1){
		$req_loc_friend	    = $arr[4];
	    }
	    $is_request_for_group   = $arr[5];
            $group_id 		    = $arr[6];
            $note		    = $arr[7];
	    if($connectDb->verify_user($sender_user_id,$sender_device_id)!=0){
		$created_date 	    = date ("Y-m-d H:i:s");
		try{
		    $connectDb->transactionBegin();
			
		    $message_id = $connectDb->create_message_id($sender_user_id,$receiver_user_id);
			
		    $query  = 	"INSERT INTO `request_user_location`(`sender_id`, `receiver_id`,`request_location_of_user_id`, `created_date`, `is_request_for_group`, `group_id`, `request_type`,`note`)
					VALUES('".$sender_user_id."','".$receiver_user_id."','".$req_loc_friend."','".$created_date."','".$is_request_for_group."','".$group_id."','".$request_type."','".$note."')";
			
		    mysql_query($query);
			
		    if(mysql_affected_rows()>0){
			$respArr['respStatus'] = 200;
			$respArr['respMsg'] = "Request has been sent";
			$respArr['request_id']  = mysql_insert_id();
			$respArr['respMsg_id']  = $message_id;
			
			$gcm_notification = array();
			$gcm_notification['GCM_NOTI_TYPE']  = 3;
			$gcm_notification['GCM_DATA_PACKET']= array("request_id"=>$respArr['request_id'],
								    "sender_user_id"=>$sender_user_id,
								    "request_type"=>$request_type,
								    "req_loc_friend"=>$req_loc_friend,
								    "is_request_for_group"=>$is_request_for_group,
								    "group_id"=>$group_id,
								    "note"=>$note,
								    "msg_id"=>$message_id
								);
			
			//print_r($gcm_notification);
			$dcm_code = $connectDb->fetchColumnById('user','dcm_code','user_id',$receiver_user_id);
			    
			if(!$gcm->send_notification(array($dcm_code),$gcm_notification)){
			    $respArr['respStatus']  = 300;
			    $respArr['respMsg']	    = "Error sending notification. Request not sent.";
			    $connectDb->transactionRollBack();
			    return $respArr;
			}
			$connectDb->transactionCommit();
			return $respArr;
		    }else{
			$respArr['respStatus'] = 400;
			$respArr['respMsg'] = "Request not sent";
			$connectDb->transactionRollBack();
		    }
		    return $respArr;
		}catch(Exception $e){
		    
		}
	    }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
                return $respArr;
            }
        }
        
        public function get($jsonData)
        {
            $arr = explode(",",$jsonData);
	    $request_id = $arr[0];
            $query = "SELECT `request_id`,`sender_id`,`receiver_id`, `is_approved`, `request_location_of_user_id`, `created_date`, `is_request_for_group`, `group_id`, `request_type`
                      From `request_user_location`
                      WHERE `request_id` =".$request_id;
            $recordset = mysql_query($query);
            $request_record = mysql_fetch_object($recordset);
            
            return $request_record;
        }
        
        public function approveRequest($arr)
        {
            global $connectDb,$gcm;
	        
            $request_id  	= $arr[0];
            $receiver_id 	= $arr[1];
	    $receiver_device_id = $arr[2];
	    $latitude    	= $arr[3];
	    $longitude   	= $arr[4];
	    $capture_map	= $arr[5];
	    $note		= $arr[6];
	    $is_autolocated     = $arr[7];
	    $msg_id         	= $arr[8];
            $msg_type       	= $arr[9];
            $temp_code      	= $arr[10];
	    $is_group       	= $arr[11];
            $group_id       	= $arr[12];
	    
            try{
                $connectDb->transactionBegin();
                    
                $query = "UPDATE `request_user_location` SET `is_approved`=1, is_autolocated=".$is_autolocated." WHERE `receiver_id`=".$receiver_id. " and `request_id`=".$request_id;
		    
                $requestData = array();
				mysql_query($query);
                if(mysql_affected_rows()>0){
                    $requestData = $connectDb->fetchRowById("request_user_location","request_id",$request_id);
		    
		    //Receiver will be the sender of message
		    $message_id  = $connectDb->create_message_id($receiver_id,$requestData['receiver_id']);
		    
                    $created_date = date('Y-m-d H:i:s');
                    if($requestData['request_type']!="2"){
                        $location_id =  $this->find_location_id($latitude,$longitude);
			
			if($location_id!=0){
				
			    //sender of request will be receiver of sharelocation and vice a verse
			    $query = "INSERT INTO `share_user_location`(`sender_user_id`, `receiver_user_id`,`location_id`,`created_date`, `is_group`, `group_id`,`reference_id`,`capture_map`,`note`,`msg_id`,`msg_type`,`temp_code`)
						     VALUES('".$requestData['receiver_id']."','".$requestData['sender_id']."','".$location_id."','".$created_date."','".$requestData['is_request_for_group']."','".$requestData['group_id']."','".$requestData['request_id']."','".$capture_map."','".$note."','".$msg_id."','".$msg_type."','".$temp_code."')";
    			    if(mysql_query($query)){
				$respArr['respStatus']  = 200;
				$respArr['respMsg']	= "Request approved & location shared";
				$respArr['share_id']    = mysql_insert_id();
				$respArr['respMsg_id'] = $message_id;
				
				$gcm_notification = array();
				$gcm_notification['GCM_NOTI_TYPE']  = 4;
				$gcm_notification['GCM_DATA_PACKET']= array(
									    "request_id"=>$request_id,
									    "share_id"=>$respArr['share_id'],
									    "sender_id"=>$requestData['receiver_id'],
									    "user_id"=>$requestData['sender_id'],
									    "msg_id"=>$message_id,
									    "latitude"=>$latitude,
									    "longitude"=>$longitude,
									    "is_autolocated"=>$is_autolocated,
									    "note"=>$note
									);
				//print_r($gcm_notification);
				
				$dcm_code = $connectDb->fetchColumnById('user','dcm_code','user_id',$requestData['sender_id']);
				if($dcm_code==null || $dcm_code=="null" || $dcm_code===false){
				    $respArr['respStatus']  = 404;
				    $respArr['respMsg']	    = "Recipient's GCM code not found. Request not sent.";
				    $connectDb->transactionRollBack();
				    return $respArr;
				}
				if(!$gcm->send_notification(array($dcm_code),$gcm_notification)){
				    $respArr['respStatus']  = 300;
				    $respArr['respMsg']	    = "Error sending notification. Request not sent.";
				    $connectDb->transactionRollBack();
				    return $respArr;
				}
				
				$connectDb->transactionCommit();
				
			    }else{
				$respArr['respStatus']  = 401;
				$respArr['respMsg']	= "Request not approved & location not shared";
				$connectDb->transactionRollBack();
			    }
			}else{
			    $respArr['respStatus']  = 402;
			    $respArr['respMsg']	    = "Request did not approved. Check your latitude longitude. Cannot create a location";
			    $connectDb->transactionRollBack();
			}
                    }
                }else{
		    $respArr['respStatus']  = 400;
		    $respArr['respMsg']	    = "Request is already approved";
                    $connectDb->transactionRollBack();
                }
                return $respArr;
            }catch(Exception $e){
	    	
            }
        }
	
	public function rejectRequest($jsonData)
        {
            global $connectDb;
	    
            $request_id  = $arr[0];
	    $user_id     = $arr[1];
	    $device_id   = $arr[2];
            
            $query = "UPDATE `request_user_location` SET `is_approved`=2 WHERE `receiver_id`=".$receiver_id. " and `request_id`=".$request_id;
                
            $recordset = mysql_query($query);
                
            $requestData = array();
            
            $respArr = array();
            if(mysql_affected_rows()==1){
		$respArr['respStatus'] = 200;
                $respArr['respMsg'] = "Request is rejected by you";
                $respArr['request_id']  = $request_id;
            }else{
		$respArr['respStatus'] = 200;
                $respArr['respMsg'] = "Request cannot be rejected. You are not the receiver of this request";
            }
           return $respArr;
        }
        protected function find_location_id($latitude,$longitude){
            $latitude  = round($latitude,6);
            $longitude = round($longitude,6);

            $sql = "SELECT `location_id`, `latitude`,`longitude`
                    FROM `location`
                    WHERE round(`latitude`,4)=round(".$latitude.",4) and round(`longitude`,4)=round(".$longitude.",4)";
                
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
	public function gettry($data){
            $query = "SELECT `request_id`,`sender_id`,`receiver_id`, `is_approved`, `request_location_of_user_id`, `created_date`, `is_request_for_group`, `group_id`, `request_type`
                      From `request_user_location`";
            $recordset = mysql_query($query);
            $arr = array();
            while($user_record = mysql_fetch_assoc($recordset)){
                $arr[] = $user_record;
            }
            
            return $arr;
	}
        public function getSentRequestIndividualList($jsonData)
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
            $query = "SELECT `request_id`, `mobile_number` as `receiver_mobile_number`, `is_approved`,
                             `request_location_of_user_id`, `request_user_location`.`created_date`, `request_type`
                      From  `request_user_location`,`user`
                      WHERE `request_user_location`.`receiver_id`=`user`.`user_id` and `is_request_for_group`=0 and `group_id`=0
                               and `sender_id` =".$sender_id;
		
            $recordset = mysql_query($query);
            
            $request_array = array();
            
            while($request_record = mysql_fetch_assoc($recordset))
            {
                $request_array[] = $request_record;
            }
            
            return $request_array;
        }
        
        public function getSentRequestGroupList($jsonData)
        {
            global $connectDb;
            
            $arr = explode(",",$jsonData);
            $sender_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[0]);;
            if($sender_id=="null"){
                $respArr['resp']['msg'] = "No data found";
                $respArr['resp']['id']  = "Sender Mobile Number not registered";
                return $respArr;
            }
            $query = "SELECT `request_id`,`mobile_number` as `receiver_mobile_number`,
                             `is_approved`, `request_user_location`.`group_id`, `user_group`.`group_name`, `request_type`
                      FROM  `request_user_location`,`user_group`,`user`
                      WHERE `request_user_location`.`receiver_id`=`user`.`user_id` and 
                            `user_group`.`group_id` = `request_user_location`.`group_id`
                            and `is_request_for_group`=1 and `sender_id`=".$sender_id;
                
            $recordset = mysql_query($query);
            
            $request_array = array();
            while($request_record = mysql_fetch_assoc($recordset))
            {
                 $request_array[] = $request_record;
            }
            
            return $request_array;
        }
        
        public function getReceiveRequestIndividualList($jsonData)
        {
            global $connectDb;
            
            $arr = explode(",",$jsonData);
            $receiver_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[0]);
            if($receiver_id=="null"){
                $respArr['resp']['msg'] = "No data found";
                $respArr['resp']['id']  = "Receiver Mobile Number not registered";
                return $respArr;
            }            
            $query = "SELECT `request_id`, `mobile_number` as `sender_mobile_number`,`sender_id`, `is_approved`,
                             `request_location_of_user_id`, `created_date`, `request_type`
                      From `request_user_location`,`user`
                      WHERE `request_user_location`.`sender_id`=`user`.`user_id` and `is_request_for_group`=0 and `group_id`=0
                               and `receiver_id` =".$receiver_id;
            
            $recordset = mysql_query($query);
            
            $request_array = array();
            
            while($request_record = mysql_fetch_assoc($recordset))
            {
                $request_array[] = $request_record;
            }
            
            return $request_array;
        }
        
        public function getReceiveRequestGroupList($jsonData)
        {
            global $connectDb;
            
            $arr = explode(",",$jsonData);
            $receiver_id = $connectDb->fetchColumnById('user','user_id','mobile_number',$arr[0]);
            if($receiver_id=="null"){
                $respArr['resp']['msg'] = "No data found";
                $respArr['resp']['id']  = "Receiver Mobile Number not registered";
                return $respArr;
            }
            $query = "SELECT `request_id`,`mobile_number` as `sender_mobile_number`,`sender_id`,
                             `is_approved`, `request_user_location`.`group_id`, `user_group`.`group_name`, `request_type`
                      FROM  `request_user_location`,`user_group`,`user`
                      WHERE `request_user_location`.`sender_id`=`user`.`user_id` and 
                            `user_group`.`group_id` = `request_user_location`.`group_id`
			    and `is_request_for_group`=1 and `receiver_id`=".$receiver_id;
            
            $recordset = mysql_query($query);
            
            $request_array = array();
            while($request_record = mysql_fetch_assoc($recordset))
            {
                $request_array[] = $request_record;
            }
            
            return $request_array;
        }
        
    }

     $var1 = new RequestUserLocation();
     /*
          $var1->push("1","2","0","0","1","1","2");
          $var1->push("1","3","0","0","1","1","2");
          $var1->push("1","4","0","0","1","1","2");
          $var1->push("2","1","0","0","0","0","1");
          $var1->push("3","4","0","0","0","0","1");
          $var1->push("4","2","0","0","0","0","2");
          $var1->push("3","1","0","0","0","0","1");
          $var1->push("1","4","0","0","0","0","1");
          $var1->push("4","3","0","0","0","0","2");
          echo "<pre>";
          print_r($var1->getIndividualList("2"));
          print_r($var1->getGroupList("2"));
          echo "</pre>";
     */
     
     //echo $var1->approveRequest(1, 2);
?>
