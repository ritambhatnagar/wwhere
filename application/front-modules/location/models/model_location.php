<?php

class Model_location extends CI_Model {

    private $primary_key;
    private $main_table;
    public $errorCode;
    public $errorMessage;

    public function __construct() {
        parent::__construct();
        $this->main_table = "location";
        $this->primary_key = "iLocationId";
    }

    function insert($data = array()) {
        $this->db->insert($this->main_table, $data);

        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function update($data = array(), $where = '') {
        $this->db->where($where);
        $res = $this->db->update($this->main_table, $data);
        //$rs = mysqli_affected_rows();
        return $res;
    }

    function getData($fields = '', $join_ary = array(), $condition = '', $orderby = '', $groupby = '', $having = '', $climit = '', $paging_array = array(), $reply_msgs = '', $like = array()) {

        if ($fields == '') {
            $fields = "*";
        }

        if (trim($fields) != '') {
            $this->db->select($fields, false);
        }

        if (trim($condition) != '') {
            $this->db->where($condition);
        }
        if (is_array($join_ary) && count($join_ary) > 0) {

            foreach ($join_ary as $ky => $vl) {
                $this->db->join($vl['table'], $vl['condition'], $vl['jointype']);
            }
        }
        if (trim($climit) != '') {
            $this->db->limit($climit);
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
        $list_data = $this->db->get()->result_array();
        return $list_data;
    }

    function delete($where = '') {
        $this->db->where($where);
        $this->db->delete($this->main_table);
//          echo $this->db->last_query();exit;
        return 'deleted';
    }

    function query($sql) {

        $data = $this->db->query($sql)->result_array();
//        echo $this->db->last_query();exit;
        return $data;
    }

    function getContactsValue($id) {
        $query = "SELECT iContactId, eStatus, vFirstName, vLastName, (SELECT GROUP_CONCAT(vGroup) FROM contact_groups WHERE FIND_IN_SET(iContactGroupId,contact_book.vContactGroupId)) AS ContactType,vEmail FROM contact_book WHERE iCompanyId = '$id' AND isLead = 'No'";
        $result = $this->db->query($query)->result_array();
        return $result;
    }

    function blockList($url) {
        $query = $this->db->get_where('block_url', array('url' => $url));
        return count($query->result());
    }

    function getContactGroup($id = '') {
        $this->db->select('iContactGroupId,vGroup');
        $this->db->from('contact_groups');
        if ($id != '') {
            $this->db->where('iCompanyId', $id);
        }
        $data = $this->db->get()->result_array();
        return $data;
    }

    public function getUserValidation($usertype = '', $value = '', $cond = '') {

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
                echo "false";
            } else {
                echo "true";
            }
        } else {
            echo "false";
        }
    }

    function checkEmail($email = '', $phone = '') {
        $id = $this->session->userdata('iCompanyId');
        $query = "SELECT distinct(contact_book.iContactId) FROM contact_book_fields JOIN contact_book ON contact_book.iContactId = contact_book_fields.iContactId and contact_book.iCompanyId = '$id' WHERE ((contact_book_fields.vField = 'Email' AND contact_book_fields.vValue in $email) or contact_book.vEmail in $email)";
        if ($phone != '') {
            $query .= " OR ((contact_book_fields.vField = 'Phone' AND contact_book_fields.vValue in $phone))";
        }
        $result = $this->db->query($query)->result_array();
        //echo $this->db->last_query();exit;
        if (count($result) > 0) {
            $result[0]['tot'] = count($result);
        } else {
            $result[0]['tot'] = 0;
        }
        return $result;
    }

    function categoryList($catType = '', $where = '', $fields = '') {
        if ($fields != '') {
            $this->db->select($fields);
        } else {
            $this->db->select('*');
        }
        if ($catType != '') {
            $this->db->where('iParentId', $catType);
        }
        if ($where != '') {
            $this->db->where($where);
        }
        $data = $this->db->get('category')->result_array();
//        echo $this->db->last_query();exit;
        return $data;
    }

    function getlocationTiming($id, $vDays = '') {
        $this->db->select('*');
        $this->db->where('iLocationId', $id);
        if ($vDays != '') {
            $this->db->where('vDays', $vDays);
        }
        $data = $this->db->get('location_timings')->result_array();
        return $data;
    }

    function updateTiming($data) {
        $this->db->where("vDays", $data['vDays']);
        $this->db->where("iLocationId", $data['iLocationId']);
        $res = $this->db->update("location_timings", $data);
        $rs = mysql_affected_rows();
        return $rs;
    }

    function insertTiming($data) {
        $this->db->insert('location_timings', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function urlName($vUrlName, $mode = 'add', $id = '') {
        $this->db->select('vUrl');
        $this->db->where('vUrl', $vUrlName);
        if ($mode != 'add' && $id != '') {
            //$this->db->where('vUrl <> "' . $vUrlName . '"');
            $this->db->where('iTypeId <> "' . $id . '"');
        }
        $data = $this->db->get('domain')->result_array();
//        echo $this->db->last_query();exit;
        return $data;
    }

    function getlocationImage($id) {
        $this->db->select('*');
        $this->db->where('iLocationId', $id);
        $this->db->order_by('eCoverImage');
        $data = $this->db->get('location_images')->result_array();
        return $data;
    }

    function deletelocationImageLogo($id) {
        $this->db->delete('location_images', array('iLocationId' => $id, 'eCoverImage'=>'Yes')); 
    }

    function getlocationImageLogo($id) {
        $this->db->select('*');
        $this->db->where('iLocationId', $id);
        $this->db->where('eCoverImage', 'Yes');
        $this->db->order_by('eCoverImage');
        $data = $this->db->get('location_images')->result_array();
        return $data;
    }

    function image_delete($id) {
        $this->db->where('iImageId', $id);
        $this->db->delete('location_images');
        return 'deleted';
    }

    function updateLocationImage($data, $where) {
        $this->db->where($where);
        $res = $this->db->update("location_images", $data);
        //$rs = mysql_affected_rows();
        return $res;
    }

    function updateDomain($newUrl, $type_id, $etype) {
        $this->db->set('vUrl', $newUrl);
        $this->db->where('eType', $etype);
        $this->db->where('iTypeId', $type_id);
        $res = $this->db->update("domain");
        return $res;
    }

    function insertDomain($data) {
        $this->db->insert('domain', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function getDomainINLocation($locationid) {
        $this->db->select('vUrlName');
        $this->db->where('iLocationId', $locationid);
        $data = $this->db->get('location')->result_array();
        return $data[0]['vUrlName'];
    }

    function getTotalLocations() {
        $id = $this->session->userdata('iUserId');
        $query = "SELECT COUNT(*) as tot FROM " . $this->main_table . " WHERE iUserId = '$id' AND eStatus = 'Active'";
        $result = $this->db->query($query)->result_array();
        return $result;
    }

    function insertData($data = array(), $table = '') {
        if ($table != '') {
            $this->db->insert($table, $data);
        }

        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    function getGL($where = '', $fields = '') {
        if ($fields == '') {
            $fields = "*";
        }

        $this->db->select($fields);
        $this->db->where($where);
        $tot = $this->db->get('group_location')->result_array();
        return $tot;
    }

    function removeGL($where = '') {
        $this->db->where($where);
        $tot = $this->db->delete('group_location');
        //echo $this->db->last_query();exit;
        return $tot;
    }

    function getDomain($val = '') {
        $this->db->select('*');
        if ($val != '') {
            $this->db->where('vUrl', $val);
        }
        $data = $this->db->get('domain')->result_array();
        return $data;
    }

    function getDistancewiseLocation($currentLat, $currentLong, $distance, $locid) {
        
        /*SELECT *, ( 6371 * acos( cos( radians(23.0347254) ) * cos( radians( vLatitude ) ) * cos( radians( vLongitude ) - radians(72.5324346) ) + sin( radians(23.0347254) ) * sin( radians( vLatitude ) ) ) ) AS distance FROM location where `iLocationId` IN (86,88,89,235,87,90,233) ORDER BY distance ASC;*/

        $this->db->select("location.*,round((6371 * acos(cos(radians($currentLat)) * cos(radians(location.vLatitude)) 
                                            * cos(radians('$currentLong') - radians(location.vLongitude)) + sin(radians('$currentLat')) 
                                            * sin(radians(location.vLatitude)))),2) AS distance", false);

        $this->db->where("iLocationId in ($locid)");
        $this->db->order_by('distance', 'ASC');
        $data = $this->db->get('location')->result_array();
        //echo $this->db->last_query();exit;
        return $data;
    }

    function deleteData($table = '', $where = '') {
        $this->db->where($where);
        $this->db->delete($table);
        return 'deleted';
    }

    function top_Category($city) {
        if($city == "") 
            $query = "SELECT top_category.* FROM (SELECT (SELECT COUNT(*) FROM location WHERE location.iCategoryId = category.iCategoryId AND eStatus = 'Active') AS tot_cat, category.* FROM category ORDER BY  `tot_cat` DESC  LIMIT 10) AS top_category";
        else
            $query = "SELECT top_category.* FROM (SELECT (SELECT COUNT(*) FROM location WHERE location.iCategoryId = category.iCategoryId AND eStatus = 'Active' AND vCity = '" . $city ."') AS tot_cat, category.* FROM category ORDER BY  `tot_cat` DESC  LIMIT 10) AS top_category";
        $data = $this->db->query($query)->result_array();
//        echo $this->db->last_query();exit;
        return $data;
    }

    function getAvailability($user_id = '',$location_id = '') {
        $this->db->select('count(lm.iImageId) as tot');
        $this->db->from('location_images lm');
        if(!empty($location_id)){
            $this->db->where('lm.iLocationId',$location_id);
        }
        $result = $this->db->get()->result_array();
        return $result;
    }
    
    function remove_image($name = '', $id = ''){
        $this->db->where(array('vImage' => $name, 'iLocationId' => $id));
        $this->db->delete('location_images');
        return 'deleted';
    }
    
    function delete_image($id = ''){
        $this->db->where('iImageId',$id);
        $this->db->delete('location_images');
        return 'deleted';
    }

    function delete_image_name($id = '', $name = ''){
        $this->db->where('iImageId',$id);
        $this->db->delete('location_images');
        return 'deleted';
    }


    function createthumb($name,$filename,$new_w,$new_h){
        $system=explode('.',$name);
        if (preg_match('/jpg|jpeg/',$system[1])){
            $src_img=imagecreatefromjpeg($name);
        }
        if (preg_match('/png/',$system[1])){
            $src_img=imagecreatefrompng($name);
        }

        $old_x=imageSX($src_img);
        $old_y=imageSY($src_img);

        $new_w = ($new_h * $old_x) / $old_y;

        if ($old_x > $old_y) {
            $thumb_w=$new_w;
            $thumb_h=$old_y*($new_h/$old_x);
        }
        if ($old_x < $old_y) {
            $thumb_w=$old_x*($new_w/$old_y);
            $thumb_h=$new_h;
        }
        if ($old_x == $old_y) {
            $thumb_w=$new_w;
            $thumb_h=$new_h;
        }

        $dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
        imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);         
    
        if (preg_match("/png/",$system[1]))
        {   
            imagepng($dst_img,$filename); 
        } else {
            imagejpeg($dst_img,$filename); 
        }

        imagedestroy($dst_img); 
        imagedestroy($src_img); 
    }
    
    function compress($source, $destination, $quality) {

        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') 
            $image = imagecreatefromjpeg($source);

        elseif ($info['mime'] == 'image/gif') 
            $image = imagecreatefromgif($source);

        elseif ($info['mime'] == 'image/png') 
            $image = imagecreatefrompng($source);

        imagejpeg($image, $destination, $quality);

        return $destination;
    }
}
