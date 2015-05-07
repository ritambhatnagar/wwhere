<?php include APPPATH . '/front-modules/views/top.php'; //exit;?>
<header class="white_bg">
    <div class="container top_marg">
        <!-- Introduction Row -->
<!--        <div class="row">
            <div class="col-lg-6 location_box">
                <h3 class="txtblue">Similar List</h3>
            </div>
        </div>-->
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
            <div class="col-md-12 text-left marg20 container_margin ">
                <div class="col-lg-4 pad00">
                    <div class="cat_box " >
                        <div class="cat_box_txt" style="border:0px;">
                            <div class="col-lg-12 location_box ">
            <div  id="myListDiv">
                <?php if (count($data) > 0) { ?>
                    <?php foreach ($data as $key => $value) { ?>
                                            <div class="media border_category">
                                                <div class="media-left"> 
                                                    <a href='<?php echo $this->config->item('site_url') . $value['vUrlName'] ?>'> 
                                                        <?php
                                                        $image_url = $this->config->item('site_url') . 'public/img/imgo.jpeg';
                                                        $image = $value['cover_image'];
                                                        $coverimage_path = $this->config->item('site_path') . 'public/upload/location/' . $value['iLocationId'] . '/' . $image;
                                                        $coverimage_url = $this->config->item('site_url') . 'public/upload/location/' . $value['iLocationId'] . '/' . $image;
                                                        if ($image != '' && file_exists($coverimage_path)) {
                                                            $image_url = $coverimage_url;
                                                        }
                                                        ?>
                                                        <img src="<?php echo $image_url; ?>" class="media-object location_img"  alt=""/>
                                                    </a> 
                            </div>
                                                <div class="media-body">
                                                    <h3 class="grey_txt12px"><a href='<?php echo $this->config->item('site_url') . $value['vUrlName'] ?>'> <?php echo $value['vName']; ?></a></h3>
                                                    <p style=" color:#777;" class="greytxt11"><?php echo $value['vCategory']; ?></p>
                                                    <p style=" color:#777;" class="greytxt11"><?php echo $value['vAddress'] . ( $value['vCity'] != '' ? "," . $value['vCity'] : ''); ?></p>
                                </div>
                            </div>
                    <?php } ?>
                <?php } else { ?>
                                        <div class="media border_category">
                    <div class="col-md-12 ">
                        <div class="description_txt " style="border-bottom:0;">
                            <p>No Result Found</p>
                        </div>

                    </div>
                                        </div>
                <?php } ?>
            </div>
        </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 ">
                    <div id="map_wrapper">
                        <div id="map_canvas" class="mapping">
                            <img src="<?php echo $this->config->item('site_url'); ?>public/img/loader.gif" id="loader"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members Row -->
        <div class="row pad15 padding-left-none padding-right-none">


        </div>
            <?php } ?>
    </div>
</header>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places"></script>
<script>
    $(document).ready(function () {
        $('#loader').show();
        $('#map_canvas').on('load', function () {
            // hide/remove the loading image
            $('#loader').hide();
        });
    });
</script>
<script>
    $(function () {
        function initialize() {
            var marker;
            var bounds = new google.maps.LatLngBounds();

            var mapOptions = {
                mapTypeId: 'roadmap'
            };
            var map = new google.maps.Map(document.getElementById('map_canvas'),
                    mapOptions);

            var infowindow = new google.maps.InfoWindow();

            var icon = {
                url: rootPath + 'public/img/pegman.png',
                scaledSize: new google.maps.Size(40, 50), // scaled size
                origin: new google.maps.Point(0, 0), // origin
                anchor: new google.maps.Point(0, 0) // anchor
            };

            var markers = <?php echo $result; ?>;

            // Loop through our array of markers & place each one on the map  
            for (i = 0; i < markers.length; i++) {
                var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
                bounds.extend(position);
                if (markers[i][0] == 'Session') {
                    marker = new google.maps.Marker({
                        map: map
                    });
                } else {
                    marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        icon: icon,
                        title: markers[i][0]
                    });

                    // Allow each marker to have an info window    
                    google.maps.event.addListener(marker, 'mouseover', (function (marker, i) {
                        return function () {
                            infowindow.setContent('<div>' + markers[i][0] + '</div>');
                            infowindow.open(map, marker);
                        }
                    })(marker, i));
                }
                // Automatically center the map fitting all markers on the screen
                map.fitBounds(bounds);
            }
            // Override our map zoom level once our fitBounds function runs (Make sure it only runs once)
            var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function (event) {
                this.setZoom(12);
                google.maps.event.removeListener(boundsListener);
            });
        }

        google.maps.event.addDomListener(window, 'load', initialize);
    });
</script>
<?php include APPPATH . '/front-modules/views/bottom_script.php'; ?>