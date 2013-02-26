<?php
namespace AssetKit;

class Config
{

    const JSON_FORMAT = 1;
    const PHP_FORMAT = 1;

    /**
     * @var string $file the config file path
     */
    public $file;


    /**
     * @var array $config the config hash.
     */
    public $config;



    /**
     * @var string $baseDir the base directory for public files.
     */
    public $baseDir;


    /**
     * @var string $baseUrl The base url for front-end
     */
    public $baseUrl;

    public $cacheEnable = true;

    public $cacheSupport = false;

    public function __construct($file,$options = array())
    {
        $this->file = $file;

        if(isset($options['cache']) ) {
            $this->cacheEnable = $options['cache'];
        }

        $useCache = $this->cacheEnabled();
        if($useCache) {
            // get apc cache
            $cacheId = isset($options['cache_id'])
                ? $options['cache_id']
                : __DIR__;
            $this->config = apc_fetch($cacheId);
        }

        if ( ! $this->config ) {
            // read the config file
            if( file_exists($file) ) {
                $this->config = json_decode(file_get_contents($file),true);
                if($useCache) {
                    apc_store($cacheId, 
                        $this->config, 
                        isset($options['cache_expiry']) 
                        ? $options['cache_expiry'] 
                        : 0 
                    );
                }
            } else {
                $this->config = array();
            }
        }
    }



    /**
     * Check if apc cache is supported and is cache enabled by user.
     *
     * @return bool 
     */
    public function cacheEnabled() 
    {
        if($this->cacheEnable) {
            return $this->cacheSupport = extension_loaded('apc') ;
        }
        return false;
    }


    /**
     * Get registered assets and return asset objects.
     *
     * @return AssetKit\Asset[]
     */
    public function getRegisteredAssets()
    {
        if( isset($this->config['assets'] ) ) {
            return $this->config['assets'];
        }
        return array();
    }


    /**
     * Write current config to file
     *
     * @param string $filename 
     * @param integer $format Can be PHP_FORMAT, JSON_FORMAT.
     */
    public function write($path, $config, $format = PHP_FORMAT )
    {
        if( $format == self::JSON_FORMAT ) {
            if( ! defined('JSON_PRETTY_PRINT') )
                define('JSON_PRETTY_PRINT',0);
            return file_put_contents($path, json_encode($config,
                JSON_PRETTY_PRINT));
        } else if ($format == self::PHP_FORMAT ) {
            $php = '<?php return ' .  var_export($config,true) . ';';
            return file_put_contents($path, $php);
        }
    }


    /**
     * Save current asset config with $format
     *
     * @param integer $format PHP_FORMAT or JSON_FORMAT
     */
    public function save($format = PHP_FORMAT)
    {
        return $this->write($this->file, $this->config, $format);
    }


    /**
     * Get baseDir, this is usually used for compiling and minifing.
     *
     * @param bool $absolute reutrn absolute path or not 
     * @return string the path
     */
    public function getBaseDir($absolute = false) 
    {
        return $this->config['baseDir'];
    }


    /**
     * Get baseUrl for front-end including
     * 
     * @param bool $absolute return absolute path or not.
     * @return string the path.
     */
    public function getBaseUrl($absolute = false)
    {
        return $this->config['baseUrl'];
    }
}

