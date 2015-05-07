<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Location extends MX_Controller {

    public function __construct() {
        parent::__construct();
        // $dat=array();
        //error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
        error_reporting(1);
//        $this->general->checkSession();
        $this->load->model('model_location');
    }

    public function locations() {
        $getvar = $this->input->get();

        $search = isset($getvar['myl']) ? $getvar['myl'] : '';

        $this->load->model('group/model_group');
        $id = $this->session->userdata('iUserId');
        $fields = "location.*,(SELECT GROUP_CONCAT(group_location.iGroupId) FROM group_location WHERE group_location.iLocationId = location.iLocationId)AS select_id, (SELECT vGroup 
FROM group_master JOIN group_location ON group_location.iGroupId = group_master.iGroupId 
WHERE group_location.iLocationId = location.iLocationId) AS group_name,category.vCategory";

        $extcond = 'location.iUserId = "' . $id . '" and location.eStatus = "Active"';

        if ($search != '') {
            $extcond .= ' and (vCategory like ("%' . $search . '%") or vTags like ("%' . $search . '%") or vUrlName like ("%' . $search . '%") or vName like ("%' . $search . '%") or vCity like ("%' . $search . '%"))';
        }

        $join_arr = array(array('table' => 'category', 'condition' => 'category.iCategoryId = location.iCategoryId and category.eStatus = "Active"', 'jointype' => 'left'));
        $data['data'] = $this->model_location->getData($fields, $join_arr, $extcond);
        //echo $this->db->last_query();exit;
        $extcond = 'iUserId = "' . $id . '" and eStatus = "Active"';
        $data['group_data'] = $this->model_group->getData('*', array(), $extcond);
        $data['search'] = $search;
        $this->load->view('location', $data);
    }

//
//    public function createlocation() {
//
//        $this->load->view('createlocation');
//    }

    public function createlocation() {
        $getvar = $this->input->get();

        $this->session->set_userdata('rand_name', time());

        if ($getvar != '') {
            $eType = urldecode($this->general->decryptData($getvar['t']));
        }

        $imageArray = array();
        if (isset($getvar['id']) && $getvar['id'] != '') {
            $id = $this->general->decryptData($getvar['id']);
            $extcond = 'iLocationId = "' . $id . '"';
            $data['data'] = $this->model_location->getData('*', array(), $extcond);

            $shiftTiming = $this->model_location->getlocationTiming($id);
            $data['all'] = $shiftTiming;

            $imageArray = $this->model_location->getlocationImage($id);
        }
        $days = array("Monday To Friday", "Saturday", "Sunday");
        $data['days'] = $days;
        $data['eType'] = $eType;
        $data['imageArray'] = $imageArray;
        if ($eType != '') {
            $ext_Cond = 'vCategory ="' . $eType . '"';
            $fields = 'iCategoryId';
            $result = $this->model_location->categoryList('', $ext_Cond, $fields);
            $category_cond = 'iParentId ="' . $result[0]['iCategoryId'] . '"';
            $categoryList = $this->model_location->categoryList('', $category_cond);
        }

        $data['categoryList'] = $categoryList;
        $this->load->view('location_add_edit', $data);
    }

    public function cropUpload() {

        $img = $_POST['base64data'];
        $img = substr(explode(";",$img)[1], 7);
        $r = time() . time();
        $uploadPath = $this->config->item('upload_path') . 'temp/' . $r;
        $this->general->createfolder($uploadPath);
        $rname = time() . ".png";        
        $this->session->set_userdata('rand_name_logo', $r);
        file_put_contents($uploadPath . '/' . $rname, base64_decode($img));

        $this->session->set_userdata('rand_name_logo_image', $uploadPath . '/' . $rname);
//        $this->model_location->createthumb($uploadPath . '/' . $rname, $uploadPath . '/' . $rname, 60,60);
        
    }

    public function newLocationInsert() {
        $postvar = $this->input->post();
       $postvar['vLogo'] = '';

        $mode = $postvar['mode'];
        $opentimeArray = $postvar['opentime'];
        $closetimeArray = $postvar['closetime'];
        $statusArray = $postvar['statusArray'];
        if (is_array($postvar['opentime']) && $postvar['eType'] == 'Private') {
            $del_cond = 'iLocationId ="' . $postvar['iLocationId'] . '"';
            $result = $this->model_location->deleteData(location_timings, $del_cond);
        }
        unset($postvar['mode']);
        unset($postvar['opentime']);
        unset($postvar['closetime']);
        unset($postvar['statusArray']);
        unset($postvar['imageredio']);

        if ($postvar['eType'] == 'Event') {
            $postvar['dtStartDate'] = date("Y-m-d", strtotime($postvar['dtStartDate']));
            $postvar['dtFinishDate'] = date("Y-m-d", strtotime($postvar['dtFinishDate']));

            $postvar['vStartTime'] = date("H:i:s", strtotime($postvar['vStartTime']));
            $postvar['vFinishTime'] = date("H:i:s", strtotime($postvar['vFinishTime']));
        } else {
            unset($postvar['dtStartDate']);
            unset($postvar['dtFinishDate']);
            unset($postvar['vStartTime']);
            unset($postvar['vFinishTime']);
        }

        if ($postvar['eType'] == 'Private') {
            unset($postvar['dtStartDate']);
        }

        $postvar['iCategoryId'] = ($postvar['eType'] != 'Private') ? $postvar['iCategoryId'] : '0';

        $postvar['vUrlName'] = strtolower($postvar['vUrlName']);
        if ($postvar['iLocationId'] == '' && $mode == 'add') {
            unset($postvar['iLocationId']);
            $postvar['iUserId'] = $this->session->userdata('iUserId');
            $locationid = $this->model_location->insert($postvar);

            $dataDomain['eType'] = 'Location';
            $dataDomain['iTypeId'] = $locationid;
            $dataDomain['vUrl'] = $postvar['vUrlName'];
            $this->model_location->insertDomain($dataDomain);
        } else {
            $extcond = 'iLocationId = "' . $postvar['iLocationId'] . '"';
            $this->model_location->update($postvar, $extcond);
            $locationid = $postvar['iLocationId'];

            //$old_domain = $this->model_location->getDomainINLocation($locationid);

            $result = $this->model_location->updateDomain($postvar['vUrlName'], $locationid, 'Location');
        }

        if ($postvar['eType'] == 'Public') {
            $this->timing_action($opentimeArray, $closetimeArray, $statusArray, $locationid);
        }

        if (!empty($locationid)) {
            $destination = $this->config->item('upload_path') . 'location/' . $locationid;
            $this->general->createfolder($destination);
            $source = $this->config->item('upload_path') . 'temp/' . $this->session->userdata('rand_name');
            $sourceLogo = $this->config->item('upload_path') . 'temp/' . $this->session->userdata('rand_name_logo');
            $files_arr = $this->general->deleteDir($source, true, $destination);
            $files_arr_logo = $this->general->deleteDir($sourceLogo, true, $destination);
            if (is_array($files_arr) && count($files_arr) > 0) {
                foreach ($files_arr AS $key => $value) {
                    $dataimg['eCoverImage'] = 'No';
                    $dataimg['vImage'] = $value;
                    $dataimg['iLocationId'] = $locationid;
                    $this->model_location->insertData($dataimg, 'location_images');
                }
            }
            if (is_array($files_arr_logo) && count($files_arr_logo) > 0) {
                //var_dump($files_arr_logo);
                $dataImages = $this->model_location->getlocationImageLogo($locationid);
                if(count($dataImages) >= 1) {
                    $this->model_location->deletelocationImageLogo($locationid);
                }
                foreach ($files_arr_logo AS $key => $value) {
                    $dataimg['eCoverImage'] = 'Yes';
                    $dataimg['vImage'] = $value;
                    $dataimg['iLocationId'] = $locationid;
                    $this->model_location->insertData($dataimg, 'location_images');
                }
            }
        }
        $this->session->unset_userdata('rand_name');
        $this->session->unset_userdata('rand_name_logo');

        $return['mode'] = 'edit';
        $return['locationid'] = $locationid;
        $return['vUrlName'] = $postvar['vUrlName'];
        echo json_encode($return);
        exit;
    }

    public function timing_action($opentimeArray, $closetimeArray, $statusArray, $locationid) {

        if (is_array($statusArray) && count($statusArray) > 0) {

            foreach ($statusArray as $key => $value) {
                $opentime = $closetime = '00:00:00';

                if ($value == 'open') {
                    $opentime = date("H:i:s", strtotime($opentimeArray[$key]));
                    $closetime = date("H:i:s", strtotime($closetimeArray[$key]));
                }
                $newpostvar["iLocationId"] = $locationid;
                $newpostvar["vDays"] = $key + 1;
                $newpostvar["eType"] = ucfirst($value);
                $newpostvar["vOpenTime"] = $opentime;
                $newpostvar["vCloseTime"] = $closetime;

                $timeExistArray = $this->model_location->getlocationTiming($locationid, $key + 1);
                if (count($timeExistArray) > 0) {
                    //update
                    $rply = $this->model_location->updateTiming($newpostvar);
                } else {
                    // insert
                    $rply = $this->model_location->insertTiming($newpostvar);
                }
            }
        }
        return 1;
    }

    function urlName() {
        $postvar = $this->input->post();
        $haystack = array('index', 'createlocation', 'locations', 'my_profile', 'forgotpassword', 'register', 'verification', 'user', 'login', 'serviceAuth', 'reset_pass', 'logout', 'chksession1', 'home', 'dashboard', 'search', 'fb', 'search_result_page', 'user_mgmt', 'user_add_edit', 'autocomplete', 'search_result', 'system_setting', 'select_city', 'checkContact', 'category_mgmt', 'category_add_edit', 'groups', 'group_add_edit', 'urlName', 'search_location', 'search_sub_location', 'similar_location', 'inbox', 'country_mgmt', 'country_add_edit', 'contact_us', 'forbidden', '');
        $postvar['vUrlName'] = strtolower($postvar['vUrlName']);

        if($postvar['vUrlName'] == "") {
            $return = "empty";
            echo $return;
            exit;
        }

        if (in_array($postvar['vUrlName'], $haystack)) {
            $return = 'false';
            echo $return;
            exit;
        } else {
            $locationid = ($postvar['iLocationId'] != '' && $postvar['mode'] == 'edit') ? $postvar['iLocationId'] : '';
            $reply = $this->model_location->urlName($postvar['vUrlName'], $postvar['mode'], $locationid);
            if (is_array($reply) && count($reply) > 0) {
                $return = 'false';
            } else {
                $blocklist = $this->model_location->blockList($postvar['vUrlName']);       
                if($blocklist > 0) {    
                    $return = 'false';
                } else {
                    $return = 'true';
                }
            }
            echo $return;
            exit;
        }
    }

    function city() {
        $record = geoip_record_by_name('wwhere.is');
        if ($record) {
            print_r($record);
        }
        exit;
    }

    function imageUpload() {
        $filevar = $_FILES;
        $postvar = $this->input->post();
//        pr($postvar);
//        pr($filevar);exit;
//        
        $ImageFile = $_FILES;
//        $locationid = '2';
        $locationid = $postvar['locaid'];

        /* image upload start */

        if ($ImageFile != '') {
            $this->load->library('upload');
            foreach ($ImageFile as $key => $value) {
                if (!empty($value['name'])) {
                    $upload_path = $this->config->item('upload_path') . 'location/' . $locationid;
                    $this->general->createfolder($upload_path);
                    $file_name = $locationid . '_' . $key;
                    $temp_folder_path = str_replace('\\', '/', $upload_path);

                    $upload_config = array(
                        'upload_path' => $temp_folder_path,
                        'allowed_types' => "jpg|jpeg|gif|png",
                        'max_size' => 1028 * 1028 * 2,
                        'file_name' => $file_name,
                        'remove_space' => TRUE,
                        'overwrite' => FALSE
                    );
                    $this->upload->initialize($upload_config);

                    if ($this->upload->do_upload($key)) {
                        $file_info = $this->upload->data();
                        $uploadedFile = $file_info['file_name'];
                        $cover = 'No';
                        $this->db->set('iLocationId', $locationid);
                        $this->db->set('vImage', $uploadedFile);
                        $this->db->set('eCoverImage', $cover);
                        $this->db->insert('location_images');

//                $this->db->set('vImage', $uploadedFile);
//                $this->db->set('eCoverImage', $cover);
//                $this->db->where('iLocationId', $locationid);
//                $this->db->update('location_images');
                    } else {
                        $this->session->set_flashdata('failure', $this->upload->display_errors());

                        redirect($this->config->item('site_url') . 'locations');
                    }
                }
            }
        }

        $imageArray = $this->model_location->getlocationImage($locationid);
        for ($i = 0; $i < count($imageArray); $i++) {
            if ($i + 1 == $postvar['imageredio']) {
                $data['eCoverImage'] = 'Yes';
                $where = 'iImageId = "' . $imageArray[$i]['iImageId'] . '"';
                $this->model_location->updateLocationImage($data, $where);
            }
        }

        /* image upload end * */
        redirect($this->config->item('site_url') . 'locations');
    }

    public function image_delete() {
        $postArray = $this->input->post();
        $ext_cond = "iImageId ='" . $postArray['iImageId'] . "'";
        $sql = 'select vImage,iLocationId from location_images where iImageId = "' . $postArray['iImageId'] . '"';
        $imges = $this->model_location->query($sql);

        $img = 'public/upload/location/' . $imges[0]['iLocationId'] . '/' . $imges[0]['vImage'];
        $img_path = $this->config->item('site_path') . $img;
        if (file_exists($img_path)) {
            unlink($img_path);
        }
        $rply = $this->model_location->image_delete($postArray['iImageId']);
        echo 1;
        exit;
    }

    function delete_data() {
        $postvar = $this->input->post();
        if (isset($postvar['d']) && $postvar['d'] != '') {
            $flaguser_id = $this->general->decryptData(urldecode($postvar['d']));
        }
        $ext_cond = "iLocationId ='" . $flaguser_id . "'";
        $data['eStatus'] = 'Deleted';
        $result = $this->model_location->update($data, $ext_cond);
        $domain_Cond = 'iTypeId ="' . $flaguser_id . '"';
        $this->model_location->deleteData('domain', $domain_Cond);
//        echo $this->db->last_query();exit;
        return $result;
    }

    function group_location() {
        $postvar = $this->input->post();
        $data['iUserId'] = $this->session->userdata('iUserId');
        $data['iLocationId'] = $this->general->decryptData($postvar['lid']);
        $data['iGroupId'] = $this->general->decryptData($postvar['cid']);

        if ($postvar['mode'] == 'Remove') {
            $ext_cond = 'iUserId ="' . $data['iUserId'] . '" and iLocationId ="' . $data['iLocationId'] . '"';
            $result = $this->model_location->removeGL($ext_cond);
            return $result;
            exit;
        }

        $ext_cond = 'iUserId ="' . $data['iUserId'] . '" and iLocationId ="' . $data['iLocationId'] . '"';
        $result = $this->model_location->removeGL($ext_cond);

        $data['dtAddedDate'] = date('Y-m-d H:i:s');
        $result = $this->model_location->insertData($data, 'group_location');
//        pr($data);exit;
        return $result;
    }

    public function location_detail() {
        $vPageCode = end($this->uri->rsegments);
        $getDomain = $this->model_location->getDomain($vPageCode);

/*        $blocklist = $this->model_location->blockList($vPageCode);

        if($blocklist > 0) {
            show_404('location/location1');
        }*/

        $data = $data1 = array();
        $data['categoryName'] = '';
        $data['tags'] = '';

        $currentLat = ($this->session->userdata('currentLat') != '') ? $this->session->userdata('currentLat') : '0.00';
        $currentLong = ($this->session->userdata('currentLong') != '') ? $this->session->userdata('currentLong') : '0.00';


        if (is_array($getDomain) && count($getDomain) > 0) {
            if ($getDomain[0]['eType'] == 'Location') {
                $check_fields = 'eStatus';
                $check_condition = 'iLocationId ="' . $getDomain[0]['iTypeId'] . '"';
                $check_result = $this->model_location->getData($check_fields, array(), $check_condition);
                
                
                if ($check_result[0]['eStatus'] == 'Deleted') {
                    show_404("location/location1");
                }
                $fields = "*";
                $extcond = 'vUrlName = "' . strtolower($vPageCode) . '" and eStatus <> "Deleted"';
                $data1 = $this->model_location->getData($fields, array(), $extcond);
                $this->general->getTitle($this->config->item('SITE_TITLE') . ' - ' . $data1[0]['vName']);
                $data['name'] = $data1[0]['vName'];
                $data['description'] = $data1[0]['vDescription'];
                $data['lat'] = $data1[0]['vLatitude'];
                $data['long'] = $data1[0]['vLongitude'];
                $data1['cityy'] = $data1[0]['vCity'];

                $ext_similar = 'iCategoryId = "' . $data1[0]['iCategoryId'] . '" AND vCity ="' . $data1['cityy'] . '" AND eStatus <> "Deleted" AND location.iLocationId <> "' . $data1[0]['iLocationId'] . '" and location.eType <> "Private"';
                $order_by = 'location.vCity,vCategory,distance';
                $join_arr = array(array('table' => 'location_images', 'condition' => 'location.iLocationId = location_images.iLocationId and location_images.eCoverImage = "Yes"', 'jointype' => 'left'));
                $data['similar'] = $this->model_location->getData('location.*,location_images.vImage as cover_image,(select category.vCategory from category where category.iCategoryId = location.iCategoryId and category.eStatus = "Active") as vCategory,round((6371 * acos(cos(radians("' . $currentLat . '")) * cos(radians(location.vLatitude)) * cos(radians("' . $currentLong . '") - radians(location.vLongitude)) + sin(radians("' . $currentLat . '")) * sin(radians(location.vLatitude)))),2) AS distance', $join_arr, $ext_similar, $order_by, '', '', '2');
                //$data['similar'] = $this->model_location->getData($join_arr, $ext_similar, $order_by, '', '', '2');





//                echo "dabhi".$this->db->last_query();exit;

                $data['timing'] = $this->model_location->getlocationTiming($data1[0]['iLocationId']);
                $data['images'] = $this->model_location->getlocationImage($data1[0]['iLocationId']);
                if ($data1[0]['iCategoryId'] != '') {
                    $catwhere = 'iCategoryId = "' . $data1[0]['iCategoryId'] . '"';
                    $cate = $this->model_location->categoryList('', $catwhere);
                    $data['categoryName'] = $cate[0]['vCategory'];
                }
                $data['tags'] = $data1[0]['vTags'];
                $urltype = 'Location';
            } elseif ($getDomain[0]['eType'] == 'Group') {
                $this->load->model('group/model_group');
                $check_fields = 'eStatus';
                $check_condition = 'iGroupId ="' . $getDomain[0]['iTypeId'] . '"';
                $check_result = $this->model_group->getData($check_fields, array(), $check_condition);
                

                if ($check_result[0]['eStatus'] == 'Inactive') {
                    show_404("group/group1");
                }
                $currentLat = ($this->session->userdata('currentLat') != '') ? $this->session->userdata('currentLat') : '0.00';
                $currentLong = ($this->session->userdata('currentLong') != '') ? $this->session->userdata('currentLong') : '0.00';
                $distance = $this->config->item('DISTANCE');

                $sql = 'SELECT iGroupId, vGroup , (SELECT GROUP_CONCAT(iLocationId) FROM group_location WHERE group_location.iGroupId = group_master.iGroupId) AS location_id FROM group_master WHERE vGroup = "' . $vPageCode . '"';
                $group = $this->model_location->query($sql);

                $locationdata = $this->model_location->getDistancewiseLocation($currentLat, $currentLong, $distance, $group[0]['location_id']);

                $data['name'] = $locationdata[0]['vName'];
                $data['description'] = $locationdata[0]['vDescription'];
                $data['lat'] = $locationdata[0]['vLatitude'];
                $data['long'] = $locationdata[0]['vLongitude'];
                $data1 = $locationdata;

                if ($locationdata[0]['iCategoryId'] != '') {
                    $catwhere = 'iCategoryId = "' . $locationdata[0]['iCategoryId'] . '"';
                    $cate = $this->model_location->categoryList('', $catwhere);
                    $data['categoryName'] = $cate[0]['vCategory'];
                }

                $data['timing'] = $this->model_location->getlocationTiming($locationdata[0]['iLocationId']);
                $data['images'] = $this->model_location->getlocationImage($locationdata[0]['iLocationId']);
                $data['tags'] = $locationdata[0]['vTags'];
                unset($locationdata[0]);

                $ext_similar = 'iCategoryId = "' . $locationdata[0]['iCategoryId'] . '" AND eStatus <> "Deleted" and location.iLocationId <> "' . $data1[0]['iLocationId'] . '" and location.eType <> "Private"';
                $order_by = 'location.vCity,vCategory,distance';
                $join_arr = array(array('table' => 'location_images', 'condition' => 'location.iLocationId = location_images.iLocationId and location_images.eCoverImage = "Yes"', 'jointype' => 'left'));
                $data['similar'] = $this->model_location->getData('location.*,location_images.vImage as cover_image,(select category.vCategory from category where category.iCategoryId = location.iCategoryId and category.eStatus = "Active") as vCategory,round((6371 * acos(cos(radians("' . $currentLat . '")) * cos(radians(location.vLatitude)) * cos(radians("' . $currentLong . '") - radians(location.vLongitude)) + sin(radians("' . $currentLat . '")) * sin(radians(location.vLatitude)))),2) AS distance', $join_arr, $ext_similar, $order_by, '', '', '2');
                //echo "sarthak".$this->db->last_query();exit;                



                //$data['similar'] = $locationdata;
                $urltype = 'Group';
            }
        }

        if (count($data) > 0) {


            $data['data'] = $data1;
            $data['url_type'] = $urltype;
//            pr($data);exit;
        } else {
            show_404("location/location_detail");
        }
        $data['currentLat'] = $currentLat;
        $data['currentLong'] = $currentLong;
        $this->load->view('location_detail', $data);
    }

    public function search_location() {


        if($_GET['s'] === NULL  || $_GET['s'] === "") show_404("location/location1");
        $searchCity = "";
        if($_GET['sc'] === NULL  || $_GET['sc'] === "") {
        	$searchCity = "false";
        }


        $this->load->model('group/model_group');
        $getvar = $this->input->get();

        $currentLat = ($this->session->userdata('currentLat') != '') ? $this->session->userdata('currentLat') : '0.00';
        $currentLong = ($this->session->userdata('currentLong') != '') ? $this->session->userdata('currentLong') : '0.00';
        $search = isset($getvar['s']) ? $getvar['s'] : '';
        $sc = isset($getvar['sc']) ? $getvar['sc'] : '';

        $fields = "location.vName,vCity,location.vLatitude,location.vLongitude,location.iLocationId,vAddress,location.vUrlName,category.vCategory,vTags,location_images.vImage as cover_image,"
                . "round((6371 * acos(cos(radians($currentLat)) * cos(radians(location.vLatitude)) 
                    * cos(radians('$currentLong') - radians(location.vLongitude)) + sin(radians('$currentLat')) 
                    * sin(radians(location.vLatitude)))),2) AS distance";

        $extcond = 'location.eStatus = "Active"';
        if ($search != '') {
            $extcond .= ' AND (((vCategory LIKE ("%' . $search . '%") OR vTags LIKE ("%' . $search . '%") OR vUrlName LIKE ("%' . $search . '%")  OR vName LIKE ("%' . $search . '%")) AND vCity LIKE ("%' . $sc . '%"))  OR vCity LIKE ("%' . $search . '%")) AND location.eStatus = "Active" AND location.eType <> "Private"';
        }
        $join_arr = array(array('table' => 'category', 'condition' => 'category.iCategoryId = location.iCategoryId and category.eStatus = "Active"', 'jointype' => 'left'), array('table' => 'location_images', 'condition' => 'location.iLocationId = location_images.iLocationId and location_images.eCoverImage = "Yes"', 'jointype' => 'left'));
        $order_by = 'location.vCity,category.vCategory,distance';
        $data['data'] = $this->model_location->getData($fields, $join_arr, $extcond, $order_by);
        if (count($data['data']) > 0) {
            foreach ($data['data'] as $key => $value) {
                $result[$key] = array($value['vName'] . ' ' . $value['vCity'], $value['vLatitude'], $value['vLongitude']);
            }
        } else {
            $result[] = array('Session', $this->session->userdata('currentLat'), $this->session->userdata('currentLong'));
        }

        $json_result = json_encode($result);

        $data['searchCity'] = $searchCity;

        if($searchCity == "false"){
	        $data['result'] = [];
        } else {    	
	        $data['result'] = $json_result;
        }

//        echo $this->db->last_query();exit;
        $this->load->view('search_location', $data);
    }

    function sessonlatlng() {
        $postvar = $this->input->post();
        $this->session->set_userdata('currentLat', $postvar['lat']);
        $this->session->set_userdata('currentLong', $postvar['long']);
        $return['city'] = '';
        $return['country'] = '';
        if ($this->session->userdata('currentCity') == '') {
            $return['city'] = $city = $this->getCityByLatLong($this->session->userdata('currentLat'), $this->session->userdata('currentLong'));
            $this->session->set_userdata('currentCity', $city);
        }
        $country = '';
        if ($this->session->userdata('currentCountry') == '') {
            $return['country'] = $country = $this->getCountryByLatLong($this->session->userdata('currentLat'), $this->session->userdata('currentLong'));
            $this->session->set_userdata('currentCountry', $country);
        }
        // pr($this->sesstion->userdata('currentCity'));exit;
        echo json_encode($return);
        exit;
    }

    function similar_location() {
        $this->load->model('group/model_group');
        $getvar = $this->input->get();

        $currentLat = ($this->session->userdata('currentLat') != '') ? $this->session->userdata('currentLat') : '0.00';
        $currentLong = ($this->session->userdata('currentLong') != '') ? $this->session->userdata('currentLong') : '0.00';

        $search = isset($getvar['c']) ? $this->general->decryptData($getvar['c']) : '';
        $fields = "location.vName,vCity,location.vLatitude,location.vLongitude,location.iLocationId,vAddress,location.vUrlName,(select category.vCategory from category where category.iCategoryId = location.iCategoryId and category.eStatus = 'Active') as vCategory,vTags,location_images.vImage as cover_image,"
                . "round((6371 * acos(cos(radians($currentLat)) * cos(radians(location.vLatitude)) 
                    * cos(radians('$currentLong') - radians(location.vLongitude)) + sin(radians('$currentLat')) 
                    * sin(radians(location.vLatitude)))),2) AS distance";

//        if ($search > 0) {
        $ext_similar = 'iCategoryId = "' . $search . '" AND vCity="' . $getvar['city'] .'" AND eStatus <> "Deleted" and location.eType <> "Private"';
        $join_arr = array(array('table' => 'location_images', 'condition' => 'location.iLocationId = location_images.iLocationId and location_images.eCoverImage = "Yes"', 'jointype' => 'left'));
//            $join_arr = array(array('table' => 'category', 'condition' => 'category.iCategoryId = location.iCategoryId and category.eStatus = "Active"', 'jointype' => 'left'));
        $order_by = 'location.vCity,vCategory,distance';
        $data['data'] = $this->model_location->getData($fields, $join_arr, $ext_similar, $order_by);
       //echo $this->db->last_query();exit;

        if (count($data['data']) > 0) {
            foreach ($data['data'] as $key => $value) {
                $result[$key] = array($value['vName'] . ' ' . $value['vCity'], $value['vLatitude'], $value['vLongitude']);
            }
        } else {
            $result[] = array('Session', $this->session->userdata('currentLat'), $this->session->userdata('currentLong'));
        }

        $json_result = json_encode($result);
        $data['result'] = $json_result;
        $this->load->view('similar_location', $data);
//        } else {
//            redirect('/');
//        }
    }

    function updateImageName() {
        $postvar = $this->input->post();

        $where = 'iLocationId = "' . $postvar['iLocationId'] . '"';
        $data['vLocationImage'] = $postvar['vLocationImage'][0];
        $this->model_location->update($data, $where);
        echo json_encode('true');
        exit;
    }

    function l_deleteimg() {
        $postvar = $this->input->post();

        $where = 'iLocationId = "' . $postvar['iLocationId'] . '"';
        $data['vLocationImage'] = '';
        $this->model_location->update($data, $where);
        echo json_encode('true');
        exit;
    }

    function insertMultiImageName() {
        $postvar = $this->input->post();

        //$postvar['vUrlName'];
        $this->db->set('iLocationId', $postvar['iLocationId']);
        $this->db->set('vImage', $postvar['vLocationImage'][0]);
        $this->db->set('eCoverImage', 'No');
        $isertid = $this->db->insert('location_images');




        echo json_encode('true');
        exit;
    }

    function getCityByLatLong($lat, $lng) {
        $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&sensor=false');
//        $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=8.000,10.000&sensor=false');

        $output = json_decode($geocode);
//        pr(end($output->results[0]->address_components));
        for ($j = 0; $j < count($output->results[0]->address_components); $j++) {
            if ($output->results[0]->address_components[$j]->types[0] == "locality") {
                $GetCity = $output->results[0]->address_components[$j]->long_name;
            }
        }

        return $GetCity;
    }

    function getCountryByLatLong($lat, $lng) {
        $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&sensor=false');
//        $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=8.000,10.000&sensor=false');

        $output = json_decode($geocode);
        //pr(end($output->results[0]->address_components));
        for ($j = 0; $j < count($output->results[0]->address_components); $j++) {
            if ($output->results[0]->address_components[$j]->types[0] == "country") {
                $GetCountry = $output->results[0]->address_components[$j]->short_name;
            }
        }
        return $GetCountry;
    }

    function setCurrentCity() {
        $postvar = $this->input->post();

        if($postvar['searchCity'] == "false") {
        	$url = $this->config->item('site_url') . 'search_location?s=' . $postvar['searchObj'] . "&sc=" . $postvar['city'];
//            redirect($this->config->item('site_url') . 'locations');
        	echo $url;
        	exit;
        }

        $this->session->set_userdata('currentCity', $postvar['city']);
        echo 1;
        exit;
    }

    function getCategory() {
        $postvar = $this->input->post();
        if ($postvar != '') {
            $Type = $postvar['eType'];
        }
        if ($Type != '') {
            $ext_Cond = 'vCategory ="' . $Type . '"';
            $fields = 'iCategoryId';
            $result = $this->model_location->categoryList('', $ext_Cond, $fields);
            $category_cond = 'iParentId ="' . $result[0]['iCategoryId'] . '"';
            $categoryList = $this->model_location->categoryList('', $category_cond);
        }
        $data['categoryList'] = $categoryList;
        $this->load->view('getCategory', $data);
    }

    public function upload_multi_images() {
        $postvar = $this->input->post();
        $location_id = $this->general->decryptData($postvar['location_id']);
        $user_id = $this->session->userdata('iUserId');

        if (!empty($location_id)) {
            $check_availability = $this->model_location->getAvailability($user_id, $location_id);
            if ($check_availability[0]['tot'] > 25) {
                echo "You have exceeded limit of upload  i.e. 25 Images";
                exit;
            }
        }


        if ($this->session->userdata('rand_name') != '') {
            $rand_name = $this->session->userdata('rand_name');
        } else {
 //           $rand_name = time();
            $this->session->set_userdata('rand_name', $rand_name);
        }


        if ($_FILES['file']['error'] != 4) {
            $this->load->library('upload');
            if (!empty($location_id)) {
                $temp_folder_path = $this->config->item('upload_path') . 'location/' . $location_id;
            } else {
                $temp_folder_path = $this->config->item('upload_path') . 'temp/' . $rand_name;
            }
            $this->general->createfolder($temp_folder_path);
            if (!empty($location_id)) {
                $file_name = $location_id . '@@@' . preg_replace('/\s+/', '', $_FILES['file']['name']);
            } else {
                $file_name = $rand_name . '@@@' . preg_replace('/\s+/', '', $_FILES['file']['name']);
            }

            $upload_config = array(
                'upload_path' => $temp_folder_path,
                'allowed_types' => "jpg|jpeg|gif|png", //*
                'max_size' => 1024 * 1024 * 2,
                'file_name' => $file_name,
                'remove_space' => TRUE,
                'overwrite' => TRUE
            );


            $this->upload->initialize($upload_config);


            if ($this->upload->do_upload('file')) {
                $file_info = $this->upload->data();
                $uploadedFile = $file_info['file_name'];
                if (!empty($location_id)) {
                    $newpostvar['vImage'] = $uploadedFile;
                    $newpostvar['iLocationId'] = $location_id;
                    $this->model_location->createthumb($temp_folder_path . '/' . $file_name, $temp_folder_path . '/thumb_' . $file_name, 160,225 );
                    $this->model_location->compress($temp_folder_path . '/' . $file_name, $temp_folder_path . '/comp_' . $file_name,30 );
                    $result = $this->model_location->insertData($newpostvar, 'location_images');
                    $newpostvar['vImage'] = 'comp_'.$uploadedFile;
                    $result = $this->model_location->insertData($newpostvar, 'location_images');
                    $newpostvar['vImage'] = 'thumb_'.$uploadedFile;
                    $result = $this->model_location->insertData($newpostvar, 'location_images');
                    if ($result > 0) {
                       echo "Success";
                        exit;
                    }
                } else {
                    $this->model_location->createthumb($temp_folder_path . '/' . $file_name, $temp_folder_path . '/thumb_' . $file_name, 160,225 );
                    $this->model_location->compress($temp_folder_path . '/' . $file_name, $temp_folder_path . '/comp_' . $file_name,30 ); 
                }
                echo 'Success';
                exit;
            } else {
                echo $this->upload->display_errors();
                $this->session->set_flashdata('failure', $temp_folder_path . ' <br/>' . $this->upload->display_errors());
            }

        }
    }

    public function remove_image() {
        $postvar = $this->input->post();
        if ($postvar['id'] == '') {
            $filename = $this->config->item('upload_path') . 'temp/' . $this->session->userdata('rand_name') . '/' . $this->session->userdata('rand_name') . '@@@' . $postvar['name'];
        } else {
            $filename = $this->config->item('upload_path') . 'location/' . $postvar['id'] . '/' . $postvar['id'] . '@@@' . $postvar['name'];
            $name = $postvar['id'] . '@@@' . $postvar['name'];
            $result = $this->model_location->remove_image($name, $postvar['id']);
        }
        unlink($filename);
        return true;
        exit;
    }

    public function delete_img() {
        $postvar = $this->input->post();
        $id = $this->general->decryptData($postvar['id']);
        $source = $this->general->decryptData($postvar['source']);
        if ($id != '' && $source != '') {
            $f = explode('comp_', $source);
            $l = explode('/', $source);

            $filename1 = $this->config->item('upload_path') . 'location/' .$l[0]. '/comp_' .$f[1];
            $filename2 = $this->config->item('upload_path') . 'location/' .$l[0]. '/thumb_' .$f[1];
            $filename3 = $this->config->item('upload_path') . 'location/' .$l[0]. '/' .$f[1];
            

            $result = $this->model_location->delete_image($id);
            unlink($filename1);
            unlink($filename2);
            unlink($filename3);
            echo 1;
        } else {
            echo 0;
        }
        exit;
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */












