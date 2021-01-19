<?php

namespace App\Libraries;

class Whatsapp {

    protected $host = null;
    protected $api_key = null;
    protected $device_key = null;

    function __construct() {
        $config = config('wablastgo');
        if($config) foreach($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function mainCurl($endpoint, $data) {
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,  rtrim($this->host,'/') . '/' . ltrim($endpoint,'/') );
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($ch);
                curl_close($ch);

        return json_decode($result,true);
    }

    public function sendMessage($phone, $message) {
        $request = $this->mainCurl('send-message',[
            'api_key' => $this->api_key,
            'device_key' => $this->device_key,
            'destination' => $phone,
            'message' => $message
        ]);

        return $request;
    }

    public static function send($phone, $message) {
        $class = new Whatsapp;
        return $class->sendMessage($phone, $message);
    }
}