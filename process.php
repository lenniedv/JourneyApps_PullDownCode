<?php
/*
    https://docs.journeyapps.com/docs/editor-file-api

    Author: Lennie De Villiers (lenniedg@gmail.com)
    Created: 3 July 2019
	*/

    function journeyQuery($appId, $authToken, $dir) {
        $url = 'https://build.journeyapps.com/api/v4/apps/'.$appId.'/files/?recursive=true'; 

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 200,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$authToken,
            "cache-control: no-cache",
            "content-type: application/json",
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response);
            $cloudCodeList = $data->children->cloudcode;     
            writeDoc($cloudCodeList, $dir); 

            $mobileList = $data->children->mobile; 
            writeDoc($mobileList, $dir);    
        
            writeFile($dir, "config.json", getFileContent($appId, $authToken, $data->children->config.json));
            writeFile($dir, "schema.xml", getFileContent($appId, $authToken, $data->children->schema.xml));
            writeFile($dir, "sync_rules.xml", getFileContent($appId, $authToken, $data->children->sync_rules.xml));
          }
    }

    function writeDoc($fileList, $dir) {
        foreach($fileList->children as $currentParentItem) {
            if (!is_null($currentParentItem->children)) {
                foreach ($currentParentItem->children as $currentChild) {
                    writeFile($dir, $currentChild->path, getFileContent($appId, $authToken, $currentChild->path));
                }   
            }
            else {
                writeFile($dir, $currentParentItem->path, getFileContent($appId, $authToken, $currentParentItem->path));
            }
        } 
    }

    function writeFile($dir, $path, $content) {
        $filePath = $dir."/".$path;    

        echo $filePath."\n\n";

        if(!file_exists(dirname($filePath)))
            mkdir(dirname($filePath), 0777, true);

        $myfile = fopen($filePath, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
    }

    function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    function getFileContent($appId, $authToken, $fileName) {
        $url = 'https://build.journeyapps.com/api/v4/apps/'.$appId.'/files/'.$fileName; 

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 200,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$authToken,
            "cache-control: no-cache",
            "content-type: application/json",
        ),
        ));

        $content = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        return $content;
    }

    $appid = $argv[1];
    $authToken = $argv[2];
    $dir = $argv[3];

    deleteDir($dir);
    journeyQuery($appid, $authToken, $dir);
?>
