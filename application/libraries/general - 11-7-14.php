<?php

(defined('BASEPATH')) OR exit('No direct script access allowed');

Class General {

    protected $CI;
    public $orderBook;

    function __construct() {
        $this->CI = & get_instance();
        $this->orderBook = array();
    }

    public static function encrypt_decrypt($action, $string) {
        $output = false;

        $key = 'Coi^@gent';

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

    function encrypt($sData, $sKey = 'Coi^@gent') {
        $sResult = '';
        for ($i = 0; $i < strlen($sData); $i++) {
            $sChar = substr($sData, $i, 1);
            $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1);
            $sChar = chr(ord($sChar) + ord($sKeyChar));
            $sResult .= $sChar;
        }
        return $this->encode_base64($sResult);
    }

    function decrypt($sData, $sKey = 'Coi^@gent') {
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

    function getDateTime($date, $format, $top = false) {
        if ($date != '0000-00-00' && $date != '0000-00-00 00:00:00' && $date != '') {
            return ($top) ? date($format, $date) : date($format, strtotime($date));
        } else {
            return '---';
        }
    }

    function DisplayTopInListAddDealer($TOP_HEADER, $BACK_LABEL = '', $BACK_LINK = '', $HEADING = '') {
        $admin_image_url = $this->CI->config->item('admin_images_url');

        $html = '<div class="screenTitle">
      		    <div align="left">&nbsp;' . $TOP_HEADER . '</div>';
        if ($BACK_LABEL != '')
            $html .= '<div align="right"><img src="' . $admin_image_url . 'icon/back-icon.gif" align="absmiddle">&nbsp;<a href="' . $BACK_LINK . '" class="backlisting-link">' . $BACK_LABEL . '</a>&nbsp;</div>';
        if ($HEADING != '')
            $html .= '<div align="right" width="5%">&nbsp;' . $HEADING . '</div>';
        $html .= '
		    </div>';
        return $html;
    }

    function redirect_ajax($url) {
        echo "<script>window.top.location.href = '" . $url . "';</script>";
        exit;
    }

    function getEncodedURL($data) {
        return $data;
    }

    /*     * ******************************************************* */

    function sendNotification($device_id, $iVPNId, $lang = 'en') {
        if ($iVPNId != '') {
            $notification_msg = 'NOTIFICATION_TEXT_' . strtoupper($lang);
            $notification_array['aps']['alert'] = $this->CI->systemsettings->getSettings($notification_msg);
            $notification_array['aps']['url'] = $this->CI->config->item('site_url') . 'download.html?d=' . base64_encode($iVPNId . '@@@' . $this->CI->systemsettings->getSettings('LINK_EXPIRE_PERIOD') . '@@@' . time());
            $this->pushNotification($device_id, $notification_array);
        }
    }

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

    function xml2array($respons, $get_attributes = 1, $priority = 'tag') {
        $doc = new DOMDocument();
        $doc->loadXML($respons);
        $contents = $doc->saveXML();

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
        $segmentval = array();
        $current = & $xml_array;
        $repeated_tag_index = array();
        $i = 0;
        $j = 0;
        $k = 0;
        $g = 0;
        $e = 0;
        $loop = 0;
        //pr($xml_values);exit;
        foreach ($xml_values as $data) {

            unset($attributes, $value);
            extract($data);
            //pr($data);exit;
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
                    if ($priority == 'tag') {
                        $attributes_data[$attr] = $val;
                    } else {
                        $result['attr'][$attr] = $val;
                    }
                    //Set all the attributes in a array called 'attr'
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
                // pr($current);exit;
                $current = & $parent[$level - 1];
            }
            
            /*********************************************
             * Code By : Ravi Patel (10-07-2014)
             * Code for getting flight base on search
             * *******************************************/    
            if ($xml_values[$i]['tag'] == 'air:AirSegment') {
                //pr($tag);
                // echo $j;
                //  pr($xml_values[$i]['tag']);exit;
                if (is_array($xml_values[$i]['attributes'])) {

                    if ($xml_values[$i + 1]['tag'] == 'air:AirAvailInfo') {
                        //pr($xml_values[$i+1]);
                        // pr($xml_values[$i+4]);exit;
                        $xml_values[$i]['attributes']['air:FlightDetailsRef'] = $xml_values[$i + 4]['attributes']['Key'];
                        $segmentval['val'][$j] = $xml_values[$i]['attributes'];
                        $segmentval['segmentarr'][$xml_values[$i]['attributes']['Key']] = $xml_values[$i]['attributes'];
                        $segmentval['avail'][$j] = $xml_values[$i + 1]['attributes'];
                        $segmentval['key'][$k] = $j;
                        $k++;
                    }
                    if (($xml_values[$i + 2]['tag'] == 'air:BookingCodeInfo') && ($xml_values[$i + 3]['tag'] == 'air:BookingCodeInfo')) {
                        // pr($xml_values[$i+2]);
                        $t = $i + 2;
                        while ($xml_values[$t]['tag'] == 'air:BookingCodeInfo') {
                            $segmentval['class'][$j].=$xml_values[$t]['attributes']['CabinClass'] . "-";
                            $t++;
                        }
                        // pr($segmentval['book']);
                        //$segmentval['book'][$j]=$xml_values[$i+2]['attributes']['CabinClass'];
                    }

                    $j++;
                }
            }else if ($xml_values[$i]['tag'] == 'air:FlightDetails') {
                $segmentval['FlightDetails'][$xml_values[$i]['attributes']['Key']] = $xml_values[$i]['attributes'];
            }else if (($xml_values[$i]['tag'] == 'air:BookingInfo') && ($xml_values[$i]['type'] == 'complete')) {
                $obj = $xml_values[$i]['attributes'];
                $segmentval['link'][$obj['SegmentRef']] = array('bookingcode' => $obj['BookingCode'], 'cabinclass' => $obj['CabinClass'], 'fareinforef' => $obj['FareInfoRef'], 'segmentref' => $obj['SegmentRef']);
                //pr($obj['fareinfo']);exit;
                $segmentval['link2'][$obj['FareInfoRef']] = array('bookingcode' => $obj['BookingCode'], 'cabinclass' => $obj['CabinClass'], 'fareinforef' => $obj['FareInfoRef'], 'segmentref' => $obj['SegmentRef']);
            }else if (($xml_values[$i]['tag'] == 'air:FareInfo') && ($xml_values[$i]['type'] == 'open')) {
                $obj = $xml_values[$i + 4]['attributes'];
                $cnt = $i;
                $state = 1;
                $farebase = $xml_values[$i]['attributes']['FareBasis'];
                while ($cnt != count($xml_values)) {
                    if ($farebase == $xml_values[($cnt)]['attributes']['FareBasis']) {
                        $farenextref = $xml_values[$cnt]['attributes']['Key'];
                        //  pr($farenextref);exit;
                        if ($cnt == $i) {
                            $state = 1;
                        } else {
                            break;
                        }
                    }
                    $cnt++;
                }
//                       if($xml_values[$i]['attributes']['FareBasis']==$xml_values[$i+6]['attributes']['FareBasis']){
//                           $farenextref=$xml_values[$i+6]['attributes']['Key'];
//                       }
                $segmentval['fareinfo'][
                    $xml_values[$i]['attributes']['Key']] = array('air:Fareinfo' => $xml_values[$i]['attributes'], 'air:FareRuleKey' => $xml_values[$i + 4]['value'], 'air:BaggageAllowance' => array($xml_values[$i + 2]), 'farenextkey' => $farenextref);
                //pr($segmentval['fareinfo']);
            }else if (($xml_values[$i]['tag'] == 'air:AirPricingInfo') && ($xml_values[$i]['type'] == 'open')) {
                // pr($xml_values[$i]['tag']);exit;
                $init = 0;
                $key = $xml_values[$i]['attributes']['Key'];
                $set = $i;
                $level = $xml_values[$set]['level'];
//                     / pr($level);
                while ($init < 1) {

                    //pr($xml_values[$set]['type']);exit;
                    $segmentval['alldata'][$key][$loop] = $xml_values[$set];
                    if ($xml_values[$set]['tag'] == 'air:FareCalc') {
                        $segmentval['FareCalc'][$key] = $xml_values[$set];
                    }
                    $set++;
                    $loop++;
                    if (($xml_values[$set]['level'] == '5') && ($xml_values[$i]['tag'] == 'air:AirPricingInfo')) {
                        $init++;
                        $loop = 0;
                    }
                    // $level=$xml_values[$set]['level'];
                }
              
                if (($xml_values[$i + 1]['tag'] == 'air:FareInfoRef') || ($xml_values[$i + 2]['tag'] == 'air:FareInfoRef')) {
                    $t = $i + 1;
                 
                    while ($xml_values[$t]['tag'] == 'air:FareInfoRef') {
                        $segmentval['pricefarekey'][$xml_values[$t]['attributes']['Key']] = $xml_values[$i]['attributes']['Key'];
                        $t++;
                    }
                    
                }

//                      /$segmentval['fareinfo'];exit;
//                      $cnt=0;
//                      while ($cnt!=2){
//                          $segmentval['pricesolution'][]=$xml_values[$i];
//                          if($xml_values[$i]['type']=='close'){
//                              $cnt++;
//                          }
//                      }
            }else if (($xml_values[$i]['tag'] == 'air:Journey') && ($xml_values[$i]['type'] == 'open')) {
                $segmentval['air:Journey'][$xml_values[$i + 1]['attributes']['Key']] = $xml_values[$i]['attributes'];
            }
            $i++;
        }
//       /  pr($xml_array);exit;
        if (!empty($segmentval)) {
            array_push($xml_array, $segmentval);
        }
      
        return $xml_array; //$segmentval;
    }

    function xml2array_old($contents, $get_attributes = 1, $priority = 'tag') {
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

    function sendSMSNexmo($to = '', $message = '') {
        // load library
        $this->load->library('nexmo');
        // set response format: xml or json, default json
        $this->nexmo->set_format('json');

        $from = '919033177107';
        $message_arr = array(
            'text' => $message
        );
        $response = $this->nexmo->send_message($from, $to, $message_arr);
        if ($response['messages'][0]['status'] == 0) {
            return true;
        } else {
            return false;
        }
    }

    function sendSMSClickatell($to = '', $message = '') {
        $this->load->library('clickatel');

        // you can send your custom message after buying the credits only
        $response = $this->clickatel->send_sms($to, $message);
        return $response;
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

    function getTopMenu($iParentId = '', $location = "Header") {
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $this->CI->db->select();
        $this->CI->db->where("l.location", $location);
        $this->CI->db->where("m.parent_id", "$iParentId");
        $this->CI->db->where("m.status", 'Active');
        $this->CI->db->from("menu_master AS m");
        $this->CI->db->join("location AS l", "l.id = m.location_id");
        $this->CI->db->join("page_master AS p", "p.page_id = m.page_id", 'left');
        $this->CI->db->order_by("order_by");
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
        $menu_data = $this->getTopMenu($iParentId, "Sidebar Login");
        return $menu_data;
    }

    function getFooterMenu($iParentId = '') {
        $iParentId = ($iParentId == '') ? 0 : $iParentId;
        $menu_data = $this->getTopMenu($iParentId, "Footer");
        return $menu_data;
    }

    function encryptData($input) {
        //$output = trim(base64_encode(base64_encode($input)),'==');
        //$output = $this->encrypt($input);
        $output = $this->encrypt_decrypt('encrypt', $input);
        return $output;
    }

    function decryptData($input) {
        //$output = base64_decode(base64_decode($input));
        //$output = $this->decrypt($input);
        $output = $this->encrypt_decrypt('decrypt', $input);
        return $output;
    }

    function checkSession() {
        if (!$this->CI->session->userdata('expire') || $this->CI->session->userdata('expire') > time()) {
            $this->CI->session->set_userdata('start', time());
            $expire = $this->CI->session->userdata('start') + 10 * 60;
            $this->CI->session->set_userdata('expire', $expire);
        } else if ($this->CI->session->userdata('expire') < time()) {
            $this->CI->session->unset_userdata('expire');
            $this->CI->session->set_flashdata('failure', 'Session time out..');
            redirect('logout.html');
        }
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
       
      /*  $respons = '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Header xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" ></s:Header>
  <SOAP:Body>
    <air:LowFareSearchRsp TransactionId="109D66C20A07643C0AD32D8FAC5EF348" ResponseTime="7099" DistanceUnits="MI" CurrencyType="GBP" xmlns:common_v17_0="http://www.travelport.com/schema/common_v17_0" xmlns:air="http://www.travelport.com/schema/air_v20_0">
      <common_v17_0:ResponseMessage Code="15017" Type="Error" ProviderCode="ACH"> [ACH] Provider error: No flight records found for this date or market</common_v17_0:ResponseMessage>
      <air:FlightDetailsList>
        <air:FlightDetails Key="2T" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T07:45:00.000+05:30" ArrivalTime="2014-07-09T08:55:00.000+05:30" FlightTime="70" Equipment="738" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="4T" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T18:55:00.000+05:30" ArrivalTime="2014-07-12T20:30:00.000+05:30" FlightTime="95" Equipment="738" OriginTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="20T" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T14:30:00.000+05:30" ArrivalTime="2014-07-09T16:00:00.000+05:30" FlightTime="90" Equipment="738" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="33T" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T12:35:00.000+05:30" ArrivalTime="2014-07-12T14:00:00.000+05:30" FlightTime="85" Equipment="738" OriginTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="100T" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T06:00:00.000+05:30" ArrivalTime="2014-07-12T07:25:00.000+05:30" FlightTime="85" Equipment="319" OriginTerminal="3" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="115T" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T18:10:00.000+05:30" ArrivalTime="2014-07-12T19:35:00.000+05:30" FlightTime="85" Equipment="321" OriginTerminal="3" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="126T" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T08:10:00.000+05:30" ArrivalTime="2014-07-09T09:35:00.000+05:30" FlightTime="85" Equipment="319" OriginTerminal="2" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="141T" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T20:15:00.000+05:30" ArrivalTime="2014-07-09T21:40:00.000+05:30" FlightTime="85" Equipment="321" OriginTerminal="2" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="152T" Origin="DEL" Destination="IDR" DepartureTime="2014-07-12T05:40:00.000+05:30" ArrivalTime="2014-07-12T07:45:00.000+05:30" FlightTime="125" Equipment="AT7" OriginTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="154T" Origin="IDR" Destination="BOM" DepartureTime="2014-07-12T20:05:00.000+05:30" ArrivalTime="2014-07-12T21:25:00.000+05:30" FlightTime="80" Equipment="320" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="156T" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T01:45:00.000+05:30" ArrivalTime="2014-07-13T02:50:00.000+05:30" FlightTime="65" Equipment="321" OriginTerminal="2" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="179T" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T05:30:00.000+05:30" ArrivalTime="2014-07-13T06:30:00.000+05:30" FlightTime="60" Equipment="320" OriginTerminal="2" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="192T" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T17:50:00.000+05:30" ArrivalTime="2014-07-13T19:00:00.000+05:30" FlightTime="70" Equipment="320" OriginTerminal="1A" DestinationTerminal="1" ></air:FlightDetails>
        <air:FlightDetails Key="205T" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T18:30:00.000+05:30" ArrivalTime="2014-07-13T19:50:00.000+05:30" FlightTime="80" Equipment="77W" OriginTerminal="2" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="218T" Origin="DEL" Destination="IDR" DepartureTime="2014-07-12T17:00:00.000+05:30" ArrivalTime="2014-07-12T19:00:00.000+05:30" FlightTime="120" Equipment="AT7" OriginTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="233T" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T21:45:00.000+05:30" ArrivalTime="2014-07-09T23:15:00.000+05:30" FlightTime="90" Equipment="77W" OriginTerminal="2" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="235T" Origin="BOM" Destination="IDR" DepartureTime="2014-07-10T06:00:00.000+05:30" ArrivalTime="2014-07-10T07:15:00.000+05:30" FlightTime="75" Equipment="320" OriginTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="237T" Origin="IDR" Destination="DEL" DepartureTime="2014-07-10T12:20:00.000+05:30" ArrivalTime="2014-07-10T14:20:00.000+05:30" FlightTime="120" Equipment="AT7" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="258T" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T20:00:00.000+05:30" ArrivalTime="2014-07-09T21:10:00.000+05:30" FlightTime="70" Equipment="320" OriginTerminal="2" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="271T" Origin="IDR" Destination="DEL" DepartureTime="2014-07-10T08:25:00.000+05:30" ArrivalTime="2014-07-10T10:00:00.000+05:30" FlightTime="95" Equipment="737" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="298T" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T07:15:00.000+05:30" ArrivalTime="2014-07-09T08:25:00.000+05:30" FlightTime="70" Equipment="320" OriginTerminal="1" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="311T" Origin="BOM" Destination="BHO" DepartureTime="2014-07-09T16:00:00.000+05:30" ArrivalTime="2014-07-09T17:25:00.000+05:30" FlightTime="85" Equipment="321" OriginTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="313T" Origin="BHO" Destination="DEL" DepartureTime="2014-07-09T20:55:00.000+05:30" ArrivalTime="2014-07-09T22:45:00.000+05:30" FlightTime="110" Equipment="AT7" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="329T" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T04:00:00.000+05:30" ArrivalTime="2014-07-09T05:05:00.000+05:30" FlightTime="65" Equipment="321" OriginTerminal="2" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="342T" Origin="DEL" Destination="BHO" DepartureTime="2014-07-12T06:20:00.000+05:30" ArrivalTime="2014-07-12T08:05:00.000+05:30" FlightTime="105" Equipment="AT7" OriginTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="344T" Origin="BHO" Destination="BOM" DepartureTime="2014-07-12T15:55:00.000+05:30" ArrivalTime="2014-07-12T17:20:00.000+05:30" FlightTime="85" Equipment="319" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="372T" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T21:00:00.000+05:30" ArrivalTime="2014-07-12T23:05:00.000+05:30" FlightTime="125" Equipment="321" OriginTerminal="3" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="389T" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T13:00:00.000+05:30" ArrivalTime="2014-07-12T15:00:00.000+05:30" FlightTime="120" Equipment="321" OriginTerminal="3" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="391T" Origin="BOM" Destination="AMD" DepartureTime="2014-07-12T17:50:00.000+05:30" ArrivalTime="2014-07-12T19:00:00.000+05:30" FlightTime="70" Equipment="320" OriginTerminal="1A" DestinationTerminal="1" ></air:FlightDetails>
        <air:FlightDetails Key="402T" Origin="BOM" Destination="AMD" DepartureTime="2014-07-12T18:30:00.000+05:30" ArrivalTime="2014-07-12T19:50:00.000+05:30" FlightTime="80" Equipment="77W" OriginTerminal="2" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="414T" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T20:00:00.000+05:30" ArrivalTime="2014-07-12T22:10:00.000+05:30" FlightTime="130" Equipment="321" OriginTerminal="3" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="426T" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T19:00:00.000+05:30" ArrivalTime="2014-07-12T21:05:00.000+05:30" FlightTime="125" Equipment="319" OriginTerminal="3" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="438T" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T08:00:00.000+05:30" ArrivalTime="2014-07-09T10:05:00.000+05:30" FlightTime="125" Equipment="321" OriginTerminal="1A" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="456T" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T13:00:00.000+05:30" ArrivalTime="2014-07-09T15:00:00.000+05:30" FlightTime="120" Equipment="321" OriginTerminal="1A" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="479T" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T17:00:00.000+05:30" ArrivalTime="2014-07-09T19:00:00.000+05:30" FlightTime="120" Equipment="321" OriginTerminal="1A" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="491T" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T19:00:00.000+05:30" ArrivalTime="2014-07-09T21:05:00.000+05:30" FlightTime="125" Equipment="321" OriginTerminal="1A" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="503T" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T20:00:00.000+05:30" ArrivalTime="2014-07-09T22:00:00.000+05:30" FlightTime="120" Equipment="319" OriginTerminal="2" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="611T" Origin="DEL" Destination="JAI" DepartureTime="2014-07-12T05:25:00.000+05:30" ArrivalTime="2014-07-12T06:20:00.000+05:30" FlightTime="55" Equipment="738" OriginTerminal="3" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="613T" Origin="JAI" Destination="BOM" DepartureTime="2014-07-12T13:20:00.000+05:30" ArrivalTime="2014-07-12T15:10:00.000+05:30" FlightTime="110" Equipment="319" OriginTerminal="2" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="810T" Origin="DEL" Destination="JAI" DepartureTime="2014-07-12T10:20:00.000+05:30" ArrivalTime="2014-07-12T11:30:00.000+05:30" FlightTime="70" Equipment="AT7" OriginTerminal="3" DestinationTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="921T" Origin="AMD" Destination="HYD" DepartureTime="2014-07-09T06:50:00.000+05:30" ArrivalTime="2014-07-09T08:30:00.000+05:30" FlightTime="100" Equipment="320" OriginTerminal="2" ></air:FlightDetails>
        <air:FlightDetails Key="923T" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T09:40:00.000+05:30" ArrivalTime="2014-07-09T11:50:00.000+05:30" FlightTime="130" Equipment="319" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="941T" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T16:15:00.000+05:30" ArrivalTime="2014-07-09T18:30:00.000+05:30" FlightTime="135" Equipment="321" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="953T" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T19:10:00.000+05:30" ArrivalTime="2014-07-09T21:20:00.000+05:30" FlightTime="130" Equipment="319" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="965T" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T20:55:00.000+05:30" ArrivalTime="2014-07-09T23:10:00.000+05:30" FlightTime="135" Equipment="77W" DestinationTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="1097T" Origin="DEL" Destination="NAG" DepartureTime="2014-07-12T05:45:00.000+05:30" ArrivalTime="2014-07-12T07:10:00.000+05:30" FlightTime="85" Equipment="319" OriginTerminal="3" ></air:FlightDetails>
        <air:FlightDetails Key="1099T" Origin="NAG" Destination="BOM" DepartureTime="2014-07-12T08:30:00.000+05:30" ArrivalTime="2014-07-12T09:50:00.000+05:30" FlightTime="80" Equipment="319" DestinationTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="1139T" Origin="BOM" Destination="NAG" DepartureTime="2014-07-09T18:40:00.000+05:30" ArrivalTime="2014-07-09T20:05:00.000+05:30" FlightTime="85" Equipment="319" OriginTerminal="1A" ></air:FlightDetails>
        <air:FlightDetails Key="1141T" Origin="NAG" Destination="DEL" DepartureTime="2014-07-10T07:40:00.000+05:30" ArrivalTime="2014-07-10T10:40:00.000+05:30" FlightTime="180" Equipment="319" DestinationTerminal="3" ></air:FlightDetails>
      </air:FlightDetailsList>
      <air:AirSegmentList>
        <air:AirSegment Key="1T" Group="0" Carrier="9W" FlightNumber="7079" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T07:45:00.000+05:30" ArrivalTime="2014-07-09T08:55:00.000+05:30" FlightTime="70" Distance="472" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo OperatingCarrier="S2" OperatingFlightNumber="4391">JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J7|Z7|I0|P0|Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="2T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="3T" Group="1" Carrier="9W" FlightNumber="7080" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T18:55:00.000+05:30" ArrivalTime="2014-07-12T20:30:00.000+05:30" FlightTime="95" Distance="472" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo OperatingCarrier="S2" OperatingFlightNumber="4392">JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J7|Z7|I7|P7|Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="4T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="19T" Group="0" Carrier="9W" FlightNumber="689" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T14:30:00.000+05:30" ArrivalTime="2014-07-09T16:00:00.000+05:30" FlightTime="90" Distance="472" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J7|Z7|I7|P0|Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="20T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="32T" Group="1" Carrier="9W" FlightNumber="688" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T12:35:00.000+05:30" ArrivalTime="2014-07-12T14:00:00.000+05:30" FlightTime="85" Distance="472" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J5|Z5|I5|P0|Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="33T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="55T" Group="1" Carrier="S2" FlightNumber="4392" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T18:55:00.000+05:30" ArrivalTime="2014-07-12T20:30:00.000+05:30" FlightTime="95" Distance="472" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J7|P7|W7|Y7|K7|T7|U7|S7|H7|E7|X7|N7|Q7|R0" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="4T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="67T" Group="0" Carrier="S2" FlightNumber="4391" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T07:45:00.000+05:30" ArrivalTime="2014-07-09T08:55:00.000+05:30" FlightTime="70" Distance="472" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J7|P0|W7|Y7|K7|T7|U7|S7|H7|E7|X7|N7|Q7|R0" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="2T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="99T" Group="1" Carrier="AI" FlightNumber="19" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T06:00:00.000+05:30" ArrivalTime="2014-07-12T07:25:00.000+05:30" FlightTime="85" Distance="472" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z3|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="100T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="114T" Group="1" Carrier="AI" FlightNumber="10" Origin="DEL" Destination="AMD" DepartureTime="2014-07-12T18:10:00.000+05:30" ArrivalTime="2014-07-12T19:35:00.000+05:30" FlightTime="85" Distance="472" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|E9|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="115T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="125T" Group="0" Carrier="AI" FlightNumber="18" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T08:10:00.000+05:30" ArrivalTime="2014-07-09T09:35:00.000+05:30" FlightTime="85" Distance="472" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z3|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="126T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="140T" Group="0" Carrier="AI" FlightNumber="11" Origin="AMD" Destination="DEL" DepartureTime="2014-07-09T20:15:00.000+05:30" ArrivalTime="2014-07-09T21:40:00.000+05:30" FlightTime="85" Distance="472" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|E9|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="141T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="151T" Group="1" Carrier="9W" FlightNumber="2337" Origin="DEL" Destination="IDR" DepartureTime="2014-07-12T05:40:00.000+05:30" ArrivalTime="2014-07-12T07:45:00.000+05:30" FlightTime="125" Distance="412" ETicketability="Yes" Equipment="AT7" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V7|O7|W7" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="152T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="153T" Group="1" Carrier="AI" FlightNumber="636" Origin="IDR" Destination="BOM" DepartureTime="2014-07-12T20:05:00.000+05:30" ArrivalTime="2014-07-12T21:25:00.000+05:30" FlightTime="80" Distance="315" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="154T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="155T" Group="1" Carrier="AI" FlightNumber="12" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T01:45:00.000+05:30" ArrivalTime="2014-07-13T02:50:00.000+05:30" FlightTime="65" Distance="276" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|E9|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="156T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="178T" Group="1" Carrier="AI" FlightNumber="130" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T05:30:00.000+05:30" ArrivalTime="2014-07-13T06:30:00.000+05:30" FlightTime="60" Distance="276" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C1|D1|JL|ZL|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|E9|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="179T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="191T" Group="1" Carrier="AI" FlightNumber="643" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T17:50:00.000+05:30" ArrivalTime="2014-07-13T19:00:00.000+05:30" FlightTime="70" Distance="276" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y2|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="192T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="204T" Group="1" Carrier="AI" FlightNumber="144" Origin="BOM" Destination="AMD" DepartureTime="2014-07-13T18:30:00.000+05:30" ArrivalTime="2014-07-13T19:50:00.000+05:30" FlightTime="80" Distance="276" ETicketability="Yes" Equipment="77W" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="F4|A4|P2|C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S7|E5|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="205T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="217T" Group="1" Carrier="9W" FlightNumber="2795" Origin="DEL" Destination="IDR" DepartureTime="2014-07-12T17:00:00.000+05:30" ArrivalTime="2014-07-12T19:00:00.000+05:30" FlightTime="120" Distance="412" ETicketability="Yes" Equipment="AT7" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V7|O0|W0" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="218T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="232T" Group="0" Carrier="AI" FlightNumber="191" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T21:45:00.000+05:30" ArrivalTime="2014-07-09T23:15:00.000+05:30" FlightTime="90" Distance="276" ETicketability="Yes" Equipment="77W" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="F4|A4|P2|C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S7|E5|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="233T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="234T" Group="0" Carrier="AI" FlightNumber="635" Origin="BOM" Destination="IDR" DepartureTime="2014-07-10T06:00:00.000+05:30" ArrivalTime="2014-07-10T07:15:00.000+05:30" FlightTime="75" Distance="315" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="235T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="236T" Group="0" Carrier="9W" FlightNumber="2254" Origin="IDR" Destination="DEL" DepartureTime="2014-07-10T12:20:00.000+05:30" ArrivalTime="2014-07-10T14:20:00.000+05:30" FlightTime="120" Distance="412" ETicketability="Yes" Equipment="AT7" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V7|O7|W7" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="237T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="257T" Group="0" Carrier="AI" FlightNumber="985" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T20:00:00.000+05:30" ArrivalTime="2014-07-09T21:10:00.000+05:30" FlightTime="70" Distance="276" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="258T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="270T" Group="0" Carrier="9W" FlightNumber="792" Origin="IDR" Destination="DEL" DepartureTime="2014-07-10T08:25:00.000+05:30" ArrivalTime="2014-07-10T10:00:00.000+05:30" FlightTime="95" Distance="412" ETicketability="Yes" Equipment="737" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J7|Z7|I7|P0|Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V7|O7|W6" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="271T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="297T" Group="0" Carrier="AI" FlightNumber="614" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T07:15:00.000+05:30" ArrivalTime="2014-07-09T08:25:00.000+05:30" FlightTime="70" Distance="276" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="298T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="310T" Group="0" Carrier="AI" FlightNumber="633" Origin="BOM" Destination="BHO" DepartureTime="2014-07-09T16:00:00.000+05:30" ArrivalTime="2014-07-09T17:25:00.000+05:30" FlightTime="85" Distance="409" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="311T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="312T" Group="0" Carrier="9W" FlightNumber="2653" Origin="BHO" Destination="DEL" DepartureTime="2014-07-09T20:55:00.000+05:30" ArrivalTime="2014-07-09T22:45:00.000+05:30" FlightTime="110" Distance="366" ETicketability="Yes" Equipment="AT7" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V7|O7|W5" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="313T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="328T" Group="0" Carrier="AI" FlightNumber="131" Origin="AMD" Destination="BOM" DepartureTime="2014-07-09T04:00:00.000+05:30" ArrivalTime="2014-07-09T05:05:00.000+05:30" FlightTime="65" Distance="276" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|E4|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="329T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="341T" Group="1" Carrier="9W" FlightNumber="2654" Origin="DEL" Destination="BHO" DepartureTime="2014-07-12T06:20:00.000+05:30" ArrivalTime="2014-07-12T08:05:00.000+05:30" FlightTime="105" Distance="366" ETicketability="Yes" Equipment="AT7" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V7|O7|W6" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="342T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="343T" Group="1" Carrier="AI" FlightNumber="634" Origin="BHO" Destination="BOM" DepartureTime="2014-07-12T15:55:00.000+05:30" ArrivalTime="2014-07-12T17:20:00.000+05:30" FlightTime="85" Distance="409" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J3|Z2|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="344T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="371T" Group="1" Carrier="AI" FlightNumber="602" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T21:00:00.000+05:30" ArrivalTime="2014-07-12T23:05:00.000+05:30" FlightTime="125" Distance="708" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|P4|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="372T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="388T" Group="1" Carrier="AI" FlightNumber="863" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T13:00:00.000+05:30" ArrivalTime="2014-07-12T15:00:00.000+05:30" FlightTime="120" Distance="708" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|E9|P4|N9" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="389T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="390T" Group="1" Carrier="AI" FlightNumber="643" Origin="BOM" Destination="AMD" DepartureTime="2014-07-12T17:50:00.000+05:30" ArrivalTime="2014-07-12T19:00:00.000+05:30" FlightTime="70" Distance="276" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y2|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="391T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="401T" Group="1" Carrier="AI" FlightNumber="144" Origin="BOM" Destination="AMD" DepartureTime="2014-07-12T18:30:00.000+05:30" ArrivalTime="2014-07-12T19:50:00.000+05:30" FlightTime="80" Distance="276" ETicketability="Yes" Equipment="77W" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="F4|A4|P2|C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S7|E5|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="402T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="413T" Group="1" Carrier="AI" FlightNumber="805" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T20:00:00.000+05:30" ArrivalTime="2014-07-12T22:10:00.000+05:30" FlightTime="130" Distance="708" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S5|EC|P4|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="414T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="425T" Group="1" Carrier="AI" FlightNumber="624" Origin="DEL" Destination="BOM" DepartureTime="2014-07-12T19:00:00.000+05:30" ArrivalTime="2014-07-12T21:05:00.000+05:30" FlightTime="125" Distance="708" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z2|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S6|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="426T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="437T" Group="0" Carrier="AI" FlightNumber="806" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T08:00:00.000+05:30" ArrivalTime="2014-07-09T10:05:00.000+05:30" FlightTime="125" Distance="708" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S7|EC|P4|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="438T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="455T" Group="0" Carrier="AI" FlightNumber="677" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T13:00:00.000+05:30" ArrivalTime="2014-07-09T15:00:00.000+05:30" FlightTime="120" Distance="708" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|ZC|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="456T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="478T" Group="0" Carrier="AI" FlightNumber="660" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T17:00:00.000+05:30" ArrivalTime="2014-07-09T19:00:00.000+05:30" FlightTime="120" Distance="708" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|P4|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="479T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="490T" Group="0" Carrier="AI" FlightNumber="888" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T19:00:00.000+05:30" ArrivalTime="2014-07-09T21:05:00.000+05:30" FlightTime="125" Distance="708" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="491T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="502T" Group="0" Carrier="AI" FlightNumber="314" Origin="BOM" Destination="DEL" DepartureTime="2014-07-09T20:00:00.000+05:30" ArrivalTime="2014-07-09T22:00:00.000+05:30" FlightTime="120" Distance="708" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="503T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="610T" Group="1" Carrier="9W" FlightNumber="2249" Origin="DEL" Destination="JAI" DepartureTime="2014-07-12T05:25:00.000+05:30" ArrivalTime="2014-07-12T06:20:00.000+05:30" FlightTime="55" Distance="145" ETicketability="Yes" Equipment="738" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C7|J7|Z7|I7|P5|Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V7|O7|W7" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="611T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="612T" Group="1" Carrier="AI" FlightNumber="612" Origin="JAI" Destination="BOM" DepartureTime="2014-07-12T13:20:00.000+05:30" ArrivalTime="2014-07-12T15:10:00.000+05:30" FlightTime="110" Distance="567" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z3|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="613T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="809T" Group="1" Carrier="9W" FlightNumber="2261" Origin="DEL" Destination="JAI" DepartureTime="2014-07-12T10:20:00.000+05:30" ArrivalTime="2014-07-12T11:30:00.000+05:30" FlightTime="70" Distance="145" ETicketability="Yes" Equipment="AT7" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:CodeshareInfo>JETKONNECT</air:CodeshareInfo>
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y7|M7|T7|U7|N7|L7|Q7|S7|K7|H7|V3|O3|W0" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="810T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="920T" Group="0" Carrier="AI" FlightNumber="982" Origin="AMD" Destination="HYD" DepartureTime="2014-07-09T06:50:00.000+05:30" ArrivalTime="2014-07-09T08:30:00.000+05:30" FlightTime="100" Distance="547" ETicketability="Yes" Equipment="320" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L1|U1|T1|S9|E9|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="921T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="922T" Group="0" Carrier="AI" FlightNumber="543" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T09:40:00.000+05:30" ArrivalTime="2014-07-09T11:50:00.000+05:30" FlightTime="130" Distance="781" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="923T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="940T" Group="0" Carrier="AI" FlightNumber="541" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T16:15:00.000+05:30" ArrivalTime="2014-07-09T18:30:00.000+05:30" FlightTime="135" Distance="781" ETicketability="Yes" Equipment="321" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z4|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S7|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="941T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="952T" Group="0" Carrier="AI" FlightNumber="840" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T19:10:00.000+05:30" ArrivalTime="2014-07-09T21:20:00.000+05:30" FlightTime="130" Distance="781" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z3|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|N2" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="953T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="964T" Group="0" Carrier="AI" FlightNumber="127" Origin="HYD" Destination="DEL" DepartureTime="2014-07-09T20:55:00.000+05:30" ArrivalTime="2014-07-09T23:10:00.000+05:30" FlightTime="135" Distance="781" ETicketability="Yes" Equipment="77W" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="F1|A1|C4|D4|J4|Z3|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|E2|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="965T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="1096T" Group="1" Carrier="AI" FlightNumber="469" Origin="DEL" Destination="NAG" DepartureTime="2014-07-12T05:45:00.000+05:30" ArrivalTime="2014-07-12T07:10:00.000+05:30" FlightTime="85" Distance="531" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z3|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="1097T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="1098T" Group="1" Carrier="AI" FlightNumber="628" Origin="NAG" Destination="BOM" DepartureTime="2014-07-12T08:30:00.000+05:30" ArrivalTime="2014-07-12T09:50:00.000+05:30" FlightTime="80" Distance="425" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|ZC|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="1099T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="1138T" Group="0" Carrier="AI" FlightNumber="629" Origin="BOM" Destination="NAG" DepartureTime="2014-07-09T18:40:00.000+05:30" ArrivalTime="2014-07-09T20:05:00.000+05:30" FlightTime="85" Distance="425" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="1139T" ></air:FlightDetailsRef>
        </air:AirSegment>
        <air:AirSegment Key="1140T" Group="0" Carrier="AI" FlightNumber="469" Origin="NAG" Destination="DEL" DepartureTime="2014-07-10T07:40:00.000+05:30" ArrivalTime="2014-07-10T10:40:00.000+05:30" FlightTime="180" Distance="531" ETicketability="Yes" Equipment="319" ChangeOfPlane="false" ParticipantLevel="Secure Sell" LinkAvailability="true" PolledAvailabilityOption="Polled avail used" OptionalServicesIndicator="false" NumberOfStops="1" AvailabilitySource="Seamless">
          <air:AirAvailInfo ProviderCode="1G">
            <air:BookingCodeInfo BookingCounts="C4|D4|J4|Z3|Y9|B9|M9|H9|K9|Q9|V9|W9|G9|L9|U9|T9|S9|EC|NC" ></air:BookingCodeInfo>
          </air:AirAvailInfo>
          <air:FlightDetailsRef Key="1141T" ></air:FlightDetailsRef>
        </air:AirSegment>
      </air:AirSegmentList>
      <air:FareInfoList>
        <air:FareInfo Key="14T" FareBasis="H2IPRS2" PassengerTypeCode="ADT" Origin="AMD" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR5635" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="14T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs17JIDMeZPpzfG/oTSMnCxukzyf2+pRvpyns5ozf4ir2/AL4aOHc/1lmJ7wShfEPzJwCzINtYhO9VwOe9rDJ4X9msDbCZMNcPb9tyHliUv0zeULS6L9mhF5wLT2Pt1/ElNkslb6lb1mpiztdLOMrJy+WSXy5veOSEzt5qAe7lMg7EAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2GYLD7+36jmUBgkKWq8uWT4QEJXTCeCNjHp9lEhU54VD9SRzkGGU6CYtuK/DjrhVesRF9XyNCsFufduDRM7xs9C</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="16T" FareBasis="H2IPRS2" PassengerTypeCode="ADT" Origin="DEL" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR5635" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="16T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs0StiwEZyaByfOpDFhu2nrmkzyf2+pRvpyns5ozf4ir2/AL4aOHc/1lwSJRM27zbwZwCzINtYhO9VwOe9rDJ4X9msDbCZMNcPb9tyHliUv0zeULS6L9mhF5wLT2Pt1/ElNkslb6lb1mpiztdLOMrJy+kkycBGLESY/t5qAe7lMg7EAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2GYLD7+36jmUBgkKWq8uWT4QEJXTCeCNjHp9lEhU54VD9SRzkGGU6CYtuK/DjrhVesRF9XyNCsFufduDRM7xs9C</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="28T" FareBasis="H2CRTIP" PassengerTypeCode="ADT" Origin="AMD" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR5985" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="28T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs17JIDMeZPpzea3yamOCrDqkzyf2+pRvpyns5ozf4ir2xmDdL28pQANmJ7wShfEPzJwCzINtYhO9VwOe9rDJ4X9msDbCZMNcPb9tyHliUv0zeULS6L9mhF5wLT2Pt1/ElNkslb6lb1mpiztdLOMrJy+XC4AXtmdonrALnXXKl5aNEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2GYLD7+36jmUBgkKWq8uWT4QEJXTCeCNjHp9lEhU54VD9SRzkGGU6CYtuK/DjrhVesRF9XyNCsFufduDRM7xs9C</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="41T" FareBasis="H2CRTIP" PassengerTypeCode="ADT" Origin="DEL" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR5985" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="41T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs0StiwEZyaByVHT/8yfQjFBkzyf2+pRvpyns5ozf4ir2xmDdL28pQANwSJRM27zbwZwCzINtYhO9VwOe9rDJ4X9msDbCZMNcPb9tyHliUv0zeULS6L9mhF5wLT2Pt1/ElNkslb6lb1mpiztdLOMrJy+8GtbIb+Gv3PALnXXKl5aNEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2GYLD7+36jmUBgkKWq8uWT4QEJXTCeCNjHp9lEhU54VD9SRzkGGU6CYtuK/DjrhVesRF9XyNCsFufduDRM7xs9C</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="63T" FareBasis="E2SIPRT" PassengerTypeCode="ADT" Origin="DEL" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR7170" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="63T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CPKWO7vwDzqRdRX4b5tNqs0StiwEZyaByQouVRrPAQdikzyf2+pRvpxua9vUMvwxKUP6egVVTjRTwSJRM27zbwZwCzINtYhO9VwOe9rDJ4X9msDbCZMNcPbjxDgVULo4Ld0TG75LcdeSwLT2Pt1/ElNkslb6lb1mpiztdLOMrJy+plRQr0rYzZSG4tWdjfXa2UAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2GYLD7+36jmUBgkKWq8uWT4QEJXTCeCNjHp9lEhU54VDwPh/fBX3DavtuK/DjrhVet63P1h5dRXe/duDRM7xs9C</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="75T" FareBasis="E2SIPRT" PassengerTypeCode="ADT" Origin="AMD" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR7170" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="75T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CPKWO7vwDzqRdRX4b5tNqs17JIDMeZPpzRu9+7M10AIhkzyf2+pRvpxua9vUMvwxKUP6egVVTjRTmJ7wShfEPzJwCzINtYhO9VwOe9rDJ4X9msDbCZMNcPbjxDgVULo4Ld0TG75LcdeSwLT2Pt1/ElNkslb6lb1mpiztdLOMrJy+tyz3q3axizqG4tWdjfXa2UAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2GYLD7+36jmUBgkKWq8uWT4QEJXTCeCNjHp9lEhU54VDwPh/fBX3DavtuK/DjrhVet63P1h5dRXe/duDRM7xs9C</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="108T" FareBasis="N2IP" PassengerTypeCode="ADT" Origin="AMD" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR9990" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:NumberOfPieces>2</air:NumberOfPieces>
            <air:MaxWeight ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="108T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs17JIDMeZPpzaj2FFmok4X3kzyf2+pRvpzzB3uEDEWhsFMFz4jLStekxJMlpiVkYoLcuRp7LwVfsyUcbA1fgxo1zook14Y2DG4PxE1w8OjpVkXO0D+KKrULXA572sMnhf0bLjzTCJJ00XcABNespdzbvs9xFBT/EYlrfxc7nYAShVJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0XoV4RSVaPuh+tdfE5N4XPyQxTHEUTsU3Wpn4Xhbd39RNSqEFjFFlcDC66bh7E2np57SEVj1FHXg==</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="110T" FareBasis="Y" PassengerTypeCode="ADT" Origin="DEL" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR12039" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="110T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs0StiwEZyaByU0qdIDWEkkvVMiCpCwFbLCp7HksMLaGOBQpx4gMHn+QNFaYV2NmuXKTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5Bl/BH4AWkCKTSp0gNYSSS+GfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="134T" FareBasis="Y" PassengerTypeCode="ADT" Origin="AMD" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR12039" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="134T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs17JIDMeZPpzU0qdIDWEkkvAO8WX7I3ycep7HksMLaGOBQpx4gMHn+QMAN0niqNWiuTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5ONflefIVZoFTSp0gNYSSS+GfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="137T" FareBasis="N2IP" PassengerTypeCode="ADT" Origin="DEL" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR9990" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:NumberOfPieces>2</air:NumberOfPieces>
            <air:MaxWeight ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="137T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs0StiwEZyaBydE3AYH1t7Olkzyf2+pRvpzzB3uEDEWhsFMFz4jLStekqv13acy+E0DcuRp7LwVfsyUcbA1fgxo1zook14Y2DG4PxE1w8OjpVkXO0D+KKrULXA572sMnhf0bLjzTCJJ00ShinkiCQvA2dLjIRUQJvD9rfxc7nYAShVJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0XoV4RSVaPuh+tdfE5N4XPyQxTHEUTsU3Wpn4Xhbd39RNSqEFjFFlcDC66bh7E2np57SEVj1FHXg==</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="164T" FareBasis="H2IP" PassengerTypeCode="ADT" Origin="AMD" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR6750" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="164T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs17JIDMeZPpzVcO43YyRiN0kzyf2+pRvpyns5ozf4ir21MFz4jLStekxJMlpiVkYoLcuRp7LwVfsyUcbA1fgxo1zook14Y2DG4PxE1w8OjpVkXO0D+KKrULXA572sMnhf0bLjzTCJJ00XcABNespdzbOcAHO7xTqBtrfxc7nYAShVJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0XoV4RSVaPuh+tdfE5N4XPyQxTHEUTsU3Wpn4Xhbd39RNSqEFjFFlcDC66bh7E2np57SEVj1FHXg==</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="166T" FareBasis="H2IPJK" PassengerTypeCode="ADT" Origin="DEL" Destination="IDR" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR4090" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="166T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs1DTPr59AMcyeyH/ez7lNyQkzyf2+pRvpyns5ozf4ir211T9IRbfZU4Lx17h5lGuz6fGWyakQEP0Iv2EJYMYS9AbXWJmqiHg4rWbwZG7U6DowMtNB4Cf3ZqATB4J6FJe1yC1h2th8PgjDlxXuj5pUzAkVdaY38UvV8hn/+ga4Qwx1JilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo1grVxzthPy4282Qa9UvUsn6NYeRRkrvx7XT3fcmPbGt0Fa6yAJ873ZRlGPWEvVX1NOPrq5B75RNdRl0ohIHLQj</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="168T" FareBasis="Y" PassengerTypeCode="ADT" Origin="IDR" Destination="BOM" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR9838" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="168T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs30opIfPs5bPTDQqiEySGZXkzyf2+pRvpyR7vre3X6zfP1rcWGZHIG0eDr60OJc1OtF9d5THFiohCUcbA1fgxo1/V/pT9sS9whFXJCu9CBP1Z+z9hRO8bMZbgIKgu466RAGYcTny5etdkdkm3vjh8XkkGvDw6Mvh3bjzYaz4kLfxUAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hsca8v4TAgOobePZJRUNtNtPUw1y4rJ7K/QnygtTyJ+XYEcaew1max3SB8vEqBtHhpvl5itD/1mlAYe/MKKSunQ==</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="170T" FareBasis="Y" PassengerTypeCode="ADT" Origin="BOM" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-13" Amount="INR10946" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-13" NotValidAfter="2014-07-13">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="170T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs0StiwEZyaByV5mTq3wMFYSrCNgIwD2KFmp7HksMLaGOBQpx4gMHn+Q0idjQEuroPeTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5K+Fjj1QtQitXmZOrfAwVhKGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="226T" FareBasis="H2IPJK" PassengerTypeCode="ADT" Origin="DEL" Destination="IDR" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR4650" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="226T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs1DTPr59AMcyWijEVovaphRkzyf2+pRvpyns5ozf4ir211T9IRbfZU4Lx17h5lGuz6fGWyakQEP0Iv2EJYMYS9AbXWJmqiHg4rWbwZG7U6DowMtNB4Cf3ZqATB4J6FJe1yC1h2th8PgjDlxXuj5pUzAPg39uTpgL98hn/+ga4Qwx1JilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo1grVxzthPy4282Qa9UvUsn6NYeRRkrvx7XT3fcmPbGt0Fa6yAJ873Zh1d4e0J/yT1OPrq5B75RNdRl0ohIHLQj</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="245T" FareBasis="Y" PassengerTypeCode="ADT" Origin="AMD" Destination="BOM" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR10946" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="245T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs30opIfPs5bPV5mTq3wMFYSAO8WX7I3ycep7HksMLaGOBQpx4gMHn+QMAN0niqNWiuTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5ONflefIVZoFXmZOrfAwVhKGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="250T" FareBasis="Y" PassengerTypeCode="ADT" Origin="BOM" Destination="IDR" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-10" Amount="INR9838" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-10" NotValidAfter="2014-07-10">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="250T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs1DTPr59AMcyVOW1Y2qX3Qzkzyf2+pRvpyR7vre3X6zfP1rcWGZHIG03s9ZkiD6k6BF9d5THFiohCUcbA1fgxo1/V/pT9sS9whFXJCu9CBP1Z+z9hRO8bMZbgIKgu466RAGYcTny5etdhtSmFfmwX1XkGvDw6Mvh3bjzYaz4kLfxUAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hsca8v4TAgOobePZJRUNtNtPUw1y4rJ7K/QnygtTyJ+XYEcaew1max3SB8vEqBtHhpvl5itD/1mlAYe/MKKSunQ==</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="252T" FareBasis="H2IPJK" PassengerTypeCode="ADT" Origin="IDR" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-10" Amount="INR4650" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-10" NotValidAfter="2014-07-10">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="252T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs17JIDMeZPpza4ClB5smeXvkzyf2+pRvpyns5ozf4ir211T9IRbfZU4sB431K2o1TGfGWyakQEP0Iv2EJYMYS9AbXWJmqiHg4rWbwZG7U6DowMtNB4Cf3ZqATB4J6FJe1yC1h2th8PgjDlxXuj5pUzAZNPzohGiUichn/+ga4Qwx1JilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo1grVxzthPy4282Qa9UvUsn6NYeRRkrvx7XT3fcmPbGt0Fa6yAJ873Zh1d4e0J/yT1OPrq5B75RNdRl0ohIHLQj</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="254T" FareBasis="H2IP" PassengerTypeCode="ADT" Origin="DEL" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR6750" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="254T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs0StiwEZyaByZK7eiNFptHRkzyf2+pRvpyns5ozf4ir21MFz4jLStekqv13acy+E0DcuRp7LwVfsyUcbA1fgxo1zook14Y2DG4PxE1w8OjpVkXO0D+KKrULXA572sMnhf0bLjzTCJJ00ShinkiCQvA2fUNp6HaH9dxrfxc7nYAShVJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0XoV4RSVaPuh+tdfE5N4XPyQxTHEUTsU3Wpn4Xhbd39RNSqEFjFFlcDC66bh7E2np57SEVj1FHXg==</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="279T" FareBasis="H2IP" PassengerTypeCode="ADT" Origin="IDR" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-10" Amount="INR4790" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-10" NotValidAfter="2014-07-10">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="279T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs17JIDMeZPpzUnvOdmNFE/5kzyf2+pRvpyns5ozf4ir21MFz4jLStekTIdjn8imXtLcuRp7LwVfsyUcbA1fgxo1zook14Y2DG4PxE1w8OjpVkXO0D+KKrULXA572sMnhf0bLjzTCJJ00W6fBqjQmCcd7Kvd0nnM04drfxc7nYAShVJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0XoV4RSVaPuh+tdfE5N4XPyQxTHEUTsU3Wpn4Xhbd39RNSqEFjFFlcDC66bh7E2np57SEVj1FHXg==</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="321T" FareBasis="Y" PassengerTypeCode="ADT" Origin="BOM" Destination="BHO" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR12230" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="321T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs2Xih1J+Roqz2qxNdjr2dnpVMiCpCwFbLCp7HksMLaGOBQpx4gMHn+QMAN0niqNWiuTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5K+Fjj1QtQitarE12OvZ2emGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="323T" FareBasis="H2IPJK" PassengerTypeCode="ADT" Origin="BHO" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR5230" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="323T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs17JIDMeZPpzQTBiQ+mse4Zkzyf2+pRvpyns5ozf4ir211T9IRbfZU4Oa/IxB3QaTefGWyakQEP0Iv2EJYMYS9AbXWJmqiHg4rWbwZG7U6DowMtNB4Cf3ZqATB4J6FJe1yC1h2th8PgjDlxXuj5pUzA/14Iq6a2I+8hn/+ga4Qwx1JilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo1grVxzthPy4282Qa9UvUsn6NYeRRkrvx7XT3fcmPbGt0Fa6yAJ873Z0VLeehQLncxOPrq5B75RNdRl0ohIHLQj</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="352T" FareBasis="H2IPJK" PassengerTypeCode="ADT" Origin="DEL" Destination="BHO" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR5430" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="352T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs2Xih1J+Roqz0Zh5wgFrDhmkzyf2+pRvpyns5ozf4ir211T9IRbfZU4Lx17h5lGuz6fGWyakQEP0Iv2EJYMYS9AbXWJmqiHg4rWbwZG7U6DowMtNB4Cf3ZqATB4J6FJe1yC1h2th8PgjDlxXuj5pUzA3qvHm2TK61Qhn/+ga4Qwx1JilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo1grVxzthPy4282Qa9UvUsn6NYeRRkrvx7XT3fcmPbGt0Fa6yAJ873ZtVz548MJQYJOPrq5B75RNdRl0ohIHLQj</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="354T" FareBasis="Y" PassengerTypeCode="ADT" Origin="BHO" Destination="BOM" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR12230" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="354T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs30opIfPs5bPWqxNdjr2dnpv9oDTCdH3+Gp7HksMLaGOBQpx4gMHn+QNFaYV2NmuXKTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5KNbmCQmlJh4arE12OvZ2emGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="380T" FareBasis="Y" PassengerTypeCode="ADT" Origin="DEL" Destination="BOM" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR18336" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="380T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs30opIfPs5bPSEMob6J+gGfVMiCpCwFbLCp7HksMLaGOBQpx4gMHn+QNFaYV2NmuXKTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5Bl/BH4AWkCKIQyhvon6AZ+GfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="446T" FareBasis="Y" PassengerTypeCode="ADT" Origin="BOM" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR18336" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="446T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs17JIDMeZPpzSEMob6J+gGfVMiCpCwFbLCp7HksMLaGOBQpx4gMHn+QMAN0niqNWiuTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5K+Fjj1QtQitIQyhvon6AZ+GfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="621T" FareBasis="H2IPJK" PassengerTypeCode="ADT" Origin="DEL" Destination="JAI" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR4090" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="621T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs3vE4wHbhFdCuyH/ez7lNyQkzyf2+pRvpyns5ozf4ir211T9IRbfZU4Lx17h5lGuz6fGWyakQEP0Iv2EJYMYS9AbXWJmqiHg4rWbwZG7U6DowMtNB4Cf3ZqATB4J6FJe1yC1h2th8PgjDlxXuj5pUzAkVdaY38UvV8hn/+ga4Qwx1JilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo1grVxzthPy4282Qa9UvUsn6NYeRRkrvx7XT3fcmPbGt0Fa6yAJ873ZRlGPWEvVX1NOPrq5B75RNdRl0ohIHLQj</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="623T" FareBasis="Y" PassengerTypeCode="ADT" Origin="JAI" Destination="BOM" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR15799" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="623T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs30opIfPs5bPTmfUbumwXqov9oDTCdH3+Gp7HksMLaGOBQpx4gMHn+QNFaYV2NmuXKTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5LdIlrUGSyYtOZ9Ru6bBeqiGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="625T" FareBasis="Y" PassengerTypeCode="ADT" Origin="BOM" Destination="AMD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR10946" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="625T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs0StiwEZyaByV5mTq3wMFYSrCNgIwD2KFmp7HksMLaGOBQpx4gMHn+QNFaYV2NmuXKTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5K+Fjj1QtQitXmZOrfAwVhKGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="818T" FareBasis="H2IPJK" PassengerTypeCode="ADT" Origin="DEL" Destination="JAI" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR4650" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="15" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="818T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CEdwFjOgtL/GdRX4b5tNqs3vE4wHbhFdCmijEVovaphRkzyf2+pRvpyns5ozf4ir211T9IRbfZU4Lx17h5lGuz6fGWyakQEP0Iv2EJYMYS9AbXWJmqiHg4rWbwZG7U6DowMtNB4Cf3ZqATB4J6FJe1yC1h2th8PgjDlxXuj5pUzAPg39uTpgL98hn/+ga4Qwx1JilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo1grVxzthPy4282Qa9UvUsn6NYeRRkrvx7XT3fcmPbGt0Fa6yAJ873Zh1d4e0J/yT1OPrq5B75RNdRl0ohIHLQj</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="931T" FareBasis="Y" PassengerTypeCode="ADT" Origin="AMD" Destination="HYD" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR16832" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="931T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs1pLq/uVFbtryPH0dT0t5q8AO8WX7I3ycep7HksMLaGOBQpx4gMHn+QMAN0niqNWiuTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5ONflefIVZoFI8fR1PS3mryGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="933T" FareBasis="Y" PassengerTypeCode="ADT" Origin="HYD" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR17921" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="933T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs17JIDMeZPpzRtTLoElMrKEVMiCpCwFbLCp7HksMLaGOBQpx4gMHn+QMAN0niqNWiuTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5DllOPUBfTPlG1MugSUysoSGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="1107T" FareBasis="Y" PassengerTypeCode="ADT" Origin="DEL" Destination="NAG" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR16100" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="1107T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs0DpoksTS1FtZ1ioEx8GDYyVMiCpCwFbLCp7HksMLaGOBQpx4gMHn+QNFaYV2NmuXKTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5Bl/BH4AWkCKnWKgTHwYNjKGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="1109T" FareBasis="Y" PassengerTypeCode="ADT" Origin="NAG" Destination="BOM" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-12" Amount="INR12415" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-12" NotValidAfter="2014-07-12">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="1109T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs30opIfPs5bPSrdxKm2Usrpv9oDTCdH3+Gp7HksMLaGOBQpx4gMHn+QNFaYV2NmuXKTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5KjlBMvb64A+Kt3EqbZSyumGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="1149T" FareBasis="Y" PassengerTypeCode="ADT" Origin="BOM" Destination="NAG" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-09" Amount="INR12415" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-09" NotValidAfter="2014-07-09">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="1149T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs0DpoksTS1FtSrdxKm2UsrpVMiCpCwFbLCp7HksMLaGOBQpx4gMHn+QMAN0niqNWiuTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5K+Fjj1QtQitKt3EqbZSyumGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
        <air:FareInfo Key="1151T" FareBasis="Y" PassengerTypeCode="ADT" Origin="NAG" Destination="DEL" EffectiveDate="2014-07-07T12:37:00.000+01:00" DepartureDate="2014-07-10" Amount="INR16100" PrivateFare="false" NegotiatedFare="false" NotValidBefore="2014-07-10" NotValidAfter="2014-07-10">
          <air:BaggageAllowance>
            <air:MaxWeight Value="20" Unit="Kilograms" ></air:MaxWeight>
          </air:BaggageAllowance>
          <air:FareRuleKey FareInfoRef="1151T" ProviderCode="1G">Sc7S6Dprj1tvICGiajJE9VJilViIlUaNUmKVWIiVRo1SYpVYiJVGjVJilViIlUaNUmKVWIiVRo0Y7R9JFxW8CMtNz0xCOr6sdRX4b5tNqs17JIDMeZPpzZ1ioEx8GDYyv9oDTCdH3+Gp7HksMLaGOBQpx4gMHn+QZulYIyztTbyTPJ/b6lG+nJ8ZbJqRAQ/Qiig6wb0RG+fWtqXuP+jznQyvWfzQDg8dr2sd4BVTpV6YDFVttl+Z5KjlBMvb64A+nWKgTHwYNjKGfD7Un4r4QEAgfxiFqv9hQCB/GIWq/2FAIH8Yhar/YUAgfxiFqv9hQCB/GIWq/2Fcxt8mIOH+sFJilViIlUaN1ZtsCeyYNU25mvns5tQIf3gnqtjX9DGtUFrJD1t/ksg8Itsp1k+pAhP0UsjmcQ3+</air:FareRuleKey>
        </air:FareInfo>
      </air:FareInfoList>
      <air:RouteList>
        <air:Route Key="2487T">
          <air:Leg Key="5T" Group="0" Origin="AMD" Destination="DEL" ></air:Leg>
          <air:Leg Key="6T" Group="1" Origin="DEL" Destination="AMD" ></air:Leg>
        </air:Route>
      </air:RouteList>
      <air:AirPricingSolution Key="0T" TotalPrice="GBP180.90" BasePrice="INR11270" ApproximateTotalPrice="GBP180.90" ApproximateBasePrice="GBP113.00" EquivalentBasePrice="GBP113.00" Taxes="GBP67.90">
        <air:Journey TravelTime="P0DT1H10M0S">
          <air:AirSegmentRef Key="1T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H35M0S">
          <air:AirSegmentRef Key="3T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="7T" TotalPrice="GBP180.90" BasePrice="INR11270" ApproximateTotalPrice="GBP180.90" ApproximateBasePrice="GBP113.00" EquivalentBasePrice="GBP113.00" Taxes="GBP67.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="14T" ></air:FareInfoRef>
          <air:FareInfoRef Key="16T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="14T" SegmentRef="1T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="16T" SegmentRef="3T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP17.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 5635H2IPRS2 9W AMD 5635H2IPRS2 INR11270END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="18T" TotalPrice="GBP185.40" BasePrice="INR11620" ApproximateTotalPrice="GBP185.40" ApproximateBasePrice="GBP117.00" EquivalentBasePrice="GBP117.00" Taxes="GBP68.40">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H35M0S">
          <air:AirSegmentRef Key="3T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="21T" TotalPrice="GBP185.40" BasePrice="INR11620" ApproximateTotalPrice="GBP185.40" ApproximateBasePrice="GBP117.00" EquivalentBasePrice="GBP117.00" Taxes="GBP68.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="28T" ></air:FareInfoRef>
          <air:FareInfoRef Key="16T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="28T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="16T" SegmentRef="3T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 5985H2CRTIP 9W AMD 5635H2IPRS2 INR11620END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="31T" TotalPrice="GBP185.40" BasePrice="INR11620" ApproximateTotalPrice="GBP185.40" ApproximateBasePrice="GBP117.00" EquivalentBasePrice="GBP117.00" Taxes="GBP68.40">
        <air:Journey TravelTime="P0DT1H10M0S">
          <air:AirSegmentRef Key="1T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="34T" TotalPrice="GBP185.40" BasePrice="INR11620" ApproximateTotalPrice="GBP185.40" ApproximateBasePrice="GBP117.00" EquivalentBasePrice="GBP117.00" Taxes="GBP68.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="14T" ></air:FareInfoRef>
          <air:FareInfoRef Key="41T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="14T" SegmentRef="1T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="41T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 5635H2IPRS2 9W AMD 5985H2CRTIP INR11620END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="44T" TotalPrice="GBP189.90" BasePrice="INR11970" ApproximateTotalPrice="GBP189.90" ApproximateBasePrice="GBP121.00" EquivalentBasePrice="GBP121.00" Taxes="GBP68.90">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="45T" TotalPrice="GBP189.90" BasePrice="INR11970" ApproximateTotalPrice="GBP189.90" ApproximateBasePrice="GBP121.00" EquivalentBasePrice="GBP121.00" Taxes="GBP68.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="28T" ></air:FareInfoRef>
          <air:FareInfoRef Key="41T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="28T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="41T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP18.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 5985H2CRTIP 9W AMD 5985H2CRTIP INR11970END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="54T" TotalPrice="GBP198.90" BasePrice="INR12805" ApproximateTotalPrice="GBP198.90" ApproximateBasePrice="GBP129.00" EquivalentBasePrice="GBP129.00" Taxes="GBP69.90">
        <air:Journey TravelTime="P0DT1H10M0S">
          <air:AirSegmentRef Key="1T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H35M0S">
          <air:AirSegmentRef Key="55T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="56T" TotalPrice="GBP198.90" BasePrice="INR12805" ApproximateTotalPrice="GBP198.90" ApproximateBasePrice="GBP129.00" EquivalentBasePrice="GBP129.00" Taxes="GBP69.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="14T" ></air:FareInfoRef>
          <air:FareInfoRef Key="63T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="14T" SegmentRef="1T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="E" CabinClass="Economy" FareInfoRef="63T" SegmentRef="55T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP19.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 5635H2IPRS2 S2 AMD 7170E2SIPRT INR12805END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="66T" TotalPrice="GBP198.90" BasePrice="INR12805" ApproximateTotalPrice="GBP198.90" ApproximateBasePrice="GBP129.00" EquivalentBasePrice="GBP129.00" Taxes="GBP69.90">
        <air:Journey TravelTime="P0DT1H10M0S">
          <air:AirSegmentRef Key="67T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H35M0S">
          <air:AirSegmentRef Key="3T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="68T" TotalPrice="GBP198.90" BasePrice="INR12805" ApproximateTotalPrice="GBP198.90" ApproximateBasePrice="GBP129.00" EquivalentBasePrice="GBP129.00" Taxes="GBP69.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="75T" ></air:FareInfoRef>
          <air:FareInfoRef Key="16T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="E" CabinClass="Economy" FareInfoRef="75T" SegmentRef="67T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="16T" SegmentRef="3T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP19.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD S2 DEL 7170E2SIPRT 9W AMD 5635H2IPRS2 INR12805END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="78T" TotalPrice="GBP202.20" BasePrice="INR13155" ApproximateTotalPrice="GBP202.20" ApproximateBasePrice="GBP132.00" EquivalentBasePrice="GBP132.00" Taxes="GBP70.20">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H35M0S">
          <air:AirSegmentRef Key="55T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="79T" TotalPrice="GBP202.20" BasePrice="INR13155" ApproximateTotalPrice="GBP202.20" ApproximateBasePrice="GBP132.00" EquivalentBasePrice="GBP132.00" Taxes="GBP70.20" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="28T" ></air:FareInfoRef>
          <air:FareInfoRef Key="63T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="28T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="E" CabinClass="Economy" FareInfoRef="63T" SegmentRef="55T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP20.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 5985H2CRTIP S2 AMD 7170E2SIPRT INR13155END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="88T" TotalPrice="GBP202.20" BasePrice="INR13155" ApproximateTotalPrice="GBP202.20" ApproximateBasePrice="GBP132.00" EquivalentBasePrice="GBP132.00" Taxes="GBP70.20">
        <air:Journey TravelTime="P0DT1H10M0S">
          <air:AirSegmentRef Key="67T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="89T" TotalPrice="GBP202.20" BasePrice="INR13155" ApproximateTotalPrice="GBP202.20" ApproximateBasePrice="GBP132.00" EquivalentBasePrice="GBP132.00" Taxes="GBP70.20" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="75T" ></air:FareInfoRef>
          <air:FareInfoRef Key="41T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="E" CabinClass="Economy" FareInfoRef="75T" SegmentRef="67T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="41T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP20.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP18.20" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD S2 DEL 7170E2SIPRT 9W AMD 5985H2CRTIP INR13155END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="98T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="101T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="108T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="N" CabinClass="Economy" FareInfoRef="108T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP32.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP30.70" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 9990N2IP AI AMD 12039Y INR22029END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="113T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="116T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="108T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="N" CabinClass="Economy" FareInfoRef="108T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP32.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP30.70" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 9990N2IP AI AMD 12039Y INR22029END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="124T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="127T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="137T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="N" CabinClass="Economy" FareInfoRef="137T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP32.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP30.70" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W AMD 9990N2IP INR22029END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="139T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="142T" TotalPrice="GBP310.70" BasePrice="INR22029" ApproximateTotalPrice="GBP310.70" ApproximateBasePrice="GBP222.00" EquivalentBasePrice="GBP222.00" Taxes="GBP88.70" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="137T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="N" CabinClass="Economy" FareInfoRef="137T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP32.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP30.70" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W AMD 9990N2IP INR22029END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="150T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="157T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP47.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR31624END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="177T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H50M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="180T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP47.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR31624END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="190T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT13H20M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="191T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="193T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="191T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP47.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR31624END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="203T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT14H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="204T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="206T" TotalPrice="GBP449.60" BasePrice="INR31624" ApproximateTotalPrice="GBP449.60" ApproximateBasePrice="GBP318.00" EquivalentBasePrice="GBP318.00" Taxes="GBP131.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="204T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP47.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR31624END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="216T" TotalPrice="GBP456.30" BasePrice="INR32184" ApproximateTotalPrice="GBP456.30" ApproximateBasePrice="GBP324.00" EquivalentBasePrice="GBP324.00" Taxes="GBP132.30">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT9H50M0S">
          <air:AirSegmentRef Key="217T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="219T" TotalPrice="GBP456.30" BasePrice="INR32184" ApproximateTotalPrice="GBP456.30" ApproximateBasePrice="GBP324.00" EquivalentBasePrice="GBP324.00" Taxes="GBP132.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="226T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="226T" SegmentRef="217T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP48.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W IDR 4650H2IPJK AI BOM 9838Y AI AMD 10946Y INR32184END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="231T" TotalPrice="GBP456.30" BasePrice="INR32184" ApproximateTotalPrice="GBP456.30" ApproximateBasePrice="GBP324.00" EquivalentBasePrice="GBP324.00" Taxes="GBP132.30">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="238T" TotalPrice="GBP456.30" BasePrice="INR32184" ApproximateTotalPrice="GBP456.30" ApproximateBasePrice="GBP324.00" EquivalentBasePrice="GBP324.00" Taxes="GBP132.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP48.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W AMD 6750H2IP INR32184END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="256T" TotalPrice="GBP456.30" BasePrice="INR32184" ApproximateTotalPrice="GBP456.30" ApproximateBasePrice="GBP324.00" EquivalentBasePrice="GBP324.00" Taxes="GBP132.30">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="259T" TotalPrice="GBP456.30" BasePrice="INR32184" ApproximateTotalPrice="GBP456.30" ApproximateBasePrice="GBP324.00" EquivalentBasePrice="GBP324.00" Taxes="GBP132.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP48.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W AMD 6750H2IP INR32184END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="269T" TotalPrice="GBP457.40" BasePrice="INR32324" ApproximateTotalPrice="GBP457.40" ApproximateBasePrice="GBP325.00" EquivalentBasePrice="GBP325.00" Taxes="GBP132.40">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="272T" TotalPrice="GBP457.40" BasePrice="INR32324" ApproximateTotalPrice="GBP457.40" ApproximateBasePrice="GBP325.00" EquivalentBasePrice="GBP325.00" Taxes="GBP132.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP48.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W AMD 6750H2IP INR32324END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="284T" TotalPrice="GBP457.40" BasePrice="INR32324" ApproximateTotalPrice="GBP457.40" ApproximateBasePrice="GBP325.00" EquivalentBasePrice="GBP325.00" Taxes="GBP132.40">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="285T" TotalPrice="GBP457.40" BasePrice="INR32324" ApproximateTotalPrice="GBP457.40" ApproximateBasePrice="GBP325.00" EquivalentBasePrice="GBP325.00" Taxes="GBP132.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP48.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W AMD 6750H2IP INR32324END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="296T" TotalPrice="GBP457.40" BasePrice="INR32324" ApproximateTotalPrice="GBP457.40" ApproximateBasePrice="GBP325.00" EquivalentBasePrice="GBP325.00" Taxes="GBP132.40">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="299T" TotalPrice="GBP457.40" BasePrice="INR32324" ApproximateTotalPrice="GBP457.40" ApproximateBasePrice="GBP325.00" EquivalentBasePrice="GBP325.00" Taxes="GBP132.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP48.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W AMD 6750H2IP INR32324END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="309T" TotalPrice="GBP490.00" BasePrice="INR35156" ApproximateTotalPrice="GBP490.00" ApproximateBasePrice="GBP354.00" EquivalentBasePrice="GBP354.00" Taxes="GBP136.00">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="314T" TotalPrice="GBP490.00" BasePrice="INR35156" ApproximateTotalPrice="GBP490.00" ApproximateBasePrice="GBP354.00" EquivalentBasePrice="GBP354.00" Taxes="GBP136.00" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP51.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W AMD 6750H2IP INR35156END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="327T" TotalPrice="GBP490.00" BasePrice="INR35156" ApproximateTotalPrice="GBP490.00" ApproximateBasePrice="GBP354.00" EquivalentBasePrice="GBP354.00" Taxes="GBP136.00">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="330T" TotalPrice="GBP490.00" BasePrice="INR35156" ApproximateTotalPrice="GBP490.00" ApproximateBasePrice="GBP354.00" EquivalentBasePrice="GBP354.00" Taxes="GBP136.00" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP51.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W AMD 6750H2IP INR35156END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="340T" TotalPrice="GBP492.20" BasePrice="INR35356" ApproximateTotalPrice="GBP492.20" ApproximateBasePrice="GBP356.00" EquivalentBasePrice="GBP356.00" Taxes="GBP136.20">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="345T" TotalPrice="GBP492.20" BasePrice="INR35356" ApproximateTotalPrice="GBP492.20" ApproximateBasePrice="GBP356.00" EquivalentBasePrice="GBP356.00" Taxes="GBP136.20" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP51.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR35356END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="358T" TotalPrice="GBP492.20" BasePrice="INR35356" ApproximateTotalPrice="GBP492.20" ApproximateBasePrice="GBP356.00" EquivalentBasePrice="GBP356.00" Taxes="GBP136.20">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="359T" TotalPrice="GBP492.20" BasePrice="INR35356" ApproximateTotalPrice="GBP492.20" ApproximateBasePrice="GBP356.00" EquivalentBasePrice="GBP356.00" Taxes="GBP136.20" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP51.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP52.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR35356END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="370T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT5H50M0S">
          <air:AirSegmentRef Key="371T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="373T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="371T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI BOM 18336Y AI AMD 10946Y INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="387T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H0M0S">
          <air:AirSegmentRef Key="388T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="392T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="388T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI BOM 18336Y AI AMD 10946Y INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="400T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H50M0S">
          <air:AirSegmentRef Key="388T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="403T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="388T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI BOM 18336Y AI AMD 10946Y INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="412T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H50M0S">
          <air:AirSegmentRef Key="413T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="415T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="413T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI BOM 18336Y AI AMD 10946Y INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="424T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT7H50M0S">
          <air:AirSegmentRef Key="425T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="427T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="425T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI BOM 18336Y AI AMD 10946Y INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="436T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT6H5M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="437T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="439T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="446T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="446T" SegmentRef="437T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI DEL 18336Y 9W AMD 6750H2IP INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="454T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT7H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="455T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="457T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="446T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="446T" SegmentRef="455T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI DEL 18336Y 9W AMD 6750H2IP INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="466T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT11H0M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="455T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="467T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="446T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="446T" SegmentRef="455T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI DEL 18336Y 9W AMD 6750H2IP INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="477T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT11H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="478T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="480T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="446T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="446T" SegmentRef="478T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI DEL 18336Y 9W AMD 6750H2IP INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="489T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT13H50M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="490T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="492T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="446T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="446T" SegmentRef="490T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI DEL 18336Y 9W AMD 6750H2IP INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="501T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10">
        <air:Journey TravelTime="P0DT14H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="502T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="504T" TotalPrice="GBP499.10" BasePrice="INR36032" ApproximateTotalPrice="GBP499.10" ApproximateBasePrice="GBP363.00" EquivalentBasePrice="GBP363.00" Taxes="GBP136.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="446T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="446T" SegmentRef="502T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP52.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP57.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI DEL 18336Y 9W AMD 6750H2IP INR36032END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="513T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="514T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="525T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="526T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="537T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H50M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="538T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="549T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H50M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="550T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="561T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT13H20M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="191T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="562T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="191T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="573T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT13H20M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="191T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="574T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="191T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="585T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT14H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="204T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="586T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="204T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="597T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT14H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="204T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="598T" TotalPrice="GBP517.50" BasePrice="INR36913" ApproximateTotalPrice="GBP517.50" ApproximateBasePrice="GBP372.00" EquivalentBasePrice="GBP372.00" Taxes="GBP145.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="204T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP54.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR36913END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="609T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="614T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR37585END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="631T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="632T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR37585END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="642T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="643T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR37585END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="653T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="654T" TotalPrice="GBP520.30" BasePrice="INR37585" ApproximateTotalPrice="GBP520.30" ApproximateBasePrice="GBP378.00" EquivalentBasePrice="GBP378.00" Taxes="GBP142.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.00" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR37585END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="664T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT9H50M0S">
          <air:AirSegmentRef Key="217T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="665T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="226T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="226T" SegmentRef="217T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4650H2IPJK AI BOM 9838Y AI AMD 10946Y INR37473END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="676T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT9H50M0S">
          <air:AirSegmentRef Key="217T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="677T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="226T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="226T" SegmentRef="217T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W IDR 4650H2IPJK AI BOM 9838Y AI AMD 10946Y INR37473END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="688T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="689T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK AI AMD 12039Y INR37473END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="700T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="701T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK AI AMD 12039Y INR37473END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="712T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="713T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK AI AMD 12039Y INR37473END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="724T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="725T" TotalPrice="GBP523.10" BasePrice="INR37473" ApproximateTotalPrice="GBP523.10" ApproximateBasePrice="GBP377.00" EquivalentBasePrice="GBP377.00" Taxes="GBP146.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK AI AMD 12039Y INR37473END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="736T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="737T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP AI AMD 12039Y INR37613END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="748T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="749T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP AI AMD 12039Y INR37613END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="760T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="761T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP AI AMD 12039Y INR37613END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="772T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="773T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP AI AMD 12039Y INR37613END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="784T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="785T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP AI AMD 12039Y INR37613END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="796T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="797T" TotalPrice="GBP525.40" BasePrice="INR37613" ApproximateTotalPrice="GBP525.40" ApproximateBasePrice="GBP379.00" EquivalentBasePrice="GBP379.00" Taxes="GBP146.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP AI AMD 12039Y INR37613END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="808T" TotalPrice="GBP527.10" BasePrice="INR38145" ApproximateTotalPrice="GBP527.10" ApproximateBasePrice="GBP384.00" EquivalentBasePrice="GBP384.00" Taxes="GBP143.10">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="811T" TotalPrice="GBP527.10" BasePrice="INR38145" ApproximateTotalPrice="GBP527.10" ApproximateBasePrice="GBP384.00" EquivalentBasePrice="GBP384.00" Taxes="GBP143.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP55.80" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP55.30" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR38145END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="823T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="824T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI AMD 12039Y INR40445END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="835T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="99T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="836T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="99T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI AMD 12039Y INR40445END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="847T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="848T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI AMD 12039Y INR40445END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="859T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="114T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="860T" TotalPrice="GBP556.90" BasePrice="INR40445" ApproximateTotalPrice="GBP556.90" ApproximateBasePrice="GBP407.00" EquivalentBasePrice="GBP407.00" Taxes="GBP149.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="110T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="110T" SegmentRef="114T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI AMD 12039Y INR40445END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="871T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="872T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR40645END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="883T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="884T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR40645END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="895T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="896T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR40645END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="907T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="908T" TotalPrice="GBP559.10" BasePrice="INR40645" ApproximateTotalPrice="GBP559.10" ApproximateBasePrice="GBP409.00" EquivalentBasePrice="GBP409.00" Taxes="GBP150.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP64.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR40645END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="919T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="924T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP60.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W AMD 6750H2IP INR41503END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="939T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="942T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP60.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W AMD 6750H2IP INR41503END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="951T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="954T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP60.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W AMD 6750H2IP INR41503END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="963T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="966T" TotalPrice="GBP565.80" BasePrice="INR41503" ApproximateTotalPrice="GBP565.80" ApproximateBasePrice="GBP418.00" EquivalentBasePrice="GBP418.00" Taxes="GBP147.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP59.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP60.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W AMD 6750H2IP INR41503END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="975T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="976T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="987T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="988T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="999T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1000T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1011T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1012T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1023T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1024T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1035T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1036T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1047T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1048T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1059T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1060T" TotalPrice="GBP588.30" BasePrice="INR42874" ApproximateTotalPrice="GBP588.30" ApproximateBasePrice="GBP432.00" EquivalentBasePrice="GBP432.00" Taxes="GBP156.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP62.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR42874END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1071T" TotalPrice="GBP593.90" BasePrice="INR43434" ApproximateTotalPrice="GBP593.90" ApproximateBasePrice="GBP437.00" EquivalentBasePrice="GBP437.00" Taxes="GBP156.90">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="125T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1072T" TotalPrice="GBP593.90" BasePrice="INR43434" ApproximateTotalPrice="GBP593.90" ApproximateBasePrice="GBP437.00" EquivalentBasePrice="GBP437.00" Taxes="GBP156.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="125T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP63.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR43434END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1083T" TotalPrice="GBP593.90" BasePrice="INR43434" ApproximateTotalPrice="GBP593.90" ApproximateBasePrice="GBP437.00" EquivalentBasePrice="GBP437.00" Taxes="GBP156.90">
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1084T" TotalPrice="GBP593.90" BasePrice="INR43434" ApproximateTotalPrice="GBP593.90" ApproximateBasePrice="GBP437.00" EquivalentBasePrice="GBP437.00" Taxes="GBP156.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="134T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="134T" SegmentRef="140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP63.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.80" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI DEL 12039Y 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR43434END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1095T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H15M0S">
          <air:AirSegmentRef Key="1096T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="1098T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1100T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1107T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1109T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1107T" SegmentRef="1096T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1109T" SegmentRef="1098T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP66.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.90" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI NAG 16100Y AI BOM 12415Y AI AMD 10946Y INR46211END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1113T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H5M0S">
          <air:AirSegmentRef Key="1096T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="1098T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1114T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1107T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1109T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1107T" SegmentRef="1096T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1109T" SegmentRef="1098T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP66.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.90" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI NAG 16100Y AI BOM 12415Y AI AMD 10946Y INR46211END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1125T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50">
        <air:Journey TravelTime="P0DT1H30M0S">
          <air:AirSegmentRef Key="19T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H5M0S">
          <air:AirSegmentRef Key="1096T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="1098T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1126T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="164T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1107T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1109T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="164T" SegmentRef="19T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1107T" SegmentRef="1096T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1109T" SegmentRef="1098T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP66.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.90" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD 9W DEL 6750H2IP AI NAG 16100Y AI BOM 12415Y AI AMD 10946Y INR46211END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1137T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50">
        <air:Journey TravelTime="P1DT3H25M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="1138T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="1140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1142T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1149T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1151T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1149T" SegmentRef="1138T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1151T" SegmentRef="1140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP66.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.90" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI NAG 12415Y AI DEL 16100Y 9W AMD 6750H2IP INR46211END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1155T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50">
        <air:Journey TravelTime="P1DT6H40M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="1138T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="1140T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT1H25M0S">
          <air:AirSegmentRef Key="32T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1156T" TotalPrice="GBP625.50" BasePrice="INR46211" ApproximateTotalPrice="GBP625.50" ApproximateBasePrice="GBP465.00" EquivalentBasePrice="GBP465.00" Taxes="GBP160.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1149T" ></air:FareInfoRef>
          <air:FareInfoRef Key="1151T" ></air:FareInfoRef>
          <air:FareInfoRef Key="254T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1149T" SegmentRef="1138T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="1151T" SegmentRef="1140T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="254T" SegmentRef="32T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP66.60" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP67.90" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI NAG 12415Y AI DEL 16100Y 9W AMD 6750H2IP INR46211END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1167T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1168T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1181T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1182T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1195T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H50M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1196T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1209T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H50M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1210T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1223T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT13H20M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="191T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1224T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="191T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1237T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT13H20M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="191T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1238T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="191T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1251T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT14H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="204T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1252T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="204T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1265T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT14H10M0S">
          <air:AirSegmentRef Key="151T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="204T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1266T" TotalPrice="GBP732.80" BasePrice="INR53280" ApproximateTotalPrice="GBP732.80" ApproximateBasePrice="GBP536.00" EquivalentBasePrice="GBP536.00" Taxes="GBP196.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="166T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="166T" SegmentRef="151T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="204T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP78.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4090H2IPJK AI BOM 9838Y AI AMD 10946Y INR53280END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1279T" TotalPrice="GBP739.60" BasePrice="INR53840" ApproximateTotalPrice="GBP739.60" ApproximateBasePrice="GBP542.00" EquivalentBasePrice="GBP542.00" Taxes="GBP197.60">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT9H50M0S">
          <air:AirSegmentRef Key="217T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1280T" TotalPrice="GBP739.60" BasePrice="INR53840" ApproximateTotalPrice="GBP739.60" ApproximateBasePrice="GBP542.00" EquivalentBasePrice="GBP542.00" Taxes="GBP197.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="226T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="226T" SegmentRef="217T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4650H2IPJK AI BOM 9838Y AI AMD 10946Y INR53840END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1293T" TotalPrice="GBP739.60" BasePrice="INR53840" ApproximateTotalPrice="GBP739.60" ApproximateBasePrice="GBP542.00" EquivalentBasePrice="GBP542.00" Taxes="GBP197.60">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT9H50M0S">
          <air:AirSegmentRef Key="217T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="153T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1294T" TotalPrice="GBP739.60" BasePrice="INR53840" ApproximateTotalPrice="GBP739.60" ApproximateBasePrice="GBP542.00" EquivalentBasePrice="GBP542.00" Taxes="GBP197.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="226T" ></air:FareInfoRef>
          <air:FareInfoRef Key="168T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="226T" SegmentRef="217T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="168T" SegmentRef="153T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W IDR 4650H2IPJK AI BOM 9838Y AI AMD 10946Y INR53840END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1307T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1308T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54040END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1321T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1322T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54040END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1335T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1336T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54040END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1349T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1350T" TotalPrice="GBP741.80" BasePrice="INR54040" ApproximateTotalPrice="GBP741.80" ApproximateBasePrice="GBP544.00" EquivalentBasePrice="GBP544.00" Taxes="GBP197.80" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54040END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1363T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1364T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54180END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1377T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1378T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54180END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1391T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1392T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54180END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1405T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1406T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54180END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1419T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1420T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54180END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1433T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1434T" TotalPrice="GBP742.90" BasePrice="INR54180" ApproximateTotalPrice="GBP742.90" ApproximateBasePrice="GBP545.00" EquivalentBasePrice="GBP545.00" Taxes="GBP197.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP79.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR54180END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1447T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1448T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1461T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1462T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1475T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1476T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1489T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1490T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1503T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1504T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1517T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1518T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1531T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1532T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1545T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1546T" TotalPrice="GBP769.90" BasePrice="INR56269" ApproximateTotalPrice="GBP769.90" ApproximateBasePrice="GBP566.00" EquivalentBasePrice="GBP566.00" Taxes="GBP203.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56269END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1559T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1560T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1573T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1574T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1587T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1588T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1601T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1602T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1615T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1616T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1629T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1630T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1643T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1644T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1657T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1658T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1671T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1672T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1685T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1686T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1699T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1700T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1713T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1714T" TotalPrice="GBP772.10" BasePrice="INR56409" ApproximateTotalPrice="GBP772.10" ApproximateBasePrice="GBP568.00" EquivalentBasePrice="GBP568.00" Taxes="GBP204.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP82.70" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR56409END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1727T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1728T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR57012END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1741T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1742T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR57012END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1755T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1756T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR57012END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1769T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1770T" TotalPrice="GBP775.50" BasePrice="INR57012" ApproximateTotalPrice="GBP775.50" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP201.50" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP86.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR57012END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1783T" TotalPrice="GBP776.60" BasePrice="INR56829" ApproximateTotalPrice="GBP776.60" ApproximateBasePrice="GBP572.00" EquivalentBasePrice="GBP572.00" Taxes="GBP204.60">
        <air:Journey TravelTime="P0DT16H35M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1784T" TotalPrice="GBP776.60" BasePrice="INR56829" ApproximateTotalPrice="GBP776.60" ApproximateBasePrice="GBP572.00" EquivalentBasePrice="GBP572.00" Taxes="GBP204.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR56829END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1797T" TotalPrice="GBP776.60" BasePrice="INR56829" ApproximateTotalPrice="GBP776.60" ApproximateBasePrice="GBP572.00" EquivalentBasePrice="GBP572.00" Taxes="GBP204.60">
        <air:Journey TravelTime="P0DT18H20M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="236T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1798T" TotalPrice="GBP776.60" BasePrice="INR56829" ApproximateTotalPrice="GBP776.60" ApproximateBasePrice="GBP572.00" EquivalentBasePrice="GBP572.00" Taxes="GBP204.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="252T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="252T" SegmentRef="236T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4650H2IPJK 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR56829END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1811T" TotalPrice="GBP778.90" BasePrice="INR56969" ApproximateTotalPrice="GBP778.90" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP204.90">
        <air:Journey TravelTime="P0DT12H15M0S">
          <air:AirSegmentRef Key="232T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1812T" TotalPrice="GBP778.90" BasePrice="INR56969" ApproximateTotalPrice="GBP778.90" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP204.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="232T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR56969END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1825T" TotalPrice="GBP778.90" BasePrice="INR56969" ApproximateTotalPrice="GBP778.90" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP204.90">
        <air:Journey TravelTime="P0DT14H0M0S">
          <air:AirSegmentRef Key="257T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1826T" TotalPrice="GBP778.90" BasePrice="INR56969" ApproximateTotalPrice="GBP778.90" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP204.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="257T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR56969END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1839T" TotalPrice="GBP778.90" BasePrice="INR56969" ApproximateTotalPrice="GBP778.90" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP204.90">
        <air:Journey TravelTime="P1DT2H45M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="234T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="270T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1840T" TotalPrice="GBP778.90" BasePrice="INR56969" ApproximateTotalPrice="GBP778.90" ApproximateBasePrice="GBP574.00" EquivalentBasePrice="GBP574.00" Taxes="GBP204.90" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="250T" ></air:FareInfoRef>
          <air:FareInfoRef Key="279T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="250T" SegmentRef="234T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="279T" SegmentRef="270T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.50" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI IDR 9838Y 9W DEL 4790H2IP 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR56969END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1853T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT5H50M0S">
          <air:AirSegmentRef Key="371T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1854T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="371T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1866T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT5H50M0S">
          <air:AirSegmentRef Key="371T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1867T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="371T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1879T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H0M0S">
          <air:AirSegmentRef Key="388T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1880T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="388T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1892T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H0M0S">
          <air:AirSegmentRef Key="388T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1893T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="388T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1905T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H50M0S">
          <air:AirSegmentRef Key="388T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1906T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="388T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1918T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H50M0S">
          <air:AirSegmentRef Key="388T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1919T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="388T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1931T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H50M0S">
          <air:AirSegmentRef Key="413T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1932T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="413T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1944T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT6H50M0S">
          <air:AirSegmentRef Key="413T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1945T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="413T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1957T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT7H50M0S">
          <air:AirSegmentRef Key="425T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1958T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="425T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1970T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT7H50M0S">
          <air:AirSegmentRef Key="425T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1971T" TotalPrice="GBP782.40" BasePrice="INR57688" ApproximateTotalPrice="GBP782.40" ApproximateBasePrice="GBP581.00" EquivalentBasePrice="GBP581.00" Taxes="GBP201.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="380T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="380T" SegmentRef="425T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP83.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP91.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK AI BOM 18336Y AI AMD 10946Y INR57688END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1983T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1984T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="1997T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="1998T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2011T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2012T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2025T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2026T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2039T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2040T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2053T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2054T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2067T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2068T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2081T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2082T" TotalPrice="GBP803.60" BasePrice="INR59241" ApproximateTotalPrice="GBP803.60" ApproximateBasePrice="GBP596.00" EquivalentBasePrice="GBP596.00" Taxes="GBP207.60" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.20" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR59241END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2095T" TotalPrice="GBP810.30" BasePrice="INR59801" ApproximateTotalPrice="GBP810.30" ApproximateBasePrice="GBP602.00" EquivalentBasePrice="GBP602.00" Taxes="GBP208.30">
        <air:Journey TravelTime="P0DT15H30M0S">
          <air:AirSegmentRef Key="297T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2096T" TotalPrice="GBP810.30" BasePrice="INR59801" ApproximateTotalPrice="GBP810.30" ApproximateBasePrice="GBP602.00" EquivalentBasePrice="GBP602.00" Taxes="GBP208.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="297T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR59801END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2109T" TotalPrice="GBP810.30" BasePrice="INR59801" ApproximateTotalPrice="GBP810.30" ApproximateBasePrice="GBP602.00" EquivalentBasePrice="GBP602.00" Taxes="GBP208.30">
        <air:Journey TravelTime="P0DT18H45M0S">
          <air:AirSegmentRef Key="328T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="310T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="312T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2110T" TotalPrice="GBP810.30" BasePrice="INR59801" ApproximateTotalPrice="GBP810.30" ApproximateBasePrice="GBP602.00" EquivalentBasePrice="GBP602.00" Taxes="GBP208.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="245T" ></air:FareInfoRef>
          <air:FareInfoRef Key="321T" ></air:FareInfoRef>
          <air:FareInfoRef Key="323T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="245T" SegmentRef="328T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="321T" SegmentRef="310T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="323T" SegmentRef="312T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP86.90" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP5.70" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP89.40" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP12.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI BOM 10946Y AI BHO 12230Y 9W DEL 5230H2IPJK 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR59801END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP17.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="1" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="4" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2123T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2124T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2136T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2137T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2149T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2150T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2162T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT20H30M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2163T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2175T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2176T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2188T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2189T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2201T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2202T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2214T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT0H10M0S">
          <air:AirSegmentRef Key="341T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="343T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2215T" TotalPrice="GBP851.30" BasePrice="INR63359" ApproximateTotalPrice="GBP851.30" ApproximateBasePrice="GBP638.00" EquivalentBasePrice="GBP638.00" Taxes="GBP213.30" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="352T" ></air:FareInfoRef>
          <air:FareInfoRef Key="354T" ></air:FareInfoRef>
          <air:FareInfoRef Key="170T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="352T" SegmentRef="341T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="354T" SegmentRef="343T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="170T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP91.30" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP94.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W BHO 5430H2IPJK AI BOM 12230Y AI AMD 10946Y INR63359END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2227T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2228T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2240T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2241T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2253T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2254T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2266T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT13H35M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2267T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2279T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2280T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2292T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2293T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2305T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2306T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2318T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT14H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="401T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2319T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="401T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2331T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2332T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2344T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2345T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2357T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2358T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2370T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT21H25M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="155T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2371T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="155T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2383T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2384T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2396T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2397T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2409T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2410T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2422T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P1DT1H5M0S">
          <air:AirSegmentRef Key="610T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="178T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2423T" TotalPrice="GBP879.40" BasePrice="INR65588" ApproximateTotalPrice="GBP879.40" ApproximateBasePrice="GBP660.00" EquivalentBasePrice="GBP660.00" Taxes="GBP219.40" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="621T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="621T" SegmentRef="610T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="178T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP94.40" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4090H2IPJK AI BOM 15799Y AI AMD 10946Y INR65588END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2435T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10">
        <air:Journey TravelTime="P0DT5H0M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="922T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2436T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="922T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP95.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR66148END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2448T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10">
        <air:Journey TravelTime="P0DT11H40M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="940T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2449T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="940T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP95.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR66148END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2461T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10">
        <air:Journey TravelTime="P0DT14H30M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="952T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2462T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="952T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP95.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR66148END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
      <air:AirPricingSolution Key="2474T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10">
        <air:Journey TravelTime="P0DT16H20M0S">
          <air:AirSegmentRef Key="920T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="964T" ></air:AirSegmentRef>
        </air:Journey>
        <air:Journey TravelTime="P0DT8H40M0S">
          <air:AirSegmentRef Key="809T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="612T" ></air:AirSegmentRef>
          <air:AirSegmentRef Key="390T" ></air:AirSegmentRef>
        </air:Journey>
        <air:LegRef Key="5T" ></air:LegRef>
        <air:LegRef Key="6T" ></air:LegRef>
        <air:AirPricingInfo Key="2475T" TotalPrice="GBP886.10" BasePrice="INR66148" ApproximateTotalPrice="GBP886.10" ApproximateBasePrice="GBP666.00" EquivalentBasePrice="GBP666.00" Taxes="GBP220.10" LatestTicketingTime="2014-07-09T23:59:00.000+01:00" PricingMethod="Guaranteed" Refundable="true" ETicketability="Yes" PlatingCarrier="HR" ProviderCode="1G">
          <air:FareInfoRef Key="931T" ></air:FareInfoRef>
          <air:FareInfoRef Key="933T" ></air:FareInfoRef>
          <air:FareInfoRef Key="818T" ></air:FareInfoRef>
          <air:FareInfoRef Key="623T" ></air:FareInfoRef>
          <air:FareInfoRef Key="625T" ></air:FareInfoRef>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="931T" SegmentRef="920T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="933T" SegmentRef="964T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="H" CabinClass="Economy" FareInfoRef="818T" SegmentRef="809T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="623T" SegmentRef="612T" ></air:BookingInfo>
          <air:BookingInfo BookingCode="Y" CabinClass="Economy" FareInfoRef="625T" SegmentRef="390T" ></air:BookingInfo>
          <air:TaxInfo Category="IN" Amount="GBP13.20" ></air:TaxInfo>
          <air:TaxInfo Category="JN" Amount="GBP95.10" ></air:TaxInfo>
          <air:TaxInfo Category="WO" Amount="GBP7.20" ></air:TaxInfo>
          <air:TaxInfo Category="YM" Amount="GBP1.10" ></air:TaxInfo>
          <air:TaxInfo Category="YQ" Amount="GBP97.50" ></air:TaxInfo>
          <air:TaxInfo Category="YR" Amount="GBP6.00" ></air:TaxInfo>
          <air:FareCalc>AMD AI HYD 16832Y AI DEL 17921Y 9W JAI 4650H2IPJK AI BOM 15799Y AI AMD 10946Y INR66148END</air:FareCalc>
          <air:PassengerType Code="ADT" ></air:PassengerType>
          <air:ChangePenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:ChangePenalty>
          <air:CancelPenalty>
            <air:Amount>GBP10.00</air:Amount>
          </air:CancelPenalty>
        </air:AirPricingInfo>
        <air:Connection StopOver="true" SegmentIndex="0" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="2" ></air:Connection>
        <air:Connection StopOver="true" SegmentIndex="3" ></air:Connection>
      </air:AirPricingSolution>
    </air:LowFareSearchRsp>
  </SOAP:Body>
</SOAP:Envelope>';*/
       // pr($respons);exit;
        $xml2Array = $this->xml2array($respons);
        // pr($xml2Array[0]);exit;
        if (!empty($xml2Array[0])) {
            $xml2Array['SOAP:Envelope']['SOAP:Body']['segmentdata'] = $xml2Array[0];
        }
        // pr($xml2Array['SOAP:Envelope']['SOAP:Body']);exit;
       return $xml2Array['SOAP:Envelope']['SOAP:Body'];
       // return $xml2Array[0];;
    }

}
