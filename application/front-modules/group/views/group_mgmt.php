<?php include APPPATH . '/front-modules/views/top.php'; //exit;          ?>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>custom/group.js"></script>
<header class="white_bg">
    <div class="container top_marg grouplist">
        <!-- Introduction Row -->
        <div class="row">
            <div class="col-lg-3 location_box">
                <h3 class="txtblue">MY GROUPS</h3>
            </div>
            
                <div class="col-lg-9">
                    <div class="location_search">                    
                        <div class="link_location_right">
                            <form action='<?php echo $this->config->item('site_url'); ?>groups' method = "GET">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Search" name="myg" value="<?php echo isset($search) ? $search : ''; ?>">
                                </div>
                            </form>
                        </div>

                        <div class="link_location_left">
                            <a href="<?php echo $this->config->item('site_url'); ?>locations"><?php echo $this->general->getTotalLocations(); ?> location(s)  |</a>
                            <a href="<?php echo $this->config->item('site_url'); ?>groups"><?php echo $this->general->getTotalGroup(); ?> Group(s)</a>            	
                        </div>
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

            </div>

            <!-- Team Members Row -->
            <div class="row">
                <div  id="myListDiv">
                    <?php if (count($group_data) > 0) { ?>
                        <?php foreach ($group_data as $key => $value) { ?>
                            <div class="col-lg-4 col-sm-6 text-center marg20">
                                <div class="blue_sqr blue_sqr_inner"><span class=""><?php echo $value['vGroup']; ?></span>
                                </div>
                                <div class="col-md-12 ">

                                    <div class="col-md-8  col-xs-8  grouppadleft">
                                        <ul class="nav navbar-nav navbar-left">
                                            <li><a href="<?php echo $this->config->item('site_url') . $value['vGroup']; ?>" class="textsetting"><?php echo $this->config->item('site_url') . $value['vGroup']; ?></a></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4 col-xs-4 grouppadright">

                                        <div class="link_location_right1">
                                            <a href="<?php // echo $this->config->item('site_url') . 'group_add_edit?id=' . $this->general->encryptData($value['iGroupId']);      ?>"></a>
                                            <a data-href="<?php echo $this->config->item('site_url'); ?>group_add_edit?id='<?php echo $this->general->encryptData($value['iGroupId']); ?>&N=Edit Group" onclick="showformpage(this)">Edit  </a>| <a href="javascript:void(0)" class="Delete delete_group" data-id="<?php echo $this->general->encryptData($value['iGroupId']); ?>">Delete</a>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="col-lg-4 col-sm-6">
                            <p>No Group</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </header>
    <?php include APPPATH . '/front-modules/views/bottom_script.php'; ?>