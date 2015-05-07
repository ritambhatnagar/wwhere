<?php 
$cityy = $this->session->userdata['currentCity'];
$category = $this->general->top_Category($cityy);?>
<?php $country = $this->general->getCountry(); ?>
<?php $title = $this->general->getTitle(); //pr($this->config->item('SITE_TITLE'));exit;     ?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="en" http-equiv="Content-Language">
        <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
        <title><?php echo $this->config->item('SITE_TITLE'); ?></title>
        <!-- Meta -->
    <?php 
        if( $data[0]['eType'] === "Private") { 
    ?>
        <meta name="robots" content="noindex, nofollow" />
    <?php } else if ($data === NULL) {?>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
        <meta name="description" content="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people and businesses. wWhere users across the world exchange real time locations with their families, friends and loved ones, for free. Currently available for the Android. Coming Soon on iPhone, Blackberry, Windows Phone. Download Now">
        <meta name="keywords" content="Location, sharing, app, messenger, life360, life 360, foursquare, waze, google, maps, google maps, maps, places, startup, coolest, send location, request location, receive location, save location, estimated time, directions, navigation, direction, zippr, zeocode, iim, iima, ciie, funded, fun, fun app, android, ios, playstore, play store, local, tracking, spy, exchange, download, kitkat, venue, event, global, city, parents, kids, grandparents, spouse, employee, employer, best friend, aut locate, locate, find, search, look, cities, review ">    
    <?php } else {?>    
        <meta name="robots" content="index, follow" />
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="application-name" content="wWhere">
        <meta name="description" content="<?php echo $data[0]['vDescription']; ?>">
        <meta name="keywords" content="<?php echo $data[0]['vName'].' '.$data[0]['vDescription'].' '. $data[0]['vAddress'].' '. $data[0]['vTags'];?>">
        <meta content="<?php echo $data[0]['vName']; ?>" property="og:title">
        <meta content="<?php echo $data[0]['vDescription'] . " Get Directions to " . $data[0]['vName'] . $data[0]['vAddress'] . '. Check pictures, timings, contact details of ' . $data[0]['vName'] . ' at wWhere. Changing the way you exchange locations and addresses.'; ?>" property="og:description">
        <meta content="<?php echo $this->config->item('site_url') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $images[0]['vImage']; ?>" property="og:image">
        <meta content="<?php echo "http://wwhere.is/".$data[0]['vUrlName']; ?>" property="og:url">
        <meta content="wWhere" property="og:site_name">
        <meta name="twitter:site" content="@wWhereapp">
        <meta name="twitter:creator" content="@wwhereapp">
        <meta name="twitter:card" content="<?php echo $data[0]['vDescription']; ?>">
        <meta name="twitter:url" content="<?php echo $data[0]['vUrlName']; ?>">
        <meta name="twitter:description" content="<?php echo $data[0]['vDescription']; ?>">
        <meta name="twitter:title" content="<?php echo $data[0]['vName']; ?>">
        <meta name="twitter:image" content="<?php echo $data[0]['imgurl']; ?>">
    <?php }?>
        <!-- Meta -->
        <link href="<?php echo ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http'; ?>://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400" rel="stylesheet" />
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        <link href='http://fonts.googleapis.com/css?family=Roboto:100,300,100italic,400' rel='stylesheet' type='text/css'>
        <link href="<?php echo $this->config->item('css_url'); ?>dropzone.css" rel="stylesheet">
        <?php include APPPATH . '/front-modules/views/common_files.php'; ?>
        <script type="text/javascript">var switchTo5x = true;</script>
        <script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
        <script type="text/javascript">stLight.options({publisher: "ae8f0651-ad5b-41f1-a46e-205a610313f7", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
        <script>
            (function (i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-55818731-2', 'auto');
            ga('send', 'pageview');

        </script>
    </head>
    <body id="page-top" class="index"> 
        <!-- Navigation -->
        <?php
        $vSelectedMenu = array('', 'index', 'staticpages', 'location_detail', 'search_location', 'similar_location', 'register', 'verification', 'forgotpassword');
        $noAddBorder = array('location_detail', 'search_location', 'similar_location', 'register', 'verification', 'forgotpassword');
        $currentUrl = $this->router->fetch_method();
        ?>

        <?php if($this->router->fetch_class() == "user" && $this->router->fetch_method() == "index") { ?>
            <nav class="navbar navbar-default <?php echo (!in_array($currentUrl, $noAddBorder) && in_array($currentUrl, $vSelectedMenu) ? "" : "fixed_header"); ?>">
        <?php } else { ?>
            <nav class="navbar snavbar navbar-default <?php echo (!in_array($currentUrl, $noAddBorder) && in_array($currentUrl, $vSelectedMenu) ? "" : "fixed_header"); ?>">
        <?php } ?>
<!--             <div class="container <?php echo (!in_array($currentUrl, $noAddBorder) && in_array($currentUrl, $vSelectedMenu) ? "border-bottom" : ""); ?>">  -->
            <div class="container"> 
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header ">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"> <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
                    <a class="navbar-brand" href="<?php echo $this->config->item('site_url'); ?>locations"><img src="<?php echo $this->config->item('assets_url'); ?>img/logo.png" class="logo_pad" alt=""/></a> 
                    <span class="cityname cityname1"><?php echo ($this->session->userdata('currentCity') != '') ? $this->session->userdata('currentCity') : ''; ?>  <a href="#" data-toggle="modal" data-target="#searchModal" style="font-size:11px;" class="whitelink1"><i class="fa fa-repeat text-white"></i></a></span>
                    <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="border:0px;">
                                    <button type="button" class="close" id="searchClose" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h3 class="modal-title" ><span id="viewformtitle"><strong>SELECT YOUR CITY</strong></span></h3>
                                </div>
                                <div class="modal-body">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-sm-9 col-md-6 ">
                                                <div class="panel panel-default">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" method="POST" action="" autocomplete="off">
                                                            <fieldset>
                                                                <div class="row">
                                                                    <div class="col-sm-12 col-md-10  col-md-offset-1 ">
                                                                        <div class="form-group">
                                                                            <div class="input-group">
                                                                                <span class="input-group-addon">
                                                                                    <i class="glyphicon glyphicon-map-marker"></i>
                                                                                </span> 
                                                                                <input type="text" id="vSelectCity" name="vCity" class="form-control" placeholder="City" <?php
                                                                                if (isset($user_id)) {
                                                                                    echo "value='" . $all['vCity'] . "'";
                                                                                }
                                                                                ?>>
                                                                                <span id="vCityErr" class="help-block"></span>
                                                                                <div id="autocomplete_city_list" style="top:0; position: relative; left:0; margin-top: 42px;"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <input type="button" class="btn btn-lg btn-primary btn-block"  id="selectecitysubmit" value="Submit">                                 </div>
                                                                    </div>
                                                                </div>
                                                            </fieldset>
                                                        </form>
                                                    </div>                                       
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            $(document).ready(function () {
                                setTimeout(function () {
                                    jQuery("#vSelectCity").autocomplete({
                                        source: function (request, response) {
                                            jQuery.getJSON(
                                                    "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + $('#vSelectCity').val(),
                                                    function (data) {
                                                        response(data);
                                                    }
                                            );
                                        },
                                        minLength: 3,
                                        select: function (event, ui) {
                                            var selectedObj = ui.item;
                                            jQuery("#vSelectCity").val(selectedObj.value);
                                            return false;
                                        },
                                        open: function () {
                                            jQuery(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                                        },
                                        close: function () {
                                            jQuery(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                                        },
                                        appendTo: $('#autocomplete_city_list')
                                    });
                                    jQuery("#vSelectCity").autocomplete("option", "delay", 100);
                                }, 300);

                                jQuery("#selectecitysubmit").click(function () {
                                    var sel_arr = $("#vSelectCity").val().split(',');
                                    $('#scity').val(sel_arr[0]);
                                    $('.cityname1').html(sel_arr[0]);
                                    $('#searchClose').trigger('click');
                                    $.ajax({
                                        url: rootPath + 'location/setCurrentCity',
                                        type: 'POST',
                                        data: {
                                            'city': sel_arr[0],
                                            'searchCity': false,
                                            'searchObj': searchObj
                                        },
                                        success: function (dataa) {
                                            if(dataa != "1") {
                                                window.location = dataa;
                                                return true;
                                            } 
                                            $('#searchClose').trigger('click');
                                            return true;
                                        }
                                    });
                                });
                            });
                        </script>
                    </div>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <li class="hidden"> <a href="#page-top"></a> </li>
                        <li class="page-scroll searchpadding"> 
                            <a href="javascript:void(0)"></a>
                            <form action="<?php echo $this->config->item('site_url') ?>search_location" type="get">
                                <input type="search" placeholder="Search" class="Search_input catgtoggle " name="s" value="<?php echo isset($_GET['s']) ? $_GET['s'] : ''; ?>" autocomplete="off" required="required">
                                <input type="hidden" placeholder="Search" name="sc" id="scity" value="<?php echo ($this->session->userdata('currentCity') != '') ? $this->session->userdata('currentCity') : ''; ?><?php //echo (isset($_GET['sc']) && $_GET['sc'] != '')? $_GET['sc']:'';              ?>">
                                <input type="submit" placeholder="Search" class="Search_input" style="display: none">
                                <div class="category_count category_count_inner categdiv" style="display:none;margin-top: 38px;">
                                    <div class="categorysmall  margin-top" >
                                        <h5>Categories</h5>
                                        <ul>
                                            <?php foreach ($category as $cKey => $cValue) { ?>
                                                <li>
                                                    <a href="<?php echo $this->config->item('site_url'); ?>search_location?s=<?php echo $cValue['vCategory'] ?>&sc=<?php echo ($this->session->userdata('currentCity') != '') ? $this->session->userdata('currentCity') : ''; ?>"/>
                                                    <span class="country"><?php echo $cValue['vCategory']; ?></span> </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </form>
                        </li>
                        <?php if ($this->session->userdata('iUserId') == '') { ?>
                            <li class="margin_left10px"> <a href="#" data-toggle="modal" data-target="#myModal" class="btn btn-default">Create Location</a></li> 

                        <?php } else { ?>
                            <li>
                                <ul class="nav navbar-nav navbar-right">
                                    <li class="dropdown dropdown_marg">
                                        <a href="<?php echo $this->config->item('site_url'); ?>" class="dropdown-toggle" style="font-weight: bold;" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $this->config->item('LBU_USER_NAME'); ?><span class="caret"></span></a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="<?php echo $this->config->item('site_url'); ?>locations">MY Locations </a></li>
                                            <li><a href="<?php echo $this->config->item('site_url'); ?>my_profile?d=<?php echo $this->general->encryptData($this->session->userdata('iUserId')); ?>">EDIT PROFILE</a></li>
                                            <li><a href="<?php echo $this->config->item('site_url'); ?>">DOWNLOAD wWHERE</a></li>
                                            <li class="divider"></li>
                                            <li><a href="<?php echo $this->config->item('site_url'); ?>logout">LOG OUT</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>

                    </ul>
                    
                </div>
                <!-- /.navbar-collapse --> 
            </div>
            <!-- /.container-fluid --> 
        </nav>
        
        <!-- Modal -->
                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="border:0px;">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="container" style="margin-top:40px">
                                        <div class="row">
                                            <div class="col-sm-9 col-md-6 ">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <strong>Sign in to continue</strong>
                                                    </div>
                                                    <span id="log_error" style="margin:5px 0px 0px 15px;color: #e74c3c;"></span>
                                                    <div class="panel-body">
                                                        <form role="form" action="user/login_action" method="POST" id="frm-login">
                                                            <fieldset>
                                                                <div class="row">
                                                                    <div class="col-sm-12 col-md-10  col-md-offset-1 ">
                                                                        <div class='form-group'>
                                                                            <div class="input-group">
                                                                                <span class="input-group-addon">
                                                                                    <i class="glyphicon glyphicon-globe"></i>
                                                                                </span>
                                                                                    <select name="iCountryId1" id="iCountryId1" class="form-control select-chosen ">
                                                                                        <option value="">Select a Country</option>
                                                                                        <?php for ($i = 0; $i < count($country); $i++) { ?>
                                                                                            <option value="<?php echo $country[$i]['iCountryId'] . "|" . $country[$i]['vDialCode']; ?>" data-code="<?php echo $country[$i]['vCountryCode']; ?>" <?php if ($this->session->userdata('currentCountry') == $country[$i]['vCountryCode']) { ?> selected="selected"<?php } ?>><?php echo $country[$i]['vCountry']; ?></option>
                                                                                        <?php } ?>
                                                                                    </select>

                                                                                <div class="col-md-6 pull-right padding-right-none hidden">
                                                                                    <input type="text" class="form-control" id="ccode1" placeholder="Dial Code" disabled="disabled"/>
                                                                                </div>
                                                                            </div>
                                                                            <span id="iCountryId1Err"></span>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <div class="input-group">
                                                                                <span class="input-group-addon">
                                                                                    <i class="glyphicon glyphicon-user"></i>
                                                                                </span> 
                                                                                <input class="form-control" placeholder="Contact Number" autocomplete="off" id="vContactNo" name="vContactNo" type="text" autofocus>
                                                                                <span id="vContactNoErr"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <div class="input-group">
                                                                                <span class="input-group-addon">
                                                                                    <i class="glyphicon glyphicon-lock"></i>
                                                                                </span>
                                                                                <input class="form-control" placeholder="Password" name="vPassword" id="Password" type="password" value="">
                                                                                <span id="vPasswordErr"></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <input type="submit" class="btn btn-lg btn-primary btn-block" value="Sign in">
                                                                        </div>
                                                                        <div class="form-group">

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </fieldset>
                                                        </form>
                                                    </div>
                                                    <div class="panel-footer" style="padding-bottom: 30px;">
                                                        <span class="pull-left">Don't have an account! <a href="register" onClick="" class="orangelink"> Sign Up Here </a></span>
                                                        <span class="pull-right"><a href="<?php echo $this->config->item('site_url'); ?>forgotpassword" class="text-uppercase orangelink">Forgot Password?</a></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

        <?php include APPPATH . '/front-modules/views/notification_message.php'; //footer file   ?>
<?php 
    if($searchCity == "false") {
?>
        <script>
    $('#searchModal').modal('show');
        var searchObj = "<?php echo $_GET['s'];?>";
    </script>
<?php
    }
?>

        <script>
            var currentlat = 0;
            var currentlong = 0;
            $(document).ready(function () {







                $('#iCountryId1').change(function () {
                    var code = $(this).val();
                    var code_data = code.split('|');
                    $('#ccode1').val("+" + code_data[1]);
                });

                var x = document.getElementById("demo");
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                } else {
                    x.innerHTML = "Geolocation is not supported by this browser.";
                }


<?php if ($this->session->userdata('iUserId') == '') { ?>
                    if ($('#iCountryId1 :selected').val() != '') {
                        var code_data = $('#iCountryId1 :selected').val().split('|');
                        $('#ccode1').val("+" + code_data[1]);
                    }
<?php } ?>
            });
            function showPosition(position) {
                currentlat = position.coords.latitude;
                currentlong = position.coords.longitude;
                $.ajax({
                    url: rootPath + 'location/sessonlatlng',
                    type: 'POST',
                    data: {
                        'lat': currentlat,
                        'long': currentlong
                    },
                    success: function (data) {
                        //window.location.reload();
<?php if (($this->session->userdata('currentCity') == '')) { ?>
    <?php // if(!isset($_GET['sc']) || $_GET['sc'] == '') {             ?>
                            var $result = $.parseJSON(data);
                            $('.cityname1').html($result.city);
                            $('#scity').val($result.city);
                            $('#iCountryId1').find('option').each(function (e) {
                                if ($(this).data('code') == $result.country)
                                {
                                    $('#iCountryId1').val($(this).val());
                                    var code_data = $(this).val().split('|');
                                    $('#ccode1').val("+" + code_data[1]);
                                }
                            });
<?php } ?>
                    }
                });
            }
        </script>