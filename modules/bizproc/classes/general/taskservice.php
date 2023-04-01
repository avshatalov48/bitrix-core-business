<?php

use Bitrix\Main;
use Bitrix\Bizproc;

class CBPTaskService extends CBPRuntimeService
{
	const COUNTERS_CACHE_TAG_PREFIX = 'b_bp_tasks_cnt_';

	public function deleteTask($id)
	{
		self::Delete($id);
	}

	public function deleteAllWorkflowTasks($workflowId)
	{
		self::DeleteByWorkflow($workflowId);
	}

	public function markCompleted($taskId, $userId, $status = CBPTaskUserStatus::Ok)
	{
		global $DB;

		$taskId = (int)$taskId;
		if ($taskId <= 0)
			throw new Exception("id");
		$userId = (int)$userId;
		if ($userId <= 0)
			throw new Exception("userId");
		$status = (int)$status;

		$DB->Query("UPDATE b_bp_task_user SET STATUS = ".$status.", DATE_UPDATE = ".$DB->CurrentTimeFunction()." WHERE TASK_ID = ".$taskId." AND USER_ID = ".$userId, true);

		CUserCounter::Decrement($userId, 'bp_tasks', '**');

		self::onTaskChange(
			$taskId,
			[
				'USERS_STATUSES' => [$userId => $status],
				'COUNTERS_DECREMENTED' => [$userId]
			],
			CBPTaskChangedStatus::Update
		);
		foreach (GetModuleEvents("bizproc", "OnTaskMarkCompleted", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($taskId, $userId, $status));
		}
	}

	public static function getTaskUsers($taskId)
	{
		global $DB;

		$taskId = (array)$taskId;
		$taskId = array_map('intval', $taskId);
		$taskId = array_filter($taskId);
		if (sizeof($taskId) < 1)
			throw new Exception("taskId");

		$where = '';
		foreach ($taskId as $id)
		{
			if ($where)
				$where .= ' OR ';
			$where .= ' TASK_ID = '.$id;
		}

		$users = array();
		$iterator = $DB->Query('SELECT TU.*, U.PERSONAL_PHOTO, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.LOGIN, U.TITLE'
			.' FROM b_bp_task_user TU'
			.' INNER JOIN b_user U ON (U.ID = TU.USER_ID)'
			.' WHERE '.$where
			.' ORDER BY TU.DATE_UPDATE DESC'
		);
		while ($user = $iterator->fetch())
		{
			$users[$user['TASK_ID']][] = $user;
		}
		return $users;
	}

	public static function getTaskUserIds(int $taskId): array
	{
		$ids = [];
		$taskUsers = static::getTaskUsers($taskId);
		if (isset($taskUsers[$taskId]))
		{
			$ids = array_column($taskUsers[$taskId], 'USER_ID');
		}
		return array_map('intval', $ids);
	}

	/**
	 * @param string $workflowId - Internal workflow id.
	 * @param null|int $userStatus - Filter participants by status.
	 * @return array - User ids array (ex. array(1, 2, 3)).
	 * @throws Exception
	 */
	public static function getWorkflowParticipants($workflowId, $userStatus = null)
	{
		global $DB;

		if ($workflowId == '')
			throw new Exception('workflowId');

		$users = array();
		$iterator = $DB->Query('SELECT DISTINCT TU.USER_ID'
			.' FROM b_bp_task_user TU'
			.' INNER JOIN b_bp_task T ON (T.ID = TU.TASK_ID)'
			.' WHERE T.WORKFLOW_ID = \''.$DB->ForSql($workflowId).'\''
			.($userStatus !== null ? ' AND TU.STATUS = '.(int)$userStatus : '')
		);
		while ($user = $iterator->fetch())
		{
			$users[] = (int)$user['USER_ID'];
		}
		return $users;
	}

	public static function delegateTask($taskId, $fromUserId, $toUserId)
	{
		global $DB;
		$taskId = (int)$taskId;
		$fromUserId = (int)$fromUserId;
		$toUserId = (int)$toUserId;

		if (!$taskId || !$fromUserId || !$toUserId)
			return false;

		$originalUserId = 0;

		//check ORIGINAL_USER_ID
		$iterator = $DB->Query('SELECT ORIGINAL_USER_ID'
			.' FROM b_bp_task_user'
			.' WHERE TASK_ID = '.$taskId.' AND USER_ID = '.$fromUserId
		);
		$row = $iterator->fetch();
		if (!empty($row['ORIGINAL_USER_ID']))
			$originalUserId = $row['ORIGINAL_USER_ID'];

		// check USER_ID (USER_ID must be unique for task)
		$iterator = $DB->Query('SELECT USER_ID'
			.' FROM b_bp_task_user'
			.' WHERE TASK_ID = '.$taskId.' AND USER_ID = '.$toUserId
		);
		$row = $iterator->fetch();
		if (!empty($row['USER_ID']))
			return false;

		$DB->Query("UPDATE b_bp_task_user SET USER_ID = "
			.$toUserId
			.(!$originalUserId? ', ORIGINAL_USER_ID = '.$fromUserId : '')
			." WHERE TASK_ID = ".$taskId." AND USER_ID = ".$fromUserId, true);
		CUserCounter::Decrement($fromUserId, 'bp_tasks', '**');
		CUserCounter::Increment($toUserId, 'bp_tasks', '**');
		self::onTaskChange(
			$taskId,
			[
				'USERS' => [$toUserId],
				'USERS_ADDED' => [$toUserId],
				'USERS_REMOVED' => [$fromUserId],
				'COUNTERS_DECREMENTED' => [$fromUserId],
				'COUNTERS_INCREMENTED' => [$toUserId],
			],
			CBPTaskChangedStatus::Delegate
		);
		foreach (GetModuleEvents("bizproc", "OnTaskDelegate", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($taskId, $fromUserId, $toUserId));
		}
		return true;
	}

	public static function getOriginalTaskUserId($taskId, $realUserId)
	{
		global $DB;
		$taskId = (int)$taskId;
		$realUserId = (int)$realUserId;

		$iterator = $DB->Query('SELECT ORIGINAL_USER_ID'
			.' FROM b_bp_task_user'
			.' WHERE TASK_ID = '.$taskId.' AND USER_ID = '.$realUserId
		);
		if ($row = $iterator->fetch())
		{
			return $row['ORIGINAL_USER_ID'] > 0 ? $row['ORIGINAL_USER_ID'] : $realUserId;
		}
		return false;
	}

	public static function delete($id)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			throw new Exception("id");

		$removedUsers = $decremented = [];
		$dbRes = $DB->Query("SELECT USER_ID, STATUS FROM b_bp_task_user WHERE TASK_ID = ".intval($id)." ");
		while ($arRes = $dbRes->Fetch())
		{
			if ($arRes['STATUS'] == CBPTaskUserStatus::Waiting)
			{
				CUserCounter::Decrement($arRes["USER_ID"], 'bp_tasks', '**');
				$decremented[] = $arRes["USER_ID"];
			}
			$removedUsers[] = $arRes["USER_ID"];
		}
		$DB->Query("DELETE FROM b_bp_task_user WHERE TASK_ID = ".intval($id)." ", true);
		$DB->Query("DELETE FROM b_bp_task WHERE ID = ".intval($id)." ", true);

		self::onTaskChange(
			$id,
			[
				'USERS_REMOVED' => $removedUsers,
				'COUNTERS_DECREMENTED' => $decremented
			],
			CBPTaskChangedStatus::Delete
		);
		foreach (GetModuleEvents("bizproc", "OnTaskDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($id));
	}

	public static function deleteByWorkflow($workflowId, $taskStatus = null)
	{
		global $DB;

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			throw new Exception("workflowId");

		$dbRes = $DB->Query(
			"SELECT ID ".
			"FROM b_bp_task ".
			"WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' "
			.($taskStatus !== null? 'AND STATUS = '.(int)$taskStatus : '')
		);
		while ($arRes = $dbRes->Fetch())
		{
			$taskId = intval($arRes["ID"]);
			$removedUsers = $decremented = [];
			$dbResUser = $DB->Query("SELECT USER_ID, STATUS FROM b_bp_task_user WHERE TASK_ID = ".$taskId." ");
			while ($arResUser = $dbResUser->Fetch())
			{
				if ($arResUser['STATUS'] == CBPTaskUserStatus::Waiting)
				{
					CUserCounter::Decrement($arResUser["USER_ID"], 'bp_tasks', '**');
					$decremented[] = $arResUser["USER_ID"];
				}
				$removedUsers[] = $arResUser['USER_ID'];
			}
			$DB->Query("DELETE FROM b_bp_task_user WHERE TASK_ID = ".$taskId." ", true);

			self::onTaskChange(
				$taskId,
				[
					'USERS_REMOVED' => $removedUsers,
					'COUNTERS_DECREMENTED' => $decremented
				],
				CBPTaskChangedStatus::Delete
			);
			foreach (GetModuleEvents("bizproc", "OnTaskDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($taskId));
		}

		$DB->Query(
			"DELETE FROM b_bp_task ".
			"WHERE WORKFLOW_ID = '".$DB->ForSql($workflowId)."' "
			.($taskStatus !== null? 'AND STATUS = '.(int)$taskStatus : ''),
			true
		);
	}

	public static function getCounters($userId)
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
				'FROM b_bp_task T '.
				'	INNER JOIN b_bp_task_user TU ON (T.ID = TU.TASK_ID) '.
				'	INNER JOIN b_bp_workflow_instance WI ON (T.WORKFLOW_ID = WI.ID) '.
				'WHERE TU.STATUS = '.(int)CBPTaskUserStatus::Waiting.' '.
				'	AND TU.USER_ID = '.(int)$userId.' '.
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

	protected static function onTaskChange($taskId, $taskData, $status)
	{
		$workflowId = isset($taskData['WORKFLOW_ID']) ? $taskData['WORKFLOW_ID'] : null;
		if (!$workflowId)
		{
			$iterator = CBPTaskService::GetList(array('ID'=>'DESC'), array('ID' => $taskId), false, false, array('WORKFLOW_ID'));
			$row = $iterator->fetch();
			if (!$row)
				return false;
			$workflowId = $row['WORKFLOW_ID'];
			$taskData['WORKFLOW_ID'] = $workflowId;
		}

		//clean counters cache
		$users = array();
		if (!empty($taskData['USERS']))
			$users = $taskData['USERS'];
		if (!empty($taskData['USERS_REMOVED']))
			$users = array_merge($users, $taskData['USERS_REMOVED']);
		if (!empty($taskData['USERS_STATUSES']))
			$users = array_merge($users, array_keys($taskData['USERS_STATUSES']));
		self::cleanCountersCache($users);

		//ping document
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentId = CBPStateService::GetStateDocumentId($workflowId);
		if ($documentId)
		{
			$documentService = $runtime->GetService('DocumentService');
			try
			{
				$documentService->onTaskChange($documentId, $taskId, $taskData, $status);
			}
			catch (Exception $e)
			{

			}
		}
		return true;
	}

	protected static function cleanCountersCache($users)
	{
		$users = (array) $users;
		$users = array_unique($users);
		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
		foreach ($users as $userId)
		{
			$cache->clean(self::COUNTERS_CACHE_TAG_PREFIX.$userId);
		}
	}

	protected static function parseFields(&$arFields, $id = 0)
	{
		global $DB;

		$id = intval($id);
		$updateMode = ($id > 0 ? true : false);
		$addMode = !$updateMode;

		if ($addMode && !is_set($arFields, "USERS"))
			throw new Exception("USERS");

		if (is_set($arFields, "USERS"))
		{
			$arUsers = $arFields["USERS"];
			if (!is_array($arUsers))
				$arUsers = array($arUsers);

			$arFields["USERS"] = array();
			foreach ($arUsers as $userId)
			{
				$userId = intval($userId);
				if ($userId > 0 && !in_array($userId, $arFields["USERS"]))
					$arFields["USERS"][] = $userId;
			}

			if (count($arFields["USERS"]) <= 0)
				throw new Exception(GetMessage("BPTS_AI_AR_USERS"));
		}

		if (is_set($arFields, "WORKFLOW_ID") || $addMode)
		{
			$arFields["WORKFLOW_ID"] = trim($arFields["WORKFLOW_ID"]);
			if ($arFields["WORKFLOW_ID"] == '')
				throw new Exception("WORKFLOW_ID");
		}

		if (is_set($arFields, "ACTIVITY") || $addMode)
		{
			$arFields["ACTIVITY"] = trim($arFields["ACTIVITY"]);
			if ($arFields["ACTIVITY"] == '')
				throw new Exception("ACTIVITY");
		}

		if (is_set($arFields, "ACTIVITY_NAME") || $addMode)
		{
			$arFields["ACTIVITY_NAME"] = trim($arFields["ACTIVITY_NAME"]);
			if ($arFields["ACTIVITY_NAME"] == '')
				throw new Exception("ACTIVITY_NAME");
		}

		if (is_set($arFields, "NAME") || $addMode)
		{
			$arFields["NAME"] = is_scalar($arFields["NAME"]) ? trim($arFields["NAME"]) : '';
			if ($arFields["NAME"] == '')
				throw new Exception("NAME");

			$arFields["NAME"] = htmlspecialcharsback($arFields["NAME"]);
		}

		if (is_set($arFields, "DESCRIPTION"))
		{
			$arFields["DESCRIPTION"] = htmlspecialcharsback(CBPHelper::stringify($arFields["DESCRIPTION"]));
		}

		if (is_set($arFields, "PARAMETERS"))
		{
			if ($arFields["PARAMETERS"] == null)
			{
				$arFields["PARAMETERS"] = false;
			}
			else
			{
				$arParameters = $arFields["PARAMETERS"];
				if (!is_array($arParameters))
					$arParameters = array($arParameters);
				if (count($arParameters) > 0)
					$arFields["PARAMETERS"] = serialize($arParameters);
			}
		}

		if (is_set($arFields, "OVERDUE_DATE"))
		{
			if ($arFields["OVERDUE_DATE"] == null)
				$arFields["OVERDUE_DATE"] = false;
			elseif (!$DB->IsDate($arFields["OVERDUE_DATE"], false, LANG, "FULL"))
				throw new Exception("OVERDUE_DATE");
		}
	}

	public static function onAdminInformerInsertItems()
	{
		global $USER;

		if(!defined("BX_AUTH_FORM"))
		{
			$tasksCount = CUserCounter::GetValue($USER->GetID(), 'bp_tasks');

			if($tasksCount > 0)
			{
				$bpAIParams = array(
					"TITLE" => GetMessage("BPTS_AI_BIZ_PROC"),
					"HTML" => '<span class="adm-informer-strong-text">'.GetMessage("BPTS_AI_EX_TASKS").'</span><br>'.GetMessage("BPTS_AI_TASKS_NUM").' '.$tasksCount,
					"FOOTER" => '<a href="/bitrix/admin/bizproc_task_list.php?lang='.LANGUAGE_ID.'">'.GetMessage("BPTS_AI_TASKS_PERF").'</a>',
					"COLOR" => "red",
					"ALERT" => true
				);

				CAdminInformer::AddItem($bpAIParams);
			}
		}
	}

	public function createTask($arFields)
	{
		return self::Add($arFields);
	}

	public static function add($arFields)
	{
		global $DB;

		self::ParseFields($arFields, 0);

		$arInsert = $DB->PrepareInsert("b_bp_task", $arFields);

		$strSql =
			"INSERT INTO b_bp_task (".$arInsert[0].", MODIFIED) ".
			"VALUES(".$arInsert[1].", ".$DB->CurrentTimeFunction().")";
		$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);

		$taskId = intval($DB->LastID());

		if ($taskId > 0)
		{
			$users = [];
			foreach ($arFields["USERS"] as $userId)
			{
				$userId = intval($userId);
				if (in_array($userId, $users))
					continue;

				$DB->Query(
					"INSERT INTO b_bp_task_user (USER_ID, TASK_ID, ORIGINAL_USER_ID) ".
					"VALUES (".intval($userId).", ".intval($taskId).", ".intval($userId).") "
				);

				CUserCounter::Increment($userId, 'bp_tasks', '**');

				$users[] = $userId;
			}

			$arFields['COUNTERS_INCREMENTED'] = $users;
			self::onTaskChange($taskId, $arFields, CBPTaskChangedStatus::Add);

			foreach (GetModuleEvents("bizproc", "OnTaskAdd", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($taskId, $arFields));
		}

		return $taskId;
	}

	public static function update($id, $arFields)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
		{
			throw new Exception("id");
		}

		self::ParseFields($arFields, $id);

		$strUpdate = $DB->PrepareUpdate("b_bp_task", $arFields);

		if ($strUpdate)
		{
			$strSql =
				"UPDATE b_bp_task SET ".
				"	".$strUpdate.", ".
				"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
				"WHERE ID = ".intval($id)." ";
			$DB->Query($strSql, False, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$removedUsers = $addedUsers = $decremented = $incremented = [];

		if (is_set($arFields, 'USERS'))
		{
			$arFields['USERS'] = array_map('intval', array_unique($arFields['USERS']));
			$previousUserIds = static::getTaskUserIds($id);

			foreach ($arFields['USERS'] as $userId)
			{
				if (in_array($userId, $previousUserIds, true))
				{
					continue;
				}

				$DB->Query(
					"INSERT INTO b_bp_task_user (USER_ID, TASK_ID, ORIGINAL_USER_ID) ".
					"VALUES ({$userId}, {$id}, {$userId})"
				);

				$incremented[] = $userId;
				CUserCounter::Increment($userId, 'bp_tasks', '**');
			}

			$diff = array_diff($previousUserIds, $arFields['USERS']);
			foreach ($diff as $removedUserId)
			{
				$decremented[] = $removedUserId;
				CUserCounter::Decrement($removedUserId, 'bp_tasks', '**');
				$removedUsers[] = $removedUserId;
				$DB->Query("DELETE FROM b_bp_task_user WHERE TASK_ID = {$id} AND USER_ID = {$removedUserId}");
			}
		}

		$userStatuses = array();
		if (isset($arFields['STATUS']) && $arFields['STATUS'] > CBPTaskStatus::Running)
		{
			$dbResUser = $DB->Query("SELECT USER_ID FROM b_bp_task_user WHERE TASK_ID = ".$id." AND STATUS = ".CBPTaskUserStatus::Waiting);
			while ($userIterator = $dbResUser->Fetch())
			{
				$decremented[] = $userIterator["USER_ID"];
				CUserCounter::Decrement($userIterator["USER_ID"], 'bp_tasks', '**');

				if ($arFields['STATUS'] == CBPTaskStatus::Timeout)
					$userStatuses[$userIterator["USER_ID"]] = CBPTaskUserStatus::No;
				else
					$removedUsers[] = $userIterator["USER_ID"];
			}
			if ($arFields['STATUS'] == CBPTaskStatus::Timeout)
			{
				$DB->Query("UPDATE b_bp_task_user SET STATUS = ".CBPTaskUserStatus::No.", DATE_UPDATE = ".$DB->CurrentTimeFunction()
					." WHERE TASK_ID = ".$id." AND STATUS = ".CBPTaskUserStatus::Waiting);
			}
			else
				$DB->Query("DELETE FROM b_bp_task_user WHERE TASK_ID = ".$id." AND STATUS = ".CBPTaskUserStatus::Waiting);
		}

		foreach (GetModuleEvents("bizproc", "OnTaskUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($id, $arFields));

		if ($removedUsers)
		{
			$arFields['USERS_REMOVED'] = $removedUsers;
		}
		if ($addedUsers)
		{
			$arFields['USERS_ADDED'] = $addedUsers;
		}
		if ($userStatuses)
		{
			$arFields['USERS_STATUSES'] = $userStatuses;
		}

		$arFields['COUNTERS_INCREMENTED'] = $incremented;
		$arFields['COUNTERS_DECREMENTED'] = $decremented;

		self::onTaskChange($id, $arFields, CBPTaskChangedStatus::Update);
		return $id;
	}

	public static function getList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS");

		static $arFields = array(
			"ID" => array("FIELD" => "T.ID", "TYPE" => "int"),
			"WORKFLOW_ID" => array("FIELD" => "T.WORKFLOW_ID", "TYPE" => "string"),
			"ACTIVITY" => array("FIELD" => "T.ACTIVITY", "TYPE" => "string"),
			"ACTIVITY_NAME" => array("FIELD" => "T.ACTIVITY_NAME", "TYPE" => "string"),
			"MODIFIED" => array("FIELD" => "T.MODIFIED", "TYPE" => "datetime"),
			"OVERDUE_DATE" => array("FIELD" => "T.OVERDUE_DATE", "TYPE" => "datetime"),
			"NAME" => array("FIELD" => "T.NAME", "TYPE" => "string"),
			"DESCRIPTION" => array("FIELD" => "T.DESCRIPTION", "TYPE" => "string"),
			"PARAMETERS" => array("FIELD" => "T.PARAMETERS", "TYPE" => "string"),
			"IS_INLINE" => array("FIELD" => "T.IS_INLINE", "TYPE" => "string"),
			"DELEGATION_TYPE" => array("FIELD" => "T.DELEGATION_TYPE", "TYPE" => "int"),
			"STATUS" => array("FIELD" => "T.STATUS", "TYPE" => "int"),
			'DOCUMENT_NAME' => array("FIELD" => "T.DOCUMENT_NAME", "TYPE" => "string"),
			"USER_ID" => array("FIELD" => "TU.USER_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_bp_task_user TU ON (T.ID = TU.TASK_ID)"),
			"USER_STATUS" => array("FIELD" => "TU.STATUS", "TYPE" => "int", "FROM" => "INNER JOIN b_bp_task_user TU ON (T.ID = TU.TASK_ID)"),
			"WORKFLOW_TEMPLATE_ID" => array("FIELD" => "WS.WORKFLOW_TEMPLATE_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)"),
			"MODULE_ID" => array("FIELD" => "WS.MODULE_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)"),
			"ENTITY" => array("FIELD" => "WS.ENTITY", "TYPE" => "string", "FROM" => "INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)"),
			"DOCUMENT_ID" => array("FIELD" => "WS.DOCUMENT_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)"),
			"WORKFLOW_TEMPLATE_NAME" => array("FIELD" => "WT.NAME", "TYPE" => "string",
											"FROM" => array("INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)",
											"INNER JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID)")),
			"WORKFLOW_TEMPLATE_TEMPLATE_ID" => array("FIELD" => "WS.WORKFLOW_TEMPLATE_ID", "TYPE" => "int",
													"FROM" => array("INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)")),
			'WORKFLOW_STATE' => array("FIELD" => "WS.STATE_TITLE", "TYPE" => "string", "FROM" => "INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)"),
			'WORKFLOW_STARTED' => array("FIELD" => "WS.STARTED", "TYPE" => "datetime", "FROM" => "INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)"),
			'WORKFLOW_STARTED_BY' => array("FIELD" => "WS.STARTED_BY", "TYPE" => "int", "FROM" => "INNER JOIN b_bp_workflow_state WS ON (T.WORKFLOW_ID = WS.ID)"),
		);

		$arSqls = CBPHelper::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_bp_task T ".
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
			"FROM b_bp_task T ".
			"	".$arSqls["FROM"]." ";
		if ($arSqls["WHERE"] <> '')
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if ($arSqls["GROUPBY"] <> '')
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if ($arSqls["ORDERBY"] <> '')
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && empty($arNavStartParams["nTopCount"]))
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_bp_task T ".
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

		$dbRes = new CBPTaskResult($dbRes);
		return $dbRes;
	}
}

class CBPTaskResult extends CDBResult
{
	private static $classesList = [
		Bizproc\BaseType\Value\Date::class,
		Bizproc\BaseType\Value\DateTime::class,
		Main\Type\Date::class,
		Main\Type\DateTime::class,
		\DateTime::class,
		\DateTimeZone::class,
		Main\Web\Uri::class
	];

	function fetch()
	{
		$res = parent::Fetch();

		if ($res)
		{
			if (!empty($res["PARAMETERS"]))
			{
				$res["PARAMETERS"] = unserialize($res["PARAMETERS"], ['allowed_classes' => self::$classesList]);
			}
		}

		return $res;
	}

	function getNext($bTextHtmlAuto=true, $use_tilda=true)
	{
		$res = parent::GetNext($bTextHtmlAuto, $use_tilda);

		if ($res)
		{
			if ($res["DESCRIPTION"] <> '')
			{
				$res["DESCRIPTION"] = CBPHelper::convertBBtoText($res["DESCRIPTION"]);
			}
		}

		return $res;
	}

	/**
	 * @deprecated
	 * @param $text
	 * @return array|string|string[]|null
	 */
	function convertBBCode($text)
	{
		$text = preg_replace(
			"'(?<=^|[\s.,;:!?\#\-\*\|\[\(\)\{\}]|\s)((http|https|news|ftp|aim|mailto)://[\.\-\_\:a-z0-9\@]([^\"\s\'\[\]\{\}])*)'is",
			"[url]\\1[/url]",
			$text
		);

		$text = preg_replace_callback("#\[img\](.+?)\[/img\]#i", array($this, "ConvertBCodeImageTag"), $text);

		$text = preg_replace_callback(
			"/\[url\]([^\]]+?)\[\/url\]/i".BX_UTF_PCRE_MODIFIER,
			array($this, "ConvertBCodeAnchorTag"),
			$text
		);
		$text = preg_replace_callback(
			"/\[url\s*=\s*([^\]]+?)\s*\](.*?)\[\/url\]/i".BX_UTF_PCRE_MODIFIER,
			array($this, "ConvertBCodeAnchorTag"),
			$text
		);

		$text = preg_replace(
			array(
				"/\[b\](.+?)\[\/b\]/is".BX_UTF_PCRE_MODIFIER,
				"/\[i\](.+?)\[\/i\]/is".BX_UTF_PCRE_MODIFIER,
				"/\[s\](.+?)\[\/s\]/is".BX_UTF_PCRE_MODIFIER,
				"/\[u\](.+?)\[\/u\]/is".BX_UTF_PCRE_MODIFIER
			),
			array(
				"<b>\\1</b>",
				"<i>\\1</i>",
				"<s>\\1</s>",
				"<u>\\1</u>"
			),
			$text
		);

		return $text;
	}

	/**
	 * @deprecated
	 * @param string $url
	 * @return string
	 */
	function convertBCodeImageTag($url = "")
	{
		if (is_array($url))
			$url = $url[1];
		$url = trim($url);
		if ($url == '')
			return "";

		$extension = preg_replace("/^.*\.(\S+)$/".BX_UTF_PCRE_MODIFIER, "\\1", $url);
		$extension = mb_strtolower($extension);
		$extension = preg_quote($extension, "/");

		$bErrorIMG = False;

		if (preg_match("/[?&;]/".BX_UTF_PCRE_MODIFIER, $url))
			$bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/$extension(\||\$)/".BX_UTF_PCRE_MODIFIER, "gif|jpg|jpeg|png"))
			$bErrorIMG = True;
		if (!$bErrorIMG && !preg_match("/^((http|https|ftp)\:\/\/[-_:.a-z0-9@]+)*(\/[-_+\/=:.a-z0-9@%]+)$/i".BX_UTF_PCRE_MODIFIER, $url))
			$bErrorIMG = True;

		if ($bErrorIMG)
			return "[img]".$url."[/img]";

		return '<img src="'.$url.'" border="0" />';
	}

	/**
	 * @deprecated
	 * @param $url
	 * @param string $text
	 * @return string
	 */
	function convertBCodeAnchorTag($url, $text = '')
	{
		if (is_array($url))
		{
			$text = isset($url[2]) ? $url[2] : $url[1];
			$url = $url[1];
		}

		$result = "";

		if ($url === $text)
		{
			$arUrl = explode(", ", $url);
			$arText = $arUrl;
		}
		else
		{
			$arUrl = array($url);
			$arText = array($text);
		}

		for ($i = 0, $n = count($arUrl); $i < $n; $i++)
		{
			$url = $arUrl[$i];
			$text = $arText[$i];

			$text = str_replace("\\\"", "\"", $text);
			$end = "";

			if (preg_match("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, $url, $match))
			{
				$end = $match[1];
				$url = preg_replace("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, "", $url);
				$text = preg_replace("/([\.,\?]|&#33;)$/".BX_UTF_PCRE_MODIFIER, "", $text);
			}

			$url = preg_replace(
				array("/&amp;/".BX_UTF_PCRE_MODIFIER, "/javascript:/i".BX_UTF_PCRE_MODIFIER),
				array("&", "java script&#58; "),
				$url
			);
			if (mb_substr($url, 0, 1) != "/" && !preg_match("/^(http|news|https|ftp|aim|mailto)\:\/\//i".BX_UTF_PCRE_MODIFIER, $url))
				$url = 'http://'.$url;
			if (!preg_match("/^((http|https|news|ftp|aim):\/\/[-_:.a-z0-9@]+)*([^\"\'])+$/i".BX_UTF_PCRE_MODIFIER, $url))
				return $text." (".$url.")".$end;

			$text = preg_replace(
				array("/&amp;/i".BX_UTF_PCRE_MODIFIER, "/javascript:/i".BX_UTF_PCRE_MODIFIER),
				array("&", "javascript&#58; "),
				$text
			);

			if ($result !== "")
				$result .= ", ";

			$result .= "<a href=\"".$url."\" target='_blank'>".$text."</a>".$end;
		}

		return $result;
	}

}
