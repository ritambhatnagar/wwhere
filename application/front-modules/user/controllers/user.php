<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User extends MX_Controller {

    public function __construct() {
        parent::__construct();
        // $dat=array();
        error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
//        $this->general->checkSession();
        $this->load->model('model_user');
    }

    public function index() {
        $data['indexpage'] = true;
        $this->load->view('index',$data);
    }
	public function privacy_policy()
	{
		$this->load->view('privacy');
	}
	public function faq()
	{
		$this->load->view('faq');
	}
	public function press()
	{
		$this->load->view('press');
	}

    public function login() {
        if ($this->session->userdata('iUserId') > 0) {
            redirect('locations');
        } else {
            $cookiearr = $this->cookie->read('userarray');
            $data['vContactNo'] = '';
            $data['vPassword'] = '';
            $data['eRemember'] = '';

            if (is_array($cookiearr) && $cookiearr['nspl_username'] != '') {
                $data['vContactNo'] = $cookiearr['nspl_username'];
                $data['vPassword'] = $cookiearr['nspl_password'];
                $data['eRemember'] = 'On';
            }
            $this->load->view('login', $data);
        }
    }

    public function login_action($verify_data = '') {

        $this->load->helper('date');
        $postarr = $this->input->post();
        $country_code = explode('|',$postarr['iCountryId']);
        unset($postarr['iCountryId1']);
        $postarr['vContactNo'] = $country_code[1].$postarr['vContactNo'];
        $this->load->library('cookie');

        if ($postarr['eRemember'] == 'on') {
            $cookiedata = array(
                'nspl_username' => $postarr['vContactNo'],
                'nspl_password' => $postarr['vPassword']
            );
            $this->cookie->write('userarray', $cookiedata);
        } else {
            $cookiedata = array(
                'nspl_username' => '',
                'nspl_password' => ''
            );
            $this->cookie->write('userarray', $cookiedata);
        }

        //login-authentication
        $this->load->model('user/model_log_history');
        if ($verify_data != '') {
            $postarr['vContactNo'] = $verify_data['vContactNo'];
            $postarr['vPassword'] = $verify_data['vPassword'];
            $rply = $this->model_user->authenticate($postarr['vContactNo'], $postarr['vPassword']);
        } else {
            $rply = $this->model_user->authenticate($postarr['vContactNo'], md5($postarr['vPassword']));
        }
        if ($rply['errorCode'] == 1) {
            if ($data['iLogId'] == '') {
                $data['iUserId'] = $this->session->userdata('iUserId');
                $data['vSessionId'] = $this->general->encryptData($postarr['vContactNo'] . date('Y-m-d h:i:s'));
                $data['vIP'] = $this->input->ip_address();
                $data['eUserType'] = 'Member';
                $data['dLoginDate'] = date('Y-m-d H:i:s');
            }
            $this->model_log_history->insert($data);
//            $this->session->set_flashdata('success', 'Logged in successfully');
            //redirect('serviceAuth');
            if ($verify_data != '') {
                redirect('user/user/serviceAuth');
            }else{
//                redirect('locations');
                echo 'locations';
                exit;
            }
        } else {
//            $this->session->set_flashdata('failure', $rply['errorMessage']);
//            redirect('index');
            echo $rply['errorMessage'];
            exit;
        }
    }

    public function serviceAuth() {
        if ($this->session->userdata('iUserId') > 0) {
            redirect('locations');
        } else {
            redirect('signin');
        }
    }

    public function logout() {
        $this->load->helper('date');
        $this->load->model('model_log_history');
        $this->load->model('model_user');
        $fields = "";
        $query = "(select max(iLogId) as iLogId from log_history where iUserId = '" . $this->session->userdata('iUserId') . "')";
        $getval = $this->model_log_history->query($query);
        $id = $getval[0]['iLogId'];
        $ext_con = "iLogId = '" . $id . "'";
//        $ext_con = "iLogId in ";
        $data['dLogoutDate'] = date('Y-m-d H:i:s');
        $this->model_log_history->update($data, $ext_con);

        //user lastaccess query

        $ext_cond = 'iUserId = "' . $this->session->userdata('iUserId') . '"';
        $lastAccess['dLastAccess'] = date('Y-m-d H:i:s');
        $this->model_user->update($lastAccess, $ext_cond);

        $this->session->sess_destroy();
        redirect('index');
    }

    public function register() {
        if ($this->session->userdata('iUserId') > 0) {
            redirect('locations');
        } else {
            $this->general->checkSession();
            $country = $this->model_user->getCountryDetail();
            $data['country'] = $country;
            $this->load->view('register', $data);
        }
    }

    public function register_action() {
        $postdata = $this->input->post();
        $postArray = $this->general->strip_get_post($postdata);

        unset($postArray['vPassword2']);
        unset($postArray['ccode']);
        $postArray['vPassword'] = md5($postArray['vPassword']);
        $iCountryId = explode('|', $postArray['iCountryId']);
        $postArray['iCountryId'] = $iCountryId[0];
        $postArray['vLastName'] = "";
        $postArray['vContactNo'] = $iCountryId[1] . $postArray['vContactNo'];
        $postArray['dtAddedDate'] = date('Y-m-d H:i:s');
        $postArray['dtModifiedDate'] = date('Y-m-d H:i:s');
        $postArray['dLastAccess'] = date('Y-m-d H:i:s');
        $postArray['eStatus'] = 'Active';
        $postArray['vVerifyCode'] = $this->general->getRandomNumber('4');
        $verifycode = $postArray['vVerifyCode'];
        $reply = $this->model_user->insert($postArray);

        if ($reply > 0) {
            $return = $this->send_sms($reply, 'register', $verifycode);
            if ($return['scode'] == 0) {
                $this->session->set_flashdata('failure', $return['error']);
            }
        }
        redirect('verification?d=' . $this->general->encryptData($reply));
    }

    public function dashboard() {
        $this->load->view('dashboard');
    }

    public function user_mgmt() {
        //$selfurl = basename($_SERVER['REQUEST_URI']);
        //$this->general->getPagePermission($this->session->userdata('iRoleId'), $selfurl, $type = "list");
        //$deletePrm = $this->general->getPageDeletePermission($this->session->userdata('iRoleId'), $selfurl);


        $rply = $this->model_user->getData();
        $getvar = $this->input->get();
        if ($getvar['id'] != '' && $getvar['m'] != '') {
            //delete
            $mode = (isset($getvar['m']) && $getvar['m'] != '') ? $this->general->decryptData($getvar['m']) : '';
            if ($mode == 'delete') {
                $id = (isset($getvar['id']) && $getvar['id'] != '') ? $this->general->decryptData($getvar['id']) : '';
                $resurl = basename($this->uri->uri_string);
                if ($getvar['call'] == 'ajax') {
                    $res = 1;
                } else {
                    $res = $this->general->check_permission('del', $resurl, 'ajax');
                    if ($res != 1) {
                        redirect('forbidden');
                    }
                }
                if ($res == 1) {
                    $ext_cond = 'iUserId ="' . $id . '"';
                    $rply = $this->model_user->delete($ext_cond);
                }
            }
        }
        $data['all'] = $rply;
        $data['roles'] = $rply1;
        $data['deletePrm'] = $deletePrm;
        $this->load->view('user_mgmt', $data);
        // $this->template->build('user_mgmt');
    }

    public function chnge_role_user() {
        $this->load->model('model_user');
        $postvar = $this->input->post();
        $ext_cond = 'iUserId = "' . $postvar['iUserId'] . '"';
        unset($postvar['iUserId']);
        $rply = $this->model_user->update($postvar, $ext_cond);
        echo $rply;
        exit;
    }

    public function user_add_edit() {
        //$selfurl = 'user_mgmt.html';
        //$this->general->getPagePermission($this->session->userdata('iRoleId'), $selfurl, $type = "form");
        $fields = "*,PASSWORD('vPassword')";
        $getvar = $this->input->get();
        $country = $this->model_user->getCountryDetail();

        if (isset($getvar['d'])) {
            $flag_userid = urldecode($this->general->decryptData($getvar['d']));
            $ext_cond = 'iUserId = "' . $flag_userid . '"';
            $rply = $this->model_user->getData($fields, array(), $ext_cond);
            $data['all'] = $rply[0];
            $data['user_id'] = $flag_userid;
        }
        $data['country'] = $country; //pr($data);exit;
        $this->load->view('user_add_edit', $data);
    }

    public function user_action() {
//        $selfurl = 'user_mgmt.html';
//        $this->general->getPagePermission($this->session->userdata('iRoleId'), $selfurl, $type = "list");
//        $deletePrm = $this->general->getPageDeletePermission($this->session->userdata('iRoleId'), $selfurl);
        //pr($deletePrm);exit;
        $this->load->model('model_user');
        $postdata = $this->input->post();
        $getdata = $this->input->get();

        $postvar = $this->general->strip_get_post($postdata);
        $getvar = $this->general->strip_get_post($getdata);
        unset($postvar['vPassword2']);
        unset($postvar['vOldPassword']);

        $postvar['dtModifiedDate'] = date("Y-m-d h:i:s");

        if (empty($postvar['iUserId']) && $getvar['id'] == '') {
            //add
//            if($postvar['vEmail']= )
            $postvar['vPassword'] = stripslashes(md5($postvar['vPassword']));
            $postvar['dtAddedDate'] = date("Y-m-d h:i:s");
//            pr ($postvar);exit;
            $rply = $this->model_user->insert($postvar);
        } else if ($getvar['id'] != '' && empty($postvar['iUserId'])) {
            //delete
            $ext_cond = 'iUserId = "' . $getvar['id'] . '"';
            $rply = $this->model_user->delete($ext_cond);
        } else {
            //edit
            $ext_cond = 'iUserId = "' . $postvar['iUserId'] . '"';
            unset($postvar['iUserId']);
            unset($postvar['vContactNo']);
            $postvar['vPassword'] = stripslashes(md5($postvar['vPassword']));
            $rply = $this->model_user->update($postvar, $ext_cond);
        }
        redirect('locations');
    }

    public function forgotpassword() {
        $this->load->view('forgotpassword');
    }

    public function forgotpassword_action() {
        $this->load->model('model_user');
        $postvar = $this->input->post();
        $vContact = $postvar['login_contact'];
        $user_exist = $this->model_user->getForgotPassword($vContact);
        if (is_array($user_exist) && count($user_exist) > 0) {
            $onetimepass = $this->general->getRandomNumber('6');
            $this->db->set('vPassword', "md5('$onetimepass')", false);
            $this->db->where('iUserId', $user_exist[0]['iUserId']);
            $res = $this->db->update('user');
            $this->send_sms($user_exist[0]['iUserId'], 'forgotpassword', $onetimepass);
        } else {
            $this->session->set_flashdata('failure', "Account does not exists");
        }
        redirect('index');
    }

    function changeStatus() {
        $postvar = $this->input->post();
        $table = $postvar['table'];
        $primaryField = $postvar['primaryField'];
        $id = $postvar['primaryId'];
        $st = $postvar['status'];
        $success = $this->model_user->changeStatus($table, $primaryField, $id, $st);
        echo $success;
        exit;
    }

    public function checkEmail() {
        $userArr = $this->input->post();
        $id = $this->session->userdata('iUserId');
        if (isset($userArr["vEmail"])) {
            if ($id != '') {
                $ext_cond = "iUserId !='" . $id . "'";
            }
            echo $this->model_user->getUserValidation('vEmail', $userArr['vEmail'], $ext_cond);
            exit;
        }
        exit;
    }

    public function verification() {
        $getvar = $this->input->get();
        if ($getvar != '') {
            $data['d'] = $getvar['d'];

            if (isset($getvar['rs']) && $getvar['rs'] != '') {
                $id = $this->general->decryptData($getvar['d']);
                $onetimepass = $this->general->getRandomNumber('4');
                $this->db->set('vVerifyCode', $onetimepass);
                $this->db->where('iUserId', $id);
                $res = $this->db->update('user');
                $this->send_sms($id, 'register');
//                $this->session->set_flashdata('success', "Resend verification code successfully");
                redirect('verification?d=' . $getvar['d']);
            }
        }
        $this->load->view('verification', $data);
    }

    public function verification_action() {
        $postvar = $this->input->post();
        if ($postvar['d'] != '') {
            $id = $this->general->decryptData($postvar['d']);
        } else {
            $id = $this->session->userdata('iUserId');
        }
//        unset($postvar['d']);
        $ext_cond = "iUserId ='" . $id . "'";
        $reply = $this->model_user->getUserValidation('vVerifyCode', $postvar['code'], $ext_cond);

        if ($reply == 'false') {
            $this->session->set_flashdata('success', "Login successfully.");
            $data['eVerify'] = 'Yes';
            $this->model_user->update($data, $ext_cond);
            $fields = "vContactNo,vPassword";
            $received_data = $this->model_user->getData($fields, array(), $ext_cond);
            $this->login_action($received_data[0]);
        } else {
            $this->session->set_flashdata('failure', "Verification does not match");
            if ($postvar['d'] != '') {
                redirect('verification?d=' . $postvar['d']);
            } else {
                $d = $this->general->encryptData($id);
                redirect('verification?d=' . $d);
            }
        }
    }

    function verification_denied() {
        $path = $this->config->item('site_path');
        include_once ($path . "application/back-modules/user/views/verification_denied.php");
        exit;
    }

    function userEmail() {
        $userArr = $this->input->post();

        if (isset($userArr["vEmail"])) {
            echo $this->model_user->getUserValidation('vEmail', $userArr["vEmail"]);
            exit;
        }
        if (isset($userArr["vContactNo"])) {
            $data['vContactNo'] = ltrim($userArr['c'], '+') . $userArr["vContactNo"];
            echo $this->model_user->getUserValidation('vContactNo', $data["vContactNo"]);
            exit;
        }
        exit;
    }

    function send_sms($id = '', $type = '', $msg_data = '') {
        $ext_Cond = "iUserId ='" . $id . "'";
        $data = $this->model_user->getData('vContactNo,vVerifyCode', array(), $ext_Cond);
        if ($type == 'register') {
            $msg = 'Your verification code for wwhere is: ' . $data[0]['vVerifyCode'];
            $urlSMS = "http://tripadasmsbox.com/api/sendhttp.php?authkey=167Ad1Ti5Pj3c53803bd4&mobiles=". $data[0]['vContactNo'] ."&message=". $msg ."&sender=WWHERE&route=4";
        } else if ($type == 'forgotpassword') {
            $msg = 'Your new password for wwhere is: ' . $msg_data . ' Use this for login.';
            $urlSMS = "http://tripadasmsbox.com/api/sendhttp.php?authkey=167Ad1Ti5Pj3c53803bd4&mobiles=". $data[0]['vContactNo'] ."&message=". $msg ."&sender=WWHERE&route=4";
        }
        // create a new cURL resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $urlSMS);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // grab URL and pass it to the browser
        curl_exec($ch);

        // close cURL resource, and free up system resources
        curl_close($ch);


    }

    public function checkPassword() {
        $postvar = $this->input->post();
        $id = $this->session->userdata('iUserId');
        $pwd = md5($postvar['vOldPassword']);
        $fields = "vPassword";
        $ext_cond = "iUserId = '" . $id . "'";
        $getdata = $this->model_user->getData($fields, array(), $ext_cond);
        $password = $getdata[0]['vPassword'];

        if ($pwd == $password) {
            echo "true";
        } else {
            echo "false";
        }
        exit;
    }

    function resend_vCode() {
        
    }

    function select_city() {
        $this->load->view('select_city');
    }
    
    function eventExpire(){
        //$sql="UPDATE location SET eStatus = 'Deleted' WHERE eStatus = 'Active' AND eType='Event' AND CONCAT_WS(' ',DATE(dtFinishDate),vFinishTime) <= (NOW() - INTERVAL 2 DAY)";
        $where = "eStatus = 'Active' AND eType='Event' AND CONCAT_WS(' ',DATE(dtFinishDate),vFinishTime) <= (NOW() - INTERVAL 2 DAY)";
        $data['eStatus'] = 'Deleted';
        $this->model_user->expireEvent($data, $where);
        exit;
    }
    
    function checkContact() {
        $userArr = $this->input->post();
//        pr($userArr);
        if (isset($userArr["login_contact"])) {
            $data['vContactNo'] = $userArr["login_contact"];
            echo $this->model_user->getContactValidation('vContactNo', $data["vContactNo"]);
            exit;
        }
        exit;
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
