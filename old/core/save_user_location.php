<?php
    
    include_once('set_error_log.php');
    include_once "configuration.php";
    include_once "gcm_server_php/GCM.php";
    include_once ("error_message.php");
    
    $connectDb = new Configuration();
    $connectDb->dbConnect();
    $gcm = new GCM();
    
    class SaveUserLocation{
         
        public function push($arr){
            global $connectDb,$message,$gcm;
            
            $user_id            =   $arr[0];
            $device_id          =   $arr[1];
            
            $latitude           =   $arr[2];
            $longitude          =   $arr[3];
            $altitude           =   $arr[4];
            
            $under_whom_user_id =   $arr[5];
            $public_category_id =   $arr[6];
            
            $tag_name           =   $arr[7];
            $capture_map        =   $arr[8];
            $notes              =   $arr[9];
            
            if($connectDb->verify_user($user_id,$device_id)!=0){
                $created_date       =   $modified_date = date ("Y-m-d H:i:s");
                
                $location_id = $this->find_location_id($latitude,$longitude);
                if($location_id==0){
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = $message["save_location"]["saveFail"];
                    return $respArr;
                }
                    
                $sql = "INSERT INTO `saved_location`(`location_id`,`altitude`,`user_id`,`under_whom_user_id`,`public_category_id`,`tag_name`,`capture_map`,`created_date`,`last_modified_date`,`notes`)
                                               VALUE('".$location_id."','".$altitude."','".$user_id."','".$under_whom_user_id."','".$public_category_id."',
                                                     '".$tag_name."','".$capture_map."','".$created_date."','".$modified_date."','".$notes."')";
                    
                mysql_query($sql);
                    
                $respArr = array();
                    
                if (mysql_affected_rows()>0){
                    $respArr['respStatus']      = 200;
                    $respArr['respMsg']         = $message["save_location"]["saveSucc"];
                    $respArr['saved_location']  = $this->getSavedDetailById(mysql_insert_id());
                }else{
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = $message["save_location"]["saveFail"];
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are unauthorized for sending this request";
            }
            
            return $respArr;
        }
        
        public function update_saved_location($arr){
            global $message;
            
            $saved_location_id  =   $arr[0];
            $user_id            =   $arr[1];
            $device_id          =   $arr[2];
            
            $latitude           =   $arr[3];
            $longitude          =   $arr[4];
            $altitude           =   $arr[5];
            
            $under_whom_user_id =   $arr[6];
            $public_category_id =   $arr[7];
            
            $tag_name           =   $arr[8];
            $capture_map        =   $arr[9];
            $notes              =   $arr[10];
            
            $update_clauses     =   array();
            
            $modified_date      =   date ("Y-m-d H:i:s");
            
            if($latitude!="" || $longitude!=""){
                $location_id        =   $this->find_location_id($latitude,$longitude);
                if ($location_id==0){
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = $message["save_location"]["editFail"];
                    return $respArr;
                }
                $update_clauses[] = "`location_id`='".$location_id."'";
            }
            if($altitude!=""){
                $update_clauses[] = "`altitude`='".$altitude."'";
            }
            if($under_whom_user_id!=""){
                $update_clauses[] = "`under_whom_user_id`='".$under_whom_user_id."'";
            }
            if($tag_name!=""){
                $update_clauses[] = "`tag_name`='".$tag_name."'";
            }
            if($capture_map!=""){
                $update_clauses[] = "`capture_map`='".$capture_map."'";
            }
            if($notes!=""){
                $update_clauses[] = "`notes`='".$notes."'";
            }
            if(($under_whom_user_id=="" || $under_whom_user_id=="0") && $public_category_id!=""){
                $update_clauses[] = "`public_category_id`='".$public_category_id."'";
            }
            
            $update_clauses = implode(",",$update_clauses);
            
            $sql = "UPDATE `saved_location` SET $update_clauses , `last_modified_date`='".$modified_date."'
                                            WHERE `saved_id`='".$saved_location_id."'";
            
            mysql_query($sql);
                
            $respArr = array();
                
            if (mysql_affected_rows()>0){
                $respArr['respStatus']      = 200;
                $respArr['respMsg']         = $message["save_location"]["editSucc"];
                $respArr['saved_location_id'] = $this->getSavedDetailById($saved_location_id);
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = $message["save_location"]["editFail"];
            }
                
            return $respArr;
        }
        
        public function flushtable($arg){
            
            $query = "TRUNCATE TABLE `saved_location`";
            $recordset = mysql_query($query);
            return "done";
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
                
                $actual_address = htmlspecialchars($this->get_actual_address($latitude,$longitude));
                    
                $query = "INSERT INTO `location`(`latitude`,`longitude`,`actual_address`,`created_date`,`modified_date`,`geo_fencing`,`note`)
                                          values('".$latitude."','".$longitude."','".$actual_address."','".$created_date."','".$modified_date."','10','')";
                mysql_query($query);
                $location_id = mysql_insert_id();
            }
            return $location_id;
        }
        public function updateTagName($jsonData){
            $arr = explode(",",$jsonData);
            $saved_id       =   $arr[0];
            $user_id        =   $arr[1];
            $device_id      =   $arr[1];
            $tag_name       =   $arr[2];
            
            $modified_date = date ("Y-m-d H:i:s");
            
            $sql = "UPDATE `saved_location` SET `tag_name`='".$tag_name."', `last_modified_date`='".$modified_date."'
                                           WHERE md5(`saved_id`)='".$saved_id."' and `user_id`='".$user_id."'";
            mysql_query($sql);
            $respArr = array();
            if(mysql_affected_rows()>0){
                $respArr['resp']['msg'] = "Tag of saved location updated";
                $respArr['resp']['id']  = $saved_id;
            }else{
                $respArr['resp']['msg'] = "Tag of saved location not updated";
                $respArr['resp']['id']  = "null";
            }
            return $respArr;
        }
        public function updateNote($jsonData){
            $arr = explode(",",$jsonData);
            $saved_id   =   $arr[0];
            $user_id    =   $arr[1];
            $note       =   $arr[2];
            
            $modified_date = date ("Y-m-d H:i:s");
            
            $sql = "UPDATE `saved_location` SET `notes`='".$note."', `last_modified_date`='".$modified_date."'
                                           WHERE `saved_id`='".$saved_id."' and `user_id`='".$user_id."'";
            mysql_query($sql);
            $respArr = array();
            if(mysql_affected_rows()>0){
                $respArr['resp']['msg'] = "Note of saved location updated";
                $respArr['resp']['id']  = $saved_id;
            }else{
                $respArr['resp']['msg'] = "Note of saved location not updated";
                $respArr['resp']['id']  = "null";
            }
            return $respArr;
        }
        
        protected function get_actual_address($latitude,$longitude){
            $xml = file_get_contents("http://nominatim.openstreetmap.org/reverse?format=json&lat=".$latitude."&lon=".$longitude."&zoom=18&addressdetails=1");
            $array = (array)json_decode($xml);
            $address = (array)$array['address'];
            $string = $address['road'].", ".$address['suburb'].", ".$address['city'].", ".$address['state_district'].", ".$address['state']."-".$address['postcode'].", ".$address['country_code'];
            return $string;
        }
        public function getUserSavedLocation($user_id_para){
            $user_id = is_array($user_id_para)?$user_id_para[0]:$user_id_para;
            $query = "SELECT `saved_id`,`location`.`latitude`,`location`.`longitude`,`under_whom_user_id`,`tag_name`,`public_category_id`,
                             `saved_location`.`created_date`,`saved_location`.`last_modified_date`,`saved_location`.`notes`,`is_deleted`,
                             `saved_location`.`capture_map`
                      From   `saved_location`,`location`
                      WHERE  `user_id`='".$user_id."' and `location`.`location_id`=`saved_location`.`location_id` and `is_deleted`=0";
            
            $recordset = mysql_query($query);
            $arr = array();
            while($user_record = mysql_fetch_assoc($recordset)){
                $arr[] = $user_record;
            }            
            return $arr;
	}
        protected function getSavedDetailById($saved_id){
            $query = "SELECT `saved_location`.*,`location`.`latitude`,`location`.`longitude`
                      From `saved_location`,`location`
                      WHERE `saved_location`.`location_id`=`location`.`location_id` and `saved_id`='".$saved_id."'";
            $arr = mysql_fetch_assoc(mysql_query($query));
            return $arr;
        }
        
        public function deleteSavedLocation($arr){
            global $connectDb;
            
            $save_location_id   =   $arr[0];
            $user_id            =   $arr[1];
            $device_id          =   $arr[2];
            
            if($connectDb->verify_user($user_id,$device_id)!=0){
                $query = "UPDATE `saved_location` SET `is_deleted`=1
                          WHERE `saved_id`='".$save_location_id."' and `user_id`='".$user_id."'";
                $recordset = mysql_query($query);
                
                if($recordset==1)
                {
                    $respArr['respStatus']      = 200;
                    $respArr['respMsg']         = "Saved Location deleted.";
                }else{
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = "Saved Location not deleted. Try again";
                }
            }else{
                $respArr['respStatus']      = 400;
                $respArr['respMsg']         = "You are unauthorized for sending this request";
            }
            return $respArr;
        }
        
        public function gettry($data){
            $query = "SELECT `saved_id`,`location_id`,`user_id`,`under_whom_user_id`,`tag_name`,`created_date`,`last_modified_date`,`notes`,`is_deleted`
                      From `saved_location`";
            $recordset = mysql_query($query);
            $arr = array();
            while($user_record = mysql_fetch_assoc($recordset)){
                $arr[] = $user_record;
            }            
            return $arr;
	}
    }
    
    //$var1 = new SaveUserLocation();
    //echo $var1->push('1','2','63.1023','71.2353','0','0','1');
?>
