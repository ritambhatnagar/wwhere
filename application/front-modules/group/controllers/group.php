<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Group extends MX_Controller {

    public function __construct() {
        parent::__construct();
        // $dat=array();
        error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
        $this->load->model('model_group');
    }

    public function groups() {
        $getvar = $this->input->get();
        $search = isset($getvar['myg']) ? $getvar['myg'] : '';
        $id = $this->session->userdata('iUserId');
        $extcond = 'iUserId = "'.$id.'"';
        if($search != ''){
            $extcond .= " and vGroup like ('%".$search."%')";
        }
        $data['group_data'] = $this->model_group->getData('*',array(),$extcond);
        //delete end        
        $this->load->view('group_mgmt',$data);
    }

    public function group_add_edit() {
        $getvar = $this->input->get();
        
        $flaguserId = (isset($getvar['id']))?$this->general->decryptData($getvar['id']):'';
        if ($flaguserId != '') {
            $fields = "*";
            $ext_cond = 'iGroupId ="' . $flaguserId . '"';
            $reply = $this->model_group->getData($fields, array(), $ext_cond);
            $data['all'] = $reply;
            $data['iGroupId'] = $flaguserId;
        }
        $this->load->view('group_add_edit', $data);
    }

    public function group_add_edit_action() {
        $postdata = $this->input->post();
        $postvar = $this->general->strip_get_post($postdata);
        unset($postvar['ajax']);
        $id = $this->session->userdata('iUserId');
        $postvar['iCreatedBy'] = $id;
        $postvar['iModifyBy'] = $id;
        $postvar['iUserId'] = $id;
        $postvar['dtModify'] = date('Y-m-d H:i:s');
        $postvar['eStatus'] = 'Active';
        $domain_postvar['vUrl'] = strtolower($postvar['vGroup']);
        $domain_postvar['eType'] = "Group";
        
        if ($postvar['iGroupId'] == '' || $postvar['iGroupId'] == 'undefined'){
            //add
            unset($postvar['iGroupId']);
            $postvar['dtCreated'] = date('Y-m-d H:i:s');
            $rply = $this->model_group->insert($postvar);
            $domain_postvar['iTypeId'] = $rply;
            $domain_rply = $this->model_group->insertData($domain_postvar,'domain');
        }else if($postvar['iGroupId']!=''){
            //update
            $iGroupId = (isset($postvar['iGroupId']) && $postvar['iGroupId'] != '') ? $postvar['iGroupId'] : '';
            if ($iGroupId != '') {
                $fields = 'vGroup';
                $ext_cond = "iGroupId ='" . $iGroupId . "'";
                $geted = $this->model_group->getData($fields, array(), $ext_cond);
                unset($postvar['iGroupId']);
                $rply = $this->model_group->update($postvar, $ext_cond);
                $domain_postvar['iTypeId'] = $iGroupId;
                $upd_cond = 'eType = "Group" and vUrl="'.$geted[0]['vGroup'].'"';
                $result = $this->model_group->updateData($domain_postvar,$upd_cond,'domain');
            }
        }
        redirect('groups');
    }

    function userGroup() {
        $postdata = $this->input->post();
        $getdata = $this->input->get();
        $userArr = $this->general->strip_get_post($postdata);
        $getArr = $this->general->strip_get_post($getdata);

        if (isset($userArr["vGroup"])) {
            $ext_cond = '';
            if (isset($getArr['d']) && $getArr['d'] != '') {
                $ext_cond .= "iTypeId <> '" . $getArr['d'] . "'";
            }
            echo $this->model_group->getUserValidation('vUrl', $userArr["vGroup"], $ext_cond, 'domain');
        }
        exit;
    }

    function delete_data() {
        $postvar = $this->input->post();
        if(isset($postvar) && $postvar['d']){
            $id = $this->general->decryptData($postvar['d']);
        }
        $fields = 'vGroup';
        $ext_cond = "iGroupId ='".$id."'";
        $result = $this->model_group->getData($fields,array(),$ext_cond);
        $del_cond = "eType = 'Group' and vUrl='".$result[0]['vGroup']."'";
        $delete_group = $this->model_group->delete($ext_cond);
        $delete_group_location = $this->model_group->deleteData($ext_cond,'group_location');
        $delete_domain = $this->model_group->deleteData($del_cond,'domain');
        return 1;
    }
    
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */