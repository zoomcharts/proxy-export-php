<?php
//call this function:
function export(){
    $type = $_POST["type"];
    $valid = array("image/jpeg"=>"jpeg", "image/png"=>"png", "application/pdf"=>"pdf", "image/svg" => "svg", "text/csv" => "csv", "application/vnd.ms-excel" => "xls");
    if (isset($valid[$type])){
        $ext = $valid[$type];
        if ($_POST["encoding"] == "base64"){
            $c = $_POST["data"];
            if ($p=strpos($_POST["data"], ",")){
                $c = substr($_POST["data"], $p+1);
            }
            $c = base64_decode($c);
        } else if ($_POST["encoding"] == "json") {
            $c = json_decode($_POST["data"]);
        } else {
            $c = $_POST["data"];
        }
        if (!$c){
            echo "Sorry, could not export this image";
            exit;
        }
        header("Content-Type: " . $type);
        header("Content-Disposition: attachment; filename=\"export.$ext\"");
        if ($ext == "xls"){
            /* convert json to xls */
            print("<table>");
            foreach ($c as $row){
                print("<tr><td>".implode("</td><td>", $row)."</td></tr>");
            }
            print("</table>");
            exit;
        } else if ($ext == "csv"){
            /* convert to csv */
            $fmt = numfmt_create("lv_LV", NumberFormatter::PATTERN_DECIMAL);
            foreach ($c as $row){
                array_walk($row, function(&$item,$key) use ($fmt){
                    if ($key > 1){
                        $item = $fmt->format($item);
                    }

                });
                print(implode(";", $row)."\n");
            }
            exit;
        } else if ($_POST["setdpi"]){
            $fid=fopen($a="cache/tmp".microtime(true).".$ext", "w");
            fputs($fid, $c);
            fclose($fid);
            $dpi = (int)$_POST["setdpi"];
            if ($dpi < 36){
                $dpi = 36;
            } else if ($dpi > 1200){
                $dpi = 1200;
            }
            system("LC_ALL=en_US.UTF-8 LANG=en_US.UTF-8 exiftool -Xresolution=$dpi -Yresolution=$dpi -overwrite_original -q $a");
            print file_get_contents($a);
            unlink($a);
            exit;
        }
        print $c;
        exit;
    } else {
        echo "Invalid request";
    }
    exit;
}
