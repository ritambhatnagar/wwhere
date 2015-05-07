<?php include 'inc/config.php'; // Configuration php file ?>
<?php include 'inc/config.php'; // Configuration php file  ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Alpha2Delta</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <script type="text/javascript" src="../public/assets/js/jquery.min.js"></script>

        <!--  <link rel="stylesheet" href="../public/assets/css/style.css"> -->
        <link rel="stylesheet" href="../public/assets/css/loader-style.css">
        <link rel="stylesheet" href="../public/assets/css/bootstrap.css">
        <link rel="stylesheet" href="../public/assets/css/signin.css">

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
            <![endif]-->
        <!-- Fav and touch icons -->
        <link rel="shortcut icon" href="../public/assets/ico/minus.png">
    </head>

    <body>
        <!-- Preloader -->
        <div id="preloader">
            <div id="status">&nbsp;</div>
        </div>
        <div class="container">



            <div class="" id="login-wrapper" >
                <div class="row" >
                    <div  class="col-md-4 col-md-offset-4" align="center">
                        <div id="logo-login" align="center">
                            <img src="../public/assets/img/logo_AD.png" height="150px" style="margin-right: 30px; margin-top:10px;"/>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <div class="account-box">

                            <form action="reset_password_action.html" method="post" id="forgot_password" role="form">
                                
                                 <div class="form-group">
                                    <input type="password" id="vPassword" name="vPassword" class="form-control" placeholder="Enter your Password" tabindex="1">
                                </div>
                                <div class="form-group">
                                    <input type="password" id="vPassword2" name="vPassword2" class="form-control" placeholder="Re-type Password" tabindex="2">
                                </div>
                               <button class="btn btn btn-primary pull-right" type="submit">
                                    Submit
                                </button>
                            </form>
                            <div class="row-block">
                                <div class="row">
              
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
        <div style="text-align:center;margin:0 auto;">
            <h6 style="color:#fff;">Powered by © a 2014</h6>
        </div>
        <div id="test1" class="gmap3"></div>
        <!--  END OF PAPER WRAP -->
        <!-- MAIN EFFECT -->
        <script type="text/javascript" src="../public/assets/js/preloader.js"></script>
        <script type="text/javascript" src="../public/assets/js/bootstrap.js"></script>
        <script type="text/javascript" src="../public/assets/js/app.js"></script>
        <script type="text/javascript" src="../public/assets/js/load.js"></script>
        <script type="text/javascript" src="../public/assets/js/main.js"></script>

        <script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
        <script type="text/javascript" src="../public/assets/js/map/gmap3.js"></script>
        <script type="text/javascript">
            $(function() {

                $("#test1").gmap3({
                    marker: {
                        latLng: [-7.782893, 110.402645],
                        options: {
                            draggable: true
                        },
                        events: {
                            dragend: function(marker) {
                                $(this).gmap3({
                                    getaddress: {
                                        latLng: marker.getPosition(),
                                        callback: function(results) {
                                            var map = $(this).gmap3("get"),
                                                    infowindow = $(this).gmap3({
                                                get: "infowindow"
                                            }),
                                                    content = results && results[1] ? results && results[1].formatted_address : "no address";
                                            if (infowindow) {
                                                infowindow.open(map, marker);
                                                infowindow.setContent(content);
                                            } else {
                                                $(this).gmap3({
                                                    infowindow: {
                                                        anchor: marker,
                                                        options: {
                                                            content: content
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }
                                });
                            }
                        }
                    },
                    map: {
                        options: {
                            zoom: 15
                        }
                    }
                });

            });
        </script>
    </body>
</html>