<?php
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST');
//header('Access-Control-Allow-Headers: *');

$host = "5.199.161.42:3306";
    $username = "tester";
    $password = "5DMW5fV2jroGGpQO";
    $dbname = "homelessbeavers";

    $fileLoc = "/uploads/".$_POST['filename'];

    $ifp = fopen( $fileLoc, 'wb');

$imagedata = explode(',', $_POST['image']);

fwrite ($ifp, base64_decode($imagedata[1]);

fclose($ifp);

    //file_put_contents($fileLoc, base64_decode($_POST['image']));

    try{

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $q = "INSERT into reports (reptype, animaltype, descr, lat, longi, file) values ('".$_POST['reportType']."', '".$_POST['animalType']."', '".$_POST['description']."', '".$_POST['latitude']."', '".$_POST['longitude']."', '".$fileLoc."')";
    
        $pdo->exec($q);
	print ("<script> console.log(".$_POST['latitude'].");</script>");         
        }
        catch(PDOException $e){
            echo "Error" . $e->getMessage();
        }
        $pdo = null;

function customError($errno, $errstr){
echo ("<script>console.log(Error: [".$errno."] ".$errstr.");</script>");
}

set_error_handler("customError");
?>
