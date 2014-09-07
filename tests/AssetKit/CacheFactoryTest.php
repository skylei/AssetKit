<?php
use AssetKit\CacheFactory;
use AssetKit\AssetConfig;

class CacheFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCacheFactory()
    {
        $config = new AssetConfig("app.yml",array(
            'namespace' => 'app',
            'cache_dir' => 'cache',
        ));
        $cache = CacheFactory::create($config);
        ok($cache);
    }
}
