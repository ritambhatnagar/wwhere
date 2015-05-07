<?php include APPPATH . '/front-modules/views/top.php'; //exit;                     ?>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>custom/delete.js"></script>
<header class="white_bg">
    <div class="container top_marg">
        <!-- Introduction Row -->
        <div class="row" style="position: relative">

            <div class="col-lg-3 location_box margin15pxl">
                <h3 class="txtblue pull-left">MY LOCATIONS </h3>
                <a class="txtgreylc pad8 create_pad" href="<?php echo $this->config->item('site_url') . 'createlocation'; ?>"><i class="fa fa-plus"></i> Create</a>
            </div>

            <div class="col-lg-9" style=" z-index: 100">
                <div class="location_search">
                    <div class="link_location_right">
                        <form action="<?php echo $this->config->item('site_url'); ?>locations" method="GET">
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Search" name="myl" value="<?php echo isset($search) ? $search : ''; ?>">
                            </div>
                        </form>
                    </div>
                    <div class="link_location_left">
                        <a href="<?php echo $this->config->item('site_url'); ?>locations"><?php echo $this->general->getTotalLocations(); ?> location(s)  |</a>
                        <a href="<?php echo $this->config->item('site_url'); ?>groups"><?php echo $this->general->getTotalGroup(); ?> Group(s)</a>            	
                    </div>
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
            <!-- Team Members Row -->
            <div class="row">
                <img src="<?php echo $this->config->item('assets_url');?>img/loader.gif" id="loader"/>        
                <div  id="myListDiv">
                    <?php if (count($data) > 0) { ?>
                        <?php foreach ($data as $key => $value) { ?>
                            <div class="col-lg-4 col-sm-6 text-center marg20">
                                <div class="blue_sqr blue_sqr_inner boxShadow"><span class=""><a href="<?php echo $this->config->item('site_url') . $value['vUrlName']; ?>"><?php echo $value['vName']; ?></a></span>
                                    <div class="headingtxt"><?php echo $value['vAddress'] . "," . ($value['vCity'] != '') ? $value['vCity'] : ''; ?></div>
                                    <div class="headingtxt"><?php echo $this->config->item('site_url') . $value['vUrlName']; ?>
                                    </div>
                                </div>
                                <div class="col-md-12 ">

                                    <div class="col-md-8 grouppadleft">
                                        <ul class="nav navbar-nav navbar-left" id="gp_select_<?php echo $this->general->encryptData($value['iLocationId']); ?>">
                                            <li class="dropdown inherit">
                                                <a href="#" class="dropdown-toggle  textsetting" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo ($value['select_id'] == '') ? '<span id="gp_name' . $this->general->encryptData($value['iLocationId']) . '">Add to Group</span>' : '<span id="gp_name' . $this->general->encryptData($value['iLocationId']) . '">Added to ' . $value['group_name'] . '</span>'; ?><span class="caret"></span></a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <?php echo '<li id="removeli' . $this->general->encryptData($value['iLocationId']) . '" style="display:'.($value['select_id'] != '' ? 'block' : 'none').'"><a href="#" data-lid="'.$this->general->encryptData($value['iLocationId']).'" data-cid="'.$this->general->encryptData($group_data[$i]['iGroupId']).'" class="border border-bottom remove_group">Remove From Group</a></li>';?>
                                                    <li><a href="#modal-add-form" data-toggle="modal">Create new Group</a></li>
                                                    <?php for ($i = 0; $i < count($group_data); $i++) { ?>
                                                        <li <?php echo ($group_data[$i]['iGroupId'] == $value['select_id']) ? 'class="active"' : ''; ?>><a href="javascript:void(0)" class="group_select" name="group_select" id="group_select" data-lid="<?php echo $this->general->encryptData($value['iLocationId']); ?>" data-cid="<?php echo $this->general->encryptData($group_data[$i]['iGroupId']); ?>"> <?php echo $group_data[$i]['vGroup']; ?></a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="col-md-4 grouppadright">

                                        <div class="link_location_right1">
                                            <a href="<?php echo $this->config->item('site_url') . 'createlocation?id=' . $this->general->encryptData($value['iLocationId']) . '&t=' . $this->general->encryptData($value['eType']); ?>">Edit  </a>|
                                            <a href="javascript:void(0)" class="delete_location" data-id="<?php echo $this->general->encryptData($value['iLocationId']); ?>">Delete</a>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="col-lg-4 col-sm-6">
                            <p>No Location</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php include APPPATH . '/front-modules/group/views/add_group_form.php'; ?>
        <?php } ?>
    </div>
</header>
<?php include APPPATH . '/front-modules/views/bottom_script.php'; ?>