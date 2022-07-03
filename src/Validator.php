<?php

namespace Stocktrading;

use DateTime;


class Validator
{

    public static function validateDate($date, $format = 'm/d/Y')    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    

}