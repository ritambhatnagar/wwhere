<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of systemsettings
 *
 * @author nilay
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class SystemSettings extends CI_Model {

    protected $_settings_array = Array();

    public function __construct() {
        parent::__construct();
        $this->getSettingsFromDB();
//        $this->getPackageFromDB();
        $this->getUserData();
//        $this->getAdminUserData();
        $this->getLanguageSettings();
    }

    private function getSettingsFromDB() {
        $result = $this->db->get('setting')->result_array();
        for ($i = 0; $i < count($result); $i++) {
            $this->_settings_array[$result[$i]['vName']] = $result[$i]['vValue'];
            $this->config->set_item($result[$i]['vName'], $result[$i]['vValue']);
        }
    }

//    private function getPackageFromDB() {
//        $companyid = $this->session->userdata('iCompanyId');
//        if (trim($companyid) != '') {
//            $this->db->select('iPackageId as package_id,vPackageName as package_name,iUserLimit as user_limit,'
//                    . 'iContactLimit as contact_limit,iEmailLimit as email_limit,dtStartDate as start_date,'
//                    . 'iCampaignLimit as campaign_limit, iLandingPageLimit as landingpage_limit,'
//                    . 'iSocialScheduleLimit as social_schedule_limit, iDays as days,eType as package_type');
//            $this->db->where('iCompanyId', $companyid);
//            $this->db->where('eDelete', 'No');
//            $this->db->where('eStatus <> "Pending"');
//            $result = $this->db->get('package_subscription')->result_array();
//            if (is_array($result) && count($result) > 0) {
//                foreach ($result[0] as $key => $value) {
//                    $this->config->set_item(strtoupper('lbp_' . $key), $value);
//                }
//            }
//        }
//    }

    private function getUserData() {
        $userid = $this->session->userdata('iUserId');
        if (trim($userid) != '') {
            $this->db->select('concat(vFirstName," ", vLastName) as user_name,vEmail as user_email',false);
            $this->db->where('iUserId', $userid);
            $this->db->where('eStatus', 'Active');
            $result = $this->db->get('user')->result_array();
            if (is_array($result) && count($result) > 0) {
                foreach ($result[0] as $key => $value) {
                    $this->config->set_item(strtoupper('lbu_' . $key), $value);
                }
            }
        }
    }
    
//    private function getAdminUserData() {
//        $userid = $this->session->userdata('iAdminId');
//        if (trim($userid) != '') {
//            $this->db->select('concat(vFirstName," ", vLastName) as user_name,vEmail as user_email,iRoleId as role_id',false);
//            $this->db->where('iAdminId', $userid);
//            $this->db->where('eStatus', 'Active');
//            $result = $this->db->get('admin_master')->result_array();
//            if (is_array($result) && count($result) > 0) {
//                foreach ($result[0] as $key => $value) {
//                    $this->config->set_item(strtoupper('lbau_' . $key), $value);
//                }
//            }
//        }
//    }

    private function getLanguageSettings() {
        
    }

    public function getSettings($var_name) {

        if (array_key_exists($var_name, $this->_settings_array)) {
            return $this->_settings_array[$var_name];
        } else {
            return false;
        }
    }

    public function getAllSettings() {

        return $this->_settings_array;
    }

    function getSettingsMaster($eConfigType = "", $assoc_value = false, $fields = "") {
        if ($fields == '')
            $fields = '*';
        $this->db->select($fields);
        $this->db->from("setting");
        $this->db->where("setting.eStatus", "Active");
        if ($eConfigType != '') {
            $this->db->where("setting.eConfigType", $eConfigType);
        }
        $this->db->order_by("setting.iOrderBy, setting.eConfigType ASC");

        if ($assoc_value != false) {
            $sql = $this->db->_compile_select();
            $list_data = $this->db->select_assoc($sql, $assoc_value);
        } else {
            $list_data = $this->db->get()->result_array();
        }
        return $list_data;
    }

    function getQueryResult() {
        return $this->db->query($query);
    }

    function save_setting($updateArr, $field_name, $vValue) {
        $sess_setting = $this->session->userdata('sess_setting');
        $sql_update = "UPDATE setting SET " . @implode(", ", $updateArr) . " WHERE vName = '" . $field_name . "'";
        $db_update = $this->db->query($sql_update);
        return $db_update;
    }

}
