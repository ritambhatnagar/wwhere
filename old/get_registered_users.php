<html>
        <head><title>wWhere - List Registered Email IDs</title></head>
        <body>

<?php
if($_POST){
        
        if(isset($_POST['username'],$_POST['password']) && $_POST['username']=="wWhere" && $_POST['password']=="wWhere33##"){
                include('include/utils.php');
        
                $requestParam = array();
                
                $requestParam["controller"]="user";
                $requestParam["action"]="get_test_user";
                
                $requestParam["data"]='{}';
                
                $output = json_decode(httpPost("http://yogesh.joeee.com/locator/database_apis/apicall.php",$requestParam));
                
                $email_list = $output->respMsg;
                
                echo "<table>";
                echo "<tr>";
                echo "<th>Email Id</th>";
                echo "<th>Prefered OS</th>";
                echo "</tr>";
                foreach($email_list as $email_row){
                        echo "<tr>";
                        echo "<td>".$email_row->email_id."</td>";
                        echo "<td>".($email_row->prefer_os_id==1?'Android':'iOS')."</td>";
                        echo "</tr>";
                }
                echo "</table>";
        }else{
                echo "invalid access<br/><br/>";
                echo '  <form action="" method="post">
                                <input type="text" name="username" value="">
                                <input type="password" name="password" value="">
                                <button>Submit</button>
                        </form>';
        }
}else{
?>

                <form action="" method="post">
                        <input type="text" name="username" value="">
                        <input type="password" name="password" value="">
                        <button>Submit</button>
                </form>
<?php  } ?>
        </body>
</html>