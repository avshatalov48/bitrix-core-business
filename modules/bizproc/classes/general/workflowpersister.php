<?
IncludeModuleLangFile(__FILE__);

/**
* Workflow persistence service.
*/
class CBPAllWorkflowPersister
{
	const LOCK_BY_TIME = false;
	protected $serviceInstanceId = "";
	protected $ownershipDelta = 300;
	protected $useGZipCompression = false;

	private static $instance;

	public function __clone()
	{
		trigger_error('Clone in not allowed.', E_USER_ERROR);
	}

	private function __construct()
	{
		$this->serviceInstanceId = uniqid("", true);
		$this->useGZipCompression = \CBPWorkflowTemplateLoader::useGZipCompression();
	}

	/**
	 * @return self
	 */
	public static function GetPersister()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	protected function RetrieveWorkflow($instanceId, $silent = false)
	{
		global $DB;

		$queryCondition = $this->getLockerQueryCondition();

		$buffer = "";
		$dbResult = $DB->Query(
			"SELECT WORKFLOW, IF (".$queryCondition.", 'Y', 'N') as UPDATEABLE ".
			"FROM b_bp_workflow_instance ".
			"WHERE ID = '".$DB->ForSql($instanceId)."' "
		);
		if ($arResult = $dbResult->Fetch())
		{
			if ($arResult["UPDATEABLE"] == "Y" && !$silent)
			{
				$DB->Query(
					"UPDATE b_bp_workflow_instance SET ".
					"	OWNER_ID = '".$DB->ForSql($this->serviceInstanceId)."', ".
					"	OWNED_UNTIL = ".$DB->CharToDateFunction(date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATETIME), $this->GetOwnershipTimeout()))." ".
					"WHERE ID = '".$DB->ForSql($instanceId)."'"
				);
			}
			elseif (!$silent)
			{
				throw new Exception(GetMessage("BPCGWP_WF_LOCKED"), \CBPRuntime::EXCEPTION_CODE_INSTANCE_LOCKED);
			}
			$buffer = $arResult["WORKFLOW"];
		}
		else
		{
			throw new Exception(GetMessage("BPCGWP_INVALID_WF"), \CBPRuntime::EXCEPTION_CODE_INSTANCE_NOT_FOUND);
		}

		return $buffer;
	}

	protected function InsertWorkflow($id, $buffer, $status, $bUnlocked)
	{
		global $DB;

		$queryCondition = $this->getLockerQueryCondition();

		if ($status == CBPWorkflowStatus::Completed || $status == CBPWorkflowStatus::Terminated)
		{
			$DB->Query(
				"DELETE FROM b_bp_workflow_instance ".
				"WHERE ID = '".$DB->ForSql($id)."'"
			);
		}
		else
		{
			$dbResult = $DB->Query(
				"SELECT ID, IF (".$queryCondition.", 'Y', 'N') as UPDATEABLE ".
				"FROM b_bp_workflow_instance ".
				"WHERE ID = '".$DB->ForSql($id)."' "
			);
			if ($arResult = $dbResult->Fetch())
			{
				if ($arResult["UPDATEABLE"] == "Y")
				{
					$DB->Query(
						"UPDATE b_bp_workflow_instance SET ".
						"	WORKFLOW = '".$DB->ForSql($buffer)."', ".
						"	STATUS = ".intval($status).", ".
						"	MODIFIED = ".$DB->CurrentTimeFunction().", ".
						"	OWNER_ID = ".($bUnlocked ? "NULL" : "'".$DB->ForSql($this->serviceInstanceId)."'").", ".
						"	OWNED_UNTIL = ".($bUnlocked ? "NULL" : $DB->CharToDateFunction(date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATETIME), $this->GetOwnershipTimeout())))." ".
						"WHERE ID = '".$DB->ForSql($id)."' "
					);
				}
				else
				{
					throw new Exception(GetMessage('BPCGWP_WF_LOCKED'), \CBPRuntime::EXCEPTION_CODE_INSTANCE_LOCKED);
				}
			}
			else
			{
				$DB->Query(
					"INSERT INTO b_bp_workflow_instance (ID, WORKFLOW, STATUS, MODIFIED, OWNER_ID, OWNED_UNTIL) ".
					"VALUES ('".$DB->ForSql($id)."', '".$DB->ForSql($buffer)."', ".intval($status).", ".$DB->CurrentTimeFunction().", ".($bUnlocked ? "NULL" : "'".$DB->ForSql($this->serviceInstanceId)."'").", ".($bUnlocked ? "NULL" : $DB->CharToDateFunction(date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATETIME), $this->GetOwnershipTimeout()))).")"
				);
			}
		}
	}

	protected function GetOwnershipTimeout()
	{
		return time() + $this->ownershipDelta;
	}

	public function LoadWorkflow($instanceId, $silent = false)
	{
		$state = $this->RetrieveWorkflow($instanceId, $silent);
		if (strlen($state) > 0)
			return $this->RestoreFromSerializedForm($state);

		throw new Exception("WorkflowNotFound");
	}

	private function RestoreFromSerializedForm($buffer)
	{
		if ($this->useGZipCompression)
			$buffer = gzuncompress($buffer);

		if (strlen($buffer) <= 0)
			throw new Exception("EmptyWorkflowInstance");

		$activity = CBPActivity::Load($buffer);
		return $activity;
	}

	public static function __InsertWorkflowHack($id, $buffer)
	{
		$p = CBPWorkflowPersister::GetPersister();
		if ($p->useGZipCompression)
			$buffer = gzcompress($buffer, 9);
		$p->InsertWorkflow($id, $buffer, 1, true);
	}

	public function SaveWorkflow(CBPActivity $rootActivity, $bUnlocked)
	{
		if ($rootActivity == null)
			throw new Exception("rootActivity");

		$workflowStatus = $rootActivity->GetWorkflowStatus();

		$buffer = "";
		if (($workflowStatus != CBPWorkflowStatus::Completed) && ($workflowStatus != CBPWorkflowStatus::Terminated))
			$buffer = $this->GetSerializedForm($rootActivity);

		$this->InsertWorkflow($rootActivity->GetWorkflowInstanceId(), $buffer, $workflowStatus, $bUnlocked);
	}

	private function GetSerializedForm(CBPActivity $rootActivity)
	{
		$buffer = $rootActivity->Save();

		if ($this->useGZipCompression)
			$buffer = gzcompress($buffer, 9);
		return $buffer;
	}

	public function UnlockWorkflow(CBPActivity $rootActivity)
	{
		global $DB;

		if ($rootActivity == null)
			throw new Exception("rootActivity");

		$DB->Query(
			"UPDATE b_bp_workflow_instance SET ".
			"	OWNER_ID = NULL, ".
			"	OWNED_UNTIL = NULL ".
			"WHERE ID = '".$DB->ForSql($rootActivity->GetWorkflowInstanceId())."' ".
			"	AND ( ".
			"		(OWNER_ID = '".$DB->ForSql($this->serviceInstanceId)."' ".
			"			AND OWNED_UNTIL >= ".$DB->CurrentTimeFunction().") ".
			"		OR ".
			"		(OWNER_ID IS NULL) ".
			"		OR ".
			"		(OWNER_ID IS NOT NULL ".
			"			AND OWNED_UNTIL < ".$DB->CurrentTimeFunction().") ".
			"	)"
		);
	}

	protected function getLockerQueryCondition()
	{
		global $DB;

		if (!static::LOCK_BY_TIME)
		{
			return "(OWNER_ID IS NULL OR OWNER_ID = '".$DB->ForSql($this->serviceInstanceId)."')";
		}

		return
			"( ".
			"	(OWNER_ID = '".$DB->ForSql($this->serviceInstanceId)."' ".
			"		AND OWNED_UNTIL >= ".$DB->CurrentTimeFunction().") ".
			"	OR ".
			"	(OWNER_ID IS NULL) ".
			"	OR ".
			"	(OWNER_ID IS NOT NULL ".
			"		AND OWNED_UNTIL < ".$DB->CurrentTimeFunction().") ".
			") ";
	}
}

//Compatibility
class CBPWorkflowPersister extends CBPAllWorkflowPersister
{
}