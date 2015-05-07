<?php include APPPATH . '/front-modules/views/top.php'; //exit;       ?>
<style>
    #resend_data{
        outline: 0;
        color: #28a6d0 !important;
    }
</style>
<!-- Header -->
<header class="white_bg">
    <div class="container  top_marg">
        <div class="row">


            <div class="col-lg-6  col-lg-offset-3">


                <h3 class="txtblue">Verify Number</h3>

                <form action="user/verification_action" method="post" id="verification" role="form">
                    <div class='col-xs-12' style="color:#CCC">
                        <input type="hidden" id="d" name="d" value="<?php echo ($d != '') ? $d : ''; ?>">
                        <input type="hidden" name="m" value="<?php echo ($m != '') ? $m : ''; ?>">
                        <div class="row form-group">
                            <input type="text" id="code" name="code" class="col-md-4 form-control" placeholder="Enter the four digit code here">
                        </div>
                        <div class="row form-group">
                            <a href="<?php echo $this->config->item('site_url'); ?>verification?d=<?php echo ($d != '') ? $d : ''; ?>&rs=t" id="resend_data" name="resend" class="text-warning"><input type="hidden" id="verify_id" value="<?php echo ($d != '') ? $d : ''; ?>">Resend</a> Verification Link
                        </div>

                        <div class="row form-group">
                            <button type="submit" class="btn btn-primary">Enter</button>
                            <a href="<?php echo ($this->session->userdata('iUserId') != '') ? $this->config->item('site_url').'locations' : $this->config->item('site_url').'index' ?>" class="btn btn-danger">Leave</a>
                        </div>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>
</header>

<?php include APPPATH . '/front-modules/views/bottom_script.php'; ?>