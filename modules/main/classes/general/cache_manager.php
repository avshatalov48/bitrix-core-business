<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */


// The main purpose of the class is:
// one read - many uses - optional one write
// of the set of variables
class CCacheManager
{
	/** @var Bitrix\Main\Data\ManagedCache */
	private $managedCache;

	/** @var Bitrix\Main\Data\TaggedCache */
	private $taggedCache;

	public function __construct()
	{
		$app = \Bitrix\Main\Application::getInstance();
		$this->managedCache = $app->getManagedCache();
		$this->taggedCache = $app->getTaggedCache();
	}

	// Tries to read cached variable value from the file
	// Returns true on success
	// otherwise returns false
	public function Read($ttl, $uniqid, $table_id=false)
	{
		return $this->managedCache->read($ttl, $uniqid, $table_id);
	}

	public function GetImmediate($ttl, $uniqid, $table_id=false)
	{
		return $this->managedCache->getImmediate($ttl, $uniqid, $table_id);
	}

	// This method is used to read the variable value
	// from the cache after successful Read
	public function Get($uniqid)
	{
		return $this->managedCache->get($uniqid);
	}

	// Sets new value to the variable
	public function Set($uniqid, $val)
	{
		$this->managedCache->set($uniqid, $val);
	}

	public function SetImmediate($uniqid, $val)
	{
		$this->managedCache->setImmediate($uniqid, $val);
	}

	// Marks cache entry as invalid
	public function Clean($uniqid, $table_id=false)
	{
		$this->managedCache->clean($uniqid, $table_id);
	}

	// Marks cache entries associated with the table as invalid
	public function CleanDir($table_id)
	{
		$this->managedCache->cleanDir($table_id);
	}

	// Clears all managed_cache
	public function CleanAll()
	{
		$this->managedCache->cleanAll();
	}

	// Use it to flush cache to the files.
	// Caution: only at the end of all operations!
	public function _Finalize()
	{
		\Bitrix\Main\Data\ManagedCache::finalize();
	}

	function GetCompCachePath($relativePath)
	{
		return $this->managedCache->getCompCachePath($relativePath);
	}

	/*Components managed(tagged) cache*/

	function StartTagCache($relativePath)
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$this->taggedCache->startTagCache($relativePath);
		}
	}

	function EndTagCache()
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$this->taggedCache->endTagCache();
		}
	}

	function AbortTagCache()
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$this->taggedCache->abortTagCache();
		}
	}

	function RegisterTag($tag)
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$this->taggedCache->registerTag($tag);
		}
	}

	function ClearByTag($tag)
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$this->taggedCache->clearByTag($tag);
		}
	}
}
