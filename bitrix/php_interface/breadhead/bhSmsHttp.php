<?php
/**
 * Created by PhpStorm.
 * User: Katrin
 * Date: 02.07.2015
 * Time: 18:56
 */

class bhSmsHttp
{

    // returns array(sms_id, error description)
    function send($phone, $message, $originator='GrtTidy', $rus=0, $udh='')
    {
        // constants
        // !!! Edit these contants after you receive an account from SMS Traffic !!!
        //$smstraffic_login = 'server1.smstraffic.ru';
        //$smppport = 4442;
        $smstraffic_login = 'novativegar';
        $smstraffic_password = "begibyfa";
        $max_parts = 10; // set to 2 or more if you want messages to be split into several SMS automatically

        $host = "www.smstraffic.ru";
        $failover_host = "server2.smstraffic.ru";

        $path = "/multi.php";
        $params = "login=".urlencode($smstraffic_login) . "&password=".urlencode($smstraffic_password) . "&want_sms_ids=1&phones=$phone&message=".urlencode($message) . "&max_parts=$max_parts&rus=$rus&originator=".urlencode($originator);
        if ($udh)
            $params .= "&udh=".urlencode($udh);

        $response=bhSmsHttp::httpPost($host, $path, $params);
        if ($response==null){
            $response=Sms::httpPost($failover_host, $path, $params);
            if ($response==null)
                return array(0, "failed to send sms");
        }

        // interpret response
        if (strpos($response, '<result>OK</result>')){
            if (preg_match('|<sms_id>(\d+)</sms_id>|s', $response, $regs)){
                $sms_id=$regs[1];
                return array($sms_id, 'OK');
            }
            else // impossible
                return array(-1, 'failed to find sms_id');
        }
        elseif (preg_match('|<description>(.+?)</description>|s', $response, $regs)){
            $error=$regs[1];
            return array(0, $error);
        }
        else
            return array(0, 'failed to send sms '.$response);
    }



    function httpPost($host, $path, $params)
    {

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        // 1. do HTTP POST via fsockopen. Uncomment this code and comment cURL code if cURL is not installed
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        $params_len=strlen($params);
        $fp = @fsockopen($host, 80);
        if (!$fp)
            return null;
        fputs($fp, "POST $path HTTP/1.0\nHost: $host\nContent-Type: application/x-www-form-urlencoded\nUser-Agent: sms.php class 1.0 (fsockopen)\nContent-Length: $params_len\nConnection: Close\n\n$params\n");
        $response = fread($fp, 8000);
        fclose($fp);
        if (preg_match('|^HTTP/1\.[01] (\d\d\d)|', $response, $regs))
            $http_result_code=$regs[1];
        return ($http_result_code==200) ? $response : null;

        // end of fsockopen code
        ///////////////////////////////////////////////////////////////////////////////////////////////////



        ///////////////////////////////////////////////////////////////////////////////////////////////////
        // 2. do HTTP POST via cURL. Uncomment this code and comment fsockopen code if cURL is installed
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        $protocol='http'; // alternatively, use https
        $ch = curl_init($protocol.'://'.$host.$path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // do not verify that ssl cert is valid (it is not the case for failover server)
        curl_setopt($ch, CURLOPT_USERAGENT, "sms.php class 1.0 (curl $protocol)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        ob_start();
        $bSuccess=curl_exec($ch);
        $response=ob_get_contents();
        ob_end_clean();
        $http_result_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($bSuccess && $http_result_code==200) ? $response : null;

        // end of cURL code
        ///////////////////////////////////////////////////////////////////////////////////////////////////

    }


}