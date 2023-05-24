<?php
    $host = "5.199.161.42:3306";
    $username = "tester";
    $password = "5DMW5fV2jroGGpQO";
    $dbname = "homelessbeavers";
     
    try{

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $q = $pdo->prepare("SELECT type FROM types");
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    echo "Hello";
    //print(json_encode($output));
     
    }
    catch(PDOException $e){
        echo "Error" . $e->getMessage();
    }
    $pdo = null;
    ?>
