<?php

use Auth;

if(!defined('__secret_app_key__'))  {
    define('__secret_app_key__', 'yOKMFcJVeOWx8kpDGIWNOlSvEjm12wMPT6BWdhdOY323sFE8BEfW2oThs9Sg19zN');
}

if(!defined('__iv_app_key__')) {
    define('__iv_app_key__', 'eRtd26Y7evRamm6i');
}

/**
 * Helper function
 * by aughyvikrii
 */

if(!function_exists('format_phone')) {
    /**
     * Format string untuk nomor hp +62
     */

    function format_phone(string $string, bool $format62=true): string {
        $string = preg_replace('/[^0-9]/', '', $string);
        if($format62=='62') {
            if(substr($string, 0,2) == '08') return "62" . substr($string,1);
            else return $string;
        } else {
            if(substr($string, 0,2) == '62') return "0" . substr($string,2);
            else return $string;
        }
    }
}

if(!function_exists('_encode')) {
    /**
     * Simple encode string with base64 key
     */

    function _encode($value) {
        if(is_array($value)) $value = json_encode($value);
        // Store the cipher method 
        $ciphering = "AES-128-CTR"; 
        
        // Use OpenSSl Encryption method 
        $iv_length = openssl_cipher_iv_length($ciphering); 
        $options = 0; 
        
        // Non-NULL Initialization Vector for encryption 
        $encryption_iv = __iv_app_key__; 
        
        // Store the encryption key 
        $encryption_key = __secret_app_key__; 
        
        // Use openssl_encrypt() function to encrypt the data 
        $encryption = openssl_encrypt($value, $ciphering, $encryption_key, $options, $encryption_iv); 
        return base64_encode($encryption);

        /*
            if (!$value) {
                return false;
            }
        
            $key = sha1(__secret_app_key__);
            $strLen = strlen($value);
            $keyLen = strlen($key);
            $j = 0;
            $crypttext = '';
        
            for ($i = 0; $i < $strLen; $i++) {
                $ordStr = ord(substr($value, $i, 1));
                if ($j == $keyLen) {
                    $j = 0;
                }
                $ordKey = ord(substr($key, $j, 1));
                $j++;
                $crypttext .= strrev(base_convert(dechex($ordStr + $ordKey), 16, 36));
            }
        
            return $crypttext;
        */
    }
}

if(!function_exists('_decode')) {
    /**
     * Simple decode string from _encode
     */

    function _decode($value) {
        $ciphering = "AES-128-CTR"; 
        
        // Use OpenSSl Encryption method 
        $iv_length = openssl_cipher_iv_length($ciphering); 
        $options = 0; 

        // Non-NULL Initialization Vector for decryption 
        $decryption_iv = __iv_app_key__; 
        
        // Store the decryption key 
        $decryption_key = __secret_app_key__; 
        
        // Use openssl_decrypt() function to decrypt the data 
        $decryption=openssl_decrypt ( base64_decode($value) , $ciphering, $decryption_key, $options, $decryption_iv); 
        
        return $decryption;

        /* 
            if (!$value) {
                return false;
            }
        
            $key = sha1(__secret_app_key__);
            $strLen = strlen($value);
            $keyLen = strlen($key);
            $j = 0;
            $decrypttext = '';
        
            for ($i = 0; $i < $strLen; $i += 2) {
                $ordStr = hexdec(base_convert(strrev(substr($value, $i, 2)), 36, 16));
                if ($j == $keyLen) {
                    $j = 0;
                }
                $ordKey = ord(substr($key, $j, 1));
                $j++;
                $decrypttext .= chr($ordStr - $ordKey);
            }
        
            return $decrypttext;
         */
    }
}

if(!function_exists('array_match_key')) {
    function array_match_key($array, array $column): array {
        if(!is_array($array)) {
            try {
                $array = $array->toArray();
            } catch (\Exception $e) {
                $array = (array) $array;
            }
        }

        $flip = array_fill_keys($column, NULL);
        return array_intersect_key((array) $array, $flip);
    }
}

if(!function_exists('person_level')) {
    function person_level($pid) {
        if(is_object($pid)) {
            $pid = @$pid->pid;
        }

        $query = \App\Models\Person::joinUser('left')
                ->select('users.lid')
                ->whereRaw('persons.pid = ?', [$pid])
                ->first();
        return $query->lid;
    }
}

if(!function_exists('is_admin')) {
    function is_admin($person=false) {
        if(!$person) {
            $level = @Auth::user()->lid;
        } else {
            $level = person_level($person);
        }
        return $level === '1' ? true : false;
    }
}

if(!function_exists('is_doctor')) {
    function is_doctor($person) {
        $level = person_level($person);
        return $level === '2' ? true : false;
    }
}

if(!function_exists('is_patient')) {
    function is_patient($person) {
        $level = person_level($person);
        return $level === '3' ? true : false;
    }
}

if(!function_exists('profile_pic')) {
    function profile_pic($person) {
        if($person->profile_pic) {
            $file_name = $person->profile_pic;
        } else {

            $level = person_level($person);
            if($level == '1') {
                $file_name = 'admin.png';
            }
            else if ($level == '2') {
                $file_name = $person->gid == '2' ? 'doctor-female.png' : 'doctor-male.png';
            }
            else if ($level == '3') {
                $file_name = $person->gid == '2' ? 'patient-female.png' : 'patient-male.png';
            }

        }


        return asset('storage/img/profile/'. $file_name);
    }
}