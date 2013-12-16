<?php
define("TOKEN", "*****");

/**
 * class WXReceiver 
 * 
 * @author https://github.com/wzllai
 * @version $Id: 2013-12-12
 * Receive Message or Event from weixin 
 * and response as xml
 */
class WXReceiver 
{
    /**
     * message type
     */
    const MSG_TEXT      = "text";
    const MSG_IMG       = "image";
    const MSG_VOICE     = "voice";
    const MSG_VIDEO     = "video";
    const MSG_MUSIC     = "music";
    const MSG_NEWS      = "news";
    const MSG_LOCAT     = "location";
    const MSG_LINK      = "link";
    const MSG_EVNET     = "event";

    /**
     * envent type
     */
    const EVENT_SUB         = "subscribe";
    const EVENT_UNSUB       = "unsubscribe";
    const EVENT_LOCATION    = "LOCATION";

    /**
     * resquest object
     * @var stdclass 
     */
    private $request    = null;
   
    /**
     * 
     * @var string
     */
    private $ret        = "";

    private static $baseTpl     = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType>%s</xml>";
    private static $textTpl     = "<Content><![CDATA[%s]]></Content>"; 
    private static $voiceTpl    = "<Voice><MediaId><![CDATA[%s]]></MediaId></Voice>"; 
    private static $newsTpl     = "<ArticleCount>%d</ArticleCount><Articles>%s</Articles>";
    private static $newsItemTpl = "<item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>";

    public function bootstrap() {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (empty($postStr)){ 
            //log
            exit;
        }    
        $postObj        = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $type           = $postObj->MsgType;
        $this->request  = $postObj;
        switch ($type) {
            case self::MSG_TEXT:
                $this->handleText();
                break;
            case self::MSG_LOCAT:
                $this->handleLocation();
                break;
            case self::MSG_VOICE :
                $this->handleVoice();
                break;
            case self::MSG_EVNET:
                $this->handleEvent();
                break;           
            default:
                # todo:add other handle functions
                break;
        }
    }

    /**
     * handle text message
     * @return 
     */
    private function handleText() {
  
        $content = $this->request->Content;
        $this->ret  = sprintf(self::$textTpl, $content);
        $this->responseTextMsg();
    }

    /**
     * handler location message
     * @return 
     */
    private function handleLocation() {
        //$latitude   = $this->request->Location_X;
        //$longitude  = $this->request->Location_Y;
        //$location   = $this->request->Label;
   
    }

    /**
     * handler location message
     * @return 
     */
    private function handleVoice() {
        $mid        = $this->request->MediaId;
        $format     = $this->request->Format;
        $str        = "mid:$mid,format:$format,";
        if (isset($this->request->Recognition)) {
            $str .= "recognition:" . $this->request->Recognition;
        }
        $location   = $this->request->Label;
        $this->ret = sprintf(self::$voiceTpl, $mid);
        $this->responseVoiceMsg();
    }

    /**
     * handler event message
     * @return void
     */
    private function handleEvent() {
        $event  =  (string)$this->request->Event;
        if ($event === self::EVENT_SUB) {
             $this->ret = sprintf(self::$textTpl, self::$welcome);
        } 
        $this->responseTextMsg();
    }

    /**
     * response text message to weixin
     * @return void
     */
    private function responseTextMsg() {
        $responseStr = $this->assembleResponse(self::MSG_TEXT);
        echo $responseStr;
    }

    /**
     * response image message to weixin
     * @return void
     */
    private function responseImgMsg() {

    }

    /**
     * response news message to weixin
     * @return void
     */
    private function responseNewsMsg() {
        $responseStr = $this->assembleResponse(self::MSG_NEWS);
        echo $responseStr;       
    }

    /**
     * response voice message to weixin
     * @return void
     */    
    private function responseVoiceMsg() {
        $responseStr = $this->assembleResponse(self::MSG_VOICE);
        echo $responseStr;        
    }

    /**
     * assemble Response to weinxin server
     * @param  string $type
     * @return string      
     */
    private function assembleResponse($type) {
        return sprintf(
            self::$baseTpl, $this->request->FromUserName, 
            $this->request->ToUserName, time(), $type, $this->ret
        );
    }

    public function valid() {
        return $this->checkSignature();
    }  

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        return $tmpStr == $signature; 
    }
}

$receiver = new WXReceiver();
if ($receiver->valid()) {
    $receiver->bootstrap();
} 
