<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Sanjay Sinalkar
 *  .
 * need to extends CI_Form_validation
 * 
 *Required Config Two Variable in CodeIgniter
 *$config['captcha_font']= path for captcha font
   $config['captcha_temp_path']=path for temp.Image
 * temp_path needed File Read Write Permission
 * use  setParameter() pass sample array eg.
 * $param=array(
            "type"=>"png",
            "length"=>6,
            "height"=>75,
            "case"=>false,
            "filters"=>array(
                       "noise"=>20,
                       "blur"=>5
                        ),
            "bgColor"=>array(255,232,255),
            "textColor"=>array(0,134,11)
            
        );
 * $this->captcha->setParameter($param);
 * <img src="<%$this->captcha->show()%>"/>
 *set  show() to Img Src
 */


class Captcha  {

    protected $CI;
    
    var $length = 6;
    var $fontpath;
    var $fonts;
    var $controller = array();
    var $sessionKey = 'NSPLCaptcha';
    var $case = false;
    var $filters = array("noise"=>50);
    var $imgFormat = "png";
    var $bgColor = array(221,221,221);
    var $stringColor = array(0, 0, 0);
    var $bgStringColor = array(175, 175, 175);
    var $param=array();
    var $is_valid;
    var $error;
    
    function __construct() {
      //   parent::__construct();
        $this->CI = &get_instance();
       
        $this->CI->load->library('session');
    }
    
    function setParameter($param=  array())
    {
        if(is_array($param))
        {
        $this->param=$param;
        }
    }


    
    function protect($model = 'Captcha') {
        
      //  $response_field = $this->CI->input->post('recaptcha_response_field');
        if (isset($this->controller->params['nspl_captcha_input']) && !empty($this->controller->params['nspl_captcha_input'])) {

            if ($this->__check($this->controller->params['nspl_captcha_input'])) {
                $this->CI->session->set_userdata($this->sessionKey,"");
                unset($this->controller->params['nspl_captcha_input']);
                return true;
            } else {
                $this->__generate();
                $this->controller->errormsg->setError_Message('Incorrect image verification please retry!', 'err');
                unset($this->controller->params['nspl_captcha_input']);
                return false;
            }
        } else {
            $this->__generate();
            return false;
        }
    }

    // Create a function called captcha in a controller and reference
    // the captcha image src in the view to it.
    public function show() {

        $this->sessionKey=  isset($this->param['sessionKey'])?$this->param['sessionKey']:$this->sessionKey;
        
        if(isset($this->param['case']) && is_bool($this->param['case']))
        {
            $this->case=$this->param['case'];
        }
        
      
        
        if(isset($this->param['filters']) && is_array($this->param['filters']))
        {
            $this->filters=$this->param['filters'];
        }

        
        $this->fontpath = $this->__getFontPath();

        $this->fonts = $this->__getFonts();

        $this->__makeCaptcha();
        //$this->controller->autoRender=false;
    }

//captcha

    function __check($string) {
        return ($string === $this->CI->session->userdata($this->sessionKey));
    }

    function __generate($protect = false) {

        if (!$protect) {
            if ($this->CI->session->userdata($this->sessionKey) == "")
                $protect = false;
            else
                $protect = true;
        }

        if ($protect) {
            $this->CI->session->set_userdata($this->sessionKey,$this->__stringGen()) ;
        }
    }

    function __getFontPath() {

        return $this->CI->config->item('captcha_font');
    }

    function __getFonts() {

        $fonts = array();

        if ($handle = @opendir($this->fontpath)) {

            while (($file = readdir($handle)) !== FALSE) {

                $extension = strtolower(substr($file, strlen($file) - 3, 3));

                if ($extension == 'ttf') {

                    $fonts[] = $file;
                }
            }

            closedir($handle);
        } else {

            return null;
        }

        if (count($fonts) == 0) {

            return null;
        } else {

            return $fonts;
        }
    }

//getFonts

    function __getRandFont() {

        return $this->fontpath . $this->fonts[mt_rand(0, count($this->fonts) - 1)];
    }

//getRandFont

    function __stringGen() {

        $results = null;
        $uppercase = range('A', 'Z');
        $numeric = range(0, 9);

        $CharPool = array_merge($uppercase, $numeric);

        if ($this->case) {

            $lowercase = range('a', 'z');
            $CharPool = array_merge($CharPool, $lowercase);
        }

        $PoolLength = count($CharPool) - 1;

        for ($i = 0; $i < $this->length; $i++) {

            $results .= $CharPool[mt_rand(0, $PoolLength)];
        }

        return $results;
    }

//StringGen

    function __makeCaptcha() {

       //  echo ''.__FILE__;
        
        $this->__generate(true);
        $captchaString = $this->CI->session->userdata($this->sessionKey);

        $imagelength = isset($this->param['length']) && is_numeric($this->param['length'])?$this->param['length']*25+16:$this->length * 25 + 16;
        $imageheight = isset($this->param['height']) && is_numeric($this->param['height'])?$this->param['height']:50;
        //ob_clean();
        $image = imagecreate($imagelength, $imageheight);

        $this->bgColor=  isset($this->param['bgColor']) && is_array($this->param['bgColor']) && $this->validColorArr($this->param['bgColor'])?$this->param['bgColor']:$this->bgColor;
        $this->stringColor=  isset($this->param['textColor']) && $this->validColorArr($this->param['textColor'])?$this->param['textColor']:$this->stringColor;
        
        $bgcolor = imagecolorallocate($image, $this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
        $stringcolor = imagecolorallocate($image, $this->stringColor[0], $this->stringColor[1], $this->stringColor[2]);

        //$this->__signs($image, $this->__getRandFont());

        for ($i = 0; $i < strlen($captchaString); $i++) {
            imagettftext($image, 25, mt_rand(-30, 10), $i * 25, mt_rand(40, 40), $stringcolor, $this->__getRandFont(), $captchaString{$i});
        }

        if (isset($this->filters['noise']) && is_numeric($this->filters['noise'])) {

            $this->__noise($image, $this->filters['noise']);
        }

        if (isset($this->filters['blur']) && is_numeric($this->filters['blur'])) {

            $this->__blur($image, $this->filters['blur']);
        }

        $imgType=  isset($this->param['type'])?$this->param['type']:$this->imgFormat;
        
        switch ($imgType) {

            case "png" :
                $filename = $this->CI->config->item('captcha_temp_path') . $captchaString . time() . ".png";
              //  echo $this->CI->config->item('captcha_temp_path') . $captchaString . time() . ".png";;exit;
                imagepng($image, $filename);
             //   echo imagepng($image, $filename);
                //$picture = ob_get_clean();
                $fp = fopen($filename, "rb", 0);
                $picture_string = fread($fp, filesize($filename));
                fclose($fp);
                $base64 = chunk_split(base64_encode($picture_string));
                echo 'data:image/png;base64,' . trim($base64);
                break;

            case "jpg" :
                $filename = $this->CI->config->item('captcha_temp_path') . $captchaString . ".jpg";
                imagejpeg($image);
                //$picture = ob_get_clean();
                $fp = fopen($filename, "rb", 0);
                $picture_string = fread($fp, filesize($filename));
                fclose($fp);
                $base64 = chunk_split(base64_encode($picture_string));
                echo 'data:image/jpg;base64,' . trim($base64);
                break;

            case "jpeg" :
                $filename = $this->CI->config->item('captcha_temp_path') . $captchaString . ".jpeg";
                imagejpeg($image);
                //$picture = ob_get_clean();
                $fp = fopen($filename, "rb", 0);
                $picture_string = fread($fp, filesize($filename));
                fclose($fp);
                $base64 = chunk_split(base64_encode($picture_string));
                echo 'data:image/jpg;base64,' . trim($base64);
                break;

            case "gif" :
                $filename = $this->CI->config->item('captcha_temp_path') . $captchaString . ".gif";
                imagegif($image);
                //$picture = ob_get_clean();
                $fp = fopen($filename, "rb", 0);
                $picture_string = fread($fp, filesize($filename));
                fclose($fp);
                $base64 = chunk_split(base64_encode($picture_string));
                echo 'data:image/gif;base64,' . trim($base64);
                break;

            default :
                $filename = $this->CI->config->item('captcha_temp_path') . $captchaString . ".png";
                imagepng($image, $filename);
                //$picture = ob_get_clean();
                $fp = fopen($filename, "rb", 0);
                $picture_string = fread($fp, filesize($filename));
                fclose($fp);
                $base64 = chunk_split(base64_encode($picture_string));
                echo 'data:image/png;base64,' . trim($base64);
                break;
        }
        imagedestroy($image);
        unlink($filename);
    }

//MakeCaptcha


    /* -----------------------------
     * FILTER FOR CAPTCHA
     *
     *
     * ------------------------------ */

    function __noise(&$image, $runs = 30) {

        $w = imagesx($image);
        $h = imagesy($image);

        for ($n = 0; $n < $runs; $n++) {

            for ($i = 1; $i <= $h; $i++) {

                $randcolor = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));

                imagesetpixel($image, mt_rand(1, $w), mt_rand(1, $h), $randcolor);
            }
        }
    }

//noise

    function __signs(&$image, $font, $cells = 3) {

        $w = imagesx($image);
        $h = imagesy($image);

        for ($i = 0; $i < $cells; $i++) {

            $centerX = mt_rand(1, $w);
            $centerY = mt_rand(1, $h);
            $amount = mt_rand(1, 15);
            $stringcolor = imagecolorallocate($image, 175, 175, 175);

            for ($n = 0; $n < $amount; $n++) {

                $signs = range('A', 'Z');
                $sign = $signs[mt_rand(0, count($signs) - 1)];

                imagettftext($image, 25, mt_rand(-15, 15), $centerX + mt_rand(-50, 50), $centerY + mt_rand(-50, 50), $stringcolor, $font, $sign);
            }
        }
    }

//signs

    function __blur(&$image, $radius = 3) {

        $radius = round(max(0, min($radius, 50)) * 2);

        $w = imagesx($image);
        $h = imagesy($image);

        $imgBlur = imagecreate($w, $h);

        for ($i = 0; $i < $radius; $i++) {

            imagecopy($imgBlur, $image, 0, 0, 1, 1, $w - 1, $h - 1);
            imagecopymerge($imgBlur, $image, 1, 1, 0, 0, $w, $h, 50.0000);
            imagecopymerge($imgBlur, $image, 0, 1, 1, 0, $w - 1, $h, 33.3333);
            imagecopymerge($imgBlur, $image, 1, 0, 0, 1, $w, $h - 1, 25.0000);
            imagecopymerge($imgBlur, $image, 0, 0, 1, 0, $w - 1, $h, 33.3333);
            imagecopymerge($imgBlur, $image, 1, 0, 0, 0, $w, $h, 25.0000);
            imagecopymerge($imgBlur, $image, 0, 0, 0, 1, $w, $h - 1, 20.0000);
            imagecopymerge($imgBlur, $image, 0, 1, 0, 0, $w, $h, 16.6667);
            imagecopymerge($imgBlur, $image, 0, 0, 0, 0, $w, $h, 50.0000);
            imagecopy($image, $imgBlur, 0, 0, 0, 0, $w, $h);
        }

        imagedestroy($imgBlur);
    }

//blur

    public function valid($value) {
     //   echo 'Called';exit;
        if ($value == $this->CI->session->userdata('NSPLCaptcha')) {
           //echo 'Called';exit;
            $this->is_valid = true;
        } else {
            $this->is_valid = false;
            $this->error = "Wrong Captcha Input";
        }

        $captcha_response['is_valid'] = $this->is_valid;
        $captcha_response['error'] = $this->error;

        return $captcha_response;
    }

    function setBgColor($r = 0, $g = 0, $b = 0) {
        $this->bgColor = array($r, $g, $b);
    }

    function setStringColor($r = 0, $g = 0, $b = 0) {
        $this->stringColor = array($r, $g, $b);
    }

    function setBgStringColor($r = 0, $g = 0, $b = 0) {
        $this->bgStringColor = array($r, $g, $b);
    }
    
    function validColorArr($colorArr=  array())
    {
        if(count($colorArr)==3)
        {
            if(is_numeric($colorArr[0]) && is_numeric($colorArr[1]) && is_numeric($colorArr[2]))
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        
        else {
            return FALSE;
        }
    }
    
     function printImg()
    {
        $img_src = $this->show();
        echo '<img src="'.$img_src.'"/><input type="text" name="captcha_input" id="captcha_input" placeholder="captcha" maxlength="'.$this->length.'" size="60" />';
    }
}

