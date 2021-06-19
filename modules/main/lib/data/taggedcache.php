<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

namespace Bitrix\Main\Data;

use Bitrix\Main;

class TaggedCache
{
	protected $compCacheStack = [];
	protected $salt = false;
	protected $cacheTag = [];
	protected $wasTagged = false;
	protected $isMySql = false;
	protected $pool = false;

	public function __construct()
	{
		$this->pool = Main\Application::getInstance()->getConnectionPool();
		$this->isMySql = ($this->pool->getConnection()->getType() === "mysql");
	}

	protected function initDbCache($path)
	{
		if (!isset($this->cacheTag[$path]))
		{
			$this->cacheTag[$path] = [];

			$con = Main\Application::getConnection();
			$sqlHelper = $con->getSqlHelper();

			$this->pool->useMasterOnly(true);

			$rs = $con->query("
				SELECT TAG
				FROM b_cache_tag
				WHERE SITE_ID = '".$sqlHelper->forSql(SITE_ID, 2)."'
				AND CACHE_SALT = '".$sqlHelper->forSql($this->salt, 4)."'
				AND RELATIVE_PATH = '".$sqlHelper->forSql($path)."'
			");

			while ($ar = $rs->fetch())
			{
				$this->cacheTag[$path][$ar["TAG"]] = true;
			}

			$this->pool->useMasterOnly(false);
		}
	}

	protected function initCompSalt()
	{
		if ($this->salt === false)
		{
			$this->salt = Cache::getSalt();
		}
	}

	public function startTagCache($relativePath)
	{
		array_unshift($this->compCacheStack, [$relativePath, []]);
	}

	public function endTagCache()
	{
		$this->initCompSalt();

		if ($this->wasTagged)
		{
			$this->pool->useMasterOnly(true);

			$con = Main\Application::getConnection();
			$sqlHelper = $con->getSqlHelper();

			// TODO: SITE_ID
			$siteIdForSql = $sqlHelper->forSql(SITE_ID, 2);
			$cacheSaltForSql = $this->salt;

			$strSqlPrefix = "
				INSERT ".($this->isMySql ? "IGNORE": "")." INTO b_cache_tag (SITE_ID, CACHE_SALT, RELATIVE_PATH, TAG)
				VALUES
			";
			$maxValuesLen = $this->isMySql ? 2048: 0;
			$strSqlValues = "";

			foreach ($this->compCacheStack as $arCompCache)
			{
				$path = $arCompCache[0];
				if ($path <> '')
				{
					$this->initDbCache($path);
					$sqlRELATIVE_PATH = $sqlHelper->forSql($path, 255);

					$sql = ",\n('".$siteIdForSql."', '".$cacheSaltForSql."', '".$sqlRELATIVE_PATH."',";

					foreach ($arCompCache[1] as $tag => $t)
					{
						if (!isset($this->cacheTag[$path][$tag]))
						{
							$strSqlValues .= $sql." '".$sqlHelper->forSql($tag, 100)."')";
							if (mb_strlen($strSqlValues) > $maxValuesLen)
							{
								$con->queryExecute($strSqlPrefix.mb_substr($strSqlValues, 2));
								$strSqlValues = "";
							}
							$this->cacheTag[$path][$tag] = true;
						}
					}
				}
			}
			if ($strSqlValues <> '')
			{
				$con->queryExecute($strSqlPrefix.mb_substr($strSqlValues, 2));
			}

			$this->pool->useMasterOnly(false);
		}

		array_shift($this->compCacheStack);
	}

	public function abortTagCache()
	{
		array_shift($this->compCacheStack);
	}

	public function registerTag($tag)
	{
		if (!empty($this->compCacheStack))
		{
			$this->compCacheStack[0][1][$tag] = true;
			$this->wasTagged = true;
		}
	}

	public function clearByTag($tag)
	{
		$this->pool->useMasterOnly(true);

		$con = Main\Application::getConnection();
		$helper = $con->getSqlHelper();

		if ($tag === true)
		{
			$where = " WHERE TAG <> '*'";
		}
		else
		{
			$where = " WHERE TAG = '".$helper->forSql($tag)."'";
		}

		$dirs = [];
		$rs = $con->query("SELECT ID, RELATIVE_PATH FROM b_cache_tag".$where);
		while ($ar = $rs->fetch())
		{
			$dirs[$ar["RELATIVE_PATH"]][] = $ar["ID"];
		}

		$con->queryExecute("DELETE FROM b_cache_tag".$where);

		$max_length = 102400;
		$sql = "DELETE FROM b_cache_tag WHERE ID in (%s)";
		$where_list = array();
		$length = 0;
		$cache = Cache::createInstance();
		foreach ($dirs as $path => $ar)
		{
			$cache->cleanDir($path);
			unset($this->cacheTag[$path]);

			foreach ($ar as $cacheTagId)
			{
				$where = intval($cacheTagId);
				$length += mb_strlen($where) + 1;
				$where_list[] = $where;
				if ($length > $max_length)
				{
					$con->queryExecute(sprintf($sql, implode(",", $where_list)));
					$where_list = array();
					$length = 0;
				}
			}
		}

		if ($where_list)
		{
			$con->queryExecute(sprintf($sql, implode(",", $where_list)));
		}

		$this->pool->useMasterOnly(false);
	}

	public function deleteAllTags()
	{
		$con = Main\Application::getConnection();
		$con->query("TRUNCATE TABLE b_cache_tag");
	}
}
