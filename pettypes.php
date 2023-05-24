<?php
header("Access-Control-Allow-Origin: *");
    $host = "5.199.161.42:3306";
    $username = "tester";
    $password = "5DMW5fV2jroGGpQO";
    $dbname = "homelessbeavers";
    
    try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $q=$pdo->prepare("SELECT * FROM types");
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    $list = array();
    //foreach($rows as &$item){
    //$item['key'] = $item['id'];
    //unset($item['key']);
//}
function keyrename($item){
$item['key'] = $item['id'];
unset($item['id']);

$item['value'] = $item['animal'];
unset($item['animal']);

return $item;
}

$rows = array_map("keyrename", $rows);
unset($rows["id"]);
    $data = array();
    $data['types'] = $rows;

//	echo "Hello";
   print(json_encode($data));
}
catch(PDOException $e){
echo "Connection failed" . $e->getMessage();
}
     
   $pdo = null;
    ?>
