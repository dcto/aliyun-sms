<?php

namespace Varimax\Aliyun;

use AlibabaCloud\Client\AlibabaCloud;

class Sms {
    /**
     * @var array $config
     */
    protected $config = array(
        'accessKeyId' => null,
        'accessSecret'=> null,
        'host'        => 'dysmsapi.ap-southeast-1.aliyuncs.com',
        'action'      => 'SendMessageToGlobe',
        'regionId'    => 'cn-hangzhou',
        'version'     => '2018-05-01',
    );

    /**
     * request query
     */
    protected $query = array();


    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, $config);
    }


    /**
     * setting host
     */
    public function host($host)
    {
        return $this->config('host', $host);
    }

    /**
     * config setting
     */
    public function config($key, $value = null)
    {
        if(is_array($key)){
            $this->config = array_merge($this->config, $key);
        }else if($value){
            $this->config[$key] = $value;
        }else{
            return $this->config;
        }

        return $this;
    }

    /**
     * phone alias name
     */
    public function to($number)
    {
        return $this->phone($number);
    }

    /**
     * template param alias name
     */
    public function with($key, $value = null)
    {
       return $this->query($key, $value);
    }

    /**
     * set parameters 
     */
    public function query($key, $value = null)
    {
        if(is_array($key)){
            $this->query = array_merge($this->config, $key);
        }else if($value){
            $this->query[$key] = $value;
        }else{
            return $this->query;
        }
        return $this;
    }

    /**
     * 
     * @param mixed $action 
     * @return array|$this 
     */
    public function action($action)
    {
        return $this->config('action', $action);
    }

    /**
     * From number
     * @param mixed $number 
     * @return array|$this 
     */
    public function from($number)
    {
        return $this->with('From', $number);
    }

    /**
     * set phone number
     */
    public function phone($number)
    {
        $this->with('To', $number);
        return $this;
    }

    /**
     * set template code
     */
    public function batch($template, $parameters = null)
    {
        $this->config('TemplateCode', $template);
        $parameters && $this->config('TemplateParam', $parameters);
        return $this;
    }

    public function message($content)
    {
        return $this->with('Message', $content);
    }

    /**
     * send sms
     * @return bool
     */
    public function send($phone = null, $message)
    {
        return $this->with('To', $phone)->with('Message', $message)->client()->request();
    }

    /**
     * 
     * @return void 
     */
    public function client()
    {
        // Download：https://github.com/aliyun/openapi-sdk-php-client
        // Usage：https://github.com/aliyun/openapi-sdk-php-client/blob/master/README-CN.md
        AlibabaCloud::accessKeyClient($this->config('accessKeyId'), $this->config('accessSecret'))
                        ->regionId($this->config('regionId')) 
                        ->asGlobalClient();
        return AlibabaCloud::rpcRequest()->product('Dysmsapi')
                            ->host($this->config('host'))
                            ->version($this->config('version'))
                            ->action($this->config('action'))
                            ->method('POST')
                            ->options([
                                'query' => $this->query,
                            ]);

    }
}