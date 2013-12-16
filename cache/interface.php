<?php
/**
 * cache interface
 * @author https://github.com/wzllai
 * @version $Id: 2013-12-09
 */
interface Icache {
	public function set($key, $value, $expire);
	public function get($key);
	public function delete($key);
	public function flush();
}