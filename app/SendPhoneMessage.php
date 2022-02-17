<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class SendPhoneMessage extends Model
{
    public static function whatsAppMessaging($phoneNumber, $message) {

        $userkey = '6c1174161f07';
        $passkey = '1f80e0e7ea7cdf7df84afc1f';

        $sms = array(
            'userkey' 	=> $userkey,
            'passkey' 	=> $passkey,
            'to' 		=> $phoneNumber,
            'message' 	=> $message,
            );

        //------------------CURL WA-----------------------
        $url          = "https://console.zenziva.net/wareguler/api/sendWA/";
        $method       = "POST";
        //---------------------------------------------
        $res_sms = set_curl_sms($url,$method,$sms);
        return $res_sms;

    }

    public static function smsMessaging($phoneNumber, $message) {

        $userkey = '6c1174161f07';
        $passkey = '1f80e0e7ea7cdf7df84afc1f';

        $sms = array(
            'userkey' 	=> $userkey,
            'passkey' 	=> $passkey,
            'to' 		=> $phoneNumber,
            'message' 	=> $message,
            );

        //------------------CURL SMS-----------------------
        $url          = "https://console.zenziva.net/reguler/api/sendsms/";
        $method       = "POST";
        //---------------------------------------------
        $res_sms = set_curl_sms($url,$method,$sms);
        return $res_sms;

    }
}
