<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title><?php echo $this->config->item('SITE_TITLE'); ?></title>
        <link href="http://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css">
        <link href="http://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
        <!-- Bootstrap Core CSS -->
        <link href="<?php echo $this->config->item('assets_url'); ?>assets/css/bootstrap.css" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="<?php echo $this->config->item('assets_url'); ?>assets/css/wwhere.css" rel="stylesheet">
        <!-- Custom Fonts -->
        <link href="<?php echo $this->config->item('assets_url'); ?>assets/font-awesome-4.2.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top top-nav-collapse">
        <!-- Navigation -->
        <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="row">
                    <div class="navbar-header col-xs-6">
                        <a class="navbar-brand page-scroll" href="#page-top">
                            <img src="<?php echo $this->config->item('assets_url'); ?>assets/img/logoo.png" class="img-responsive">
                        </a>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="col-xs-6 navbar-right">
                        <ul class="nav navbar-nav pull-right">
                            <!-- Hidden li included to remove active class from about link when scrolled up past about section -->
                            <li class="hidden">
                                <a href="#page-top"></a>
                            </li>
                            <li class"login_dropdown_parent">
                                <a class="login_dropdown" style="background-color:#F93" href="#login">CREATE DOMAIN</a>
                                <div class="login_dropdown_div">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <form action='user/login_action' method="post" role="form">
                                                <div class="row_login form-group">
                                                    LOGIN
                                                    <div class="control_login">
                                                        <input type="text" name="vContactNo" class="form-control" id="vContactNo" placeholder="ContactNo"/>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="row_login form-group">
                                                    <div class="control_login">
                                                        <input type="password" name="vPassword" class="form-control" id="vPassword" placeholder="Password"/>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="row_login form-group">
                                                    <div class="button_login">
                                                        <input type="submit" value="LOGIN" class="btn btn-primary"/> <a href="forgotpassword">Forgot Password?</a>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>

                                                <div class="row_login form-group">
                                                    <div class="button_login" >
                                                        New to wWhere? 

                                                        <a href="register" class="btn btn-primary" style="width: 36%;height: 40px;">REGISTER</a>

                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /.navbar-collapse -->
            </div>
            <!-- /.container -->
        </nav>
        <!-- About Section -->
        <?php
//            $login = false;
//            if($login){
        ?>
<!--        <section id="about" class="container content-section text-center">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <h2>About Grayscale</h2>
                    <p>Grayscale is a free Bootstrap 3 theme created by Start Bootstrap. It can be yours right now, simply download the template on <a href="http://startbootstrap.com/template-overviews/grayscale/">the preview page</a>. The theme is open source, and you can use it for any purpose, personal or commercial.</p>
                    <p>This theme features stock photos by <a href="http://gratisography.com/">Gratisography</a> along with a custom Google Maps skin courtesy of <a href="http://snazzymaps.com/">Snazzy Maps</a>.</p>
                    <p>Grayscale includes full HTML, CSS, and custom JavaScript files along with LESS files for easy customization.</p>
                </div>
            </div>
        </section>
         Download Section 
        <section id="download" class="content-section text-center">
            <div class="download-section">
                <div class="container">
                    <div class="col-lg-8 col-lg-offset-2">
                        <h2>Download Grayscale</h2>
                        <p>You can download Grayscale for free on the preview page at Start Bootstrap.</p>
                        <a href="http://startbootstrap.com/template-overviews/grayscale/" class="btn btn-default btn-lg">Visit Download Page</a>
                    </div>
                </div>
            </div>
        </section>
         Contact Section 
        <section id="contact" class="container content-section text-center">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <h2>Contact Start Bootstrap</h2>
                    <p>Feel free to email us to provide some feedback on our templates, give us suggestions for new templates and themes, or to just say hello!</p>
                    <p><a href="mailto:feedback@startbootstrap.com">feedback@startbootstrap.com</a>
                    </p>
                    <ul class="list-inline banner-social-buttons">
                        <li>
                            <a href="https://twitter.com/SBootstrap" class="btn btn-default btn-lg"><i class="fa fa-twitter fa-fw"></i> <span class="network-name">Twitter</span></a>
                        </li>
                        <li>
                            <a href="https://github.com/IronSummitMedia/startbootstrap" class="btn btn-default btn-lg"><i class="fa fa-github fa-fw"></i> <span class="network-name">Github</span></a>
                        </li>
                        <li>
                            <a href="https://plus.google.com/+Startbootstrap/posts" class="btn btn-default btn-lg"><i class="fa fa-google-plus fa-fw"></i> <span class="network-name">Google+</span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
         Map Section 
        <div id="map"></div>-->
        <?php
//            }
        ?>
        <!-- Footer -->
        <?php /* ?> <footer>
          <div class="container text-center">
          <p>Copyright &copy; wWhere 2014</p>
          </div>
          </footer><?php */ ?>
        <!-- jQuery -->
        <script src="<?php echo $this->config->item('assets_url'); ?>assets/js/jquery.js"></script>
        <!-- Bootstrap Core JavaScript -->
        <script src="<?php echo $this->config->item('assets_url'); ?>assets/js/bootstrap.js"></script>
        <!-- Plugin JavaScript -->
        <script src="<?php echo $this->config->item('assets_url'); ?>assets/js/jquery.easing.min.js"></script>
        <!-- Google Maps API Key - Use your own API key to enable the map feature. More information on the Google Maps API can be found at https://developers.google.com/maps/ -->
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCRngKslUGJTlibkQ3FkfTxj3Xss1UlZDA&sensor=false"></script>
        <!-- Custom Theme JavaScript -->
        <script src="<?php echo $this->config->item('assets_url'); ?>assets/js/wwhere.js"></script>
        <script src="<?php echo $this->config->item('assets_url'); ?>assets/js/custom.js"></script>
    </body>
</html>
