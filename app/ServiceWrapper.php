<?php declare(strict_types = 1);

namespace App;


class ServiceWrapper
{
    private $city_name,$api_key,$phone;
    public function __construct($city_name,$api_key,$phone){
        $this->city_name = $city_name;
        $this->api_key = $api_key;
        $this->phone = $phone;
    }

    public function handle(){
        $this->proceed();
    }
    private function proceed(){
        $forecast = new ForecastService($this->city_name,$this->api_key);
        $result = $forecast->handle();
        echo '<h1>Weather forecast API results</h1>';
        if(count($result['errors'])>0)
            foreach($result['errors'] as $error)
                $forecast->outPutMessage($error);
        if($result['temperature']){
            $forecast->outPutMessage((string)$result['temperature'].' Celcius degrees');
            $notification = new NotificationService($this->phone);
            $notification_result = $notification->handle($result['temperature']);
            echo '<h1>Routee API results</h1>';
            if($notification_result['errors']){
                foreach($notification_result['errors'] as $error){
                    $notification->outPutMessage($error);
                }
            }
            if(array_key_exists('trackingId',$notification_result) && $notification_result['status'] === 'Queued'){
                $notification->outPutMessage($notification_result['body']);
            }
        }
    }
}
