<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

interface ICacheBackend
{
	function IsAvailable();
	function clean($basedir, $initdir = false, $filename = false);
	function read(&$arAllVars, $basedir, $initdir, $filename, $TTL);
	function write($arAllVars, $basedir, $initdir, $filename, $TTL);
	function IsCacheExpired($path);
}

class CPHPCache
{
	/**
	 * @var Bitrix\Main\Data\Cache
	 */
	private $cache;

	public function __construct()
	{
		$this->cache = \Bitrix\Main\Data\Cache::createInstance();
	}

	public function Clean($uniq_str, $initdir = false, $basedir = "cache")
	{
		return $this->cache->clean($uniq_str, $initdir, $basedir);
	}

	public function CleanDir($initdir = false, $basedir = "cache")
	{
		return $this->cache->cleanDir($initdir, $basedir);
	}

	public function InitCache($TTL, $uniq_str, $initdir=false, $basedir = "cache")
	{
		return $this->cache->initCache($TTL, $uniq_str, $initdir, $basedir);
	}

	public function Output()
	{
		$this->cache->output();
	}

	public function GetVars()
	{
		return $this->cache->getVars();
	}

	public function StartDataCache($TTL=false, $uniq_str=false, $initdir=false, $vars=Array(), $basedir = "cache")
	{
		$narg = func_num_args();
		if($narg<=0)
			return $this->cache->startDataCache();
		if($narg<=1)
			return $this->cache->startDataCache($TTL);
		if($narg<=2)
			return $this->cache->startDataCache($TTL, $uniq_str);
		if($narg<=3)
			return $this->cache->startDataCache($TTL, $uniq_str, $initdir);

		return $this->cache->startDataCache($TTL, $uniq_str, $initdir, $vars, $basedir);
	}

	function AbortDataCache()
	{
		$this->cache->abortDataCache();
	}

	/**
	 * Saves the result of calculation to the cache.
	 *
	 * @param mixed $vars
	 * @return void
	 */
	function EndDataCache($vars=false)
	{
		$this->cache->endDataCache($vars);
	}

	function IsCacheExpired($path)
	{
		if(isset($this) && is_object($this) && ($this instanceof CPHPCache))
		{
			return $this->cache->isCacheExpired($path);
		}
		else
		{
			$obCache = new CPHPCache();
			return $obCache->IsCacheExpired($path);
		}
	}
}

class CPageCache
{
	var $_cache;
	var $filename;
	var $content;
	var $TTL;
	var $bStarted = false;
	var $uniq_str = false;
	var $basedir;
	var $initdir = false;

	function __construct()
	{
		$this->_cache = \Bitrix\Main\Data\Cache::createCacheEngine();
	}

	function GetPath($uniq_str)
	{
		$un = md5($uniq_str);
		return mb_substr($un, 0, 2)."/".$un.".html";
	}

	function Clean($uniq_str, $initdir = false, $basedir = "cache")
	{
		if(isset($this) && is_object($this) && is_object($this->_cache))
		{
			$basedir = BX_PERSONAL_ROOT."/".$basedir."/";
			$filename = CPageCache::GetPath($uniq_str);
			if (\Bitrix\Main\Data\Cache::getShowCacheStat())
				\Bitrix\Main\Diag\CacheTracker::add(0, "", $basedir, $initdir, "/".$filename, "C");
			return $this->_cache->clean($basedir, $initdir, "/".$filename);
		}
		else
		{
			$obCache = new CPageCache();
			return $obCache->Clean($uniq_str, $initdir, $basedir);
		}
	}

	function CleanDir($initdir = false, $basedir = "cache")
	{
		$basedir = BX_PERSONAL_ROOT."/".$basedir."/";
		if (\Bitrix\Main\Data\Cache::getShowCacheStat())
			\Bitrix\Main\Diag\CacheTracker::add(0, "", $basedir, $initdir, "", "C");
		return $this->_cache->clean($basedir, $initdir);
	}

	function InitCache($TTL, $uniq_str, $initdir = false, $basedir = "cache")
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $USER;
		if($initdir === false)
			$initdir = $APPLICATION->GetCurDir();

		$this->basedir = BX_PERSONAL_ROOT."/".$basedir."/";
		$this->initdir = $initdir;
		$this->filename = "/".CPageCache::GetPath($uniq_str);
		$this->TTL = $TTL;
		$this->uniq_str = $uniq_str;

		if($TTL<=0)
			return false;

		if(is_object($USER) && $USER->CanDoOperation('cache_control'))
		{
			if(isset($_GET["clear_cache_session"]))
			{
				if(mb_strtoupper($_GET["clear_cache_session"]) == "Y")
					\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_CLEAR_CACHE"] = "Y";
				elseif($_GET["clear_cache_session"] <> '')
					unset(\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_CLEAR_CACHE"]);
			}

			if(isset($_GET["clear_cache"]) && mb_strtoupper($_GET["clear_cache"]) == "Y")
				return false;
		}

		if(isset(\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_CLEAR_CACHE"]) && \Bitrix\Main\Application::getInstance()->getSession()["SESS_CLEAR_CACHE"] == "Y")
			return false;

		if(!$this->_cache->read($this->content, $this->basedir, $this->initdir, $this->filename, $this->TTL))
			return false;

//		$GLOBALS["CACHE_STAT_BYTES"] += $this->_cache->read;
		if (\Bitrix\Main\Data\Cache::getShowCacheStat())
		{
			$read = 0;
			$path = '';
			if ($this->_cache instanceof \Bitrix\Main\Data\ICacheEngineStat)
			{
				$read = $this->_cache->getReadBytes();
				$path = $this->_cache->getCachePath();
			}
			elseif ($this->_cache instanceof \ICacheBackend)
			{
				/** @noinspection PhpUndefinedFieldInspection */
				$read = $this->_cache->read;

				/** @noinspection PhpUndefinedFieldInspection */
				$path = $this->_cache->path;
			}

			\Bitrix\Main\Diag\CacheTracker::addCacheStatBytes($read);
			\Bitrix\Main\Diag\CacheTracker::add($read, $path, $this->basedir, $this->initdir, $this->filename, "R");
		}
		return true;
	}

	function Output()
	{
		echo $this->content;
	}

	function StartDataCache($TTL, $uniq_str=false, $initdir=false, $basedir = "cache")
	{
		if($this->InitCache($TTL, $uniq_str, $initdir, $basedir))
		{
			$this->Output();
			return false;
		}

		if($TTL<=0)
			return true;

		ob_start();
		$this->bStarted = true;
		return true;
	}

	function AbortDataCache()
	{
		if(!$this->bStarted)
			return;
		$this->bStarted = false;

		ob_end_flush();
	}

	function EndDataCache()
	{
		if(!$this->bStarted)
			return;
		$this->bStarted = false;

		$arAllVars = ob_get_contents();

		$this->_cache->write($arAllVars, $this->basedir, $this->initdir, $this->filename, $this->TTL);

		if (\Bitrix\Main\Data\Cache::getShowCacheStat())
		{
			$written = 0;
			$path = '';
			if ($this->_cache instanceof \Bitrix\Main\Data\ICacheEngineStat)
			{
				$written = $this->_cache->getWrittenBytes();
				$path = $this->_cache->getCachePath();
			}
			elseif ($this->_cache instanceof \ICacheBackend)
			{
				/** @noinspection PhpUndefinedFieldInspection */
				$written = $this->_cache->written;

				/** @noinspection PhpUndefinedFieldInspection */
				$path = $this->_cache->path;
			}
			\Bitrix\Main\Diag\CacheTracker::addCacheStatBytes($written);
			\Bitrix\Main\Diag\CacheTracker::add($written, $path, $this->basedir, $this->initdir, $this->filename, "W");
		}

		if($arAllVars <> '')
			ob_end_flush();
		else
			ob_end_clean();
	}

	function IsCacheExpired($path)
	{
		if(isset($this) && is_object($this) && is_object($this->_cache))
		{
			return $this->_cache->IsCacheExpired($path);
		}
		else
		{
			$obCache = new CPHPCache();
			return $obCache->IsCacheExpired($path);
		}
	}
}

function BXClearCache($full=false, $initdir="")
{
	if($full !== true && $full !== false && $initdir === "" && is_string($full))
	{
		$initdir = $full;
		$full = true;
	}

	$res = true;

	if($full === true)
	{
		$obCache = new CPHPCache;
		$obCache->CleanDir($initdir, "cache");
	}

	$path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/cache".$initdir;
	if(is_dir($path) && ($handle = opendir($path)))
	{
		while(($file = readdir($handle)) !== false)
		{
			if($file == "." || $file == "..") continue;

			if(is_dir($path."/".$file))
			{
				if(!BXClearCache($full, $initdir."/".$file))
				{
					$res = false;
				}
				else
				{
					@chmod($path."/".$file, BX_DIR_PERMISSIONS);
					//We suppress error handle here because there may be valid cache files in this dir
					@rmdir($path."/".$file);
				}
			}
			elseif($full)
			{
				@chmod($path."/".$file, BX_FILE_PERMISSIONS);
				if(!unlink($path."/".$file))
					$res = false;
			}
			elseif(mb_substr($file, -5) == ".html")
			{
				$obCache = new CPHPCache();
				if($obCache->IsCacheExpired($path."/".$file))
				{
					@chmod($path."/".$file, BX_FILE_PERMISSIONS);
					if(!unlink($path."/".$file))
						$res = false;
				}
			}
			elseif(mb_substr($file, -4) == ".php")
			{
				$obCache = new CPHPCache();
				if($obCache->IsCacheExpired($path."/".$file))
				{
					@chmod($path."/".$file, BX_FILE_PERMISSIONS);
					if(!unlink($path."/".$file))
						$res = false;
				}
			}
			else
			{
				//We should skip unknown file
				//it will be deleted with full cache cleanup
			}
		}
		closedir($handle);
	}

	return $res;
}
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

global $CACHE_MANAGER;
$CACHE_MANAGER = new CCacheManager;

$GLOBALS["CACHE_STAT_BYTES"] = 0;

/*****************************************************************************************************/
/************************  CStackCacheManager  *******************************************************/
/*****************************************************************************************************/
class CStackCacheEntry
{
	var $entity = "";
	var $id = "";
	var $values = array();
	var $len = 10;
	var $ttl = 3600;
	var $cleanGet = true;
	var $cleanSet = true;

	function __construct($entity, $length = 0, $ttl = 0)
	{
		$this->entity = $entity;

		if($length > 0)
			$this->len = intval($length);

		if($ttl > 0)
			$this->ttl = intval($ttl);
	}

	function SetLength($length)
	{
		if($length > 0)
			$this->len = intval($length);

		while(count($this->values) > $this->len)
		{
			$this->cleanSet = false;
			array_shift($this->values);
		}
	}

	function SetTTL($ttl)
	{
		if($ttl > 0)
			$this->ttl = intval($ttl);
	}

	function Load()
	{
		global $DB;
		$objCache = \Bitrix\Main\Data\Cache::createInstance();
		if($objCache->InitCache($this->ttl, $this->entity, $DB->type."/".$this->entity, "stack_cache"))
		{
			$this->values = $objCache->GetVars();
			$this->cleanGet = true;
			$this->cleanSet = true;
		}
	}

	function DeleteEntry($id)
	{
		if(array_key_exists($id, $this->values))
		{
			unset($this->values[$id]);
			$this->cleanSet = false;
		}
	}

	function Clean()
	{
		global $DB;

		$objCache = \Bitrix\Main\Data\Cache::createInstance();
		$objCache->Clean($this->entity, $DB->type."/".$this->entity, "stack_cache");

		$this->values = array();
		$this->cleanGet = true;
		$this->cleanSet = true;
	}

	function Get($id)
	{
		if(array_key_exists($id, $this->values))
		{
			$result = $this->values[$id];
			//Move accessed value to the top of list only when it is not at the top
			end($this->values);
			if(key($this->values) !== $id)
			{
				$this->cleanGet = false;
				unset($this->values[$id]);
				$this->values = $this->values + array($id => $result);
			}

			return $result;
		}
		else
		{
			return false;
		}
	}

	function Set($id, $value)
	{
		if(array_key_exists($id, $this->values))
		{
			unset($this->values[$id]);
			$this->values = $this->values + array($id => $value);
		}
		else
		{
			$this->values = $this->values + array($id => $value);
			while(count($this->values) > $this->len)
				array_shift($this->values);
		}

		$this->cleanSet = false;
	}

	function Save()
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		global $DB;

		if(
			!$this->cleanSet
			|| (
				!$this->cleanGet
				&& (count($this->values) >= $this->len)
			)
		)
		{
			$objCache = \Bitrix\Main\Data\Cache::createInstance();

			//Force cache rewrite
			$objCache->forceRewriting(true);

			if($objCache->startDataCache($this->ttl, $this->entity, $DB->type."/".$this->entity, $this->values, "stack_cache"))
			{
				$objCache->endDataCache();
			}

			$this->cleanGet = true;
			$this->cleanSet = true;
		}
	}
}

class CStackCacheManager
{
	/** @var CStackCacheEntry[] */
	var $cache = array();
	var $cacheLen = array();
	var $cacheTTL = array();
	var $eventHandlerAdded = false;

	function SetLength($entity, $length)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if(isset($this->cache[$entity]) && is_object($this->cache[$entity]))
			$this->cache[$entity]->SetLength($length);
		else
			$this->cacheLen[$entity] = $length;
	}

	function SetTTL($entity, $ttl)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if(isset($this->cache[$entity]) && is_object($this->cache[$entity]))
			$this->cache[$entity]->SetTTL($ttl);
		else
			$this->cacheTTL[$entity] = $ttl;
	}

	function Init($entity, $length = 0, $ttl = 0)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!$this->eventHandlerAdded)
		{
			AddEventHandler("main", "OnEpilog", array("CStackCacheManager", "SaveAll"));
			$this->eventHandlerAdded = True;
		}

		if($length <= 0 && isset($this->cacheLen[$entity]))
			$length = $this->cacheLen[$entity];

		if($ttl <= 0 && isset($this->cacheTTL[$entity]))
			$ttl = $this->cacheTTL[$entity];

		if (!array_key_exists($entity, $this->cache))
			$this->cache[$entity] = new CStackCacheEntry($entity, $length, $ttl);
	}

	function Load($entity)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!array_key_exists($entity, $this->cache))
			$this->Init($entity);

		$this->cache[$entity]->Load();
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Clear($entity, $id = False)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		if ($id !== False)
			$this->cache[$entity]->DeleteEntry($id);
		else
			$this->cache[$entity]->Clean();
	}

	// Clears all managed_cache
	function CleanAll()
	{
		$this->cache = array();

		$objCache = new CPHPCache;
		$objCache->CleanDir(false, "stack_cache");
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Exist($entity, $id)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return False;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		return array_key_exists($id, $this->cache[$entity]->values);
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Get($entity, $id)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return False;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		return $this->cache[$entity]->Get($id);
	}

	//NO ONE SHOULD NEVER EVER USE INTEGER $id HERE
	function Set($entity, $id, $value)
	{
		if (defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if (!array_key_exists($entity, $this->cache))
			$this->Load($entity);

		$this->cache[$entity]->Set($id, $value);
	}

	function Save($entity)
	{
		if(defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		if(array_key_exists($entity, $this->cache))
			$this->cache[$entity]->Save();
	}

	public static function SaveAll()
	{
		if(defined("BITRIX_SKIP_STACK_CACHE") && BITRIX_SKIP_STACK_CACHE)
			return;

		/** @global CStackCacheManager $stackCacheManager */
		global $stackCacheManager;

		foreach($stackCacheManager->cache as $value)
		{
			$value->Save();
		}
	}

	function MakeIDFromArray($values)
	{
		$id = "id";

		sort($values);

		for ($i = 0, $c = count($values); $i < $c; $i++)
			$id .= "_".$values[$i];

		return $id;
	}
}

$GLOBALS["stackCacheManager"] = new CStackCacheManager();
