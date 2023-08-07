<?php

/**
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global int $CACHE_STAT_BYTES
 */

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Diag\CacheTracker;

IncludeModuleLangFile(__FILE__);

class CDebugInfo
{
	var $start_time;
	/** @var \Bitrix\Main\Diag\SqlTracker */
	var $savedTracker = null;
	var $cache_size = 0;
	var $arCacheDebugSave;
	var $arResult;
	static $level = 0;
	var $is_comp = true;
	var $index = 0;

	public function __construct($is_comp = true)
	{
		$this->is_comp = $is_comp;
	}

	public function Start()
	{
		global $APPLICATION;
		global $DB;
		global $CACHE_STAT_BYTES;

		if ($this->is_comp)
		{
			self::$level++;
		}

		$this->start_time = microtime(true);
		if ($DB->ShowSqlStat)
		{
			$application = Application::getInstance();
			$connection  = $application->getConnection();
			$this->savedTracker = $application->getConnection()->getTracker();

			$connection->setTracker(null);
			$connection->startTracker();
			$DB->sqlTracker = $connection->getTracker();
		}

		if (Cache::getShowCacheStat())
		{
			$this->arCacheDebugSave = CacheTracker::getCacheTracking();
			CacheTracker::setCacheTracking([]);
			$this->cache_size = CacheTracker::getCacheStatBytes();
			CacheTracker::setCacheStatBytes($CACHE_STAT_BYTES = 0);
		}
		$this->arResult = [];
		$this->index = count($APPLICATION->arIncludeDebug);
		$APPLICATION->arIncludeDebug[$this->index] = &$this->arResult;
	}

	public function Stop($rel_path = "", $path = "", $cache_type = "")
	{
		global $DB;
		global $CACHE_STAT_BYTES;

		if ($this->is_comp)
		{
			self::$level--;
		}

		$this->arResult = [
			"PATH" => $path,
			"REL_PATH" => $rel_path,
			"QUERY_COUNT" => 0,
			"QUERY_TIME" => 0,
			"QUERIES" => [],
			"TIME" => (microtime(true) - $this->start_time),
			"BX_STATE" => $GLOBALS["BX_STATE"],
			"CACHE_TYPE" => $cache_type,
			"CACHE_SIZE" => Cache::getShowCacheStat() ? CacheTracker::getCacheStatBytes() : 0,
			"LEVEL" => self::$level,
		];

		if ($this->savedTracker)
		{
			$application = Application::getInstance();
			$connection = $application->getConnection();
			$sqlTracker = $connection->getTracker();

			if ($sqlTracker->getCounter() > 0)
			{
				$this->arResult["QUERY_COUNT"] = $sqlTracker->getCounter();
				$this->arResult["QUERY_TIME"] = $sqlTracker->getTime();
				$this->arResult["QUERIES"] = $sqlTracker->getQueries();
			}

			$connection->setTracker($this->savedTracker);
			$DB->sqlTracker = $connection->getTracker();
			$this->savedTracker = null;
		}

		if (Cache::getShowCacheStat())
		{
			$this->arResult["CACHE"] = CacheTracker::getCacheTracking();
			CacheTracker::setCacheTracking($this->arCacheDebugSave);
			CacheTracker::setCacheStatBytes($CACHE_STAT_BYTES = $this->cache_size);
		}
	}

	public function Output($rel_path = "", $path = "", $cache_type = "")
	{
		$this->Stop($rel_path, $path, $cache_type);

		$result = '<div class="bx-component-debug">';
		$result .= ($rel_path <> "" ? $rel_path . ": " : "") . "<nobr>" . round($this->arResult["TIME"], 4)
			. " " . Loc::getMessage("main_incl_file_sec")."</nobr>";

		if ($this->arResult["QUERY_COUNT"])
		{
			$result .= '; <a title="' . Loc::getMessage("main_incl_file_sql_stat") . '" href="javascript:BX_DEBUG_INFO_'
				. $this->index . '.Show(); BX_DEBUG_INFO_' . $this->index
				. '.ShowDetails(\'BX_DEBUG_INFO_' . $this->index.'_1\'); ">' . Loc::getMessage("main_incl_file_sql")
				. ' ' . ($this->arResult["QUERY_COUNT"]) . ' (' . round($this->arResult["QUERY_TIME"], 4)
				. ' ' . Loc::getMessage("main_incl_file_sec") . ')</a>';
		}

		if ($this->arResult["CACHE_SIZE"])
		{
			if ($this->arResult["CACHE"] && !empty($this->arResult["CACHE"]))
			{
				$result .= '<nobr>; <a href="javascript:BX_DEBUG_INFO_CACHE_' . $this->index
					. '.Show(); BX_DEBUG_INFO_CACHE_' . $this->index . '.ShowDetails(\'BX_DEBUG_INFO_CACHE_'
					. $this->index . '_0\');">' . Loc::getMessage("main_incl_cache_stat") . '</a> '
					. CFile::FormatSize($this->arResult["CACHE_SIZE"], 0) . '</nobr>';
			}
			else
			{
				$result .= "<nobr>; " . Loc::getMessage("main_incl_cache_stat") . " "
					. CFile::FormatSize($this->arResult["CACHE_SIZE"], 0) . "</nobr>";
			}
		}

		return $result . "</div>";
	}
}