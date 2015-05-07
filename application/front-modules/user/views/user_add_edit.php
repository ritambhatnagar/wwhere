<?php include APPPATH . 'front-modules/views/top.php'; ?>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>custom/user.js"></script>
<link href="<?php echo $this->config->item('css_url'); ?>jquery-ui.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jquery-ui.min.js"></script>
<!-- Header -->
<header class="white_bg">
    <div class="container top_marg">
        <div class="row">
            <div class="col-lg-6  col-lg-offset-3">
                <h3 class="txtblue">My Account</h3>
                <?php
                $verification_link_data = $this->general->getVerification();
                $currentUrl = basename($this->uri->uri_string);
                if ($verification_link_data == No && $currentUrl != 'verification') {
                    ?>
                    <div class="">
                        <p class='text-warning' style='margin:20px 0px;font-size: 15px'>You didn't verified your number please verify your number, to verify <a href='<?php echo $this->config->item('site_url') ?>verification?d=<?php echo $this->general->encryptData($this->session->userdata('iUserId')); ?>' style='color:#28a6d0'>Click Here</a></p>
                    </div>
                <?php } else { ?>
                    <form id="user-form" action="user/user_action" method="post" class="form-horizontal">
                        <input type="hidden" id="iUserId" name="iUserId" <?php
                        if (isset($user_id)) {
                            echo "value='" . $user_id . "'";
                        }
                        ?>>
                        <!--div.row -->
                        <div class="col-md-12">
                            <div class='form-group'>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-user"></i>
                                    </span>
                                    <div class="col-md-6 pull-left margleft">
                                        <input type="text" class="form-control " id="vFirstName" name="vFirstName" placeholder="First Name" value="<?php echo $all['vFirstName']; ?>"">    
                                    </div>
                                    <div class="col-md-6 pull-right margright">
                                        <input type="text" class="form-control " id="vLastName" name="vLastName" placeholder="Last Name" value="<?php echo $all['vLastName']; ?>">
                                    </div>
                                </div>
                            </div>
                            <?php if ($user_id == '') { ?>
                                <div id="vContactNodiv" class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-phone"></i>
                                        </span>
                                        <input type="text" id="vContactNo" name="vContactNo" class="form-control" value="<?php echo $all['vContactNo']; ?>">
                                    </div>
                                    <span id="vContactNoErr" class="help-block"></span>
                                </div>
                            <?php } else { ?>
                                <div id="vContactNodiv" class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-phone"></i>
                                        </span>
                                        <input type="text" value="<?php echo $all['vContactNo']; ?>" disabled class="form-control">

                                    </div>
                                </div>
                            <?php } ?>

                            <div class='form-group'>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-envelope"></i>
                                    </span>
                                    <input type="text" id="vEmail" name="vEmail" placeholder="Email" class="form-control" <?php
                                    if (isset($user_id)) {
                                        echo "value='" . $all['vEmail'] . "'";
                                    }
                                    ?>>
                                </div>
                            </div>

                            <?php if (isset($all) && count($all) > 0) { ?>
                                <!-- Group -->
                                <div class="form-group margin-none innerB" id="changediv">
                                    <div class="col-md-8 margleft">
                                        <div class="input-group">
                                            <span class="btn btn-primary" data-toggle="tooltip" data-container="body" data-placement="top" id="changeid"><i class="fa fa-repeat"></i> CHANGE &nbsp;<i class="glyphicon glyphicon-lock"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <!-- // Group END -->
                                <!-- Group -->
                                <div class="form-group margin-none innerB" id="oldpassworddiv">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-lock"></i>
                                        </span>

                                        <input type="password" id="vOldPassword" class="form-control" placeholder="Old Password" value=""> 
                                        <span id="vPasswordErr"></span>
                                    </div>
                                </div>
                                <!-- // Group END -->
                                <!-- Group -->
                                <div class="form-group margin-none innerB" id="passworddiv">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-lock"></i>
                                        </span>
                                        <input type="password" id="vPassword" name="vPassword" placeholder="New Password" class="form-control" value=""> 
                                        <span id="vPasswordErr"></span>
                                    </div>
                                </div>
                                <!-- // Group END -->
                                <!-- Group -->
                                <div class="form-group margin-none innerB" id="password2div">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-lock"></i>
                                        </span>
                                        <input type="password" id="vPassword2" placeholder="Repeat Password" class="form-control" value=""> 
                                        <span id="vPassword2Err"></span>
                                        <br>
                                    </div>
                                    <span class="btn btn-danger" data-toggle="tooltip" data-container="body" data-placement="top" id="cancelid" style="margin-top: 5px;"><i class="fa fa-times"></i> Cancel</span>
                                </div>
                                <!-- // Group END -->
                            <?php } else { ?>   
                                <!-- Group -->
                                <div class="form-group margin-none innerB">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-lock"></i>
                                        </span>
                                        <input type="password" id="vPassword" placeholder="Password" name="vPassword" class="form-control" value=""> 
                                        <span id="vPasswordErr"></span>
                                    </div>
                                </div>
                                <!-- // Group END -->
                                <!-- Group -->
                                <div class="form-group margin-none innerB">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-lock"></i>
                                        </span>
                                        <input type="password" id="vPassword2" name="vPassword2" class="form-control" placeholder="Repeat Password" value=""> 
                                        <span id="vPassword2Err"></span>
                                    </div>
                                </div>
                                <!-- // Group END -->
                            <?php } ?>

                            <div id="vAddress1div" class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-map-marker"></i>
                                    </span>
                                    <input type="text" id="vAddress1" name="vAddress1" class="form-control" placeholder="Address" <?php
                                    if (isset($user_id)) {
                                        echo "value='" . $all['vAddress1'] . "'";
                                    }
                                    ?>>
                                    <span id="vAddress1Err" class="help-block"></span>
                                </div>
                            </div>
                            <!--                                <div class='form-group'>
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="glyphicon glyphicon-globe"></i>
                                                                    </span>
                                                                    <select name="iCountryId" id="iCountryId" class="form-control select-chosen">
                                                                        <option value="">Select Country</option>
                            <?php foreach ($country as $k => $val) { ?>
                                <?php if ($val['iCountryId'] == $all['iCountryId']) { ?>
                                                                                                                                                        <option value="<?php echo $val['iCountryId'] ?>" selected="selected"><?php echo $val['vCountry'] ?></option>
                                <?php } else { ?>
                                                                                                                                                        <option value="<?php echo $val['iCountryId'] ?>"><?php echo $val['vCountry'] ?></option>
                                <?php } ?>
                            <?php } ?>
                            
                                                                    </select>
                                                                </div>
                                                            </div>
                            
                                                            <div id="vStatediv" class="form-group">
                                                                <div class="input-group">
                                                                    <span class="input-group-addon">
                                                                        <i class="glyphicon glyphicon-map-marker"></i>
                                                                    </span>
                                                                    <input type="text" id="vState" name="vState" class="form-control" placeholder="State" <?php
                            if (isset($user_id)) {
                                echo "value='" . $all['vState'] . "'";
                            }
                            ?>>
                                                                    <span id="vStateErr" class="help-block"></span>
                                                                </div>
                                                            </div>-->

                            <div id="vCitydiv" class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-map-marker"></i>
                                    </span>
                                    <input type="text" id="vCity" name="vCity" class="form-control" placeholder="City" <?php
                                    if (isset($user_id)) {
                                        echo "value='" . $all['vCity'] . "'";
                                    }
                                    ?>>
                                    <span id="vCityErr" class="help-block"></span>
                                    <div id="autocomplete_list" style="top:0; position: relative; left:0; margin-top: 42px;"></div>
                                </div>
                            </div>

                            <div id="vZipcodediv" class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-question-sign"></i>
                                    </span>
                                    <input type="text" id="vZipcode" name="vZipcode" class="form-control" placeholder="Zipcode" value="<?php echo $all['vZipcode']; ?>">

                                    <span id="vZipcodeErr" class="help-block"></span>
                                </div>
                            </div>

                        </div>
                        <div class="form-group form-actions">
                            <div style="margin-left: 15px; float:left; " >
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Submit</button>
                                <a href="<?php echo $this->config->item('site_url');?>locations" class="btn btn-danger"><i class="entypo-cancel-squared"></i> Cancel</a>
                            </div>
                        </div>
                
                <!--   END div.row -->
                </form>
                <?php } ?>
                </div>
            </div>
        </div>
    </div>
    </header>
    <?php include APPPATH . 'front-modules/views/bottom_script.php'; ?>