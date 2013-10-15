<?php 
    include_once 'lib/SmartlingAPI.php';
    
    header("Content-type: text/html;charset=utf-8");    
    
    $fileUri = 'test.xml';    
    $fileType = 'xml';
    $newFileUri = 'newfile.xml'; 
    $locale = 'ru_RU';
    $file = '';
    $translationState = '';
 
    $api = new SmartlingAPI("58b3e7a2-5f75-4324-b554-c677a76e8094", "1295c174d");    
    $locale = 'ru-RU';
    $fileUri = 'testing.xml';
        
    $result = $api->uploadFile('./test.xml', $fileType, $fileUri);
    var_dump($result);
    echo "<br />This is a upload file<br />";
     
    $result = $api->downloadFile($fileUri, $locale);
    var_dump($result);
     echo "<br />This is a download file<br />";
     
    $result = $api->getStatus($fileUri, $locale);
    var_dump($result);
     echo "<br />This is a get status<br />";
     
    $result = $api->getList($locale);
    var_dump($result);
    echo "<br />This is a get list";
    
    $result = $api->renameFile($fileUri, $newFileUri);
    var_dump($result);
    echo "<br />This is a rename file";   
    
    
    $result = $api->import($newFileUri, $fileType, $locale, $file, $overwrite = false, $translationState);
    var_dump($result);
    echo "<br />This is delete file";
    
    $result = $api->deleteFile($newFileUri);
    var_dump($result);
    echo "<br />This is delete file";