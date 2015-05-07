<?php

require_once ("header.php");
require_once ("include/utils.php");
$output = "";
$outputArray= "";


if($_POST && isset($_POST['email'])){
    $requestParam = array();
    
    $email_id   =   $_POST['email'];
    //if selected then android else ios
    $os         =   isset($_POST['select_os'])?"1":"2";
    
    $requestParam["controller"]="user";
    $requestParam["action"]="register_test_user";
    $requestParam["data"]='{"0":"'.$email_id.'","1":'.$os.'}';
    
    $output = httpPost("http://yogesh.joeee.com/locator/database_apis/apicall.php",$requestParam);
    
}else{
    header("location:index.php");exit;
}
    $output_array = json_decode($output);
    $message = "";
    if($output_array->respStatus == 200){
        $message = "Congratulations. You are added to our mailing list.";
    }else{
        $message = "You are already added to our mailing list.";
    }
    echo "<div id='home' class='container'>
            <div class='row text-center logo'>
              <img src='img/lgo-image-text.png' alt='wWhere'/>
            </div>
            <div class='signup_div'>
                <h3 style='color:#fff;'>".$message."</h3>
                <script>
                    window.setTimeout(function(){
                        window.location.href = 'index.php';
                    }, 5000);
                </script>
            </div>
        </div>";
        
require_once ("footer.php");

?>