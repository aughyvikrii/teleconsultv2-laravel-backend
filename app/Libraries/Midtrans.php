<?php
namespace App\Libraries;
use \App\Models\Branch;

class Midtrans {

    public $branch;

    function __construct($branch) {
        if($branch instanceof Branch) {
            $this->branch = $branch;
        }
        else if ( is_integer($branch) ) {
            $branch = Branch::find($branch);
            if(!$branch) {
                $this->message = 'Schedule not found';
                $this->error = true;
            } else {
                $this->branch = $branch;
            }
        } else {
            $this->message = 'error: construct';
            $this->error = true;
        }


        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = $branch->midtrans_server_key;
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = ( config('app.env') == 'production' ? true : false );
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;
    }

    public function error() { return $this->error; }
    public function message() { return $this->message; }

    public function getSnapToken($params) {
        $res = $error = null;
        try {
            $res = \Midtrans\Snap::getSnapToken($params);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        } catch(\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return [$res, $error];
    }

    public function cancel($order_id) {
        $res = $error = null;
        try {
            $res = \Midtrans\Transaction::cancel($order_id);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        } catch(\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return [$res, $error];
    }

    public function expire($order_id) {
        $res = $error = null;
        try {
            $res = \Midtrans\Transaction::expire($order_id);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        } catch(\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return [$res, $error];
    }

    public function deny($order_id) {
        $res = $error = null;
        try {
            $res = \Midtrans\Transaction::deny($order_id);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        } catch(\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return [$res, $error];
    }

    public function status($order_id) {
        $res = $error = null;
        try {
            $res = \Midtrans\Transaction::status($order_id);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        } catch(\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return [$res, $error];
    }

    public function refund($order_id, $params = array()) {
        $res = $error = null;
        try {
            $res = \Midtrans\Transaction::refund($order_id, $params);
        } catch(\Exception $e) {
            $error = $e->getMessage();
        } catch(\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return [$res, $error];
    }

    public function verifySignature($request) {
    }
}