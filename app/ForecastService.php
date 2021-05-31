<?php declare(strict_types = 1);

namespace App;
require_once ('BaseService.php');

class ForecastService extends BaseService
{
    private $city_name,$api_key;
    public function __construct(String $city_name,String $api_key)
    {
        $this->city_name = $city_name;
        $this->api_key = $api_key;
    }

    private function call(){
        return $this->curler("https://api.openweathermap.org/data/2.5/weather?q=".$this->city_name."&appid=".$this->api_key);
    }


    public function handle(){
        $errors = [];
        $result = $this->call();
        if($result['error']){
            return $result['error'];
        }
        $response = $this->getResponse($result);


        if(!$this->validateCall($response)){
            //$response['message'] contains error message from API.
            $errors[] = 'Failure to retrieve data from the Weather Forecast API - error message:'.$response['message'];
            return ['errors' => $errors];
        }
        if(!array_key_exists('main',$response)||!array_key_exists('temp',$response['main'])){
            $errors[] = 'Malformed response from openweathermap.org';
            /*$this->outPutMessage('Malformed response from openweathermap.org');*/
            /*return false;*/
        }

        $temperature_celcius = $this->KelvinToCelsius(floatval($response['main']['temp']));
        return ['temperature' => $temperature_celcius,'errors' => $errors];
    }

    private function validateCall($response){
        //checks the integrity of the response.
        //asume that anything above 300 status code is unexpected behavior.

        if(intval($response['cod'])>=300){
            return false;
        }
        return true;
    }

    private function KelvinToCelsius(float $temperature){
        return $temperature = $temperature - 273.15;
    }

}
