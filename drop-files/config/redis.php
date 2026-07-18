<?php

/**
 * Configuration for: Redis Database Connection
 */

if (!function_exists('droptaxiConfigValue')) {
    function droptaxiConfigValue($name, $default = '') {
        $file = getenv($name . '_FILE');
        if ($file !== false && $file !== '' && is_readable($file)) {
            return trim(file_get_contents($file));
        }

        $value = getenv($name);
        return $value === false ? $default : $value;
    }
}

define("REDIS_HOST", droptaxiConfigValue("REDIS_HOST", "127.0.0.1"));
define("REDIS_PORT", droptaxiConfigValue("REDIS_PORT", "6379"));
define("REDIS_PASSWORD", droptaxiConfigValue("REDIS_PASSWORD", ""));

function connectRedis(){
    try{
        $redis = new Redis();
        $redis->connect(REDIS_HOST, (int) REDIS_PORT);
        if (REDIS_PASSWORD !== '') {
            $redis->auth(REDIS_PASSWORD);
        }
    }catch(Throwable $e){
        return false;
    }
    return $redis;
}
