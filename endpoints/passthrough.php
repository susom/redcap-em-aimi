<?php
namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$em_setting     = (isset($_GET["em_setting"])) ? filter_var($_GET["em_setting"], FILTER_SANITIZE_STRING) : null;
$shard_name     = (isset($_GET["shard"])) ? filter_var($_GET["shard"], FILTER_SANITIZE_STRING) : null;

$active_alias   = $module->getProjectSetting("active_alias");
$aliases        = $module->getProjectSetting('aliases');
$current        = $aliases[$active_alias];
$shard_paths    = array_key_exists("model_json",$current) && !empty($current["model_json"]) ? $current["model_json"]["weightsManifest"][0]["paths"] : array();

if($em_setting && !$shard_name){
    if($em_setting == "config_js"){
        header("content-type: application/javascript");
        echo $current["config_js"];
    }

    if($em_setting == "model_json"){
        header("content-type: application/json");
        echo json_encode($current["model_json"]);
    }
}elseif($shard_name){
    // Make sure the requested file is one of the desired active model's shard paths
    $model_file_check = null;
    foreach($shard_paths as $shard_path ){
        if(strpos($shard_path , $shard_name) > -1){
            $model_file_check = $module->getSafePath($shard_name, APP_PATH_TEMP);
            break;
        }
    }

    // MAKE SURE THE FILE PATH EXISTS
    if($model_file_check && file_exists($model_file_check)){
        $file_size  = filesize($model_file_check);
        $mime_type  = mime_content_type($model_file_check) ?: 'application/octet-stream';

        header('Accept-Ranges: bytes');
        header("Content-Length: $file_size");
        header("content-type: $mime_type");

        header_remove('Access-Control-Allow-Origin');
        header_remove('cache-control');
        header_remove('Connection');
        header_remove('Expires');
        header_remove('Keep-Alive');
        header_remove('Pragma');
        header_remove('REDCap-Random-Text');
        header_remove('X-Content-Type-Options');
        header_remove('X-XSS-Protection');

        $handle = fopen($model_file_check, "rb");
        $contents = fread($handle, filesize($model_file_check));
        fclose($handle);
        echo $contents;
    }
}
exit();
?>
