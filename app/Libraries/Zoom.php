<?php

namespace App\Libraries;

use App\Models\{Person};
use Illuminate\Support\Facades\Http;

class Zoom {

    public $userId = null;
    
    public $apiKey = null;

    public $apiSecret = null;

    public $jwtToken = null;

    public $person = null;

    public $message = null;

    public $error = null;

    function __construct($person=array()) {

        if(!$person instanceof Person) {
            if ( is_integer($person) ) {
                $person = Person::find($person);
                if(!$person) {
                    $this->message = 'Schedule not found';
                    $this->error = true;
                }
            }
        }

        if(is_array($person)) $person = (Object) $person;

        // Account ID
        if(@$person->zoom_user_id) $this->userId = @$person->zoom_user_id;
        else if (@$person->account_id) $this->userId = @$person->account_id;

        // ApiKey
        if(@$person->zoom_api_key) $this->apiKey = @$person->zoom_api_key;
        else if(@$person->api_key) $this->apiKey = @$person->api_key;

        // ApiSecret
        if(@$person->zoom_api_secret) $this->apiSecret = @$person->zoom_api_secret;
        else if(@$person->api_secret) $this->apiSecret = @$person->api_secret;

        // JWTToken
        if(@$person->zoom_jwt_token) $this->jwtToken = @$person->zoom_jwt_token;
        else if(@$person->jwt_token) $this->jwtToken = @$person->jwt_token;
    }
    
    public function error() { return $this->error; }
    public function message() { return $this->message; }

    public function setData($data=array()) {
        $this->__construct($data);
        return $this;
    }

    public function jwtInfo() {
        $split = explode(".", $this->jwtToken);
        $header = @$split[0];
        $payload = @$split[1];
        $signature = @$split[2];

        try {
            $payload = base64_decode($payload);
            $json_payload = json_decode($payload, 1);
        } catch(\Exception $e) {
            $error = $e->getMessage();
            $json_payload = [];
        }

        if(!$json_payload) return false;

        $json_payload['exp_date'] = date('Y-m-d H:i:s', @$json_payload['exp']);

        return $json_payload;
    }

    public function userInfo($email=null) {

        $tokenInfo = $this->jwtInfo();

        if(!@$tokenInfo['exp_date']) return false;

        $request = Http::withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}"
        ])->get('https://api.zoom.us/v2/users');

        $response = $request->json();

        if(!$response) return false;

        if(!@$response['users']) return false;

        $account = null;

        if(!@$response['total_records'] == '1') {
            if($email) {
                foreach(@$response['users'] as $item) {
                    if($item['email'] == $email) {
                        $account = $item;
                        break;
                    }
                }
            } else $account = $response['users'][0];
        } else $account = $response['users'][0];


        return [
            'account' => $account,
            'token'=> $tokenInfo
        ];
    }

    public function createMeeting($postData=array()) {

        $request = Http::withHeaders([
            'Authorization' => "Bearer {$this->jwtToken}"
        ])->post("https://api.zoom.us/v2/users/{$this->userId}/meetings", $postData);

        $response = $request->json();

        if(!$response) return false;

        return $response;
    }
}