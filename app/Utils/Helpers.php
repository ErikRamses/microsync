<?php

/*
|--------------------------------------------------------------------------
| Global Helper methods
|--------------------------------------------------------------------------
*/

function getDateTimeByTimeZone($date, $timezone)
{
    $date = new DateTime($date);
    $date->setTimezone(new DateTimeZone($timezone));

    return $date->format('d-m-Y H:i:s');
}
