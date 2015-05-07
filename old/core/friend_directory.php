<?php

    include_once 'configuration.php';
    
    class FriendDirectory
    {
        /*
        private $directory_id;
        private $user_id;
        private $friend_user_id;
        private $is_mutual;
        
        public function __construct($directory_id="",$user_id="",$friend_user_id="",$is_mutual="")
        {
            $this->directory_id="";
            $this->user_id="";
            $this->friend_user_id="";
            $this->is_mutual="";
        }
        */
        
        public function filterFriends($user_id,$list_of_numbers)
        {
            $friends_added = 0;
            $friends_not_added = 0;
            $user_numbers = array();
            
            $user_numbers = explode(",",$list_of_numbers);
            
            foreach($user_numbers as $user_number)
            {
                $resultSet1 = mysql_query("SELECT `user_id` FROM `user` WHERE `mobile_number` like '". $user_number ."'");
                
                $friend_user_id = array();
                
                if(mysql_numrows($resultSet1)==1)
                {
                    $friend_user_id = mysql_fetch_array($resultSet1);
                
                    $resultSet2 = mysql_query("SELECT `directory_id` FROM `friend_directory` WHERE `user_id` = ". $friend_user_id['user_id'] ." and `friend_user_id`=".$user_id);
                    
                    $directory_id = array();
                    
                    if(mysql_numrows($resultSet2)==1)
                    {
                        //It means the current $user_number has already added the caller of function as friend
                        $directory_id = mysql_fetch_array($resultSet2);
                        
                        $this->updateIsMutual($directory_id['directory_id']);
                        
                        $friends_added++;
                        
                    }
                    else
                    {
                        $this->push($user_id,$friend_user_id['user_id'],'0');
                        $friends_added++;
                    }
                }
                else
                {
                    $friends_not_added++;
                }
            }
            
            return $friends_added." added <br>".$friends_not_added." not added.";
        }
        
        public function refreshFriends($user_id,$user_contacts)
        {
            $user_data = array(array());
            include_once ('user.php');
            $fetchdata = new User();
            echo "<pre>";
            $user_data = $fetchdata->getUserArrayByMobile($user_contacts);
            print_r($user_data);
            echo "</pre>";
            return $user_data;
        }
        
        protected function updateIsMutual($directory_id)
        {
            $query = "UPDATE `friend_directory` SET `is_mutual`=1 WHERE `directory_id`=".$directory_id;
            if(mysql_query($query))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        public function push($user_id,$friend_user_id,$is_mutual)
        {
            $query = "INSERT INTO `friend_directory`(`user_id`,`friend_user_id`,`is_mutual`)
                                   values('".$user_id."','".$friend_user_id."','".$is_mutual."')";
                
            if(mysql_query($query))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        public function update($directory_id,$user_id,$friend_user_id,$is_mutual)
        {
            $query = "UPDATE `friend_directory` SET `user_id`=".$user_id.",`friend_user_id`=".$friend_user_id.",`is_mutual`=". $is_mutual ."
                                   WHERE `directory_id`=".$directory_id;
                
            if(mysql_query($query))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        public function removeFriendContact($user_id,$friend_contact){}
    }
    
    $fd = new FriendDirectory();
    
    $fd->refreshFriends(2,"+919974640966,+8866770482,+919408754092,+919979230069");
?>