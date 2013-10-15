<?php 
    include_once 'lib/SmartlingAPI.php';
        
    $fileUri = 'testing.json';    
    $fileType = 'json';
    $newFileUri = 'newfile.json'; 
    $locale = 'ru_RU';
    $fileName = 'translated.json';
    $translationState = 'PUBLISHED';
    $key = "";
    $projectId = "";
    $locale = 'ru-RU';
    
    //init api object
    $api = new SmartlingAPI($key, $projectId);    
        
    $params = array(        
        'approved'    => true,
    );
       
    $result = $api->uploadFile('./test.json', $fileType, $fileUri, $params);
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
    echo "<br />This is a get list<br />";
    
    $result = $api->renameFile($fileUri, $newFileUri);
    var_dump($result);
    echo "<br />This is a rename file<br />";   
    
    
//    $result = $api->import($newFileUri, $fileType, $locale, $fileName, $overwrite = false, $translationState);
//    var_dump($result);
//    echo "<br />This is import file<br />";
    
    $result = $api->deleteFile($newFileUri);
    var_dump($result);
    echo "<br />This is delete file<br />";