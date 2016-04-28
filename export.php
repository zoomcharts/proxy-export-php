<?php
//call this function:
export();
function export(){
    $p = $_POST;
    if(!isset($p["data"]) || !$p["data"] || (substr($p["data"], 0, 5) != "data:")) {
        echo "The request did not include a valid 'data' parameter which must be a valid data-uri.\n";
        echo "Received input:\n";
        echo $p["data"] ?: "null";
        exit;
    }
    $data = $p["data"];
    $i = strpos($data, ",");
    $j = strpos($data, ";");
    if($j == false) $j = $i;
    if($i == false) $i = $j;
    
    $type = substr($data, 5, $j - 5);
    $valid = array("image/jpeg"=>"jpeg", "image/png"=>"png", "application/pdf"=>"pdf", "image/svg" => "svg", "text/csv" => "csv", "application/vnd.ms-excel" => "xls", "text/plain" => "txt");
    if (!isset($type) || !$type || !isset($valid[$type])){
        echo "Unsupported type.";
        exit;   
    } else {
        $ext = $valid[$type];
    }

    $mime = $type ?: "application/octet-stream";
    $name = (isset($p["name"]) && $p["name"]) ? $p["name"] : "export";
    $base64 = substr($data, $i - 7, 7) == ";base64";

    //Proper name handling:   
    $tmp_ext = ".".$ext;
    if(strlen($tmp_ext) < strlen($name)) {
        $ex = substr_compare($name, $tmp_ext, strlen($name) - strlen($tmp_ext), strlen($tmp_ext)) === 0;
        if(!$ex) {
            $name = $name.".".$ext;
        }
    }


    try {
        if($base64) {
            if(!strpos($data, ",")) {
                echo "Invalid data-uri format";
            }
            header("Content-Type: " . $mime);
            header("Content-Disposition: attachment; filename=\"$name\"");
            header("Cache-Control: private, must-revalidate, max-age=0");

            $sub = substr($data, $i + 1);
            $bdata = base64_decode($sub);
            echo $bdata;
            exit;
        } else {
            header("Content-Type: " . $mime);
            header("Content-Disposition: attachment; filename=\"$name\"");
            header("Cache-Control: private, must-revalidate, max-age=0");

            $sub = substr($data, $i + 1);
            echo urldecode($sub);
        }
    } catch(Exception $e) {
        echo "The data-uri could not be parsed.\n";
        echo $e->getMessage();
        exit;
    }
    exit;
}
