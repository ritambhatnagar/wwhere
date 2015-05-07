<?php
$text = '';
$style_val = "display:none;";
$loop = array();
if ($this->session->userdata('facebooksocialAlert') != '') {
    if(is_array($this->session->userdata('facebooksocialAlert'))){
        $loop = array_merge($loop, $this->session->userdata('facebooksocialAlert'));            
    }else {
        $alert = array('Facebook' => array('message' => 'Facebook message posted successfully.', 'class' => 'alert-success'));
        $loop = array_merge($loop, $alert);
    }
    $this->session->unset_userdata('facebooksocialAlert');
    $style_val = "";
}
if ($this->session->userdata('twittersocialAlert') != '') {
    if(is_array($this->session->userdata('twittersocialAlert'))){
        $loop = array_merge($loop, $this->session->userdata('twittersocialAlert'));
    }else{
        $alert = array('Twitter' => array('message' => 'Twitter message posted successfully.', 'class' => 'alert-success'));
        $loop = array_merge($loop, $alert);
    }
    $this->session->unset_userdata('twittersocialAlert');
    $style_val = "";
}
if ($this->session->userdata('linkedinsocialAlert') != '') {
    if(is_array($this->session->userdata('linkedinsocialAlert'))){
        $loop = array_merge($loop, $this->session->userdata('linkedinsocialAlert'));
    }else{
        $alert = array('LinkedIn' => array('message' => 'LinkedIn message posted successfully.', 'class' => 'alert-success'));
        $loop = array_merge($loop, $alert);
    }
    $this->session->unset_userdata('linkedinsocialAlert');
    $style_val = "";
}
if ($this->session->userdata('socialAlertSuccess') != '') {
    if(is_array($this->session->userdata('socialAlertSuccess'))){
        $loop = array_merge($loop, $this->session->userdata('socialAlertSuccess'));
    }else{
        $alert = array('Posted' => array('message' => 'The message posted successfully.', 'class' => 'alert-success'));
        $loop = array_merge($loop, $alert);
    }
    $this->session->unset_userdata('socialAlertSuccess');
    $style_val = "";
}
if ($this->session->userdata('errorsocialAlert') != '') {
    if(is_array($this->session->userdata('errorsocialAlert'))){
        $loop = array_merge($loop, $this->session->userdata('errorsocialAlert'));
    }else{
        $upgrade = '<a data-href="' . $this->config->item('site_url') . 'my_account?t=package_info" onclick="urlParse(this)"> Upgrade Plan...</a>';
//        if($this->config->item('LBP_PACKAGE_ID') == 1){
//            $alert = array('Notice' => array('message' => 'If you want to use this feature, then ' . $upgrade, 'class' => 'alert-warning'));
//        }else {
            $alert = array('Notice' => array('message' => 'If you want to use schedule, than ' . $upgrade, 'class' => 'alert-warning'));
//        }
        $loop = array_merge($loop, $alert);
    }
    $this->session->unset_userdata('errorsocialAlert');
    $style_val = "";
}
?>
<?php if (count($loop) > 0) { ?>
    <div class="col-md-12">
        <div class="widget-body-gray" style="<?php echo $style_val; ?>">
            <?php foreach ($loop as $key => $value) { ?>
                <div class="widget-body" data-toggle="source-code" data-placement="outside">       
                    <!-- Alert -->
                    <div class="alert <?php echo $value['class']; ?>">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong><?php echo $key; ?> :</strong> <span> <?php echo $value['message']; ?></span>
                    </div>
                    <!-- // Alert END -->
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>