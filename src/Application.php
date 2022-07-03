<?php
declare(strict_types=1);

namespace Stocktrading;

use DateTime;

class Application {

    //private $tradingLists;

    // public function __construct(array $tradingLists){
    //     $this->tradingLists=$tradingLists;
    // }

    public function init($data,$startDate,$endDate) : array{
        $finalStockList = $this->isProcessed($data,$startDate,$endDate);
        $stockCount = count($finalStockList);
        $msg = '';
        $result = array();
        if($stockCount<=1){
            $msg = "Wer't finding any trade available for the selected date.";
        } else {
            $result = $this->doCalculation($finalStockList);
        }
        return $result;
    }

    private function isProcessed($data,$startDate,$endDate) : array {
        $result = [];
        $startDateFormated = DateTime::createFromFormat('d-m-Y ', $startDate);
        $endDateFormated = DateTime::createFromFormat('d-m-Y ', $endDate);

        foreach($data as $element){
            $dateFormated = DateTime::createFromFormat('d-m-Y ', $element['date']);
            if (
                // Check if date is between two dates
                ($dateFormated  <= $startDateFormated) &&             
                ($dateFormated >= $endDateFormated)) 
            {
                //print_r($element);
                $result[] = $element;
            }
        }
        
        return $result;
    }


    private function doCalculation($stocks) : array{
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
}
