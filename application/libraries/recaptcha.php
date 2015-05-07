<?php

/*
 * @author Sanjay Sinalkar
 *  
 */

class Recaptcha {

    protected $CI;
    private $public_key = "6LcQPfMSAAAAAI0C1s-WD2yl9F-AJoU8CcDoBZSL";
    private $private_key = "6LcQPfMSAAAAAPSoKCbOrxJvCUHJ9nKR7RzQkrNC";
//    private $public_key = "6LcEGOASAAAAAN1BF1kCUsHijLIb6piEJQSrnLPh";
//    private $private_key = "6LcEGOASAAAAAFs-4Fb0Ed52ZsOpNwUO5PnDsvn9";

    public function __construct() {
        $this->CI = &get_instance();
    }

    const RECAPTCHA_API_SERVER = "http://www.google.com/recaptcha/api";
    const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
    const RECAPTCHA_VERIFY_SERVER = "www.google.com";

    var $is_valid;
    var $error;

    function setKeys($public_key, $private_key) {
        $this->public_key = $public_key;
        $this->private_key = $private_key;
    }

    function recaptcha_qsencode($data) {
        $req = "";
        foreach ($data as $key => $value)
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';

        // Cut the last '&'
        $req = substr($req, 0, strlen($req) - 1);
        return $req;
    }

    function recaptcha_http_post($host, $path, $data, $port = 80) {

        $req = $this->recaptcha_qsencode($data);

        $http_request = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if (false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) )) {
            die('Could not open socket');
        }

        fwrite($fs, $http_request);

        while (!feof($fs))
            $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
    }

    function recaptcha_get_html($error = null, $use_ssl = false) {
        if ($this->public_key == null || $this->public_key == '') {
            die("recaptcha_get_html To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
        }

        if ($use_ssl) {
            $server = self::RECAPTCHA_API_SECURE_SERVER;
        } else {
            $server = self::RECAPTCHA_API_SERVER;
        }

        $errorpart = "";
        if ($error) {
            $errorpart = "&amp;error=" . $error;
        }

        return '<script type="text/javascript">
                var RecaptchaOptions = {
                    theme : "white"
                 };
                </script>
                <script type="text/javascript" src="' . $server . '/challenge?k=' . $this->public_key . $errorpart . '"></script>

            <noscript>
                    <iframe src="' . $server . '/noscript?k=' . $this->public_key . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
                    <textarea name="recaptcha_challenge_field" rows="3" cols="30"></textarea>
                    <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
            </noscript>';
    }

    function recaptcha_check_answer($remoteip, $challenge, $response, $extra_params = array()) {

        $recaptcha_response = array();

        if ($this->private_key == null || $this->private_key == '') {
            die(" recaptcha_check_answer To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
        }

        if ($remoteip == null || $remoteip == '') {
            die("For security reasons, you must pass the remote ip to reCAPTCHA");
        }




        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {

            //$recaptcha_response = array(


            return $recaptcha_response;


            $this->is_valid = false;
            $this->error = 'incorrect-captcha-sol';
        }

        $response = $this->recaptcha_http_post(self::RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify", array(
            'privatekey' => $this->private_key,
            'remoteip' => $remoteip,
            'challenge' => $challenge,
            'response' => $response
                ) + $extra_params
        );

        $answers = explode("\n", $response [1]);


        if (trim($answers [0]) == 'true') {
            $this->is_valid = true;
        } else {
            $this->is_valid = false;
            $this->error = $answers [1];
        }

        $recaptcha_response['is_valid'] = $this->is_valid;
        $recaptcha_response['error'] = $this->erro;

        return $recaptcha_response;
    }

    function recaptcha_get_signup_url($domain = null, $appname = 'Codeigniter') {
        return "https://www.google.com/recaptcha/admin/create?" . $this->recaptcha_qsencode(array('domains' => $domain, 'app' => $appname));
    }

    function recaptcha_aes_pad($val) {
        $block_size = 16;
        $numpad = $block_size - (strlen($val) % $block_size);
        return str_pad($val, strlen($val) + $numpad, chr($numpad));
    }

    /* Mailhide related code */

    function recaptcha_aes_encrypt($val, $ky) {
        if (!function_exists("mcrypt_encrypt")) {
            die("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
        }
        $mode = MCRYPT_MODE_CBC;
        $enc = MCRYPT_RIJNDAEL_128;
        $val = $this->recaptcha_aes_pad($val);
        return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
    }

    function recaptcha_mailhide_urlbase64($x) {
        return strtr(base64_encode($x), '+/', '-_');
    }

    /* gets the reCAPTCHA Mailhide url for a given email, public key and private key */

    function recaptcha_mailhide_url($email) {
        if ($this->public_key == '' || $this->public_key == null || $this->private_key == "" || $this->private_key == null) {
            die("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
                    "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>");
        }


        $ky = pack('H*', $this->private_key);
        $cryptmail = $this->recaptcha_aes_encrypt($email, $ky);

        return "http://www.google.com/recaptcha/mailhide/d?k=" . $this->public_key . "&c=" . $this->recaptcha_mailhide_urlbase64($cryptmail);
    }

    function recaptcha_mailhide_email_parts($email) {
        $arr = preg_split("/@/", $email);

        if (strlen($arr[0]) <= 4) {
            $arr[0] = substr($arr[0], 0, 1);
        } else if (strlen($arr[0]) <= 6) {
            $arr[0] = substr($arr[0], 0, 3);
        } else {
            $arr[0] = substr($arr[0], 0, 4);
        }
        return $arr;
    }

    function recaptcha_mailhide_html($email) {
        $emailparts = $this->recaptcha_mailhide_email_parts($email);
        $url = $this->recaptcha_mailhide_url($this->public_key, $this->private_key, $email);

        return htmlentities($emailparts[0]) . "<a href='" . htmlentities($url) .
                "' onclick=\"window.open('" . htmlentities($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities($emailparts [1]);
    }

    public function getCaptcha() {

        $htmlstr = $this->recaptcha_get_html();

        return $htmlstr;
    }

}

?>