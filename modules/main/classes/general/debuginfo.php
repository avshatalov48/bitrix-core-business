<?php

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
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		/** @global CDatabase $DB */
		global $DB;
		/** @global int $CACHE_STAT_BYTES */
		global $CACHE_STAT_BYTES;

		if($this->is_comp)
			self::$level++;

		$this->start_time = getmicrotime();
		if($DB->ShowSqlStat)
		{
			$application = \Bitrix\Main\Application::getInstance();
			$connection  = $application->getConnection();
			$this->savedTracker = $application->getConnection()->getTracker();
			$connection->setTracker(null);
			$connection->startTracker();
			$DB->sqlTracker = $connection->getTracker();
		}

		if(\Bitrix\Main\Data\Cache::getShowCacheStat())
		{
			$this->arCacheDebugSave = \Bitrix\Main\Diag\CacheTracker::getCacheTracking();
			\Bitrix\Main\Diag\CacheTracker::setCacheTracking(array());
			$this->cache_size = \Bitrix\Main\Diag\CacheTracker::getCacheStatBytes();
			\Bitrix\Main\Diag\CacheTracker::setCacheStatBytes($CACHE_STAT_BYTES = 0);
		}
		$this->arResult = array();
		$this->index = count($APPLICATION->arIncludeDebug);
		$APPLICATION->arIncludeDebug[$this->index] = &$this->arResult;
	}

	public function Stop($rel_path="", $path="", $cache_type="")
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		/** @global CDatabase $DB */
		global $DB;
		/** @global int $CACHE_STAT_BYTES */
		global $CACHE_STAT_BYTES;

		if($this->is_comp)
			self::$level--;

		$this->arResult = array(
			"PATH" => $path,
			"REL_PATH" => $rel_path,
			"QUERY_COUNT" => 0,
			"QUERY_TIME" => 0,
			"QUERIES" => array(),
			"TIME" => (getmicrotime() - $this->start_time),
			"BX_STATE" => $GLOBALS["BX_STATE"],
			"CACHE_TYPE" => $cache_type,
			"CACHE_SIZE" => \Bitrix\Main\Data\Cache::getShowCacheStat() ? \Bitrix\Main\Diag\CacheTracker::getCacheStatBytes() : 0,
			"LEVEL" => self::$level,
		);

		if($this->savedTracker)
		{
			$application = \Bitrix\Main\Application::getInstance();
			$connection  = $application->getConnection();
			$sqlTracker  = $connection->getTracker();

			if($sqlTracker->getCounter() > 0)
			{
				$this->arResult["QUERY_COUNT"] = $sqlTracker->getCounter();
				$this->arResult["QUERY_TIME"] = $sqlTracker->getTime();
				$this->arResult["QUERIES"] = $sqlTracker->getQueries();
			}

			$connection->setTracker($this->savedTracker);
			$DB->sqlTracker = $connection->getTracker();
			$this->savedTracker = null;
		}

		if(\Bitrix\Main\Data\Cache::getShowCacheStat())
		{
			$this->arResult["CACHE"] = \Bitrix\Main\Diag\CacheTracker::getCacheTracking();
			\Bitrix\Main\Diag\CacheTracker::setCacheTracking($this->arCacheDebugSave);
			\Bitrix\Main\Diag\CacheTracker::setCacheStatBytes($CACHE_STAT_BYTES = $this->cache_size);
		}
	}

	public function Output($rel_path="", $path="", $cache_type="")
	{
		$this->Stop($rel_path, $path, $cache_type);
		$result = "";

		$result .= '<div class="bx-component-debug">';
		$result .= ($rel_path<>""? $rel_path.": ":"")."<nobr>".round($this->arResult["TIME"], 4)." ".GetMessage("main_incl_file_sec")."</nobr>";

		if($this->arResult["QUERY_COUNT"])
		{
			$result .= '; <a title="'.GetMessage("main_incl_file_sql_stat").'" href="javascript:BX_DEBUG_INFO_'.$this->index.'.Show(); BX_DEBUG_INFO_'.$this->index.'.ShowDetails(\'BX_DEBUG_INFO_'.$this->index.'_1\'); ">'.GetMessage("main_incl_file_sql").' '.($this->arResult["QUERY_COUNT"]).' ('.round($this->arResult["QUERY_TIME"], 4).' '.GetMessage("main_incl_file_sec").')</a>';
		}
		if($this->arResult["CACHE_SIZE"])
		{
			if ($this->arResult["CACHE"] && !empty($this->arResult["CACHE"]))
				$result .= '<nobr>; <a href="javascript:BX_DEBUG_INFO_CACHE_'.$this->index.'.Show(); BX_DEBUG_INFO_CACHE_'.$this->index.'.ShowDetails(\'BX_DEBUG_INFO_CACHE_'.$this->index.'_0\');">'.GetMessage("main_incl_cache_stat").'</a> '.CFile::FormatSize($this->arResult["CACHE_SIZE"], 0).'</nobr>';
			else
				$result .= "<nobr>; ".GetMessage("main_incl_cache_stat")." ".CFile::FormatSize($this->arResult["CACHE_SIZE"], 0)."</nobr>";
		}
		$result .= "</div>";

		return $result;
	}
}
