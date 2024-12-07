<?php
namespace Bitrix\Main\Composite\Data;

use Bitrix\Main\Composite\Helper;
use Bitrix\Main\Composite\MemcachedResponse;

/**
 * Class MemcachedStorage
 * Stores html cache in a memcached
 * @package Bitrix\Main\Composite\Data
 */
final class MemcachedStorage extends AbstractStorage
{
	private $memcached = null;
	private $props = null;
	private $compression = true;

	public function __construct($cacheKey, array $configuration, array $htmlCacheOptions)
	{
		parent::__construct($cacheKey, $configuration, $htmlCacheOptions);
		$this->memcached = MemcachedResponse::getConnection($configuration, $htmlCacheOptions);
		if ($this->memcached !== null)
		{
			$this->memcached->setCompressThreshold(0);
		}
		
		$this->compression = !isset($htmlCacheOptions["MEMCACHE_COMPRESSION"]) || $htmlCacheOptions["MEMCACHE_COMPRESSION"] !== "N";
	}

	public function write($content, $md5)
	{
		$flags = 0;
		if ($this->compression)
		{
			$flags = MemcachedResponse::MEMCACHED_GZIP_FLAG;
			$content = gzencode($content, 4);
		}

		if (!$this->memcached || !$this->memcached->set($this->cacheKey, $content, $flags))
		{
			return false;
		}

		$this->props = new \stdClass();
		$this->props->mtime = time();
		$this->props->etag = md5($this->cacheKey.$this->props->size.$this->props->mtime);
		$this->props->type = "text/html; charset=".LANG_CHARSET;
		$this->props->md5 = $md5;
		$this->props->gzip = $this->compression;

		$this->props->size = strlen($content);
		$this->props->size += strlen(serialize($this->props));

		$this->memcached->set("~".$this->cacheKey, $this->props);

		return $this->props->size;
	}

	public function read()
	{
		if ($this->memcached !== null)
		{
			$flags = 0;
			$content = $this->memcached->get($this->cacheKey, $flags);
			return $flags & MemcachedResponse::MEMCACHED_GZIP_FLAG ? Helper::gzdecode($content) : $content;
		}

		return false;
	}

	public function exists()
	{
		return $this->getProps() !== false;
	}

	public function delete()
	{
		if ($this->memcached && $this->memcached->delete($this->cacheKey))
		{
			$size = $this->getProp("size");
			$this->deleteProps();
			return $size;
		}

		return false;
	}

	public function deleteAll()
	{
		if ($this->memcached)
		{
			return $this->memcached->flush();
		}

		return false;
	}

	/**
	 * Returns the md5 hash of the cache
	 * @return string|false
	 */
	public function getMd5()
	{
		return $this->getProp("md5");
	}

	/**
	 * Should we count a quota limit
	 * @return bool
	 */
	public function shouldCountQuota()
	{
		return false;
	}

	/**
	 * Returns the time the cache was last modified
	 * @return int|false
	 */
	public function getLastModified()
	{
		return $this->getProp("mtime");
	}
	
	/**
	 * Returns the size of the cache
	 *
	 * @return int|false
	 */
	public function getSize()
	{
		return $this->getProp("size");
	}

	/**
	 * Returns an array of the cache properties
	 *
	 * @return \stdClass|false
	 */
	protected function getProps()
	{
		if ($this->props === null)
		{
			if ($this->memcached !== null)
			{
				$props = $this->memcached->get("~".$this->cacheKey);
				$this->props = is_object($props) ? $props : false;
			}
			else
			{
				$this->props = false;
			}
		}

		return $this->props;
	}

	/**
	 * Deletes the cache properties
	 *
	 * @return bool
	 */
	protected function deleteProps()
	{
		if ($this->memcached)
		{
			$this->props = false;
			return $this->memcached->delete("~".$this->cacheKey);
		}

		return false;
	}

	/**
	 * Returns the property value
	 * @param string $property the property name
	 *
	 * @return string|false
	 */
	protected function getProp($property)
	{
		$props = $this->getProps();
		if ($props !== false && isset($props->{$property}))
		{
			return $props->{$property};
		}

		return false;
	}
}

class_alias("Bitrix\\Main\\Composite\\Data\\MemcachedStorage", "Bitrix\\Main\\Data\\StaticHtmlMemcachedStorage");