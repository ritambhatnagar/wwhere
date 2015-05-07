<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

class Authenticate {

    protected $CI;

    function __construct() {
        $this->CI = &get_instance();
        $this->authenticate();
    }

    protected function authenticate() {
        $front_allow_arrray = array('index', 'login', 'login_action', 'logout', 'register', 'register_action', 'forgotpassword', 'forgotpassword_action', 'verification', 'verification_denied', 'setlatlong','userEmail','send_sms','verification_action','location_detail','sessonlatlng','search_location','location_detail','similar_location','select_city','setCurrentCity','eventExpire','checkContact','privacy_policy','faq','press');
        $admin_allow_arrray = array('index', 'login', 'login_action', 'logout', 'forgotpassword', 'forgotpassword_action', 'verification', 'verification_denied', 'setlatlong', 'cron_update_task_status');
        $class_allow_arrray = array('');
        $current_class = $this->CI->router->fetch_class();
        $current_method = $this->CI->router->fetch_method();
        $current_module = $this->CI->router->fetch_module();

        for ($i=0; $i < count($front_allow_arrray); $i++) { 
            $front_allow_arrray[$i] = strtolower($front_allow_arrray[$i]);
        }

        if (!in_array($current_method, $front_allow_arrray) && !in_array($current_class, $class_allow_arrray) && $this->CI->config->item('is_admin') == 0) {
            if (!$this->checkValidAuth('Member')) {
                echo "<script>location.href='".site_url('index')."'</script>";
                exit;
            }else if($this->checkValidAuth('Member')){
                $geturl = basename($this->CI->uri->uri_string());
                if ($this->CI->session->userdata('beforeurl') == '' && $current_method != 'signout' && $current_method != 'index')
                    $this->CI->session->set_userdata('beforeurl', $geturl);
            }
        }

        if (!in_array($current_method, $admin_allow_arrray) && !in_array($current_class, $class_allow_arrray) && $this->CI->config->item('is_admin') == 1) {
            if (!$this->checkValidAuth('Admin')) {
                echo "<script>location.href='".site_url('index')."'</script>";
                exit;
            }
        }
    }

    function checkValidAuth($eType) {
        $flag = false;
        if ($eType == 'Admin') {
            if ($this->CI->session->userdata('iAdminId') > 0) {
                $flag = true;
            }
        } elseif ($eType == 'Member') {
            if ($this->CI->session->userdata('iUserId') > 0) {
                $flag = true;
            }
        }
        return $flag;
    }

}
