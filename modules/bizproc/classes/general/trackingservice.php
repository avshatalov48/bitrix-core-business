<?php

use Bitrix\Main;

class CBPTrackingService extends CBPRuntimeService
{
	protected const CLEAR_LOG_SELECT_LIMIT = 50000;
	protected const CLEAR_LOG_DELETE_LIMIT = 1000;
	protected $skipTypes = [];
	protected $forcedModeWorkflows = [];
	protected static $userGroupsCache = [];

	private $cutQueue = [];

	public const DEBUG_TRACK_TYPES = [
		\CBPTrackingType::Debug,
		\CBPTrackingType::DebugAutomation,
		\CBPTrackingType::DebugDesigner,
		\CBPTrackingType::DebugLink
	];

	public function start(CBPRuntime $runtime = null)
	{
		parent::Start($runtime);

		$skipTypes = \Bitrix\Main\Config\Option::get("bizproc", "log_skip_types", CBPTrackingType::ExecuteActivity.','.CBPTrackingType::CloseActivity);
		if ($skipTypes !== '')
		{
			$this->skipTypes = explode(',', $skipTypes);
		}
	}

	public function deleteAllWorkflowTracking($workflowId)
	{
		self::DeleteByWorkflow($workflowId);
	}

	public static function dumpWorkflow($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT ID, TYPE, MODIFIED, ACTION_NAME, ACTION_TITLE, EXECUTION_STATUS, EXECUTION_RESULT, ACTION_NOTE, MODIFIED_BY ".
			"FROM b_bp_tracking ".
			"WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' ".
			"ORDER BY ID "
		);
		$dbResult = new CBPTrackingServiceResult($dbResult);

		$r = array();
		$level = 0;
		while ($arResult = $dbResult->GetNext())
		{
			if ($arResult["TYPE"] == CBPTrackingType::CloseActivity)
			{
				$level--;
				$arResult["PREFIX"] = str_repeat("&nbsp;&nbsp;&nbsp;", $level > 0 ? $level : 0);
				$arResult["LEVEL"] = $level;
			}
			elseif ($arResult["TYPE"] == CBPTrackingType::ExecuteActivity)
			{
				$arResult["PREFIX"] = str_repeat("&nbsp;&nbsp;&nbsp;", $level > 0 ? $level : 0);
				$arResult["LEVEL"] = $level;
				$level++;
			}
			else
			{
				$arResult["PREFIX"] = str_repeat("&nbsp;&nbsp;&nbsp;", $level > 0 ? $level : 0);
				$arResult["LEVEL"] = $level;
			}

			$r[] = $arResult;
		}

		return $r;
	}

	public function loadReport($workflowId, int $limit = 0)
	{
		$result = [];
		$navStartParams = $limit > 0 ? ['nTopCount' => $limit] : false;
		$order = ['ID' => $limit > 0 ? 'DESC' : 'ASC'];

		$dbResult = static::getList(
			$order,
			["WORKFLOW_ID" => $workflowId, "TYPE" => CBPTrackingType::Report],
			false,
			$navStartParams,
			["ID", "MODIFIED", "ACTION_NOTE"]
		);
		while ($arResult = $dbResult->GetNext())
		{
			$result[] = $arResult;
		}

		if ($limit > 0)
		{
			return array_reverse($result);
		}

		return $result;
	}

	public static function deleteByWorkflow($workflowId)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$workflowId = trim($workflowId);
		if (!$workflowId)
		{
			throw new Exception("workflowId");
		}

		$queryString = sprintf(
			"SELECT ID FROM b_bp_tracking t WHERE WORKFLOW_ID = '%s'",
			$helper->forSql($workflowId)
		);

		$ids = $connection->query($queryString)->fetchAll();

		while ($partIds = array_splice($ids, 0, static::CLEAR_LOG_DELETE_LIMIT))
		{
			$connection->query(
				sprintf(
					'DELETE from b_bp_tracking WHERE ID IN(%s)',
					implode(',', array_column($partIds, 'ID'))
				)
			);
		}
	}

	public function setCompletedByWorkflow($workflowId, $flag = true)
	{
		global $DB;

		$workflowId = trim($workflowId);

		if (!$workflowId)
		{
			throw new Exception("workflowId");
		}

		$value1 = $flag ? 'Y' : 'N';
		$value2 = $DB->ForSql($workflowId, 32);

		$DB->Query(
			"UPDATE b_bp_tracking SET COMPLETED = '{$value1}' WHERE WORKFLOW_ID = '{$value2}'"
		);
	}

	public static function clearOldAgent()
	{
		CBPTrackingService::ClearOld(COption::GetOptionString("bizproc", "log_cleanup_days", "90"));

		return "CBPTrackingService::ClearOldAgent();";
	}

	public static function parseStringParameter($string, $documentType = null, $htmlSpecialChars = true)
	{
		if (!$documentType)
		{
			$documentType = ['', '', ''];
		}

		return preg_replace_callback(
			CBPActivity::ValueInlinePattern,
			static function ($matches) use ($documentType, $htmlSpecialChars)
			{
				return CBPAllTrackingService::parseStringParameterMatches(
					$matches,
					[$documentType[0], $documentType[1], $documentType[2]],
					$htmlSpecialChars
				);
			},
			$string
		);
	}

	public static function parseStringParameterMatches($matches, $documentType = null, $htmlSpecialChars = true)
	{
		$result = "";
		$documentType = is_array($documentType) ? array_filter($documentType) : null;

		if ($matches[1] === "user")
		{
			$user = $matches[2];

			$l = mb_strlen("user_");
			if (mb_strpos($user, "user_") === 0)
			{
				$result = CBPHelper::ConvertUserToPrintableForm((int)(mb_substr($user, $l)), '', $htmlSpecialChars);
			}
			elseif (mb_strpos($user, 'group_') === 0)
			{
				$result =
					$htmlSpecialChars
						? htmlspecialcharsbx(CBPHelper::getExtendedGroupName($user))
						: CBPHelper::getExtendedGroupName($user)
				;
			}
			elseif ($documentType)
			{
				$v = implode(",", $documentType);
				if (!array_key_exists($v,self::$userGroupsCache ))
				{
					self::$userGroupsCache[$v] = CBPDocument::GetAllowableUserGroups($documentType);
				}

				$result = self::$userGroupsCache[$v][$user];
			}
			else
			{
				$result = $user;
			}
		}
		elseif ($matches[1] === "group")
		{
			if (mb_strpos($matches[2], 'group_') === 0)
			{
				$result =
					$htmlSpecialChars
						? htmlspecialcharsbx(CBPHelper::getExtendedGroupName($matches[2]))
						: CBPHelper::getExtendedGroupName($matches[2])
				;
			}
			elseif ($documentType)
			{
				$v = implode(",", $documentType);
				if (!array_key_exists($v, self::$userGroupsCache))
				{
					self::$userGroupsCache[$v] = CBPDocument::GetAllowableUserGroups($documentType);
				}

				$result = self::$userGroupsCache[$v][$matches[2]];
			}
			else
			{
				$result = $matches[2];
			}
		}
		else
		{
			$result = $matches[0];
		}

		return $result;
	}

	public function setForcedMode($workflowId)
	{
		$this->forcedModeWorkflows[] = $workflowId;
		return $this;
	}

	public function isForcedMode($workflowId)
	{
		return in_array($workflowId, $this->forcedModeWorkflows);
	}

	public function canWrite($type, $workflowId)
	{
		if (in_array((int)$type, self::DEBUG_TRACK_TYPES, true))
		{
			return false;
		}

		return (!in_array($type, $this->skipTypes) || $this->isForcedMode($workflowId));
	}

	public function write(
		$workflowId,
		$type,
		$actionName,
		$executionStatus,
		$executionResult,
		$actionTitle = "",
		$actionNote = "",
		$modifiedBy = 0
	): ?int
	{
		global $DB;

		if (!$this->canWrite($type, $workflowId))
		{
			return null;
		}

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		$actionName = trim($actionName);
		if ($actionName == '')
			throw new Exception("actionName");

		$type = intval($type);
		$executionStatus = intval($executionStatus);
		$executionResult = intval($executionResult);
		$actionNote = trim($actionNote);

		$modifiedBy = intval($modifiedBy);

		$actionTitle = is_string($actionTitle) ? $actionTitle : '';

		$DB->Query(
			"INSERT INTO b_bp_tracking(WORKFLOW_ID, TYPE, MODIFIED, ACTION_NAME, ACTION_TITLE, EXECUTION_STATUS, EXECUTION_RESULT, ACTION_NOTE, MODIFIED_BY) ".
			"VALUES('".$DB->ForSql($workflowId, 32)."', ".intval($type).", ".$DB->CurrentTimeFunction().", '".$DB->ForSql($actionName, 128)."', '".$DB->ForSql($actionTitle, 255)."', ".intval($executionStatus).", ".intval($executionResult).", ".($actionNote <> '' ? "'".$DB->ForSql($actionNote)."'" : "NULL").", ".($modifiedBy > 0 ? $modifiedBy : "NULL").")"
		);
		$id = $DB->LastID();

		if (self::getLogSizeLimit() && !$this->isForcedMode($workflowId))
		{
			$this->cutLogSizeDeferred($workflowId);
		}

		return $id;
	}

	public static function getList(
		$arOrder = ["ID" => "DESC"],
		$arFilter = [],
		$arGroupBy = false,
		$arNavStartParams = false,
		$arSelectFields = []
	)
	{
		global $DB;

		if (count($arSelectFields) <= 0)
		{
			$arSelectFields = [
				"ID",
				"WORKFLOW_ID",
				"TYPE",
				"MODIFIED",
				"ACTION_NAME",
				"ACTION_TITLE",
				"EXECUTION_STATUS",
				"EXECUTION_RESULT",
				"ACTION_NOTE",
				"MODIFIED_BY",
			];
		}

		static $arFields = [
			"ID" => ["FIELD" => "T.ID", "TYPE" => "int"],
			"WORKFLOW_ID" => ["FIELD" => "T.WORKFLOW_ID", "TYPE" => "string"],
			"TYPE" => ["FIELD" => "T.TYPE", "TYPE" => "int"],
			"ACTION_NAME" => ["FIELD" => "T.ACTION_NAME", "TYPE" => "string"],
			"ACTION_TITLE" => ["FIELD" => "T.ACTION_TITLE", "TYPE" => "string"],
			"MODIFIED" => ["FIELD" => "T.MODIFIED", "TYPE" => "datetime"],
			"EXECUTION_STATUS" => ["FIELD" => "T.EXECUTION_STATUS", "TYPE" => "int"],
			"EXECUTION_RESULT" => ["FIELD" => "T.EXECUTION_RESULT", "TYPE" => "int"],
			"ACTION_NOTE" => ["FIELD" => "T.ACTION_NOTE", "TYPE" => "string"],
			"MODIFIED_BY" => ["FIELD" => "T.MODIFIED_BY", "TYPE" => "int"],
		];

		$arSqls = CBPHelper::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_bp_tracking T ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
			{
				return $arRes["CNT"];
			}

			return false;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_bp_tracking T ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_bp_tracking T ".
				"	".$arSqls["FROM"]." ";
			if ($arSqls["WHERE"] <> '')
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if ($arSqls["GROUPBY"] <> '')
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arSqls["GROUPBY"] == '')
			{
				if ($arRes = $dbRes->Fetch())
				{
					$cnt = $arRes["CNT"];
				}
			}
			else
			{
				// only for MySQL
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return new CBPTrackingServiceResult($dbRes);
	}

	public static function clearOld($days = 0)
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$days = intval($days);
		if ($days <= 0)
		{
			$days = 90;
		}

		$completed = self::shouldClearCompletedTracksOnly() ? "= 'Y'" : "IN ('N', 'Y')";
		$limit = static::CLEAR_LOG_SELECT_LIMIT;
		$partLimit = static::CLEAR_LOG_DELETE_LIMIT;

		$strSql = "SELECT ID FROM b_bp_tracking t WHERE t.COMPLETED {$completed} "
			. " AND t.MODIFIED < DATE_SUB(NOW(), INTERVAL " . $days . " DAY)"
			. " AND t.TYPE IN (0,1,2,3,4,5,7,8,9) LIMIT {$limit}"
		;

		$ids = $connection->query($strSql)->fetchAll();

		while ($partIds = array_splice($ids, 0, $partLimit))
		{
			$connection->query(
				sprintf(
					'DELETE from b_bp_tracking WHERE ID IN(%s)',
					implode(',', array_column($partIds, 'ID'))
				)
			);
		}

		return true;
	}

	private function cutLogSize(string $workflowId, int $size): bool
	{
		global $DB;

		$queryResult = $DB->Query(
			sprintf(
				"SELECT ID FROM b_bp_tracking"
				. " WHERE WORKFLOW_ID = '%s' AND `TYPE` IN (0,1,2,3,4,5,7,8,9) ORDER BY ID DESC LIMIT %d,100",
				$DB->ForSql($workflowId),
				$size
			)
		);

		$ids = [];
		while ($row = $queryResult->fetch())
		{
			$ids[] = $row['ID'];
		}

		if ($ids)
		{
			$DB->Query(
				sprintf(
					'DELETE FROM b_bp_tracking WHERE ID IN (%s)',
					implode(',', $ids)
				),
				true
			);
		}

		return true;
	}

	private function cutLogSizeDeferred(string $workflowId)
	{
		$this->cutQueue[$workflowId] = true;
		$this->setCutJob();
	}

	private function setCutJob()
	{
		static $inserted = false;

		if (!$inserted)
		{
			Main\Application::getInstance()->addBackgroundJob(
				[$this, 'doBackgroundCut'],
				[],
				Main\Application::JOB_PRIORITY_LOW - 10
			);
			$inserted = true;
		}
	}

	public function doBackgroundCut()
	{
		$size = self::getLogSizeLimit();
		$list = array_keys($this->cutQueue);
		$this->cutQueue = [];//clear

		foreach ($list as $workflowId)
		{
			$this->cutLogSize($workflowId, $size);
		}
	}

	private static function getLogSizeLimit(): int
	{
		static $limit;
		if ($limit === null)
		{
			$limit = Main\ModuleManager::isModuleInstalled('bitrix24') ? 50 : 0;
		}

		return $limit;
	}

	public static function shouldClearCompletedTracksOnly(): bool
	{
		if (Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			//more logic later
			return false;
		}

		return true;
	}
}

class CBPTrackingServiceResult extends CDBResult
{
	public function fetch()
	{
		$result = parent::Fetch();

		if ($result && isset($result['ACTION_NOTE']) && is_string($result['ACTION_NOTE']))
		{
			$actionNote = $result['ACTION_NOTE'];

			if (isset($result['TYPE']) && in_array((int)$result['TYPE'], CBPTrackingService::DEBUG_TRACK_TYPES, true))
			{
				$actionNote = \Bitrix\Main\Web\Json::decode($actionNote);
				if (isset($actionNote['propertyValue']) && is_string($actionNote['propertyValue']))
				{
					$propertyValue = $actionNote['propertyValue'];
					$propertyValue = \CBPTrackingService::parseStringParameter($propertyValue, null, false);
					$propertyValue = self::convertTimestampTag($propertyValue);
					$actionNote['propertyValue'] = $propertyValue;
				}

				$result['ACTION_NOTE'] = \Bitrix\Main\Web\Json::encode($actionNote);
			}
			else
			{
				$actionNote = \CBPTrackingService::parseStringParameter($actionNote, null, false);
				$actionNote = self::convertTimestampTag($actionNote);

				$result['ACTION_NOTE'] = $actionNote;
			}
		}

		return $result;
	}

	private static function convertTimestampTag($string): string
	{
		return preg_replace_callback(
			'/\[timestamp=(\d+)\].*\[\/timestamp\]/i',
			static function ($matches)
			{
				$timestamp = (int)$matches[1];

				$datetime = new \Bitrix\Bizproc\BaseType\Value\DateTime($timestamp, CTimeZone::GetOffset());
				if ($datetime->getTimestamp() === null)
				{
					return '';
				}

				return (string)$datetime;
			},
			$string
		);
	}
}

class CBPTrackingType
{
	public const Unknown = 0;
	public const ExecuteActivity = 1;
	public const CloseActivity = 2;
	public const CancelActivity = 3;
	public const FaultActivity = 4;
	public const Custom = 5;
	public const Report = 6;
	public const AttachedEntity = 7;
	public const Trigger = 8;
	public const Error = 9;
	public const Debug = 10;
	public const DebugAutomation = 11;
	public const DebugDesigner = 12;
	public const DebugLink = 13;
}
