<?php

namespace Bitrix\Vote\Base;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Diag\SqlTrackerQuery;
use Bitrix\Main\SystemException;

final class Diag
{
	private $showOnDisplay = 0;
	private $exclusiveUserId = null;
	private $enableLog = false;

	/** @var  Diag */
	private static $instance;
	private $stackSql = array();
	private $stackMemory = array();
	/** @var Connection connection */
	private $connection;

	private function __construct()
	{
		$this->connection = Application::getInstance()->getConnection();
	}

	/**
	 * Gets instance of Diag.
	 * @return Diag
	 */
	public static function getInstance()
	{
		if(!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Sets value to status of show log message on display.
	 * @param bool $showOnDisplay Value.
	 * @return $this
	 */
	public function setShowOnDisplay($showOnDisplay)
	{
		$this->showOnDisplay = $showOnDisplay;

		return $this;
	}

	/**
	 * Sets user id who can use class Diag. If set null then everybody can use it.
	 * @param int $exclusiveUserId Id of user.
	 * @return $this
	 */
	public function setExclusiveUserId($exclusiveUserId)
	{
		$this->exclusiveUserId = $exclusiveUserId;

		return $this;
	}

	/**
	 * Activate log.
	 * @return $this
	 */
	public function activate()
	{
		$this->enableLog = true;
		return $this;
	}

	/**
	 * Deactivate log.
	 * @return $this
	 */
	public function deactivate()
	{
		$this->enableLog = false;
		return $this;
	}
	/**
	 * Collects debug info.
	 * @param mixed $uniqueId Id of segment.
	 * @return void
	 */
	public function collectDebugInfo($uniqueId)
	{
		if ($this->enableLog !== true || ($this->exclusiveUserId !== null && $this->getUser()->getId() != $this->exclusiveUserId))
		{
			return;
		}
		Debug::startTimeLabel($uniqueId);

		if(empty($this->stackSql))
		{
			$this->connection->startTracker(true);
			array_push($this->stackSql, array($uniqueId, 0, array()));
		}
		else
		{
			list($prevLabel, $prevLabelCount, $prevSqlTrackerQueries) = array_pop($this->stackSql);
			list($countQueries, $sqlTrackerQueries) = $this->getDebugInfoSql();
			array_push($this->stackSql, array($prevLabel, $countQueries + $prevLabelCount, array_merge($prevSqlTrackerQueries, $sqlTrackerQueries)));

			$this->connection->startTracker(true);
			array_push($this->stackSql, array($uniqueId, 0, array()));
		}
		array_push($this->stackMemory, array($uniqueId, memory_get_usage(true)));
	}

	private function getDebugInfoSql()
	{
		if ($tracker = $this->connection->getTracker())
		{
			$sqlTrackerQueries = $tracker->getQueries();
			return array(count($sqlTrackerQueries), $sqlTrackerQueries);
		}
		return array(0, array());
	}

	/**
	 * Logs debug info.
	 * @param mixed $uniqueId Id of segment.
	 * @param null  $label Label for human.
	 * @throws SystemException
	 * @return void
	 */
	public function logDebugInfo($uniqueId, $label = null)
	{
		if($label === null)
		{
			$label = $uniqueId;
		}

		if ($this->enableLog !== true || ($this->exclusiveUserId !== null && $this->getUser()->getId() != $this->exclusiveUserId))
		{
			return;
		}

		Debug::endTimeLabel($uniqueId);
		$timeLabels = Debug::getTimeLabels();

		$debugData = array(
			"Time: {$timeLabels[$uniqueId]['time']}"
		);

		list($prevLabel, $prevLabelCount, $prevSqlTrackerQueries) = array_pop($this->stackSql);
		list($countQueries, $sqlTrackerQueries) = $this->getDebugInfoSql();
		if($countQueries === null)
		{
			$sqlTrackerQueries = array();
			$debugData[] = 'Sql tracker has not been found.';
		}
		else
		{
			if($prevLabel === $uniqueId)
			{
				$countQueries += $prevLabelCount;
				$sqlTrackerQueries = array_merge($prevSqlTrackerQueries, $sqlTrackerQueries);
			}
			$debugData[] = 'Count sql: ' . $countQueries;

		}
		/** @var SqlTrackerQuery[] $sqlTrackerQueries */
		foreach($sqlTrackerQueries as $query)
		{
			$debugData[] = array(
				$query->getTime(),
				$query->getSql(),
				$this->reformatBackTrace($query->getTrace())
			);
			unset($query);
		}

		list($prevLabel, $prevMemoryStart) = array_pop($this->stackMemory);
		if($prevLabel === $uniqueId)
		{
			$debugData[] = 'Memory start: ' . \CFile::FormatSize($prevMemoryStart);
			$debugData[] = 'Memory diff: ' . \CFile::FormatSize(memory_get_usage(true) - $prevMemoryStart);
		}
		$debugData[] = 'Memory amount: ' . \CFile::FormatSize(memory_get_usage(true));
		$debugData[] = 'Memory peak usage: ' . \CFile::FormatSize(memory_get_peak_usage(true));
		array_unshift($debugData, "Label: {$label}");
		$this->log($debugData);
	}

	/**
	 * Logs data in common log (@see AddMessage2Log).
	 * @param mixed $data Mixed data to log.
	 * @return void
	 */
	public function log($data)
	{
		$this->showOnDisplay && var_dump($data);
		AddMessage2Log(var_export($data, true), 'vote', 0);
	}

	private function reformatBackTrace(array $backtrace)
	{
		$functionStack = $filesStack = '';
		foreach($backtrace as $b)
		{
			if($functionStack <> '')
			{
				$functionStack .= " < ";
			}

			if(isset($b["class"]))
			{
				$functionStack .= $b["class"] . "::";
			}

			$functionStack .= $b["function"];

			if(isset($b["file"]))
			{
				$filesStack .= "\t" . $b["file"] . ":" . $b["line"] . "\n";
			}
		}

		return $functionStack . "\n" . $filesStack;
	}

	/**
	 * @return array|bool|\CUser
	 */
	private function getUser()
	{
		global $USER;
		return $USER;
	}
} 