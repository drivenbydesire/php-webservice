<?php
/**
 * Created by PhpStorm.
 * User: sayantan
 * Date: 5/4/16
 * Time: 12:38 PM
 */

class curl_calls
{
    /**
     * Function to post data to restful API
     * Using Post Method without API_Key
     * @param $url -> Url of API to send post data
     * @param $params -> Aray with key value pair of data to be sent
     */

    public function post_curl($url, $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);


        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query($params));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        //return $server_output;

        // further processing ....
        $output = json_decode($server_output, true);
        return $output;
        //return $server_output;


    }


    /**
     * Function to post data to restful API
     * Using Post Method with API_Key
     * @param $url -> Url of API to send post data
     * @param $params -> Aray with key value pair of data to be sent
     * @param $api_key -> API key of user
     */

    public function post_curl_with_api($url, $params, $api_key)
    {
        $headers = array();
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
        $headers[] = 'Authorization: ' . $api_key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query($params));


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        $output = json_decode($server_output, true);
        return $output;

    }
}
