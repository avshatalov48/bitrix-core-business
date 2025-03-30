<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Data;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Internal\CacheTagTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class TaggedCache
{
	protected int $limit = 200;
	protected int $delLimit = 100;
	protected array $cacheStack = [];
	protected array $cacheTag = [];
	protected string $salt = '';
	protected bool $wasTagged = false;
	protected ConnectionPool $pool;

	public function __construct()
	{
		$this->pool = Application::getInstance()->getConnectionPool();
	}

	protected function initDbCache($path): void
	{
		if (!isset($this->cacheTag[$path]))
		{
			$this->cacheTag[$path] = [];
			$this->pool->useMasterOnly(true);

			$tags = CacheTagTable::query()
				->setSelect(['ID', 'TAG'])
				->where('SITE_ID', SITE_ID)
				->where('CACHE_SALT', $this->salt)
				->where('RELATIVE_PATH', $path)
				->fetchAll();

			foreach ($tags as $tag)
			{
				$this->cacheTag[$path][$tag['TAG']] = true;
			}

			$this->pool->useMasterOnly(false);
			unset($tags, $tag);
		}
	}

	protected function initCompSalt(): void
	{
		if ($this->salt == '')
		{
			$this->salt = Cache::getSalt();
		}
	}

	public function startTagCache($relativePath) : void
	{
		array_unshift($this->cacheStack, [$relativePath, []]);
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function endTagCache(): void
	{
		$this->initCompSalt();
		if ($this->wasTagged)
		{
			$cnt = 0;
			$tags = [];
			$this->pool->useMasterOnly(true);

			foreach ($this->cacheStack as $cache)
			{
				$path = $cache[0];
				if ($path <> '')
				{
					$this->initDbCache($path);
					foreach ($cache[1] as $tag => $t)
					{
						if (!isset($this->cacheTag[$path][$tag]))
						{
							$cnt++;
							$tags[] = [
								'TAG' => $tag,
								'RELATIVE_PATH' => $path,
								'SITE_ID' => SITE_ID,
								'CACHE_SALT' => $this->salt
							];

							$this->cacheTag[$path][$tag] = true;
						}

						if ($cnt > $this->limit)
						{
							CacheTagTable::addMulti($tags, true);
							$cnt = 0;
							$tags = [];
						}
					}
				}
			}

			array_shift($this->cacheStack);

			if (!empty($tags))
			{
				CacheTagTable::addMulti($tags, true);
			}

			$this->pool->useMasterOnly(false);
		}
	}

	public function abortTagCache(): void
	{
		array_shift($this->cacheStack);
	}

	public function registerTag($tag): void
	{
		if (!empty($this->cacheStack))
		{
			$this->cacheStack = array_map(
				function($val) use ($tag) {
					$val[1][$tag] = true;
					return $val;
				}, $this->cacheStack
			);

			$this->wasTagged = true;
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function clearByTag($tag): void
	{
		$cnt = 0;
		$id = [];
		$paths = [];

		$this->pool->useMasterOnly(true);
		$cache = Cache::createInstance();

		$query = CacheTagTable::query()->setSelect(['ID', 'RELATIVE_PATH']);

		if ($tag === true)
		{
			$query->whereNot('TAG', '*');
		}
		else
		{
			$query->where('TAG', $tag);
		}

		$res = $query->exec();
		while ($ar = $res->fetch())
		{
			$cnt++;
			$id[] = $ar['ID'];
			$paths[$ar['RELATIVE_PATH']] = 1;
			if ($cnt > $this->delLimit)
			{
				$this->deleteTags($cache, $id, $paths);

				$cnt = 0;
				$id = [];
				$paths = [];
			}
		}

		if (!empty($id))
		{
			$this->deleteTags($cache, $id, $paths);
		}

		$this->pool->useMasterOnly(false);
		unset($res, $paths, $path, $id);
	}

	protected function deleteTags(Cache $cache, array $id, array $paths): void
	{
		CacheTagTable::deleteByFilter(['@ID' => $id]);
		CacheTagTable::deleteByFilter(['@RELATIVE_PATH' => array_keys($paths)]);

		foreach ($paths as $path => $v)
		{
			$cache->cleanDir($path);
			unset($this->cacheTag[$path]);
		}
	}

	public function deleteAllTags(): void
	{
		CacheTagTable::cleanTable();
	}
}