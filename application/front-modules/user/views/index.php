<?php include APPPATH . "front-modules/views/top.php"; ?>
<script src="<?php echo $this->config->item('js_url'); ?>custom/login.js"></script>

<!-- Header -->
<header>
    <div class="container <?php
                $vSelectedMenu = array('', 'index', 'staticpages');
                $currentUrl = basename($this->uri->uri_string);
                echo (in_array($currentUrl, $vSelectedMenu)) ? "border-bottom" : "";
                ?>">
        <div class="row padding_top">

            <div class="col-xs-12 col-lg-12 ">
                <div class="col-lg-6 col-sm-5">
                    <div class="phone_frame">
                        <div>
                            <iframe  src="https://www.youtube.com/embed/sCmc0-LK_l0" class="youtube_video" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>



                <div class="col-lg-6 col-sm-7">

                  
                    
  <div class="slogan"> <span >Change the way you</span> Exchange Locations
                    <br/>& Addresses <span >Forever</span> </div>

                   

                </div>



            </div>
        </div>
        
        <?php include APPPATH . 'front-modules/views/bottom_script.php'; ?>