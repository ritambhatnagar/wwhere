<?php include APPPATH . '/front-modules/views/top.php'; //exit;?>
<header class="white_bg">
    <div class="container top_marg">
        <!-- Introduction Row -->
        <div class="row">
            <div class="col-lg-6 location_box">
                <h3 class="txtblue">Similar List</h3>
            </div>
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
        <div class="row pad15 padding-left-none padding-right-none">

            <div  id="myListDiv">
                <?php if (count($data) > 0) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                        <div class="col-lg-4 col-sm-6 col-sm-3 text-center marg20">
                            <div class="blue_sqr blue_sqr_inner"><a href='<?php echo $this->config->item('site_url') . $value['vUrlName'] ?>'><?php echo $value['vName']; ?></a>
                                <div class="headingtxt"><a href='<?php echo $this->config->item('site_url') . $value['vUrlName'] ?>'><?php echo $value['vAddress'] . ( $value['vCity'] != '' ? "," . $value['vCity'] : ''); ?></a></div>
                                <div class="urltext"><a href='<?php echo $this->config->item('site_url') . $value['vUrlName'] ?>'><?php echo $value['vCategory']; ?></a></div>
                                <div class="urltext"><a href='<?php echo $this->config->item('site_url') . $value['vUrlName'] ?>'><?php echo $value['vTags']; ?></a></div>
                            </div>
                            <div class="col-md-12 ">

                                <div class="col-md-6  col-xs-6  grouppadleft">
                                    <ul class="nav navbar-nav navbar-left ">
                                        <li class="dropdown inherit">
                                            <a href="<?php echo $this->config->item('site_url') . $value['vUrlName'] ?>"><?php echo  $this->config->item('site_url') . $value['vUrlName'] ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="col-md-12 ">
                        <div class="description_txt " style="border-bottom:0;">
                            <p>No Result Found</p>
                        </div>

                    </div>
                <?php } ?>
            </div>
        </div>
            <?php } ?>
    </div>
</header>
<?php include APPPATH . '/front-modules/views/bottom_script.php'; ?>