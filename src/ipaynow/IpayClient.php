<?php

namespace ipaynow;

use ipaynow\Core\IpayException;
use ipaynow\Http\RequestCore;
use ipaynow\Http\ResponseCore;
use ipaynow\Result\ApiResult;

class ipaynow
{
    const BASE_URL = "http://121.42.52.69:3005";

    static $_client;

    private $_headers = array(
        'x-auth-token' => '',
        'version' => 'server_rp_3.0.0',
        'device-id' => '',
        'User-Agent' => '',
        'request-id' => '',
    );

    /* 
TODO(Zhijie Deng): 调用相关 REST API
1. 获得红包 ID：http.post('/api/hongbao/generate-id')
2. 获得红包配置：http.get('/api/hongbao/settings')
3. 用户填写红包金额、祝福语、收件人，选择支付方式
4. 若用户使用零钱支付，填写支付密码
5. 若用户使用京东支付，调用京东支付相关 API，获得成功的流水号
6. 获得红包 ID：http.post('/api/hongbao/generate-id')
7. 将红包ID、金额、祝福语、收件人、支付相关参数 提交：http.post('/api/hongbao/send', data)
8. 提交后，会获得红包 ID，将红包 ID 通过 IM 等方式发给收件人
     */

    static function getInstance($options)
    {
        if (empty(self::$_client)) {
            self::$_client = new self($options);
        }
        return self::$_client;
    }

    function __construct($options)
    {
        // 初始化 token
        // 每次都获取一次 token
        $this->_headers['x-auth-token'] = $this->getToken();
        $this->_headers['User-Agent'] = $this->getUserAgent();
        $this->_headers['version'] = $options['version'];
        $this->_headers['device-id'] = $options['deviceId'];
        return $this;
    }

    public function getToken()
    {
        // todo 
        return 'foo';
    }

    public function getipaynowId()
    {
        // 每个请求都创建 request_id
        $this->_headers['request-id'] = $this->getRequestId();

        //
        $options['method'] = 'GET';
        $options['object'] = '/api/hongbao/generate-id';
        $options['data'] = '/';
        $response = $this->auth($options);
        return new ApiResult($response);
    }

    private function auth($options)
    {
        $requestUrl = self::BASE_URL . $options['object'];
        $request = new RequestCore($requestUrl);
        $request->timeout = 0;
        $request->connect_timeout = 0;
        $request->set_useragent($this->generateUserAgent());
        $request->set_method($options['method']);
        $request->add_header('Content-Type', 'application/x-www-form-urlencoded');
        try {
            $request->send_request();
        } catch (RequestCore_Exception $e) {
            throw(new IpayException('RequestCoreException: ' . $e->getMessage()));
        }
        $response_header = $request->get_response_header();
        $response_header['request-url'] = $requestUrl;
        $response_header['request-headers'] = $request->request_headers;
        $data = new ResponseCore($response_header, $request->get_response_body(), $request->get_response_code());
        return $data;
    }

    function hello()
    {
        return 'hello';
    }

    /**
     * 用来检查sdk所以来的扩展是否打开
     *
     * @throws IpayException
     */
    public static function checkEnv()
    {
        if (function_exists('get_loaded_extensions')) {
            //检测curl扩展
            $enabled_extension = array("curl");
            $extensions = get_loaded_extensions();
            if ($extensions) {
                foreach ($enabled_extension as $item) {
                    if (!in_array($item, $extensions)) {
                        throw new IpayException("Extension {" . $item . "} is not installed or not enabled, please check your php env.");
                    }
                }
            } else {
                throw new IpayException("function get_loaded_extensions not found.");
            }
        } else {
            throw new IpayException('Function get_loaded_extensions has been disabled, please check php config.');
        }
    }

    /**
     * 生成请求用的 UserAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return "redpacket-sdk-php/1.0" . " (" . php_uname('s') . "/" . php_uname('r') . "/" . php_uname('m') . ";" . PHP_VERSION . ")";
    }


}
