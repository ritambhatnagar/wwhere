<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>custom/group.js"></script>
<!--         Introduction Row 
        <div class="row">
            <div class="col-lg-6 location_box">
                <h3 class="txtblue headertitle"> Edit Form</h3>
            </div>
        </div>-->
<!-- Form -->
<form class="form-horizontal groupform" id="validateGroupForm" method="POST" action="" autocomplete="off">
    <input class="form-control" id="ajax" name="ajax" type="hidden" value="true">
    <input type="hidden" id="iGroupId" name="iGroupId" value="<?php echo (isset($iGroupId) && $iGroupId != '') ? $iGroupId : ''; ?>">
    <!-- Widget -->
    <!-- Row -->
    <!--    <div class="modal-body">
            <div class="container">
                <div class="row">
                    <div class="col-sm-9 col-md-6 ">
                        <div class="panel-default">
                            <div class="panel-body">-->


                        <div class="row">
                            <div class="col-md-12">
                            <div class="input-group">
                                <div class="input-group-addon">wwhere.is/</div>
                                <input class="form-control text-lowercase" id="vGroup" placeholder="Enter Your Group Name" name="vGroup" type="text" maxlength="20" <?php
                                if (isset($iGroupId)) {
                                    echo "value='" . $all[0]['vGroup'] . "'";
                                }
                                ?>>
                            </div>
                                <span style="position: absolute;right:20px;top:10px" id="groupurlcheck"></span>
                            </div>
                        </div>
    <div class="separator"></div>
    <!-- Form actions -->
    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="groupeditsubmit"><i class="fa fa-check-circle"></i> Submit </button>
    </div>

    <!--                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>-->

</form>
<!-- // Form END -->










