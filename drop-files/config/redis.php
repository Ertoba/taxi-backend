<?php

/**
 * Configuration for: Redis Database Connection
 * 
 */

define("REDIS_HOST", "127.0.0.1");
define("REDIS_PORT", "6379");


function connectRedis(){
  try{  
    $redis = new Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT);    
  }catch(Throwable $e){
    return false;
  }
  return $redis;
}