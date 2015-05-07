<?php $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
<?php include APPPATH . '/front-modules/views/top.php'; //exit;    ?>
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
                <div class="col-lg-6 location_box container_margin">

                    <div class="media">
                        <div class="media-left">
                            <?php
                            $image_url = $this->config->item('site_url') . 'public/img/imgo.jpeg';
                            if (is_array($images) && count($images) > 0) {
                                $coverimage_path = $this->config->item('site_path') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $images[0]['vImage'];
                                $coverimage_url = $this->config->item('site_url') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $images[0]['vImage'];
                                if (file_exists($coverimage_path)) {
                                    $image_url = $coverimage_url;
                                }
                            }
                            ?>
                            <img src="<?php echo $image_url; ?>" class="media-object location_img"  alt="" style="width: 50px;"/>
                        </div>
                        <div class="media-body">
                            <h3 class="txtblue"><?php echo $name; ?></h3>
                            <p style="font-size:12px; color:#777;" class="greytxt11"><?php echo $categoryName; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Team Members Row -->
            <div class="row">
                <div class="col-md-12 text-left marg20 container_margin">
                    <div class="col-md-9 pad0">
                        <div class="mapbox">
                            <input type="hidden" id="vLatitude" name="vLatitude" value="<?php echo $lat; ?>">
                            <input type="hidden" id="vLongitude" name="vLongitude" value="<?php echo $long; ?>">
                            <div style="padding:0;" class="body-nest" id="Gmap">
                                <div id="locmap" class="gmap" style="width:100%;height:400px;">
                                    <img src="<?php echo $this->config->item('site_url'); ?>public/img/loader.gif" id="loader"/>
                                </div>
                            </div>
                        </div> 

                        <div class="sharebox">
                            <div class="sharebox_left">
                                <h1><?php echo ($data[0]['vAddress'] != '') ? $data[0]['vAddress'] : '&nbsp;'; ?></h1>
                            </div>
                            <div class="sharebox_right" id="share_dropdown">
                                <div class="btn_grey_main">
                                    <div class="btn_grey_small">
                                        <ul class="navbar-nav navbar-left" style="margin: 9.75px -15px;list-style-type: none">
                                            <li class="dropdown inherit">
                                                <a href="#" class="dropdown-toggle btn-primary" data-toggle="dropdown" role="button" aria-expanded="false">Share</a>
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
                                    <span class='st_sharethis'></span>
                                    <span class='st_facebook'></span>
                                    <span class='st_twitter'></span>
                                    <span class='st_linkedin'></span>
                                    <span class='st_googleplus'></span>
                                    <span class='st_pinterest'></span>
                                    <span class='st_email'></span>
                                </div>
                            </div>
                            <div class="button_box hidden-lg hidden-md hidden-sm">
                                <div class="btn_blue_direction">
                                    <div class="btn_blue_small">
                                        <a href="https://www.google.com/maps/dir/<?php echo $lat; ?>,<?php echo $long; ?>/@<?php echo $lat; ?>,<?php echo $long; ?>,13z/data=!3m1!4b1!4m8!4m7!1m5!1m1!1s0x395e848aba5bd449:0x4fcedd11614f6516!2m2!1d72.5713621!2d23.022505!1m0?hl=en-US" title="GET DIRECTION" target="_blank">GET DIRECTION</a>
                                    </div>
                                </div>

                                <div class="btn_blue_direction">
                                    <div class="btn_blue_small">
                                        <a href="#" title="BOOK NOW">DOWNLOAD</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 ">
                        <?php if ($description != '' || $tags != '' || $data[0]['vBookingUrl'] != '' || count($timing) > 0 || ($data[0]['vFBPage'] != '' || $data[0]['vInstPage'] != '' || $data[0]['vGplusPage'] != '' || $data[0]['vTweetPage'] != '')) { ?>
                            <div class="description_box">
                                <?php if ($tags != '') { ?>
                                    <div class="description_txt select2-container-multi word-wrap">
                                        <p class="select2-choices"><?php
                                            $tags_arr = explode(',', $tags);
                                            foreach ($tags_arr as $key => $value) {
                                                echo '<a href="' . $this->config->item('site_url') . 'search_location?s=' . $value . '" class="select2-search-choice pad5">' . $value . '</a>';
                                            }
                                            ?></p>
                                    </div>
                                <?php } ?>
                                <?php if ($description != '') { ?>
                                    <div class="description_txt">
                                        <p><?php echo $description; ?></p>
                                    </div>
                                <?php } ?>                            
                                <?php if (count($timing) > 0 || ($data[0]['eType'] == 'Event' && $data[0]['dtStartDate'] != '')) { ?>
                                    <div class="description_txt">
                                        <?php
                                        if ($data[0]['eType'] == 'Public') {
                                            $days = array("Monday To Friday", "Saturday", "Sunday");

                                            foreach ($timing as $keyd => $valued) {
                                                if ($valued['eType'] == 'Open') {
                                                    ?>
                                                    <p><strong><?php echo ucfirst($days[$keyd]); ?></strong><br/><?php echo date('h:i A', strtotime($valued['vOpenTime'])) . ' - ' . date('h:i A', strtotime($valued['vCloseTime'])); ?></p>
                                                    <?php
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

                                <?php if ($data[0]['vWebsite'] != '') { ?>
                                    <div class="description_txt">
                                        <p style="text-align:center;"><a href="#" class="bluelink"><?php echo $data[0]['vWebsite']; ?></a></p>
                                    </div>
                                <?php } ?>
                                <?php if ($data[0]['vBookingUrl'] != '') { ?>
                                    <div class="description_txt " style="border-bottom:0;">
                                        <div class="btn_blue_main">
                                            <div class="btn_blue_small">
                                                <a href="<?php echo $data[0]['vBookingUrl']; ?>" title="BOOK NOW">BOOK NOW</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="description_txt " style="border-bottom:0;">
                                    <ul class="social-icons  list-unstyled list-inline"> 
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
                                    <a href="https://www.google.com/maps/dir/<?php echo $lat; ?>,<?php echo $long; ?>/@<?php echo $lat; ?>,<?php echo $long; ?>,13z/data=!3m1!4b1!4m8!4m7!1m5!1m1!1s0x395e848aba5bd449:0x4fcedd11614f6516!2m2!1d72.5713621!2d23.022505!1m0?hl=en-US" title="GET DIRECTION" target="_blank">GET DIRECTION</a>
                                </div>
                            </div>

                            <div class="btn_blue_direction">
                                <div class="btn_blue_small">
                                    <a href="<?php echo $this->config->item('site_url'); ?>" title="DOWNLOAD">DOWNLOAD</a>
                                </div>
                            </div>
                        </div>                    
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-left marg20 container_margin">
                    <div class="col-md-9 pad0">
                        <?php if (count($images) > 0) { ?>
                            <div class="photogallery_box">
                                <?php
                                foreach ($images as $key => $value) {
                                    $coverimage_path = $this->config->item('site_path') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $value['vImage'];
                                    $imgurl = $this->config->item('site_url') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $value['vImage'];
                                    if (file_exists($coverimage_path)) {
                                        ?>
                                        <div class="photogallery_cover">
                                            <div class="fancy_box_gallery">
                                                <a class="fancybox" href="<?php echo $imgurl; ?>" data-fancybox-group="gallery"><img src="<?php echo $imgurl; ?>" class="thumbnail_img"  alt="" /></a>
                                            </div>
                                        </div>

                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-lg-3" style="padding-right:15px;">                    
                        <?php
                        if (count($similar) > 1) {
                            ?>
                            <div class="bluetxt">Similar <span class="viewpad"><a href="<?php echo $this->config->item('site_url') . 'similar_location?c=' . $this->general->encryptData($data[0]['iCategoryId']); ?>" class="bluelink text-small">View All</a></span></div>
                            <div class="description_box location_box">                        
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
                                                    <img src="<?php echo $image_url; ?>" class="media-object location_img"  alt="" style="width: 50px;"/>
                                                </a> 
                                            </div>
                                            <div class="media-body">
                                                <h3 class="txtblue"><a href='<?php echo $this->config->item('site_url') . $values['vUrlName'] ?>'> <?php echo $values['vName']; ?></a></h3>
                                                <div class="description_txt" style="border-bottom: 0px;">
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
            </div>
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
        $('#loader').show();
        $('#locmap').on('load', function () {
            // hide/remove the loading image
            $('#loader').hide();
        });
    });
</script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places"></script>
<script>
    $(function () {
        function initialize() {
            var mylatlng = new google.maps.LatLng($("#vLatitude").val(), $("#vLongitude").val());
            geocodePosition(mylatlng);
            var mapOptions = {
                center: mylatlng,
                zoom: 18
            };
            var map = new google.maps.Map(document.getElementById('locmap'),
                    mapOptions);

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
                anchor: new google.maps.Point(0, 0) // anchor
            };

            var marker = new google.maps.Marker({
                map: map,
                draggable: false,
                animation: google.maps.Animation.ROADMAP,
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