<?php
  require_once ("header.php");
  require_once ("include/utils.php");
  $requestParam = array();
  $requestParam["controller"]="user";
  $requestParam["action"]="register_counter";
  $requestParam["data"]='{"0":"1"}';
  $output = httpPost("http://yogesh.joeee.com/locator/database_apis/apicall.php",$requestParam);
  $output_array = json_decode($output);
  $counter_string = "";
  define("APP_TEST_USER",5000);
  $counter_string = (APP_TEST_USER - ((int)$output_array->respMsg)) . " of ". APP_TEST_USER." invites pending";
  
?>
<script type="text/javascript">
            $(document).ready(function () {
                $('.div_android').click(function(){
                    if($('.div_ios').hasClass('div_on'))
                      $('.div_ios').removeClass('div_on');
                    if(!$('.div_ios').hasClass('div_off'))
                    $('.div_ios').addClass('div_off');
                    
                    if($('.div_android').hasClass('div_off'))
                      $('.div_android').removeClass('div_off');
                    if(!$('.div_android').hasClass('div_on'))
                    $('.div_android').addClass('div_on');
                    
                    $('#select_os').attr("checked","checked");
                });
                $('.div_ios').click(function(){
                    if($('.div_android').hasClass('div_on'))
                      $('.div_android').removeClass('div_on');
                    if(!$('.div_android').hasClass('div_off'))
                    $('.div_android').addClass('div_off');
                    
                    if($('.div_ios').hasClass('div_off'))
                      $('.div_ios').removeClass('div_off');
                    if(!$('.div_ios').hasClass('div_on'))
                    $('.div_ios').addClass('div_on');
                    
                    $('#select_os').removeAttr("checked","checked");
                });
            });
        </script>
        <div id="home" class="container">
            <div class="row text-center logo">
              <img src="img/lgo-image-text.png" alt="wWhere"/>
            </div>
            <div class="signup_div">
              <form id="mailer_form" action="mailer_signup.php" method="post" class="form-horizontal" role="form">
                  <div class='os_div'>
                    I use<br>
                    <input type="checkbox" id="select_os" name="select_os" checked="checked">
                    <div class='div_android div_on fleft'>Android</div>
                    <div class='div_ios div_off fleft'>iOS</div>
                    <div class='clear'></div>
                  </div>
                  <div class='signup_controls'>
                    <div class='signup_email_id fleft'>
                      <input id="inputEmail" type="email" name="email" class="form-control" placeholder="E-mail Address">
                    </div>
                    <div class='signup_submit_btn fleft'>
                      <button id="mailer-submit" type="button" class="btn btn-default">SUBMIT</button>
                    </div>
                    <div class='clear'></div>
                  </div>
                  <div class="row text-center" style="color:#fff;font-size:12px">
                    (<?php echo $counter_string; ?>)
                  </div>
              </form>
            </div>
            <div class="row text-center">
                <a href="/blog" style="color:#fff;font-size:14px">VIEW OUR BLOG</a>
            </div>
        <!--
        <div id="contactus" class="pages">
            contact us page
        </div>-->
<?php
  require_once ("footer.php");
?>