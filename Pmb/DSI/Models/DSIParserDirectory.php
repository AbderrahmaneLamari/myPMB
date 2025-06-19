<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DSIParserDirectory.php,v 1.1.4.1 2023/07/20 07:51:30 rtigero Exp $

namespace Pmb\DSI\Models;

use Pmb\Common\Library\Parser\ParserDirectory;

class DSIParserDirectory extends ParserDirectory
{
    protected $baseDir = __DIR__;
    /**
     *
     * @var array
     */
    protected $catalog = [];
    
    protected $parserManifest = "\Pmb\DSI\Models\DSIParserManifest";
    
		/**
	 * 
	 * @param string $namespace
	 * @return string[]
	 */
	public function getCompatibility(string $namespace) 
	{
		$manifest = $this->getManifestByNamespace($namespace);
		return $manifest ? $manifest->compatibility : [];
	}
	/**
	 *
	 * @param string $namespace
	 * @return ParserManifest|NULL
	 */
	public function getManifestByNamespace(string $namespace)
	{
	    return !empty($this->manifest[$namespace]) ? $this->manifest[$namespace] : null;
	}
	
	protected function parse() 
	{
	    $path = $this->baseDir;
	    $manifests = $this->loadManifests($path);
	    
	    foreach ($manifests as $manifest) {
	        $this->manifest[$manifest->namespace] = $manifest;
	        
	        if (!isset($this->catalog[$manifest->type])) {
	            $this->catalog[$manifest->type] = [];
	        }
	        $this->catalog[$manifest->type][] = $manifest->namespace;
	    }
	    $this->parsed = true;
	}
	
	/**
	 *
	 * @param string $type
	 * @return string
	 */
	public function getClass(string $type)
	{
	    return !empty($this->catalog[$type]) ? $this->catalog[$type] : "";
	}
	
	/**
	 * Retourne le catalogue
	 * @return array
	 */
	public function getCatalog()
	{
		return $this->catalog;
	}
}

