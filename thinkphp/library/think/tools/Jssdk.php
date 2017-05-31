<?php
namespace think\tools;


class Jssdk {
    private static $appId;
    private static $appSecret;

    public function __construct($appId='', $appSecret='') {
        parent::__construct();
        self::$appId = config("wechat.app_id");
        self::$appSecret = config("wechat.app_secret");
  
    }


    public static function getSignPackage() {

      $jsapiTicket = self::getJsApiTicket();

      // 注意 URL 一定要动态获取，不能 hardcode.
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

      $timestamp = time();
      $nonceStr = self::createNonceStr();

      // 这里参数的顺序要按照 key 值 ASCII 码升序排序
      $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

      $signature = sha1($string);

      $signPackage = array(
        "appId"     => self::$appId,
        "nonceStr"  => $nonceStr,
        "timestamp" => $timestamp,
        "url"       => $url,
        "signature" => $signature,
        "rawString" => $string
      );
      return $signPackage; 
    }

    private static function createNonceStr($length = 16) {
      $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      $str = "";
      for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
      }
      return $str;
    }

    private static function getJsApiTicket() {
      // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
     // $data = json_decode(file_get_contents("jsapi_ticket.json"));
       //$data = json_decode( S('jsapi_ticket'));
      if ( ! cache('ticket') ) {
        $accessToken = self::getAccessToken();
        // 如果是企业号用以下 URL 获取 ticket
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
        $res = json_decode(self::httpGet($url));
        $ticket = $res->ticket;
       
        cache('ticket',$ticket,7000);
        return $ticket;
        
      } else {
        return  cache('ticket'); 
      }

     
    }

    private static function getAccessToken() {
      // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
      //$data = json_decode(file_get_contents("access_token.json"));
      

      if ( ! cache('access_token') ) {
        // 如果是企业号用以下URL获取access_token
        // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".self::$appId."&secret=".self::$appSecret;
        $res = json_decode(self::httpGet($url));
        $access_token = $res->access_token;
       
        cache('access_token',$access_token,7000);
        return $access_token;
        
      } else {
        return cache('access_token');
      }
      
    }

    private static function httpGet($url) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 500);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_URL, $url);

      $res = curl_exec($curl);
      curl_close($curl);

      return $res;
    }
  }

