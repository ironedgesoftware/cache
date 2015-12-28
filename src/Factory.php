<?php

/*
 * This file is part of the frenzy-framework package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Cache;


/*
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use IronEdge\Component\Cache\Exception\InvalidConfigException;
use IronEdge\Component\Cache\Exception\InvalidTypeException;
use IronEdge\Component\Cache\Exception\MissingExtensionException;

class Factory
{
    const TYPE_APCU                     = 'apcu';
    const TYPE_ARRAY                    = 'array';
    const TYPE_COUCHBASE                = 'couchbase';
    const TYPE_FILESYSTEM               = 'filesystem';
    const TYPE_MEMCACHE                 = 'memcache';
    const TYPE_MEMCACHED                = 'memcached';
    const TYPE_MONGODB                  = 'mongodb';
    const TYPE_PHP_FILE                 = 'php_file';
    const TYPE_PREDIS                   = 'predis';
    const TYPE_REDIS                    = 'redis';
    const TYPE_RIAK                     = 'riak';
    const TYPE_SQLITE3                  = 'sqlite3';
    const TYPE_VOID                     = 'void';
    const TYPE_WINCACHE                 = 'wincache';
    const TYPE_XCACHE                   = 'xcache';
    const TYPE_ZEND_DATA                = 'zend_data';

    /**
     * Available cache types.
     *
     * @var array
     */
    public static $availableTypes       = [
        self::TYPE_APCU,
        self::TYPE_ARRAY,
        self::TYPE_COUCHBASE,
        self::TYPE_FILESYSTEM,
        self::TYPE_MEMCACHE,
        self::TYPE_MEMCACHED,
        self::TYPE_MONGODB,
        self::TYPE_PHP_FILE,
        self::TYPE_PREDIS,
        self::TYPE_REDIS,
        self::TYPE_RIAK,
        self::TYPE_SQLITE3,
        self::TYPE_VOID,
        self::TYPE_WINCACHE,
        self::TYPE_XCACHE,
        self::TYPE_ZEND_DATA
    ];

    /**
     * Cache instances created by this factory.
     *
     * @var array
     */
    private $_instances = [];


    /**
     * Method create.
     *
     * @param string               $id             - Instance ID.
     * @param string|CacheProvider $typeOrInstance - Instance type.
     * @param array                $config         - Instance config.
     *
     * @throws InvalidTypeException
     * @throws InvalidConfigException
     * @throws MissingExtensionException
     *
     * @return CacheProvider
     */
    public function create($id, $typeOrInstance, array $config = [])
    {
        if (!is_string($id)) {
            throw new \InvalidArgumentException('Argument $id must be a string.');
        }

        if (!is_string($id) && (!is_object($typeOrInstance) || !($typeOrInstance instanceof CacheProvider))) {
            throw new \InvalidArgumentException(
                'Argument $typeOrInstance must be a string or an instance of \Doctrine\Common\Cache\CacheProvider.'
            );
        }

        if (!isset($this->_instances[$id])) {
            if (is_object($typeOrInstance)) {
                $this->_instances[$id] = $typeOrInstance;
            } else {
                $cache = null;

                switch ($typeOrInstance) {
                    case self::TYPE_APCU:
                        if (!function_exists('apcu_fetch')) {
                            throw MissingExtensionException::create('apcu', 'ApcuCache');
                        }

                        $cache = new \Doctrine\Common\Cache\ApcuCache();

                        break;
                    case self::TYPE_ARRAY:
                        $cache = new \Doctrine\Common\Cache\ArrayCache();

                        break;
                    case self::TYPE_COUCHBASE:
                        if (!class_exists('\Couchbase')) {
                            throw MissingExtensionException::create('couchbase', 'CouchbaseCache');
                        }

                        $config = array_merge(
                            [
                                'couchbase'          => null
                            ],
                            $config
                        );

                        if (!is_object($config['couchbase']) || !($config['couchbase'] instanceof \Couchbase)) {
                            throw InvalidConfigException::create('couchbase', 'an instance of \Couchbase.');
                        }

                        $cache = new \Doctrine\Common\Cache\CouchbaseCache();

                        $cache->setCouchbase($config['couchbase']);

                        break;
                    case self::TYPE_FILESYSTEM:
                        $config = array_merge(
                            [
                                'directory'         => null,
                                'extension'         => FilesystemCache::EXTENSION,
                                'umask'             => 0002
                            ],
                            $config
                        );

                        $cache = new FilesystemCache(
                            $config['directory'],
                            $config['extension'],
                            $config['umask']
                        );

                        break;
                    case self::TYPE_MEMCACHE:
                        if (!class_exists('\Memcache')) {
                            throw MissingExtensionException::create('memcache', 'MemcacheCache');
                        }

                        $config = array_merge(
                            [
                                'memcache'          => null
                            ],
                            $config
                        );

                        if (!is_object($config['memcache']) || !($config['memcache'] instanceof \Memcache)) {
                            throw InvalidConfigException::create('memcache', 'an instance of \Memcache.');
                        }

                        $cache = new \Doctrine\Common\Cache\MemcacheCache();

                        $cache->setMemcache($config['memcache']);

                        break;
                    case self::TYPE_MEMCACHED:
                        if (!class_exists('\Memcached')) {
                            throw MissingExtensionException::create('memcached', 'MemcachedCache');
                        }

                        $config = array_merge(
                            [
                                'memcached'          => null
                            ],
                            $config
                        );

                        if (!is_object($config['memcached']) || !($config['memcached'] instanceof \Memcached)) {
                            throw InvalidConfigException::create('memcached', 'an instance of \Memcached.');
                        }

                        $cache = new \Doctrine\Common\Cache\MemcachedCache();

                        $cache->setMemcached($config['memcached']);

                        break;
                    case self::TYPE_MONGODB:
                        if (!class_exists('\MongoCollection')) {
                            throw MissingExtensionException::create('mongo', 'MongoDBCache');
                        }

                        $config = array_merge(
                            [
                                'mongoCollection'          => null
                            ],
                            $config
                        );

                        if (!is_object($config['mongoCollection'])
                            || !($config['mongoCollection'] instanceof \MongoCollection)
                        ) {
                            throw InvalidConfigException::create('mongoCollection', 'an instance of \MongoCollection.');
                        }

                        $cache = new \Doctrine\Common\Cache\MongoDBCache($config['mongoCollection']);

                        break;
                    case self::TYPE_PHP_FILE:
                        $config = array_merge(
                            [
                                'directory'         => null,
                                'extension'         => PhpFileCache::EXTENSION,
                                'umask'             => 0002
                            ],
                            $config
                        );

                        $cache = new PhpFileCache(
                            $config['directory'],
                            $config['extension'],
                            $config['umask']
                        );

                        break;
                    case self::TYPE_PREDIS:
                        if (!class_exists('\Predis\ClientInterface')) {
                            throw MissingExtensionException::create('predis', 'PredisCache');
                        }

                        $config = array_merge(
                            [
                                'predisClient'          => null
                            ],
                            $config
                        );

                        if (!is_object($config['predisClient'])
                            || !($config['predisClient'] instanceof \Predis\ClientInterface)
                        ) {
                            throw InvalidConfigException::create('predisClient', 'an instance of \Predis\ClientInterface.');
                        }

                        $cache = new \Doctrine\Common\Cache\PredisCache($config['predisClient']);

                        break;
                    case self::TYPE_REDIS:
                        if (!class_exists('\Redis')) {
                            throw MissingExtensionException::create('redis', 'RedisCache');
                        }

                        $config = array_merge(
                            [
                                'redis'          => null
                            ],
                            $config
                        );

                        if (!is_object($config['redis']) || !($config['redis'] instanceof \Redis)) {
                            throw InvalidConfigException::create('redis', 'an instance of \Redis.');
                        }

                        $cache = new \Doctrine\Common\Cache\RedisCache();

                        $cache->setRedis($config['redis']);

                        break;
                    case self::TYPE_RIAK:
                        if (!class_exists('\Riak\Bucket')) {
                            throw MissingExtensionException::create('riak', 'RiakCache');
                        }

                        $config = array_merge(
                            [
                                'riakBucket'          => null
                            ],
                            $config
                        );

                        if (!is_object($config['riakBucket']) || !($config['riakBucket'] instanceof \Riak\Bucket)) {
                            throw InvalidConfigException::create('riakBucket', 'an instance of \Riak\Bucket.');
                        }

                        $cache = new \Doctrine\Common\Cache\RiakCache($config['riakBucket']);

                        break;
                    case self::TYPE_SQLITE3:
                        if (!class_exists('\SQLite3')) {
                            throw MissingExtensionException::create('sqlite3', 'SQLite3Cache');
                        }

                        $config = array_merge(
                            [
                                'sqlite3'           => null,
                                'sqlite3Table'      => null
                            ],
                            $config
                        );

                        if (!is_object($config['sqlite3']) || !($config['sqlite3'] instanceof \SQLite3)) {
                            throw InvalidConfigException::create('sqlite3', 'an instance of \SQLite3.');
                        }

                        if (!is_string($config['sqlite3Table']) || $config['sqlite3Table'] === '') {
                            throw InvalidConfigException::create('sqlite3Table', 'a non-empty string.');
                        }

                        $cache = new \Doctrine\Common\Cache\SQLite3Cache($config['sqlite3'], $config['sqlite3Table']);

                        break;
                    case self::TYPE_VOID:
                        $cache = new VoidCache();

                        break;
                    case self::TYPE_WINCACHE:
                        if (!function_exists('wincache_ucache_get')) {
                            throw MissingExtensionException::create('wincache', 'WinCacheCache');
                        }

                        $cache = new \Doctrine\Common\Cache\WinCacheCache();

                        break;
                    case self::TYPE_XCACHE:
                        if (!function_exists('xcache_isset')) {
                            throw MissingExtensionException::create('xcache', 'XcacheCache');
                        }

                        $cache = new \Doctrine\Common\Cache\XcacheCache();

                        break;
                    case self::TYPE_ZEND_DATA:
                        if (!function_exists('zend_shm_cache_fetch')) {
                            throw new \RuntimeException(
                                'Zend Data component must be installed and available before using this Cache Driver.'
                            );
                        }

                        $cache = new \Doctrine\Common\Cache\ZendDataCache();

                        break;
                    default:
                        throw InvalidTypeException::create($typeOrInstance);
                }

                $this->_instances[$id] = $cache;
            }
        }

        return $this->_instances[$id];
    }
}