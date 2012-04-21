<?php
namespace AssetKit;

class Config
{
    public $file;
    public $config = array();

    public function __construct($file)
    {
        $this->file = $file;
        if( file_exists($file) ) {
            $this->config = json_decode(file_get_contents($file),true);
        }
    }

    public function addAsset($asset,$path)
    {
        if( ! $this->config['assets'] )
            $this->config['assets'] = array();
        $this->config['assets'][$asset] = $path;
    }

    public function removeAsset($asset)
    {
        unset($this->config['assets'][$asset]);
    }

    public function save()
    {
        if( ! defined('JSON_PRETTY_PRINT') )
            define('JSON_PRETTY_PRINT',0);
        file_put_contents($this->file, json_encode($this->config, 
                JSON_PRETTY_PRINT));
    }

}

