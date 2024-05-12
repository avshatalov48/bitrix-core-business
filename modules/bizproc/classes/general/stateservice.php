<?php

use Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Main;

class CBPStateService extends CBPRuntimeService
{
	const COUNTERS_CACHE_TAG_PREFIX = 'b_bp_wfi_cnt_';

	public function setStateTitle($workflowId, $stateTitle)
	{
		$workflowId = trim($workflowId);
		if ($workflowId === '')
		{
			throw new Exception("workflowId");
		}

		WorkflowStateTable::update($workflowId, ['STATE_TITLE' => $stateTitle]);
	}

	public function setStatePermissions($workflowId, $arStatePermissions = array(), $bRewrite = true)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		if ($bRewrite === true || ($bRewrite['setMode'] ?? null) === CBPSetPermissionsMode::Clear)
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

	public function getStateTitle($workflowId)
	{
		$workflowId = trim($workflowId);
		if ($workflowId === '')
		{
			throw new Exception("workflowId");
		}

		$result = WorkflowStateTable::query()
			->setSelect(['STATE_TITLE'])
			->where('ID', $workflowId)
			->fetch();

		return $result['STATE_TITLE'] ?? '';
	}

	public static function getStateDocumentId($workflowId)
	{
		$workflowId = trim($workflowId);
		if ($workflowId === '')
		{
			throw new Exception("workflowId");
		}

		$result = WorkflowStateTable::query()
			->setSelect(['MODULE_ID', 'ENTITY', 'DOCUMENT_ID'])
			->where('ID', $workflowId)
			->fetch();

		if ($result)
		{
			return [$result['MODULE_ID'], $result['ENTITY'], $result['DOCUMENT_ID']];
		}

		return false;
	}

	public function	AddWorkflow($workflowId, $workflowTemplateId, $documentId, $starterUserId = 0)
	{
		$docId = CBPHelper::ParseDocumentId($documentId);

		$workflowId = trim($workflowId);
		if ($workflowId === '')
		{
			throw new Exception("workflowId");
		}

		$workflowTemplateId = (int)$workflowTemplateId;
		if ($workflowTemplateId <= 0)
		{
			throw new Exception("workflowTemplateId");
		}

		$starterUserId = (int)$starterUserId;

		if (WorkflowStateTable::exists($workflowId))
		{
			throw new Exception("WorkflowAlreadyExists");
		}

		$addResult = WorkflowStateTable::add([
			'ID' => $workflowId,
			'MODULE_ID' => $docId[0] ?: null,
			'ENTITY' => $docId[1],
			'DOCUMENT_ID' => $docId[2],
			'DOCUMENT_ID_INT' => (int)$docId[2],
			'WORKFLOW_TEMPLATE_ID' => $workflowTemplateId,
			'STARTED_BY' => $starterUserId ?: null,
		]);

		if ($starterUserId > 0 && $addResult->isSuccess())
		{
			self::cleanRunningCountersCache($starterUserId);
		}
	}

	public static function deleteWorkflow($workflowId)
	{
		$connection = Main\Application::getConnection();

		$workflowId = trim($workflowId);
		if ($workflowId === '')
		{
			throw new Exception("workflowId");
		}

		$info = self::getWorkflowStateInfo($workflowId);

		if (!empty($info['STARTED_BY']))
		{
			self::cleanRunningCountersCache($info['STARTED_BY']);
		}

		$workflowIdSql = $connection->getSqlHelper()->forSql($workflowId);
		$connection->query(
			"DELETE FROM b_bp_workflow_permissions WHERE WORKFLOW_ID = '{$workflowIdSql}'",
		);

		WorkflowStateTable::delete($workflowId);
	}

	public function deleteAllDocumentWorkflows($documentId)
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
			{
				ExecuteModuleEventEx($event, array($workflowId, $status));
			}

			// Clean workflow subscriptions
			\Bitrix\Bizproc\SchedulerEventTable::deleteByWorkflow($workflowId);

			if ($info)
			{
				$this->fillWorkflowDurationStat($info, $status);
			}
		}
	}

	private static function extractState(&$arStates, $arResult): void
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
				"STATE_PARAMETERS" => ($arResult["STATE_PARAMETERS"] <> '' ? unserialize($arResult["STATE_PARAMETERS"], ['allowed_classes' => false]) : array()),
				"WORKFLOW_STATUS" => $arResult["STATUS"],
				"STATE_PERMISSIONS" => array(),
				"DOCUMENT_ID" => array($arResult["MODULE_ID"], $arResult["ENTITY"], $arResult["DOCUMENT_ID"]),
				"STARTED" => $arResult["STARTED"],
				"STARTED_BY" => $arResult["STARTED_BY"],
				"STARTED_FORMATTED" => $arResult["STARTED_FORMATTED"],
			);
		}

		if ($arResult["PERMISSION"] <> '' && $arResult["OBJECT_ID"] <> '')
		{
			$arResult["PERMISSION"] = mb_strtolower($arResult["PERMISSION"]);

			if (!array_key_exists($arResult["PERMISSION"], $arStates[$arResult["ID"]]["STATE_PERMISSIONS"]))
				$arStates[$arResult["ID"]]["STATE_PERMISSIONS"][$arResult["PERMISSION"]] = array();

			$arStates[$arResult["ID"]]["STATE_PERMISSIONS"][$arResult["PERMISSION"]][] = $arResult["OBJECT_ID"];
		}
	}

	public static function countDocumentWorkflows($documentId)
	{
		$documentId = \CBPHelper::ParseDocumentId($documentId);

		return WorkflowInstanceTable::getCount([
			'=MODULE_ID' => $documentId[0],
			'=ENTITY' => $documentId[1],
			'=DOCUMENT_ID' => $documentId[2],
			'!=STARTED_EVENT_TYPE' => CBPDocumentEventType::Automation,
		]);
	}

	public static function getDocumentStates($documentId, $workflowId = "")
	{
		global $DB;

		[$moduleId, $entity, $ids] = $documentId;

		$idsCondition = [];
		foreach ((array)$ids as $id)
		{
			$idsCondition[] = 'WS.DOCUMENT_ID = \''.$DB->ForSql($id).'\'';
		}

		if (empty($idsCondition))
		{
			return [];
		}

		$sqlAdditionalFilter = "";
		if (is_array($workflowId) && count($workflowId) > 0)
		{
			$workflowId = array_map(function ($id) use ($DB)
			{
				return '\''.$DB->ForSql((string)$id).'\'';
			}, $workflowId);
			$sqlAdditionalFilter = " AND WS.ID IN (".implode(',', $workflowId).")";
		}
		elseif (is_string($workflowId) && $workflowId)
		{
			$sqlAdditionalFilter = " AND WS.ID = '".$DB->ForSql(trim($workflowId))."' ";
		}

		$dbResult = $DB->Query(
			"SELECT WS.ID, WS.WORKFLOW_TEMPLATE_ID, WS.STATE, WS.STATE_TITLE, WS.STATE_PARAMETERS, ".
			"	".$DB->DateToCharFunction("WS.MODIFIED", "FULL")." as MODIFIED, ".
			"	WS.MODULE_ID, WS.ENTITY, WS.DOCUMENT_ID, ".
			"	WT.NAME, WT.DESCRIPTION, WP.OBJECT_ID, WP.PERMISSION, WI.STATUS, ".
			"	WS.STARTED, ". $DB->DateToCharFunction("WS.STARTED", "FULL")
			. " as STARTED_FORMATTED, WS.STARTED_BY ".
			"FROM b_bp_workflow_state WS ".
			"	LEFT JOIN b_bp_workflow_permissions WP ON (WS.ID = WP.WORKFLOW_ID) ".
			"	LEFT JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID) ".
			"	LEFT JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID) ".
			"WHERE (".implode(' OR ', $idsCondition).") ".
			"	AND WS.ENTITY = '" . $DB->ForSql($entity) . "' " .
			"	AND WS.MODULE_ID " . ($moduleId ? "= '" . $DB->ForSql($moduleId) . "'" : "IS NULL") . " ".
			$sqlAdditionalFilter
		);

		$arStates = array();
		while ($arResult = $dbResult->Fetch())
		{
			self::extractState($arStates, $arResult);
		}

		return $arStates;
	}

	public static function getIdsByDocument(array $documentId, int $limit = null)
	{
		return WorkflowStateTable::getIdsByDocument($documentId, $limit);
	}

	public static function getWorkflowState($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
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
		{
			self::extractState($arStates, $arResult);
		}

		$keys = array_keys($arStates);
		if (count($keys) > 0)
			$arStates = $arStates[$keys[0]];

		return $arStates;
	}

	public static function getWorkflowStateInfo($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId === '')
		{
			throw new Exception('workflowId');
		}

		$dbResult = $DB->Query(
			"SELECT 
				WS.ID, WS.STATE_TITLE, WS.MODULE_ID, WS.ENTITY, WS.DOCUMENT_ID, WI.STATUS, WS.STARTED_BY,
				WS.WORKFLOW_TEMPLATE_ID, WT.NAME WORKFLOW_TEMPLATE_NAME, WS.STARTED ".
			"FROM b_bp_workflow_state WS ".
			"LEFT JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID) ".
			"LEFT JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID) ".
			"WHERE WS.ID = '".$DB->ForSql($workflowId)."' "
		);

		$state = false;
		$result = $dbResult->Fetch();
		if ($result)
		{
			$state = [
				'ID' => $result["ID"],
				'WORKFLOW_TEMPLATE_ID' => $result['WORKFLOW_TEMPLATE_ID'],
				'WORKFLOW_TEMPLATE_NAME' => $result['WORKFLOW_TEMPLATE_NAME'],
				'STATE_TITLE' => $result['STATE_TITLE'],
				'WORKFLOW_STATUS' => $result['STATUS'],
				'DOCUMENT_ID' => [$result['MODULE_ID'], $result['ENTITY'], $result['DOCUMENT_ID']],
				'STARTED_BY' => $result['STARTED_BY'],
				'STARTED' => $result['STARTED'],
			];
		}

		return $state;
	}

	public static function exists(string $workflowId)
	{
		return WorkflowStateTable::exists($workflowId);
	}

	public static function getWorkflowIntegerId($workflowId)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
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
			$res = $DB->Query($strSql, true);
			//crutch for #0071996
			if ($res)
			{
				$result = array('ID' => $DB->LastID());
			}
			else
			{
				$dbResult = $DB->Query(
					"SELECT ID FROM b_bp_workflow_state_identify WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' "
				);

				$result = $dbResult->fetch();
			}
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

	public static function deleteByDocument($documentId)
	{
		global $DB;

		$arDocumentId = CBPHelper::ParseDocumentId($documentId);
		$users = array();

		$dbRes = $DB->Query(
			"SELECT ID, STARTED_BY ".
			"FROM b_bp_workflow_state ".
			"WHERE DOCUMENT_ID = '".$DB->ForSql($arDocumentId[2])."' ".
			"	AND ENTITY = '".$DB->ForSql($arDocumentId[1])."' ".
			"	AND MODULE_ID ".(($arDocumentId[0] <> '') ? "= '".$DB->ForSql($arDocumentId[0])."'" : "IS NULL")." "
		);
		while ($arRes = $dbRes->Fetch())
		{
			$DB->Query(
				"DELETE FROM b_bp_workflow_permissions ".
				"WHERE WORKFLOW_ID = '".$DB->ForSql($arRes["ID"])."' "
			);

			WorkflowStateTable::delete($arRes["ID"]);

			if (!empty($arRes['STARTED_BY']))
			{
				$users[] = $arRes['STARTED_BY'];
			}
		}

		self::cleanRunningCountersCache($users);
	}

	public static function mergeStates($firstDocumentId, $secondDocumentId)
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

	public static function migrateDocumentType($oldType, $newType, $workflowTemplateIds)
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

	public function setState($workflowId, $arState, $arStatePermissions = array())
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		$state = trim($arState["STATE"]);
		$stateTitle = trim($arState["TITLE"]);
		$stateParameters = "";
		if (count($arState["PARAMETERS"]) > 0)
			$stateParameters = serialize($arState["PARAMETERS"]);

		$DB->Query(
			"UPDATE b_bp_workflow_state SET ".
			"	STATE = ".($state <> '' ? "'".$DB->ForSql($state)."'" : "NULL").", ".
			"	STATE_TITLE = ".($stateTitle <> '' ? "'".$DB->ForSql($stateTitle)."'" : "NULL").", ".
			"	STATE_PARAMETERS = ".($stateParameters <> '' ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
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

			$permissionRewrite = true;
			if (isset($arStatePermissions['__mode']) || isset($arStatePermissions['__scope']))
			{
				$permissionRewrite = [
					'setMode' => $arStatePermissions['__mode'] ?? CBPSetPermissionsMode::Clear,
					'setScope' => $arStatePermissions['__scope'] ?? CBPSetPermissionsMode::ScopeWorkflow,
				];
				unset($arStatePermissions['__mode'], $arStatePermissions['__scope']);
			}

			$documentService->SetPermissions($arState["DOCUMENT_ID"], $workflowId, $arStatePermissions, $permissionRewrite);
			$documentType = $documentService->GetDocumentType($arState["DOCUMENT_ID"]);
			if ($documentType)
			{
				$arStatePermissions = $documentService->toInternalOperations($documentType, $arStatePermissions);
			}

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

	public function setStateParameters($workflowId, $arStateParameters = array())
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		$stateParameters = "";
		if (count($arStateParameters) > 0)
			$stateParameters = serialize($arStateParameters);

		$DB->Query(
			"UPDATE b_bp_workflow_state SET ".
			"	STATE_PARAMETERS = ".($stateParameters <> '' ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);
	}

	public function addStateParameter($workflowId, $arStateParameter)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT STATE_PARAMETERS ".
			"FROM b_bp_workflow_state ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);

		if ($arResult = $dbResult->Fetch())
		{
			$stateParameters = array();
			if ($arResult["STATE_PARAMETERS"] <> '')
				$stateParameters = unserialize($arResult["STATE_PARAMETERS"], ['allowed_classes' => false]);

			$stateParameters[] = $arStateParameter;

			$stateParameters = serialize($stateParameters);

			$DB->Query(
				"UPDATE b_bp_workflow_state SET ".
				"	STATE_PARAMETERS = ".($stateParameters <> '' ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
				"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
				"WHERE ID = '".$DB->ForSql($workflowId)."' "
			);
		}
	}

	public function deleteStateParameter($workflowId, $name)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		$dbResult = $DB->Query(
			"SELECT STATE_PARAMETERS ".
			"FROM b_bp_workflow_state ".
			"WHERE ID = '".$DB->ForSql($workflowId)."' "
		);

		if ($arResult = $dbResult->Fetch())
		{
			$stateParameters = array();
			if ($arResult["STATE_PARAMETERS"] <> '')
				$stateParameters = unserialize($arResult["STATE_PARAMETERS"], ['allowed_classes' => false]);

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
				"	STATE_PARAMETERS = ".($stateParameters <> '' ? "'".$DB->ForSql($stateParameters)."'" : "NULL").", ".
				"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
				"WHERE ID = '".$DB->ForSql($workflowId)."' "
			);
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
				"SELECT WI.MODULE_ID AS MODULE_ID, WI.ENTITY AS ENTITY, COUNT('x') AS CNT ".
				'FROM b_bp_workflow_instance WI '.
				'WHERE WI.STARTED_BY = '.(int)$userId.' '.
				'GROUP BY MODULE_ID, ENTITY';

			$iterator = $DB->Query($query);
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

	private function fillWorkflowDurationStat(array $workflowStateInfo, int $status)
	{
		$dateFormat = 'Y-m-d H:i:s';

		if ($status === CBPWorkflowStatus::Completed && Main\Type\DateTime::isCorrect($workflowStateInfo['STARTED'], $dateFormat))
		{
			$templateId = (int)$workflowStateInfo['WORKFLOW_TEMPLATE_ID'];
			$startedDate = new Main\Type\DateTime($workflowStateInfo['STARTED'], $dateFormat);

			WorkflowDurationStatTable::add([
				'WORKFLOW_ID' => (string)$workflowStateInfo['ID'],
				'TEMPLATE_ID' => $templateId,
				'DURATION' => (new Main\Type\DateTime())->getTimestamp() - $startedDate->getTimestamp(),
			]);
		}
	}
}
