<?php

class Model_user extends CI_Model {

    private $primary_key;
    private $main_table;
    public $errorCode;
    public $errorMessage;

    public function __construct() {
        parent::__construct();
        $this->main_table = "user";
        $this->primary_key = "iUserId";
    }

    function insert($data = array()) {

        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }

        $this->db->insert($this->main_table);

        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function update($data = array(), $where = '') {
        $this->db->where($where);
        $res = $this->db->update($this->main_table, $data);
//        echo $this->db->last_query();exit;   

        $rs = mysqli_affected_rows();
        return $rs;
    }

    function getData($fields = '', $join_ary = array(), $condition = '', $orderby = '', $groupby = '', $having = '', $climit = '', $paging_array = array(), $reply_msgs = '', $like = array()) {

        if ($fields == '') {
            $fields = "*";
        }

        if (trim($fields != '')) {
            $this->db->select($fields);
        }
        $this->db->start_cache();

        if (trim($condition) != '') {
            $this->db->where($condition);
        }
        if (trim($groupby) != '') {
            $this->db->group_by($groupby);
        }
        if (trim($having) != '') {
            $this->db->having($having);
        }
        if ($orderby != '' && is_array($paging_array) && count($paging_array) == "0") {
            $this->db->order_by($orderby);
        }

        $this->db->from($this->main_table);
        $this->db->stop_cache();
        $list_data = $this->db->get()->result_array();
        $this->session->set_userdata(array('query' => $this->db->last_query()));
        $this->db->flush_cache();
        //print_r($list_data);
        //$this->db->last_query();exit;
        return $list_data;
    }

    function delete($where = '') {
        $this->db->where($where);
        $this->db->delete($this->main_table);
        //  echo $this->db->last_query();exit;
        return 'deleted';
    }

    public function authenticate($vUserName, $uPwd) {

        $ext = 'vPassword = "' . $uPwd . '" AND vContactNo = "' . $vUserName . '"';
        $this->db->select('iUserId,vFirstName,vEmail,vPassword,vContactNo,vAddress1,eStatus');
        $this->db->from($this->main_table);
        $this->db->where($ext);
        $result = $this->db->get();
        $record = $result->result_array();

        if (is_array($record) && count($record) > 0) {
            if ($record[0]['eStatus'] == 'Inactive') {
                $this->errorCode = 2;
                $this->errorMessage = 'Your account has been not activated, Contact to administrator.';
            } else {
                $this->_id = $record[0]["iUserId"];
                //  $record->role = "owner";
                $this->session->set_userdata("iUserId", $record[0]["iUserId"]);
                $sessionLoginData['iUserId'] = $record[0]['iUserId'];
                $sessionLoginData['dLoginDate'] = date('Y-m-d H:i:s');

                //print_r($record);
//                $this->session->set_userdata("iLogId", $iLogId);
                $this->errorCode = 1;
            }
        } else {
            $this->errorCode = 0;
            $this->errorMessage = 'Contact or Password are incorrect.';
        }

        $error['errorCode'] = $this->errorCode;
        $error['errorMessage'] = $this->errorMessage;

        return $error;
    }

    function query($sql) {

        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    function getForgotPassword($vContact) {
        $ext = '(vContactNo = "' . $vContact . '")';
        $this->db->select('*');
        $this->db->from($this->main_table);
        $this->db->where($ext);
        $result = $this->db->get();
        $record = $result->result_array();
        //echo $this->db->last_query();exit;
        return $record;
    }

    public function checkDuplicate($table = 'user_master', $field = '', $value = '') {
        if ($field != '' && $value != '') {
            $this->db->select($field);
            $this->db->from($table);
            $this->db->where($field, $value);
            $user_data = $this->db->get()->result_array();

            if (is_array($user_data) && count($user_data) > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function getUserValidation($usertype = '', $value = '', $cond = '') {

        if ($usertype != '' && $value != '') {
            $this->db->select($usertype);
            $this->db->from($this->main_table);
            if ($cond != '') {
                $this->db->where($cond);
            }

            if (is_array($usertype)) {
                foreach ($usertype as $key => $type_value) {
                    $this->db->where($type_value, $value[$key]);
                }
            } else {
                $this->db->where($usertype, $value);
            }

            $user_data = $this->db->get()->result_array();
//            echo $this->db->last_query();exit;
            if (is_array($user_data) && count($user_data) > 0) {
                return "false";
            } else {
                return "true";
            }
        } else {
            return "false";
        }
    }
    
    function getContactValidation($usertype = '', $value = '', $cond = '') {

        if ($usertype != '' && $value != '') {
            $this->db->select($usertype);
            $this->db->from($this->main_table);
            if ($cond != '') {
                $this->db->where($cond);
            }

            if (is_array($usertype)) {
                foreach ($usertype as $key => $type_value) {
                    $this->db->where($type_value, $value[$key]);
                }
            } else {
                $this->db->where($usertype, $value);
            }

            $user_data = $this->db->get()->result_array();
//            echo $this->db->last_query();exit;
            if (is_array($user_data) && count($user_data) > 0) {
                return "true";
            } else {
                return "false";
            }
        } else {
            return "false";
        }
    }

    function getaddress($lat, $lng) {
        $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&sensor=false');

        $output = json_decode($geocode);
        for ($j = 0; $j < count($output->results[0]->address_components); $j++) {
            //echo ''.$output->results[0]->address_components[$j]->types[0].':   '.$output->results[0]->address_components[$j]->long_name.'<br/>';
            if ($output->results[0]->address_components[$j]->types[0] == "locality") {
                $GetCity = $output->results[0]->address_components[$j]->long_name;
            }
            if ($output->results[0]->address_components[$j]->types[0] == "sublocality") {
                $GetArea = $output->results[0]->address_components[$j]->long_name;
            }
            if ($output->results[0]->address_components[$j]->types[0] == "country") {
                $GetCountry = $output->results[0]->address_components[$j]->long_name;
            }
            if ($output->results[0]->address_components[$j]->types[0] == "postal_code") {
                $GetZipcode = $output->results[0]->address_components[$j]->long_name;
            }
        }
        $this->db->select('Country_Id');
        $this->db->where('Country_Name', $GetCountry);
        $rescid = $this->db->get('country')->result_array();
        $res = array("city" => $GetCity, "area" => $GetArea, "country" => $GetCountry, "zip" => $GetZipcode, "countryId" => $rescid[0]['Country_Id']);
        //pr($res);
        return $res;
    }

    function getCountryDetail($id = '') {
        $this->db->select('*');
        if ($id != '') {
            $this->db->where('Country_Id', $id);
        }
        $array = $this->db->get('country')->result_array();
        return $array;
    }

    function getAddressLatLng($myaddress) {
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address=$myaddress&sensor=false";
        //get the content from the api using file_get_contents
        $getmap = file_get_contents($url);
        //the result is in json format. To decode it use json_decode
        $googlemap = json_decode($getmap);
        //get the latitute, longitude from the json result by doing a for loop
        foreach ($googlemap->results as $res) {
            $address = $res->geometry;
            $latlng = $address->location;
            return $latlng;
            //$formattedaddress = $res->formatted_address;
        }
    }

    function insertMessage($senderid, $recieverid, $message, $uName, $uContact) {
        $this->db->set('sender_id', $senderid);
        $this->db->set('receiver_id', $recieverid);
        $this->db->set('message', $message);
        $this->db->set('sender_name', $uName);
        $this->db->set('contact_no', $uContact);
        $this->db->set('status', 'true');
        $this->db->set("time", "NOW()", false);

        $this->db->insert('messages');
        $insertId = $this->db->insert_id();
        return $insertId;
    }

    function updateData($table, $data = array(), $where = '') {
        $this->db->where($where);
        $res = $this->db->update($table, $data);
        //echo $this->db->last_query();exit;  
        $rs = mysql_affected_rows();
        return $rs;
    }

    function deleteData($table = '', $where = '') {
        $this->db->where($where);
        $this->db->delete($table);
        //  echo $this->db->last_query();exit;
        return true;
    }

    function changeStatus($table, $primaryField, $id, $st) {
        $this->db->set('eStatus', $st);
        $this->db->where($primaryField, $id);
        $res = $this->db->update($table);
        //echo $this->db->last_query();exit;
        $rs = mysql_affected_rows();
        return $rs;
    }

    public function system_setting($eConfigType) {
        $this->db->select('*');
        $this->db->from('setting');
        $this->db->where("eConfigType", $eConfigType);
        $this->db->where("eStatus", 'Active');
        $this->db->order_by("iOrderBy", 'ASC');
        $result = $this->db->get()->result_array();
        return $result;
    }

    public function update_system_setting($key, $value) {
        $this->db->set('vValue', $value);
        $this->db->where('vName', $key);
        $res = $this->db->update('setting');
//        echo $this->db->last_query();exit;
//        $rs = mysql_affected_rows();
        return true;
    }

//    function getSearchResults($keyword){
////        $this->db->like('vFirstName',$keyword,'%');
////        return $this->db->get('admin')->result();
//        
//         $query = "SELECT * FROM admin WHERE vFirstName like '$keyword%'";
//         return $this->db->query($query)->result();
//    }

    function getVerification() {
        $id = $this->session->userdata('iUserId');
        $this->db->select('eVerify');
        $this->db->where('iUserId', $id);
        $result = $this->db->get($this->main_table)->result_array();
        return $result[0]['eVerify'];
    }

    function expireEvent($data = array(), $where = '') {
        $rs = 1;
        if ($where != '') {
            $this->db->where($where);
            $res = $this->db->update('location', $data);
            //echo $this->db->last_query();exit;  
            $rs = mysql_affected_rows();
        }
        return $rs;
    }

}
