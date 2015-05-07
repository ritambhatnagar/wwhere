<?php
     include_once('set_error_log.php');

    include_once "configuration.php";
    $connectDb = new Configuration();
    $connectDb->dbConnect();
    
    class Location
    {
        public $respArr = array();
        
        /*
        private $location_id;
        private $latitude;
        private $longitude;
        private $actual_address;
        private $share_count;
        private $created_date;
        private $modified_date;
        private $geo_fencing;
        private $note;
        
        public function __construct($location_id="",$latitude="",$logitude="",$actual_address="",$share_count="",$created_date="",$modified_date="",$note="")
        {
            $this->location_id="";
            $this->latitude="";
            $this->longitude="";
            $this->actual_address="";
            $this->share_count="";
            $this->created_date="";
            $this->modified_date="";
            $this->geo_fencing="";
            $this->note ="";
        }
        */
          public function __construct()
          {
              $respArr['resp'] = array();
              $respArr['resp']['msg']="";
              $respArr['resp']['id'] ="";
          }
          
          public function little_fluffy($arr)
          {
               global $connectDb;
               
               $user_id       =    $arr[0];
               $device_id     =    $arr[1];
               $latitude      =    $arr[2];
               $longitude     =    $arr[3];
               $user_timestamp=    $arr[4];
               
               $respArr = array();
               
               if($connectDb->verify_user($user_id,$device_id)!=0){
                    $location_id = $connectDb->get_location_id($latitude,$longitude);
                    
                    $query =  "INSERT INTO little_fluffy_location(user_id,location_id,latitude,longitude,user_timestamp)
                                                           values('".$user_id."','".$location_id."','".$latitude."','".$longitude."','".$user_timestamp."')";
                         
                    mysql_query($query);
                        
                    if(mysql_affected_rows()>0){
                         $respArr['respStatus']   =    200;
                         $respArr['respMsg']      =    "Little fluffy location added";
                         $respArr['respID']       =    mysql_insert_id();
                         return $respArr;
                    }else{
                         $respArr['respStatus']   =    400;
                         $respArr['respMsg']      =    "little fluffy location not added";
                         return $respArr;
                    }
               }else{
                    $respArr['respStatus']      = 400;
                    $respArr['respMsg']         = "You are unauthorized for sending this request";
                    return $respArr;
               }
                    
          }
          public function push($jsonData)
          {
               $arr = explode(",",$jsonData);
               
               $latitude       =   $arr[0];
               $longitude      =   $arr[1];
               $actual_address =   $arr[2];
               $geo_fencing    =   $arr[3];
               $note           =   $arr[4];
               
               $created_date = $modified_date = date("Y-m-d H:i:s");
               $query = "INSERT INTO `location`(`latitude`,`longitude`,`actual_address`,`created_date`,`modified_date`,`geo_fencing`,`note`)
                                      values('".$latitude."','".$longitude."','".$actual_address."','".$created_date."','".$modified_date."','".$geo_fencing."','".$note."')";
                   
               mysql_query($query);
                   
               if(mysql_affected_rows()>0){
                   $respArr['resp']['msg'] = "Location Added";
                   $respArr['resp']['id']  = mysql_insert_id();
               }else{
                   $respArr['resp']['msg'] = "Location Not Added";
                   $respArr['resp']['id']  = "null";
               }
               return $respArr;
          }
        
        public function update($jsonData)
        {
            $arr = explode(",",$jsonData);
                
            $location_id    =   $arr[0];
            $latitude       =   $arr[1];
            $longitude      =   $arr[2];
            $actual_address =   $arr[3];
            $share_count    =   $arr[4];
            $geo_fencing    =   $arr[5];
            $note           =   $arr[6];

            $modified_date = date("Y-m-d H:i:s");
                
            $query = "UPDATE `location` SET `latitude`='". $latitude ."',`longitude`='". $longitude ."',`actual_address`='".$actual_address."',
                             `share_count`='". $share_count ."',`modified_date`='".$modified_date."',`geo_fencing`='".$geo_fencing."',`note`='".$note."'
                      WHERE `location_id`='".$location_id."'";
                
            mysql_query($query);
                
            if(mysql_affected_rows()>0){
                return true;
            }else{
                return false;
            }
        }
        
        public function  updateShareCount($jsonData)
        {
            $arr = explode(",",$jsonData);
                
            $latitude       =   $arr[0];
            $longitude      =   $arr[1];

            $modified_date = date("Y-m-d H:i:s");
            
            $query = "UPDATE `location` SET `share_count`=`share_count` + 1,`modified_date`='".$modified_date."'
                      WHERE `latitude`='". $latitude ."' and `longitude`='". $longitude."'";
                
            mysql_query($query);
                
            if(mysql_affected_rows()>0){
                return true;
            }else{
                return false;
            }
        }
        
        public function updateActualAddress($jsonData)
        {
            $arr = explode(",",$jsonData);
                
            $location_id    =   $arr[0];
            $actual_address =   $arr[1];
                
            $modified_date =date("Y-m-d H:i:s");
            
            $query = "UPDATE `location` SET `actual_address`='". $actual_address ."',`modified_date`='".$modified_date."'
                      WHERE `location_id`='".$location_id."'";
                
            mysql_query($query);
                
            if(mysql_affected_rows()>0){
                return true;
            }else{
                return false;
            }
        }
        
        public function updateNote($jsonData)
        {
            $arr = explode(",",$jsonData);
                
            $location_id    =   $arr[0];
            $note           =   $arr[1];
                
            $modified_date =date("Y-m-d H:i:s");
            
            $query = "UPDATE `location` SET `note`='". $note ."',`modified_date`='".$modified_date."'
                      WHERE `location_id`='".$location_id."'";
                
            mysql_query($query);
                
            if(mysql_affected_rows()>0){
                return true;
            }else{
                return false;
            }
        }
        
        public function updateGeoFence($jsonData){
            $arr = explode(",",$jsonData);
                
            $location_id    =   $arr[0];
            $geo_fencing    =   $arr[1];
                
            $modified_date =date("Y-m-d H:i:s");
            
            $query = "UPDATE `location` SET `geo_fencing`='". $geo_fencing ."',`modified_date`='".$modified_date."'
                      WHERE `location_id`='".$location_id."'";
                
            mysql_query($query);
                
            if(mysql_affected_rows()>0){
                return true;
            }else{
                return false;
            }
        }
        public function gettry($data){
            $query = "SELECT `location_id`,`latitude`,`longitude`,`actual_address`,`created_date`,`modified_date`,`geo_fencing`,`note`
                      From `location`";
            $recordset = mysql_query($query);
            $arr = array();
            while($user_record = mysql_fetch_assoc($recordset)){
                $arr[] = $user_record;
            }
            
            return $arr;
	}
        public function get($jsonData){
            $arr = explode(",",$jsonData);
                
            $location_id    =   $arr[0];
                
            $query = "SELECT `latitude`,`longitude`,`actual_address`,`created_date`,`modified_date`,`geo_fencing`,`note`
                      From `location`
                      WHERE `location_id`='".$location_id."'";
            $recordset = mysql_query($query);
            $arr = array();
            while($user_record = mysql_fetch_assoc($recordset)){
                $arr[] = $user_record;
            }
            
            return $arr;
        }
        public function getLocationIdByLatitudeLogitude($jsonData){
            
            /*$arr = explode(",",$jsonData);
                
            $latitude    =   $arr[0];
            $longitude   =   $arr[1];
            
            $query = "SELECT `location_id`
                      From `location`
                      WHERE `latitude`='".$latitude."' and `logitude`='".$longitude."'";
            $recordset = mysql_query($query);
            $location_id = array();
            if(mysql_numrows($recordset)>0){
                $location_id = mysql_fetch_array($recordset);
            }else{
                $actual_address =   "";
                $geo_fencing    =   "10";
                $note           =   "";
                $this->push("$latitude,$longitude,$actual_address,$geo_fencing,$note");
                
            }
            $user_record = mysql_fetch_assoc($recordset);
            
            return $arr;
            */
        }
    }
    
?>
