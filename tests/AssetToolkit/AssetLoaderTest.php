<?php
use AssetToolkit\ResourceUpdater;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\AssetUrlBuilder;
use AssetToolkit\Asset;
use AssetToolkit\Collection;

class AssetLoaderTest extends AssetToolkit\TestCase
{

    public function manifestProvider() 
    {
        return array(
            array("tests/assets/jquery-ui"),
            array("tests/assets/jquery"),
            array("tests/assets/underscore"),
        );
    }


    /**
     * @dataProvider manifestProvider
     */
    public function testFindAsset($manifestPath) {
        $config = $this->getConfig();
        $loader = $this->getLoader();
        $name = basename($manifestPath);
        $asset = $loader->lookup($name);
        ok($asset);
        ok($asset instanceof Asset);
    }


    /**
     * @dataProvider manifestProvider
     */
    public function testAssetRegister($manifestPath)
    {
        $config = $this->getConfig();
        $loader = $this->getLoader();

        $asset = $loader->register($manifestPath);
        ok($asset, "Asset is loaded from $manifestPath");

        $collections = $asset->getCollections();
        ok($collections);

        foreach( $collections as $collection ) {
            ok( $collection instanceof Collection, 'Got Collection object');
        }

        $urlBuilder = new AssetUrlBuilder($config);
        $assetBaseUrl = $urlBuilder->buildBaseUrl($asset);
        is( "/assets/" . $asset->name, $assetBaseUrl);



        /*
        $updater = new ResourceUpdater();
        ok($updater, "Resource updater is loaded");
        $updater->update($asset);

        $installer = new AssetToolkit\LinkInstaller($config);
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();

        $installer = new AssetToolkit\Installer($config);
        ob_start();
        $installer->install( $asset );
        $installer->uninstall( $asset );
        ob_clean();
        */
        $config->save();
    }
}

