<link href="<?php echo $this->config->item('css_url'); ?>bootstrap.min.css" rel="stylesheet">
<link href="<?php echo $this->config->item('css_url'); ?>main.css" rel="stylesheet">
<link href="<?php echo $this->config->item('css_url'); ?>responsive.css" rel="stylesheet">
<link href="<?php echo $this->config->item('css_url'); ?>jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->config->item('assets_url'); ?>font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

<script>
    var rootPath = "<?php echo $this->config->item('site_url'); ?>";
</script> 

<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jquery-ui.min.js"></script>

<form class="form-horizontal groupform" id="validateGroupForm" method="POST" action="" autocomplete="off">
    <input class="form-control" id="ajax" name="ajax" type="hidden" value="true">
    <input type="hidden" id="iGroupId" name="iGroupId" value="<?php echo (isset($iGroupId) && $iGroupId != '') ? $iGroupId : ''; ?>">
    <!-- Widget -->
    <!-- Row -->
    <div class="modal-body">
        <div class="container">
            <div class="row">
                <div class="col-sm-9 col-md-6 ">
                    <div class="panel-default">
                        <span id="log_error" style="margin:5px 0px 0px 15px;color: #e74c3c;"></span>
                        <div class="panel-body">

                            <fieldset>
                                <div class="row">
                                    <div class="col-sm-12 col-md-10  col-md-offset-1 ">

                                        <div id="vCitydiv" class="form-group">
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
                                            <input type="button" class="btn btn-primary" id="groupeditsubmit" value="Submit">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
<!-- // Form END -->
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
            $('#formClose').trigger('click');
            $.ajax({
                url: rootPath + 'location/setCurrentCity',
                type: 'POST',
                data: {
                    'city': sel_arr[0]
                },
                success: function (dataa) {
                    $('#formClose').trigger('click');
                    return true;
                }
            });
        });
    });
</script>
<?php
include APPPATH . 'front-modules/views/bottom_script.php'; // bottom  ?>