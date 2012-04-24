<?php
namespace AssetKit\Filter;

class CssImportFilter
{

	public function filter($collection) 
	{
		// get css files and find @import statement to import related content
		//
		// XXX: notice, base path ($file is a related path)
		foreach( $collection->getFilePaths() as $file ) {
			$content = file_get_contents( $file );
			preg_replace_callback('#url\(([^)]+)\)#' , function($matches) {
				list($orig,$url) = $matches;
				var_dump( $url ); 
			}, $content );
#  			preg_replace_callback('#@import\s+"[^"]*"#', function($matches) { 
#  				var_dump( $matches ); 
#  			});
		}
	}

}
