<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>wWhere - World's First Location Messenger</title>
        <meta name="description" content="Sharing addresses was never so convenient">
        <meta name="keywords" content="Location, Android, Messenger, ios, ciie, iim, ahmedabad, startup, maps, directions, exchange, places, event, venue, alpha testing, beta testing, incubated, funding, wwhere, where, india, silicon valley, new app, app, application, mobile app, mobile app 2014, 2014, UI">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/demo.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-switch.css">
        <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.0.js"></script>
        <script src="js/jquery.backstretch.min.js"></script>
        <script src="js/bootstrap-switch.js"></script>
        <script src="js/kinetic.js"></script>
        
        <script src="js/jquery.final-coutdown.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                
                //$("[name='select_os']").bootstrapSwitch();
                
                $('.header li').click(function(){
                    var scrollToDiv = $(this).attr('data_link');
                    if(scrollToDiv!="home"){
                        $('.header-logo').slideDown();
                    }else{
                        $('.header-logo').slideUp();
                    }
                    $('.header li').removeClass('active');
                    $(this).addClass('active');
                    $('html,body').animate({
                        scrollTop: $("#"+scrollToDiv).offset().top-50},
                    'slow');
                    window.location.replace = "https://wwhere.co/alpha/index.php#"+scrollToDiv;
                });
                $.backstretch("img/backgroundNew.jpg");
                
                $('#mailer-submit').click(function(){
                  var email = $('#inputEmail').val();
                  var pattern = /^[a-zA-Z0-9\-_]+(\.[a-zA-Z0-9\-_]+)*@[a-z0-9]+(\-[a-z0-9]+)*(\.[a-z0-9]+(\-[a-z0-9]+)*)*\.[a-z]{2,4}$/;
                  var form_control_div = $('#inputEmail').parents('.signup_email_id');
                  if (!pattern.test(email)){
                      form_control_div.addClass("has-error");
                      if(form_control_div.find(".glyphicon").hasClass('glyphicon-ok')){
                        form_control_div.find(".glyphicon").removeClass('glyphicon-ok');
                      }
                      if(form_control_div.find(".glyphicon").hasClass('glyphicon')){
                        form_control_div.find(".glyphicon").addClass('glyphicon-remove');
                      }else{
                        form_control_div.append("<div class='glyphicon glyphicon-remove form-control-feedback'></div>");
                      }
                      
                  }else{
                      if(form_control_div.hasClass("has-error")){
                        form_control_div.removeClass("has-error");
                      }
                      if(form_control_div.find(".glyphicon").hasClass('glyphicon-remove')){
                        form_control_div.find(".glyphicon").removeClass('glyphicon-remove');
                      }
                      if(form_control_div.find(".glyphicon").hasClass('glyphicon')){
                        form_control_div.find(".glyphicon").addClass('glyphicon-ok');
                      }else{
                        form_control_div.append("<div class='glyphicon glyphicon-ok form-control-feedback'></div>");
                      }
                      $('#mailer_form').submit();
                  }
                });
            });
        </script>
    </head>
    <body>