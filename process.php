<?php
//require_once(__DIR__.'/vendor/autoload.php');

//use StockTrading\Application;

$tmpName = $_FILES['stockFile']['tmp_name'];
$csvAsArray = array_map('str_getcsv', file($tmpName));

//doValidation($_REQUEST);

$CSVdata = CSVToArray($tmpName);
$finalStockList = init($CSVdata,$_REQUEST);

$stockCount = count($finalStockList);
$msg = '';
$reports = array();
if($stockCount<=1){
    $msg = "Wer't finding any trade available for the selected date.";
} else {
    //$reports = profitCalculation($finalStockList);
    $reports = doCalculation($finalStockList);
}

$return = array(
    'buy-date'  => '', // day you should've bought, so it price should be low
    'sell-date' => '', // day you should've sold, so its price should be high so you make profit
    'profit'    => '',  // value of profit
    //'request'   => $_REQUEST,
    //'file'      => $csvAsArray,
    //'arr'       => $arr,
    'reports'   => $reports,
    'msg'       => $msg,
);

//header('Content-Type: application/json; charset=utf-8');
echo json_encode($return);

function doValidation($request){
    //throw new Exception("Value must be 1 or below");
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

function init($data,$request){
    $stockPrices = array();
    if(isset($data[$request['stockName']])){
        $stockPrices = $data[$request['stockName']];    
        array_multisort(array_map(function($element) {        
            return $element['date'];
        }, $stockPrices), SORT_ASC, $stockPrices);  
        
        //$app= new Application();
        $stockPrices = isProcessed($stockPrices,$request['stockStartDate'],$request['stockEndDate']);
    }
    return $stockPrices;
}

function isProcessed($stocks,$startDate,$endDate){

    $startDateFormated = date('Y-m-d', strtotime($startDate));
    $endDateFormated = date('Y-m-d', strtotime($endDate));

    $result = array();
    foreach($stocks as $element){
        $dateFormated=date('Y-m-d', strtotime($element['date']));
            
        // Check if date is between two dates
        if (($dateFormated >= $startDateFormated) && ($dateFormated <= $endDateFormated)){
            $result[] = $element;
        }
    }
    return $result;
}

function doCalculation($stocks){
    $tradingDates=array();
    $buyDate= $sellDate = '';
    $localMinima=max(array_column($stocks, 'price'));
    $localMaxima=min(array_column($stocks, 'price'));
    $mean= $meanforVariance= $standardDeviation= $stockCount = $totalProfit = $variance= 0;
    $status='recent_lowest_trade_price';  //recent_Highest_trade_price find_profit

    foreach($stocks as $item){

        $mean+=$item['price'];
        $stockCount++;
        $delta=$item['price']-$meanforVariance;
        $meanforVariance+=$delta/$stockCount;
        $variance+=$delta*($item['price']-$meanforVariance);

        if($status=='recent_lowest_trade_price'){
            if($localMinima>$item['price']){
                $localMinima=$item['price'];
                $buyDate=$item['date'];
                // echo "local_minima $localMinima $buyDate <br/>";
            }else{
                $status='recent_Highest_trade_price';
            }
        }

        if($status=='recent_Highest_trade_price'){
            if($localMaxima<$item['price']){
                $localMaxima=$item['price'];
                $sellDate=$item['date'];
                //echo "recent_Highest_trade_price $localMinima $buyDate <br/>";
            }else{
                $status='find_profit';
            }
        }

        if($status=='find_profit'){
            $profit=$localMaxima-$localMinima;
            $totalProfit+=$profit;
            array_push($tradingDates,[
                'buy_date'=>$buyDate,
                'sell_date'=>$sellDate,
                'profit'=>$profit
            ]);
            $localMinima=$item['price'];
            $buyDate=$item['date'];
            $localMaxima=min(array_column($stocks, 'price'));
            $status='recent_lowest_trade_price';
        }
    }
    

    if($status=='recent_Highest_trade_price'){
        $profit=$localMaxima-$localMinima;
        $totalProfit+=$profit;
        array_push($tradingDates,[
            'buy_date'=>$buyDate,
            'sell_date'=>$sellDate,
            'profit'=>$profit
        ]);
    }

    if($stockCount>0){
        $mean=$mean/$stockCount;
    }

    if($stockCount>1){
        $variance=$variance/($stockCount-1);
        $standardDeviation=sqrt($variance);
    }


    return [
        'mean'=>$mean,
        'standard_deviation'=>$standardDeviation,
        'total_profit'=>$totalProfit,
        'trading_dates'=>$tradingDates
    ];
}



?>