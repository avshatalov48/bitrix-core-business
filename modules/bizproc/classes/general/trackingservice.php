<?php

use Bitrix\Main;

class CBPAllTrackingService
	extends CBPRuntimeService
{
	protected $skipTypes = [];
	protected $forcedModeWorkflows = [];
	protected static $userGroupsCache = [];

	private $cutQueue = [];

	public function Start(CBPRuntime $runtime = null)
	{
		parent::Start($runtime);

		$skipTypes = \Bitrix\Main\Config\Option::get("bizproc", "log_skip_types", CBPTrackingType::ExecuteActivity.','.CBPTrackingType::CloseActivity);
		if ($skipTypes !== '')
		{
			$this->skipTypes = explode(',', $skipTypes);
		}
	}

	public function DeleteAllWorkflowTracking($workflowId)
	{
		self::DeleteByWorkflow($workflowId);
	}

	public static function DumpWorkflow($workflowId)
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

	public function LoadReport($workflowId)
	{
		$result = array();

		$dbResult = CBPTrackingService::GetList(
			array("ID" => "ASC"),
			array("WORKFLOW_ID" => $workflowId, "TYPE" => CBPTrackingType::Report),
			false,
			false,
			array("ID", "MODIFIED", "ACTION_NOTE")
		);
		while ($arResult = $dbResult->GetNext())
			$result[] = $arResult;

		return $result;
	}

	public static function DeleteByWorkflow($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (!$workflowId)
		{
			throw new Exception("workflowId");
		}

		$DB->Query(
			"DELETE FROM b_bp_tracking ".
			"WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' ",
			true
		);
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

	public static function ClearOldAgent()
	{
		CBPTrackingService::ClearOld(COption::GetOptionString("bizproc", "log_cleanup_days", "90"));
		return "CBPTrackingService::ClearOldAgent();";
	}

	public static function parseStringParameter($string, $documentType = null)
	{
		if (!$documentType)
		{
			$documentType = ['', '', ''];
		}

		return preg_replace_callback(
			CBPActivity::ValueInlinePattern,
			function ($matches) use ($documentType) {
				return CBPAllTrackingService::parseStringParameterMatches(
					$matches,
					[$documentType[0], $documentType[1], $documentType[2]]
				);
			},
			$string
		);
	}

	public static function parseStringParameterMatches($matches, $documentType = null)
	{
		$result = "";
		$documentType = is_array($documentType) ? array_filter($documentType) : null;

		if ($matches[1] == "user")
		{
			$user = $matches[2];

			$l = mb_strlen("user_");
			if (mb_substr($user, 0, $l) == "user_")
			{
				$result = CBPHelper::ConvertUserToPrintableForm(intval(mb_substr($user, $l)));
			}
			elseif (mb_strpos($user, 'group_') === 0)
			{
				$result = htmlspecialcharsbx(CBPHelper::getExtendedGroupName($user));
			}
			elseif ($documentType)
			{
				$v = implode(",", $documentType);
				if (!array_key_exists($v,self::$userGroupsCache ))
					self::$userGroupsCache[$v] = CBPDocument::GetAllowableUserGroups($documentType);

				$result = self::$userGroupsCache[$v][$user];
			}
			else
				$result = $user;
		}
		elseif ($matches[1] == "group")
		{
			if (mb_strpos($matches[2], 'group_') === 0)
			{
				$result = htmlspecialcharsbx(CBPHelper::getExtendedGroupName($matches[2]));
			}
			elseif ($documentType)
			{
				$v = implode(",", $documentType);
				if (!array_key_exists($v, self::$userGroupsCache))
					self::$userGroupsCache[$v] = CBPDocument::GetAllowableUserGroups($documentType);

				$result = self::$userGroupsCache[$v][$matches[2]];
			}
			else
				$result = $matches[2];
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
		return (!in_array($type, $this->skipTypes) || $this->isForcedMode($workflowId));
	}

	public function Write($workflowId, $type, $actionName, $executionStatus, $executionResult, $actionTitle = "", $actionNote = "", $modifiedBy = 0)
	{
		global $DB;

		if (!$this->canWrite($type, $workflowId))
			return;

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

		$DB->Query(
			"INSERT INTO b_bp_tracking(WORKFLOW_ID, TYPE, MODIFIED, ACTION_NAME, ACTION_TITLE, EXECUTION_STATUS, EXECUTION_RESULT, ACTION_NOTE, MODIFIED_BY) ".
			"VALUES('".$DB->ForSql($workflowId, 32)."', ".intval($type).", ".$DB->CurrentTimeFunction().", '".$DB->ForSql($actionName, 128)."', '".$DB->ForSql($actionTitle, 255)."', ".intval($executionStatus).", ".intval($executionResult).", ".($actionNote <> '' ? "'".$DB->ForSql($actionNote)."'" : "NULL").", ".($modifiedBy > 0 ? $modifiedBy : "NULL").")"
		);

		if (self::getLogSizeLimit() && !$this->isForcedMode($workflowId))
		{
			$this->cutLogSizeDeferred($workflowId);
		}
	}

	public static function GetList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "WORKFLOW_ID", "TYPE", "MODIFIED", "ACTION_NAME", "ACTION_TITLE", "EXECUTION_STATUS", "EXECUTION_RESULT", "ACTION_NOTE", "MODIFIED_BY");

		static $arFields = array(
			"ID" => Array("FIELD" => "T.ID", "TYPE" => "int"),
			"WORKFLOW_ID" => Array("FIELD" => "T.WORKFLOW_ID", "TYPE" => "string"),
			"TYPE" => Array("FIELD" => "T.TYPE", "TYPE" => "int"),
			"ACTION_NAME" => Array("FIELD" => "T.ACTION_NAME", "TYPE" => "string"),
			"ACTION_TITLE" => Array("FIELD" => "T.ACTION_TITLE", "TYPE" => "string"),
			"MODIFIED" => Array("FIELD" => "T.MODIFIED", "TYPE" => "datetime"),
			"EXECUTION_STATUS" => Array("FIELD" => "T.EXECUTION_STATUS", "TYPE" => "int"),
			"EXECUTION_RESULT" => Array("FIELD" => "T.EXECUTION_RESULT", "TYPE" => "int"),
			"ACTION_NOTE" => Array("FIELD" => "T.ACTION_NOTE", "TYPE" => "string"),
			"MODIFIED_BY" => Array("FIELD" => "T.MODIFIED_BY", "TYPE" => "int"),
		);

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
				return $arRes["CNT"];
			else
				return False;
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
					$cnt = $arRes["CNT"];
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

		return $dbRes;
	}

	public static function ClearOld($days = 0)
	{
		global $DB;

		$days = intval($days);
		if ($days <= 0)
		{
			$days = 90;
		}

		$completed = self::shouldClearCompletedTracksOnly() ? "= 'Y'" : "IN ('N', 'Y')";

		$strSql = "DELETE t FROM b_bp_tracking t".
			" WHERE t.COMPLETED {$completed} ".
			" AND t.MODIFIED < DATE_SUB(NOW(), INTERVAL ".$days." DAY)".
			" AND t.TYPE IN (0,1,2,3,4,5,7,8,9)";
		$bSuccess = $DB->Query($strSql, true);

		return $bSuccess;
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

class CBPTrackingType
{
	const Unknown = 0;
	const ExecuteActivity = 1;
	const CloseActivity = 2;
	const CancelActivity = 3;
	const FaultActivity = 4;
	const Custom = 5;
	const Report = 6;
	const AttachedEntity = 7;
	const Trigger = 8;
	const Error = 9;
}

//Compatibility
class CBPTrackingService extends CBPAllTrackingService {}