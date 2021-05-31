<?php declare(strict_types = 1);

/**
 * Created by PhpStorm.
 * User: ashirion
 * Date: 30/5/2021
 * Time: 5:55 μμ
 */

namespace App;


class BaseService
{
    /**
     * @param string $url   this is request uri
     * @param string $method it is an http verb defaults to "GET"
     * @param null $post is an array of key=>value pairs used on CURLOPT_POSTFIELDS
     * @param null $options  is an array of curl set options. The keys should be valid curl_setopt constants or
     * their integer equivalents.
     * @return array
     */
    protected function curler(string $url, $method ="GET",$post = null, $options = null)
    {
        //curler function works for GET and POST requests.
        //todo  testing on other HTTP VERBS such as PUT might be needed before using it.
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($method !== "GET") {
                //if it is not a GET method it is assumed that it is a POST adding postfields in curl Request.
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
            if (!is_null($options)) {
                foreach ($options as $key => $option) {
                    curl_setopt($ch, $key, $option);
                }
            }

            // execute!
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            if (!empty(curl_error($ch))) {
                // do anything you want with your error and curl info
                return ['error' => curl_error($ch), 'info' => $info];
            }
            // close the connection, release resources used
            curl_close($ch);
            // do anything you want with your response and curl info
            return ['response' => $response, 'info' => $info];
        }catch(\Exception $e){
            //capture exception error for handling in the other Service component classes.
            return ['error' => $e->getMessage()];
        }
    }
    //getsResponse from successful request without curl info
    protected function getResponse($result){
        if(!empty($result) && array_key_exists('response',$result)){
            return json_decode($result['response'],true);
        }
    }

    //output function for browser display
    public function outPutMessage(string $message){
        echo'<p>'.$message.'</p>';
    }
}
