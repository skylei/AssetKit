<?php
namespace AssetKit;
use ZipArchive;
use Exception;
use SerializerKit;
use AssetKit\FileUtils;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use AssetKit\FileUtil;


/**
 * Asset class
 *
 * Asset object can be created from a manifest file.
 * Or can just be created with no arguments.
 */
class Asset
{
    /**
     * @var string the asset name
     */
    public $name;


    /**
     * @var array config stash.
     */
    public $stash;

    /* asset dir (related path, relate to config file) */
    public $sourceDir;


    /**
     * @var strign manifest file path
     */
    public $manifestFile;


    /**
     * @var AssetKit\Config
     */
    public $config;


    /**
     * @var AssetKit\FileCollection[]
     */
    public $collections = array();


    /**
     * @param array|string|null $arg manifest array, manifest file path, or asset name
     */
    public function __construct()
    {
    }

    public function loadFromManifestFile($manifestFile, $format = 0)
    {
        $config = null;
        if( $format ) {
            $config = Data::decode_file($manifestFile, $format);
        } else {
            $config = Data::detect_format_and_decode($manifestFile);
        }
        $this->manifestFile = $manifestFile;
        $this->sourceDir = dirname($manifestFile);
        $this->loadFromArray($config);
    }


    public function loadFromArray($config)
    {
        $this->stash = $config;
        // load assets
        if( isset($this->stash['collections']) ) {
            // create collection objects
            $this->collections = $this->create_collections($this->stash['collections']);
        }
    }


    /**
     * simply copy class members to to the file collection
     */
    static function create_collections( $collectionStash )
    {
        $sourceDir = $this->sourceDir;
        $collections = array();

        foreach( $collectionStash as $stash ) {
            $collection = new FileCollection;

            $files = array();
            foreach( $stash['files'] as $p ) {


                // found glob pattern
                if( strpos($p,'*') !== false ) 
                {
                    $expanded = FileUtil::expand_glob_from_dir($sourceDir, $p);

                    // should be unique
                    $files = array_unique( array_merge( $files , $expanded ) );

                } elseif( is_dir( $sourceDir . DIRECTORY_SEPARATOR . $p ) ) {

                    $expanded = FileUtil::expand_dir_recursively( $sourceDir . DIRECTORY_SEPARATOR . $p );
                    $expanded = FileUtil::remove_basedir_from_paths($expanded , $sourceDir);

                    $files = array_unique(array_merge( $files , $expanded ));

                } else {
                    $files[] = $p;
                }
            }
            // update filelist.
            $stash['files'] = $files;


            if( isset($stash['filters']) )
                $collection->filters = $stash['filters'];

            if( isset($stash['compressors']) ) {
                $collection->compressors = $stash['compressors'];
            }

            if( isset($stash['files']) ) {
                $collection->files = $stash['files'];
            }

            if( isset($stash['javascript']) || isset($stash['js']) ) {
                $collection->isJavascript = true;
            } elseif( isset($stash['stylesheet']) || isset($stash['css']) ) {
                $collection->isStylesheet = true;
            } elseif( isset($stash['coffeescript']) ) {
                $collection->isCoffeescript = true;
            }
            $collection->asset = $this;
            $collections[] = $collection;
        }
        return $collections;
    }

    public function getCollections()
    {
        return $this->collections;
    }

    public function export()
    {
        // we should also save installed_dir
        // installed_dir = public dir + source dir
        return array(
            'stash'      => $this->stash,
            'manifest'   => $this->manifest,
            'source_dir' => $this->sourceDir,
            'name'       => $this->name,
        );
    }

    public function compile()
    {
        // compile assets
    }

    public function getName()
    {
        return $this->name;
    }

    public function getInstalledDir($absolute = false)
    {
        return $this->config->getPublicAssetRoot($absolute) . DIRECTORY_SEPARATOR . $this->name;
    }

    public function getSourceDir($absolute = false)
    {
        return $absolute
            ? $this->config->getRoot() . DIRECTORY_SEPARATOR . $this->sourceDir
            : $this->sourceDir
            ;
    }

    /**
     * Return the public dir of this asset
     *
     *   Asset public dir = Public dir + Asset source path
     *
     * @param bool $absolute should return absolute path or relative path ?
     */
    public function getPublicDir($absolute = false)
    {
        $public = $this->config->getPublicRoot($absolute);
        return $public . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->name;
    }


    /**
     * Check source file existence.
     *
     * @return bool
     */
    public function hasSourceFiles()
    {
        $this->sourceDir;
        foreach( $this->collections as $collection ) {
            $paths = $collection->getSourcePaths(true);
            foreach( $paths as $path ) {
                if( ! file_exists($path) )
                    return false;
            }
        }
        return true;
    }

    /**
     * Init Resource file and update to public asset dir ?
     */
    public function initResource($update = false)
    {
        $updater = new \AssetKit\ResourceUpdater($this);
        return $updater->update($update);
    }
}



