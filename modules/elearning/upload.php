<?php
// (A) HELPER FUNCTION - SERVER RESPONSE
function verbose ($ok=1, $info="") {
  if ($ok==0) { http_response_code(400); }
  exit(json_encode(["ok"=>$ok, "info"=>$info]));
}


// (B) INVALID UPLOAD
if (empty($_FILES) || $_FILES["file"]["error"]) {
  verbose(0, serialize($_FILES));
}

// (C) UPLOAD DESTINATION - CHANGE FOLDER IF REQUIRED!
$filePath = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
if (!file_exists($filePath)) { if (!mkdir($filePath, 0777, true)) {
  verbose(0, "Failed to create $filePath");
}}

$fileName_tmp = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
$fileName = basename(verifica_duplicati($fileName_tmp,$filePath));


//$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];

$filePath = $filePath . DIRECTORY_SEPARATOR . $fileName;

// (D) DEAL WITH CHUNKS
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
$out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
if ($out) {
  $in = @fopen($_FILES["file"]["tmp_name"], "rb");
  if ($in) { while ($buff = fread($in, 4096)) { fwrite($out, $buff); } }
  else { verbose(0, "Failed to open input stream"); }
  @fclose($in);
  @fclose($out);
  @unlink($_FILES["file"]["tmp_name"]);
} else { verbose(0, "Failed to open output stream"); }

// (E) CHECK IF FILE HAS BEEN UPLOADED
if (!$chunks || $chunk == $chunks - 1) { rename("{$filePath}.part", $filePath); }
verbose(1, "Upload OK");


function verifica_duplicati($file, $basedir) {
    $nomefile = $basedir . '/'. $file;
    if (file_exists($nomefile)) {
        $pf = pathinfo_filename($nomefile);
        if (empty($pf['extension'])) $pf['extension'] = 'bin';

        if (preg_match('/([[:print:]]+)\_\((\d+)\)$/', $pf['filename'], $matches)) {
            $pf['filename'] = $matches[1] . '_('. ($matches[2]+1) .')';
        } else {
            $pf['filename'] .= '_(1)';
        }

        $pf['filename'] .= '.'.$pf['extension'];

        return verifica_duplicati($pf['filename'], $basedir);
    }
    return $nomefile;
}


function pathinfo_filename($path) {
    $temp = pathinfo($path);
    if ($temp['extension']) {
        $temp['filename'] = substr($temp['basename'],0 ,strlen($temp['basename'])-strlen($temp['extension'])-1);
    }
    return $temp;
}