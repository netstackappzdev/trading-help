<?php
require_once(__DIR__.'/vendor/autoload.php');

use Stocktrading\Application;
use Stocktrading\Validator;

//$csvAsArray = array_map('str_getcsv', file($tmpName));

$output = init($_REQUEST,$_FILES);

//header('Content-Type: application/json; charset=utf-8');
echo json_encode($output);


function init($request,$files){
    $msg = doValidation($request,$files);
    $stockPrices = array();
    if(empty($msg)){
        $stockCSVTmpName = $files['stockFile']['tmp_name'];
        $data = CSVToArray($stockCSVTmpName);
        if(isset($data[$request['stockName']])){
            $stockPrices = $data[$request['stockName']];    
            array_multisort(array_map(function($element) {        
                return $element['date'];
            }, $stockPrices), SORT_ASC, $stockPrices);  
            
            $app = new Application();
            $stockPrices = $app->init($stockPrices,$request['stockStartDate'],$request['stockEndDate']);
        }
    }
    return array(
        'reports'   => $stockPrices,
        'msg'       => $msg,
    );
}

function doValidation($request,$files){
    $msg = '';
    if(!Validator::validateDate($request['stockStartDate'])){
        $msg .= "Start Date is not valid. </br>";
    }
    if(!Validator::validateDate($request['stockEndDate'])){
        $msg .= "End Date is not valid. </br>";
    }
    $csv_mimetypes = array(
        'text/csv'
    );    
    if (!in_array($files['stockFile']['type'], $csv_mimetypes)) {
        $msg .=  "File must be valid CSV file. </br>";
    }
    if(empty($request['stockName'])){
        $msg .=  "Valid stock name is required. </br>";
    }
    if(isset($request['stockName'])){
        $peopleJson = file_get_contents('nasdaq-listed.json'); 
        $decodedJson = json_decode($peopleJson, false);
        $key = array_search($request['stockName'], array_column($decodedJson, 'Symbol'));
        if(empty($key)){
            $msg .=  "Choose a Valid Stock.</br>";
        }
    }
    return $msg;    
}

function CSVToArray($filename='', $delimiter=',') {
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {   $i=0;
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else{
                $id = $row[2]; // grab the ID from the row / expectiong that the ID is in the first column of your CSV
                //unset($row[0]); // <-- uncomment this line if you want to remove the ID from the data array
                $data[$id][$i] = array_combine($header, $row);
            }
            $i++;
                
        }
        fclose($handle);

    }
    return $data;
}

?>