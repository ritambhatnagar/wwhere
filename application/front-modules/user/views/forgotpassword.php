<?php include APPPATH . '/front-modules/views/top.php'; ?>
<script src="<?php echo $this->config->item('js_url'); ?>custom/forgotpassword.js"></script>
<!-- Header -->
<header class="white_bg">
    <div class="container top_marg">
        <div class="row">
            <div class="col-lg-6  col-lg-offset-3">
                <h3 class="txtblue" style="margin:10px 0px">Forgot Password</h3>
                <form action="<?php echo $this->config->item('site_url');?>user/forgotpassword_action" method="post" id="forgot_password">
                    <div class='col-md-12' style="color:#CCC">

                        <div class="form-group">
                            <select name="cc" id="cc" class="form-control select-chosen">
                                <option value="">Select a Country</option>
                                <?php for ($i = 0; $i < count($country); $i++) { ?>
                                <option value="<?php echo $country[$i]['vDialCode']; ?>" data-code="<?php echo $country[$i]['vCountryCode']; ?>" <?php if ($this->session->userdata('currentCountry') == $country[$i]['vCountryCode']) { ?> selected="selected"<?php } ?>><?php echo $country[$i]['vCountry']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" id="login_contact1" name="login_contact1" class="form-control" placeholder="Enter your registered contact number without country code">
                            <input type="text" id="login_contact" name="login_contact" class="col-md-4 form-control hidden" placeholder="Enter your registered contact number without country code">
                        </div>
                        
                    </div>
                    <div class="row form-group">
                            <a href="<?php echo $this->config->item('site_url');?>" class="orangelink"> Back to Login </a>
                        </div>
                        <button type="submit" class="btn btn btn-primary">Let's Go</button>
                </form>
            </div>
        </div>
    </div>
</header>

<script type="text/javascript">
    $(document).ready(function() {
        $('#login_contact1').bind('blur', function () {
            var ov = this.value;
            var v = $('#cc').val()+ov;
            $('#login_contact').val(v);
        });
    });
</script>

<?php include APPPATH . "/front-modules/views/bottom_script.php"; ?>

























