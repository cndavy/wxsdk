<?php
/**
 * cache 
 * @author https://github.com/wzllai
 * @version $Id: 2013-12-09
 */
require_once __DIR__ . '/interface.php';
class Cache {
	private $handler;
	private $hashFunc = "md5";
	/**
	 * construct function
	 * @param Icache $handler
	 */
	public function __construct(Icache $handler = null) {
		if ($handler == null) {
			require_once __DIR__ . '/memcache.php';
			$handler = SaeMemcache::getInstance();
		}
		$this->setHandler($handler);
	}

	/**
	 * set cache hander which implement cache interface
	 * @param Icache $handler 
	 */
	public function setHandler(Icache $handler) {
		$this->handler = $handler;
	}

	/**
	 * setCache
	 * @param string $key    
	 * @param string $value 
	 * @param int  $expire 
	 * @return  bool 
	 */
	public function setCache($key, $value, $expire) {
		return $this->handler->set(call_user_func($this->hashFunc, $key), $value, $expire);
	}

	/**
	 * getCache
	 * @param  string $key 
	 * @return string
	 */
	public function getCache($key) {
		return $this->handler->get(call_user_func($this->hashFunc, $key));
	}

	/**
	 * deleteCache
	 * @param  string $key
	 * @return bool
	 */
	public function deleteCache($key) {
		return $this->handler->delete($key);
	}

	/**
	 * flushCache
	 * @return bool
	 */
	public function flushCache() {
		return $this->handler->flush();
	}
}