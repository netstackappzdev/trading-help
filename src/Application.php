<?php
declare(strict_types=1);

namespace StockTrading;
use DateTime;

class Application {

    function isProcessed($data,$startDate,$endDate){
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
}
