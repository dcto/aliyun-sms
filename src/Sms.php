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
        'action'      => null,
        'host'        => 'dysmsapi.cn-hangzhou.aliyuncs.com',
        'from'        => '短信签名',//送方标识。发往中国传入签名，请在控制台申请短信签名；发往非中国地区传入senderId。
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
     * config setting
     */
    public function config($key, $value = null)
    {
        if(is_array($key)){
            $this->config = array_merge($this->config, $key);
            return $this;
        }else if($value){
            $this->config[$key] = $value;
            return $this;
        }else if($key){
            return isset($this->config[$key]) ? $this->config[$key] : null;
        }else{
            return $this->config;
        }
        return $this;
    }

    /**
     * set parameters 
     * @return $this
     */
    public function query($key = null, $value = null)
    {
        if(is_null($key) && is_null($value)) return $this->query;
        if(is_null($value)) return isset($this->query[$key]) ? $this->query[$key] : null;
        if(is_array($key)){
            $this->query = array_merge_recursive($this->query, $key);
        }else if(!is_null($value)){
            $this->query[$key] = $value;
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
    public function with($key = null, $value = null)
    {
        return $this->query($key, $value);
    }


    /**
     * 
     * @param mixed $action 
     * @return array|$this 
     */
    public function action($action = 'SendMessageToGlobe')
    {
        return $this->config('action', $action);
    }

    /**
     * From signature
     * @param mixed $signature 
     * @return array|$this 
     */
    public function from($signature)
    {
        return $this->query('From', $signature);
    }

    /**
     * set phone number
     * @return $this
     */
    public function phone($number)
    {
        $this->query('To', $number);
        return $this;
    }

    /**
     * set template code
     * @return $this
     */
    public function batch($template, array $parameters = array())
    {
        $this->template($parameters, $template);
        return $this;
    }

    /**
     * send with message content
     * @param mixed $content 
     * @return $this 
     */
    public function message($content)
    {
        $this->action('SendMessageToGlobe');
        return $this->query('Message', $content);
    }

    /**
     * send with template content
     * @param mixed $template 
     * @param array $parameters 
     * @return mixed 
     */
    public function template(array $TemplateParam = array(), $TemplateCode = false)
    {
        $this->action('SendMessageWithTemplate');
        return $this->query('TemplateCode', $TemplateCode ?: $this->config('defaultTemplate') )
        ->query('TemplateParam', json_encode($TemplateParam));
        
    }

    /**
     * send sms
     * @return \AlibabaCloud\Client\Result\Result
     */
    public function send($phone = null, $content = null)
    {
        $phone && $this->phone($phone);
        if($content){
            is_array($content) ? $this->template($content, $this->config('deafultTemplate')) : $this->message($content);
        }
        if($this->query('TemplateCode')){
            $this->from($this->config('from'));
            $this->action('SendMessageWithTemplate');
        }else{
            $this->query('senderId');
            $this->action('SendMessageToGlobe');
        }
        return $this->client()->request();
    }

    /**
     * send rpc alibaba clound message
     * @return \AlibabaCloud\Client\Request\RpcRequest 
     */
    public function client()
    {
        // Download：https://github.com/aliyun/openapi-sdk-php-client
        // Usage：https://github.com/aliyun/openapi-sdk-php-client/blob/master/README-CN.md
        AlibabaCloud::accessKeyClient($this->config('accessKeyId'), $this->config('accessSecret'))
                        ->regionId($this->config('regionId')) 
                        ->asDefaultClient();
        return AlibabaCloud::rpc()->product('Dysmsapi')
                            ->host($this->config('host'))
                            ->version($this->config('version'))
                            ->action($this->config('action'))
                            ->method('POST')
                            ->options([
                                'query' => $this->query,
                            ]);

    }
}