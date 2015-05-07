<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GCM
 *
 * @author Ravi Tamada
 */
class GCM {

    //put your code here
    // constructor
    function __construct() {
        
    }
    public function send_notification($registatoin_ids, $message) {
        // include config
        
        $GOOGLE_API_KEY = "AIzaSyCdcPX8fzlVcHAeMN690eqL0c7f0NBnarE";
        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
        
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
        //print_r($fields);
        $headers = array(
            'Authorization: key=' . $GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
        
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            //echo 'Curl failed: ' . curl_error($ch);
        }else{
            //print $result;
        }
        // Close connection
        curl_close($ch);
        return ($result === FALSE)?false:true;
    }
}
?>
