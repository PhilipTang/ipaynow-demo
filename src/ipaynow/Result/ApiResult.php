<?php

namespace ipaynow\Result;

use ipaynow\Core\IpayException;
use ipaynow\Http\ResponseCore;

class ApiResult
{

    function __construct(ResponseCore $response)
    {
        if ($response->isOK()) {
            $data = json_decode($response->body);
            return $data;
        } else {
            throw new IpayException('http status is not 2xx');
        }
    }
}
