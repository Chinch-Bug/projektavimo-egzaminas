<?php
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST');

    $host = "5.199.161.42:3306";
    $username = "tester";
    $password = "5DMW5fV2jroGGpQO";
    $dbname = "homelessbeavers";
     
    try{

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $q = $pdo->prepare("SELECT * FROM reports order by timestamp desc");
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    $userlat = (float)$_POST['latitude'];
    $userlong = (float)$_POST['longitude'];

    function distance($lat1, $lon1, $lat2, $lon2) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
          return 0;
        }
        else {
          $theta = $lon1 - $lon2;
          $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
          $dist = acos($dist);
          $dist = rad2deg($dist);
          $miles = $dist * 60 * 1.1515;
      
          return ($miles * 1.609344);
        }
    }

    foreach($rows as &$row){
    $row['distance'] = distance($userlat, $userlong, (float)$row['lat'], (float)$row['longi']);
    }
    
    //print(json_encode($rows)."<br><br>");

    $length = sizeof($rows);
    for ($i = 0 ; $i < $length ; $i++){
      if($rows[$i]['distance'] > 1){
        //print(json_encode($rows[$i]['distance'])."<br><br>");
        for($j = $i ; $j < $length-1 ; $j++){
                $rows[$j] = $rows[$j+1];
            }
        //print($rows[$length-2]['descr']."<br><br>");
        $length--;
        $i--;
      }
    }

    for($i = 0; $i < $length ; $i++){
      $filtered[] = $rows[$i];
    }

    //print(json_encode($rows)."<br><br>");
    
    function minRunLength($n){
        $r = 0;
        while ($n >= 32)
        {
            $r |= ($n & 1);
            $n >>= 1;
        }
        return $n + $r;
    } 

    function insertionSort(&$arr,$left,$right){
        for($i = $left + 1; $i <= $right; $i++)
        {
            $temp = $arr[$i];
            $j = $i - 1;
            
            while ($j >= $left && $arr[$j]['distance'] > $temp['distance'])
            {
                $arr[$j + 1] = $arr[$j];
                $j--;
            }
            $arr[$j + 1] = $temp;
                //print(json_encode($arr)."<br><br>");
        }
                //print(json_encode($arr)."<br><br>");
    }

    function merge(&$arr, $l, $m, $r)
    {
        $len1 = $m - $l + 1;
        $len2 = $r - $m;
        $left = array_fill(0, $len1, 0);
        $right = array_fill(0, $len2, 0);
        for($x = 0; $x < $len1; $x++)
        {
            $left[$x] = $arr[$l + $x];
        }
        for($x = 0; $x < $len2; $x++)
        {
            $right[$x] = $arr[$m + 1 + $x];
        }
    
        $i = 0;
        $j = 0;
        $k = $l;
    
        while ($i < $len1 && $j < $len2)
        {
            if ($left[$i]['distance'] <= $right[$j]['distance'])
            {
                $arr[$k] = $left[$i];
                $i++;
            }
            else
            {
                $arr[$k] = $right[$j];
                $j++;
            }
            $k++;
        }
    
        while ($i < $len1)
        {
            $arr[$k] = $left[$i];
            $k++;
            $i++;
        }
    
        while ($j < $len2)
        {
            $arr[$k] = $right[$j];
            $k++;
            $j++;
        }
    }

    function  timSort(&$arr, $n)
    {
        $minRun = minRunLength(32);
            
        for($i = 0; $i < $n; $i += $minRun)
        {
            if($i+31 > $n-1){
              insertionSort($arr, $i, $n-1);
            }  
            else{
              insertionSort($arr, $i, $i+31);
            }
          //print(json_encode($arr)."<br><br>");
        }
    
        for($size = $minRun; $size < $n; $size = 2 * $size)
        {
            
            for($left = 0; $left < $n;
                            $left += 2 * $size)
            {
    
                $mid = $left + $size - 1;
                $right =min(($left + 2 * $size - 1),
                                        ($n - 1));
    
                if($mid < $right)
                    merge($arr, $left, $mid, $right);
            }
        }
    }

    timSort($filtered, $length);

    function formMessage($filtered, $length){
      if($length < 1){
	$data[] = ["N/A", "No reports found"];
	}
      for($i = 0; $i < $length ; $i++){
        $data[][] = number_format($filtered[$i]['distance']*1000, 2, ",", "")."m away";
      }
      for($i = 0; $i < $length ; $i++){
        $message = "An animal was reported ";
        
        if($filtered[$i]['reptype'] == "Missing Animal"){
          $message = $message."missing ";
        }
        else{
          $message = $message."as found ";
        }

        $message = $message."at ".$filtered[$i]['timestamp'].". The following description of the ".strtolower($filtered[$i]['animaltype'])." was provided: ".$filtered[$i]['descr'].". Contact information currently unavailable.";

        $data[$i][] = $message;
      }

      return $data;
    }

    //print(json_encode($filtered));

    $data = formMessage($filtered, $length);
    $output['reports'] = $data;

    print(json_encode($output));
    }
    catch(PDOException $e){
        echo "Error" . $e->getMessage();
    }

    $pdo = null;
    

    ?>
