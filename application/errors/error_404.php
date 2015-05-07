<?php
error_reporting(0);
include APPPATH . '/config/config.php';
$session_var = unserialize($_COOKIE['dg_session']);

require_once(BASEPATH . 'database/DB.php');
$db_instance = &DB();

$DB_DATA = $db_instance->get_where('user',"iUserId = '" . $session_var['iUserId'] . "'  AND eStatus = 'Active'")->result_array();
$config['LBU_USER_NAME'] = $DB_DATA[0]['vFirstName'] .' '. $DB_DATA[0]['vLastName'];

$category = $db_instance->get_where('category',"iParentId in (2,3)  AND eStatus = 'Active'")->result_array();

$base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
$base_url .= '://' . $_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
$site_url = str_replace('admin/', '', $base_url);
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $config['SITE_TITLE']; ?></title>
        <!-- Meta -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
        <meta name="description" content="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people and businesses. wWhere users across the world exchange real time locations with their families, friends and loved ones, for free. Currently available for the Android. Coming Soon on iPhone, Blackberry, Windows Phone. Download Now">
        <meta name="keywords" content="Location, sharing, app, messenger, life360, life 360, foursquare, waze, google, maps, google maps, maps, places, startup, coolest, send location, request location, receive location, save location, estimated time, directions, navigation, direction, zippr, zeocode, iim, iima, ciie, funded, fun, fun app, android, ios, playstore, play store, local, tracking, spy, exchange, download, kitkat, venue, event, global, city, parents, kids, grandparents, spouse, employee, employer, best friend, aut locate, locate, find, search, look, cities, review ">
        <link href="<?php echo ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http'; ?>://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400" rel="stylesheet" />
        <link href='http://fonts.googleapis.com/css?family=Roboto:400,400italic,500,700,700italic' rel='stylesheet' type='text/css'>
        <link rel="shortcut icon" href="<?php echo $config['assets_url']; ?>favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" href="<?php echo $config['css_url']; ?>bootstrap.min.css"/>
        <link rel="stylesheet" href="<?php echo $config['css_url']; ?>main.css"/>
        <link rel="stylesheet" href="<?php echo $config['css_url']; ?>responsive.css"/>
        <link rel="stylesheet" href="<?php echo $config['assets_url']; ?>font-awesome/css/font-awesome.min.css"/>
        <link rel="stylesheet" href="<?php echo $config['css_url']; ?>dev_style.css"/>

        <script>
            var site_url = "<?php echo $config['site_url']; ?>";
            var rootPath = "<?php echo $config['site_url']; ?>";
            var timeout = '<?php echo $config['SESSION_TIMEOUT']; ?>';
            var site_path = '<?php echo $config['upload_path']; ?>';
        </script> 

        <script type="text/javascript" src="<?php echo $config['js_url']; ?>jquery-1.11.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $config['js_url']; ?>bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo $config['js_url']; ?>jquery.validate.min.js"></script>
        <!--<script type="text/javascript" src="<?php echo $config['js_url']; ?>custom/jquery.form.js"></script>-->
        <script type="text/javascript" src="<?php echo $config['js_url']; ?>custom/jquery.form.min.js"></script>
        <script type="text/javascript" src="<?php echo $config['js_url']; ?>jquery.notyfy.js"></script>

        <script src="<?php echo $config['assets_url']; ?>js/common.js"></script>
        <script type="text/javascript" src="<?php echo $config['js_url']; ?>custom/general.js"></script>

        <script type="text/javascript" src="<?php echo $config['js_url']; ?>custom/login.js"></script>
    </head>
    <body id="page-top" class="index">        
        <nav class="navbar navbar-default">
            <div class="container"> 
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header ">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"> <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
                    <a class="navbar-brand" href="<?php echo $config['site_url']; ?>locations"><img src="<?php echo $config['assets_url']; ?>img/logo.png" alt=""/></a> 
                    <span class="cityname cityname1"><?php echo ($session_var['currentCity'] != '') ? $session_var['currentCity'] : ''; ?> </span><span class="cityname">
                        <a href="#" data-toggle="modal" data-target="#searchModal" style="font-size:11px;" class="whitelink1"><i class="fa fa-repeat text-white"></i></a>
                    </span>
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
                                                    <span id="log_error" style="margin:5px 0px 0px 15px;color: #e74c3c;"></span>
                                                    <div class="panel-body">
                                                        <form class="form-horizontal groupform" id="validateGroupForm" method="POST" action="" autocomplete="off">
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
                                                                            <input type="button" class="btn btn-lg btn-primary btn-block"  id="groupeditsubmit" value="Submit">                                 </div>
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
                                        minLength: 1,
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

                                jQuery("#groupeditsubmit").click(function () {
                                    var sel_arr = $("#vSelectCity").val().split(',');
                                    $('#scity').val(sel_arr[0]);
                                    $('.cityname1').html(sel_arr[0]);
                                    $('#searchClose').trigger('click');
                                    $.ajax({
                                        url: rootPath + 'location/setCurrentCity',
                                        type: 'POST',
                                        data: {
                                            'city': sel_arr[0]
                                        },
                                        success: function (dataa) {
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
                            <form action="<?php echo $config['site_url'] ?>search_location" type="get">
                                <input type="search" placeholder="Search" class="Search_input catgtoggle" name="s" value="<?php echo isset($_GET['s']) ? $_GET['s'] : ''; ?>" autocomplete="off">
                                <input type="hidden" placeholder="Search" name="sc" id="scity" value="<?php echo ($session_var['currentCity'] != '') ? $session_var['currentCity'] : ''; ?><?php //echo (isset($_GET['sc']) && $_GET['sc'] != '')? $_GET['sc']:'';       ?>">
                                <input type="submit" placeholder="Search" class="Search_input" style="display: none">
                                <div class="category_count category_count_inner categdiv" style="display:none;margin-top: 38px;">
                                    <div class="categorysmall  margin-top" >
                                        <h5>Categories</h5>
                                        <ul>
                                            <?php foreach ($category as $cKey => $cValue) { ?>
                                                <li>
                                                    <a href="<?php echo $config['site_url']; ?>search_location?s=<?php echo $cValue['vCategory'] ?>&sc=<?php echo ($session_var['currentCity'] != '') ? $session_var['currentCity'] : ''; ?>"/>
                                                    <span class="country"><?php echo $cValue['vCategory']; ?></span> </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </form>
                        </li>
                        <?php if ($session_var['iUserId'] == '') { ?>
                            <li style="margin-top:12px;margin-left: 10px;"> <a href="#" data-toggle="modal" data-target="#myModal" class="btn btn-default">Create Domain</a></li> 

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
                                                                                <div class="form-group">
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-addon">
                                                                                            <i class="glyphicon glyphicon-user"></i>
                                                                                        </span> 
                                                                                        <input class="form-control" placeholder="Contact Number" id="vContactNo" name="vContactNo" type="text" autofocus>
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
                                                                            </div>
                                                                        </div>
                                                                    </fieldset>
                                                                </form>
                                                            </div>
                                                            <div class="panel-footer ">
                                                                Don't have an account! <a href="register" onClick="" class="orangelink"> Sign Up Here </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <li>
                                <ul class="nav navbar-nav navbar-right">
                                    <li class="dropdown dropdown_marg">
                                        <a href="<?php echo $config['site_url']; ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $config['LBU_USER_NAME']; ?><span class="caret"></span></a>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="<?php echo $config['site_url']; ?>locations">MY Locations </a></li>
                                            <li><a href="<?php echo $config['site_url']; ?>my_profile?d=<?php echo encryptData($session_var['iUserId']); ?>">EDIT PROFILE</a></li>
                                            <li><a href="<?php echo $config['site_url']; ?>">DOWNLOAD wWHERE</a></li>
                                            <li class="divider"></li>
                                            <li><a href="<?php echo $config['site_url']; ?>logout">LOG OUT</a></li>
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

        <script>
            var currentlat = 0;
            var currentlong = 0;
            $(document).ready(function () {

                var x = document.getElementById("demo");
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                } else {
                    x.innerHTML = "Geolocation is not supported by this browser.";
                }
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
<?php if (($session_var['currentCity'] == '')) { ?>
    <?php // if(!isset($_GET['sc']) || $_GET['sc'] == '') {      ?>
                            $('.cityname1').html(data);
                            $('#scity').val(data);
<?php } ?>
                    }
                });
            }
        </script>
        <!-- Header -->
        <header class="white_bg">
            <div class="container ">
                <div class="row">
                    <div class="col-lg-12 ">
                        <div class="col-md-10 col-md-offset-1">
                            <div class="error_main">
                                <div class="erroe_bg">
                                    <div class="text_error">
                                        <b>404 ERROR</b>
                                    </div>

                                    <img src="<?php echo $config['assets_url']; ?>img/error_img.png" alt=""/> 

                                    <div class="text_error">
                                        <p>Looks like the page you wanted has gone for ride Go to our <a href="<?php echo $config['site_url']; ?>" class="bluelink">Home Page</a><br>or Go <a href="javascript:window.history.back()" class="bluelink">Back to the Page</a></p>
                                    </div>         
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        
        </header>
        <a id="viewformlink" href="#viewform" data-toggle="modal" class="btn btn-primary" style="display: none"><i class="fa fa-fw fa-plus"></i></a>
        <!-- Modal -->
        <div class="modal fade" id="viewform">
            <div class="modal-dialog" style="height: auto;width: 50%;">
                <div class="modal-content" >
                    <!-- Modal heading -->
                    <div class="modal-header">
                        <button type="button" class="close" id="formClose" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title" ><span id="viewformtitle"></span></h3>
                    </div>
                    <!-- // Modal heading END -->
                    <!-- Modal body -->
                    <div class="modal-body">
                        <div class="innerLR">
                            <div id="viewformiframe" style="width: 100%;"></div>
                        </div>
                    </div>

                    <!-- // Modal body END -->
                </div> 
            </div>
        </div>
    </body>    
</html>
<?php

function encryptData($input) {
    $output = trim(base64_encode(base64_encode($input)), '==');
    $output = encrypt($input);
    //$output = encrypt_decrypt('encrypt', $input);
    return $output;
}

function encrypt($sData, $sKey = 'wWw') {
    $sResult = '';
    for ($i = 0; $i < strlen($sData); $i++) {
        $sChar = substr($sData, $i, 1);
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
        $sChar = chr(ord($sChar) + ord($sKeyChar));
        $sResult .= $sChar;
    }
    return encode_base64($sResult);
}

function encode_base64($sData) {
    $sBase64 = trim(base64_encode($sData), '=');
    return strtr($sBase64, '+/', '-_');
}
?>