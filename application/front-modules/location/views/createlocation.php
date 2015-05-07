<?php include APPPATH . '/front-modules/views/top.php'; //exit;            ?>
<header class="white_bg">
    <div class="container top_marg">
        <!-- Introduction Row -->
        <div class="row">
            <h3 class="txtblue">CREATE LOCATION HERE</h3>
        </div>
        <?php
        $verification_link_data = $this->general->getVerification();
        $currentUrl = basename($this->uri->uri_string);
        if ($verification_link_data == No && $currentUrl != 'verification') {
            ?>
            <div class="">
                <p class='text-warning' style='margin:20px 0px;font-size: 15px'>You didn't verified your number please verify your number, to verify <a href='<?php echo $this->config->item('site_url') ?>verification?d=<?php echo $this->general->encryptData($this->session->userdata('iUserId')); ?>' style='color:#28a6d0'>Click Here</a></p>
            </div>
        <?php } else { ?>

            <!-- Team Members Row -->
            <div class="row">
                <div class="col-lg-4 col-sm-6 text-center">
                    <a href="<?php echo $this->config->item('site_url') . 'location_add_edit?t=' . urlencode($this->general->encryptData('Private')); ?>"  class="whitelink"	>
                        <div class="blue_circle"><span class="circletxt">Personal</span></div>
                    </a>      
                    <div class="headingtxt">Such as your home, office, gym, etc.</div>
                    <div class="urltext">E.g.-<br/>
                        wWhere.co/JackGramHome</p>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6 text-center">
                    <a href="<?php echo $this->config->item('site_url') . 'location_add_edit?t=' . urlencode($this->general->encryptData('Public')); ?>"  class="whitelink"	>
                        <div class="blue_circle"><span class="circletxt">Public</span></div>
                    </a>  
                    <div class="headingtxt">Such as your business locations</div>
                    <div class="urltext">E.g.-<br/>
                        wWhere.co/WallMartDelhi</p>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 text-center">
                    <a href="<?php echo $this->config->item('site_url') . 'location_add_edit?t=' . urlencode($this->general->encryptData('Event')); ?>"  class="whitelink"	>
                        <div class="blue_circle"><span class="circletxt">Event</span></div>
                    </a>  
                    <div class="headingtxt">Such as Time bound locations</div>
                    <div class="urltext">E.g.-<br/>
                        wWhere.co/JackGramHome</p>
                    </div>
                </div>

            </div>
        <?php } ?>
        </div>
    </header>

    <?php include APPPATH . '/front-modules/views/bottom_script.php'; ?>