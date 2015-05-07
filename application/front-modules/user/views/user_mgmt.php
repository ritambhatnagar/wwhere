<?php include 'inc/top.php'; // Meta data and header?>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jqgrid/jquery.jqGrid.min.js"></script>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jqgrid/grid.locale-en.js"></script>

<div class="txt_orange">
    <i class="fa fa-user fa-1x" style="position: relative;margin-left: 5px;margin-top: 5px !important;"></i> Admin Management
    <div class="btn-group btn-group-sm pull-right">
        <?php $editpermission = $this->general->check_permission('form', 'admin_add_edit', 'ajax');
//            if ($editpermission == 1) {
                ?>
        <a data-href="<?php echo $this->config->item('admin_site_url'); ?>admin_add_edit" class="btn btn-primary" onclick="urlParse(this)"><i class="fa fa-fw fa-plus-circle"></i> Add New</a>
            <?php // } ?>
        <?php include APPPATH . '/front-modules/views/columnfields.php'; ?>
    </div>
</div>
<div class="BoxesGreyMain">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <table id="list"></table> 
                <div id="pager" ></div>
            </div>
        </div>
        <!-- // Widget END -->
    </div>
</div>
<script>
    $(document).ready(function () {
        var editpath = rootPath + 'admin/admin_action';
        getgriddata('admin_master', editpath);
    });
</script>

<?php include 'inc/bottom_script.php'; ?>