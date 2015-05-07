<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
<?php include APPPATH . '/front-modules/views/top.php'; //exit;      ?>
<?php if ($this->session->userdata('currentLat') != '' && $this->session->userdata('currentLong') != '') { ?>

<?php } ?>
<header class="white_bg">
    <div class="container top_marg">
        <?php
        $verification_link_data = $this->general->getVerification();
        $currentUrl = basename($this->uri->uri_string);
        if ($verification_link_data == No && $currentUrl != 'verification') {
            ?>
            <div class="">
                <p class='text-warning' style='margin:20px 0px;font-size: 15px'>You didn't verified your number please verify your number, to verify <a href='<?php echo $this->config->item('site_url') ?>verification?d=<?php echo $this->general->encryptData($this->session->userdata('iUserId')); ?>' style='color:#28a6d0'>Click Here</a></p>
            </div>
        <?php } else { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="media marg_bottom15px">
                            <div class="media-left">
                                <?php
                                $image_url = $this->config->item('site_url') . 'public/img/imgo.jpeg';
                                $flg = true;
                                foreach ($images as $key => $value) {

                                    if($value['eCoverImage'] != "No") {
                                        $flg = false;
                                    } else {
                                        $flg = true;
                                        break;
                                    }
                                }


                                if (is_array($images) && count($images) > 0 && $flg) {

                                    $coverimage_path = $this->config->item('site_path') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $images[0]['vImage'];
                                    $coverimage_url = $this->config->item('site_url') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $images[0]['vImage'];

                                    if (file_exists($coverimage_path)) {
                                        $image_url = $coverimage_url;
                                    }
                                }
                                ?>
                                <img src="<?php echo $image_url; ?>" class="media-object location_img"  alt="" style="width: 60px;"/>
                            </div>
                            <div class="media-body">
                                <h3 style="text-align: left;" class="txtblue"><?php echo $name; ?></h3>
                                <p style="font-size:12px; color:#777;text-align: left;" class="greytxt11"><?php echo $categoryName; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Team Members Row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-9">
                        <div class="mapbox">
                            <input type="hidden" id="vLatitude" name="vLatitude" value="<?php echo $lat; ?>">
                            <input type="hidden" id="vLongitude" name="vLongitude" value="<?php echo $long; ?>">
                            <div style="padding:0;" class="body-nest" id="Gmap">
                                <div id="locmap" class="gmap location_map">
                                    <!--<img src="<?php echo $this->config->item('site_url'); ?>public/img/loader.gif" id="loader"/>-->
                                </div>
                            </div>
                        </div> 

                        <div class="sharebox">
                            <div class="sharebox_left">
                                <h1 style="text-align: left;" class="hidden-sm hidden-md hidden-xs"><?php echo ($data[0]['vAddress'] != '') ? $data[0]['vAddress'] : '&nbsp;'; ?></h1>
                                <h1 style="text-align: center;" class="hidden-md hidden-lg"><?php echo ($data[0]['vAddress'] != '') ? $data[0]['vAddress'] : '&nbsp;'; ?></h1>
                            </div>
                            <div class="sharebox_right" id="share_dropdown">
                                <div class="btn_grey_main">
                                    <div class="btn_grey_small">
                                        <ul class="navbar-nav navbar-left" style="list-style-type: none">
                                            <li class="dropdown inherit">
                                                <a href="#" class="dropdown-toggle btn-primary" data-toggle="dropdown" role="button" aria-expanded="false">SHARE</a>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><span class='st_sharethis' st_url = "<?php echo $actual_link; ?>" displayText='ShareThis'></span></li>
                                                    <li><span class='st_facebook' st_url = "<?php echo $actual_link; ?>" displayText='Facebook' st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span></li>
                                                    <li><span class='st_twitter' st_url = "<?php echo $actual_link; ?>" displayText='Tweet' st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span></li>
                                                    <li><span class='st_googleplus' st_url = "<?php echo $actual_link; ?>" displayText='Google +' st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span></li>
                                                    <li><span class='st_linkedin' st_url = "<?php echo $actual_link; ?>" displayText='LinkedIn' st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span></li>
                                                    <li><span class='st_pinterest' st_url = "<?php echo $actual_link; ?>" displayText='Pinterest' st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span></li>
                                                    <li><span class='st_whatsapp' st_url = "<?php echo $actual_link; ?>" displayText='WhatsApp' st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span></li>
                                                    <li><span class='st_email' st_url = "<?php echo $actual_link; ?>" displayText='Email' st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="sharebox_right" id="share_button">
                                <div class="">
                                    <span class='st_sharethis' st_url = "<?php echo $actual_link; ?>" ></span>
                                    <span class='st_facebook' st_url = "<?php echo $actual_link; ?>" st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span>
                                    <span class='st_twitter' st_url = "<?php echo $actual_link; ?>" st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span>
                                    <span class='st_linkedin' st_url = "<?php echo $actual_link; ?>" st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span>
                                    <span class='st_googleplus' st_url = "<?php echo $actual_link; ?>" st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span>
                                    <span class='st_pinterest' st_url = "<?php echo $actual_link; ?>" st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span>
                                    <span class='st_whatsapp' st_url = "<?php echo $actual_link; ?>" st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span>
                                    <span class='st_email' st_url = "<?php echo $actual_link; ?>" st_title="<?php echo $this->config->item('SITE_TITLE'); ?>" st_summary="wWhere is World's First Location Sharing platform making it easier for you to exchange locations with people . . ."></span>
                                </div>
                            </div>
                            <div class="button_box hidden-lg hidden-md hidden-sm">
                                <div class="btn_blue_direction">
                                    <div class="btn_blue_small">
<!--                                        <a href="<?php // echo "https://www.google.com/maps/dir/'$currentLat,$currentLong'/'$lat,$long'/@$currentLat,$currentLong,13z/data=!3m1!4b1!4m8!4m7!1m5!1m1!1s0x395e848aba5bd449:0x4fcedd11614f6516!2m2!1d72.5713621!2d23.022505!1m0?hl=en-US"; ?>" title="GET DIRECTION" target="_blank">GET DIRECTION</a>-->
<!--                                         <a href="<?php echo "http://maps.google.com/maps?z=12&t=m&saddr=$currentLat,$currentLong&daddr=$lat,$long&sensor=TRUE"; ?>" title="GET DIRECTION" target="_blank">GET DIRECTION</a> -->
                                   <?php if($currentLat == 0.00) { ?>
                                        <a href="<?php echo "https://maps.google.com/maps?daddr=$lat,$long"; ?>" title="GET DIRECTION" target="_blank">GET DIRECTION</a>
                                    <?php } else { ?>
                                        <a href="<?php echo "https://maps.google.com/maps?saddr=$currentLat,$currentLong&daddr=$lat,$long"; ?>" title="GET DIRECTION" target="_blank">GET DIRECTION</a>
                                    <?php } ?>
 
                                    </div>
                                </div>

                                <!--                                <div class="btn_blue_direction">
                                                                    <div class="btn_blue_small">
                                                                        <a href="#" title="BOOK NOW">DOWNLOAD</a>
                                                                    </div>
                                                                </div>-->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php if ($description != '' || $tags != '' || $data[0]['vBookingUrl'] != '' || count($timing) > 0 || ($data[0]['vFBPage'] != '' || $data[0]['vInstPage'] != '' || $data[0]['vGplusPage'] != '' || $data[0]['vTweetPage'] != '')) { ?>
                            <div class="locations_box boxShadow">
                                <?php if ($tags != '') { ?>
                                    <div class="locations_box_txt select2-container-multi">
                                        <p class="select2-choices"><?php
                                            $tags_arr = explode(',', $tags);
                                            foreach ($tags_arr as $key => $value) {
                                                echo '<a href="' . $this->config->item('site_url') . 'search_location?s=' . $value . '&sc=' . $this->session->userdata('currentCity') . '" class="select2-search-choice">#' . $value . '</a> ';
                                            }
                                            ?></p>
                                    </div>
                                <?php } ?>
                                <?php if ($description != '') { ?>
                                    <div class="locations_box_txt">
                                        <p><?php echo $description; ?></p>
                                    </div>
                                <?php } ?>                            
                                <?php if (count($timing) > 0 || ($data[0]['eType'] == 'Event' && $data[0]['dtStartDate'] != '')) { ?>
                                    <div class="locations_box_txt">
                                        <?php
                                        if ($data[0]['eType'] == 'Public') {
                                            $days = array("Monday To Friday", "Saturday", "Sunday");

                                            foreach ($timing as $keyd => $valued) {
                                                if ($valued['eType'] == 'Open') {
                                                    ?>
                                                    <p><strong><?php echo ucfirst($days[$keyd]); ?></strong><br/><?php echo date('h:i A', strtotime($valued['vOpenTime'])) . ' - ' . date('h:i A', strtotime($valued['vCloseTime'])); ?></p>
                                                    <?php
                                                } else if ($valued['eType'] == 'Open_24') {
                                                    echo '<p><strong>' . ucfirst($days[$keyd]) . '</strong> - Open 24 Hours</p>';
                                                } else {
                                                    echo '<p><strong>' . ucfirst($days[$keyd]) . '</strong> - Close</p>';
                                                }
                                            }
                                        } elseif ($data[0]['eType'] == 'Event') {
                                            ?>
                                            <p><strong>From : </strong><?php echo date('d M Y', strtotime($data[0]['dtStartDate'])) . ' ' . date('h:i A', strtotime($data[0]['vStartTime'])); ?></p>
                                            <p><strong>To : </strong><?php echo date('d M Y', strtotime($data[0]['dtFinishDate'])) . ' ' . date('h:i A', strtotime($data[0]['vFinishTime'])); ?></p>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                <?php } ?>

                                <?php if ($data[0]['vContactNo'] != '') { ?>
                                    <div class="locations_box_txt">
                                        <p><a href="tel:<?php echo $data[0]['vContactNo']; ?>"><?php echo $data[0]['vContactNo']; ?></a></p>
                                    </div>
                                <?php } ?>
                                <?php if ($data[0]['vWebsite'] != '') { ?>
                                    <div class="locations_box_txt">
                                        <p><a href="<?php if (strpos($data[0]['vWebsite'], 'http://') !== false) {
                            echo $data[0]['vWebsite'];
                        } else {
                            echo "http://" . $data[0]['vWebsite'];
                        } ?>" class="bluelink"><?php echo str_replace("http://", '', $data[0]['vWebsite']); ?></a></p>
                                    </div>
        <?php } ?>
        <?php if ($data[0]['vBookingUrl'] != '') { ?>
                                    <div class="locations_box_txt " style="border-bottom:0;">
                                        <div class="btn_blue_main">
                                            <div class="btn_blue_small">
                                                <a href="<?php echo $data[0]['vBookingUrl']; ?>" title="BOOK NOW">BOOK NOW</a>
                                            </div>
                                        </div>
                                    </div>
        <?php } ?>

                                <div class="locations_box_txt " style="border-bottom:0;">
                                    <ul class="social-icons  list-unstyled list-inline" style="width: 100%;
                                        float: left;
                                        margin: 0px auto;
                                        text-align: center;"> 
        <?php if ($data[0]['vFBPage'] != '') { ?> <li><a href="<?php echo $data[0]['vFBPage']; ?>" target="_blank" ><i class="fa fa-facebook"></i></a></li> <?php } ?>
        <?php if ($data[0]['vInstPage'] != '') { ?> <li><a href="<?php echo $data[0]['vInstPage']; ?>" target="_blank" ><i class="fa fa-instagram"></i></a></li> <?php } ?>
        <?php if ($data[0]['vGplusPage'] != '') { ?> <li><a href="<?php echo $data[0]['vGplusPage']; ?>" target="_blank" ><i class="fa fa-google-plus"></i></a></li><?php } ?>
                            <?php if ($data[0]['vTweetPage'] != '') { ?> <li><a href="<?php echo $data[0]['vTweetPage']; ?>" target="_blank" ><i class="fa fa-twitter"></i></a></li><?php } ?>
                                    </ul>
                                </div>

                            </div>
    <?php } ?>
                        <div class="button_box hidden-xs">
                            <div class="btn_blue_direction">
                                <div class="btn_blue_small">
                                    <?php if($currentLat == 0.00) { ?>
                                        <a href="<?php echo "https://maps.google.com/maps?daddr=$lat,$long"; ?>" title="GET DIRECTION" target="_blank">GET DIRECTION</a>
                                    <?php } else { ?>
                                        <a href="<?php echo "https://maps.google.com/maps?saddr=$currentLat,$currentLong&daddr=$lat,$long"; ?>" title="GET DIRECTION" target="_blank">GET DIRECTION</a>
                                    <?php } ?>
                                </div>
                            </div>

                            <!--                            <div class="btn_blue_direction">
                                                            <div class="btn_blue_small">
                                                                <a href="<?php echo $this->config->item('site_url'); ?>" title="DOWNLOAD">DOWNLOAD</a>
                                                            </div>
                                                        </div>-->
                        </div>                    
                    </div>
                </div>
            </div>
            <br>

            <?php if (count($images) != 0) { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-9">
                            <?php if (count($images) > 0) { ?>
                            <div class="gallerybox boxShadow">
                                <?php
                                foreach ($images as $key => $value) {
                                    if ($value['vImage'] != '') {
                                        if (strpos($value['vImage'],'thumb_') !== false) {
                                            $exp =  explode('thumb_', $value['vImage']);

                                        $coverimage_path = $this->config->item('site_path') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $value['vImage'];
                                        $imgurl = $this->config->item('site_url') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $value['vImage'];
                                        $imgurlA = $this->config->item('site_url') . 'public/upload/location/' . $data[0]['iLocationId'] . '/comp_' . $exp[1];
                                        
                                        if (file_exists($coverimage_path)) {
                                            ?>
                                            <span class="internal"><a class="fancybox" href="<?php echo $imgurlA; ?>" data-fancybox-group="gallery"><img src="<?php echo $imgurlA; ?>" class="thumbnail_img"  alt="" /></a></span>
                                            <?php
                                        }
                                        
                                        }
                                    }
                                }
                                ?>

                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-3">                    
                            <?php
                            if (count($similar) > 0 && $data[0]['eType'] != "Private") {
                                ?>
                            <div class="bluetxt" style="text-align:left;">Similar <span class="viewpad"><a href="<?php echo $this->config->item('site_url') . 'similar_location?c=' . $this->general->encryptData($data[0]['iCategoryId']) . '&city=' . $data[0]['vCity']; ?>" class="bluelink text-small">View All</a></span></div>
                            <div class="similar_box location_box">                        
                                <?php
                                foreach ($similar as $keys => $values) {
                                    if ($data[0]['iLocationId'] == $values['iLocationId']) {
                                        continue;
                                    } else {
                                        ?>
                                        <div class="media border_category">
                                            <div class="media-left"> 
                                                <a href='<?php echo $this->config->item('site_url') . $values['vUrlName'] ?>'> 
                                                    <?php
                                                    $image_url = $this->config->item('site_url') . 'public/img/imgo.jpeg';
                                                    $image = $values['cover_image'];
                                                    $coverimage_path = $this->config->item('site_path') . 'public/upload/location/' . $values['iLocationId'] . '/' . $image;
                                                    $coverimage_url = $this->config->item('site_url') . 'public/upload/location/' . $values['iLocationId'] . '/' . $image;
                                                    if ($image != '' && file_exists($coverimage_path)) {
                                                        $image_url = $coverimage_url;
                                                    }
                                                    ?>
                                                    <img src="<?php echo $image_url; ?>" class="media-object location_img"  alt="" style="width: 60px;"/>
                                                </a> 
                                            </div>
                                            <div class="media-body">
                                                <h3 class="grey_txt12px"><a href='<?php echo $this->config->item('site_url') . $values['vUrlName'] ?>' class="greytxtlink"> <?php echo $values['vName']; ?></a></h3>
                                                <div class="similar_box_txt" style="border-bottom: 0px;">
                                                    <p style=" color:#777;" class="greytxt11 padding-left-none"><?php echo $values['vCategory']; ?></p>
                                                    <p style=" color:#777;" class="greytxt11 padding-left-none"><?php echo $values['vAddress'] . ( $values['vCity'] != '' ? "," . $values['vCity'] : ''); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                }
                            }
                            ?>
                            </div>
        <?php
    }
    ?>
                    </div>
                </div>
            </div><!-- end gallery -->
            <?php } ?>

<?php } ?>
    </div>
</header>

<script type="text/javascript" src="<?php echo $this->config->item('js_url') ?>jquery.fancybox.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        /*
         *  Simple image gallery. Uses default settings
         */

        $('.fancybox').fancybox();


    });
</script>
<script>
    $(document).ready(function () {
//        $('#loader').show();
        $('#locmap').on('load', function () {
            // hide/remove the loading image
//            $('#loader').hide();
        });
    });
</script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places"></script>
<script>
    $(function () {
        function initialize() {
            var mylatlng = new google.maps.LatLng($("#vLatitude").val(), $("#vLongitude").val());
            geocodePosition(mylatlng);

            var st = [{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#747474"},{"lightness":"23"}]},{"featureType":"poi.attraction","elementType":"geometry.fill","stylers":[{"color":"#f38eb0"}]},{"featureType":"poi.government","elementType":"geometry.fill","stylers":[{"color":"#ced7db"}]},{"featureType":"poi.medical","elementType":"geometry.fill","stylers":[{"color":"#ffa5a8"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#c7e5c8"}]},{"featureType":"poi.place_of_worship","elementType":"geometry.fill","stylers":[{"color":"#d6cbc7"}]},{"featureType":"poi.school","elementType":"geometry.fill","stylers":[{"color":"#c4c9e8"}]},{"featureType":"poi.sports_complex","elementType":"geometry.fill","stylers":[{"color":"#b1eaf1"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":"100"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"},{"lightness":"100"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffd4a5"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffe9d2"}]},{"featureType":"road.local","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"weight":"3.00"}]},{"featureType":"road.local","elementType":"geometry.stroke","stylers":[{"weight":"0.30"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#747474"},{"lightness":"36"}]},{"featureType":"road.local","elementType":"labels.text.stroke","stylers":[{"color":"#e9e5dc"},{"lightness":"30"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"on"},{"lightness":"100"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#d2e7f7"}]}];

            var mapOptions = {
                center: mylatlng,
                zoom: 19,
                styles: st
            };
            var map = new google.maps.Map(document.getElementById('locmap'),
                    mapOptions);
                    map.setOptions({draggable: false});
            var input = (document.getElementById('pac-input'));

            var types = document.getElementById('type-selector');
            //alert(types);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);

            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();

            var icon = {
                url: rootPath + 'public/img/marker1.png',
                scaledSize: new google.maps.Size(50, 50), // scaled size
                origin: new google.maps.Point(0, 0), // origin
                anchor: new google.maps.Point(25, 52) // anchor
            };

            var marker = new google.maps.Marker({
                map: map,
                draggable: false,
                animation: google.maps.Animation.DROP,
                position: mylatlng,
                icon: icon
            });

            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                infowindow.close();
                marker.setVisible(false);
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    return;
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(18);  // Why 17? Because it looks good.
                }

                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                var address = '';

                if (place.address_components) {
                    address = [
                        (place.address_components[0] && place.address_components[0].short_name || ''),
                        (place.address_components[1] && place.address_components[1].short_name || ''),
                        (place.address_components[2] && place.address_components[2].short_name || '')
                    ].join(' ');
                }
                var latitude = marker.position.lat();
                var longitude = marker.position.lng();

                $("#vLatitude").val(latitude);
                $("#vLongitude").val(longitude);
                //$("#location").val(address);


                infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
                infowindow.open(map, marker);
            });

            google.maps.event.trigger(map, 'resize');
            map.setCenter(marker.getPosition());

            //            google.maps.event.addListener(marker, 'dragend', changemarkerposition);
            //            function changemarkerposition() {
            //                $("#vLatitude").val(marker.position.lat());
            //                $("#vLongitude").val(marker.position.lng());
            //                geocodePosition(marker.getPosition());
            //
            //            }
        }
        google.maps.event.addDomListener(window, 'load', initialize);
    });
    function geocodePosition(pos) {
        geocoder = new google.maps.Geocoder();

        geocoder.geocode({
            latLng: pos
        }, function (responses) {
            if (responses && responses.length > 0) {
                $("#location").val(responses[0].formatted_address);
            } else {
                $("#location").val('Cannot determine address at this location.');
            }
        });
    }
</script>

<?php
include APPPATH . '/front-modules/views/bottom_script.php'; //exit;              ?>
<?php include APPPATH . '/front-modules/views/main_footer.php'; //exit;      ?>