<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>custom/group.js"></script>
<!-- Modal -->
<div class="modal fade" id="modal-add-form">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal heading -->
            <!--            <div class="modal-header">
                            <h3 class="modal-title">Creates Groups</h3>
                        </div>-->
            <!-- // Modal heading END -->
            <!-- Modal body -->
            <div class="modal-body">
                <div class="innerAll">
                            <button type="button" class="close" id="closeGroup" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <form class="form-horizontal groupform" id="validateGroupForm" method="POST" action="" autocomplete="off"> 
                        <div class="innerLR">
                            <input class="form-control" id="ajax" name="ajax" type="hidden" value="true">
                            <div class="col-md-12">
                                <!-- Group -->
                                <div class="form-group">
                                    <label class="text-info centered text-capitalize" style="font-size: large;">Create New Group</label></div>
                            </div>
                            <!-- // Group END -->
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                            <div class="input-group">
                                <div class="input-group-addon">wwhere.is/</div>
                                <input class="form-control vUrlName text-lowercase" placeholder="Enter Your Group Name" id="vGroup" name="vGroup" type="text" maxlength="20"/>    
                            </div>
                                    <span style="position: absolute;right:20px;top:10px" id="groupurlcheck"></span>
                            </div>
                        </div>

                        <!-- // Row END -->
                        <div class="separator"></div>
                        <!-- Form actions -->
                        <div class="form-actions pad15">
                            <button type="submit" class="btn btn-primary" id="groupaddsubmit"><i class="fa fa-check-circle"></i> Save </button>
                        </div>

                        <!-- // Form actions END -->
                    </form> 
                </div>
            </div>
        </div>
        <!-- // Modal body END -->
    </div>
</div>










