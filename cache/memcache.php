<?php
require_once __DIR__ . '/interface.php';
/**
 * wrapper sae memecache
 * @author https://github.com/wzllai
 * @version $Id: 2013-12-09
 */
Class SaeMemcache implements Icache {
	private static $instance = null;
	private static $mmc;
	private function __construct() {}
	private function __clone(){}

	private $flag = false;

	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new self();
			$mmc=memcache_init();
    		if($mmc == false) {
    			//log
    			
    		} else {
    			self::$mmc = $mmc;
    		}

		} 

		return self::$instance;
	}

/*	public function setFlag($flag) {
		$this->flag = (bool) $flag;
	}*/
 
	public function set($key, $value, $expire) {
		return memcache_set(self::$mmc, $key, $value, $this->flag, $expire);
	}

	public function get($key) {
		$ret =  memcache_get(self::$mmc, $key, $this->flag);
		if ($ret == '[]') { //compatiblity saememchache get null
			$ret = null;
		}
		return $ret;
	}

	public function delete($key) {
		return memcache_delete(self::$mmc, $key);
	}

	public function flush() {
		return memcache_flush(self::$mmc);
	}

}