<?php
     include_once('set_error_log.php');
/*
     define('BASE_URL', "http://yogesh.joeee.com/locator/");
     define('HOST', "dayschedulerappcom1.ipagemysql.com");
     define('USERNAME', "locator");
     define('PASSWORD', "locator22@@##");
     define('DATABASE', "locator_db");
*/
     define('BASE_URL', "http:/54.200.160/155/core/");
     define('HOST', "localhost");
     define('USERNAME', "locator_user");
     define('PASSWORD', "wWhere33##");
     define('DATABASE', "locator_db");

     $conn = "";
    
     class Configuration
     {
	  private $host;
	  private $user;
	  private $password;
	  private $database;
	  
	  public function __construct()
	  {
	      $this->host = HOST;
	      $this->user = USERNAME;
	      $this->password = PASSWORD;
	      $this->database = DATABASE;
	  }
	  
	  public function dbConnect($host="",$user="",$password="",$database="")
	  {
	      global $conn;
	      if($host!="" & $user!="" & $database !="")
	      {
		  $this->host = $host;
		  $this->user = $user;
		  $this->password = $password;
		  $this->database = $database;
	      }
	      $conn = mysql_connect($this->host,$this->user,$this->password);
	      mysql_select_db($this->database);
	  }
	  
	  public function fetchRowById($tableName,$uniqueColumnName,$uniqueColumnValue)
	  {
	       $query = "SELECT * FROM `".$tableName."` WHERE `".$uniqueColumnName."`='". $uniqueColumnValue ."'";
	       $recordset = mysql_query($query);
	       $user_record = mysql_fetch_assoc($recordset);
	       return $user_record;
	  }
	  
	  public function fetchColumnById($tableName,$selectCoulmnName,$uniqueColumnName,$uniqueColumnValue)
	  {
	       $query = "SELECT `".$selectCoulmnName."` FROM `".$tableName."` WHERE `".$uniqueColumnName."`='". $uniqueColumnValue ."'";
	       $recordset = mysql_query($query);
	       if(mysql_numrows($recordset)>0){
		  $user_record = mysql_fetch_assoc($recordset);
		  return $user_record[$selectCoulmnName];
	       }
	       return "null";
	  }
	  
	  public function recordExist($tableName,$uniqueColumnName,$uniqueColumnValue)
	  {
	       $query = "SELECT count(*) as `count` FROM `".$tableName."` WHERE `".$uniqueColumnName."`='". $uniqueColumnValue ."'";
	       $recordset = mysql_query($query);
	       $user_record = mysql_fetch_assoc($recordset);
	       return $user_record['count'];
	  }
	  
	  protected function transactionOpen()
	  {
	      mysql_query("SET AUTOCOMMIT=0");
	      mysql_query("START TRANSACTION");
	  }
	  public function transactionBegin()
	  {
	      $this->transactionOpen();
	      mysql_query("BEGIN");
	  }
	  public function transactionCommit()
	  {
	      mysql_query("COMMIT");
	      $this->transactionClose();
	  }
	  public function getFriendsGCMArray($user_id){
	       $list_friends = $this->fetchColumnById('user','friend_user_list','user_id',$user_id);
	       
	       $resultSet = mysql_query("SELECT dcm_code FROM user WHERE user_id in ($list_friends) and reg_status = 1");
	       
	       $gcmArray = array();
	       while($row = mysql_fetch_assoc($resultSet)){
		    $gcmArray[] = $row['dcm_code'];
	       }
	       return $gcmArray;
	  }
	  public function transactionRollBack()
	  {
	      mysql_query("ROLLBACK");
	      $this->transactionClose();
	  }
	  protected function transactionClose()
	  {
	      mysql_query("SET AUTOCOMMIT=1");
	  }
	  public function verify_user($user_id,$device_id){
	       $query = "SELECT count(*) as `count` FROM `user` WHERE `user_id`='".$user_id."' and `device_id`='".$device_id."'";
	       $recordset = mysql_query($query);
	       $user_record = mysql_fetch_assoc($recordset);
	       return $user_record['count'];
	  }
	  public function get_receiver_info($user_id){
	       $query = "SELECT count(*) as `count`,`dcm_code`,`user_id` FROM `user` WHERE `user_id`='".$user_id."' and `reg_status`=1";
		   
	       $respArray = mysql_fetch_assoc(mysql_query($query));
		   
	       if ($respArray['count']>0){
		   return $respArray;
	       }else{
		   return false;
	       }
	  }
	  public function get_location_id($latitude,$longitude){
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
	  public function create_message_id($sender_id,$receiver_id){
	       $datetime = date("Y-m-d",time());
	       $query = "INSERT INTO message(sender_id,receiver_id,timestamp)VALUES($sender_id,$receiver_id,$datetime)";
	       mysql_query($query);
	       $message_id = mysql_insert_id();
	       if($message_id!=0 && $message_id!=""){
		    return $message_id;
	       }else{
		    return 0;
	       }
	  }
    }  
?>