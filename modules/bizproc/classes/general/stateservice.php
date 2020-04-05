<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/runtimeservice.php");

use Bitrix\Bizproc\WorkflowStateTable;
use Bitrix\Main;

class CBPAllStateService
	extends CBPRuntimeService
{
	const COUNTERS_CACHE_TAG_PREFIX = 'b_bp_wfi_cnt_';

	public function SetStateTitle($workflowId, $stateTitle)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$DB->Query(
			"UPDATE b_bp_workflow_state SET ".
			"	STATE_TITLE = ".(strlen($stateTitle) > 0 ? "'".$DB->ForSql($stateTitle)."'" : "NULL").", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);
	}

	public function SetStatePermissions($workflowId, $arStatePermissions = array(), $bRewrite = true)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		// @TODO: add new logic to CBPSetPermissionsMode::Rewrite
		if (!is_array($bRewrite) && $bRewrite == true
			|| is_array($bRewrite) && isset($bRewrite['setMode']) && $bRewrite['setMode'] == CBPSetPermissionsMode::Clear)
		{
			$DB->Query(
				"DELETE FROM b_bp_workflow_permissions ".
				"WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' "
			);
		}
		$arState = self::GetWorkflowState($workflowId);
		$documentService = $this->runtime->GetService("DocumentService");
		$documentService->SetPermissions($arState["DOCUMENT_ID"], $workflowId, $arStatePermissions, $bRewrite);
		$documentType = $documentService->GetDocumentType($arState["DOCUMENT_ID"]);
		if ($documentType)
			$arStatePermissions = $documentService->toInternalOperations($documentType, $arStatePermissions);

		foreach ($arStatePermissions as $permission => $arObjects)
		{
			foreach ($arObjects as $object)
			{
				$DB->Query(
					"INSERT INTO b_bp_workflow_permissions (WORKFLOW_ID, OBJECT_ID, PERMISSION) ".
					"VALUES ('".$DB->ForSql($workflowId)."', '".$DB->ForSql($object)."', '".$DB->ForSql($permission)."')"
				);
			}
		}
	}

	public function GetStateTitle($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$db = $DB->Query("SELECT STATE_TITLE FROM b_bp_workflow_state WHERE ID = '".$DB->ForSql($workflowId)."' ");
		if ($ar = $db->Fetch())
			return $ar["STATE_TITLE"];

		return "";
	}

	public static function GetStateDocumentId($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$db = $DB->Query("SELECT MODULE_ID, ENTITY, DOCUMENT_ID FROM b_bp_workflow_state WHERE ID = '".$DB->ForSql($workflowId)."' ");
		if ($ar = $db->Fetch())
			return array($ar["MODULE_ID"], $ar["ENTITY"], $ar["DOCUMENT_ID"]);

		return false;
	}

	public function	AddWorkflow($workflowId, $workflowTemplateId, $documentId, $starterUserId = 0)
	{
		global $DB;

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$workflowTemplateId = intval($workflowTemplateId);
		if ($workflowTemplateId <= 0)
			throw new Exception("workflowTemplateId");

		$starterUserId = intval($starterUserId);
		if ($starterUserId <= 0)
			$starterUserId = "NULL";

		$dbResult = $DB->Query(
			"SELECT ID ".
			"FROM b_bp_workflow_state ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);

		if ($arResult = $dbResult->Fetch())
			throw new Exception("WorkflowAlreadyExists");

		$DB->Query(
			"INSERT INTO b_bp_workflow_state (ID, MODULE_ID, ENTITY, DOCUMENT_ID, DOCUMENT_ID_INT, WORKFLOW_TEMPLATE_ID, MODIFIED, STARTED, STARTED_BY) ".
			"VALUES ('".$DB->ForSql($workflowId)."', ".((strlen($arDocumentId[0]) > 0) ? "'".$DB->ForSql($arDocumentId[0])."'" : "NULL").", '".$DB->ForSql($arDocumentId[1])."', '".$DB->ForSql($arDocumentId[2])."', ".intval($arDocumentId[2]).", ".intval($workflowTemplateId).", ".$DB->CurrentTimeFunction().", ".$DB->CurrentTimeFunction().", ".$starterUserId.")"
		);

		if (is_int($starterUserId))
			self::cleanRunningCountersCache($starterUserId);
	}

	public static function DeleteWorkflow($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$info = self::getWorkflowStateInfo($workflowId);
		if (!empty($info['STARTED_BY']))
			self::cleanRunningCountersCache($info['STARTED_BY']);

		$DB->Query(
			"DELETE FROM b_bp_workflow_permissions ".
			"WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' "
		);

		$DB->Query(
			"DELETE FROM b_bp_workflow_state ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);
	}

	public function DeleteAllDocumentWorkflows($documentId)
	{
		self::DeleteByDocument($documentId);
	}

	public function onStatusChange($workflowId, $status)
	{
		if ($status == CBPWorkflowStatus::Completed || $status == CBPWorkflowStatus::Terminated)
		{
			$info = $this->getWorkflowStateInfo($workflowId);
			$userId = isset($info['STARTED_BY']) ? (int)$info['STARTED_BY'] : 0;
			if ($userId > 0)
			{
				self::cleanRunningCountersCache($userId);
			}

			foreach (GetModuleEvents('bizproc', 'OnWorkflowComplete', true) as $event)
				ExecuteModuleEventEx($event, array($workflowId, $status));
		}
	}

	private static function __ExtractState(&$arStates, $arResult)
	{
		if (!array_key_exists($arResult["ID"], $arStates))
		{
			$arStates[$arResult["ID"]] = array(
				"ID" => $arResult["ID"],
				"TEMPLATE_ID" => $arResult["WORKFLOW_TEMPLATE_ID"],
				"TEMPLATE_NAME" => $arResult["NAME"],
				"TEMPLATE_DESCRIPTION" => $arResult["DESCRIPTION"],
				"STATE_MODIFIED" => $arResult["MODIFIED"],
				"STATE_NAME" => $arResult["STATE"],
				"STATE_TITLE" => $arResult["STATE_TITLE"],
				"STATE_PARAMETERS" => (strlen($arResult["STATE_PARAMETERS"]) > 0 ? unserialize($arResult["STATE_PARAMETERS"]) : array()),
				"WORKFLOW_STATUS" => $arResult["STATUS"],
				"STATE_PERMISSIONS" => array(),
				"DOCUMENT_ID" => array($arResult["MODULE_ID"], $arResult["ENTITY"], $arResult["DOCUMENT_ID"]),
				"STARTED" => $arResult["STARTED"],
				"STARTED_BY" => $arResult["STARTED_BY"],
				"STARTED_FORMATTED" => $arResult["STARTED_FORMATTED"],
			);
		}

		if (strlen($arResult["PERMISSION"]) > 0 && strlen($arResult["OBJECT_ID"]) > 0)
		{
			$arResult["PERMISSION"] = strtolower($arResult["PERMISSION"]);

			if (!array_key_exists($arResult["PERMISSION"], $arStates[$arResult["ID"]]["STATE_PERMISSIONS"]))
				$arStates[$arResult["ID"]]["STATE_PERMISSIONS"][$arResult["PERMISSION"]] = array();

			$arStates[$arResult["ID"]]["STATE_PERMISSIONS"][$arResult["PERMISSION"]][] = $arResult["OBJECT_ID"];
		}
	}

	public static function CountDocumentWorkflows($documentId)
	{
		global $DB;

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$dbResult = $DB->Query(
			"SELECT COUNT(WS.ID) CNT ".
			"FROM b_bp_workflow_state WS ".
			"	INNER JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID) ".
			"	INNER JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID) ".
			"WHERE WS.DOCUMENT_ID = '".$DB->ForSql($arDocumentId[2])."' ".
			"	AND WS.ENTITY = '".$DB->ForSql($arDocumentId[1])."' ".
			"	AND WS.MODULE_ID ".((strlen($arDocumentId[0]) > 0) ? "= '".$DB->ForSql($arDocumentId[0])."'" : "IS NULL").
			"	AND WT.AUTO_EXECUTE <> ".(int)CBPDocumentEventType::Automation
		);

		if ($arResult = $dbResult->Fetch())
			return intval($arResult["CNT"]);

		return 0;
	}

	public static function GetDocumentStates($documentId, $workflowId = "")
	{
		global $DB;

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);

		$ids = (array) $arDocumentId[2];
		$idsCondition = array();
		foreach ($ids as $id)
		{
			$idsCondition[] = 'WS.DOCUMENT_ID = \''.$DB->ForSql($id).'\'';
		}

		$sqlAdditionalFilter = "";
		$workflowId = trim($workflowId);
		if (strlen($workflowId) > 0)
			$sqlAdditionalFilter = " AND WS.ID = '".$DB->ForSql($workflowId)."' ";

		$dbResult = $DB->Query(
			"SELECT WS.ID, WS.WORKFLOW_TEMPLATE_ID, WS.STATE, WS.STATE_TITLE, WS.STATE_PARAMETERS, ".
			"	".$DB->DateToCharFunction("WS.MODIFIED", "FULL")." as MODIFIED, ".
			"	WS.MODULE_ID, WS.ENTITY, WS.DOCUMENT_ID, ".
			"	WT.NAME, WT.DESCRIPTION, WP.OBJECT_ID, WP.PERMISSION, WI.STATUS, ".
			"	WS.STARTED, WS.STARTED_BY ".
			"FROM b_bp_workflow_state WS ".
			"	LEFT JOIN b_bp_workflow_permissions WP ON (WS.ID = WP.WORKFLOW_ID) ".
			"	LEFT JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID) ".
			"	LEFT JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID) ".
			"WHERE (".implode(' OR ', $idsCondition).") ".
			"	AND WS.ENTITY = '".$DB->ForSql($arDocumentId[1])."' ".
			"	AND WS.MODULE_ID ".((strlen($arDocumentId[0]) > 0) ? "= '".$DB->ForSql($arDocumentId[0])."'" : "IS NULL")." ".
			$sqlAdditionalFilter
		);

		$arStates = array();
		while ($arResult = $dbResult->Fetch())
			self::__ExtractState($arStates, $arResult);

		return $arStates;
	}

	public static function getIdsByDocument(array $documentId)
	{
		$documentId = \CBPHelper::ParseDocumentId($documentId);
		$rows = WorkflowStateTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $documentId[0],
				'=ENTITY' => $documentId[1],
				'=DOCUMENT_ID' => $documentId[2]
			]
		])->fetchAll();

		return array_column($rows, 'ID');
	}

	public static function GetWorkflowState($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT WS.ID, WS.WORKFLOW_TEMPLATE_ID, WS.STATE, WS.STATE_TITLE, WS.STATE_PARAMETERS, ".
			"	".$DB->DateToCharFunction("WS.MODIFIED", "FULL")." as MODIFIED, ".
			"	WS.MODULE_ID, WS.ENTITY, WS.DOCUMENT_ID, ".
			"	WT.NAME, WT.DESCRIPTION, WP.OBJECT_ID, WP.PERMISSION, WI.STATUS, ".
			"	WS.STARTED, WS.STARTED_BY, ".$DB->DateToCharFunction("WS.STARTED", "FULL")." as STARTED_FORMATTED ".
			"FROM b_bp_workflow_state WS ".
			"	LEFT JOIN b_bp_workflow_permissions WP ON (WS.ID = WP.WORKFLOW_ID) ".
			"	LEFT JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID) ".
			"	LEFT JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID) ".
			"WHERE WS.ID = '".$DB->ForSql($workflowId)."' "
		);

		$arStates = array();
		while ($arResult = $dbResult->Fetch())
			self::__ExtractState($arStates, $arResult);

		$keys = array_keys($arStates);
		if (count($keys) > 0)
			$arStates = $arStates[$keys[0]];

		return $arStates;
	}

	public static function getWorkflowStateInfo($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT 
				WS.ID, WS.STATE_TITLE, WS.MODULE_ID, WS.ENTITY, WS.DOCUMENT_ID, WI.STATUS, WS.STARTED_BY,
				WS.WORKFLOW_TEMPLATE_ID, WT.NAME WORKFLOW_TEMPLATE_NAME ".
			"FROM b_bp_workflow_state WS ".
			"LEFT JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID) ".
			"LEFT JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID) ".
			"WHERE WS.ID = '".$DB->ForSql($workflowId)."' "
		);

		$state = false;
		$result = $dbResult->Fetch();
		if ($result)
		{
			$state = array(
				'ID' => $result["ID"],
				'WORKFLOW_TEMPLATE_ID' => $result['WORKFLOW_TEMPLATE_ID'],
				'WORKFLOW_TEMPLATE_NAME' => $result['WORKFLOW_TEMPLATE_NAME'],
				"STATE_TITLE" => $result["STATE_TITLE"],
				"WORKFLOW_STATUS" => $result["STATUS"],
				"DOCUMENT_ID" => array($result["MODULE_ID"], $result["ENTITY"], $result["DOCUMENT_ID"]),
				"STARTED_BY" => $result["STARTED_BY"],
			);
		}

		return $state;
	}

	public static function getWorkflowIntegerId($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT ID FROM b_bp_workflow_state_identify WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' "
		);

		$result = $dbResult->fetch();
		if (!$result)
		{
			$strSql =
				"INSERT INTO b_bp_workflow_state_identify (WORKFLOW_ID) ".
				"VALUES ('".$DB->ForSql($workflowId)."')";
			$DB->Query($strSql);

			$result = array('ID' => $DB->LastID());
		}
		return (int)$result['ID'];
	}

	public static function getWorkflowByIntegerId($integerId)
	{
		global $DB;

		$integerId = intval($integerId);
		if ($integerId <= 0)
			throw new Exception("integerId");

		$dbResult = $DB->Query(
			"SELECT WORKFLOW_ID FROM b_bp_workflow_state_identify WHERE ID = ".$integerId." "
		);

		$result = $dbResult->fetch();
		if ($result)
		{
			return $result['WORKFLOW_ID'];
		}
		return false;
	}

	public static function DeleteByDocument($documentId)
	{
		global $DB;

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);
		$users = array();

		$dbRes = $DB->Query(
			"SELECT ID, STARTED_BY ".
			"FROM b_bp_workflow_state ".
			"WHERE DOCUMENT_ID = '".$DB->ForSql($arDocumentId[2])."' ".
			"	AND ENTITY = '".$DB->ForSql($arDocumentId[1])."' ".
			"	AND MODULE_ID ".((strlen($arDocumentId[0]) > 0) ? "= '".$DB->ForSql($arDocumentId[0])."'" : "IS NULL")." "
		);
		while ($arRes = $dbRes->Fetch())
		{
			$DB->Query(
				"DELETE FROM b_bp_workflow_permissions ".
				"WHERE WORKFLOW_ID = '".$DB->ForSql($arRes["ID"])."' "
			);
			if (!empty($arRes['STARTED_BY']))
				$users[] = $arRes['STARTED_BY'];
		}

		$DB->Query(
			"DELETE FROM b_bp_workflow_state ".
			"WHERE DOCUMENT_ID = '".$DB->ForSql($arDocumentId[2])."' ".
			"	AND ENTITY = '".$DB->ForSql($arDocumentId[1])."' ".
			"	AND MODULE_ID ".((strlen($arDocumentId[0]) > 0) ? "= '".$DB->ForSql($arDocumentId[0])."'" : "IS NULL")." "
		);

		self::cleanRunningCountersCache($users);
	}

	public static function deleteCompletedStates(array $documentId)
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		list($moduleId, $entity, $docId) = \CBPHelper::ParseDocumentId($documentId);

		$connection->queryExecute(sprintf('DELETE P FROM b_bp_workflow_permissions P '
			.'INNER JOIN b_bp_workflow_state S ON (P.WORKFLOW_ID = S.ID) '
			.'LEFT JOIN b_bp_workflow_instance I ON (S.ID = I.ID)'
			.'WHERE I.ID IS NULL AND S.MODULE_ID = \'%s\' AND S.ENTITY = \'%s\' AND S.DOCUMENT_ID = \'%s\'',
			$helper->forSql($moduleId),
			$helper->forSql($entity),
			$helper->forSql($docId)
		));

		$connection->queryExecute(sprintf('DELETE S FROM b_bp_workflow_state S LEFT JOIN b_bp_workflow_instance I '
			.'ON (S.ID = I.ID) '
			.'WHERE I.ID IS NULL AND S.MODULE_ID = \'%s\' AND S.ENTITY = \'%s\' AND S.DOCUMENT_ID = \'%s\'',
			$helper->forSql($moduleId),
			$helper->forSql($entity),
			$helper->forSql($docId)
		));
	}

	public static function MergeStates($firstDocumentId, $secondDocumentId)
	{
		global $DB;

		$arFirstDocumentId = CBPHelper::ParseDocumentId($firstDocumentId);
		$arSecondDocumentId = CBPHelper::ParseDocumentId($secondDocumentId);

		$DB->Query(
			"UPDATE b_bp_workflow_state SET ".
			"	DOCUMENT_ID = '".$DB->ForSql($arFirstDocumentId[2])."', ".
			"	DOCUMENT_ID_INT = ".intval($arFirstDocumentId[2]).", ".
			"	ENTITY = '".$DB->ForSql($arFirstDocumentId[1])."', ".
			"	MODULE_ID = '".$DB->ForSql($arFirstDocumentId[0])."' ".
			"WHERE DOCUMENT_ID = '".$DB->ForSql($arSecondDocumentId[2])."' ".
			"	AND ENTITY = '".$DB->ForSql($arSecondDocumentId[1])."' ".
			"	AND MODULE_ID = '".$DB->ForSql($arSecondDocumentId[0])."' "
		);
	}

	public static function MigrateDocumentType($oldType, $newType, $workflowTemplateIds)
	{
		global $DB;

		$arOldType = CBPHelper::ParseDocumentId($oldType);
		$arNewType = CBPHelper::ParseDocumentId($newType);

		$DB->Query(
			"UPDATE b_bp_workflow_state SET ".
			"	ENTITY = '".$DB->ForSql($arNewType[1])."', ".
			"	MODULE_ID = '".$DB->ForSql($arNewType[0])."' ".
			"WHERE ENTITY = '".$DB->ForSql($arOldType[1])."' ".
			"	AND MODULE_ID = '".$DB->ForSql($arOldType[0])."' ".
			"	AND WORKFLOW_TEMPLATE_ID IN (".implode(",", $workflowTemplateIds).") "
		);
	}

	public function SetState($workflowId, $arState, $arStatePermissions = array())
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$state = trim($arState["STATE"]);
		$stateTitle = trim($arState["TITLE"]);
		$stateParameters = "";
		if (count($arState["PARAMETERS"]) > 0)
			$stateParameters = serialize($arState["PARAMETERS"]);

		$DB->Query(
			"UPDATE b_bp_workflow_state SET ".
			"	STATE = ".(strlen($state) > 0 ? "'".$DB->ForSql($state)."'" : "NULL").", ".
			"	STATE_TITLE = ".(strlen($stateTitle) > 0 ? "'".$DB->ForSql($stateTitle)."'" : "NULL").", ".
			"	STATE_PARAMETERS = ".(strlen($stateParameters) > 0 ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);

		if ($arStatePermissions !== false)
		{
			$arState = self::GetWorkflowState($workflowId);
			$runtime = $this->runtime;
			if (!isset($runtime) || !is_object($runtime))
				$runtime = CBPRuntime::GetRuntime();
			$documentService = $runtime->GetService("DocumentService");
			$documentService->SetPermissions($arState["DOCUMENT_ID"], $workflowId, $arStatePermissions, true);
			$documentType = $documentService->GetDocumentType($arState["DOCUMENT_ID"]);
			if ($documentType)
				$arStatePermissions = $documentService->toInternalOperations($documentType, $arStatePermissions);

			$DB->Query(
				"DELETE FROM b_bp_workflow_permissions ".
				"WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' "
			);

			foreach ($arStatePermissions as $permission => $arObjects)
			{
				foreach ($arObjects as $object)
				{
					$DB->Query(
						"INSERT INTO b_bp_workflow_permissions (WORKFLOW_ID, OBJECT_ID, PERMISSION) ".
						"VALUES ('".$DB->ForSql($workflowId)."', '".$DB->ForSql($object)."', '".$DB->ForSql($permission)."')"
					);
				}
			}
		}
	}

	public function SetStateParameters($workflowId, $arStateParameters = array())
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$stateParameters = "";
		if (count($arStateParameters) > 0)
			$stateParameters = serialize($arStateParameters);

		$DB->Query(
			"UPDATE b_bp_workflow_state SET ".
			"	STATE_PARAMETERS = ".(strlen($stateParameters) > 0 ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);
	}

	public function AddStateParameter($workflowId, $arStateParameter)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT STATE_PARAMETERS ".
			"FROM b_bp_workflow_state ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);

		if ($arResult = $dbResult->Fetch())
		{
			$stateParameters = array();
			if (strlen($arResult["STATE_PARAMETERS"]) > 0)
				$stateParameters = unserialize($arResult["STATE_PARAMETERS"]);

			$stateParameters[] = $arStateParameter;

			$stateParameters = serialize($stateParameters);

			$DB->Query(
				"UPDATE b_bp_workflow_state SET ".
				"	STATE_PARAMETERS = ".(strlen($stateParameters) > 0 ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
				"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
				"WHERE ID = '".$DB->ForSql($workflowId)."' "
			);
		}
	}

	public function DeleteStateParameter($workflowId, $name)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if (strlen($workflowId) <= 0)
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT STATE_PARAMETERS ".
			"FROM b_bp_workflow_state ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);

		if ($arResult = $dbResult->Fetch())
		{
			$stateParameters = array();
			if (strlen($arResult["STATE_PARAMETERS"]) > 0)
				$stateParameters = unserialize($arResult["STATE_PARAMETERS"]);

			$ar = array();
			foreach ($stateParameters as $v)
			{
				if ($v["NAME"] != $name)
					$ar[] = $v;
			}

			$stateParameters = "";
			if (count($ar) > 0)
				$stateParameters = serialize($ar);

			$DB->Query(
				"UPDATE b_bp_workflow_state SET ".
				"	STATE_PARAMETERS = ".(strlen($stateParameters) > 0 ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
				"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
				"WHERE ID = '".$DB->ForSql($workflowId)."' "
			);
		}
	}

	public static function __InsertStateHack($id, $moduleId, $entity, $documentId, $templateId, $state, $stateTitle, $stateParameters, $arStatePermissions)
	{
		global $DB;

		$DB->Query(
			"INSERT INTO b_bp_workflow_state (ID, MODULE_ID, ENTITY, DOCUMENT_ID, DOCUMENT_ID_INT, WORKFLOW_TEMPLATE_ID, MODIFIED, STATE, STATE_TITLE, STATE_PARAMETERS) ".
			"VALUES ('".$DB->ForSql($id)."', '".$DB->ForSql($moduleId)."', '".$DB->ForSql($entity)."', '".$DB->ForSql($documentId)."', ".intval($documentId).", ".intval($templateId).", ".$DB->CurrentTimeFunction().", '".$DB->ForSql($state)."', '".$DB->ForSql($stateTitle)."', ".(strlen($stateParameters) > 0 ? "'".$DB->ForSql($stateParameters)."'" : "NULL").")"
		);

		foreach ($arStatePermissions as $permission => $arObjects)
		{
			foreach ($arObjects as $object)
			{
				$DB->Query(
					"INSERT INTO b_bp_workflow_permissions (WORKFLOW_ID, OBJECT_ID, PERMISSION) ".
					"VALUES ('".$DB->ForSql($id)."', '".$DB->ForSql($object)."', '".$DB->ForSql($permission)."')"
				);
			}
		}
	}

	public static function getRunningCounters($userId)
	{
		global $DB;

		$counters = array('*' => 0);
		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
		$cacheTag = self::COUNTERS_CACHE_TAG_PREFIX.$userId;
		if ($cache->read(3600*24*7, $cacheTag))
		{
			$counters = (array) $cache->get($cacheTag);
		}
		else
		{
			$query =
				"SELECT WS.MODULE_ID AS MODULE_ID, WS.ENTITY AS ENTITY, COUNT('x') AS CNT ".
				'FROM b_bp_workflow_state WS '.
				'	INNER JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID) '.
				'WHERE WS.STARTED_BY = '.(int)$userId.' '.
				'GROUP BY MODULE_ID, ENTITY';

			$iterator = $DB->Query($query, true);
			if ($iterator)
			{
				while ($row = $iterator->fetch())
				{
					$cnt = (int)$row['CNT'];
					$counters[$row['MODULE_ID']][$row['ENTITY']] = $cnt;
					if (!isset($counters[$row['MODULE_ID']]['*']))
						$counters[$row['MODULE_ID']]['*'] = 0;
					$counters[$row['MODULE_ID']]['*'] += $cnt;
					$counters['*'] += $cnt;
				}
				$cache->set($cacheTag, $counters);
			}
		}
		return $counters;
	}

	protected static function cleanRunningCountersCache($users)
	{
		$users = (array) $users;
		$users = array_unique($users);
		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
		foreach ($users as $userId)
		{
			$cache->clean(self::COUNTERS_CACHE_TAG_PREFIX.$userId);
		}
	}
}

//Compatibility
class CBPStateService extends CBPAllStateService
{
}