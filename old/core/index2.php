<?php
// Get the PHP helper library from twilio.com/docs/php/install
require_once('Services/Twilio.php'); // Loads the library
 
// Your Account Sid and Auth Token from twilio.com/user/account
$sid = "ACb2544178e787fb1cb905850e5e2faf82";
$token = "3f4e3a3e477c12e5b3d353fea34952f8"; 
$client = new Services_Twilio($sid, $token);

echo "<pre>";
print_r($client->account->messages->sendMessage("+12019031614", "+918866770482", "Hello Yogesh! Try this out", "http://www.example.com/hearts.png"));
echo "</pre>";
?>