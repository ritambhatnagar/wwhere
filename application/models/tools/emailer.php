<?php

class Emailer extends CI_Model {

    public $ids_arr = array();
    public $num_totrec = "";
    protected $module_array = array();
    public $main_table = "";
    public $primary_key = "";

    public function __construct() {
        parent::__construct();
        $this->load->helper('listing');
        $this->main_table = "system_email";
    }

    public function getEmailContent($vEmailCode = 'MEMBER_REGISTER') {
        $this->db->select('iEmailTemplateId,vEmailCode,vEmailTitle,vFromName,vFromEmail,vBccEmail,eEmailFormat,vEmailSubject,tEmailMessage,vEmailFooter,eStatus');
        $this->db->from($this->main_table);
        $this->db->where("vEmailCode", $vEmailCode);
        $emailData = $this->db->get()->result_array();
        return $emailData;
    }

    function send_mail($data, $type = "MEMBER_REGISTER") {
        $emailData = $this->getEmailContent($type);
        $tEmailMessage = $emailData[0]['tEmailMessage'];
        $vEmailSubject = $emailData[0]['vEmailSubject'];
        switch ($type) {
            case "MEMBER_REGISTER":
            case "MEMBER_REGISTER_ADMIN":
                $findarray = array("#COMPANY_NAME#", "#NAME#", "#USERNAME#", "#PASSWORD#", "#EMAIL#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['vFirstName'] . ' ' . $data['vLastName'], $data['vUserName'], $this->general->decryptData($data['vPassword']), $data['vEmail'], $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "MEMBER_VERIFICATION":
                //$emailencry = trim(base64_encode(base64_encode($data['vEmail'])),'==');
                $emailencry = $this->general->encryptData($data['vEmail']);
                $findarray = array("#COMPANY_NAME#", "#NAME#", "#EMAIL#", "#VERIFY_CODE#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['vFirstName'] . ' ' . $data['vLastName'], $data['vEmail'], $data['vVerifyCode'], $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "FRONT_FORGOT_PASSWORD":
            case "FORGOT_PASSWORD":
                $findarray = array("#COMPANY_NAME#", "#vName#", "#vPassword#", "#vEmail#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['vFirstName'] . ' ' . $data['vLastName'], $data['onetimepass'], $data['vEmail'], $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));

                break;

            case "LOGIN_NOTIFICATION":
                $findarray = array("#COMPANY_NAME#", "#NAME#", "#DATE#", "#IP#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['vFirstname'] . ' ' . $data['vLastname'], date("l jS \of F Y h:i:s A (T)"), $this->input->ip_address(), $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "WRONG_PASSWORD_NOTIFICATION":
                $findarray = array("#COMPANY_NAME#", "#NAME#", "#DATE#", "#IP#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['vFirstname'] . ' ' . $data['vLastname'], date("l jS \of F Y h:i:s A (T)"), $this->input->ip_address(), $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "START_SCHEDULING":
                $findarray = array("#CAMPAIGN_NAME#", "#NAME#", "#DATE#", "#START_DATE#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($data['vCampaignName'], $data['vFirstName'] . ' ' . $data['vLastName'], $this->general->getDate($data['dtScheduleDate']), $this->general->getDateTime($data['dtStartDate']), $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "FINISH_SCHEDULING":
                $findarray = array("#CAMPAIGN_NAME#", "#NAME#", "#DATE#", "#FINISH_DATE#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($data['vCampaignName'], $data['vFirstName'] . ' ' . $data['vLastName'], $this->general->getDate($data['dtScheduleDate']), $this->general->getDateTime($data['dtFinishDate']), $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "CHANGE_EMAIL_NOTIFICATION":
                $findarray = array("#COMPANY_NAME#", "#NAME#", "#DATE#", "#IP#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['NAME'], date("l jS \of F Y h:i:s A (T)"), $this->input->ip_address(), $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "CHANGE_PASSWORD_NOTIFICATION":
                $findarray = array("#COMPANY_NAME#", "#NAME#", "#DATE#", "#IP#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['NAME'], date("l jS \of F Y h:i:s A (T)"), $this->input->ip_address(), $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "NEW_USER":
                $findarray = array("#ADMINISTRATOR#", "#NAME#", "#EMAIL#", "#PASSWORD#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->config->item('LBU_USER_NAME'), $data['vFirstName'].' '.$data['vLastName'], $data['vEmail'], $data['vPassword'], $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
            case "INVITE_FRIEND":
                $comfirmUrl = site_url() . '?r='.$data['Link'];
                $findarray = array("#NAME#", "#ADMINISTRATOR#", '#SITE_URL#', '#COPY_RIGHTS#', '#LINK#');
                $replacearray = array($data['Name'], $data['vFirstName'] . ' ' . $data['vLastName'], $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'), $comfirmUrl);
                break;
            case "RESET_PASSWORD":
                $resetencry = urlencode($this->general->encryptData($data['iAdminId']));
                $resetencry1 = urlencode($this->general->encryptData($data['onetime']));
                $reset_link = site_url() . 'reset_pass?d=' . $resetencry.'&one='.$resetencry1;
                $findarray = array("#COMPANY_NAME#", "#NAME#", "#vResetLink#", "#PASSWORD#", '#SITE_URL#', '#COPY_RIGHTS#');
                $replacearray = array($this->systemsettings->getSettings('COMPANY_NAME'), $data['vFirstName'] . ' ' . $data['vLastName'], $reset_link, $data['onetime'], $this->config->item('site_url'), $this->systemsettings->getSettings('COPYRIGHTED_TEXT'));
                break;
        }

        $vBody = str_replace($findarray, $replacearray, $tEmailMessage);
        $subject = str_replace($findarray, $replacearray, $vEmailSubject);
        $this->load->library('email');
        $this->email->from($emailData[0]['vFromEmail'], 'wWhere');
        $this->email->to($data['vEmail']);
        $this->email->subject($subject);
        $this->email->message($vBody);
        $success = $this->email->send();
        return $success;
    }

}
