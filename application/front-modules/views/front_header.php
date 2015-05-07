<header class="header header-two">
    <div class="header-wrapper index-page">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-2 col-lg-3 logo-box">
                    <div class="logo">
                        <a href="<?php echo $this->config->item('site_url'); ?>">
                            <img src="<?php echo $this->config->item('images_url'); ?>logo.png" class="logo-img" alt="">
                        </a>
                    </div>
                    <?php $currentUrl = str_replace('.html','',basename($_SERVER['REQUEST_URI'])); ?>
                </div><!-- .logo-box -->

                <div class="col-xs-12 col-sm-6 col-md-10 col-lg-9 right-box">
                    <div class="right-box-wrapper">
                        <div class="header-icons">
                            <ul class="nav navbar-nav navbar-right right_nav">				                                                                              
                                <?php 
                                if ($this->session->userdata('iUserId') > 0) { ?>
                                    <li>
                                        <div class="profile_pic">
                                            <?php
                                            $img_url = $this->config->item('images_url') . 'noimage.png';
                                            if ($this->config->item('LBU_USER_PROFILE_IMAGE') != '') {
                                                $img = 'public/upload/users/' . $this->session->userdata('iUserId') . '/' . $this->config->item('LBU_USER_PROFILE_IMAGE');
                                                $img_path = $this->config->item('site_path') . $img;
                                                if (file_exists($img_path)) {
                                                    $img_url = $this->config->item('site_url') . $img;
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo $img_url; ?>" alt="" id="topuserimg"/>
                                        </div>
                                    <li>
                                        <a href="<?php echo $this->config->item('site_url'); ?>dashboard">
                                            <?php echo (trim($this->config->item('LBU_USER_NAME')) != '') ? $this->config->item('LBU_USER_NAME') : 'Update Profile'; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo $this->config->item('site_url'); ?>signout" data-toggle="tooltip" data-original-title="Signout" data-placement="bottom" title="signout" class="btn btn-success btn-sm btn_hover"><i class="fa fa-sign-out"></i></a>
                                    </li>
                                <?php } else { ?>

                                    <li><a href="<?php echo $this->config->item('site_url'); ?>signin">Log in </a></li>
                                    <li><a href="<?php echo $this->config->item('site_url'); ?>signup">Sign up</a></li>
                                    <li><a href="<?php echo $this->config->item('site_url'); ?>signup" class="btn btn-success btn-sm btn_hover">Start my demo</a></li>
                                <?php } ?>
                            </ul>
                        </div><!-- .header-icons -->
                        <div class="primary">
                            <div class="navbar navbar-default" role="navigation">
                                <button type="button" class="navbar-toggle btn-navbar collapsed" data-toggle="collapse" data-target=".primary .navbar-collapse">
                                    <span class="text">Menu</span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>

                                <nav class="collapse collapsing navbar-collapse">
                                    <ul class="nav navbar-nav navbar-center">
                                        <li><a class="scroll <?php echo ($currentUrl == '') ? "class='active'":''?>" href="#home">Home</a></li>
                                        <li><a class="scroll" href="#about-us">About Us</a></li>
                                        <li class="parent"><a class="scroll" href="#Feature_Sec">Features</a>
                                            <!--                                                            <ul class="sub">
                                                                                                            <li><a href="index.html">Feature 1</a></li>
                                                                                                            <li><a href="index-one-page.html">Feature 2</a></li>
                                                                                                            <li><a href="index-one-page.html">Feature 3</a></li>
                                                                                                            <li><a href="index-one-page.html">Feature 4</a></li>
                                                                                                            <li><a href="index-one-page.html">Feature 5</a></li>
                                                                                                        </ul>-->
                                        </li>
                                        <li><a class="scroll <?php echo ($currentUrl == 'pricing') ? "active":''?>" href="#Pricing_Sec">Pricing</a></li>
                                        <li><a <?php echo ($currentUrl == 'get_top') ? "class='active'":''?>href="<?php echo $this->config->item('site_url'); ?>blog" target="_blank">Blog</a></li>
                                        <li><a class="scroll" href="#Contact_Us">Contact Us</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div><!-- .primary -->
                    </div>
                </div>
            </div><!--.row -->
        </div>
    </div><!-- .header-wrapper -->
</header><!-- .header -->
<div class="clearfix"></div>
