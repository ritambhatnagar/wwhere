<?php include APPPATH . '/front-modules/views/top.php'; //exit;                                            ?>
<style>
    .tab2 input[type=radio],
    #tab5 input[type=radio]{
        width: 20px;
        float: left;
        vertical-align: top;
        height:20px;
    }
    .tab2 label{
        vertical-align: bottom;
        line-height: 30px;
        padding-left: 10px;
    }
    .tab2 span{
        float: left;
        text-align: left;
        padding: 0px;
    }
    .social-icons .fa{
        margin-left: 0px;
    }
    #location-logo-upload {
        float: left;
        height: 50px;    
        cursor: pointer;
    }    
    #uoload-logo-text {
        float: left;
        line-height: 50px;
        padding-left: 10px;
        font-weight: bold;
    }
</style>
<link rel="stylesheet" href="<?php echo $this->config->item('bootstrap_url'); ?>bootstrap-timepicker/assets/lib/css/bootstrap-timepicker.css"/>
<link rel="stylesheet" href="<?php echo $this->config->item('bootstrap_url'); ?>select2/assets/lib/css/select2.css"/>
<link rel="stylesheet" href="<?php echo $this->config->item('css_url'); ?>uploadfile.css"/>
<link rel="stylesheet" href="<?php echo $this->config->item('css_url'); ?>cropstyle.css"/>

<script type="text/javascript" src="<?php echo $this->config->item('bootstrap_url'); ?>bootstrap-datepicker/assets/lib/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('bootstrap_url'); ?>bootstrap-datepicker/assets/custom/js/bootstrap-datepicker.init.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('bootstrap_url'); ?>bootstrap-timepicker/assets/lib/js/bootstrap-timepicker.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('bootstrap_url'); ?>select2/assets/lib/js/select2.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('assets_url'); ?>wizards/assets/lib/jquery.bootstrap.wizard.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jquery.uploadfile.min.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>custom/createlocation.js"></script>

<link href="<?php echo $this->config->item('css_url'); ?>jquery-ui.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jquery-ui-crop.min.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>custom/cropscript.js"></script>
<script>
    var currentlat = 0;
    var currentlong = 0;
    $(document).ready(function () {
        var cropObj = new Crop;
        cropObj.id = 'upload-crop-image';
        cropObj.uploadUrl = "<?php echo $this->config->item('site_url') . 'location/cropUpload'; ?>"; 
        cropObj.init();

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
                return true;
            }
        });
    }
</script>

<header class="white_bg">
    <div class="container top_marg">
        <!-- Introduction Row -->
<!--         <div class="row">
            <div class="col-lg-12">
                <h3 class="txtblue headertitle">CREATE LOCATION</h3>
            </div>            
        </div> -->



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
                <div class="col-lg-8 col-lg-offset-2">
                    <!--<input type="hidden" name="eType" id="eType" value="<?php // echo $eType;                            ?>">-->

                    <!-- Form Wizard / Arrow navigation & Progress bar -->
                    <div id="rootwizard" class="wizard">
                        <!-- Wizard heading -->
                        <div class="wizard-head">
                            <ul class="bwizard-steps">
                                <li class="active tab1"><a href="#tab1" data-toggle="tab">Point on Map</a>
                                </li>              
                                <li class="tab2"><a href="#tab2" data-toggle="tab">Location Type</a>
                                </li>
                                <li class="tab3"><a id="stp2" href="#tab3" data-toggle="tab">Location Detail</a>
                                </li>
                                <li class="tab4"><a href="#tab4" data-toggle="tab">Contact Info</a>
                                </li>              

                                <li class="tab5"><a href="#tab5" data-toggle="tab">Timings</a>
                                </li>              
                            </ul>
                        </div>
                        <!-- // Wizard heading END -->
                        <div class="widget">
                            <div class="widget-body page-nation">
                                <div class="tab-content">
                                    <!-- Step 4 -->
                                    <div class="tab-pane active tab1" id="tab1">
                                        <!-- location -->
                                        <div class="row">                            
                                            <div class="col-md-12">
                                                <input type="hidden" id="vLatitude" name="vLatitude" value="<?php echo ($data[0]['vLatitude'] != '') ? $data[0]['vLatitude'] : $this->session->userdata('currentLat'); ?>">
                                                <input type="hidden" id="vLongitude" name="vLongitude" value="<?php echo ($data[0]['vLongitude'] != '') ? $data[0]['vLongitude'] : $this->session->userdata('currentLong'); ?>">
                                                <input type="hidden" id="resize">

                                                <div style="padding:0;" class="body-nest" id="Gmap">
                                                    <input id="pac-input" class="controls form-control" type="text" placeholder="Search a location" style="width:80%;">
                                                    <div id="locmap" class="gmap" style="width:100%;height:450px;"></div>
                                                </div>
                                                <span class="text-info"><code>You can drag 'n drop pin within map</code></span>
                                            </div>
                                        </div>
                                        <!--                                    <div class="row">
                                                                                <input type="hidden" id="hiddentrigger" name="hiddentrigger" value="">
                                        <?php /* if ($data[0]['vLocationImage'] == '') { ?>
                                          <div id="uploadlocationimg">Location icon upload</div>

                                          <?php
                                          } else {
                                          $l_img = 'public/upload/locationimg/' . $data[0]['iLocationId'] . '/' . $data[0]['vLocationImage'];
                                          $l_img_url = $this->config->item('site_url') . $l_img;
                                          ?>
                                          <div id="vLocationImgss" class="col-md-3">
                                          <a id="Delete<?php echo $keyi + 1; ?>" class="delete_location" data-id="<?php echo $data[0]['iLocationId']; ?>" class="text-danger"><i class="fa fa-trash-o"></i></a>
                                          <a class="thumb no-ajaxify" href="<?php echo $l_img_url; ?>" data-gallery="gallery">
                                          <img src="<?php echo $l_img_url; ?>" style="height:100px;width:100px;"  alt="photo" class="img-responsive"></a>
                                          </div>
                                          <?php } */ ?>
                                                                            </div>-->
                                        <!-- location -->
                                    </div>
                                    <!-- // Step 4 END -->
                                    <!-- Step 1 -->
                                    <div class="tab-pane tab2" id="tab2">
                                        <!-- location -->

                                        <div class="col-md-12">
                                            <div class="row form-group">
                                                <div class="col-md-12">
                                                    <input type="radio" class="form-control eType" id="eType" name="eType" value="Private" onchange="return changeEType('Private');" <?php echo ($data[0]['eType'] == 'Private') ? 'checked="checked"' : ''; ?> />
                                                    <label for="url" class="pull-left"> Personal</label>
                                                    <span class="headingtxt">Such as your home, office, gym, etc. Private locations do not show up in the search.</span>
                                                    <span class="urltext">E.g.-wWhere.is/JackGramHome</span>
                                                </div>                                                
                                            </div>
                                            <div class="row form-group">
                                                <div class="col-md-12">
                                                    <input type="radio" class="form-control eType" id="eType" name="eType" value="Public" onchange="return changeEType('Public');" <?php echo ($data[0]['eType'] == 'Public') ? 'checked="checked"' : ''; ?> />
                                                    <label for="url" class="pull-left">Public</label>
                                                    <span class="headingtxt">Such as your business locations.</span>
                                                    <span class="urltext">E.g.-wWhere.is/WalMartLondon</span>
                                                </div>                                                
                                            </div>
                                            <div class="row form-group">
                                                <div class="col-md-12">
                                                    <input type="radio" class="form-control eType" id="eType" name="eType" value="Event" onchange="return changeEType('Event');" <?php echo ($data[0]['eType'] == 'Event') ? 'checked="checked"' : ''; ?> />
                                                    <label for="url" class="pull-left">Event</label>
                                                    <span class="headingtxt">Such as time bound locations where the URL gets deleted 48 hours after the event.</span>
                                                    <span class="urltext">E.g.-wWhere.is/MichaelBirthday</span>
                                                    <span class="help-inline error" id="eTypeErr"></span>
                                                </div>                                                
                                            </div>
                                        </div>

                                        <!-- location -->
                                    </div>
                                    <!-- // Step 1 END -->
                                    <!-- Step 2 -->
                                    <div class="tab-pane tab3" id="tab3">
                                        <div class="row">
                                            <input type='hidden' id='type_value' value=''/>
                                            <form class="form-horizontal" role="form" id="createLocationForm" method="POST" autocomplete="off" enctype="multipart/form-data" action="<?php echo $this->config->item('site_url') . 'location/createlocation_action'; ?>" >
                                                <div class="col-md-12">

                                                    <input type="hidden" name="mode" id="mode" value="<?php echo (isset($_GET['id'])) ? 'edit' : 'add'; ?>">
                                                    <input type="hidden" name="iLocationId" id="iLocationId" value="<?php echo $data[0]['iLocationId']; ?>">

                                                    <?php //echo str_replace($this->config->item('LOCATION_URL'), "",$data[0]['vUrlName'] ); ?>
                                                    <div class="row form-group">
                                                        <div class="col-md-12">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                        <div class="input-group">
                                                                            <div class="input-group-addon">wwhere.is/</div><input type="text" name="vUrlName" class="form-control vUrlName text-lowercase" id="vUrlName" placeholder="for e.g. wwhere.is/Cabana" value="<?php echo str_replace($this->config->item('LOCATION_URL'), "", $data[0]['vUrlName']); ?>" onkeyup="return urlExist();" />
                                                                            <span style="position: absolute;right:20px;top:10px;z-index:10;" id="urlcheck"></span>
                                                                        </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>    
                                                            <span class="help-inline error" id="vUrlNameErr"></span>
                                                        </div>                                                
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col-md-12">
                                                            <label for="inputTitle" class="pull-left">Location Name<sup class="re-star">*</sup></label>
                                                            <input type="text" name="vName" style="font-size:12px !important;" class="form-control vName capitalize" id="vName" value="<?php echo $data[0]['vName']; ?>" placeholder="Like John Basker’s Home, John’s Office, Cafe Orion, etc."/>
                                                            <span class="help-inline error" id="vNameErr"></span>                                            
                                                        </div>                                                
                                                    </div>

                                                    <div class="row form-group iCategoryId">
                                                        <div class="col-md-12">

                                                            <label for="inputTitle" class="pull-left">Select Category<sup class="re-star">*</sup></label>
                                                            <select name="iCategoryId" id="iCategoryId" class="form-control select-chosen iCategoryId">
                                                                <option value="0">Select Category</option>
                                                                <?php foreach ($categoryList as $key => $value) { ?>
                                                                    <option value="<?php echo $value['iCategoryId'] ?>" <?php echo ($data[0]['iCategoryId'] == $value['iCategoryId']) ? 'selected' : ''; ?>><?php echo $value['vCategory'] ?></option>    
                                                                <?php } ?>
                                                            </select>
                                                            <span class="help-inline error" id="iCategoryIdErr"></span>                                            
                                                        </div>
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col-md-12">
                                                            <label for="inputTitle" class="pull-left">More About it</label>
                                                            <input type="text" name="vDescription" class="form-control capitalize" id="vDescription" maxlength="140" value="<?php echo $data[0]['vDescription']; ?>" placeholder="Talk about it in no more than 80 Characters"/>
                                                            <span class="help-inline error" id="vDescriptionErr"></span>                                            
                                                        </div>                                                
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col-md-12">
                                                            <label for="inputTitle" class="pull-left">Address<sup class="re-star">*</sup></label>
                                                            <input type="text" name="vAddress" class="form-control vAddress capitalize" maxlength="80" id="vAddress" value="<?php echo $data[0]['vAddress']; ?>" placeholder="Your postal address for better convenience" />
                                                            <span class="help-inline error" id="vAddressErr"></span>                                            
                                                        </div>                                                
                                                    </div>
                                                    <div class="row form-group" style="position: relative">
                                                        <div class="col-md-12">
                                                            <label for="inputTitle" class="pull-left">City<sup class="re-star">*</sup></label>
                                                            <input type="text" name="vCity" class="form-control ui-autocomplete-input vCity" id="vCity" value="<?php echo $data[0]['vCity']; ?>" placeholder="Name of City" />
                                                            <span class="help-inline error" id="vCityErr"></span>
                                                            <div id="autocomplete_list" style="top:0; position: relative; left:0;"></div>
                                                        </div>                                                
                                                    </div>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- // Step 2 END -->
                                    <!-- Step 3 -->
                                    <div class="tab-pane tab4" id="tab4">
                                        <div class="col-md-12">
                                            <div class="row form-group">
                                                <div class="col-md-12">
                                                    <label for="url" class="pull-left">Phone Number</label>
                                                    <input type="text" name="vContactNo" class="form-control" autocomplete="off" id="vContactNoC" value="<?php echo $data[0]['vContactNo']; ?>"  onkeyup="checkphone(this);"/>
                                                    <span class="help-inline error error" id="vContactNoErr"></span>
                                                </div>                                                
                                            </div>
                                            <div class="row form-group">
                                                <div class="col-md-12">
                                                    <label for="inputTitle" class="pull-left">Website</label>
                                                    <input type="text" name="vWebsite" class="form-control vWebsite" id="vWebsite" value="<?php echo $data[0]['vWebsite']; ?>" onkeyup="checkWebUrl(this);"/>
                                                    <span class="help-inline error" id="vWebsiteErr"></span>                                            
                                                </div>                                                
                                            </div>
                                            <div class="row form-group vBookingUrl">
                                                <div class="col-md-12">
                                                    <label for="inputTitle" class="pull-left">URL for Booking</label>
                                                    <input type="text" name="vBookingUrl" class="form-control vBookingUrl" id="vBookingUrl" value="<?php echo $data[0]['vBookingUrl']; ?>" placeholder="For businesses who want customers to book in advance" onkeyup="checkWebUrl(this);"/>
                                                    <span class="help-inline error" id="vWebsiteErr"></span>                                            
                                                </div>                                                
                                            </div>

                                            <div class="row form-group">
                                                <div class="col-md-12">
                                                    <label for="inputTitle" class="text-left padding-none social-icons list-unstyled col-md-12">Social Pages</label>
                                                    <div class="row social-icons list-unstyled list-inline">
                                                        <div class="col-md-6 text-left">
                                                            <i class="fa fa-facebook fb_left"></i><input  style="width:80%;margin-bottom: 15px;" type="text" name="vFBPage" class="form-control" id="vFBPage" placeholder="www.facebook.com/abcd" value="<?php echo $data[0]['vFBPage']; ?>"/> 
                                                            <i class="fa fa-instagram fb_left"></i><input  style="width:80%;margin-bottom: 15px;" type="text" name="vInstPage" class="form-control" id="vInstPage" placeholder="www.instagram.com/abcd" value="<?php echo $data[0]['vInstPage']; ?>"/> 
                                                        </div>    
                                                        <div class="col-md-6 text-right" style="left: 5%;">
                                                            <i class="fa fa-google-plus fb_left"></i><input  style="width:80%;margin-bottom: 15px;" type="text" name="vGplusPage" class="form-control" id="vGplusPage" placeholder="plus.google.com/abcd" value="<?php echo $data[0]['vGplusPage']; ?>"/>
                                                            <i class="fa fa-twitter fb_left"></i><input  style="width:80%;margin-bottom: 15px;" type="text" name="vTweetPage" class="form-control" id="vTweetPage" placeholder="twitter.com/abcd" value="<?php echo $data[0]['vTweetPage']; ?>"/>
                                                        </div>    
                                                    </div>
                                                </div>                                                
                                            </div>  
                                            <div class=" form-group">
                                                <div class="col-md-12 padding-none">
                                                    <label for="inputTitle" class="pull-left">Tags</label>
                                                    <input type="hidden" name="vTags" class="form-control" id="vTags" placeholder="Use tags for better location search" value="<?php echo $data[0]['vTags']; ?>" />
                                                </div>                                                
                                            </div>
                                        </div>

                                    </div>
                                    <!-- // Step 3 END -->

                                    <!-- Step 5 -->
                                    <div class="tab-pane tab5" id="tab5">
                                        <?php //if ($eType == 'Public') {   ?>
                                        <div class="row" id="publicdiv">
                                            <?php
                                            foreach ($days as $key => $day) {
                                                $status = 'close';
                                                $timezone = $vTimeZone;
                                                $opentime = $closetime = '';
                                                $day = $key;
                                                if (count($all) > 0) {
                                                    $day = $all[$key]['vDays'];
                                                    $dayname = $days[$key];
                                                    $status = strtolower($all[$key]['eType']);

                                                    $opentime = date('h:i A', strtotime($all[$key]['vOpenTime']));
                                                    $closetime = date('h:i A', strtotime($all[$key]['vCloseTime']));
                                                }
                                                $dayname = $days[$key];
                                                ?>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <div class="input-group col-md-2 col-xs-12">
                                                                <label for="inputEmail3" class="control-label"><?php echo ucfirst($dayname); ?></label>
                                                            </div><div class="col-md-2 col-xs-12">    <?php
                                                                if (strtolower($dayname) == 'saturday' || strtolower($dayname) == 'sunday') {
                                                                    $sty = '';
                                                                }
                                                                ?>
                                                                <select class="form-control selectpickerdays  select_<?php echo $day; ?>" id="status[<?php echo $day; ?>]" name="status[<?php echo $day; ?>]" style="<?php echo $sty; ?>">
                                                                    <option value="open" <?php $status = "open"; echo (($status == "open") ? "selected" : ""); ?> >Open</option>
                                                                    <option value="close" <?php echo (($status == "close" || $status == '') ? "selected" : ""); ?> >Close</option>
                                                                    <option value="open_24" <?php echo (($status == "open_24" || $status == '') ? "selected" : ""); ?> >Open 24Hrs</option>
                                                                </select>
                                                                <?php //}   ?>
                                                            </div>
                                                            <div class="input-group col-md-8 col-xs-12">
                                                                <div class="col-md-5 col-xs-12 col-sx-12 pull-left bootstrap-timepicker day_<?php echo $day; ?>">
                                                                    <input id="opentime[<?php echo $day; ?>]" name="opentime[<?php echo $day; ?>]" type="text" class="form-control timepicker" value="<?php echo $opentime; ?>" />
                                                                    <!-- <span class="input-group-addon"><i class="fa fa-clock-o"></i>
                                                                    </span> -->
                                                                </div>
                                                                <?php //if (strtolower($dayname) == 'saturday' || strtolower($dayname) == 'sunday') { ?>
                                                                <!-- <div class="col-md-5 col-xs-12 col-sm-12 pull-left empty_<?php echo $day; ?> text-center">
                                                                    <span class="input-group-addon"><i class="fa fa-ban"></i>
                                                                    </span>
                                                                </div> -->
                                                                <?php //} ?>

                                                                <div class="col-sm-2 to">To</div>

                                                                <div class="col-md-5 col-xs-12 col-sm-12 pull-right bootstrap-timepicker day_<?php echo $day; ?>">
                                                                    <input id="closetime[<?php echo $day; ?>]" name="closetime[<?php echo $day; ?>]" type="text" class="form-control timepicker" value="<?php echo $closetime; ?>" />
                                                                    <!-- <span class="input-group-addon"><i class="fa fa-clock-o"></i>
                                                                    </span> -->
                                                                </div>
                                                                <?php //if (strtolower($dayname) == 'saturday' || strtolower($dayname) == 'sunday') { ?>
                                                                <!-- <div class="col-md-5 col-xs-12 col-sm-12 pull-right empty_<?php echo $day; ?> text-center" >
                                                                    <span class="input-group-addon"><i class="fa fa-ban"></i>
                                                                    </span>
                                                                </div> -->
                                                                <?php //} ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?php //}    ?>
                                        <?php //if ($eType == 'Event') {    ?>
                                        <div id="eventdiv">
                                            <div class="form-group">
                                                <label for="inputEmail3" class="col-sm-3 control-label">Date</label>
                                                <div class="input-group">
                                                    <div class="col-md-5 pull-left marg15px date">
                                                        <input class="form-control" name="dtStartDate" id="dtStartDate" type="text" value="<?php echo ($data[0]['dtStartDate'] != '') ? date('d F Y', strtotime($data[0]['dtStartDate'])) : date('d F Y'); ?>" />
                                                        <!-- <span class="input-group-addon"><i class="fa fa-th"></i>
                                                        </span> -->
                                                    </div>
                                                    <div class="col-sm-2 ">To</div>
                                                    <div class="col-md-5 pull-right date">
                                                        <input class="form-control date" name="dtFinishDate" id="dtFinishDate" type="text" value="<?php echo ($data[0]['dtFinishDate'] != '') ? date('d F Y', strtotime($data[0]['dtFinishDate'])) : date('d F Y', strtotime(date('y-m-d'))); ?>" />
                                                        <!-- <span class="input-group-addon"><i class="fa fa-th"></i>
                                                        </span> -->
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="inputEmail3" class="col-sm-3 control-label">Time</label>
                                                <div class="input-group">
                                                    <div class="col-md-5 pull-left marg15px bootstrap-timepicker">
                                                        <input id="vStartTime" name="vStartTime" type="text" class="form-control timepicker" value="<?php echo date('h:i A', strtotime($data[0]['vStartTime'])); ?>" />
                                                        <!-- <span class="input-group-addon"><i class="fa fa-clock-o"></i>
                                                        </span> -->
                                                    </div>
                                                    <div class="col-sm-2 ">To</div>
                                                    <div class="col-md-5 pull-right bootstrap-timepicker">
                                                        <input id="vFinishTime" name="vFinishTime" type="text" class="form-control timepicker" value="<?php echo date('h:i A', strtotime($data[0]['vFinishTime'])); ?>" />
                                                        <!-- <span class="input-group-addon"><i class="fa fa-clock-o"></i>
                                                        </span> -->
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                        <?php //}     ?>
                                        <div class="row" style="margin-top:40px;margin-bottom:20px;">
                                            <div class="col-md-6 col-xs-12" id="upload-galaray-image-div">
                                                <button data-toggle="modal" data-target="#uploadphoto" id="galary-image-upload" class="btn btn-primary">Upload Image</button>
                                            </div>
                                            <div class="col-md-6 col-xs-12">
                                                <?php 
                                                    $urlLogo = $this->config->item('assets_url') . "img/imlogo.png";
                                                    foreach($imageArray as $imagelogo) {
                                                        if($imagelogo['eCoverImage'] == "Yes") {
                                                            $urlLogo = $this->config->item('site_url') . 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $imagelogo['vImage'];        
                                                        }
                                                    }
                                                ?>
                                                <img src="<?php echo $urlLogo; ?>" id="location-logo-upload" onClick="$('#upload-crop-image').trigger('click');"/> <span id="uoload-logo-text">Add a Logo</span>
                                                <input type="file" name="vLogo" id="upload-crop-image" class="btn btn-primary pull-left hide" value="Upload Logo">
                                            </div>
                                        </div>    
                                            <div class="modal fade" id="uploadphoto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="border:0px;">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="popup_dragdrop">

                                                                <div class="fileinput_inner_main">

                                                                    <div class="upload_title">
                                                                        <ul>
                                                                            <li class="current"><a href="#" class="tabs_common upload_tab">UPLOAD</a> |</li>
                                                                            <li><a href="#" class="tabs images_tab">IMAGES</a></li>
                                                                        </ul>	
                                                                    </div>

                                                                    <div class="upload_tab_content">
                                                                        <form action="<?php echo $this->config->item('site_url'); ?>location/upload_multi_images" method="post" class="dropzone" id="my-awesome-dropzone" enctype="multipart/form-data"><input type="hidden" name="location_id" value="<?php echo ($data[0]['iLocationId'] != '') ? $this->general->encryptData($data[0]['iLocationId']) : ''; ?>"></form>
                                                                        <p>Drop files here of click to upload</p>
                                                                    </div>

                                                                    <div class="images_tab_content">
                                                                        <ul>
                                                                            <?php
                                                                            if (count($imageArray) > 0) {
                                                                                foreach ($imageArray as $keyi => $valuei) {
                                                                                    if (strpos($valuei['vImage'],'comp_') !== false) {
                                                                                        $img1 = 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $valuei['vImage'];
                                                                                        $img_name = $data[0]['iLocationId'] . '/' . $valuei['vImage'];
                                                                                        $img_url1 = $this->config->item('site_url') . $img1;
                                                                                        $imgid1 = $valuei['iImageId']; 
                                                                                    ?>
                                                                            <li><a href="<?php echo "#"; //echo $img_url1;?>"><img src="<?php echo $img_url1; ?>" alt=""/></a><a href="" data-id="<?php echo $this->general->encryptData($imgid1);?>" data-url="<?php echo $this->general->encryptData($img_name);?>" class="delete_img"><span class="delete_btn">Delete</span></a></li>
                                                                                <?php
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </ul>
                                                                    </div>

                                                                </div>

                                                            <button data-toggle="modal" data-target="#uploadphoto" class="btn btn-primary btn-lg" id="done">Done</button>
                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <input type="hidden" id="hiddentriggerMulti" name="hiddentriggerMulti" value="">
                                                <!--<div id="locimage">Upload Image</div>-->
                                            </div>
                                            <?php /*
                                              if (count($imageArray) > 0) {
                                              foreach ($imageArray as $keyi => $valuei) {
                                              $img1 = 'public/upload/location/' . $data[0]['iLocationId'] . '/' . $valuei['vImage'];
                                              $img_url1 = $this->config->item('site_url') . $img1;
                                              $imgid1 = $valuei['iImageId'];
                                              ?>
                                              <div id="vProfileImg<?php echo $keyi + 1; ?>" class="col-md-3">
                                              <input type="radio" class="form-control" id="imageredio" name="imageredio" value="<?php echo $imgid1; ?>" <?php echo ($valuei['eCoverImage'] == 'Yes') ? 'checked="checked"' : ''; ?> /> Cover Image
                                              <a id="Delete<?php echo $keyi + 1; ?>" class="delete_image" data-id="<?php echo $imgid1; ?>" data-im="<?php echo $keyi + 1; ?>" class="text-danger"><i class="fa fa-trash-o"></i></a>
                                              <a class="thumb no-ajaxify" href="<?php echo $img_url1; ?>" data-gallery="gallery">
                                              <img src="<?php echo $img_url1; ?>" style="height:100px;width:100px;"  alt="photo" class="img-responsive"></a>
                                              </div>

                                              <?php } ?>
                                              <?php } */ ?>
                                        </div>
                                    </div>
                                    <!-- // Step 5 END -->
                                <ul class="pagination margin-bottom-none pager">
                                    <!--                                <li class="primary previous first"><a href="#" class="no-ajaxify">First</a>
                                                                    </li>-->
                                    <li class="primary previous"><a href="#" class="no-ajaxify">Previous</a>
                                    </li>
                                    <li class="next primary"><a href="#" class="no-ajaxify">Next</a>
                                    </li>
                                    <!--                                <li class="last primary"><a href="#" class="no-ajaxify">Last</a>
                                                                    </li>-->
                                    <li class="next finish primary" style="display:none;"><a href="javascript:void(0)" class="no-ajaxify">Finish</a>
                                    </li>
                                </ul>
                                </div>
                                <!-- Wizard pagination controls -->
                                <!-- // Wizard pagination controls END -->
                            </div>
                        </div>
                    </div>
                    <!-- // Form Wizard / Arrow navigation & Progress bar END -->

                </div>
            </div>
<?php } ?>
    </div>
</header>

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places"></script>
<script>
$(function () {
    var lat = -33.8688;
    var lng = 151.2195;
    var map = '';
    var marker = '';
    function initialize() {
        var mylatlng = new google.maps.LatLng($("#vLatitude").val(), $("#vLongitude").val());
        geocodePosition(mylatlng);
        var st = [{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#747474"},{"lightness":"23"}]},{"featureType":"poi.attraction","elementType":"geometry.fill","stylers":[{"color":"#f38eb0"}]},{"featureType":"poi.government","elementType":"geometry.fill","stylers":[{"color":"#ced7db"}]},{"featureType":"poi.medical","elementType":"geometry.fill","stylers":[{"color":"#ffa5a8"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#c7e5c8"}]},{"featureType":"poi.place_of_worship","elementType":"geometry.fill","stylers":[{"color":"#d6cbc7"}]},{"featureType":"poi.school","elementType":"geometry.fill","stylers":[{"color":"#c4c9e8"}]},{"featureType":"poi.sports_complex","elementType":"geometry.fill","stylers":[{"color":"#b1eaf1"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":"100"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"},{"lightness":"100"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffd4a5"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffe9d2"}]},{"featureType":"road.local","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"weight":"3.00"}]},{"featureType":"road.local","elementType":"geometry.stroke","stylers":[{"weight":"0.30"}]},{"featureType":"road.local","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#747474"},{"lightness":"36"}]},{"featureType":"road.local","elementType":"labels.text.stroke","stylers":[{"color":"#e9e5dc"},{"lightness":"30"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"on"},{"lightness":"100"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#d2e7f7"}]}];

        var mapOptions = {
            center: mylatlng,
            zoom: 19,
            styles: st
        };
        map = new google.maps.Map(document.getElementById('locmap'),
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
            anchor: new google.maps.Point(25, 52) // anchor
        };

        marker = new google.maps.Marker({
            map: map,
            draggable: true,
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
                map.setZoom(19);  // Why 17? Because it looks good.
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


            //infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
            //infowindow.open(map, marker);
        });

        google.maps.event.addListener(marker, 'dragend', changemarkerposition);
        function changemarkerposition() {
            $("#vLatitude").val(marker.position.lat());
            $("#vLongitude").val(marker.position.lng());
            geocodePosition(marker.getPosition());
            map.setCenter(marker.getPosition());
        }
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    $('a[href=#tab1]').on('click', function () {
        setTimeout(function () {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(marker.getPosition());
        }, 50);
    });

    $('#resize').on('click', function () {
        setTimeout(function () {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(marker.getPosition());
        }, 50);
    });
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
<script>
    $('#uploadphoto').on('shown.bs.modal', function () {
        $('#myInput').focus()
    })
</script>

<!-- div hide show js of upload and images -->    
<script>
    $(document).ready(function (e) {
        $(".upload_tab").click(function () {
            $('.tabs_common').parent().removeClass('current')
            $(this).parent().addClass('current')
            $(".images_tab_content").hide();
            $(".upload_tab_content").show();
        });
        $(".images_tab").click(function () {
            $('.tabs_common').parent().removeClass('current')
            $(this).parent().addClass('current')
            $(".upload_tab_content").hide();
            $(".images_tab_content").show();
        });
        $('#my-awesome-dropzone').parent().addClass('dis_none_prnt');
    });
</script>
<!-- file upload js -->
<script src="<?php echo $this->config->item('assets_url') ?>js/dropzone.min.js"></script>
<script src="<?php echo $this->config->item('assets_url') ?>js/dropzone.js"></script>
<?php include APPPATH . '/front-modules/views/bottom_script.php'; ?>
<?php include APPPATH . '/front-modules/views/main_footer.php'; //exit;      ?>