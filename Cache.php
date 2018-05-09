<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cache
{

    public $options = [
        'dir' => 'cache', //Default to BASEPATH.'cache'
        'cache_postfix' => '.cache', //Prefix to all cache filenames
        'expiry_postfix' => '.exp', //Expiry file prefix
        'group_postfix' => '.group', //Group directory prefix
        'default_ttl' => 3600, //Default time to live = 3600 seconds (One hour).
    ];
    public $memcache;
    public $useMemcache = false;
    public $memip;
    public $memport;
    /**
     *     Constructor
     *
     *     @param    Options to override defaults
     */
    public function Cache($options = null)
    {
        if ($options != null) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     *     Check if a item has a (valid) cache
     *
     *     @param    Cache id
     *     @param    Cache group id (optional)
     *     @return Boolean indicating if cache available
     */
    public function is_cached($cache_id, $cache_group = null)
    {
        if ($this->useMemcache != true) {
            if ($this->_get_expiry($cache_id, $cache_group) > time()) {
                return true;
            }
            $this->delete($cache_id, $cache_group);

            return false;
        } else {
            if ($this->memcache->connect($this->memip, $this->memport) == true) {
                if ($this->memcache->get($cache_id) != false) {
                    return true;
                }
            }
            return false;
        }

    }

    /**
     *     Connects to memcached daemon
     *
     *     @param    ip
     *     @param    port
     */
    public function useMemcache($ip, $port)
    {
        if (class_exists('Memcache')) {
            $this->memcache = new Memcache();
            if ($this->memcache->connect($ip, $port) == true) {
                $this->useMemcache = true;
                $this->memip = $ip;
                $this->memport = $port;
                $this->memcache->close();
                return true;
            }
        }
        return false;
    }
    public function useFile()
    {
        $this->useMemcache = false;
    }
    /**
     *     Save an item to the cache
     *
     *     @param    Cache id
     *     @param    Data object
     *     @param    Cache group id (optional)
     *     @param    Time to live for this item
     */
    public function save($cache_id, $data, $ttl = null, $cache_group = null)
    {
        if ($this->useMemcache != true) {
            if ($cache_group !== null) {
                $group_dir = $this->_group_dir($cache_group);

                if (!file_exists($group_dir)) {
                    mkdir($group_dir);
                }

            }

            $file = $this->_file($cache_id, $cache_group);
            $cache_file = $file . $this->options['cache_postfix'];
            $expiry_file = $file . $this->options['expiry_postfix'];

            if ($ttl === null) {
                $ttl = $this->options['default_ttl'];
            }

            //
            //    Ok, so setting ttl = 0 is not quite forever, but 1000 years
            //    Is your PHP code going to be running for 1000 years? If so dont use this library (or just regenerate the cache then)!
            //
            if ($ttl == 0) {
                $ttl = 31536000000;
            }
            //1000 years in seconds

            $expire_time = time() + $ttl;

            $f1 = fopen($expiry_file, 'w');
            $f2 = fopen($cache_file, 'w');

            flock($f1, LOCK_EX);
            flock($f2, LOCK_EX);

            fwrite($f1, $expire_time);
            fwrite($f2, serialize($data));

            flock($f1, LOCK_UN);
            flock($f2, LOCK_UN);

            fclose($f1);
            fclose($f2);
        } else {
            if ($this->memcache->connect($this->memip, $this->memport) == true) {
                if ($ttl == null) {$ttl = $this->options['default_ttl'];}
                $this->memcache->set($cache_id, $data, MEMCACHE_COMPRESSED, $ttl);
                $this->memcache->close();
            }
        }

    }

    /**
     *     Get and return an item from the cache
     *
     *     @param    Cache Id
     *     @param    Cache group Id
     *     @param    Should I check the expiry time?
     *     @return The object or NULL if not available
     */
    public function get($cache_id, $cache_group = null, $skip_checking = false)
    {
        if ($this->useMemcache != true) {
            if (!$skip_checking && !$this->is_cached($cache_id, $cache_group)) {
                return null;
            }

            $cache_file = $this->_file($cache_id, $cache_group) . $this->options['cache_postfix'];

            if (!is_file($cache_file)) {
                return null;
            }

            return unserialize(file_get_contents($cache_file));
        } else {
            if ($this->memcache->connect($this->memip, $this->memport) == true) {
                $result = $this->memcache->get($cache_id);
                $this->memcache->close();
                return $result;
            }
        }

    }

    /**
     *     Remove an item from the cache
     *
     *     @param    Cache Id
     *     @param     Cache group Id
     */
    public function delete($cache_id, $cache_group = null)
    {
        if ($this->useMemcache != true) {
            $file = $this->_file($cache_id, $cache_group);
            $cache_file = $file . $this->options['cache_postfix'];
            $expiry_file = $file . $this->options['expiry_postfix'];

            @unlink($cache_file);
            @unlink($expiry_file);
        } else {
            if ($this->memcache->connect($this->memip, $this->memport) == true) {
                $this->memcache->delete($cache_id);
                $this->memcache->close();
            }
        }

    }

    /**
     *     Remove an entire group
     *
     *     @param    Cache group Id
     */
    public function remove_group($cache_group)
    {
        if ($this->useMemcache != true) {
            $group_dir = $this->_group_dir($cache_group);

            //
            //    Empty the directory
            //
            if (!$dh = @opendir($group_dir)) {
                return;
            }

            while (($obj = readdir($dh))) {
                if ($obj == '.' || $obj == '..') {
                    continue;
                }
                @unlink($group_dir . '/' . $obj);

            }

            closedir($dh);

            //
            //    Delete the dir for tidyness
            //
            @rmdir($group_dir);
        }

    }

    /**
     *     Remove an array of cached items
     *
     *     @param    Array of cache ids
     *     @param    Cache group Id
     */
    public function remove_ids($cache_ids, $cache_group = null)
    {
        if ($this->useMemcache != true) {
            if (!is_array($cache_ids)) {
                $cache_ids = array($cache_ids);
            }

            //
            //    Hash all ids
            //
            $hashes = [];

            foreach ($cache_ids as $cache_id) {

                $hashes[] = md5($cache_id);

            }

            $group_dir = $this->_group_dir($cache_group);

            //
            //    Delete matching files
            //
            if (!$dh = @opendir($group_dir)) {
                return;
            }

            $filecount = 0;
            $delcount = 0;

            while (($obj = readdir($dh))) {

                if ($obj == '.' || $obj == '..') {
                    continue;
                }

                $parts = explode(".", $obj);
                $hash = $parts[0];

                if (in_array($hash, $hashes)) {
                    @unlink($group_dir . '/' . $obj);
                    $delcount++;
                }

                $filecount++;
            }

            closedir($dh);

            //
            //    Delete the dir if empty
            //
            if ($filecount == $delcount) {
                @rmdir($group_dir);
            }

        } else {
            if (is_array($cache_ids)) {
                if ($this->memcache->connect($this->memip, $this->memport) == true) {
                    foreach ($cache_ids as $cache_id) {
                        $this->memcache->delete($cache_id);
                    }
                }
            }
        }
    }

    public function _get_expiry($cache_id, $cache_group = null)
    {

        $file = $this->_file($cache_id, $cache_group) . $this->options['expiry_postfix'];

        if (!is_file($file)) {
            return 0;
        }

        return intval(file_get_contents($file));

    }

    public function _file($cache_id, $cache_group = null)
    {
        return $this->_group_dir($cache_group) . '/' . md5($cache_id);
    }

    public function _group_dir($cache_group)
    {
        $dir = ($cache_group != null) ? md5($cache_group) . $this->options['group_postfix'] : '';

        return BASEPATH . $this->options['dir'] . $dir;
    }

}
