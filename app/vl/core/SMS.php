<?php

namespace vl\core;

class SMS
{
    public $tenant = 41;                                                // Your Vocalogic API Login
    public $key = "5dd82664e76193f00f9b4a9c0ecd25a8";                 // Your Vocalogic API Password
    public $did = 39;
    const VOCASO_API = "http://www.vocaso.com/api/v1/sms";                    // API Location

    public function transmit($cmd, array $fields)
    {
        $url = self::VOCASO_API . "/$this->tenant/$this->did/$cmd";
       // print("Sending to $url");
        $fields_string = null;
        foreach ($fields AS $id => $field)
        {
            $fields_string .= "$id=" . urlencode($field) . "&";
        }
        $fields_string = substr($fields_string, 0, -1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
           "X-Auth-Token: $this->key"
        ]);
        $output = curl_exec($ch);
       // dd($output);
        curl_close($ch);
        return json_decode($output);
    }

    static public function command($command, array $args = [])
    {
        $api = new SMS;
        return $api->transmit('send', $args);
    }
}