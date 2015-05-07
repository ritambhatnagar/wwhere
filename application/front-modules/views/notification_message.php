<?php
$text = $type = $error_class = "";
$style_val = "display:none;";
if ($this->session->flashdata('success') != '') {
    $error_class = "notyfy_success";
    $style_val = "";
    $type = 'success';
    $text = $this->session->flashdata('success');
} elseif ($this->session->flashdata('failure') != '') {
    $error_class = "notyfy_error";
    $style_val = "";
    $type = 'error';
    $text = $this->session->flashdata('failure');
}
?>
<link rel="stylesheet" href="<?php echo $this->config->item('css_url'); ?>jquery.notyfy.css"/>
<link rel="stylesheet" href="<?php echo $this->config->item('css_url'); ?>notyfy.theme.default.css"/>
<script type="text/javascript" src="<?php echo $this->config->item('js_url'); ?>jquery.notyfy.js"></script>
<ul id="notyfy_container_top" class="notyfy_container" style="<?php echo $style_val; ?>"></ul>

<script>
    $(document).ready(function () {
        var notyfytext = '<?php echo $text; ?>';
        var notyfytype = '<?php echo $type; ?>';
        var notyfylayout = 'top'
        $(function ()
        {
            notyfy({
                text: notyfytext,
                type: notyfytype,
                layout: notyfylayout,
                timeout: 3500
            });
        });
        
        $("#navmenu").click(function () {
            var X = $(this).attr('data-id');
            if (X == 1) {
                $("#menu").attr("style", "visibility: none;display: none;");
                $("body").addClass("menu-left-visible");
                $(".container-fluid").addClass("menu-hidden");
                
                $(this).attr('data-id', '0');
            }
            else {
                $("#menu").attr("style", "visibility: visible;display: block;");
                $("body").removeClass("menu-left-visible");
                $(".container-fluid").removeClass("menu-hidden");
                $(this).attr('data-id', '1');
            }
        });
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>
