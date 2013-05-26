<?php 

namespace ToyFoundry\TwigCacheBundle\Lib;
/**  
 * @author Vladimir Cvetic
 * @version 0.1
 * 
 */ 
class _Memcache { 
  /** 
	 * Resources of the opend memcached connections 
	 * @var array [memcache objects] 
	 */ 
	protected $mc_servers = array(); 
	/** 
	 * Quantity of servers used 
	 * @var int 
	 */ 
	protected $mc_servers_count; 
 
	static $instance; 
 
	/** 
	 * Singleton to call from all other functions 
	 */ 
	static function singleton(){ 
	    //Write here where from to get the servers list from, like  
	    global $_memcache_servers; 
	    $servers = $_memcache_servers; 
	     
	    self::$instance ||  
	        self::$instance = new _Memcache($servers); 
	    return self::$instance; 
	} 
 
	/** 
	 * Accepts the 2-d array with details of memcached servers 
	 * 
	 * @param array $servers 
	 */ 
	protected function __construct(array $servers){ 
	    if (!$servers){ 
	        trigger_error('No memcache servers to connect',E_USER_WARNING); 
	    } 
	    for ($i = 0, $n = count($servers); $i < $n; ++$i){ 
	        ( $con = memcache_pconnect(key($servers[$i]), current($servers[$i])) )&&  
	            $this->mc_servers[] = $con; 
	    } 
	    $this->mc_servers_count = count($this->mc_servers); 
	    if (!$this->mc_servers_count){ 
	        $this->mc_servers[0]=null; 
	    } 
	} 
	/** 
	 * Returns the resource for the memcache connection 
	 * 
	 * @param string $key 
	 * @return object memcache 
	 */ 
	protected function getMemcacheLink($key){ 
	    if ( $this->mc_servers_count <2 ){ 
	        //no servers choice 
	        return $this->mc_servers[0]; 
	    } 
	    return $this->mc_servers[(crc32($key) & 0x7fffffff)%$this->mc_servers_count]; 
	} 
 
	/** 
	 * Clear the cache 
	 * 
	 * @return void 
	 */ 
	static function flush() { 
	    $x = self::singleton()->mc_servers_count; 
	    for ($i = 0; $i < $x; ++$i){ 
	        $a = self::singleton()->mc_servers[$i]; 
	        self::singleton()->mc_servers[$i]->flush(); 
	    } 
	} 
 
	/** 
	 * Returns the value stored in the memory by it's key 
	 * 
	 * @param string $key 
	 * @return mix 
	 */ 
	static function get($key) { 
	    return self::singleton()->getMemcacheLink($key)->get($key); 
	} 
 
	/** 
	 * Store the value in the memcache memory (overwrite if key exists) 
	 * 
	 * @param string $key 
	 * @param mix $var 
	 * @param bool $compress 
	 * @param int $expire (seconds before item expires) 
	 * @return bool 
	 */ 
	static function set($key, $var, $expire=0, $compress=0) { 
	    return self::singleton()->getMemcacheLink($key)->set($key, $var, $compress?MEMCACHE_COMPRESSED:null, $expire); 
	} 
	/** 
	 * Set the value in memcache if the value does not exist; returns FALSE if value exists 
	 * 
	 * @param sting $key 
	 * @param mix $var 
	 * @param bool $compress 
	 * @param int $expire 
	 * @return bool 
	 */ 
	static function add($key, $var, $compress=0, $expire=0) { 
	    return self::singleton()->getMemcacheLink($key)->add($key, $var, $compress?MEMCACHE_COMPRESSED:null, $expire); 
	} 
 
	/** 
	 * Replace an existing value 
	 * 
	 * @param string $key 
	 * @param mix $var 
	 * @param bool $compress 
	 * @param int $expire 
	 * @return bool 
	 */ 
	static function replace($key, $var, $compress=0, $expire=0) { 
	    return self::singleton()->getMemcacheLink($key)->replace($key, $var, $compress?MEMCACHE_COMPRESSED:null, $expire); 
	} 
	/** 
	 * Delete a record or set a timeout 
	 * 
	 * @param string $key 
	 * @param int $timeout 
	 * @return bool 
	 */ 
	static function delete($key, $timeout=0) { 
	    return self::singleton()->getMemcacheLink($key)->delete($key, $timeout); 
	} 
	/** 
	 * Increment an existing integer value 
	 * 
	 * @param string $key 
	 * @param mix $value 
	 * @return bool 
	 */ 
	static function increment($key, $value=1) { 
	    return self::singleton()->getMemcacheLink($key)->increment($key, $value); 
	} 
 
	/** 
	 * Decrement an existing value 
	 * 
	 * @param string $key 
	 * @param mix $value 
	 * @return bool 
	 */ 
	static function decrement($key, $value=1) { 
	    return self::singleton()->getMemcacheLink($key)->decrement($key, $value); 
	} 
 
 
//class end 
} 