<?php
$site_path = $_REQUEST['site_path'].$_REQUEST['mainfolder'];

if (isset($_FILES["myfile"])) {
    $ret = array();
    $path1 = $site_path.'/'.$_REQUEST['userid'].'/';
    $folder_path = createfolder($path1,$site_path);
//	This is for custom errors;	
    /* 	$custom_error= array();
      $custom_error['jquery-upload-file-error']="File already exists";
      echo json_encode($custom_error);
      die();
     */
    $error = $_FILES["myfile"]["error"];
    //You need to handle  both cases
    //If Any browser does not support serializing of multiple files using FormData() 
    if (!is_array($_FILES["myfile"]["name"])) { //single file
//            if($_REQUEST['table'] == 'location'){
//                $fileName = $_REQUEST['userid'] . '.jpg';
//            }else{
                $fileName = $_FILES["myfile"]["name"];
//            }
        move_uploaded_file($_FILES["myfile"]["tmp_name"], $path1 . $fileName);
        $ret[] = $fileName;
    } else {  //Multiple files, file[]
        $fileCount = count($_FILES["myfile"]["name"]);
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $_FILES["myfile"]["name"][$i];
            move_uploaded_file($_FILES["myfile"]["tmp_name"][$i], $path1 . $fileName);
            $ret[] = $fileName;
        }
    }
    echo json_encode($ret);
}

function createfolder($path,$site_path) {
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

?>