<?php
define("APPID", "******");
define("APPSECRET", "******");
require_once __DIR__ . '/util/http.php';
require_once __DIR__ . '/cache/cache.php';

/**
 * WXClient  
 * @author https://github.com/wzllai
 * @version $Id: 2013-12-13
 */
class WXClient 
{
	const ACCESS_TOKEN_KEY 			= "wx_access_token";
	private $http;
	private $cache;
	private $accessToken;

	public function __construct() {
		$this->http 	= new Httplib(); 
		$this->cache 	= new Cache(); 
	}

	public function authorizeUrl() {
		return "https://open.weixin.qq.com/connect/oauth2/authorize?";
	}	
	public function userAccessTokenUrl() {
		return "https://api.weixin.qq.com/sns/oauth2/access_token?";
	}
	public function usreRefreshRokenUrl() {
		return "https://api.weixin.qq.com/sns/oauth2/refresh_token?";
	}

	public function accessTokenUrl() {
		return "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . APPID . "&secret=" . APPSECRET;
	}

	public function uploadUrl($type) {
		return "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $this->getAccessToken() . "&type=" . $type;
	}

	public function downloadUrl($mid) {
		return "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=" . $this->getAccessToken() . "&media_id=" . $mid;
	}

	public function customerServiceUrl() {
		return "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . 	$this->getAccessToken();
	}

	public function userInfoUrl($openid) {
		return "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $this->getAccessToken() . "&openid=." .$openid;
	} 

	public function fansListUrl($openid = "") {
		return "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->getAccessToken() . "&next_openid=" . $openid;
	}

	public function groupUrl() {
		return "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=" . $this->getAccessToken();
	}

	public function menuUrl($action) {
		return "https://api.weixin.qq.com/cgi-bin/menu/" . $action . "?access_token=" . $this->getAccessToken();
	}

	private function _request($url, $data = "") {
		try {
			$this->http->request($url, $data);
			return $this->http->get_data();
		} catch (Exception $e) {
			//log
			return null;
		}
	}
	/**
	 * get access_token
	 * cache token by cache 
	 * @return string
	 */
	public function getAccessToken() {
		$ret = $this->cache->getCache(self::ACCESS_TOKEN_KEY);
		if ($ret) {
			header('hit cache:1');
			$this->accessToken = $ret;
		} else {
			header('hit cache:0');
			$ret  = $this->_request($this->accessTokenUrl());
			$data = json_decode($ret, true);
			if (isset($data['access_token'])) {
				$this->accessToken = $data['access_token'];
				$this->cache->setCache(self::ACCESS_TOKEN_KEY, $this->accessToken, $data['expires_in'] - 120);
			} else {
				//log $ret
				exit;
			}
		}
		return $this->accessToken;

	}

	/**
	 * upload image to weixin server 
	 * @return array 
	 */
	public function uploadImg($path) {
		$path = '@' . ltrim($path);
		$params = array('media' => $path);
		return $this->_upload('image', $params);
	}

	/**
	 * _upload 
	 * @param  [string] $type   [description]
	 * @param  [array] $params [description]
	 * @return [array|null] 
	 */
	private function _upload($type, $params) {
		$ret  = $this->_request($this->uploadUrl($type), $params);
		return json_decode($ret, true);;
	}

	/**
	 * [download media file]
	 * @param  [string] $mid [media id]
	 * @return [mixed]     
	 */
	public function download($mid) {
		return $this->_request($this->downloadUrl($mid));
	}

	/**
	 * [post customerService]
	 * @param  [array] $data [description]
	 * @return [type]       [description]
	 */
	public function customerService(array $data) {
		$data = array(json_encode($data));
		return $this->_request($this->customerServiceUrl(), $data);	
	}

	public function getUsrInfo($openid) {
		$ret = $this->_request($this->userInfoUrl($openid));	
		return json_decode($ret, true);;
	}

	public function getFansList($openid = "") {
		$ret = $this->_request($this->fansListUrl($openid));	
		return json_decode($ret, true);;
	}

	public function getGroup(){
		$ret = $this->_request($this->groupUrl());	
		return json_decode($ret, true);
	}

	const AUTH_SCOPE_BASE 		= "snsapi_base";
	const AUTH_SCOPE_USERINFO 	= "snsapi_userinfo";
	/**
	 * generate auth url for weinxin user 
	 * @param  [string] $redirectUrl [callback url to get code]
	 * @param  [string] $scope       
	 * @return [string] 
	 */
	public function generateAuthorizeUrl($redirectUrl, $scope) {
		$query = array(
			'appid' 		=> APPID, 
			'redirect_uri' 	=> $redirectUrl, 
			'scope' 		=> $scope,
			'state' 		=> md5(time()),
			'response_type' => 'code',
		);
		return $this->authorizeUrl() . http_build_query($query);
	}

	/**
	 * get User grant Access data
	 * @param  [stirng] $code 
	 * @return [array]      
	 */
	public function getUserTokenAccess($code) {
		$query = array(
			'appid' 		=> APPID, 
			'secret' 		=> APPSECRET, 
			'code' 			=> $code,
			'grant_type' 	=> "authorization_code",
		);
		$url = $this->userAccessTokenUrl() . http_build_query($query);
		$ret = $this->_request($url);
		return json_decode($ret);
	}

	/**
	 * refresh UserToken 
	 * @param  [string] $refreshToken 
	 * @return [array]       
	 */
	public function refreshUserToken($refreshToken ) {
		$query = array(
			'appid' 		=> APPID, 
			'refresh_token' => $refreshToken, 
			'grant_type' 	=> "refresh_token",
		);
		$url = $this->usreRefreshRokenUrl() . http_build_query($query);
		$ret = $this->_request($url);
		return json_decode($ret);		
	}

	/**
	 * create menu
	 * @param  array  $data 
	 * @return bool
	 */
	public function createMenu(array $data) {
		$data = json_encode($data);
		$ret = $this->_request($this->menuUrl("create"), $data);
		$ret = json_encode($ret, true);
		if (isset($ret['errcode']) && $ret['errcode'] == 0) {
			return true;
		} else {
			return false;
		}			
	}

	/**
	 * get menu
	 * @return array
	 */
	public function getMenue() {
		$ret = $this->_request($this->menuUrl("get"));
		return json_decode($ret, true);
	}

	/**
	 * delete menu
	 * @return bool
	 */
	public function deleteMenu() {
		$ret = $this->_request($this->menuUrl("delete"));
		$ret = json_encode($ret, true);
		if (isset($ret['errcode']) && $ret['errcode'] == 0) {
			return true;
		} else {
			return false;
		}	
	}
}

