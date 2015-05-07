<?php include APPPATH . '/front-modules/views/top.php'; ?>
<script src="<?php echo $this->config->item('js_url'); ?>custom/register.js"></script>
<link href="<?php echo $this->config->item('css_url'); ?>jquery-ui.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jquery-ui.min.js"></script>
<!-- Header -->
<header class="white_bg">
    <div class="container top_marg">
        <div class="row">
            <div class="col-md-6 col-xs-12" style="float:none !important;margin: 0 auto;">
                <h3 class="txtblue">SIGN UP HERE</h3>
                <form id="validateSignupForm" action="user/register_action" method="post">
                        <div class='form-group'>
                            <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="vFirstName" name="vFirstName" placeholder="First name">    
<!--                                     <input type="text" class="form-control" id="vLastName" name="vLastName" placeholder="Last name"> -->
                            </div>
                        </div>

                        <div class='form-group'>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-globe"></i>
                                </span>
                                <select name="iCountryId" id="iCountryId" class="form-control select-chosen">
                                    <option value="">Select a country</option>
                                    <?php for ($i = 0; $i < count($country); $i++) { ?>
                                        <option value="<?php echo $country[$i]['iCountryId'] . "|" . $country[$i]['vDialCode']; ?>"><?php echo $country[$i]['vCountry'] . " ( +".$country[$i]['vDialCode']." )"; ?></option>
                                    <?php } ?>
                                </select>

                            </div>
                        </div>

                        <div class='form-group'>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-phone"></i>
                                </span>
                                <input name="vContactNo" id="vContactNo" class="form-control" placeholder="Your contact number" type="text">
                                <input type="text" class="form-control" id="ccode"  style="display: none;" placeholder="Dial Code" disabled="disabled"/>
                            </div>
                        </div>

                        <div class='form-group'>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="vPassword" name="vPassword" placeholder="Password">

                            </div>
                        </div>

                        <div class='form-group'>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="vPassword2" name="vPassword2" placeholder="Confirm password">

                            </div>
                        </div>

                        <div class='form-group'>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="vEmail" name="vEmail" placeholder="Email address">

                            </div>
                        </div>

                        <div class='form-group'>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="glyphicon glyphicon-map-marker"></i>
                                </span>
                                <input name="vCity" id="vCity" class="form-control" type="text" placeholder="City">
                                <div id="autocomplete_list" style="top:0; position: relative; left:0; margin-top: 42px;"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-group">
                                <input type="submit" class="btn btn-lg btn-warning btn-block" id="submit" value="SIGN UP">
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
</header>

<?php include APPPATH . "/front-modules/views/bottom_script.php"; ?>
<?php include APPPATH . '/front-modules/views/main_footer.php'; //exit;      ?>