<a id="viewformlink" href="#viewform" data-toggle="modal" class="btn btn-primary" style="display: none"><i class="fa fa-fw fa-plus"></i></a>
<!-- Modal -->
<div class="modal fade" id="viewform">
    <div class="modal-dialog" style="height: auto;width: 50%;">
        <div class="modal-content" >
            <!-- Modal heading -->
            <!--            <div class="modal-header">
            
                        </div>-->
            <!-- // Modal heading END -->
            <!-- Modal body -->
            <div class="modal-body">
                <div class="innerAll">
                    <button type="button" class="close" id="formClose" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <div class="innerLR">
                        <div class="col-md-12">
                            <!-- Group -->
                            <div class="form-group">
                                <label class="text-info centered text-capitalize" id="viewformpagetitle" style="font-size: large;"></label></div>
                        </div>
                        <!-- // Group END -->
                    </div>
                    <div id="viewformiframe" style="width: 100%;"></div>
                </div>

                <!-- // Modal body END -->
            </div> 
        </div>
    </div>
</div>
<a id="alertboxlink" href="#alertboxdiv" data-toggle="modal" class="btn btn-primary popupelement" style="display: none"><i class="fa fa-fw fa-user"></i> </a>

<!--<div class="modal fade" id="alertboxdiv">
    <div class="modal-dialog">
        <div class="modal-content">
             Modal heading 
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" ><span id="viewboxtitle"></span>Confirm Delete</h3>
            </div>
             // Modal heading END 
             Modal body 
            <div class="modal-body">
                <div class="innerLR">
                    <h4>Are you sure you want to delete this?</h4>
                </div>
            </div>            
             // Modal body END 
            <div class="modal-bottom">
                <a href="javascript:void(0)" class="btn btn-warning col-md-offset-5" id="cancelbtn" aria-hidden="true" data-dismiss="modal">Cancel</a>
                <a id="confirmbtn" href="javascript:void(0)" class="btn btn-primary">Confirm</a>
            </div>

        </div>
    </div>
</div>-->
<!--<a id="confirmbtnlink" href="#confirmboxdiv" data-toggle="modal" class="btn btn-primary popupelement" style="display: none"><i class="fa fa-fw fa-user"></i> </a>
 Modal 
<div class="modal fade" id="confirmboxdiv">
    <div class="modal-dialog">
        <div class="modal-content">
             Modal heading 
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title" ><span id="viewboxtitle"></span>Confirm</h3>
            </div>
             // Modal heading END 
             Modal body 
            <div class="modal-body">
                <div class="innerLR">
                    <h4><span id="confirmmsg">Are you sure you want to delete this?</span></h4>
                </div>
            </div>            
             // Modal body END 
            <div class="modal-bottom">
                <a href="javascript:void(0)" class="btn btn-warning col-md-offset-5" id="cancelbtn4all" aria-hidden="true" data-dismiss="modal">Cancel</a>
                <a id="confirmbtn4all" href="javascript:void(0)" class="btn btn-primary">Confirm</a>
            </div>

        </div>
    </div>
</div>-->
<?php include APPPATH . '/front-modules/views/footer.php'; //footer file  ?>
</div>
</div>
</header>
<?php
$vSelectedMenu = array('', 'index');
$currentUrl = basename($this->uri->uri_string);
if (in_array($currentUrl, $vSelectedMenu)) {
    ?>
    <footer class="text-left">
        <div class="footer-above">
            <div class="container">
                <div class="row">
                    <div class="footer-col col-md-6 col-xs-12">
                        <ul class="list-inline">
                            <li> <a href="https://www.facebook.com/wWhereapp" target="_blank" title="Facebook" class="listinlinelink"><img src="<?php echo $this->config->item('assets_url'); ?>img/fb.png" alt="" /></a> </li>
                            <li> <a href="https://twitter.com/wwhereit" title="Twitter" target="_blank"><img src="<?php echo $this->config->item('assets_url'); ?>img/twitter.png" alt="" /></a> </li>
                            <li> <a href="https://www.youtube.com/channel/UCEyjv1_JhcUeLt4vpMerIYQ" title="Youtube" target="_blank"><img src="<?php echo $this->config->item('assets_url'); ?>img/you_tube.png" alt="" /></a> </li>
                        </ul>
                    </div>
                    <div class="footer-col col-md-6 col-xs-12 text-right">
                        <ul class="list-inline bluetxt">
                            <li> <a href="<?php echo base_url().'user/faq'; ?>" class="bluelink">FAQ's</a></li>
                            <li><i class="fa fa-circle circle_img" ></i></li>
                            <li> <a href="<?php echo base_url().'user/privacy_policy'; ?>" class="bluelink">PRIVACY POLICY</a> </li>
                            <li><i class="fa fa-circle circle_img" ></i></li>
                            <li> <a href="<?php echo base_url().'blog'; ?>" class="bluelink">BLOG</a> </li>
                            <li><i class="fa fa-circle circle_img" ></i></li>
                            <li> <a href="<?php echo base_url().'user/press'; ?>" class="bluelink">PRESS</a> </li>
                        </ul>
                        <div class="bluetxt13px">Copyright © wwhere All Rights Reserved</div>
                    </div>
                </div>
            </div>
        </div>
    </footer>    
<?php } ?>
<link rel="stylesheet" href="<?php echo $this->config->item('css_url'); ?>dev_style.css"/>
</body>
</html>