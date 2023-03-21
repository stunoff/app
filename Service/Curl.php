<?php

namespace App\Service;

class Curl
{
    private $curl;

    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Pragma: no-cache"));
    }

    public function setUrl($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
    }

    public function setData($data)
    {
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
    }

    public function sendRequest()
    {
        return curl_exec($this->curl);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}
