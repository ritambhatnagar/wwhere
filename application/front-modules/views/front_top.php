<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $this->config->item('SITE_TITLE'); ?></title>
        <!-- Meta -->
        <meta name="keywords" content="Lean Business">
        <meta name="description" content="Lean Business: See More Do More">
        <meta name="author" content="leanbusiness.io">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
        <link href="<?php echo ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http'; ?>://fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic" rel="stylesheet" />
        <link rel="shortcut icon" href="<?php echo $this->config->item('images_url'); ?>favicon.ico" type="image/x-icon" />
<!--        <script>///date format for jqgrid date picker
            var dateformat='<?php // echo $this->config->item('display_date_time_format');    ?>';
        </script>-->
        <?php include APPPATH . '/front-modules/views/front_common_files.php'; ?>
    </head>
    <body class="one-page fixed-header">        
        <div class="page-box">
            <?php include APPPATH . '/front-modules/views/notification_message.php'; //footer file  ?>
            <div class="page-box-content">
                <?php include APPPATH . '/front-modules/views/front_header.php'; //footer file  ?>    
                <div id="main">