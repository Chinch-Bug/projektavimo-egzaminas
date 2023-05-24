<?php
header('Access-Control-Allow-Origin: *'); 

$host = "5.199.161.42:3306";
    $username = "tester";
    $password = "5DMW5fV2jroGGpQO";
    $dbname = "homelessbeavers";

    $fileLoc = "/uploads"+$_POST['filename'];

    file_put_contents($fileLoc, base64_decode($_POST['image']));

    try{

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $q = $pdo->prepare("INSERT into reports (reptype, animaltype, descr, lat, longi, files) values ("+$_POST['reportType']+", "+
        $_POST['animalType']+", "+$_POST['description']+", "+$_POST['latitude']+", "+$_POST['longtitude']+", "+$fileLoc+")");
    
        $q->exec();
         
        }
        catch(PDOException $e){
            echo "Error" . $e->getMessage();
        }
        $pdo = null;

?>