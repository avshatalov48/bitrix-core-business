<?php

use Bitrix\Main;
use Bitrix\Bizproc;

/**
 * Bizproc API Helper for external usage.
 */
class CBPDocument
{
	const PARAM_TAGRET_USER = 'TargetUser';
	const PARAM_MODIFIED_DOCUMENT_FIELDS = 'ModifiedDocumentField';
	const PARAM_USE_FORCED_TRACKING = 'UseForcedTracking';
	const PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT = 'IgnoreSimultaneousProcessesLimit';
	const PARAM_DOCUMENT_EVENT_TYPE = 'DocumentEventType';
	const PARAM_DOCUMENT_TYPE = '__DocumentType';
	const PARAM_PRE_GENERATED_WORKFLOW_ID = 'PreGeneratedWorkflowId';

	public static function migrateDocumentType($oldType, $newType)
	{
		$templateIds = array();
		$db = CBPWorkflowTemplateLoader::GetList(array(), array("DOCUMENT_TYPE" => $oldType), false, false, array("ID"));
		while ($ar = $db->Fetch())
			$templateIds[] = $ar["ID"];

		foreach ($templateIds as $id)
			CBPWorkflowTemplateLoader::Update($id, array("DOCUMENT_TYPE" => $newType));

		if (count($templateIds) > 0)
		{
			CBPHistoryService::MigrateDocumentType($oldType, $newType, $templateIds);
			CBPStateService::MigrateDocumentType($oldType, $newType, $templateIds);
			Bizproc\Workflow\Entity\WorkflowInstanceTable::migrateDocumentType($oldType, $newType, $templateIds);
		}
	}

	/**
	 * Method returns array of workflow templates and states for specified document.
	 * If document id is set method returns array of running and terminated workflow states and also templates which started on document edit action.
	 * If document id is not set method returns array of templates which started on document add.
	 * Return array example: array(
	 *		workflow_id_or_template_id => array(
	 *			"ID" => workflow_id,
	 *			"TEMPLATE_ID" => template_id,
	 *			"TEMPLATE_NAME" => template_name,
	 *			"TEMPLATE_DESCRIPTION" => template_description,
	 *			"TEMPLATE_PARAMETERS" => template_parameters,
	 *			"STATE_NAME" => current_state_name,
	 *			"STATE_TITLE" => current_state_title,
	 *			"STATE_MODIFIED" => state_modified_datetime,
	 *			"STATE_PARAMETERS" => state_parameters,
	 *			"STATE_PERMISSIONS" => state_permissions,
	 *			"WORKFLOW_STATUS" => workflow_status,
	 *		),
	 * 		. . .
	 *	)
	 * TEMPLATE_PARAMETERS example:
	 *	array(
	 *		"param1" => array(
	 *			"Name" => "Parameter 1",
	 *			"Description" => "",
	 *			"Type" => "int",
	 *			"Required" => true,
	 *			"Multiple" => false,
	 *			"Default" => 8,
	 *			"Options" => null,
	 *		),
	 *		"param2" => array(
	 *			"Name" => "Parameter 2",
	 *			"Description" => "",
	 *			"Type" => "select",
	 *			"Required" => false,
	 *			"Multiple" => true,
	 *			"Default" => "v2",
	 *			"Options" => array(
	 *				"v1" => "V 1",
	 *				"v2" => "V 2",
	 *				"v3" => "V 3",
	 *				. . .
	 *			),
	 *		),
	 *		. . .
	 *	)
	 * STATE_PARAMETERS example:
	 *	array(
	 *		array(
	 *			"NAME" => event_name,
	 *			"TITLE" => event_title,
	 *			"PERMISSION" => array('user_1')
	 *		),
	 *		. . .
	 *	)
	 * STATE_PERMISSIONS example:
	 *	array(
	 *		operation => users_array,
	 *		. . .
	 *	)
	 *
	 * @param array $documentType - Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE)
	 * @param null|array $documentId - Document id array(MODULE_ID, ENTITY, DOCUMENT_ID).
	 * @return array - Workflow states and templates.
	 */
	public static function getDocumentStates($documentType, $documentId = null)
	{
		$arDocumentStates = array();

		if ($documentId != null)
			$arDocumentStates = CBPStateService::GetDocumentStates($documentId);

		$arTemplateStates = CBPWorkflowTemplateLoader::GetDocumentTypeStates(
			$documentType,
			(($documentId != null) ? CBPDocumentEventType::Edit : CBPDocumentEventType::Create)
		);

		return ($arDocumentStates + $arTemplateStates);
	}

	public static function getActiveStates(array $documentId, $limit = 0)
	{
		$documentId = CBPHelper::ParseDocumentId($documentId);
		$workflowIds = Bizproc\Workflow\Entity\WorkflowInstanceTable::getIdsByDocument($documentId);

		if (!$workflowIds)
		{
			return [];
		}

		if ($limit > 0 && count($workflowIds) > $limit)
		{
			$workflowIds = array_slice($workflowIds, 0, $limit);
		}

		return CBPStateService::GetDocumentStates($documentId, $workflowIds);
	}

	/**
	 * Method returns workflow state for specified document.
	 *
	 * @param array $documentId - Document id array(MODULE_ID, ENTITY, DOCUMENT_ID).
	 * @param string $workflowId - Workflow id.
	 * @return array - Workflow state array.
	 */
	public static function getDocumentState($documentId, $workflowId)
	{
		$arDocumentState = CBPStateService::GetDocumentStates($documentId, $workflowId);
		return $arDocumentState;
	}

	public static function mergeDocuments($firstDocumentId, $secondDocumentId)
	{
		CBPStateService::MergeStates($firstDocumentId, $secondDocumentId);
		Bizproc\Workflow\Entity\WorkflowInstanceTable::mergeByDocument($firstDocumentId, $secondDocumentId);
		CBPHistoryService::MergeHistory($firstDocumentId, $secondDocumentId);
	}

	/**
	 * Method returns array of events available for specified user and specified state
	 *
	 * @param int $userId - User id.
	 * @param array $arGroups - User groups.
	 * @param array $arState - Workflow state.
	 * @param bool $appendExtendedGroups - Append extended groups.
	 * @return array - Events array array(array("NAME" => event_name, "TITLE" => event_title), ...).
	 * @throws Exception
	 */
	public static function getAllowableEvents($userId, $arGroups, $arState, $appendExtendedGroups = false)
	{
		if (!is_array($arState))
			throw new Exception("arState");
		if (!is_array($arGroups))
			throw new Exception("arGroups");

		$arGroups = CBPHelper::convertToExtendedGroups($arGroups);
		if ($appendExtendedGroups)
		{
			$arGroups = array_merge($arGroups, CBPHelper::getUserExtendedGroups($userId));
		}
		if (!in_array("group_u".$userId, $arGroups))
			$arGroups[] = "group_u".$userId;

		$arResult = array();

		if (is_array($arState["STATE_PARAMETERS"]) && count($arState["STATE_PARAMETERS"]) > 0)
		{
			foreach ($arState["STATE_PARAMETERS"] as $arStateParameter)
			{
				$arStateParameter["PERMISSION"] = CBPHelper::convertToExtendedGroups($arStateParameter["PERMISSION"]);

				if (count($arStateParameter["PERMISSION"]) <= 0
					|| count(array_intersect($arGroups, $arStateParameter["PERMISSION"])) > 0)
				{
					$arResult[] = array(
						"NAME" => $arStateParameter["NAME"],
						"TITLE" => (($arStateParameter["TITLE"] <> '') ? $arStateParameter["TITLE"] : $arStateParameter["NAME"]),
					);
				}
			}
		}

		return $arResult;
	}

	public static function addDocumentToHistory($parameterDocumentId, $name, $userId)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId <> '')
			CModule::IncludeModule($moduleId);

		if (!class_exists($entity))
			return false;

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		$historyService = $runtime->GetService("HistoryService");
		$documentService = $runtime->GetService("DocumentService");

		$userId = intval($userId);

		$historyIndex = $historyService->AddHistory(
			array(
				"DOCUMENT_ID" => $parameterDocumentId,
				"NAME" => "New",
				"DOCUMENT" => null,
				"USER_ID" => $userId,
			)
		);

		$arDocument = $documentService->GetDocumentForHistory($parameterDocumentId, $historyIndex);
		if (!is_array($arDocument))
			return false;

		$historyService->UpdateHistory(
			$historyIndex,
			array(
				"NAME" => $name,
				"DOCUMENT" => $arDocument,
			)
		);

		return $historyIndex;
	}

	/**
	 * Method returns allowable operations for specified user in specified states.
	 * If specified states are not relevant to state machine returns null.
	 * If user has no access returns array().
	 * Else returns operations array(operation, ...).
	 *
	 * @param int $userId - User id.
	 * @param array $arGroups - User groups.
	 * @param array $arStates - Workflow states.
	 * @param bool $appendExtendedGroups - Append extended groups.
	 * @return array|null - Allowable operations.
	 * @throws Exception
	 */
	public static function getAllowableOperations($userId, $arGroups, $arStates, $appendExtendedGroups = false)
	{
		if (!is_array($arStates))
			throw new Exception("arStates");
		if (!is_array($arGroups))
			throw new Exception("arGroups");

		$arGroups = CBPHelper::convertToExtendedGroups($arGroups);
		if ($appendExtendedGroups)
		{
			$arGroups = array_merge($arGroups, CBPHelper::getUserExtendedGroups($userId));
		}
		if (!in_array("group_u".$userId, $arGroups))
			$arGroups[] = "group_u".$userId;

		$result = null;

		foreach ($arStates as $arState)
		{
			if (is_array($arState["STATE_PERMISSIONS"]) && count($arState["STATE_PERMISSIONS"]) > 0)
			{
				if ($result == null)
					$result = array();

				foreach ($arState["STATE_PERMISSIONS"] as $operation => $arOperationGroups)
				{
					$arOperationGroups = CBPHelper::convertToExtendedGroups($arOperationGroups);

					if (count(array_intersect($arGroups, $arOperationGroups)) > 0)
						$result[] = mb_strtolower($operation);
				}
			}
		}

		return $result;
	}

	/**
	 * Method check can operate user specified operation in specified state.
	 * If specified states are not relevant to state machine returns true.
	 * If user can`t do operation return false.
	 * Else returns true.
	 *
	 * @param string $operation - Operation.
	 * @param int $userId - User id.
	 * @param array $arGroups - User groups.
	 * @param array $arStates - Workflows states.
	 * @return bool
	 * @throws Exception
	 */
	public static function canOperate($operation, $userId, $arGroups, $arStates)
	{
		$operation = trim($operation);
		if ($operation == '')
			throw new Exception("operation");

		$operations = self::GetAllowableOperations($userId, $arGroups, $arStates);
		if ($operations === null)
			return true;

		return in_array($operation, $operations);
	}

	/**
	 * Method starts workflow.
	 *
	 * @param int $workflowTemplateId - Template id.
	 * @param array $documentId - Document id array(MODULE_ID, ENTITY, DOCUMENT_ID).
	 * @param array $parameters - Workflow parameters.
	 * @param array $errors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	 * @param array|null $parentWorkflow - Parent workflow information.
	 * @return string - Workflow id.
	 */
	public static function startWorkflow($workflowTemplateId, $documentId, $parameters, &$errors, $parentWorkflow = null)
	{
		$errors = [];
		$runtime = CBPRuntime::GetRuntime();

		$parameters = static::prepareWorkflowParameters($parameters);

		try
		{
			$wi = $runtime->CreateWorkflow($workflowTemplateId, $documentId, $parameters, $parentWorkflow);
			$wi->Start();
			return $wi->GetInstanceId();
		}
		catch (Exception $e)
		{
			$errors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]"
			);
		}

		return null;
	}

	public static function startDebugWorkflow($workflowTemplateId, $documentId, $parameters, &$errors): ?string
	{
		$errors = [];
		$runtime = CBPRuntime::GetRuntime(true);

		$parameters = static::prepareWorkflowParameters($parameters);

		try
		{
			$workflow = $runtime->createDebugWorkflow($workflowTemplateId, $documentId, $parameters);
			$workflow->Start();

			return $workflow->getInstanceId();
		}
		catch (Exception $e)
		{
			$errors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]"
			);
		}

		return null;
	}

	private static function prepareWorkflowParameters($parameters): array
	{
		if (!is_array($parameters))
		{
			$parameters = [$parameters];
		}

		if (!array_key_exists(static::PARAM_TAGRET_USER, $parameters))
		{
			$currentUserId = Main\Engine\CurrentUser::get()->getId();
			$parameters[static::PARAM_TAGRET_USER] = isset($currentUserId) ? "user_{$currentUserId}" : null;
		}

		if (!isset($parameters[static::PARAM_MODIFIED_DOCUMENT_FIELDS]))
		{
			$parameters[static::PARAM_MODIFIED_DOCUMENT_FIELDS] = false;
		}

		if (!isset($parameters[static::PARAM_DOCUMENT_EVENT_TYPE]))
		{
			$parameters[static::PARAM_DOCUMENT_EVENT_TYPE] = CBPDocumentEventType::None;
		}

		if (!isset($parameters[static::PARAM_PRE_GENERATED_WORKFLOW_ID]))
		{
			$parameters[static::PARAM_PRE_GENERATED_WORKFLOW_ID] = CBPRuntime::generateWorkflowId();
		}

		return $parameters;
	}

	/**
	* Method auto starts workflow.
	*
	* @param array $documentType -  Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	* @param int $autoExecute - CBPDocumentEventType (1 = CBPDocumentEventType::Create, 2 = CBPDocumentEventType::Edit).
	* @param array $documentId - Document id array(MODULE_ID, ENTITY, DOCUMENT_ID).
	* @param array $arParameters - Workflow parameters.
	* @param array $arErrors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	*/
	public static function autoStartWorkflows($documentType, $autoExecute, $documentId, $arParameters, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arParameters))
			$arParameters = array($arParameters);

		if (!isset($arParameters[static::PARAM_TAGRET_USER]))
			$arParameters[static::PARAM_TAGRET_USER] = is_object($GLOBALS["USER"]) ? "user_".intval($GLOBALS["USER"]->GetID()) : null;

		if (!isset($arParameters[static::PARAM_MODIFIED_DOCUMENT_FIELDS]))
			$arParameters[static::PARAM_MODIFIED_DOCUMENT_FIELDS] = false;

		$arParameters[static::PARAM_DOCUMENT_EVENT_TYPE] = $autoExecute;

		$arWT = CBPWorkflowTemplateLoader::SearchTemplatesByDocumentType($documentType, $autoExecute);
		foreach ($arWT as $wt)
		{
			try
			{
				$wi = $runtime->CreateWorkflow($wt["ID"], $documentId, $arParameters);
				$wi->Start();
			}
			catch (Exception $e)
			{
				$arErrors[] = array(
					"code" => $e->getCode(),
					"message" => $e->getMessage(),
					"file" => $e->getFile()." [".$e->getLine()."]"
				);
			}
		}
	}

	/**
	* Method sends external event to workflow.
	*
	* @param string $workflowId - Workflow id.
	* @param string $workflowEvent - Event name.
	* @param array $arParameters - Event parameters.
	* @param array $arErrors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	*/
	public static function sendExternalEvent($workflowId, $workflowEvent, $arParameters, &$arErrors)
	{
		$arErrors = array();

		try
		{
			CBPRuntime::SendExternalEvent($workflowId, $workflowEvent, $arParameters);
		}
		catch(Exception $e)
		{
			$arErrors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]"
			);
		}
	}

	/**
	* Method terminates workflow.
	*
	* @param string $workflowId -  Workflow id.
	* @param array $documentId - Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	* @param array $arErrors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	* @param string $stateTitle - State title (workflow status).
	*/
	public static function terminateWorkflow($workflowId, $documentId, &$arErrors, $stateTitle = '')
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		try
		{
			$workflow = $runtime->GetWorkflow($workflowId, true);
			if ($documentId)
			{
				$d = $workflow->GetDocumentId();
				if ($d[0] != $documentId[0] || $d[1] != $documentId[1] || mb_strtolower($d[2]) !== mb_strtolower($documentId[2]))
					throw new Exception(GetMessage("BPCGDOC_INVALID_WF"));
			}
			$workflow->Terminate(null, $stateTitle);
		}
		catch(Exception $e)
		{
			$arErrors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]"
			);
			return false;
		}
		return true;
	}

	public static function killWorkflow($workflowId, $terminate = true, $documentId = null)
	{
		$errors = array();
		if ($terminate)
			static::TerminateWorkflow($workflowId, $documentId, $errors);

		if (!$errors)
		{
			Bizproc\Workflow\Entity\WorkflowInstanceTable::delete($workflowId);
			CBPTaskService::DeleteByWorkflow($workflowId);
			CBPStateService::DeleteWorkflow($workflowId);

			if (!Bizproc\Debugger\Session\Manager::isDebugWorkflow($workflowId))
			{
				CBPTrackingService::DeleteByWorkflow($workflowId);
			}
		}

		return $errors;
	}

	/**
	 * Method removes all related document data.
	 * @param array $documentId - Document id array(MODULE_ID, ENTITY, DOCUMENT_ID).
	 * @param array $errors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	 */
	public static function onDocumentDelete($documentId, &$errors)
	{
		$errors = [];

		$instanceIds = Bizproc\Workflow\Entity\WorkflowInstanceTable::getIdsByDocument($documentId);
		foreach ($instanceIds as $instanceId)
		{
			static::TerminateWorkflow($instanceId, $documentId, $errors);
		}

		$debugSession = Bizproc\Debugger\Session\Manager::getActiveSession();
		if ($debugSession && $debugSession->isFixedDocument($documentId))
		{
			Bizproc\Debugger\Listener::getInstance()->onDocumentDeleted();
		}

		//Deferred deletion
		Bizproc\Worker\Document\DeleteStepper::bindDocument($documentId);

		//touch runtime
		CBPRuntime::getRuntime()->onDocumentDelete($documentId);
	}

	public static function postTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = "")
	{
		$originalUserId = CBPTaskService::getOriginalTaskUserId($arTask['ID'], $userId);

		return CBPActivity::CallStaticMethod(
			$arTask["ACTIVITY"],
			"PostTaskForm",
			array(
				$arTask,
				$originalUserId,
				$arRequest,
				&$arErrors,
				$userName,
				$userId
			)
		);
	}

	public static function showTaskForm($arTask, $userId, $userName = "", $arRequest = null)
	{
		return CBPActivity::CallStaticMethod(
			$arTask["ACTIVITY"],
			"ShowTaskForm",
			array(
				$arTask,
				$userId,
				$userName,
				$arRequest
			)
		);
	}

	/**
	 * @param int $userId Task User Id.
	 * @param int $status Task user status.
	 * @param int|array $ids Task ids.
	 * @param array $errors Error collection.
	 * @return bool
	 */
	public static function setTasksUserStatus($userId, $status, $ids = array(), &$errors = array())
	{
		$filter = array(
			'USER_ID' => $userId,
			'STATUS' => CBPTaskStatus::Running,
			'USER_STATUS' => CBPTaskUserStatus::Waiting,
		);
		if ($ids)
		{
			$ids = array_filter(array_map('intval', (array)$ids));
			if ($ids)
				$filter['ID'] = $ids;
		}

		$iterator = CBPTaskService::GetList(array('ID'=>'ASC'),
			$filter,
			false,
			false,
			array('ID', 'NAME', 'WORKFLOW_ID', 'ACTIVITY', 'ACTIVITY_NAME', 'IS_INLINE'));
		while ($task = $iterator->fetch())
		{
			if ($task['IS_INLINE'] == 'Y')
			{
				$taskErrors = array();
				self::PostTaskForm($task, $userId, array('INLINE_USER_STATUS' => $status), $taskErrors);
				if (!empty($taskErrors))
					foreach ($taskErrors as $error)
						$errors[] = GetMessage('BPCGDOC_ERROR_ACTION', array('#NAME#' => $task['NAME'], '#ERROR#' => $error['message']));
			}
			else
				$errors[] = GetMessage('BPCGDOC_ERROR_TASK_IS_NOT_INLINE', array('#NAME#' => $task['NAME']));

		}
		return true;
	}

	/**
	 * @param int $fromUserId Task current user.
	 * @param int $toUserId Task target user.
	 * @param array|int $ids Task ids.
	 * @param array $errors Error collection.
	 * @param null | array $allowedDelegationType
	 * @return bool
	 */
	public static function delegateTasks($fromUserId, $toUserId, $ids = array(), &$errors = array(), $allowedDelegationType = null)
	{
		$filter = array(
			'USER_ID' => $fromUserId,
			'STATUS' => CBPTaskStatus::Running,
			'USER_STATUS' => CBPTaskUserStatus::Waiting
		);

		if ($ids)
		{
			$ids = array_filter(array_map('intval', (array)$ids));
			if ($ids)
				$filter['ID'] = $ids;
		}

		$iterator = CBPTaskService::GetList(
				array('ID'=>'ASC'),
				$filter,
				false,
				false,
				array('ID', 'NAME', 'WORKFLOW_ID', 'ACTIVITY_NAME', 'DELEGATION_TYPE')
		);
		$found = false;
		$trackingService = null;
		$sendImNotify = (CModule::IncludeModule("im"));

		while ($task = $iterator->fetch())
		{
			if ($allowedDelegationType && !in_array((int)$task['DELEGATION_TYPE'], $allowedDelegationType, true))
			{
				$errors[] = GetMessage('BPCGDOC_ERROR_DELEGATE_'.$task['DELEGATION_TYPE'], array('#NAME#' => $task['NAME']));
			}
			elseif (!CBPTaskService::delegateTask($task['ID'], $fromUserId, $toUserId))
			{
				$errors[] = GetMessage('BPCGDOC_ERROR_DELEGATE', array('#NAME#' => $task['NAME']));
			}
			else
			{
				if (!$found)
				{
					$runtime = CBPRuntime::GetRuntime();
					$runtime->StartRuntime();
					/** @var CBPTrackingService $trackingService */
					$trackingService = $runtime->GetService('TrackingService');
				}
				$found = true;

				$trackingService->Write(
					$task['WORKFLOW_ID'],
					CBPTrackingType::Custom,
					$task['ACTIVITY_NAME'],
					CBPActivityExecutionStatus::Executing,
					CBPActivityExecutionResult::None,
					GetMessage('BPCGDOC_DELEGATE_LOG_TITLE'),
					GetMessage('BPCGDOC_DELEGATE_LOG', array(
						'#NAME#' => $task['NAME'],
						'#FROM#' => '{=user:user_'.$fromUserId.'}',
						'#TO#' => '{=user:user_'.$toUserId.'}'
					))
				);

				if ($sendImNotify)
				{
					CIMNotify::Add(array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						'FROM_USER_ID' => $fromUserId,
						'TO_USER_ID' => $toUserId,
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "bizproc",
						"NOTIFY_EVENT" => "delegate_task",
						"NOTIFY_TAG" => "BIZPROC|TASK|".$task['ID'],
						'MESSAGE' => GetMessage('BPCGDOC_DELEGATE_NOTIFY_TEXT', array(
							'#TASK_URL#' => '/company/personal/bizproc/'.(int)$task['ID'].'/',
							'#TASK_NAME#' => $task['NAME']
						))
					));
				}
			}
		}
		return $found;
	}

	public static function getTaskControls($arTask)
	{
		return CBPActivity::CallStaticMethod(
			$arTask["ACTIVITY"],
			"getTaskControls",
			array(
				$arTask
			)
		);
	}

	/**
	 * Method validates parameters values from StartWorkflowParametersShow.
	 *
	 * @param int $templateId - Template id.
	 * @param array $arWorkflowParameters - Workflow parameters.
	 * @param $documentType - Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	 * @param array $arErrors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	 * @return array - Valid Parameters values.
	 */
	public static function startWorkflowParametersValidate($templateId, $arWorkflowParameters, $documentType, &$arErrors)
	{
		$arErrors = array();

		$templateId = intval($templateId);
		if ($templateId <= 0)
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPCGDOC_EMPTY_WD_ID"),
			);
			return array();
		}

		if (!isset($arWorkflowParameters) || !is_array($arWorkflowParameters))
			$arWorkflowParameters = array();

		$arWorkflowParametersValues = array();

		$arRequest = $_REQUEST;
		foreach ($_FILES as $k => $v)
		{
			if (array_key_exists("name", $v))
			{
				if (is_array($v["name"]))
				{
					$ks = array_keys($v["name"]);
					for ($i = 0, $cnt = count($ks); $i < $cnt; $i++)
					{
						$ar = array();
						foreach ($v as $k1 => $v1)
							$ar[$k1] = $v1[$ks[$i]];

						$arRequest[$k][] = $ar;
					}
				}
				else
				{
					$arRequest[$k] = $v;
				}
			}
		}

		if (count($arWorkflowParameters) > 0)
		{
			$arErrorsTmp = array();
			$ar = array();

			foreach ($arWorkflowParameters as $parameterKey => $arParameter)
			{
				$key = "bizproc" . $templateId . "_" . $parameterKey;
				$ar[$parameterKey] = $arRequest[$key] ?? null;
			}

			$arWorkflowParametersValues = CBPWorkflowTemplateLoader::CheckWorkflowParameters(
				$arWorkflowParameters,
				$ar,
				$documentType,
				$arErrors
			);
		}

		return $arWorkflowParametersValues;
	}

	/**
	 * Method shows parameters form. Validates in StartWorkflowParametersValidate.
	 *
	 * @param int $templateId - Template id.
	 * @param array $arWorkflowParameters - Workflow parameters.
	 * @param string $formName - Form name.
	 * @param bool $bVarsFromForm - false on first form open, else - true.
	 * @param null|array $documentType Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	 */
	public static function startWorkflowParametersShow($templateId, $arWorkflowParameters, $formName, $bVarsFromForm, $documentType = null)
	{
		$templateId = intval($templateId);
		if ($templateId <= 0)
			return;

		if (!isset($arWorkflowParameters) || !is_array($arWorkflowParameters))
			$arWorkflowParameters = array();

		if ($formName == '')
			$formName = "start_workflow_form1";

		if ($documentType == null)
		{
			$dbResult = CBPWorkflowTemplateLoader::GetList(array(), array("ID" => $templateId), false, false, array("ID", "MODULE_ID", "ENTITY", "DOCUMENT_TYPE"));
			if ($arResult = $dbResult->Fetch())
				$documentType = $arResult["DOCUMENT_TYPE"];
		}

		$arParametersValues = array();
		$keys = array_keys($arWorkflowParameters);
		foreach ($keys as $key)
		{
			$v = ($bVarsFromForm ? $_REQUEST["bizproc".$templateId."_".$key] : $arWorkflowParameters[$key]["Default"]);
			if (!is_array($v))
			{
				$arParametersValues[$key] = $v;
			}
			else
			{
				$keys1 = array_keys($v);
				foreach ($keys1 as $key1)
					$arParametersValues[$key][$key1] = $v[$key1];
			}
		}

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		foreach ($arWorkflowParameters as $parameterKey => $arParameter)
		{
			$parameterKeyExt = "bizproc".$templateId."_".$parameterKey;
			?><tr>
				<td align="right" width="40%" valign="top" class="field-name"><?= $arParameter["Required"] ? "<span class=\"required\">*</span> " : ""?><?= htmlspecialcharsbx($arParameter["Name"]) ?>:<?if ($arParameter["Description"] <> '') echo "<br /><small>".htmlspecialcharsbx($arParameter["Description"])."</small><br />";?></td>
				<td width="60%" valign="top"><?
			echo $documentService->GetFieldInputControl(
				$documentType,
				$arParameter,
				array("Form" => $formName, "Field" => $parameterKeyExt),
				$arParametersValues[$parameterKey],
				false,
				true
			);
			?></td></tr><?
		}
	}

	public static function addShowParameterInit($module, $type, $document_type, $entity = "", $document_id = '')
	{
		$GLOBALS["BP_AddShowParameterInit_".$module."_".$entity."_".$document_type] = 1;
		CUtil::InitJSCore(array("window", "ajax"));

		$dts = \CBPDocument::signDocumentType([$module, $entity, $document_type]);
?>
<script src="/bitrix/js/bizproc/bizproc.js"></script>
<script>
	function BPAShowSelector(id, type, mode, arCurValues)
	{
		<?if($type=="only_users"):?>
		var def_mode = "only_users";
		<?else:?>
		var def_mode = "";
		<?endif?>

		if (!mode)
			mode = def_mode;
		var module = '<?=CUtil::JSEscape($module)?>';
		var entity = '<?=CUtil::JSEscape($entity)?>';
		var documentType = '<?=CUtil::JSEscape($document_type)?>';
		var documentId = '<?=CUtil::JSEscape($document_id)?>';

		var loadAccessLib = (typeof BX.Access === 'undefined');

		var contentUrl = '/bitrix/tools/bizproc/compatible_selector.php?mode=public&bxpublic=Y&lang=<?=LANGUAGE_ID?>'
 			+'&dts=<?=CUtil::JSEscape($dts)?>'
			+(loadAccessLib? '&load_access_lib=Y':'');

		if (mode == "only_users")
		{
			BX.WindowManager.setStartZIndex(1150);
			(new BX.CDialog({
				'content_url': contentUrl,
				'content_post': {
					'document_type': documentType,
					'document_id': documentId,
					'fieldName': id,
					'fieldType': type,
					'only_users': 'Y',
					'sessid': '<?= bitrix_sessid() ?>'
				},
				'height': 400,
				'width': 485
			})).Show();
		}
		else
		{
			if (typeof arWorkflowConstants === 'undefined')
				arWorkflowConstants = {};

			var workflowTemplateNameCur = workflowTemplateName;
			var workflowTemplateDescriptionCur = workflowTemplateDescription;
			var workflowTemplateAutostartCur = workflowTemplateAutostart;
			var arWorkflowParametersCur = arWorkflowParameters;
			var arWorkflowVariablesCur = arWorkflowVariables;
			var arWorkflowConstantsCur = arWorkflowConstants;
			var arWorkflowTemplateCur = Array(rootActivity.Serialize());

			if (arCurValues)
			{
				if (arCurValues['workflowTemplateName'])
					workflowTemplateNameCur = arCurValues['workflowTemplateName'];
				if (arCurValues['workflowTemplateDescription'])
					workflowTemplateDescriptionCur = arCurValues['workflowTemplateDescription'];
				if (arCurValues['workflowTemplateAutostart'])
					workflowTemplateAutostartCur = arCurValues['workflowTemplateAutostart'];
				if (arCurValues['arWorkflowParameters'])
					arWorkflowParametersCur = arCurValues['arWorkflowParameters'];
				if (arCurValues['arWorkflowVariables'])
					arWorkflowVariablesCur = arCurValues['arWorkflowVariables'];
				if (arCurValues['arWorkflowConstants'])
					arWorkflowConstantsCur = arCurValues['arWorkflowConstants'];
				if (arCurValues['arWorkflowTemplate'])
					arWorkflowTemplateCur = arCurValues['arWorkflowTemplate'];
			}

			var p = {
				'document_type': documentType,
				'document_id': documentId,
				'fieldName': id,
				'fieldType': type,
				'selectorMode': mode,
				'workflowTemplateName': workflowTemplateNameCur,
				'workflowTemplateDescription': workflowTemplateDescriptionCur,
				'workflowTemplateAutostart': workflowTemplateAutostartCur,
				'sessid': '<?= bitrix_sessid() ?>'
			};

			JSToPHPHidd(p, arWorkflowParametersCur, 'arWorkflowParameters');
			JSToPHPHidd(p, arWorkflowVariablesCur, 'arWorkflowVariables');
			JSToPHPHidd(p, arWorkflowConstantsCur, 'arWorkflowConstants');
			JSToPHPHidd(p, arWorkflowTemplateCur, 'arWorkflowTemplate');

			(new BX.CDialog({
				'content_url': contentUrl,
				'content_post': p,
				'height': 425,
				'width': 485
			})).Show();
		}
	}
</script>
<?
	}

	public static function showParameterField($type, $name, $values, $arParams = Array())
	{
		$id = !empty($arParams['id']) ? $arParams['id'] : md5(uniqid());

		$cols = !empty($arParams['size']) ? intval($arParams['size']) : 70;
		$defaultRows = $type == "user" ? 3 : 1;
		$rows = max((isset($arParams['rows']) && $arParams['rows']>0?intval($arParams['rows']):$defaultRows), min(5, ceil(mb_strlen((string)$values) / $cols)));

		if($type == "user")
		{
			$s = '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td valign="top">';
			$s .= '<textarea onkeydown="if(event.keyCode==45)BPAShowSelector(\''.Cutil::JSEscape(htmlspecialcharsbx($id)).'\', \''.Cutil::JSEscape($type).'\');" ';
			$s .= 'rows="'.$rows.'" ';
			$s .= 'cols="'.$cols.'" ';
			$s .= 'name="'.htmlspecialcharsbx($name).'" ';
			$s .= 'id="'.htmlspecialcharsbx($id).'" ';
			$s .= 'style="width: 100%"';
			$s .= '>'.htmlspecialcharsbx($values).'</textarea>';
			$s .= '</td><td valign="top" style="padding-left:4px" width="30">';
			$s .= CBPHelper::renderControlSelectorButton($id, $type, array('title' => GetMessage("BIZPROC_AS_SEL_FIELD_BUTTON").' (Insert)'));
			$s .= '</td></tr></table>';
		}
		elseif($type == "bool")
		{
			$s = '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td valign="top" width="30">';
			$s .= '<select name="'.htmlspecialcharsbx($name).'"><option value=""></option><option value="Y"'.($values=='Y'?' selected':'').'>'.GetMessage('MAIN_YES').'</option><option value="N"'.($values=='N'?' selected':'').'>'.GetMessage('MAIN_NO').'</option>';
			$s .= '</td><td style="padding-left:4px"><textarea ';
			$s .= 'rows="'.$rows.'" ';
			$s .= 'cols="'.$cols.'" ';
			$s .= 'name="'.htmlspecialcharsbx($name).'_X" ';
			$s .= 'id="'.htmlspecialcharsbx($id).'" ';
			$s .= 'style="width: 100%"';
			$s .= '>'.($values=="Y" || $values=="N"?"":htmlspecialcharsbx($values));
			$s .= '</textarea></td>';
			$s .= '<td valign="top" style="padding-left:4px" width="30">';
			$s .= CBPHelper::renderControlSelectorButton($id, $type);
			$s .= '</td></tr></table>';
		}
		elseif ($type == 'datetime')
		{
			$s = '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td valign="top"><textarea ';
			$s .= 'rows="'.$rows.'" ';
			$s .= 'cols="'.$cols.'" ';
			$s .= 'name="'.htmlspecialcharsbx($name).'" ';
			$s .= 'id="'.htmlspecialcharsbx($id).'" ';
			$s .= 'style="width: 100%"';
			$s .= '>'.htmlspecialcharsbx($values);
			$s .= '</textarea></td><td valign="top" style="padding-left:4px" width="20">'.CAdminCalendar::Calendar(htmlspecialcharsbx($name), "", "", true).'</td>';
			$s .= '<td valign="top" style="padding-left:4px" width="30">';
			$s .= CBPHelper::renderControlSelectorButton($id, $type);
			$s .= '</td></tr></table>';
		}
		else
		{
			$s = '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td valign="top"><textarea ';
			$s .= 'rows="'.$rows.'" ';
			$s .= 'cols="'.$cols.'" ';
			if (!empty($arParams['maxlength']))
			{
				$s .= 'maxlength="'.intval($arParams['maxlength']).'" ';
			}
			$s .= 'name="'.htmlspecialcharsbx($name).'" ';
			$s .= 'id="'.htmlspecialcharsbx($id).'" ';
			$s .= 'style="width: 100%"';
			$s .= '>'.htmlspecialcharsbx($values);
			$s .= '</textarea></td>';
			$s .= '<td valign="top" style="padding-left:4px" width="30">';
			$s .= CBPHelper::renderControlSelectorButton($id, $type);
			$s .= '</td></tr></table>';
		}

		return $s;
	}

	public static function _ReplaceTaskURL($str, $documentType)
	{
		$chttp = new CHTTP();
		$baseHref = $chttp->URN2URI('');

		return str_replace(
			Array('#HTTP_HOST#', '#TASK_URL#', '#BASE_HREF#'),
			Array($_SERVER['HTTP_HOST'], ($documentType[0]=="iblock"?"/bitrix/admin/bizproc_task.php?workflow_id={=Workflow:id}":"/company/personal/bizproc/{=Workflow:id}/"), $baseHref),
			$str
			);
	}

	public static function addDefaultWorkflowTemplates($documentType, $additionalModuleId = null)
	{
		if (!empty($additionalModuleId))
		{
			$additionalModuleId = preg_replace("/[^a-z0-9_.]/i", "", $additionalModuleId);
			$arModule = array($additionalModuleId, $documentType[0], 'bizproc');
		}
		else
		{
			$arModule = array($documentType[0], 'bizproc');
		}

		$bIn = false;
		foreach ($arModule as $sModule)
		{
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$sModule.'/templates'))
			{
				if($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$sModule.'/templates'))
				{
					$bIn = true;
					while(false !== ($file = readdir($handle)))
					{
						if(!is_file($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$sModule.'/templates/'.$file))
							continue;
						$arFields = false;
						include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$sModule.'/templates/'.$file);
						if(is_array($arFields))
						{
							/*
							 * If DOCUMENT_TYPE not defined, use current documentType
							 * Overwise check if DOCUMENT_TYPE equals to current documentType
							 */
							if (!array_key_exists("DOCUMENT_TYPE", $arFields))
								$arFields["DOCUMENT_TYPE"] = $documentType;
							elseif($arFields["DOCUMENT_TYPE"] != $documentType)
								continue;

							$arFields["SYSTEM_CODE"] = $file;
							if(is_object($GLOBALS['USER']))
								$arFields["USER_ID"] = $GLOBALS['USER']->GetID();
							$arFields["MODIFIER_USER"] = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
							try
							{
								CBPWorkflowTemplateLoader::Add($arFields);
							}
							catch (Exception $e)
							{
							}
						}
					}
					closedir($handle);
				}
			}
			if ($bIn)
				break;
		}
	}

	/**
	 * Method returns array of workflow templates for specified document type.
	 * Return array example:
	 *    array(
	 *        array(
	 *            "ID" => workflow_id,
	 *            "NAME" => template_name,
	 *            "DESCRIPTION" => template_description,
	 *            "MODIFIED" => modified datetime,
	 *            "USER_ID" => modified by user id,
	 *            "USER_NAME" => modified by user name,
	 *            "AUTO_EXECUTE" => flag CBPDocumentEventType,
	 *            "AUTO_EXECUTE_TEXT" => auto_execute_text,
	 *        ),
	 *        . . .
	 *    )
	 *
	 * @param array $documentType - Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	 * @param bool $showSystemTemplates Shows system templates.
	 * @return array - Templates array.
	 */
	public static function getWorkflowTemplatesForDocumentType($documentType, $showSystemTemplates = true)
	{
		$arResult = [];
		$filter = [
			"DOCUMENT_TYPE" => $documentType,
			"ACTIVE" => "Y",
			'<AUTO_EXECUTE' => CBPDocumentEventType::Automation
		];

		if (!$showSystemTemplates)
		{
			$filter['IS_SYSTEM'] = 'N';
		}

		$dbWorkflowTemplate = CBPWorkflowTemplateLoader::GetList(
			['SORT'=>'ASC','NAME'=>'ASC'],
			$filter,
			false,
			false,
			array("ID", "NAME", "DESCRIPTION", "MODIFIED", "USER_ID", "AUTO_EXECUTE", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "USER_SECOND_NAME", 'PARAMETERS')
		);
		while ($arWorkflowTemplate = $dbWorkflowTemplate->GetNext())
		{
			$arWorkflowTemplate["USER"] = "(".$arWorkflowTemplate["USER_LOGIN"].")".(($arWorkflowTemplate["USER_NAME"] <> '' || $arWorkflowTemplate["USER_LAST_NAME"] <> '') ? " " : "").CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), array("NAME" => $arWorkflowTemplate["USER_NAME"], "LAST_NAME" => $arWorkflowTemplate["USER_LAST_NAME"], "SECOND_NAME" => $arWorkflowTemplate["USER_SECOND_NAME"]), false, false);

			$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] = "";

			if ($arWorkflowTemplate["AUTO_EXECUTE"] == CBPDocumentEventType::None)
				$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] .= GetMessage("BPCGDOC_AUTO_EXECUTE_NONE");

			if (($arWorkflowTemplate["AUTO_EXECUTE"] & CBPDocumentEventType::Create) != 0)
			{
				if ($arWorkflowTemplate["AUTO_EXECUTE_TEXT"] <> '')
					$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] .= ", ";
				$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] .= GetMessage("BPCGDOC_AUTO_EXECUTE_CREATE");
			}

			if (($arWorkflowTemplate["AUTO_EXECUTE"] & CBPDocumentEventType::Edit) != 0)
			{
				if ($arWorkflowTemplate["AUTO_EXECUTE_TEXT"] <> '')
					$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] .= ", ";
				$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] .= GetMessage("BPCGDOC_AUTO_EXECUTE_EDIT");
			}

			if (($arWorkflowTemplate["AUTO_EXECUTE"] & CBPDocumentEventType::Delete) != 0)
			{
				if ($arWorkflowTemplate["AUTO_EXECUTE_TEXT"] <> '')
					$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] .= ", ";
				$arWorkflowTemplate["AUTO_EXECUTE_TEXT"] .= GetMessage("BPCGDOC_AUTO_EXECUTE_DELETE");
			}

			$arWorkflowTemplate['HAS_PARAMETERS'] = count($arWorkflowTemplate['PARAMETERS']) > 0;

			$arResult[] = $arWorkflowTemplate;
		}

		return $arResult;
	}

	public static function getNumberOfWorkflowTemplatesForDocumentType($documentType)
	{
		$n = CBPWorkflowTemplateLoader::GetList(
			array(),
			array("DOCUMENT_TYPE" => $documentType, "ACTIVE"=>"Y"),
			array()
		);
		return $n;
	}

	/**
	 * Method deletes workflow template.
	 *
	 * @param int $id - Template id.
	 * @param array $documentType - Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	 * @param array $arErrors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	 */
	public static function deleteWorkflowTemplate($id, $documentType, &$arErrors)
	{
		$arErrors = array();

		$dbTemplates = CBPWorkflowTemplateLoader::GetList(
			array(),
			array("ID" => $id, "DOCUMENT_TYPE" => $documentType),
			false,
			false,
			array("ID")
		);
		$arTemplate = $dbTemplates->Fetch();
		if (!$arTemplate)
		{
			$arErrors[] = array(
				"code" => 0,
				"message" => str_replace("#ID#", $id, GetMessage("BPCGDOC_INVALID_WF_ID")),
				"file" => ""
			);
			return;
		}

		try
		{
			CBPWorkflowTemplateLoader::Delete($id);
		}
		catch (Exception $e)
		{
			$arErrors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]"
			);
		}
	}

	/**
	 * Method updates workflow template.
	 *
	 * @param int $id - Template id.
	 * @param array $documentType - Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	 * @param array $arFields - Data for update.
	 * @param array $arErrors - Errors array(array("code" => error_code, "message" => message, "file" => file_path), ...).
	 */
	public static function updateWorkflowTemplate($id, $documentType, $arFields, &$arErrors)
	{
		$arErrors = array();

		$dbTemplates = CBPWorkflowTemplateLoader::GetList(
			array(),
			array("ID" => $id, "DOCUMENT_TYPE" => $documentType),
			false,
			false,
			array("ID")
		);
		$arTemplate = $dbTemplates->Fetch();
		if (!$arTemplate)
		{
			$arErrors[] = array(
				"code" => 0,
				"message" => str_replace("#ID#", $id, GetMessage("BPCGDOC_INVALID_WF_ID")),
				"file" => ""
			);
			return;
		}

		try
		{
			CBPWorkflowTemplateLoader::Update($id, $arFields);
		}
		catch (Exception $e)
		{
			$arErrors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]"
			);
		}
	}

	/**
	 * Method checks can user operate specified document with specified operation.
	 *
	 * @param int $operation - operation CBPCanUserOperateOperation.
	 * @param int $userId - User id.
	 * @param array $parameterDocumentId - Document id array(MODULE_ID, ENTITY, DOCUMENT_ID).
	 * @param array $arParameters - Additional parameters.
	 * @return bool
	 */
	public static function canUserOperateDocument($operation, $userId, $parameterDocumentId, $arParameters = array())
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId <> '')
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "CanUserOperateDocument"), array($operation, $userId, $documentId, $arParameters));

		return false;
	}

	/**
	 * Method checks can user operate specified document type with specified operation.
	 *
	 * @param int $operation - operation CBPCanUserOperateOperation.
	 * @param int $userId - User id.
	 * @param array $parameterDocumentType - Document type array(MODULE_ID, ENTITY, DOCUMENT_TYPE).
	 * @param array $arParameters - Additional parameters.
	 * @return bool
	 */
	public static function canUserOperateDocumentType($operation, $userId, $parameterDocumentType, $arParameters = array())
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId <> '')
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "CanUserOperateDocumentType"), array($operation, $userId, $documentType, $arParameters));

		return false;
	}

	/**
	 * Get document admin page URL.
	 *
	 * @param array $parameterDocumentId - Document id array(MODULE_ID, ENTITY, DOCUMENT_ID).
	 * @return string - URL.
	 */
	public static function getDocumentAdminPage($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId <> '')
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetDocumentAdminPage"), array($documentId));

		return "";
	}

	/**
	 * @param array $parameterDocumentId Document Id.
	 * @return mixed|string
	 * @throws CBPArgumentNullException
	 */
	public static function getDocumentName($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId <> '')
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, 'getDocumentName'))
			return call_user_func_array(array($entity, "getDocumentName"), array($documentId));

		return "";
	}

	/**
	 * Method returns task array for specified user and specified workflow state.
	 * Return array example:
	 *	array(
	 *		array(
	 *			"ID" => task_id,
	 *			"NAME" => task_name,
	 *			"DESCRIPTION" => task_description,
	 *		),
	 *		. . .
	 *	)
	 *
	 * @param int $userId - User id.
	 * @param string $workflowId - Workflow id.
	 * @return array - Tasks.
	 */
	public static function getUserTasksForWorkflow($userId, $workflowId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
			return array();

		$workflowId = trim($workflowId);
		if ($workflowId == '')
			return array();

		$arResult = array();

		$dbTask = CBPTaskService::GetList(
			array(),
			array("WORKFLOW_ID" => $workflowId, "USER_ID" => $userId, 'STATUS' => CBPTaskStatus::Running),
			false,
			false,
			array("ID", "WORKFLOW_ID", "NAME", "DESCRIPTION")
		);
		while ($arTask = $dbTask->GetNext())
			$arResult[] = $arTask;

		return $arResult;
	}

	public static function prepareFileForHistory($documentId, $fileId, $historyIndex)
	{
		return CBPHistoryService::PrepareFileForHistory($documentId, $fileId, $historyIndex);
	}

	public static function isAdmin()
	{
		global $APPLICATION;
		return ($APPLICATION->GetGroupRight("bizproc") >= "W");
	}

	public static function getDocumentFromHistory($historyId, &$arErrors)
	{
		$arErrors = array();

		try
		{
			$historyId = intval($historyId);
			if ($historyId <= 0)
				throw new CBPArgumentNullException("historyId");

			return CBPHistoryService::GetById($historyId);
		}
		catch (Exception $e)
		{
			$arErrors[] = array(
				"code" => $e->getCode(),
				"message" => $e->getMessage(),
				"file" => $e->getFile()." [".$e->getLine()."]"
			);
		}
		return null;
	}

	public static function getAllowableUserGroups($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId <> '')
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
		{
			$result = call_user_func_array(array($entity, "GetAllowableUserGroups"), array($documentType));
			$result1 = array();
			foreach ($result as $key => $value)
				$result1[mb_strtolower($key)] = $value;
			return $result1;
		}

		return array();
	}

	public static function onAfterTMDayStart($data)
	{
		global $DB;

		if (!CModule::IncludeModule("im"))
			return;

		$userId = (int) $data['USER_ID'];

		$iterator = Bizproc\Workflow\Entity\WorkflowInstanceTable::getList(
			[
				'select' => [new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(\'x\')')],
				'filter' => [
					'=STARTED_BY' => $userId,
					'<OWNED_UNTIL' => date(
							$DB->DateFormatToPHP(FORMAT_DATETIME),
							time() - Bizproc\Workflow\Entity\WorkflowInstanceTable::LOCKED_TIME_INTERVAL
					),
				],
			]
		);
		$row = $iterator->fetch();
		if (!empty($row['CNT']))
		{
			$path = IsModuleInstalled('bitrix24') ? '/bizproc/bizproc/?type=is_locked'
				: Main\Config\Option::get("bizproc", "locked_wi_path", '/services/bp/instances.php?type=is_locked');

			CIMNotify::Add(array(
				'FROM_USER_ID' => 0,
				'TO_USER_ID' => $userId,
				"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
				"NOTIFY_MODULE" => "bizproc",
				"NOTIFY_EVENT" => "wi_locked",
				'TITLE' => GetMessage('BPCGDOC_WI_LOCKED_NOTICE_TITLE'),
				'MESSAGE' => 	GetMessage('BPCGDOC_WI_LOCKED_NOTICE_MESSAGE', array(
					'#PATH#' => $path,
					'#CNT#' => $row['CNT']
				))
			));
		}
	}

	/**
	 * Temporary notification for B24 portal Admins
	 * Ex: CAgent::AddAgent("\CBPDocument::sendB24LimitsNotifyToAdmins();", "bizproc", "N", 43200);
	 * @param int $ts
	 * @return string
	 */
	public static function sendB24LimitsNotifyToAdmins($ts = 0)
	{
		if (time() > strtotime('2017-05-24'))
			return '';
		if ($ts > 0 && $ts > time())
			return '\CBPDocument::sendB24LimitsNotifyToAdmins('.(int)$ts.');';

		if (!CModule::IncludeModule('bitrix24') || !CModule::IncludeModule("im"))
			return '';

		$userIds = \CBitrix24::getAllAdminId();
		if (!$userIds)
			return '';

		global $DB;
		$dbResult = $DB->Query('SELECT COUNT(WS.ID) CNT
			FROM b_bp_workflow_state WS
				INNER JOIN b_bp_workflow_instance WI ON (WS.ID = WI.ID)
				INNER JOIN b_bp_workflow_template WT ON (WS.WORKFLOW_TEMPLATE_ID = WT.ID)
			WHERE WT.AUTO_EXECUTE <> '.(int)CBPDocumentEventType::Automation.'
			GROUP BY WS.MODULE_ID, WS.ENTITY, WS.DOCUMENT_ID
				HAVING CNT > 2
			LIMIT 1');

		$result = $dbResult->Fetch();

		if (!empty($result))
		{
			foreach ($userIds as $userId)
			{
				CIMNotify::Add(array(
					'FROM_USER_ID' => 0,
					'TO_USER_ID' => $userId,
					'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
					'NOTIFY_MODULE' => 'bizproc',
					'NOTIFY_EVENT' => 'wi_limits',
					'TITLE' => GetMessage('BPCGDOC_WI_LOCKED_NOTICE_TITLE'),
					'MESSAGE' => GetMessage('BPCGDOC_WI_B24_LIMITS_MESSAGE')
				));
			}
		}

		$days = 3600*24*3;
		return '\CBPDocument::sendB24LimitsNotifyToAdmins('.(time() + $days).');';
	}

	/**
	 * Method returns map of document fields aliases.
	 * @param array $fields Document fields.
	 * @return array Aliases.
	 */
	public static function getDocumentFieldsAliasesMap($fields)
	{
		if (empty($fields) || !is_array($fields))
		{
			return [];
		}

		$aliases = [];
		foreach ($fields as $key => $property)
		{
			if (isset($property['Alias']))
			{
				$aliases[$property['Alias']] = $key;
			}
		}
		return $aliases;
	}

	/**
	 * Bizproc expression checker. Required for usage from external modules!
	 * Examples: {=Document:IBLOCK_ID}, {=Document:CREATED_BY>printable}, {=SequentialWorkflowActivity1:DocumentApprovers>user,printable}
	 * @param $value
	 * @return bool
	 */
	public static function isExpression($value)
	{
		//go to internal alias
		return CBPActivity::isExpression($value);
	}

	public static function parseExpression($expression)
	{
		$matches = null;
		if (is_string($expression) && preg_match(CBPActivity::ValuePattern, $expression, $matches))
		{
			$result = array(
				'object' => $matches['object'],
				'field' => $matches['field'],
				'modifiers' => array()
			);
			if (!empty($matches['mod1']))
				$result['modifiers'][] = $matches['mod1'];
			if (!empty($matches['mod2']))
				$result['modifiers'][] = $matches['mod2'];

			return $result;
		}
		return false;
	}

	public static function signParameters(array $parameters)
	{
		return self::signArray($parameters, 'bizproc_wf_params');
	}

	/**
	 * @param string $unsignedData
	 * @return array
	 */
	public static function unSignParameters($unsignedData)
	{
		return self::unSignArray($unsignedData, 'bizproc_wf_params');
	}

	public static function signDocumentType(array $documentType)
	{
		return self::signArray($documentType, 'bizproc_document_type');
	}

	/**
	 * @param string $unsignedData
	 * @return array|null Document type.
	 */
	public static function unSignDocumentType($unsignedData): ?array
	{
		$dt =  self::unSignArray($unsignedData, 'bizproc_document_type');
		return $dt ?: null;
	}

	private static function signArray(array $source, $salt)
	{
		$signer = new Main\Security\Sign\Signer;
		$jsonData = Main\Web\Json::encode($source);

		return $signer->sign($jsonData, $salt);
	}

	private static function unSignArray(string $unsignedSource, $salt)
	{
		$signer = new Main\Security\Sign\Signer;

		try
		{
			$unsigned = $signer->unsign($unsignedSource, $salt);
			$result = Main\Web\Json::decode($unsigned);
		}
		catch (\Exception $e)
		{
			$result = [];
		}

		return $result;
	}

	public static function getTemplatesForStart($userId, $documentType, $documentId = null, array $parameters = array())
	{
		if (!isset($parameters['UserGroups']))
		{
			$parameters['UserGroups'] = CUser::GetUserGroup($userId);
		}
		if (!isset($parameters['DocumentStates']))
		{
			$parameters['DocumentStates'] = static::GetDocumentStates($documentType, $documentId);
		}
		$op = CBPCanUserOperateOperation::StartWorkflow;

		$templates = array();
		$dbWorkflowTemplate = CBPWorkflowTemplateLoader::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			array(
				"DOCUMENT_TYPE" => $documentType,
				"ACTIVE" => "Y",
				"IS_SYSTEM" => "N",
				'<AUTO_EXECUTE' => CBPDocumentEventType::Automation
			),
			false,
			false,
			array("ID", "NAME", "DESCRIPTION", "PARAMETERS")
		);
		while ($arWorkflowTemplate = $dbWorkflowTemplate->fetch())
		{
			$parameters['WorkflowTemplateId'] = $arWorkflowTemplate['ID'];
			if ($documentId)
			{
				if (!CBPDocument::CanUserOperateDocument($op, $userId, $documentId, $parameters))
				{
					continue;
				}
			}
			elseif (!CBPDocument::CanUserOperateDocumentType($op, $userId, $documentType, $parameters))
			{
				continue;
			}

			$templates[] = array(
				'id' => $arWorkflowTemplate['ID'],
				'name' => $arWorkflowTemplate['NAME'],
				'description' => $arWorkflowTemplate['DESCRIPTION'],
				'hasParameters' => count($arWorkflowTemplate['PARAMETERS']) > 0,
				'isConstantsTuned' => CBPWorkflowTemplateLoader::isConstantsTuned($arWorkflowTemplate["ID"])
			);
		}

		return $templates;
	}
}
