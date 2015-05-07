<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

Class General {

    protected $CI;
    public $orderBook;

    function __construct() {
        $this->CI = & get_instance();
        $this->orderBook = array();
//        error_reporting(E_ALL);
    }

    public static function encrypt_decrypt($action, $string) {
        $output = false;

        $key = 'wWw';

        // initialization vector
        $iv = md5(md5($key));

        if ($action == 'encrypt') {
            $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
            $output = str_replace('\0', '', addslashes(rtrim($output, "")));
        }

        return trim($output, "=");
    }

    function encrypt($sData, $sKey = 'wWw') {
        $sResult = '';
        for ($i = 0; $i < strlen($sData); $i++) {
            $sChar = substr($sData, $i, 1);
            $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
            $sChar = chr(ord($sChar) + ord($sKeyChar));
            $sResult .= $sChar;
        }
        return $this->encode_base64($sResult);
    }

    function decrypt($sData, $sKey = 'wWw') {
        $sResult = '';
        $sData = $this->decode_base64($sData);
        for ($i = 0; $i < strlen($sData); $i++) {
            $sChar = substr($sData, $i, 1);
            $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
            $sChar = chr(ord($sChar) - ord($sKeyChar));
            $sResult .= $sChar;
        }
        return $sResult;
    }

    function encode_base64($sData) {
        $sBase64 = trim(base64_encode($sData), '=');
        return strtr($sBase64, '+/', '-_');
    }

    function decode_base64($sData) {
        $sBase64 = strtr($sData, '-_', '+/');
        return base64_decode($sBase64);
    }

    function getRandomNumber($len = "15") {
        $better_token = strtoupper(md5(uniqid(rand(), true)));
        $better_token = substr($better_token, 1, $len);
        return $better_token;
    }

    public function getPostForm($post_data, $msg = "", $action = "") {
        //pr($post_data);exit;
        $str = '<html>
            <form name="frmpost" action="' . $action . '" method=post>';
        if (is_array($post_data)) {
            $str .= "<br><input type='Hidden' name='frmpostdata' value='" . str_replace("'", "\u0027", json_encode($post_data)) . "'>";
        } else {
            $str .= '<br><input type="Hidden" name="frmpostdata" value="' . stripslashes($post_data) . '">';
        }
        $str .= '<input type="Hidden" name="frmpost_msg" value="' . $msg . '"><input type="Hidden" name="record_already_exists" value="1">
            </form>
            <script>
                document.frmpost.submit();
            </script>
            </html>';
        echo $str;
        exit;
    }

    function restorePost($post_str) {
        $post_arr = array();
        if ($post_str != '') {
            $post_arr = str_replace("\u0027", "'", $post_str);
            $post_arr = json_decode(stripslashes(htmlspecialchars_decode($post_arr)), true);
        }
        return $post_arr;
    }

    function getcopyrighttext() {
        $copyrighttext = str_replace("#CURRENT_YEAR#", date('Y'), $this->CI->systemsettings->getSettings('COPYRIGHTED_TEXT'));
        $copyrighttext = str_replace("#COMPANY_NAME#", $this->CI->systemsettings->getSettings('COMPANY_NAME'), $copyrighttext);
        return $copyrighttext;
    }

    function TimetoDate($text) {
        return date("M j, Y", $text);
    }

    function getTime($date, $format = '', $top = false) {
        if ($format == '') {
            $format = $this->CI->config->item('display_time_format');
        }
        if ($date != '0000-00-00' && $date != '0000-00-00 00:00:00' && $date != '') {
            $user_time_zone = $this->CI->config->item('LBU_USER_TIME_ZONE');
            $date = ($user_time_zone != '') ? $this->changetimefromUTC($date, $user_time_zone) : $date;
            return ($top) ? date($format, $date) : date($format, strtotime($date));
        } else {
            return '---';
        }
    }

    function getDateTime($date, $format = '', $top = false) {
        if ($format == '') {
            $format = $this->CI->config->item('display_date_time_format');
        }
        if ($date != '0000-00-00' && $date != '0000-00-00 00:00:00' && $date != '') {
            $user_time_zone = $this->CI->config->item('LBU_USER_TIME_ZONE');
            $date = ($user_time_zone != '') ? $this->changetimefromUTC($date, $user_time_zone) : $date;
            return ($top) ? date($format, $date) : date($format, strtotime($date));
        } else {
            return '---';
        }
    }

    function getDate($date, $format = '', $top = false) {
        if ($format == '') {
            $format = $this->CI->config->item('display_date_format');
        }
        if ($date != '0000-00-00' && $date != '0000-00-00 00:00:00' && $date != '') {
//            $user_time_zone = $this->CI->config->item('LBU_USER_TIME_ZONE');
//            $date = ($user_time_zone != '') ? $this->changetimefromUTC($date,$user_time_zone) : $date;
            return ($top) ? date($format, $date) : date($format, strtotime($date));
        } else {
            return '---';
        }
    }

    function redirect_ajax($url) {
        echo "<script>window.top.location.href = '" . $url . "';</script>";
        exit;
    }

    function getEncodedURL($data) {
        return $data;
    }

    /*     * ******************************************************* */

    function pushNotification($device_id, $notification_array = array()) {
        if (!empty($device_id)) {
            if (strlen($device_id) > 70) {
                $messageText = $notification_array['aps']['alert'];
                $res = $this->android_notification($device_id, $messageText, $notification_array);
            } else {
                $this->iosnotification($device_id, $notification_array);
            }
        }
    }

    function iosnotification($device_id, $notification_array = array()) {
        $site_path = $this->CI->config->item('site_path');

        if (!empty($device_id)) {

            // push notification start .....

            $deviceToken = $device_id;
            //$message = "Push Notification Done";
            $badge = 0;
            $sound = 'received5.caf';
            $body = array();
            //$body['aps'] = array('alert' => str_replace("\n" , " " , strip_tags($message)));                    
            $body = array_merge($body, $notification_array);

            if ($badge)
                $body['aps']['badge'] = $badge;
            if ($sound)
                $body['aps']['sound'] = $sound;

//                    $body['aps']['alert'] = $body['aps']['alert_subject'];
            //$body['aps']['otherparam'] = "review~480";

            $ctx = stream_context_create();
            #echo $site_path.'apns-dev.pem';exit;
            stream_context_set_option($ctx, 'ssl', 'local_cert', $site_path . 'apns-dev.pem');

            $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
            if ($fp) {
                $payload = json_encode($body);
                $msg = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload;

                #print $deviceToken." sending message :" . $payload . "\n";exit;
                fwrite($fp, $msg);
                fclose($fp);

                $f = fopen($site_path . 'public/upload/notification.html', 'a+');
                fwrite($f, '<br/>' . date('Y-m-d H:i:s') . '<br/>');
                fwrite($f, print_r($device_id, true) . '<br/>');
                fwrite($f, print_r($body, true) . '<br/>');
                fwrite($f, print_r($fp, true));
                fwrite($f, '<br/>');
                fclose($f);
            }

            //echo 'Response:-';print_r($fp); //exit;
            // push notification end .....
        }
    }

    function android_notification($device_id = '', $message = 'hi', $extra = array()) {
        $result = '';
        if ($device_id != '') {
            $apiKey = $this->CI->config->item('ANDROID_NOTIFICATION_KEY');

            // Replace with real client registration IDs
            $registrationIDs = array($device_id);

            // Set POST variables
            $url = 'https://android.googleapis.com/gcm/send';

            $data1 = array("message" => $message);
            $data = array_merge($data1, $extra);

            $fields = array(
                'registration_ids' => $registrationIDs,
                'data' => $data,
            );

            $headers = array(
                'Authorization: key=' . $apiKey,
                'Content-Type: application/json'
            );

            // Open connection
            $ch = curl_init();

            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //     curl_setopt($ch, CURLOPT_POST, true);
            //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            // Execute post
            $result = curl_exec($ch);

            //pr($result);exit;
            $f = fopen($this->CI->config->item('site_path') . 'public/upload/android.html', 'a+');
            fwrite($f, '<br/>' . date('Y-m-d H:i:s') . '<br/>');
            fwrite($f, print_r($registrationIDs, true) . '<br/>');
            fwrite($f, print_r($data, true));
            fwrite($f, print_r($result, true));
            fwrite($f, '<br/>');
            fclose($f);

            // Close connection
            curl_close($ch);
        }

        return $result;
    }

    function import($vFile, $photopath, $image_size, $type, $oldFile = "") {
        $imagemagickinstalldir = $this->CI->config->item('imagemagickinstalldir');
        $sizeArrr = $image_size;
        $PARAM_ARRAY['TARGET_DIR'] = $photopath;
        for ($i = 0; $i < count($sizeArrr); $i++) {
            $PARAM_ARRAY[$i]['WIDTH_HEIGHT'] = $sizeArrr[$i];
            $PARAM_ARRAY[$i]['PREFIX'] = ($i + 1);
            $oldFilePath = $photopath . "" . $PARAM_ARRAY[$i]['PREFIX'] . $oldFile;
            @unlink($oldFilePath);
        }
        $target_dir = $PARAM_ARRAY['TARGET_DIR'];
        $temp_gallery = '';
        if ($temp_gallery == '') {
            $temp_gallery = $target_dir;
        }
        if ($this->CI->config->item('useimagemagick') == "Yes") {
            $this->CI->load->library('imagemagick');
            $imObj = new Imagemagick($imagemagickinstalldir, $temp_gallery);
            $imObj->setTargetdir($target_dir);
        }
        $count = 0;
        $idx = 0;
        foreach ($vFile as $file) {
            if ($this->CI->config->item('useimagemagick') == "Yes") {
                #echo"<pre>";print_R($file);exit;
                $imObj->loadByFileData($file);
                $imObj->setVerbose(FALSE);
                $size = $imObj->GetSize();
            } else {
                $size = GetImageSize($file['tmp_name']);
            }
            $HEIGHT = array();
            $WIDTH = array();
            $PERFIX = array();
            for ($i = 1; $i < count($PARAM_ARRAY); $i++) {
                $height_width = $PARAM_ARRAY[$i - 1]['WIDTH_HEIGHT'];
                $height_width_arr = explode("x", strtolower($height_width));
                if (isset($height_width_arr[0])) {
                    $WIDTH[$i] = $height_width_arr[0];
                } else {
                    $WIDTH[$i] = "";
                }
                if (isset($height_width_arr[1])) {
                    $HEIGHT[$i] = $height_width_arr[1];
                } else {
                    $HEIGHT[$i] = "";
                }
                $PERFIX[$i] = $PARAM_ARRAY[$i - 1]['PREFIX'];
            }
            $time = time();
            $useimagemagick = $this->CI->config->item('useimagemagick');
            if ($useimagemagick == "Yes") {
                for ($i = 1; $i < count($PARAM_ARRAY); $i++) {
                    $temp = $height_width . "_";
                    if ($WIDTH[$i] > 0 && $HEIGHT[$i] > 0) {
                        $imObj->loadByFileData($file);
                        if ($size[0] < $size[1]) {
                            $size[1] = $size[1] * $WIDTH[$i] / $size[0];
                            $size[0] = $WIDTH[$i];
                        } else {
                            $size[0] = $size[0] * $HEIGHT[$i] / $size[1];
                            $size[1] = $HEIGHT[$i];
                        }
                        //$imObj->Resize($WIDTH[$i], $HEIGHT[$i], 1);
                        $imObj->Resize($size[0], $size[1], 'keep_aspect');
                        //$imObj->Crop($WIDTH[$i], $HEIGHT[$i], 'left');
                        list($WIDTH[$i], $HEIGHT[$i]) = $imObj->GetSize();
                        $filename[$i] = $imObj->Save($temp);
                    } else {
                        $filename1 = $target_dir . "/" . $temp . basename($file['name']);
                        copy($file['tmp_name'], $filename1);
                        $filename[$i] = $temp . basename($file['name']);
                    }
                }
                $imObj->CleanUp();
                if (isset($PERFIX[$i])) {
                    $fname = substr($filename[1], strlen($PERFIX[$i]));
                } else {
                    $fname = substr($filename[1], 0);
                }
                #echo $fname;exit;
                $ReturnFile[$idx] = $fname;
            } else {
                for ($i = 1; $i < count($PARAM_ARRAY); $i++) {
                    $temp = $height_width;
                    $filename1 = $target_dir . "/" . $temp . basename($file['name']);
                    #echo $filename1;exit;
                    copy($file['tmp_name'], $filename1);
                }
                $ReturnFile[$idx] = $time . basename($file['name']);
            }
            //@unlink($file['tmp_name']);
            $idx++;
        }
        return $ReturnFile;
    }

    function file_upload($photopath = '', $vphoto = '', $vphoto_name = '', $vaildExt = '') {
        $msg = "";
        $vphotofile = '';
        if (!empty($vphoto_name) && is_file($vphoto)) {
            // Remove Dots from File name
            $tmp = explode(".", $vphoto_name);
            for ($i = 0; $i < count($tmp) - 1; $i++) {
                $tmp1[] = $tmp[$i];
            }
            $file = implode("_", $tmp1);
            $ext = $tmp[count($tmp) - 1];
            $vaildExtArr = explode(",", strtoupper($vaildExt));
            if (trim($vaildExt) == "" || in_array(strtoupper($ext), $vaildExtArr)) {
                //$vphotofile=$file.".".$ext;
                $vphotofile = $file . "_" . date("Ymdhis") . "." . $ext;
                $ftppath1 = $photopath . $vphotofile;
                if (!copy($vphoto, $ftppath1)) {
                    $vphotofile = '';
                    $msg = "Uploading file(s) is failed.!";
                } else {
                    $msg = "File(s) uploaded successfully.!";
                }
            } else {
                $vphotofile = '';
                $msg = "File extension is not valid, vaild ext. are  $vaildExt .!";
            }
        } else {
            $vphotofile = '';
            $msg = "Upload file path not found";
        }
        $ret[0] = $vphotofile;
        $ret[1] = $msg;
        return $ret;
    }

    function image_upload($photo, $path, $prefix = '', $filename = '') {
        $photo_name_str = base64_decode($photo);
        if ($filename == '') {
            $filename = date('Ymdhis') . rand() . '-image.jpg';
        }
        $filename_path = $path . $prefix . $filename;
        if (!$handle = fopen($filename_path, 'w')) {
            $filename = '';
            $msg = "Cannot open file ($filename)";
        }
        // Write $somecontent to our opened file.
        if (fwrite($handle, $photo_name_str) === FALSE) {
            $filename = '';
            $msg = "Cannot write to file ($filename)";
        }
        @fclose($handle);
        return $filename;
    }

    function do_image_replacement($photo_val = '') {
        $value_pic = str_replace(" ", "+", $photo_val);
        $value_photo = str_replace("data:image/jpeg;base64,", "", $value_pic);
        $value_photo = str_replace("data:image/png;base64,", "", $value_photo);
        return $value_photo;
    }

    function do_image_mime_operations($value_photo = '') {
        $f = finfo_open();
        $mime_type = finfo_buffer($f, base64_decode($value_photo), FILEINFO_MIME_TYPE);
        if (strstr($mime_type, "image/")) {
            $filename = date("Ymdhis") . rand() . "-image." . str_replace("image/", "", $mime_type);
        } else {
            $filename = "";
        }
        return $filename;
    }

    function getTablePrimaryKey($table_name = '') {
        if ($table_name != "") {
            $tbl_fields = $this->CI->db->field_data($table_name);
            foreach ((array) $tbl_fields as $field) {
                if ($field->primary_key) {
                    $pkkey = $field->name;
                    break;
                }
            }
        }
        return $pkkey;
    }

    function from_camel_case($str = '') {
        $str = substr($str, 1);
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    function to_camel_case($str = '') {
        $str = substr($str, 1);
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "" . strtoupper($c[1]);');
        return preg_replace_callback('/(_)/', $func, $str);
    }

    function dateDefinedFormat($format = '', $value = '') {
        if ($format == '' || $value == '') {
            return '---';
        } else if ($value == "0000-00-00" || $value == "0000-00-00 00:00:00" || $value == "00:00:00") {
            return '---';
        }
        return date($format, strtotime($value));
    }

    function dateTimeDefinedFormat($format = '', $value = '') {
        if ($format == '' || $value == '') {
            return '---';
        } else if ($value == "0000-00-00" || $value == "0000-00-00 00:00:00" || $value == "00:00:00") {
            return '---';
        }
        return date($format, strtotime($value));
    }

    function timeDefinedFormat($format = '', $value = '') {
        if ($format == '' || $value == '') {
            return '---';
        } else if ($value == "0000-00-00" || $value == "0000-00-00 00:00:00" || $value == "00:00:00") {
            return '---';
        }
        return date($format, strtotime($value));
    }

    function getPhoneMaskedView($format = '', $value = '') {
        if ($value == '') {
            return '---';
        }
        $format = ($format != "") ? $format : $this->CI->config->item("ADMIN_PHONE_FORMAT");
        $splitFormat = str_split(trim($format));
        $splitValue = str_split(trim($value));
        $retPhone = '';
        for ($i = 0, $j = 0; $i < count($splitFormat); $i++) {
            if (ctype_alnum($splitFormat[$i]) || $splitFormat[$i] == "*") {
                $retPhone .= $splitValue[$j];
                $j++;
            } else {
                $retPhone .= $splitFormat[$i];
            }
        }
        return $retPhone;
    }

    function getPhoneUnmaskedView($format = '', $value = '') {
        if ($value == '') {
            return '';
        }
        $format = ($format != "") ? $format : $this->CI->config->item("ADMIN_PHONE_FORMAT");
        $splitFormat = str_split(trim($format));
        $splitValue = str_split(trim($value));
        $retPhone = '';
        for ($i = 0; $i < count($splitValue); $i++) {
            if (ctype_alnum($splitValue[$i])) {
                $retPhone .= $splitValue[$i];
            }
        }
        return $retPhone;
    }

    function createUploadFolderIfNotExists($folder_name = '', $folderIdWise = false, $id = 0) {
        if ($folder_name == "") {
            return false;
        }
        $upload_folder = $this->CI->config->item('upload_path') . $folder_name . DS;
        if (!is_dir($upload_folder)) {
            $oldUmask = umask(0);
            $res = @mkdir($upload_folder, 0777);
            @chmod($upload_folder, 0777);
            umask($oldUmask);
        }
        if ($folderIdWise && $id > 0) {
            $upload_folder_idWise = $this->CI->config->item('upload_path') . $folder_name . DS . $id . DS;
            if (!is_dir($upload_folder_idWise)) {
                $oldUmask = umask(0);
                $res = @mkdir($upload_folder_idWise, 0777);
                @chmod($upload_folder_idWise, 0777);
                umask($oldUmask);
            }
        }
        return true;
    }

    function createfolder($path) {
        $site_path = $this->CI->config->item('upload_path');
        $res = '';
        $pathfolder = @explode("/", str_replace($site_path, "", $path));
        $realpath = "";
        for ($p = 0; $p < count($pathfolder); $p++) {
            if ($pathfolder[$p] != '') {
                $realpath = $realpath . $pathfolder[$p] . "/";
                $makefolder = $site_path . "/" . $realpath;
                if (!is_dir($makefolder)) {
//                    $makefolder = @mkdir($makefolder, 0777);
//                    @chmod($makefolder, 0777);

                    $oldUmask = umask(0);
                    $res = @mkdir($makefolder, 0777);
                    @chmod($makefolder, 0777);
                    umask($oldUmask);
                }
            }
        }

        return $res;
    }

    public static function deleteDir($dirPath, $move = false, $destination = '') {
        if (!is_dir($dirPath)) {
            return "$dirPath must be a directory";
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        
        $files = glob($dirPath . '*', GLOB_MARK);
        
        foreach ($files as $key => $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                if ($move && $destination != '') {
                    $file_arr = explode('/', $file);                    
                    $filename = $file_arr[count($file_arr) - 1];                    
                    if (@copy($file, $destination . '/' . $filename)) {
                        @chmod($destination . '/' . $filename, 0777);
                        $files_arr[] = $filename;
                        @unlink($file);
                    }
                } else {
                    @unlink($file);
                }
            }
        }
        @rmdir($dirPath);
        return $files_arr;
    }

    function navigationDateTime($value = '') {
        if ($value == '') {
            return '---';
        } else if ($value == "0000-00-00" || $value == "0000-00-00 00:00:00" || $value == "00:00:00") {
            return '---';
        }
        return date("M d, y h:i:s a", strtotime($value));
    }

    function languageTranslation($src, $dest, $text) {
        $dest = strtolower($dest);
        $src = strtolower($src);
        // using bing translation api
        $appId = $this->CI->config->item('SYSTEM_LANG_APP_ID');
        $text = urlencode($text);
        $txt = '';
        if (trim($appId) != '') {
            $trans = file_get_contents("http://api.microsofttranslator.com/v2/Http.svc/Translate?appId=" . $appId . "&text=" . $text . "&from=$src&to=$dest");
            $tr = $this->xml2array($trans, 1);
            $txt = (isset($tr['string']) && is_string($tr['string'])) ? $tr['string'] : '';
            $txt = trim($txt);
        }
        // using frengly.com api
        if (trim($txt) == '') {
            $username = $this->CI->config->item('SYSTEM_LANG_USERNAME');
            $password = $this->CI->config->item('SYSTEM_LANG_PASSWORD');
            $trans = file_get_contents("http://www.frengly.com/controller?action=translateAnyAny&crop_percent_size=100&input_convert_to=jpg&manualSrcLang=null&manualSrcLangLabel=null&lb_langs=$dest&s_langs=$src&src=$src&dest=$dest&text=" . $text . "&username=" . $username . "&password=" . $password . "&wordSrc=" . $text . "&shipping=ar&message=&translated=");
            $tr = $this->xml2array($trans, 0);
            $txt = utf8_decode($tr['response']['content']);  // return $tr['root']['translation'];
            $txt = trim($txt);
        }
        return $txt;
    }

    function uploadFilesOnSaveForm($fileArr = array(), $id = 0) {
        if (!is_array($fileArr) || count($fileArr) == 0) {
            return;
        }
        foreach ($fileArr as $key => $val) {
            $file_name = $val['file_name'];
            $folder_name = $val['folder_name'];
            $id_wise = $val['id_wise'];
            $old_file = $val['old_file'];
            $temp_file_path = $this->CI->config->item('upload_path') . '__temp/' . $file_name;
            if ($id_wise == 'Yes') {
                $this->createUploadFolderIfNotExists($folder_name, true, $id);
                $dest_file_path = $this->CI->config->item('upload_path') . $folder_name . DS . $id . DS . $file_name;
                $old_file_path = $this->CI->config->item('upload_path') . $folder_name . DS . $id . DS . $old_file;
            } else {
                $this->createUploadFolderIfNotExists($folder_name);
                $dest_file_path = $this->CI->config->item('upload_path') . $folder_name . DS . $file_name;
                $old_file_path = $this->CI->config->item('upload_path') . $folder_name . DS . $old_file;
            }
            if (file_exists($temp_file_path)) {
                if (@copy($temp_file_path, $dest_file_path)) {
                    @unlink($temp_file_path);
                    if (file_exists($old_file_path) && $old_file != '') {
                        @unlink($old_file_path);
                    }
                }
            }
        }
    }

    function getSystemEmailData($type = '') {
        $this->CI->db->where("vEmailCode", $type);
        return $this->CI->db->get('system_email')->result_array();
    }

    function getVariablesByTemplate($iEmailTemplateId) {
        $this->CI->db->where("iEmailTemplateId", $iEmailTemplateId);
        return $this->CI->db->get('system_email_vars')->result_array();
    }

    function sendMail($data = array(), $type_format = "CONTACT_US") {

        if (is_array($data) && count($data) > 0) {

            $mailarr = $this->getSystemEmailData($type_format);

            $vAdminName = $mailarr[0]['vFromName'];
            $vAdminEmail = $mailarr[0]['vFromEmail'];
            //$emailVarData = $this->getVariablesByTemplate($mailarr[0]['iEmailTemplateId']);
            $subject = $data['vSubject'];

            if ($subject == '') {
                $subject = $mailarr[0]['vEmailSubject'];
            }
            $exfindarray = $exreplacearray = $findarray = $replacearray = array();
            $i = 0;
            foreach ($data as $key => $val) {
                $findarray[] = $key;
                $replacearray[] = $val;
                $i++;
            }

            $tEmailMessage = stripslashes($mailarr[0]['tEmailMessage']);

            $site_logo = "<img src='" . $this->CI->config->item('site_url') . "images/admin/logo.gif'/>";

            if ($data['vEmail'] != '') {
                $to = $data['vEmail'];
            } else {
                $to = $this->CI->config->item('EMAIL_ADMIN');
            }
            $mailarr[0]['vEmailFooter'] = str_replace("#COMPANY_NAME#", $this->CI->config->item('COMPANY_NAME'), $mailarr[0]['vEmailFooter']);
            $admin_url = $this->CI->config->item("admin_url");
            $site_url = $this->CI->config->item("site_url");

            $findarray = array_merge($findarray, array("#COMPANY_NAME#", "#SITE_URL#", "#MAIL_FOOTER#", "#SITE_LOGO#", '#'));
            $replacearray = array_merge($replacearray, array($this->CI->config->item('COMPANY_NAME'), $site_url, $mailarr[0]['vEmailFooter'], $site_logo, ''));

            switch ($type_format) {
                case "CONTACT_US":
                    $exfindarray = array("#vName#", "#vFromEmail#", "#vPhone#", "#tComments#");
                    $exreplacearray = array($data['vName'], $data['vFromEmail'], $data['vPhone'], $data['tComments']);
                    break;
                case "ADMIN_REGISTER":
                    $exfindarray = array("#LOGIN_URL#");
                    $exreplacearray = array($admin_url);
                    break;
                case "FORGOT_PASSWORD":
                    $exfindarray = array("#LOGIN_URL#");
                    $exreplacearray = array($site_url);
                    break;
                case "NEWSLETTER":
                    $exfindarray = array("#LOGIN_URL#");
                    $exreplacearray = array($admin_url);
                    break;
            }

            $findarray = array_merge($findarray, $exfindarray);
            $replacearray = array_merge($replacearray, $exreplacearray);
            $body = str_replace($findarray, $replacearray, $tEmailMessage);

            $subject = str_replace($findarray, $replacearray, $subject);

            if ($type_format == 'FORGOT_PASSWORD') {
                $from = $vAdminName . "<" . $vAdminEmail . "> ";
            } else {
                if ($data['vFromEmail'] != "") {
                    $from = $data['vFromName'] . " < " . $data['vFromEmail'] . " >";
                    $from_name = $data['vFromName'];
                } else {
                    $from = $mailarr[0]['vFromName'] . " <" . $this->CI->config->item('EMAIL_ADMIN') . ">";
                    $from_name = 'admin';
                }
                if ($data['vCCEmail']) {
                    $cc = $data['vCCEmail'];
                }
                if ($data['vBCCEmail'] != "") {
                    $bcc = $data['vBCCEmail'];
                }
            }

            $this->CI->load->library('email');
            $this->CI->email->from($from, $from_name);
            $this->CI->email->to($to);
            if ($cc != "") {
                $this->CI->email->cc($cc);
            }
            if ($bcc != "") {
                $this->CI->email->bcc($bcc);
            }
            $this->CI->email->subject($subject);
            $this->CI->email->message($body);
            $success = $this->CI->email->send();
            //echo "<br> $body <br> success".$success = $this->CI->email->send();exit;
            return $success;
            #$success = $this->general->sendEmail($to, $subject, $vBody, $from, '', $cc, $bcc, $from_name);
        } else {
            return false;
        }
    }

    function get_admin_email() {
        return array("email" => "info@9spl.in");
    }

    function insertNavigateLogHistory($queryStr = '', $navigAction = "Viewed", $navigType = 'List', $recName = '', $vModuleName = '', $mainMenu = '', $subMenu = '') {
        $this->CI->load->model('general/model_navigation');
        $NAVIGATION_LOG_REQ = $this->CI->config->item('NAVIGATION_LOG_REQ');
        $LogNavOn = (strtolower($NAVIGATION_LOG_REQ) == "y") ? true : false;
        $iAdminId = $this->CI->session->userdata('iAdminId');
        if (empty($iAdminId) || !$LogNavOn) {
            return false;
        }
        $navigStr = $supStr = $queryStr;
        $iModuleId = 0;
        if (trim($vModuleName) != '') {
            if ($navigType == "Form") {
                $funcArr = explode("/", $navigStr);
                $supStr = $funcArr[0] . "/" . $funcArr[1] . "/index";
                $queryArr = explode("/", $funcArr[2]);
                if (in_array("parID", $queryArr)) {
                    $parModind = array_search("parMod", $queryArr);
                    $parIDind = array_search("parID", $queryArr);
                    $supStr .= "|" . $queryArr[++$parModind] . "|" . $queryArr[++$parIDind];
                }
            }
            $this->CI->db->select("m.iModuleId as iModuleId");
            $this->CI->db->select("m.vMenuDisplay as subMenu");
            $this->CI->db->select("(SELECT s.vMenuDisplay FROM admin_menu s WHERE s.iAdminMenuId = m.iParentId) as mainMenu", false);
            $this->CI->db->where("m.vModuleName", $vModuleName);
            $this->CI->db->order_by("mainMenu", "DESC");
            $this->CI->db->limit(1);
            $db_menu_data = $this->CI->db->get("admin_menu AS m")->result_array();

            if (!is_array($db_menu_data) || count($db_menu_data) == 0) {
                return false;
            }
            $mainMenu = ($db_menu_data[0]['mainMenu']) ? $db_menu_data[0]['mainMenu'] : "";
            ;
            $subMenu = $db_menu_data[0]['subMenu'] ? $db_menu_data[0]['mainMenu'] : "";
            $iModuleId = ($db_menu_data[0]['iModuleId']) ? $db_menu_data[0]['iModuleId'] : 0;
        }

        $insertNavigArr['iAdminId'] = $iAdminId;
        $insertNavigArr['iModuleId'] = $iModuleId;
        $insertNavigArr['vMainMenu'] = $mainMenu;
        $insertNavigArr['vSubMenu'] = $subMenu;
        $insertNavigArr['vRecordName'] = $recName;
        $insertNavigArr['vNavigQString'] = $navigStr;
        $insertNavigArr['vSupQString'] = $supStr;
        $insertNavigArr['eNavigAction'] = $navigAction;
        $insertNavigArr['eNavigType'] = $navigType;
        $insertNavigArr['dTimeStamp'] = date("Y-m-d H:i:s");
        $iNavigationId = $this->CI->model_navigation->insert($insertNavigArr);
        return $iNavigationId;
    }

    function getSingleColArray($dataArr = array(), $index = "") {
        $retArr = array();
        if (!is_array($dataArr) || count($dataArr) == 0 || $index == "") {
            return $retArr;
        }
        foreach ((array) $dataArr as $key => $val) {
            $retArr[] = $val[$index];
        }
        return $retArr;
    }

    function getDBQueriesList() {
        $dbQueries = $this->CI->db->queries;
        $dbQueryTimes = $this->CI->db->query_times;
        $queriesLog = array();
        for ($i = 0; $i < count($dbQueries); $i++) {
            $queriesLog[$i]['query'] = $dbQueries[$i];
            $queriesLog[$i]['time(ms)'] = round(($dbQueryTimes[$i] * 1000), 3);
        }
        $queriesLog[0]['count'] = count($dbQueries);
        return $queriesLog;
    }

    function getgroupPaternt($iParentId) {
        $this->CI->db->select('GROUP_CONCAT(iCategoryId)AS cid');
        $this->CI->db->where("iParentId", "$iParentId");
        $this->CI->db->where("eStatus", 'Active');
        $this->CI->db->from("category");
        $this->CI->db->order_by("iOrderBy");
        $menu_data = $this->CI->db->get()->result_array();
        //echo $this->CI->db->last_query();
        //pr($menu_data);exit;
        return $menu_data;
    }

    function getTopCatMenu($iParentId = '') {
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $this->CI->db->select();
        $this->CI->db->where("iParentId", "$iParentId");
        $this->CI->db->where("eStatus", 'Active');
        $this->CI->db->from("category");
        $this->CI->db->order_by("iOrderBy");
        $menu_data = $this->CI->db->get()->result_array();
        return $menu_data;
    }

    function getTopMenu($iParentId = '', $location = "Header") {
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $this->CI->db->select();
        $this->CI->db->where("l.vLocation", $location);
        $this->CI->db->where("m.iParentId", "$iParentId");
        $this->CI->db->where("m.eStatus", 'Active');
        $this->CI->db->from("menu_master AS m");
        $this->CI->db->join("location AS l", "l.iLocationId = m.iLocationId");
        $this->CI->db->join("page_settings AS p", "p.iPageId = m.iPageId", 'left');
        $this->CI->db->order_by("iOrderBy");
        $menu_data = $this->CI->db->get()->result_array();
        return $menu_data;
    }

    function getSideMenu($iParentId = '') {
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $menu_data = $this->getTopMenu($iParentId, "Sidebar");
        return $menu_data;
    }

    function getSideMenuAfterLogin($iParentId = '') {
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $menu_data = $this->getTopMenu($iParentId, "Sidebar");
        return $menu_data;
    }

    function getFooterMenu($iParentId = '') {
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $menu_data = $this->getTopMenu($iParentId, "Footer");
        return $menu_data;
    }

    function encryptData($input) {
        $output = trim(base64_encode(base64_encode($input)), '==');
        $output = $this->encrypt($input);
        //$output = $this->encrypt_decrypt('encrypt', $input);
        return $output;
    }

    function decryptData($input) {
        $output = base64_decode(base64_decode($input));
        $output = $this->decrypt($input);
        //$output = $this->encrypt_decrypt('decrypt', $input);
        return $output;
    }

    function getAPIData($message) {

        $TARGETBRANCH = $this->CI->config->item('api_target_branch');
        $CREDENTIALS = "Universal API/" . $this->CI->config->item('api_username') . ":" . $this->CI->config->item('api_password');
        //pr($TARGETBRANCH);
        $auth = base64_encode("$CREDENTIALS");
        $header = array(
            "Content-Type: text/xml;charset=UTF-8",
            "Accept: gzip,deflate",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"\"",
            "Authorization: Basic $auth",
            "Content-length: " . strlen($message),
        );
        $soap_do = curl_init("https://apac.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService");
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
        $respons = curl_exec($soap_do);
        curl_close($soap_do);

        $xml2Array = $this->xml2array($respons);
        return $xml2Array;
    }

    function checkSession($d = '', $type = 'Front') {
        if (!$this->CI->session->userdata('expire') || $this->CI->session->userdata('expire') > time()) {
            $this->CI->session->set_userdata('start', time());
            $expire = $this->CI->session->userdata('start') + $this->CI->config->item('SESSION_TIMEOUT') * 60;
            $this->CI->session->set_userdata('expire', $expire);
            if ($d != '') {
                return 'true';
            }
        } else if ($this->CI->session->userdata('expire') > 0 && $this->CI->session->userdata('expire') < time()) {
            $this->CI->session->unset_userdata('expire', '');
            $this->CI->session->set_flashdata('failure', 'Session time out..');
            if ($d != '') {
                return 'timeout';
            } else {
                if ($type == 'back') {
                    redirect('signout');
                } else {
                    redirect('logout');
                }
            }
        }
    }

//    function checkSession() {
//        if (!$this->CI->session->userdata('expire') || $this->CI->session->userdata('expire') > time()) {
//            $this->CI->session->set_userdata('start', time());
//            $expire = $this->CI->session->userdata('start') + 1 * 3;
//            $this->CI->session->set_userdata('expire', $expire);
//            } else if ($this->CI->session->userdata('expire') > 0 && $this->CI->session->userdata('expire') < time()) {
//            $this->CI->session->unset_userdata('expire', '');
//            $this->CI->session->set_flashdata('failure', 'Session time out..');
//            
////            redirect('signout');
//        }
//    }

    function checkSessionfront() {
        if (!$this->CI->session->userdata('expire') || $this->CI->session->userdata('expire') > time()) {
            $this->CI->session->set_userdata('start', time());
            $expire = $this->CI->session->userdata('start') + $this->CI->config->item('SESSION_TIMEOUT') * 60;
            $this->CI->session->set_userdata('expire', $expire);
        } else if ($this->CI->session->userdata('expire') < time()) {
            $this->CI->session->unset_userdata('expire', '');
            $this->CI->session->set_flashdata('failure', 'Session time out..');
            redirect('signout');
        }
    }

    function getdate_time($datetime) {
        $datetime = explode('T', $datetime);
        $time = substr($datetime[1], 0, 5);
        $datetime = array('date' => $datetime[0], 'time' => $time);
        return $datetime;
    }

    function getPagePermission($userid, $moduleurl, $type = "list") {
        $this->CI->load->model('new_user/model_permission');
        $permissionGrant = $this->CI->model_permission->getPagePermission($userid, $moduleurl, $type);
        //pr($permissionGrant);exit;    
        $read_arr = explode(',', $permissionGrant[0]['isRead']);
        $write_arr = explode(',', $permissionGrant[0]['isWrite']);
        $delete_arr = explode(',', $permissionGrant[0]['isDelete']);
//        pr($write_arr);exit;
        $isAllowed = 1;
        if (count($permissionGrant) > 0 && (($type == 'list' && in_array($userid, $read_arr)) || ($type == 'form' && in_array($userid, $write_arr)) || ($type == 'del' && in_array($userid, $delete_arr)))) {
            $isAllowed = 1;
        } else {
            $isAllowed = 0;
        }
        return $isAllowed;
    }

    function getPageDeletePermission($iRoleId, $selfurl, $type = "list") {
        $this->CI->load->model('new_user/model_permission');
        $permissionGrant = $this->CI->model_permission->getPageDeletePermission($iRoleId, $selfurl, $type);
        $isAllowed = true;
        if (count($permissionGrant) > 0 && ($type == 'list' && $permissionGrant[0]['isDelete'] == '1')) {
            return $isAllowed;
        } else {
            return 0;
        }
    }

    function getLoginDetail() {
        $this->CI->load->model('admin/model_log_history');
        $result = $this->CI->model_log_history->getResult();
        return $result;
        //pr($result);
    }

    function getAdminDetail() {

        $this->CI->load->model('admin/model_admin');
        $result = $this->CI->model_admin->getadmindetail();
        return $result;
    }

    function geteReadInbox() {
        $this->CI->load->model('account/model_account');
        $result = $this->CI->model_account->getReadInbox();
        return $result;
    }

    function addSpaceInString($keyword, $replace = false) {
        $wResult = preg_match_all('/(^I|[[:upper:]]{2,}|[[:upper:]][[:lower:]]*|[[:lower:]]+|\d+|#)/u', $keyword, $matches);
        if ($replace) {
            return implode('_', $matches[0]);
        } else {
            return implode(' ', $matches[0]);
        }
    }

    function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values)
            return; //Hmm...
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array;
        $repeated_tag_index = array();
        foreach ($xml_values as $data) {
            unset($attributes, $value);
            extract($data);
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag')
                    $result = $value;
                else
                    $result['value'] = $value;
            }
            if (isset($attributes) and $get_attributes) {
                foreach ($attributes as $attr => $val) {
                    if ($priority == 'tag')
                        $attributes_data[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            if ($type == "open") {
                $parent[$level - 1] = & $current;
                if (!is_array($current) or ( !in_array($tag, array_keys($current)))) {
                    $current[$tag] = $result;
                    if ($attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                }
                else {
                    if (isset($current[$tag][0])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level] ++;
                    } else {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            } elseif ($type == "complete") {
                if (!isset($current[$tag])) {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                }
                else {
                    if (isset($current[$tag][0]) and is_array($current[$tag])) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level] ++;
                    } else {
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level] ++; //0 and 1 index is already taken
                    }
                }
            } elseif ($type == 'close') {
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
    }

    function truncate($string, $limit, $link = '', $break = ".", $pad = "...") {
        $link = (($link != '') ? " <a data-href='$link' onclick='urlParse(this)'>more...</a>" : '');
        // return with no change if string is shorter than $limit        
        if (strlen($string) <= $limit)
            return $string . $link;

        // is $break present between $limit and the end of the string?
        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad . $link;
            }
        } else {
            $string = substr($string, 0, $limit) . $pad . $link;
        }

        return $string;
    }

    function checkTimeout() {

//         pr($this->CI->session->userdata('expire'));exit;
        if (!$this->CI->session->userdata('expire') || $this->CI->session->userdata('expire') > time()) {
            $this->CI->session->set_userdata('start', time());
            $expire = $this->CI->session->userdata('start') + 1 * 5;
            $this->CI->session->set_userdata('expire', $expire);
            return $this->CI->session->userdata('start') . '   ' . $this->CI->session->userdata('expire');
        } else if ($this->CI->session->userdata('expire') > 0 && $this->CI->session->userdata('expire') < time()) {
            $this->CI->session->unset_userdata('expire', '');
            $this->CI->session->set_flashdata('failure', 'Session time out..');
            return 'timeout';
//            redirect('signout');
        }
//        $status='true';
//        if ($this->CI->session->userdata('iUserId') > 0) {
//            if ($this->CI->session->userdata('timeout') == '') {
//                $this->CI->session->set_userdata('start', time());
//                $expire = $this->CI->session->userdata('start') + 15 * 60;
//                $this->CI->session->set_userdata('timeout', $expire);
//                $status= 'true';
//            } else if ($this->CI->session->userdata('timeout') < time()) {
//                $this->CI->session->unset_userdata('timeout', '');
//                $this->CI->session->set_flashdata('failure', 'Session time out..');
//                $status= 'timeout';
////            redirect('signout');
//            } 
//        }else{
//            $status= 'true';
//        }
//        return $status;
    }

    function getOS() {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $os_platform = "Unknown OS Platform";

        $os_array = array(
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/nokia/i' => 'Nokia',
            '/android(.*)/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/bb(.*)/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
                $os_platform = $value;
            }
        }

        return $os_platform;
    }

    function getFrontPanelMenu($iParentId = '', $location = "Header", $type = 'Front') {
        if ($type == 'Back') {
            $userId = $this->CI->session->userdata('iAdminId');
        } else {
            $userId = $this->CI->session->userdata('iUserId');
            $user_parentId = $this->CI->session->userdata('iParentId');
        }
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $this->CI->db->select();
        $this->CI->db->where("m.iParentId", "$iParentId");
        $this->CI->db->where("m.eMenuType", $type);
        $this->CI->db->from("module_master AS m");
        if ($user_parentId > 0 && $type == 'Front') {
            $this->CI->db->join("user_permission AS up", "up.iModuleId = m.iModuleId");
            $this->CI->db->where("(FIND_IN_SET('$userId',up.isRead) || FIND_IN_SET('$userId',up.isWrite) || FIND_IN_SET('$userId',up.isDelete)) and m.eStatus =", 'Active');
        } elseif ($type == 'Back') {
            $this->CI->db->join("permission AS p", "p.iModuleId = m.iModuleId");
            $this->CI->db->where("(FIND_IN_SET('$userId',p.isRead) || FIND_IN_SET('$userId',p.isWrite) || FIND_IN_SET('$userId',p.isDelete)) and m.eStatus =", 'Active');
            $this->CI->db->where('iRoleId', $this->CI->config->item('LBAU_ROLE_ID'));
        } else {
            $this->CI->db->where("m.eStatus", 'Active');
        }
        $this->CI->db->order_by("iSequenceOrder");
        $menu_data = $this->CI->db->get()->result_array();
//        echo $this->CI->db->last_query();exit;
        return $menu_data;
    }

    function check_permission($type = "list", $selfurl = '', $call = '') {
        if ($call == 'ajax') {
            if ($this->CI->session->userdata('iParentId') != 0) {
                return $this->getPagePermission($this->CI->session->userdata('iUserId'), $selfurl, $type);
            } else {
                return 1;
            }
        } else {
            if ($this->CI->session->userdata('iParentId') != 0) {
                $selfurl = basename($_SERVER['REQUEST_URI']);
                if ($selfurl == 'forbidden') {
                    return 1;
                }
                $isAllowed = $this->getPagePermission($this->CI->session->userdata('iUserId'), $selfurl, $type);
                if (!$isAllowed) {
                    redirect('forbidden');
                }
            } else {
                return 1;
            }
        }
    }

    function ago($time) {
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = strtotime($this->getCurrentDateTime());

        $difference = $now - $time;
        $tense = "ago";

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j].= "s";
        }

        return "$difference $periods[$j] ago ";
    }

    function after($time) {
        $now = strtotime($this->getCurrentDateTime());
        $your_date = strtotime($time);
        $datediff = $your_date - $now;
        $time_after = round($datediff / (60 * 60 * 24));
        return ($time_after >= 0) ? $time_after : 0;
    }

    function get_post() {
        $get_arr = is_array($this->CI->input->get(null, true)) ? $this->CI->input->get(null, true) : array();
        $post_arr = is_array($this->CI->input->post(null, true)) ? $this->CI->input->post(null, true) : array();
        return $request_arr = array_merge($get_arr, $post_arr);
    }

    function strip_get_post($data = '') {
        if ($data == '') {
            $data = $this->get_post();
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->strip_get_post($value);
            } else {
                $data[$key] = strip_tags($value);
            }
        }
        return $data;
    }

    function checkVirusInFile($file) {
        $return = array('success' => 'false', 'err' => 'We doubt your file is affected by Virus or do not proper extension');
        $allowed_types = array(
            /* images extensions */
            'jpeg', 'bmp', 'png', 'gif', 'tiff', 'jpg',
            /* audio extensions */
            'mp3', 'wav', 'midi', 'aac', 'ogg', 'wma', 'm4a', 'mid', 'orb', 'aif',
            /* movie extensions */
            'mov', 'flv', 'mpeg', 'mpg', 'mp4', 'avi', 'wmv', 'qt',
            /* document extensions */
            'txt', 'pdf', 'ppt', 'pps', 'xls', 'doc', 'xlsx', 'pptx', 'ppsx', 'docx', 'csv'
        );
        $mime_type_black_list = array(
            # HTML may contain cookie-stealing JavaScript and web bugs
            'text/html', 'text/javascript', 'text/x-javascript', 'application/x-shellscript',
            # PHP scripts may execute arbitrary code on the server
            'application/x-php', 'text/x-php', 'text/x-php',
            # Other types that may be interpreted by some servers
            'text/x-python', 'text/x-perl', 'text/x-bash', 'text/x-sh', 'text/x-csh',
            'text/x-c++', 'text/x-c',
                # Windows metafile, client-side vulnerability on some systems
                # 'application/x-msmetafile',
                # A ZIP file may be a valid Java archive containing an applet which exploits the
                # same-origin policy to steal cookies      
                # 'application/zip',
        );
        $file_name = $file['name'];
        pathinfo($file_name, PATHINFO_EXTENSION);
        $tmp_file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!strlen($tmp_file_extension) || (!$allow_all_types &&
                !in_array($tmp_file_extension, $allowed_types))) {
            return $return;
        }
        $finfo = new finfo(FILEINFO_MIME, MIME_MAGIC_PATH);

        if ($finfo) {
            $mime = $finfo->file($file_name_tmp);
        } else {
            $mime = $file_type;
        }

        $mime = explode(" ", $mime);
        $mime = $mime[0];

        if (substr($mime, -1, 1) == ";") {
            $mime = trim(substr($mime, 0, -1));
        }
        $rs = in_array($mime, $mime_type_black_list) == false;
        if ($rs == 1) {
            $return['success'] = 'true';
            $return['err'] = '';
        }
        //echo "come<pre>"; print_r($return);die;
        //return (in_array($mime, $mime_type_black_list) == false);
        return $return;
    }

    function checkVirus($ImageFile, $redircturl = '', $ajax = '') {
        if ($redircturl == '') {
            $redircturl = basename($_SERVER['REQUEST_URI']);
        }
        if ($ImageFile != '') {
            $vrsRes = $this->checkVirusInFile($ImageFile);
            if ($vrsRes['success'] == 'true') {
                return true;
            } else {
                $alert = array('Failure' => array('message' => $vrsRes['err'], 'class' => 'alert-danger'));
                $this->CI->session->set_userdata("errorsocialAlert", $alert);
                echo $this->CI->config->item('site_url') . $redircturl;
                exit;
            }
        } else {
            return true;
        }
    }

    function changetimefromUTC($date_time, $timezone = '') {
        $timezone = ($timezone == '') ? $this->CI->config->item('LBU_USER_TIME_ZONE') : $timezone;
        $changetime = new DateTime($date_time, new DateTimeZone('UTC'));
        $changetime->setTimezone(new DateTimeZone($timezone));
        return $changetime->format('Y-m-d H:i:s');
    }

    function changetimefromUserTime($date_time, $timezone = '') {
        $timezone = ($timezone == '') ? $this->CI->config->item('LBU_USER_TIME_ZONE') : $timezone;
        $changetime = new DateTime($date_time, new DateTimeZone($timezone));
        $changetime->setTimezone(new DateTimeZone('UTC'));
        return $changetime->format('Y-m-d H:i:s');
    }

    function getCurrentDateTime($format = '') {
        if ($format == '') {
            $format = $this->CI->config->item('display_date_time_format');
        }
        $date = $this->getDateTime(date('Y-m-d H:i:s'), $format);
        return $date;
    }

    function getTotalGroup() {
        $this->CI->load->model('group/model_group');
        $result = $this->CI->model_group->getTotalGroup();
        return $result[0]['tot'];
    }

    function getTotalLocations() {
        $this->CI->load->model('location/model_location');
        $result = $this->CI->model_location->getTotalLocations();
        return $result[0]['tot'];
    }

    function getVerification() {
        $this->CI->load->model('user/model_user');
        $result = $this->CI->model_user->getVerification();
        return $result;
    }

    function getCategories() {
        $this->CI->load->model('location/model_location');
        $ext_cond = array('iParentId' => '2', 'iParentId' => '3');
        $result = $this->CI->model_location->categoryList('', $ext_cond);
        return $result;
    }

    function getCountry() {
        $this->CI->load->model('user/model_user');
        $country = $this->CI->model_user->getCountryDetail();
        return $country;
    }

    function top_Category($city) {
        $this->CI->load->model('location/model_location');
        $result = $this->CI->model_location->top_Category($city);
        return $result;
    }

    function getTitle($obj = '') {
        if ($obj != '') {
            $this->CI->config->set_item('SITE_TITLE', $obj);
        } else {
            $this->CI->config->set_item('SITE_TITLE', $this->CI->config->item('SITE_TITLE'));
        }
    }

}
