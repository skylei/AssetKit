<?php
namespace AssetToolkit;
use Exception;
use IteratorAggregate;

class Collection
    implements IteratorAggregate
{

    public $filters = array();

    public $compressors = array();

    public $files = array();

    public $asset;

    public $isJavascript;

    public $isStylesheet;

    public $isCoffeescript;

    public $content;

    public $filetype;

    // attributes for assets rendering
    public $attributes = array();

    // cache
    private $_lastmtime = 0;

    const FILETYPE_FILE   = 1;
    const FILETYPE_JS     = 2;
    const FILETYPE_CSS    = 3;
    const FILETYPE_SASS   = 4;
    const FILETYPE_SCSS   = 5;
    const FILETYPE_COFFEE = 6;

    /**
     * Return source path (with relative or absolute path)
     *
     * @param bool $absolute Should return absolute or relative.
     * @return string
     */
    public function getSourcePaths($absolute = false)
    {
        $dir = $this->asset->getSourceDir($absolute);
        return array_map(function($file) use ($dir) {
                return $dir . DIRECTORY_SEPARATOR . $file;
            }, $this->files);
    }


    /**
     * Return fullpath of files
     *
     * @return string[] fullpaths.
     */
    public function getFullpaths()
    {
        $paths = array();
        $dir = $this->asset->getSourceDir(true);
        foreach( $this->files as $file ) {
            $paths[] = $dir . DIRECTORY_SEPARATOR . $file;
        }
        return $paths;
    }


    /**
     * @return array return the collection file list
     */
    public function getFilePaths()
    {
        return $this->files;
    }

    public function addFile($path)
    {
        $this->files[] = $path;
        return $this;
    }


    public function hasCompressor($name)
    {
        return in_array( $name, $this->compressors );
    }

    public function hasFilter($name)
    {
        return in_array( $name, $this->filters );
    }

    public function getCompressors()
    {
        return $this->compressors;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function addFilter($filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function getLastModifiedTime()
    {
        if ( $this->_lastmtime ) {
            return $this->_lastmtime;
        }

        if ( ! empty($this->files) ) {
            $fullpaths = $this->getFullpaths();
            $mtimes = array();
            foreach( $fullpaths as $fullpath ) {
                $mtimes[] = filemtime($fullpath);
            }
            rsort($mtimes, SORT_NUMERIC);
            return $mtimes[0];
        }
    }

    public function setContent($content)
    {
        $this->content = $content;
    }


    /**
     * Returns content chunks with metadata.
     *
     * @return [content=>,path=>,fullpath=>][]
     */
    public function getContents()
    {
        $sourceDir = $this->asset->getSourceDir(true);
        $contents = array();
        foreach( $this->files as $file ) {
            $fullpath = $sourceDir . DIRECTORY_SEPARATOR . $file;

            if ( ($out = file_get_contents( $fullpath )) !== false ) {
                $contents[] = array(
                    'content' => $out,
                    'path'    => $file,
                    'fullpath' => $fullpath,
                );
            } else {
                throw new Exception("Asset collection: Can not read file $fullpath");
            }
        }
        return $contents;
    }


    public function getContent()
    {
        if( $this->content ) {
            return $this->content;
        }

        $sourceDir = $this->asset->getSourceDir(true);
        $content = '';
        foreach( $this->files as $file ) {
            $fullpath = $sourceDir . DIRECTORY_SEPARATOR . $file;

            if ( ($out = file_get_contents( $fullpath )) !== false ) {
                $content .= $out;
            } else {
                throw new Exception("Asset collection: Can not read file $fullpath");
            }
        }
        return $this->content = $content;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getSourcePaths(true));
    }





    /**
     * Run default filters, for coffee-script, sass, scss filetype,
     * these content must be filtered.
     *
     * @return bool returns true if filter is matched, returns false if there is no filter matched.
     */
    public function runDefaultFilters()
    {
        if ( $this->isCoffeescript || $this->filetype === self::FILETYPE_COFFEE ) {
            $coffee = new Filter\CoffeeScriptFilter;
            $coffee->filter( $this );
            return true;
        } elseif ( $this->filetype === self::FILETYPE_SASS ) {
            $sass = new Filter\SassFilter;
            $sass->filter( $this );
            return true;
        } elseif ( $this->filetype === self::FILETYPE_SCSS ) {
            $scss = new Filter\ScssFilter;
            $scss->filter( $this );
            return true;
        }
        return false;
    }

}

