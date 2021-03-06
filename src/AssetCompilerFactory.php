<?php
namespace AssetKit;
use Exception;
use RuntimeException;
use AssetKit\FileUtil;
use AssetKit\AssetConfig;
use AssetKit\AssetLoader;
use AssetKit\AssetUrlBuilder;
use AssetKit\Collection;
use AssetKit\ProductionAssetCompiler;
use AssetKit\AssetCompiler;


class AssetCompilerFactory
{
    static public function create(AssetConfig $config, AssetLoader $loader) {
        $compiler = NULL;
        if ($config->getEnvironment() === 'production' ) {
            $compiler = new ProductionAssetCompiler($config,$loader);
        } elseif ($config->getEnvironment() === 'development' ) {
            $compiler = new AssetCompiler($config, $loader);
        } else {
            $compiler = new AssetCompiler($config, $loader);
        }
        $compiler->registerDefaultCompressors();
        $compiler->registerDefaultFilters();
        return $compiler;
    }
}




