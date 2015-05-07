<?php

    include_once('set_error_log.php');
    include_once "configuration.php";
    include_once ("error_message.php");
    include_once "gcm_server_php/GCM.php";
    
    $connectDb = new Configuration();
    $gcm = new GCM();
    $connectDb->dbConnect();

    class UserGroup
    {
        
        public function send_push_notificatoin($arr){
            global $connectDb,$gcm;
            
            $admin_id           =   $arr[0];
            $password           =   $arr[1];
            $created_user_id    =   $arr[2];
            $device_id          =   $arr[3];
            $created_date       =   $last_modified_date = $last_activity_date = date("Y-m-d H:i:s");
            //list of member is seperated by comma
            $members_id         =   $arr[4];
            $respArr            =   array();
            if($connectDb->verify_user($created_user_id,$device_id)!=0){
                $query  =   "INSERT INTO `user_group`(`group_name`,`group_profile_pic`,`create_user_id`,`created_date`,`last_modified_date`,
                                                        `last_activity_date`,`members_id`)
                                               values('".$group_name."','".$group_profile_pic."','".$created_user_id."','".$created_date."','".$last_modified_date."',
                                                '".$last_activity_date."','".$members_id."')";
                    
                mysql_query($query);
                    
                if(mysql_affected_rows()>0){
                    $respArr['respStatus']  = 200;
                    $respArr['respMsg']     = "New Group Created";
                    $respArr['respId']      = mysql_insert_id();
                    
                    //Notify all members about the group
                    $query3 =  "SELECT `user_id`,`nick_name`,`dcm_code`,`mobile_number`,`device_type_id`
                                From `user`
                                WHERE `reg_status`=1 and `user_id` in (".$members_id.")";
                        
                    $resultSet3 = mysql_query($query3);
                        
                    $receiver_dcm_keys = array();
                    $gcm_member_data   = array();
                    if(mysql_numrows($resultSet3)>0){
                        
                        while ($records = mysql_fetch_assoc($resultSet3)){
                            $gcm_member_data[]  =   array("user_id"=>$records['user_id'],
                                                          "nick_name"=>$records['nick_name'],
                                                          "mobile_number"=>$records['mobile_number']);
                            if($records['user_id']!=$created_user_id)
                                $receiver_dcm_keys[] = $records['dcm_code'];
                        }
                        
                        $gcm_notification = array();
                        // gcm_type 11 means new group notification
                        $gcm_notification['GCM_NOTI_TYPE']  =   11;
                        $gcm_notification['GCM_DATA_PACKET']=   array("members_list"=>$gcm_member_data,
                                                                    "group_name"=>$group_name,
                                                                    "group_admin"=>$created_user_id,
                                                                    "group_id"=>$respArr['respId']
                                                                );
                        //print_r($gcm_notification);
                        $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "Cannot Created New Group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to create group";
            }
            return $respArr;
        }
        
        public function add_member($arr){
            global $connectDb,$gcm;
            
            $group_id        = $arr[0];
            $created_user_id = $arr[1];
            $device_id       = $arr[2];
            $new_member_id   = $arr[3];
                
            $last_activity_date = date("Y-m-d H:i:s");
                
            if($connectDb->verify_user($created_user_id,$device_id)!=0){
                
                $query  =   "SELECT members_id
                             FROM `user_group`
                             WHERE `create_user_id` = $created_user_id and `group_id`=".$group_id;
                    
                $group_details = mysql_query($query);
                    
                if(mysql_numrows($group_details)>0){
                    $group_details = mysql_fetch_assoc($group_details);
                    if(strpos(",".$group_details['members_id']."," , ",".$new_member_id.",")===false){
                        // new variable
                        $new_members_list = $group_details['members_id'].",".$new_member_id;
                            
                        $update_query = "UPDATE `user_group` SET members_id='".$new_members_list."' WHERE `group_id`=".$group_id;
                            
                        $resultSet3 = mysql_query($update_query);
                        $respArr['respStatus']  = 200;
                        $respArr['respMsg']     = "Member added successfully";
                        
                        //Notify all members about the new member in the group
                            
                        $query3 =  "SELECT `user_id`,`nick_name`,`dcm_code`,`mobile_number`,`device_type_id`,`reg_status`
                                    From `user`
                                    WHERE `reg_status`=1 and `user_id` in (".$new_members_list.")";
                            
                        $resultSet3 = mysql_query($query3);
                        
                        $receiver_dcm_keys = array();
                        $gcm_member_data = array();
                        $new_memeber_detail = array();
                        if(mysql_numrows($resultSet3)>0){
                            $i = 0;
                            while ($records = mysql_fetch_assoc($resultSet3)){
                                if($records['user_id']==$new_member_id){
                                    $new_memeber_detail[$i]['user_id'] = $records['user_id'];
                                    $new_memeber_detail[$i]['nick_name'] = $records['nick_name'];
                                    $new_memeber_detail[$i++]['mobile_number'] = $records['mobile_number'];
                                    $new_member_gcm = array($records['dcm_code']);
                                }else{
                                    if($records['user_id']!=$created_user_id)
                                        $receiver_dcm_keys[]   =   $records['dcm_code'];
                                }
                                $gcm_member_data[]  =   array("user_id"=>$records['user_id'],
                                                                "nick_name"=>$records['nick_name'],
                                                                "mobile_number"=>$records['mobile_number']
                                                             );
                            }
                            
                            $gcm_notification = array();
                            // gcm_type=12 means notify existing members
                            $gcm_notification['GCM_NOTI_TYPE']  =   12;
                            $gcm_notification['GCM_DATA_PACKET']=   array("new_member_detail"=>$new_memeber_detail,
                                                                          "group_id"=>$group_id
                                                                         );
                                
                            //print_r($gcm_notification);
                            ///echo "<br><br><br>";
                            $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                            
                            // Also send notification to the new member
                            $gcm_notification = array();
                            // Note that here gcm_type=11, as for new member it is new group
                            $gcm_notification['GCM_NOTI_TYPE']  =   11;
                            $gcm_notification['GCM_DATA_PACKET']=   array("member_detail"=>$gcm_member_data,
                                                                          "group_id"=>$group_id,
                                                                          "group_name"=>$group_name,
                                                                          "group_admin"=>$created_user_id
                                                                        );
                            //print_r($gcm_notification);
                            $gcm->send_notification($new_member_gcm,$gcm_notification);
                        }
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "Member already exist";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized to add members to this group";
                }
                return $respArr;
            }
        }
        public function get_user_group_info($arr){
            global $connectDb,$gcm;
            
            $created_user_id    =   $arr[0];
            $device_id          =   $arr[1];
            
            if($connectDb->verify_user($created_user_id,$device_id)!=0){
                $query  =  "SELECT group_id, members_id, 
                            From `user_group`
                            WHERE `create_user_id`=".$created_user_id;
                            
                $resultSet = mysql_query($query1);
                
                $query3 =  "SELECT `user_id`,`nick_name`,`dcm_code`,`mobile_number`,`device_type_id`
                            From `user`
                            WHERE `reg_status`=1 and `user_id` in (".$members_id.")";
                    
                $resultSet3 = mysql_query($query3);
                    
                $receiver_dcm_keys = array();
                $gcm_member_data   = array();
                if(mysql_numrows($resultSet3)>0){
                    
                    while ($records = mysql_fetch_assoc($resultSet3)){
                        $gcm_member_data[]  =   array("user_id"=>$records['user_id'],
                                                      "nick_name"=>$records['nick_name'],
                                                      "mobile_number"=>$records['mobile_number']);
                        if($records['user_id']!=$created_user_id)
                            $receiver_dcm_keys[] = $records['dcm_code'];
                    }
                }
            }
        }
        
        public function remove_member($arr){
            global $connectDb,$gcm;
            
            $group_id           =   $arr[0];
            $created_user_id    =   $arr[1];
            $device_id          =   $arr[2];
            $remove_member_id   =   $arr[3];
                
            $last_activity_date = date("Y-m-d H:i:s");
                
            if($connectDb->verify_user($created_user_id,$device_id)!=0){
                
                $query  =   "SELECT members_id
                             FROM `user_group`
                             WHERE `create_user_id` = $created_user_id and `group_id`=".$group_id;
                    
                $resultset = mysql_query($query);
                    
                if(mysql_numrows($resultset)>0){
                    $group_details = mysql_fetch_assoc($resultset);
                    
                    if(strpos(",".$group_details['members_id']."," , ",".$remove_member_id.",")!==false){
                        // new variable, remove member_id from existing list
                        // replace ",id" from existing ",member list,"
                        $new_members_list = substr(str_replace(",".$remove_member_id,"",",".$group_details['members_id'].","),1,-1);
                            
                        $update_query = "UPDATE `user_group` SET members_id='".$new_members_list."' WHERE `group_id`=".$group_id;
                            
                        $resultSet = mysql_query($update_query);
                        $respArr['respStatus']  = 200;
                        $respArr['respMsg']     = "Member removed successfully";
                            
                        //Notify all members about the member removed in the group
                            
                        $query3 =  "SELECT `user_id`,`nick_name`,`dcm_code`,`mobile_number`,`device_type_id`,`reg_status`
                                    From `user`
                                    WHERE `reg_status`=1 and `user_id` in (".$new_members_list.",".$remove_member_id.")";
                            
                        $resultSet3 = mysql_query($query3);
                        
                        $receiver_dcm_keys = array();
                        $gcm_member_data = array();
                        $remove_memeber_detail = array();
                        if(mysql_numrows($resultSet3)>0){
                            $i=0;
                            while ($records = mysql_fetch_assoc($resultSet3)){
                                if($records['user_id']==$remove_member_id){
                                    $remove_memeber_detail[$i]['user_id'] = $records['user_id'];
                                    $remove_memeber_detail[$i]['nick_name'] = $records['nick_name'];
                                    $remove_memeber_detail[$i++]['mobile_number'] = $records['mobile_number'];
                                    $remove_member_gcm      =   array($records['dcm_code']);
                                }else{
                                    if($records['user_id']!=$created_user_id)
                                        $receiver_dcm_keys[]    =   $records['dcm_code'];
                                }
                            }
                            
                            $gcm_notification = array();
                            // gcm_type=13 means notify existing members about "removed member"(by admin)
                            $gcm_notification['GCM_NOTI_TYPE']  =   13;
                            $gcm_notification['GCM_DATA_PACKET']=   array("remove_member_detail"=>$remove_memeber_detail,
                                                                          "group_id"=>$group_id
                                                                         );
                                
                            
                            $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                            
                            // Also send notification to the removed member
                            $gcm_notification = array();
                            // Note that here gcm_type=14, as for removed member
                            $gcm_notification['GCM_NOTI_TYPE']  =   14;
                            $gcm_notification['GCM_DATA_PACKET']=   array("group_id"=>$group_id,
                                                                          "group_name"=>$group_name,
                                                                          "group_admin"=>$created_user_id
                                                                         );
                            
                            $gcm->send_notification($remove_member_gcm,$gcm_notification);
                        }
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "Member already removed";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized to remove members from this group";
                }
                return $respArr;
            }
        }
        
        public function leave_group($arr){
            global $connectDb,$gcm;
            
            $group_id       =   $arr[0];
            $member_id      =   $arr[1];
            $device_id      =   $arr[2];
                
            if($connectDb->verify_user($member_id,$device_id)!=0){
                
                if($this->valid_group_member($member_id,$group_id)){
                    $query  =   "SELECT members_id
                                FROM `user_group`
                                WHERE `group_id`=".$group_id;
                        
                    $resultset = mysql_query($query);
                        
                    $group_details = mysql_fetch_assoc($resultset);
                        
                    $new_members_list = substr(str_replace(",".$member_id,"",",".$group_details['members_id'].","),1,-1);
                        
                    $update_query = "UPDATE `user_group` SET members_id='".$new_members_list."' WHERE `group_id`=".$group_id;
                        
                    $resultSet = mysql_query($update_query);
                    $respArr['respStatus']  = 200;
                    $respArr['respMsg']     = "You left the group successfully";
                        
                    //Notify all members about the member left in the group
                        
                    $query3 =  "SELECT `user_id`,`nick_name`,`dcm_code`,`mobile_number`,`device_type_id`,`reg_status`
                                From `user`
                                WHERE `reg_status`=1 and `user_id` in (".$new_members_list.",".$member_id.")";
                        
                    $resultSet3 = mysql_query($query3);
                        
                    $receiver_dcm_keys = array();
                    $gcm_member_data = array();
                    $remove_memeber_detail = array();
                        
                    while ($records = mysql_fetch_assoc($resultSet3)){
                        if($records['user_id'] == $member_id){
                            $remove_memeber_detail['user_id']       =  $records['user_id'];
                            $remove_memeber_detail['nick_name']     =  $records['nick_name'];
                            $remove_memeber_detail['mobile_number'] =  $records['mobile_number'];
                        }else{
                            $receiver_dcm_keys[]    =   $records['dcm_code'];
                        }
                    }
                    
                    $gcm_notification = array();
                    // gcm_type=13 means notify existing members about "removed member"(by admin)
                    $gcm_notification['GCM_NOTI_TYPE']  =   20;
                    $gcm_notification['GCM_DATA_PACKET']=   array("left_member_detail"=>$remove_memeber_detail,
                                                                    "group_id"=>$group_id
                                                                );
                    $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not a member of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;
        }
        
        public function request_location($arr){
            
            global $connectDb,$gcm;
            
            $group_id   =   $arr[0];
            $member_id  =   $arr[1];
            $device_id  =   $arr[2];
            $lat        =   $arr[3];
            $lon        =   $arr[4];
            $alti       =   $arr[5];
            //$note     =   $arr[3];
            $note       =   "";
            
            $created_date = date("Y-m-d H:i:s");
            
            if($connectDb->verify_user($member_id,$device_id)!=0){
                $is_request_for_group   =   1;
                $request_type           =   0;          // Not tracking
                
                if($this->valid_group_member($member_id,$group_id)){
                    $message_id  = $connectDb->create_message_id($member_id,0);
                    
                    $query = "INSERT INTO `request_user_location`(`sender_id`, `receiver_id`,`request_location_of_user_id`, `created_date`, `is_request_for_group`, `group_id`, `request_type`,`note`)
				    VALUES('".$member_id."','0','0','".$created_date."','".$is_request_for_group."','".$group_id."','".$request_type."','".$note."')";
                        
                    mysql_query($query);
                        
                    if(mysql_affected_rows()>0){
                        $respArr['respStatus']  =   200;
                        $respArr['respMsg']     =   "Request sent to the group.";
                        $respArr['request_id']  =   mysql_insert_id();
                        $respArr['respMsg_id']  =   $message_id;
                        $members = mysql_fetch_assoc(mysql_query("SELECT members_id FROM user_group WHERE group_id=".$group_id));
                        
                        $query3 =  "SELECT `dcm_code`,`user_id`
                                    From   `user`
                                    WHERE  `reg_status`=1 and `user_id` in (".$members['members_id'].")";
                           
                        $resultSet3 = mysql_query($query3);
                        
                        $receiver_dcm_keys = array();
                        
                        if(mysql_numrows($resultSet3)>0){
                            while ($records = mysql_fetch_assoc($resultSet3)){
                                if($records['user_id']!=$member_id)
                                    $receiver_dcm_keys[]    =   $records['dcm_code'];
                            }
                            
                            $gcm_notification = array();
                            // gcm_type=15 means notify existing members about new request for location
                            $gcm_notification['GCM_NOTI_TYPE']  =   15;
                            $gcm_notification['GCM_DATA_PACKET']=   array("request_id"  =>  $respArr['request_id'],
                                                                          "group_id"    =>  $group_id,
                                                                          "req_mem_id"  =>  $member_id,
                                                                          "lat"         =>  $lat,
                                                                          "lon"         =>  $lon,
                                                                          "alti"        =>  $alti,
                                                                          "msg_id"      =>  $message_id
                                                                         );
                            
                            $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                            
                        }
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "Request not sent. Please try again.";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized member of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;
        }
        public function delete($arr){
            global $connectDb,$gcm;
            
            $group_id        = $arr[0];
            $created_user_id = $arr[1];
            $device_id       = $arr[2];
                
            $last_activity_date = date("Y-m-d H:i:s");
                
            if($connectDb->verify_user($created_user_id,$device_id)!=0){
                
                $query  =   "SELECT members_id
                             FROM `user_group`
                             WHERE `create_user_id` = $created_user_id and `group_id`=".$group_id;
                    
                $group_details = mysql_query($query);
                    
                if(mysql_numrows($group_details)>0){
                    $group_details = mysql_fetch_assoc($group_details);
                        
                    $query3 =  "DELETE
                                From `user_group`
                                WHERE `group_id`=".$group_id." and `create_user_id`=".$created_user_id."";
                        
                    $resultSet3 = mysql_query($query3);
                    $respArr['respStatus']  = 200;
                    $respArr['respMsg']     = "Group deleted successfully";
                        
                    //Notify all members about the new member in the group
                        
                    $query3 =  "SELECT `user_id`,`nick_name`,`dcm_code`,`mobile_number`,`device_type_id`,`reg_status`
                                From `user`
                                WHERE `reg_status`=1 and `user_id` in (".$group_details['members_id'].")";
                        
                    $resultSet3 = mysql_query($query3);
                        
                    $receiver_dcm_keys = array();
                        
                    if(mysql_numrows($resultSet3)>0){
                        $i = 0;
                        while ($records = mysql_fetch_assoc($resultSet3)){
                            if($records['user_id']!=$created_user_id){
                                $receiver_dcm_keys[]   =   $records['dcm_code'];
                            }
                        }
                        
                        $gcm_notification = array();
                        // gcm_type=19 means notify existing members about group deletion
                        $gcm_notification['GCM_NOTI_TYPE']  =   19;
                        $gcm_notification['GCM_DATA_PACKET']=   array("admin_id"=>$created_user_id,
                                                                      "group_id"=>$group_id
                                                                    );
                        $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "You are not authorized to delete this group";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized to delete this group";
                }
                return $respArr;
            }
        }
        public function share_location($arr){
            
            global $connectDb,$gcm;
	        
            $member_id          =   $arr[0];
            $device_id          =   $arr[1];
            $latitude    	    =   $arr[2];
            $longitude          =   $arr[3];
            $altitude           =   $arr[4];
            $capture_map        =   $arr[5];
            $note	            =   $arr[6];
            $group_id       	=   $arr[7];
            $msg_id             =   $arr[8];
            $msg_type           =   $arr[9];
            $temp_code          =   $arr[10];
            $template_message   =   $arr[11];
            $is_group       	=   1;
            $receiver_id        =   0;
                
            $created_date       =   date("Y-m-d H:i:s");
                
            if($connectDb->verify_user($member_id,$device_id)!=0){
                
                if($this->valid_group_member($member_id,$group_id)){
                    $message_id  = $connectDb->create_message_id($member_id,$receiver_id);
                    if($msg_type==0){
                        $temp_code  = "";
                    }
                    if($temp_code != 800){
                        $template_message = "";
                    }
                    if($msg_type==1 || $temp_code==800){
                        
                        $gcm_notification = array();
                        
                        $members = mysql_fetch_assoc(mysql_query("SELECT members_id FROM user_group WHERE group_id=".$group_id));
                        
                        $query3 =  "SELECT `dcm_code`,`user_id`
                                    From   `user`
                                    WHERE  `reg_status`=1 and `user_id` in (".$members['members_id'].")";
                        
                        $resultSet3 = mysql_query($query3);
                            
                        $receiver_dcm_keys = array();
                        
                        $gcm_member_data = array();
                        
                        if(mysql_numrows($resultSet3)>0){
                            
                            while ($records = mysql_fetch_assoc($resultSet3)){
                                if($records['user_id']!=$member_id)
                                    $receiver_dcm_keys[]    =   $records['dcm_code'];
                                
                            }
                            $gcm_notification['GCM_NOTI_TYPE'] = 17;
                            $gcm_notification['GCM_DATA_PACKET']= array(
                                                                        "temp_code"=> $temp_code,
                                                                        "sender_id"=> $member_id,
                                                                        "group_id" => $group_id,
                                                                        "msg_id"   => $msg_id,
                                                                        "temp_msg" => $template_message,
                                                                        "msg_id"   => $message_id
                                                                        );
                            
                            $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                            $respArr['respStatus']  =   200;
                            $respArr['respMsg']     =   "Notification sent";
                            $respArr['respMsg_id']  =   $message_id;
                            return $respArr;
                        }
                    }
                    
                    $location_id        = $connectDb->get_location_id($latitude,$longitude);
                    if($location_id!=0){
                            
                        $query  =   "INSERT INTO `share_user_location`(`sender_user_id`, `receiver_user_id`,`msg_id`,`msg_type`,`temp_code`,`location_id`,`altitude`,`capture_map`,`created_date`,`is_group`, `group_id`,`reference_id`,`note`)
                                                           VALUES('".$member_id."','".$receiver_id."','".$message_id."','".$msg_type."','".$temp_code."','".$location_id."','".$altitude."','".$capture_map."','".$created_date."','".$is_group."','".$group_id."','0','".$note."')";
                        mysql_query($query);
                        
                        $respArr['respStatus']  =   200;
                        $respArr['respMsg']     =   "Location shared in the group.";
                        $respArr['shared_id']   =   mysql_insert_id();
                        $respArr['respMsg_id']  =   $message_id;
                        
                        $members = mysql_fetch_assoc(mysql_query("SELECT members_id FROM user_group WHERE group_id=".$group_id));
                        
                        $query3 =  "SELECT `dcm_code`,`user_id`
                                    From   `user`
                                    WHERE  `reg_status`=1 and `user_id` in (".$members['members_id'].")";
                        
                        $resultSet3 = mysql_query($query3);
                            
                        $receiver_dcm_keys = array();
                        
                        $gcm_member_data = array();
                        
                        if(mysql_numrows($resultSet3)>0){
                            
                            while ($records = mysql_fetch_assoc($resultSet3)){
                                if($records['user_id']!=$member_id)
                                    $receiver_dcm_keys[]    =   $records['dcm_code'];
                            }
                            
                            $gcm_notification = array();
                            // gcm_type=17 means notify existing members about members shared location
                            $gcm_notification['GCM_NOTI_TYPE']  =   17;
                            $gcm_notification['GCM_DATA_PACKET']=   array("shared_id"   =>  $respArr['shared_id'],
                                                                          "group_id"    =>  $group_id,
                                                                          "share_mem_id"=>  $member_id,
                                                                          "latitude"    =>  $latitude,
                                                                          "longitude"   =>  $longitude,
                                                                          "altitude"    =>  $altitude,
                                                                          "note"    	=>  $note,
                                                                          "msg_id"      =>  $message_id,
                                                                          "msg_type"    =>  $msg_type,
                                                                          "temp_code"   =>  $temp_code
                                                                        );
                            
                            $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                        }
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "There was an error fetching your location. Please try again.";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized member of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;
        }
        
        public function approve_request($arr){
            
            global $connectDb,$gcm;
	        
            $request_id  	    =   $arr[0];
            $member_id          =   $arr[1];
            $device_id          =   $arr[2];
            $latitude    	    =   $arr[3];
            $longitude   	    =   $arr[4];
            $altitude           =   $arr[5];
            $note		        =   $arr[6];
            $group_id       	=   $arr[7];
            $msg_id             =   $arr[8];
            
            $capture_map	    =   "";
            $msg_type           =   "";
            $temp_code          =   "";
            $is_group       	=   1;
            $receiver_id 	    =   0;
	    
            $created_date       =   date("Y-m-d H:i:s");
            
            $location_id        =   $connectDb->get_location_id($latitude,$longitude);
            
            if($connectDb->verify_user($member_id,$device_id)!=0){
                
                if($this->valid_group_member($member_id,$group_id)){
                    //Some one has approve the request.
                    if($location_id!=0){
                        $message_id  = $connectDb->create_message_id($member_id,$receiver_id);
                        $query = "UPDATE `request_user_location` SET `is_approved`=1 WHERE `is_approved`=0 and `request_id`=".$request_id;
                            
                        mysql_query($query);
                            
                        $query  =   "INSERT INTO `share_user_location`(`sender_user_id`, `receiver_user_id`,`msg_id`,`msg_type`,`temp_code`,`location_id`,`altitude`,`capture_map`,`created_date`,`is_group`, `group_id`,`reference_id`,`note`)
                                                           VALUES('".$member_id."','".$receiver_id."','".$msg_id."','".$msg_type."','".$temp_code."','".$location_id."','".$altitude."','".$capture_image."','".$created_date."','".$is_group."','".$group_id."','".$request_id."','".$note."')";
                        mysql_query($query);
                        
                        $respArr['respStatus']  =   200;
                        $respArr['respMsg']     =   "Location shared in the group.";
                        $respArr['shared_id']   =   mysql_insert_id();
                        $respArr['respMsg_id']  =   $message_id;
                        
                        $members = mysql_fetch_assoc(mysql_query("SELECT members_id FROM user_group WHERE group_id=".$group_id));
                        
                        $query3 =  "SELECT `dcm_code`,`user_id`
                                    From   `user`
                                    WHERE  `reg_status`=1 and `user_id` in (".$members['members_id'].")";
                            
                        $resultSet3 = mysql_query($query3);
                        
                        $receiver_dcm_keys = array();
                        $gcm_member_data = array();
                        
                        if(mysql_numrows($resultSet3)>0){
                            
                            while ($records = mysql_fetch_assoc($resultSet3)){
                                if($records['user_id']!=$member_id)
                                    $receiver_dcm_keys[]    =   $records['dcm_code'];
                            }
                            
                            $gcm_notification = array();
                            // gcm_type=16 means notify existing members about members shared location
                            $gcm_notification['GCM_NOTI_TYPE']  =   16;
                            $gcm_notification['GCM_DATA_PACKET']=   array("shared_id"   =>  $respArr['shared_id'],
                                                                          "request_id"  =>  $request_id,
                                                                          "group_id"    =>  $group_id,
                                                                          "share_mem_id"=>  $member_id,
                                                                          "latitude"    =>  $latitude,
                                                                          "longitude"   =>  $longitude,
                                                                          "altitude"    =>  $altitude,
                                                                          "note"    	=>  $note,
                                                                          "msg_id"      =>  $message_id
                                                                        );
                            //print_r($gcm_notification);
                            $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                        }
                        $respArr['respStatus']  = 200;
                        $respArr['respMsg']     = "Your location was shared in the group";
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "There was an error fetching your location. Please try again.";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized member of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;
        }
        
        public function get_all_shared_location($arr){
            global $connectDb,$gcm;
	        
            $member_id          =   $arr[0];
            $device_id          =   $arr[1];
            $group_id       	=   $arr[2];
            
            if($connectDb->verify_user($member_id,$device_id)!=0){
                
                if($this->valid_group_member($member_id,$group_id)){
                    $query  =   "SELECT `share_user_location`.id,sender_user_id,`location`.`location_id`,latitude,longitude,`share_user_location`.altitude,`msg_id`,`msg_type`,`temp_code`,`capture_map`,`created_date`,`share_user_location`.`note`
                                 FROM   `share_user_location`,`location`
                                 WHERE  `share_user_location`.`location_id`=`location`.`location_id` and `group_id`=".$group_id;
                        
                    $resultSet = mysql_query($query);
                    $i=0;
                    while($result = mysql_fetch_assoc($resultSet)){
                        $detail[$i]['shared_id']    =   $result['id'];
                        $detail[$i]['location_id']  =   $result['location_id'];
                        $detail[$i]['latitude']     =   $result['latitude'];
                        $detail[$i]['longitude']    =   $result['longitude'];
                        $detail[$i]['altitude']     =   $result['altitude'];
                        $detail[$i]['msg_id']       =   $result['msg_id'];
                        $detail[$i]['msg_type']     =   $result['msg_type'];
                        $detail[$i]['temp_code']    =   $result['temp_code'];
                        $detail[$i]['capture_map']  =   $result['capture_map'];
                        $detail[$i]['date_time']    =   $result['create_date'];
                        $detail[$i]['note']         =   $result['note'];
                        
                        $i++;
                    }
                    if(count($detail)>0){
                        $respArr['respStatus']  =   200;
                        $respArr['respMsg']     =   "Shared locations in the group details";
                        $respArr['shared_detail']   =   $detail;
                        
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "There is no shared location in the group";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized member of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;
        }
        protected function valid_group_member($member_id, $group_id){
            $query  =   "SELECT count(*) as `count`
                         From   `user_group`
                         WHERE  `group_id` = ".$group_id. " and (concat(',',`members_id`,',') like '%,". $member_id . "%,')";
                    
            $recordset = mysql_fetch_assoc(mysql_query($query));
                
            if($recordset['count']>0){
                return true;
            }
            return false;
        }
        public function get($arr){
            $respArr = array();
            global $connectDb,$gcm;
            
            $group_id   = $arr[0];
            $member_id  = $arr[1];
            $device_id  = $arr[2];
            
            if($connectDb->verify_user($member_id,$device_id)!=0){
                $query  = "SELECT `group_name`,`group_profile_pic`,`create_user_id`,`members_id`
                           From `user_group`
                           WHERE `group_id` = ".$group_id. " and (concat(',',`members_id`,',') like '%,". $member_id . "%,')";
                    
                $recordset = mysql_query($query);
                    
                if(mysql_numrows($recordset)>0){
                    
                    $group_details = mysql_fetch_assoc($recordset);
                        
                    $query3 =  "SELECT `user_id`,`nick_name`,`mobile_number`
                                From   `user`
                                WHERE  `reg_status`=1 and `user_id` in (".$group_details['members_id'].")";
                        
                    $resultSet3 = mysql_query($query3);
                        
                    $memeber_detail = array();
                    $i=0;
                    if(mysql_numrows($resultSet3)>0){
                        while ($records = mysql_fetch_assoc($resultSet3)){
                            $memeber_detail[$i]['user_id'] = $records['user_id'];
                            $memeber_detail[$i]['nick_name'] = $records['nick_name'];
                            $memeber_detail[$i++]['mobile_number'] = $records['mobile_number'];
                        }
                        $respArr['respStatus']  = 200;
                        $respArr['respDetail']  = array(
                                                        "group_name"=>$group_details['group_name'],
                                                        "group_profile_pic"=>$group_details['group_profile_pic'],
                                                        "admin_user_id"=>$group_details['create_user_id'],
                                                        "member_detail"=>$memeber_detail
                                                       );
                        return $respArr;
                        
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized to get details of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;
        }
        
        public function getGroupList($member_id){
            global $connectDb;
            
            $query  =  "SELECT `group_id`,`group_name`,`group_profile_pic`,`create_user_id`,`members_id`
                        From `user_group`
                        WHERE concat(',',`members_id`,',') like '%,". $member_id . ",%'";
                
            $recordset = mysql_query($query);
                
            $group_list = array();
                
            while($arr = mysql_fetch_array($recordset)){
                $query3 =   "SELECT `user_id`,`nick_name`,`mobile_number`
                            From   `user`
                            WHERE  `reg_status`= 1 and `user_id` in (".$arr['members_id'].")";
                    
                $resultSet3 = mysql_query($query3);
                    
                $memeber_detail = array();
                    
                if(mysql_numrows($resultSet3)>0){
                    $i = 0 ;
                    while ($records = mysql_fetch_assoc($resultSet3)){
                        $memeber_detail[$i]['user_id'] = $records['user_id'];
                        $memeber_detail[$i]['nick_name'] = $records['nick_name'];
                        $memeber_detail[$i++]['mobile_number'] = $records['mobile_number'];
                    }
                    $group_list[]  = array(
                                        "group_id"          =>  $arr['group_id'],
                                        "group_name"        =>  $arr['group_name'],
                                        "group_profile_pic" =>  $arr['group_profile_pic'],
                                        "admin_user_id"     =>  $arr['create_user_id'],
                                        "member_detail"     =>  $memeber_detail
                                    );
                    
                }
            }
            return $group_list;
        }
        
        public function update($arr)
        {

            $respArr = array();
            global $connectDb,$gcm;
            
            $group_id   = $arr[0];
            $member_id  = $arr[1];
            $device_id  = $arr[2];
            $group_name = $arr[3];
            $group_pic  = $arr[4];
            $flag1 = $flag2 = false;
            
            if($connectDb->verify_user($member_id,$device_id)!=0){
                if($this->valid_group_member($member_id,$group_id)){
                    $last_activity_date = date('Y-m-d H:i:s');
                    $last_modified_date = $last_activity_date;
                    $update_creteria = array();
                    if($group_name != ""){
                        $flag1 = true;
                        $update_creteria[] = "`group_name`='".$group_name."'";
                    }
                    if($group_pic!=""){
                        $flag2 = true;
                        $update_creteria[] = "`group_profile_pic`='".$group_pic."'";
                    }
                    if(!empty($update_creteria)){
                        $update_creteria[] = "`last_activity_date`='".$last_activity_date."'";
                        $update_creteria[] = "`last_modified_date`='".$last_modified_date."'";
                        $query  = "UPDATE `user_group`
                                   SET ".implode(",",$update_creteria)."
                                   WHERE `group_id`=". $group_id;
                            
                        mysql_query($query);
                            
                        if(mysql_affected_rows()>0){
                            $respArr['respStatus']  = 200;
                            $respArr['respMsg']     = "Group details updated successfully";
                                
                            $members = mysql_fetch_assoc(mysql_query("SELECT members_id FROM user_group WHERE group_id=".$group_id));
                                
                            $query3  = "SELECT `dcm_code`,`user_id`
                                        From   `user`
                                        WHERE  `reg_status`=1 and `user_id` in (".$members['members_id'].")";
                                
                            $resultSet3 = mysql_query($query3);
                                
                            $receiver_dcm_keys = array();
                            $gcm_member_data = array();
                            if(mysql_numrows($resultSet3)>0){
                                while ($records = mysql_fetch_assoc($resultSet3)){
                                    if($records['user_id']!=$member_id)
                                        $receiver_dcm_keys[]    =   $records['dcm_code'];
                                }
                                
                                $gcm_notification = array();
                                $gcm_notification['GCM_NOTI_TYPE']  =   18;
                                $gcm_notification['GCM_DATA_PACKET']=   array("group_id"    =>  $group_id,
                                                                              "group_name"  =>  $group_name,
                                                                              "name_changed"=>  $flag1,
                                                                              "pic_changed" =>  $flag2
                                                                            );
                                //print_r($gcm_notification);
                                $gcm->send_notification($receiver_dcm_keys,$gcm_notification);
                            }
                            
                        }else{
                            $respArr['respStatus']  = 400;
                            $respArr['respMsg']     = "Group details did not updated. Try again";
                        }
                    }else{
                        $respArr['respStatus']  = 400;
                        $respArr['respMsg']     = "Group details did not updated. Try again";
                    }
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized member of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;    
        }
        
        public function get_group_image($arr)
        {
            $respArr = array();
            global $connectDb,$gcm;
            
            $group_id   = $arr[0];
            $member_id  = $arr[1];
            $device_id  = $arr[2];
            
            if($connectDb->verify_user($member_id,$device_id)!=0){
                if($this->valid_group_member($member_id,$group_id)){
                    $query  = "SELECT `group_profile_pic`
                            From `user_group`
                            WHERE `group_id` = ".$group_id;
                    $result = mysql_fetch_assoc(mysql_query($query));
                    $respArr['respStatus'] = 200;
                    $respArr['group_id'] = $group_id;
                    $respArr['group_pic'] = $result ['group_profile_pic'];
                }else{
                    $respArr['respStatus']  = 400;
                    $respArr['respMsg']     = "You are not authorized member of this group";
                }
            }else{
                $respArr['respStatus']  = 400;
                $respArr['respMsg']     = "You are not authorized to make this request";
            }
            return $respArr;    
        }
        
        
        public function removeMember($group_id,$member_id,$remove_member_id)
        {
            $query = "SELECT `active_members_id`,`inactive_members_id` FROM `user_group` WHERE `group_id`=".$group_id;
                
            $recordset = mysql_query($query);
                
            $group_record = mysql_fetch_array($recordset);
                
            $active_members = $group_record['active_members_id'];
                
            $inactive_members = $group_record['inactive_members_id'];
                
            $active_members = str_replace($remove_member_id,"",$active_members);
            $active_members = str_replace(",,",",",$active_members);
            $active_members = rtrim($active_members,",");
                
            $inactive_members = str_replace($remove_member_id,"",$inactive_members);
            $inactive_members = str_replace(",,",",",$inactive_members);
            $inactive_members = rtrim($inactive_members,",");
                
            $last_modified_date = date('Y-m-d H:i:s');
                
            $query = "UPDATE `user_group` SET `active_members_id`='".$active_members."',`inactive_members_id`='".$inactive_members."', `last_modified_date`='".$last_modified_date."' WHERE `group_id`=". $group_id ." and (`active_members_id` like '%".$member_id."%' OR `inactive_members_id` like '%".$member_id."%')";
                
            mysql_query($query);
                
            if(mysql_affected_rows()>0){
                return true;
            }else{
                return false;
            }
        }
    }
        
    /*
        
        $usergroup->push("Group5","groupprofilepic/default/default1.png",1,date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),1,":1:,:4:","");
        echo "<br><pre>";
        print_r($usergroup->get("1",":2:"));
        echo "</pre>";
        
        $usergroup->removeMember(1,1,":2:");
        
        echo $usergroup->removeMember(1,":1:",":3:");
        
        echo $usergroup->updateGroupName("1","NewNameOfGroup",":1:");
        
        echo $usergroup->updateGroupProfilePic("1","sdfsdf/asdfdsf/s.df",":1:");
        
        echo $usergroup->updateGroupActivity("1",":1:");
        
    */
    
?>
