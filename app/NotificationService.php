<?php declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: ashirion
 * Date: 30/5/2021
 * Time: 5:39 μμ
 */

namespace App;


class NotificationService extends BaseService
{

    private $encoded_credentials, $access_token,$phone;
    //todo swap the production credentials with dummy credentials when needing a new access token.
    //production credentials
    //const APPLICATIONID = '5f9138288b71de3617a87cd3',SECRET = 'RSj69jLowJ';
    //dummy credentials
    const APPLICATIONID = '1f1111888b71de3617a87cd3',SECRET = 'vjaj69jowJ';
    public function __construct($phone)
    {
        $this->encoded_credentials = base64_encode(self::APPLICATIONID.":".self::SECRET);
        $this->phone = $phone;
    }
    private function call($url,$method,$post_data,$headers){
        $response = $this->curler($url,$method,$post_data,$headers);
        return $response;
    }

    private function authorizationRequest(){
        $headers = [CURLOPT_HTTPHEADER => [
            "authorization: Basic ".$this->encoded_credentials,
            "content-type: application/x-www-form-urlencoded"
        ],
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30];
        $post = http_build_query(["grant_type"=>"client_credentials"]);
        $response = $this->call("https://auth.routee.net/oauth/token","POST",$post,$headers);
        return $response;
    }
    private function messageRequest(float $temperature){
        $headers = [CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".$this->access_token['token'],
            "content-type: application/json"
        ),
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30];
        /*$post = addcslashes(json_encode(["body"=>$this->bodyMessage($temperature),"to" =>$this->phone,"from" => "amdTelecom"]),'"');
        var_dump($post);echo '<hr>';*/
        $post = (json_encode(["body"=>$this->bodyMessage($temperature),"to" =>$this->phone,"from" => substr("IonAssign",0,11)]));


        $response = $this->call("https://connect.routee.net/sms","POST",$post,$headers);
        var_dump($post,$headers);
        echo '<hr>';
        return $response;
    }

    public function handle($temperature){
        $errors = [];

        //new access_token:  d584066d-49dc-4799-81b4-55a8e73c5fcc

        if(array_key_exists('access_token',$_SESSION))
            $this->access_token = $_SESSION['access_token'];
        //if there is no access_token in runtime or
        // if access_token is expired or malformed request new access_token.
        if (empty($this->access_token) || (!strlen($this->access_token['token']) > 8 &&
            !$this->tokenIsActive($this->access_token["expiration_timestamp"]))){
            $result = $this->authorizationRequest();
            if ($result['error']) {
                return $result['error'];
            }
            $response = $this->getResponse($result);
            if(!$this->validateCall($response)){
                //$response['message'] contains error message from API.
                $errors[] = 'Failure to retrieve data from the Routee API - error message:'.$response['message'];
                return ['errors' => $errors];
            }

            //writes access_token to session
            $this->writeToSession($response);
        }
        if($this->access_token){
            //if access token already exists in memory
                $this->phone = $this->sanitizePhoneNumber($this->phone);
                if(!$this->phone){
                    $errors[] = 'Phone is in wrong format';
                    return ['errors' => $errors];
                }

            $result = $this->messageRequest($temperature);
            $response = $this->getResponse($result);
            if(!$this->validateCall($response)){
                //unify error messages to return for output handling on the ServiceWrapper
                if(array_key_exists('developerMessage',$response))
                    $response['message'] = $response['developerMessage'];
                if(array_key_exists('error',$response))
                    $response['message'] = $response['error'];
                //$response['message'] contains error message from API.
                $errors[] = 'Failure to retrieve data from the Routee API - error message:'.$response['message'];

            }
            if($response['trackingId']){
                //contains successful message to be sent by the API
                return $response;
            }
            if(count($errors)>0){
                return ['errors' => $errors];
            }

        }
    }

    private function validateCall($response){
        //checks the integrity of the response.
        if(intval($response['code']) === 400000000){
            return false;
        }
        //checks the integrity of the response.
        if($response['error']){
            return false;
        }
        //asume that anything above 300 status code is unexpected behavior.
        if(intval($response['status'])>=300){
            return false;
        }
        return true;
    }

    private function tokenIsActive($timestamp){
        return (floatval($timestamp) - strtotime('now') >0)?true:false;
    }
    private function sanitizePhoneNumber(string $phone){
        preg_match('/^\+?[1-9]\d{1,14}$/',$phone,$matches);
            return (count($matches)>0)?$matches[0]:false;
    }
    private function bodyMessage(float $temperature){
        if($temperature>20)
            return "Ion Perakis and Temperature more than 20C. ".(string)$temperature. " Celcius";
        else
            return "Ion Perakis and Temperature lower than 20C. ".(string)$temperature. " Celcius";
    }

    private function writeToSession($response){
        //pass the access token and expiry time within the runtime memory and session
        if(array_key_exists("access_token",$response) &&
            $this->tokenIsActive(strtotime("now") + $response['expires_in'])) {
            $this->access_token = [
                'token' => $response['access_token'],
                'expiration_timestamp' => strtotime("now") + $response['expires_in']
            ];
            $_SESSION['access_token']= $this->access_token;
            session_write_close();
        }
    }
}
