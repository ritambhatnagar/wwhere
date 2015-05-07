<?php
$expiredate = substr($all['dtExpireDate'], 0, 10);
$now = date("Y-m-d");
?>
<?php include 'inc/top.php'; // Meta data and header     ?>
<link rel="stylesheet" href="<?php echo $this->config->item('bootstrap_url'); ?>bootstrap-switch/assets/lib/css/bootstrap-switch.css"/>
<link rel="stylesheet" href="<?php echo $this->config->item('bootstrap_url'); ?>select2/assets/lib/css/select2.css"/>
<link rel="stylesheet" href="<?php echo $this->config->item('assets_url'); ?>gallery/blueimp-gallery/assets/lib/css/blueimp-gallery.min.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('assets_url'); ?>gallery/blueimp-gallery/assets/custom/blueimp-gallery.less" media="screen" />

<script type="text/javascript" src="<?php echo $this->config->item('bootstrap_url'); ?>bootstrap-switch/assets/lib/js/bootstrap-switch.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('bootstrap_url'); ?>bootstrap-switch/assets/custom/js/bootstrap-switch.init.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('bootstrap_url'); ?>select2/assets/lib/js/select2.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('assets_url'); ?>gallery/blueimp-gallery/assets/lib/js/jquery.blueimp-gallery.min.js"></script>

<div class="txt_orange">
    <span class="users_right">&nbsp;</span> My Account
</div>
<div class="innerPage myaccount">
    <!-- Widget -->
    <div class="widget widget-tabs border-bottom-none">
        <!-- Widget heading -->
        <div class="widget-head" role="tabpanel">
            <ul class="nav nav-tabs" role="tablist">
                <li class=""><a class="glyphicons edit" href="#general-settings" data-toggle="tab"><i></i>General Settings</a>
                </li>
                <li class=""><a class="glyphicons settings" href="#social-settings" data-toggle="tab"><i></i>Social settings</a>
                </li>
                <li class=""><a class="glyphicons settings" href="#smtp-settings" data-toggle="tab"><i></i>SMTP settings</a>
                </li>
            </ul>
        </div>
        <!-- // Widget heading END -->
        <div class="widget-body">
            <div class="tab-content col-sm-12 pad15">
                <!-- Tab content -->
                <div class="tab-pane active" id="general-settings">
                    <!--General Settings-->
                    <form id="general-setting" action="admin/system_setting_action" method="post" class="form-horizontal">

                        <?php foreach ($appearance as $key => $value) { ?>
                            <div class="row">
                                <!--Start of 1st column-->
                                <div class="control-group">
                                    <label class="control-label col-md-3" for="name"><?php echo $value['vDesc']; ?></label>
                                    <div class="col-md-6">                                                 
                                        <input type="text" id="<?php echo $value['vName']; ?>" name="<?php echo $value['vName']; ?>" class="form-control" value="<?php echo $value['vValue']; ?>" >
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group form-actions">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Submit</button>
                            </div>
                        </div>
                    </form>
                    <!-- // Form actions END -->
                </div>
                <!-- // Tab content END -->

                <!-- Tab content -->
                <div class="tab-pane" id="social-settings">
                    <form id="frm-social" action="admin/system_setting_action" method="post" class="form-horizontal">

                        <?php foreach ($social as $k => $v) { ?>
                            <div class="row">
                                <!--Start of 1st column-->
                                <div class="control-group">
                                    <label class="control-label col-md-3" for="name"><?php echo $v['vDesc']; ?></label>
                                    <div class="col-md-6">                                                                                <input type="text" id="<?php echo $v['vName']; ?>" name="<?php echo $v['vName']; ?>" class="form-control" value="<?php echo $v['vValue']; ?>" >
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group form-actions">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="tab-pane" id="smtp-settings">
                    <form id="frm-smtp" action="admin/system_setting_action" method="post" class="form-horizontal">

                        <?php foreach ($smtp as $smtpKey => $smtpValue) { ?>
                            <div class="row">
                                <!--Start of 1st column-->
                                <div class="control-group">
                                    <label class="control-label col-md-3" for="name"><?php echo $smtpValue['vDesc']; ?></label>
                                    <div class="col-md-6">                                                 
                                        <input type="text" id="<?php echo $smtpValue['vName']; ?>" name="<?php echo $smtpValue['vName']; ?>" class="form-control" value="<?php echo $smtpValue['vValue']; ?>" >
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group form-actions">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            <!--End of Content-->
        </div>
        <!-- // Widget END -->
    </div>
</div>
<!-- END Two Column Form Content -->
<?php include 'inc/bottom_script.php'; // bottom             ?>
