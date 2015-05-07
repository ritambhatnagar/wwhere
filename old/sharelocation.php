<!DOCTYPE html>
<?php
    $lat  = isset($_GET['lat'])?$_GET['lat']:"";
    $long = isset($_GET['long'])?$_GET['long']:"";
    $add  = isset($_GET['add'])?$_GET['add']:"Hey, I am here";
    
    $responseObject = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$long.'&key=AIzaSyCdcPX8fzlVcHAeMN690eqL0c7f0NBnarE'));
    
    $googleAddress  = $responseObject->results[0]->formatted_address;
?>
<html lang="en">
    <head>
        <title>wWhere - World's First Location Messenger</title>
        <meta charset="utf-8">
        <meta name="description" content="Sharing addresses was never so convenient">
        <meta name="keywords" content="Location, Android, Messenger, ios, ciie, iim, ahmedabad, startup, maps, directions, exchange, places, event, venue, alpha testing, beta testing, incubated, funding, wwhere, where, india, silicon valley, new app, app, application, mobile app, mobile app 2014, 2014, UI">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/demo.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-switch.css">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.0.js"></script>
        <script src="js/jquery.backstretch.min.js"></script>
        <script src="js/bootstrap-switch.js"></script>
        <script src="js/kinetic.js"></script>
        
        <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCdcPX8fzlVcHAeMN690eqL0c7f0NBnarE&sensor=false"></script>
        
        <script>
            $(document).ready(function(){
                var height = $( window ).height();
                height = parseInt(height - (height*30)/100);
                $('#googleMap').css('height',height);
            })
            function initialize(){
                
                var myCenter = new google.maps.LatLng(<?php echo "$lat,$long";?>);
                    
                var mapProp  = {
                    center:myCenter,
                    zoom:13,
                    mapTypeId:google.maps.MapTypeId.ROADMAP
                };
                    
                var map = new google.maps.Map(document.getElementById("googleMap"),mapProp);
                    
                var marker  =   new google.maps.Marker({
                                    position:myCenter,
                                    animation:google.maps.Animation.DROP,
                                    icon:'img/marker_icon.png'
                                });
                var infowindow = new google.maps.InfoWindow({
                                    content:"<?php echo $add;?>"
                                });
                  
                marker.setMap(map);
                    
                google.maps.event.addListener(marker, 'click', function () { infowindow.open(map, marker) });
                
            }
            google.maps.event.addDomListener(window, 'load', initialize);
        </script>
    </head>

    <body>
        <div class="container-fluid">
            <div class="row logo_header">
                <div class="col-md-2 col-md-offset-2">
                    <img src="img/logo_header3.png" class="img-responsive"/>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div id="googleMap">
                        
                    </div>
                    <div class="googleAddress">
                        <p><?php echo $googleAddress;?></p>
                    </div>
                    <div class="wwherebutton">
                        <p class="wwhere_message">Send your location back using wWhere</p>
                        <button class="btn btn-info"><a href="https://play.google.com/store/apps/details?id=com.locator.wwhere">Download wWhere for Android</a></button>
                    </div>
                </div>
            </div>
            <div class="row footer">
                <p>wWhere is the World's First Location Messenger, meant for changing the way we exchange locations and address.</p>
                <p>wWhere is currently in its Beta stage and available for all Android phones across the world</p>
                <p class="copyright">(c) 2014, wWhere | for Business, Contact us on business@wwhere.com </p>
            </div>
        </div>
    </body>
</html>