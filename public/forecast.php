<?php declare(strict_types = 1);

require_once ('../app/ForecastService.php');
require_once ('../app/NotificationService.php');
require_once ('../app/ServiceWrapper.php');


session_start();
$timeInterval = '600';  //600 seconds is 10 minutes.
if($_GET['times_run'] >= 10){
    die('script finished: run 10 times');
}else{
    $count =0;
    if(array_key_exists('times_run',$_GET)){
        $count = intval($_GET['times_run']) +1;
    }
    $count=($count === 0)?1:$count;
    //use header to refresh page every timeInterval specified
    header('refresh:'.$timeInterval.'; url=forecast.php?times_run='.$count);

}
$wrapper = new \App\ServiceWrapper('Thessaloniki','b385aa7d4e568152288b3c9f5c2458a5','+306978745957');
$wrapper->handle();


