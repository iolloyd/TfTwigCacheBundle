<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tf\TwigCacheBundle\Twig\CacheExtension\CacheProvider;

use Tf\TwigCacheBundle\Twig\CacheExtension\CacheProviderInterface;

/**
 * Adapter class to use the cache classes provider by Doctrine.
 *
 * @author Vladimir Cvetic <vladimir@ferdinand.rs>
 */
class MemcachedCacheProvider implements CacheProviderInterface
{
    private $cache;

    /**
     * Add a server to the memcache pool.
     *
     * Does not probe server, does not set Safe to true.
     *
     * Should really be private, or modified to handle the probeServer action itself.
     *
     * @param string $ip Location of memcache server
     * @param int $port Optional: Port number (default: 11211)
     * @access public
     * @return void
     */
    public function addServer($ip, $port = 11211)
    {
        if (is_object($this->cache)) {
            return $this->cache->addServer($ip, $port);
        }
    }

    /**
     * Add an array of servers to the memcache pool
     *
     * Uses ProbeServer to verify that the connection is valid.
     *
     * Format of array:
     *
     *   $servers[ '127.0.0.1' ] = 11211;
     *
     * Logic is somewhat flawed, of course, because it wouldn't let you add multiple
     * servers on the same IP.
     *
     * Serious flaw, right? ;-)
     *
     * @param array $servers See above format definition
     * @access public
     * @return void
     */
    public function addServers(array $servers)
    {

        if (sizeof($servers) == 0) {
            return false;
        }

        foreach ($servers as $server) {
            if (intval($server['port']) == 0) {
                $port = null;
            }

            if ($this->probeServer($server['host'], $server['port'])) {
                $status = $this->addServer($server['host'], $server['port']);
                $this->safe = true;
            }
        }
    }

    /**
     * Spend a few tenths of a second opening a socket to the requested IP and port
     *
     * The purpose of this is to verify that the server exists before trying to add it,
     * to cut down on weird errors when doing ->get(). This could be a controversial or
     * flawed way to go about this.
     *
     * @param string $ip IP address (or hostname, possibly)
     * @param int $port Port that memcache is running on
     * @access public
     * @return boolean True if the socket opens successfully, or false if it fails
     */
    public function probeServer($ip, $port)
    {
        $errno = null;
        $errstr = null;
        $fp = @fsockopen($ip, $port, $errno, $errstr, $this->sockttl);

        if ($fp) {
            fclose($fp);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Cache $cache
     */
    public function __construct(\Memcached $memcached)
    {
        $this->cache = $memcached;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($key)
    {
        return $this->cache->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function save($key, $value, $lifetime = 0)
    {
        return $this->cache->set($key, $value, $lifetime);
    }
}
