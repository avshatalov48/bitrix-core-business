<?
namespace Bitrix\Bizproc;

use \Bitrix\Main\Loader;
use \Bitrix\Rest\AppLangTable;
use \Bitrix\Rest\AppTable;
use \Bitrix\Rest\RestException;
use \Bitrix\Rest\AccessException;

Loader::includeModule('rest');

class RestService extends \IRestService
{
	const SCOPE = 'bizproc';
	protected static $app;
	private static $allowedOperations = array('', '!', '<', '<=', '>', '>=');//, '><', '!><', '?', '=', '!=', '%', '!%', ''); May be later?

	const ERROR_UNSUPPORTED_PROTOCOL = 'ERROR_UNSUPPORTED_PROTOCOL';
	const ERROR_WRONG_HANDLER_URL = 'ERROR_WRONG_HANDLER_URL';
	const ERROR_HANDLER_URL_MATCH = 'ERROR_HANDLER_URL_MATCH';

	const ERROR_ACTIVITY_ALREADY_INSTALLED = 'ERROR_ACTIVITY_ALREADY_INSTALLED';
	const ERROR_ACTIVITY_ADD_FAILURE = 'ERROR_ACTIVITY_ADD_FAILURE';
	const ERROR_ACTIVITY_VALIDATION_FAILURE = 'ERROR_ACTIVITY_VALIDATION_FAILURE';
	const ERROR_ACTIVITY_NOT_FOUND = 'ERROR_ACTIVITY_NOT_FOUND';
	const ERROR_EMPTY_LOG_MESSAGE = 'ERROR_EMPTY_LOG_MESSAGE';
	const ERROR_WRONG_WORKFLOW_ID = 'ERROR_WRONG_WORKFLOW_ID';
	const ERROR_WRONG_ACTIVITY_NAME = 'ERROR_WRONG_ACTIVITY_NAME';

	const ERROR_TASK_VALIDATION = 'ERROR_TASK_VALIDATION';
	const ERROR_TASK_NOT_FOUND = 'ERROR_TASK_NOT_FOUND';
	const ERROR_TASK_TYPE = 'ERROR_TASK_TYPE';
	const ERROR_TASK_COMPLETED = 'ERROR_TASK_COMPLETED';
	const ERROR_TASK_EXECUTION = 'ERROR_TASK_EXECUTION';

	public static function onRestServiceBuildDescription()
	{
		$map = array();
		if (\CBPRuntime::isFeatureEnabled())
		{
			$map = array(

				//activity
				'bizproc.activity.add' => array(__CLASS__, 'addActivity'),
				'bizproc.activity.delete' => array(__CLASS__, 'deleteActivity'),
				'bizproc.activity.log' => array(__CLASS__, 'writeActivityLog'),
				'bizproc.activity.list' => array(__CLASS__, 'getActivityList'),

				//event
				'bizproc.event.send' => array(__CLASS__, 'sendEvent'),

				//task
				'bizproc.task.list' =>  array(__CLASS__, 'getTaskList'),
				'bizproc.task.complete' =>  array(__CLASS__, 'completeTask'),

				//workflow
				'bizproc.workflow.terminate' => array(__CLASS__, 'terminateWorkflow'),
				'bizproc.workflow.start' => array(__CLASS__, 'startWorkflow'),
				//workflow.instance
				'bizproc.workflow.instance.list' => array(__CLASS__, 'getWorkflowInstances'),
				//workflow.template
				'bizproc.workflow.template.list' => array(__CLASS__, 'getWorkflowTemplates'),

				//aliases
				'bizproc.workflow.instances' => array(__CLASS__, 'getWorkflowInstances'),
			);
		}

		if (\CBPRuntime::isFeatureEnabled()
			|| \CBPRuntime::isFeatureEnabled('crm_automation_lead')
			|| \CBPRuntime::isFeatureEnabled('crm_automation_deal')
		)
		{
			$map = array_merge($map, array(

				//robot
				'bizproc.robot.add' => array(__CLASS__, 'addRobot'),
				'bizproc.robot.delete' => array(__CLASS__, 'deleteRobot'),
				'bizproc.robot.list' => array(__CLASS__, 'getRobotList'),

				//provider
				'bizproc.provider.add' => array(__CLASS__, 'addProvider'),
				'bizproc.provider.delete' => array(__CLASS__, 'deleteProvider'),
				'bizproc.provider.list' => array(__CLASS__, 'getProviderList'),
			));
		}

		return $map ? array(static::SCOPE => $map) : false;
	}

	/**
	 * Deletes application activities.
	 * @param array $fields Fields describes application.
	 * @return void
	 */
	public static function onRestAppDelete(array $fields)
	{
		$fields = array_change_key_case($fields, CASE_UPPER);
		if (empty($fields['APP_ID']))
			return;

		if (!Loader::includeModule('rest'))
			return;

		$dbRes = AppTable::getById($fields['APP_ID']);
		$app = $dbRes->fetch();

		if(!$app)
			return;

		$iterator = RestActivityTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=APP_ID' => $app['CLIENT_ID'])
		));

		while ($activity = $iterator->fetch())
		{
			RestActivityTable::delete($activity['ID']);
		}

		$iterator = RestProviderTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=APP_ID' => $app['CLIENT_ID'])
		));

		while ($activity = $iterator->fetch())
		{
			RestProviderTable::delete($activity['ID']);
		}
	}

	/**
	 * Deletes application activities.
	 * @param array $fields Fields describes application.
	 * @return void
	 */
	public static function onRestAppUpdate(array $fields)
	{
		static::onRestAppDelete($fields);
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function addActivity($params, $n, $server)
	{
		return self::addActivityInternal($params, $server, false);
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function addRobot($params, $n, $server)
	{
		return self::addActivityInternal($params, $server, true);
	}

	/**
	 * @param array $params
	 * @param  \CRestServer $server
	 * @param bool $isRobot
	 * @return bool
	 * @throws AccessException
	 * @throws RestException
	 */
	private static function addActivityInternal($params, $server, $isRobot = false)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$params = self::prepareActivityData($params);

		if ($isRobot)
			self::validateRobot($params, $server);
		else
			self::validateActivity($params, $server);

		$params['APP_ID'] = $server->getClientId();
		$params['INTERNAL_CODE'] = self::generateInternalCode($params);
		$params['APP_NAME'] = self::getAppName($params['APP_ID']);

		$iterator = RestActivityTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=INTERNAL_CODE' => $params['INTERNAL_CODE'])
		));
		$result = $iterator->fetch();
		if ($result)
		{
			throw new RestException('Activity or Robot already installed!', self::ERROR_ACTIVITY_ALREADY_INSTALLED);
		}

		$params['AUTH_USER_ID'] = isset($params['AUTH_USER_ID'])? (int) $params['AUTH_USER_ID'] : 0;
		$params['IS_ROBOT'] = $isRobot ? 'Y' : 'N';

		if ($isRobot)
			$params['USE_SUBSCRIPTION'] = 'N';

		$result = RestActivityTable::add($params);

		if ($result->getErrors())
			throw new RestException('Activity save error!', self::ERROR_ACTIVITY_ADD_FAILURE);

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function deleteActivity($params, $n, $server)
	{
		return self::deleteActivityInternal($params, $server, false);
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function deleteRobot($params, $n, $server)
	{
		return self::deleteActivityInternal($params, $server, true);
	}

	/**
	 * @param array $params
	 * @param \CRestServer $server
	 * @param bool $isRobot
	 * @return bool
	 * @throws AccessException
	 * @throws RestException
	 */
	private static function deleteActivityInternal($params, $server, $isRobot = false)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$params = array_change_key_case($params, CASE_UPPER);
		self::checkAdminPermissions();
		self::validateActivityCode($params['CODE']);
		$params['APP_ID'] = $server->getClientId();
		$internalCode = self::generateInternalCode($params);

		$iterator = RestActivityTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=INTERNAL_CODE' => $internalCode,
				'=IS_ROBOT' => $isRobot ? 'Y' : 'N'
			)
		));
		$result = $iterator->fetch();
		if (!$result)
		{
			throw new RestException('Activity or Robot not found!', self::ERROR_ACTIVITY_NOT_FOUND);
		}
		RestActivityTable::delete($result['ID']);

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws AccessException
	 * @throws RestException
	 */
	public static function sendEvent($params, $n, $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);
		list($workflowId, $activityName, $eventId) = self::extractEventToken($params['EVENT_TOKEN']);

		\CBPRuntime::sendExternalEvent(
			$workflowId,
			$activityName,
			array(
				'EVENT_ID' => $eventId,
				'RETURN_VALUES' => isset($params['RETURN_VALUES']) ? $params['RETURN_VALUES'] : array(),
				'LOG_MESSAGE' => isset($params['LOG_MESSAGE']) ? $params['LOG_MESSAGE'] : '',
			)
		);

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws AccessException
	 * @throws RestException
	 */
	public static function writeActivityLog($params, $n, $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);
		list($workflowId, $activityName, $eventId) = self::extractEventToken($params['EVENT_TOKEN']);

		$logMessage = isset($params['LOG_MESSAGE']) ? $params['LOG_MESSAGE'] : '';

		if (empty($logMessage))
			throw new RestException('Empty log message!', self::ERROR_EMPTY_LOG_MESSAGE);

		\CBPRuntime::sendExternalEvent(
			$workflowId,
			$activityName,
			array(
				'EVENT_ID' => $eventId,
				'LOG_ACTION' => true,
				'LOG_MESSAGE' => $logMessage
			)
		);

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return array
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getActivityList($params, $n, $server)
	{
		return self::getActivityListInternal($params, $server, false);
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return array
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getRobotList($params, $n, $server)
	{
		return self::getActivityListInternal($params, $server, true);
	}

	/**
	 * @param array $params
	 * @param \CRestServer $server
	 * @param bool $isRobot
	 * @return array
	 * @throws AccessException
	 */
	private static function getActivityListInternal($params, $server, $isRobot = false)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$iterator = RestActivityTable::getList(array(
			'select' => array('CODE'),
			'filter' => array(
				'=APP_ID' => $server->getClientId(),
				'=IS_ROBOT' => $isRobot ? 'Y' : 'N'
			)
		));

		$result = array();
		while ($row = $iterator->fetch())
		{
			$result[] = $row['CODE'];
		}
		return $result;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return array
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getWorkflowInstances($params, $n, $server)
	{
		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		$fields = array(
			'ID' => 'ID',
			'MODIFIED' => 'MODIFIED',
			'OWNED_UNTIL' => 'OWNED_UNTIL',
			'MODULE_ID' => 'STATE.MODULE_ID',
			'ENTITY' => 'STATE.ENTITY',
			'DOCUMENT_ID' => 'STATE.DOCUMENT_ID',
			'STARTED' => 'STATE.STARTED',
			'STARTED_BY' => 'STATE.STARTED_BY',
			'TEMPLATE_ID' => 'STATE.WORKFLOW_TEMPLATE_ID',
		);

		$select = static::getSelect($params['SELECT'], $fields, array('ID', 'MODIFIED', 'OWNED_UNTIL'));
		$filter = static::getFilter($params['FILTER'], $fields, array('MODIFIED', 'OWNED_UNTIL'));
		$order = static::getOrder($params['ORDER'], $fields, array('MODIFIED' => 'DESC'));

		$iterator = WorkflowInstanceTable::getList(array(
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'limit' => static::LIST_LIMIT,
			'offset' => (int) $n,
			'count_total' => true,
		));

		$result = array();
		while ($row = $iterator->fetch())
		{
			if (isset($row['MODIFIED']))
				$row['MODIFIED'] = \CRestUtil::convertDateTime($row['MODIFIED']);
			if (isset($row['STARTED']))
				$row['STARTED'] = \CRestUtil::convertDateTime($row['STARTED']);
			if (isset($row['OWNED_UNTIL']))
				$row['OWNED_UNTIL'] = \CRestUtil::convertDateTime($row['OWNED_UNTIL']);
			$result[] = $row;
		}

		return static::setNavData($result, ['count' => $iterator->getCount(), 'offset' => $n]);
	}

	public static function terminateWorkflow($params, $n, $server)
	{
		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		if (empty($params['ID']))
		{
			throw new RestException('Empty workflow instance ID', self::ERROR_WRONG_WORKFLOW_ID);
		}

		$id = $params['ID'];
		$status = isset($params['STATUS']) ? (string)$params['STATUS'] : '';
		$errors = [];

		if (!\CBPDocument::terminateWorkflow($id, [], $errors, $status))
		{
			throw new RestException($errors[0]['message']);
		}

		return true;
	}

	public static function startWorkflow($params, $n, $server)
	{
		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		if (empty($params['TEMPLATE_ID']))
		{
			throw new RestException('Empty TEMPLATE_ID', self::ERROR_WRONG_WORKFLOW_ID);
		}

		$documentId = self::validateDocumentId($params['DOCUMENT_ID']);

		$templateId = (int)$params['TEMPLATE_ID'];
		$workflowParameters = isset($params['PARAMETERS']) && is_array($params['PARAMETERS']) ? $params['PARAMETERS'] : [];

		$workflowParameters[\CBPDocument::PARAM_TAGRET_USER] = self::getCurrentUserId();

		$errors = [];
		$workflowId = \CBPDocument::startWorkflow($templateId, $documentId, $workflowParameters, $errors);

		if (!$workflowId)
		{
			throw new RestException($errors[0]['message']);
		}

		return $workflowId;
	}

	public static function getWorkflowTemplates($params, $n, $server)
	{
		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		$fields = array(
			'ID' => 'ID',
			'MODULE_ID' => 'MODULE_ID',
			'ENTITY' => 'ENTITY',
			'DOCUMENT_TYPE' => 'DOCUMENT_TYPE',
			'AUTO_EXECUTE' => 'AUTO_EXECUTE',
			'NAME' => 'NAME',
			'DESCRIPTION' => 'DESCRIPTION',
			'TEMPLATE' => 'TEMPLATE',
			'PARAMETERS' => 'PARAMETERS',
			'VARIABLES' => 'VARIABLES',
			'CONSTANTS' => 'CONSTANTS',
			'MODIFIED' => 'MODIFIED',
			'IS_MODIFIED' => 'IS_MODIFIED',
			'USER_ID' => 'USER_ID',
			'SYSTEM_CODE' => 'SYSTEM_CODE',
		);

		$select = static::getSelect($params['SELECT'], $fields, array('ID'));
		$filter = static::getFilter($params['FILTER'], $fields, array('MODIFIED'));
		$filter['!AUTO_EXECUTE'] = \CBPDocumentEventType::Automation;

		$order = static::getOrder($params['ORDER'], $fields, array('ID' => 'ASC'));

		$iterator = WorkflowTemplateTable::getList(array(
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
			'limit' => static::LIST_LIMIT,
			'offset' => (int) $n,
			'count_total' => true,
		));

		$countTotal = $iterator->getCount();

		$iterator = new \CBPWorkflowTemplateResult($iterator, \CBPWorkflowTemplateLoader::useGZipCompression());

		$result = array();
		while ($row = $iterator->fetch())
		{
			if (isset($row['MODIFIED']))
				$row['MODIFIED'] = \CRestUtil::convertDateTime($row['MODIFIED']);
			if (isset($row['STARTED']))
				$row['STARTED'] = \CRestUtil::convertDateTime($row['STARTED']);
			if (isset($row['OWNED_UNTIL']))
				$row['OWNED_UNTIL'] = \CRestUtil::convertDateTime($row['OWNED_UNTIL']);
			$result[] = $row;
		}

		return static::setNavData($result, ['count' => $countTotal, 'offset' => $n]);
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return array
	 * @throws AccessException
	 */
	public static function getTaskList($params, $n, $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$fields = array(
			'ID' => 'ID',
			'ACTIVITY' => 'ACTIVITY',
			'WORKFLOW_ID' => 'WORKFLOW_ID',
			'DOCUMENT_NAME' => 'DOCUMENT_NAME',
			'DESCRIPTION' => 'DESCRIPTION',
			'NAME' => 'NAME',
			'MODIFIED' => 'MODIFIED',
			'WORKFLOW_STARTED' => 'WORKFLOW_STARTED',
			'WORKFLOW_STARTED_BY' => 'WORKFLOW_STARTED_BY',
			'OVERDUE_DATE' => 'OVERDUE_DATE',
			'WORKFLOW_TEMPLATE_ID' => 'WORKFLOW_TEMPLATE_ID',
			'WORKFLOW_TEMPLATE_NAME' => 'WORKFLOW_TEMPLATE_NAME',
			'WORKFLOW_STATE' => 'WORKFLOW_STATE',
			'STATUS' => 'STATUS',
			'USER_ID' => 'USER_ID',
			'USER_STATUS' => 'USER_STATUS',
			'MODULE_ID' => 'MODULE_ID',
			'ENTITY' => 'ENTITY',
			'DOCUMENT_ID' => 'DOCUMENT_ID',
			'PARAMETERS' => 'PARAMETERS',
		);

		$select = static::getSelect($params['SELECT'], $fields, array('ID', 'WORKFLOW_ID', 'DOCUMENT_NAME', 'NAME'));
		$select = array_merge(array('MODULE', 'ENTITY', 'DOCUMENT_ID'), $select);
		$filter = static::getFilter($params['FILTER'], $fields, array('MODIFIED', 'WORKFLOW_STARTED', 'OVERDUE_DATE'));
		$order = static::getOrder($params['ORDER'], $fields, array('ID' => 'DESC'));

		$currentUserId = self::getCurrentUserId();
		$isAdmin = static::isAdmin();

		if (!$isAdmin && !isset($filter['USER_ID']))
		{
			$filter['USER_ID'] = $currentUserId;
		}

		$targetUserId = isset($filter['USER_ID'])? (int)$filter['USER_ID'] : 0;
		if ($targetUserId !== $currentUserId && !\CBPHelper::checkUserSubordination($currentUserId, $targetUserId))
		{
			self::checkAdminPermissions();
		}

		$iterator = \CBPTaskService::getList(
			$order,
			$filter,
			false,
			static::getNavData($n),
			$select
		);

		$result = array();
		while ($row = $iterator->fetch())
		{
			if (isset($row['MODIFIED']))
				$row['MODIFIED'] = \CRestUtil::convertDateTime($row['MODIFIED']);
			if (isset($row['WORKFLOW_STARTED']))
				$row['WORKFLOW_STARTED'] = \CRestUtil::convertDateTime($row['WORKFLOW_STARTED']);
			if (isset($row['OVERDUE_DATE']))
				$row['OVERDUE_DATE'] = \CRestUtil::convertDateTime($row['OVERDUE_DATE']);
			$row['DOCUMENT_URL'] = \CBPDocument::getDocumentAdminPage(array(
				$row['MODULE_ID'], $row['ENTITY'], $row['DOCUMENT_ID']
			));

			if (isset($row['PARAMETERS']))
			{
				$row['PARAMETERS'] = static::filterTaskParameters($row['PARAMETERS']);
			}

			$result[] = $row;
		}

		return static::setNavData($result, $iterator);
	}

	private static function filterTaskParameters(array $parameters)
	{
		$whiteList = array(
			array('CommentLabelMessage', 'CommentLabel'),
			'CommentRequired', 'ShowComment',
			array('TaskButtonMessage', 'StatusOkLabel'),
			array('TaskButton1Message', 'StatusYesLabel'),
			array('TaskButton2Message', 'StatusNoLabel'),
			array('TaskButtonCancelMessage', 'StatusCancelLabel'),
		);

		$filtered = array();

		foreach ($whiteList as $whiteKey)
		{
			$filterKey = $whiteKey;
			if (is_array($whiteKey))
			{
				$filterKey = $whiteKey[1];
				$whiteKey = $whiteKey[0];
			}
			if (isset($parameters[$whiteKey]))
			{
				$filtered[$filterKey] = $parameters[$whiteKey];
			}
		}

		return $filtered;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws RestException
	 */
	public static function completeTask($params, $n, $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);
		self::validateTaskParameters($params);

		$userId = self::getCurrentUserId();
		$task = static::getTask($params['TASK_ID'], $userId);

		if ($task['ACTIVITY'] !== 'ReviewActivity' && $task['ACTIVITY'] !== 'ApproveActivity')
		{
			throw new RestException('Incorrect task type', self::ERROR_TASK_TYPE);
		}

		$errors = array();
		$request = array(
			'INLINE_USER_STATUS' => \CBPTaskUserStatus::resolveStatus($params['STATUS']),
			'task_comment' => !empty($params['COMMENT']) && is_string($params['COMMENT']) ? $params['COMMENT'] : null
		);

		if (!\CBPDocument::postTaskForm($task, $userId, $request, $errors))
		{
			throw new RestException($errors[0]["message"], self::ERROR_TASK_EXECUTION);
		}

		return true;
	}

	private static function validateTaskParameters(array $params)
	{
		if (empty($params['TASK_ID']))
		{
			throw new RestException('empty TASK_ID', self::ERROR_TASK_VALIDATION);
		}
		if (empty($params['STATUS']) || \CBPTaskUserStatus::resolveStatus($params['STATUS']) === null)
		{
			throw new RestException('incorrect STATUS', self::ERROR_TASK_VALIDATION);
		}
	}

	private static function getTask($id, $userId)
	{
		$dbTask = \CBPTaskService::getList(
			array(),
			array("ID" => (int)$id, "USER_ID" => $userId),
			false,
			false,
			array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS", "USER_STATUS")
		);
		$task = $dbTask->fetch();

		if (!$task)
		{
			throw new RestException('Task not found', self::ERROR_TASK_NOT_FOUND);
		}
		elseif ((int)$task['USER_STATUS'] !== \CBPTaskUserStatus::Waiting)
		{
			throw new RestException('Task already completed', self::ERROR_TASK_COMPLETED);
		}

		if ($task)
		{
			$task["PARAMETERS"]["DOCUMENT_ID"] = \CBPStateService::getStateDocumentId($task['WORKFLOW_ID']);
			$task["MODULE_ID"] = $task["PARAMETERS"]["DOCUMENT_ID"][0];
			$task["ENTITY"] = $task["PARAMETERS"]["DOCUMENT_ID"][1];
			$task["DOCUMENT_ID"] = $task["PARAMETERS"]["DOCUMENT_ID"][2];
		}

		return $task;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function addProvider($params, $n, $server)
	{
		if (Loader::includeModule('messageservice'))
		{
			return \Bitrix\MessageService\RestService::addSender($params, $n, $server);
		}

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$params = self::prepareActivityData($params);

		self::validateProvider($params, $server);

		$params['APP_ID'] = $server->getClientId();
		$params['APP_NAME'] = self::getAppName($params['APP_ID']);

		$iterator = RestProviderTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=APP_ID' => $params['APP_ID'],
				'=CODE' => $params['CODE']
			)
		));
		$result = $iterator->fetch();
		if ($result)
		{
			throw new RestException('Provider already installed!', self::ERROR_ACTIVITY_ALREADY_INSTALLED);
		}

		$result = RestProviderTable::add($params);

		if ($result->getErrors())
			throw new RestException('Activity save error!', self::ERROR_ACTIVITY_ADD_FAILURE);

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function deleteProvider($params, $n, $server)
	{
		if (Loader::includeModule('messageservice'))
		{
			return \Bitrix\MessageService\RestService::deleteSender($params, $n, $server);
		}

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$params = array_change_key_case($params, CASE_UPPER);
		self::checkAdminPermissions();
		self::validateActivityCode($params['CODE']);
		$params['APP_ID'] = $server->getClientId();

		$iterator = RestProviderTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=APP_ID' => $params['APP_ID'],
				'=CODE' => $params['CODE']
			)
		));
		$result = $iterator->fetch();
		if (!$result)
		{
			throw new RestException('Provider not found!', self::ERROR_ACTIVITY_NOT_FOUND);
		}
		RestProviderTable::delete($result['ID']);

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return array
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getProviderList($params, $n, $server)
	{
		if (Loader::includeModule('messageservice'))
		{
			return \Bitrix\MessageService\RestService::getSenderList($params, $n, $server);
		}

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$iterator = RestProviderTable::getList(array(
			'select' => array('CODE'),
			'filter' => array(
				'=APP_ID' => $server->getClientId()
			)
		));

		$result = array();
		while ($row = $iterator->fetch())
		{
			$result[] = $row['CODE'];
		}
		return $result;
	}

	private static function getSelect($rules, $fields, $default = array())
	{
		$select = array();
		if (!empty($rules) && is_array($rules))
		{
			foreach ($rules as $field)
			{
				$field = strtoupper($field);
				if (isset($fields[$field]) && !in_array($field, $select))
					$select[$field] = $fields[$field];
			}
		}

		return $select ? $select : $default;
	}

	private static function getOrder($rules, $fields, array $default = array())
	{
		$order = array();
		if (!empty($rules) && is_array($rules))
		{
			foreach ($rules as $field => $ordering)
			{
				$field = strtoupper($field);
				$ordering = strtoupper($ordering);
				if (isset($fields[$field]))
					$order[$fields[$field]] = $ordering == 'DESC' ? 'DESC' : 'ASC';
			}
		}

		return $order ? $order : $default;
	}

	private static function getFilter($rules, $fields, array $datetimeFieldsList = array())
	{
		$filter = array();
		if (!empty($rules) && is_array($rules))
		{
			foreach ($rules as $key => $value)
			{
				if (preg_match('/^([^a-zA-Z]*)(.*)/', $key, $matches))
				{
					$operation = $matches[1];
					$field = $matches[2];

					if (in_array($operation, static::$allowedOperations, true) && isset($fields[$field]))
					{
						if (in_array($field, $datetimeFieldsList))
							$value = \CRestUtil::unConvertDateTime($value);

						$filter[$operation.$fields[$field]] = $value;
					}
				}
			}
		}

		return $filter;
	}

	private static function checkAdminPermissions()
	{
		if (!static::isAdmin())
		{
			throw new AccessException();
		}
	}

	private static function isAdmin()
	{
		global $USER;
		return (
			isset($USER)
			&& is_object($USER)
			&& (
				$USER->isAdmin()
				|| Loader::includeModule('bitrix24') && \CBitrix24::isPortalAdmin($USER->getID())
			)
		);
	}

	private static function getCurrentUserId()
	{
		global $USER;
		return (isset($USER) && is_object($USER)) ? $USER->getID() : 0;
	}

	private static function generateInternalCode($data)
	{
		return md5($data['APP_ID'].'@'.$data['CODE']);
	}

	private static function getAppName($appId)
	{
		if (!Loader::includeModule('rest'))
			return array('*' => 'No app');

		$iterator = AppTable::getList(
			array(
				'filter' => array(
					'=CLIENT_ID' => $appId
				),
				'select' => array('ID', 'APP_NAME', 'CODE'),
			)
		);
		$app = $iterator->fetch();
		$result = array('*' => $app['APP_NAME'] ? $app['APP_NAME'] : $app['CODE']);

		$iterator = AppLangTable::getList(array(
			'filter' => array(
				'=APP_ID' => $app['ID'],
			),
			'select' => array('LANGUAGE_ID', 'MENU_NAME')
		));
		while($lang = $iterator->fetch())
		{
			$result[strtoupper($lang['LANGUAGE_ID'])] = $lang['MENU_NAME'];
		}

		return $result;
	}

	private static function prepareActivityData(array $data, $ignore = false)
	{
		if (!$ignore)
			$data = array_change_key_case($data, CASE_UPPER);
		foreach ($data as $key => &$field)
		{
			if (is_array($field))
				$field = self::prepareActivityData($field, $key == 'PROPERTIES' || $key == 'RETURN_PROPERTIES' || $key == 'OPTIONS');
		}
		return $data;
	}

	private static function validateActivity($data, $server)
	{
		if (!is_array($data) || empty($data))
			throw new RestException('Empty data!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		static::validateActivityCode($data['CODE']);
		static::validateActivityHandler($data['HANDLER'], $server);
		if (empty($data['NAME']))
			throw new RestException('Empty activity NAME!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		if (isset($data['PROPERTIES']))
			static::validateActivityProperties($data['PROPERTIES']);

		if (isset($data['RETURN_PROPERTIES']))
			static::validateActivityProperties($data['RETURN_PROPERTIES']);
		if (isset($data['DOCUMENT_TYPE']))
			static::validateActivityDocumentType($data['DOCUMENT_TYPE']);
		if (isset($data['FILTER']) && !is_array($data['FILTER']))
			throw new RestException('Wrong activity FILTER!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
	}

	private static function validateProvider($data, $server)
	{
		if (!is_array($data) || empty($data))
			throw new RestException('Empty data!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		static::validateActivityCode($data['CODE']);
		static::validateActivityHandler($data['HANDLER'], $server);
		if (empty($data['NAME']))
			throw new RestException('Empty provider NAME!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		if (empty($data['TYPE']))
			throw new RestException('Empty provider TYPE!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		if (!in_array($data['TYPE'], RestProviderTable::getTypesList(), true))
			throw new RestException('Unknown provider TYPE!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
	}

	private static function validateRobot($data, $server)
	{
		if (!is_array($data) || empty($data))
			throw new RestException('Empty data!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		static::validateActivityCode($data['CODE']);
		static::validateActivityHandler($data['HANDLER'], $server);
		if (empty($data['NAME']))
			throw new RestException('Empty activity NAME!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		if (isset($data['PROPERTIES']))
			static::validateActivityProperties($data['PROPERTIES'], true);

		if (isset($data['RETURN_PROPERTIES']))
			throw new RestException('Return properties is not supported in Robots!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		if (isset($data['USE_SUBSCRIPTION']) && $data['USE_SUBSCRIPTION'] !== 'N')
			throw new RestException('USE_SUBSCRIPTION is not supported in Robots!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		if (isset($data['FILTER']) && !is_array($data['FILTER']))
			throw new RestException('Wrong activity FILTER!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
	}

	private static function validateActivityCode($code)
	{
		if (empty($code))
			throw new RestException('Empty activity code!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		if (!preg_match('#^[a-z0-9\.\-_]+$#i', $code))
			throw new RestException('Wrong activity code!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
	}

	private static function validateActivityHandler($handler, $server)
	{
		$handlerData = parse_url($handler);

		if (is_array($handlerData)
			&& strlen($handlerData['host']) > 0
			&& strpos($handlerData['host'], '.') > 0
		)
		{
			if ($handlerData['scheme'] == 'http' || $handlerData['scheme'] == 'https')
			{
				$host = $handlerData['host'];
				$app = self::getApp($server);
				if (strlen($app['URL']) > 0)
				{
					$urls = array($app['URL']);

					if (strlen($app['URL_DEMO']) > 0)
					{
						$urls[] = $app['URL_DEMO'];
					}
					if (strlen($app['URL_INSTALL']) > 0)
					{
						$urls[] = $app['URL_INSTALL'];
					}

					$found = false;
					foreach($urls as $url)
					{
						$a = parse_url($url);
						if ($host == $a['host'] || $a['host'] == 'localhost')
						{
							$found = true;
							break;
						}
					}

					if(!$found)
					{
						throw new RestException('Handler URL host doesn\'t match application url', self::ERROR_HANDLER_URL_MATCH);
					}
				}
			}
			else
			{
				throw new RestException('Unsupported event handler protocol', self::ERROR_UNSUPPORTED_PROTOCOL);
			}
		}
		else
		{
			throw new RestException('Wrong handler URL', self::ERROR_WRONG_HANDLER_URL);
		}
	}

	private static function validateActivityProperties($properties, $isRobot = false)
	{
		if (!is_array($properties))
			throw new RestException('Wrong properties array!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		$map = 	array(
			FieldType::BOOL => true,
			FieldType::DATE => true,
			FieldType::DATETIME => true,
			FieldType::DOUBLE => true,
			FieldType::INT => true,
			FieldType::SELECT => true,
			FieldType::STRING => true,
			FieldType::TEXT => true,
			FieldType::USER => true,
		);

		foreach ($properties as $key => $property)
		{
			if (!preg_match('#^[a-z][a-z0-9_]*$#i', $key))
				throw new RestException('Wrong property key ('.$key.')!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
			if (empty($property['NAME']))
				throw new RestException('Empty property NAME ('.$key.')!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

			if ($isRobot)
			{
				$type = isset($property['TYPE']) ? $property['TYPE'] : FieldType::STRING;
				if (!array_key_exists($type, $map))
					throw new RestException('Unsupported property type ('.$type.')!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
			}
		}
	}

	private static function validateActivityDocumentType($documentType)
	{
		try
		{
			$runtime = \CBPRuntime::getRuntime();
			$runtime->startRuntime();
			/** @var \CBPDocumentService $documentService */
			$documentService = $runtime->getService('DocumentService');
			$documentService->getDocumentFieldTypes($documentType);
		}
		catch (\CBPArgumentNullException $e)
		{
			throw new RestException('Wrong activity DOCUMENT_TYPE!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		}
	}

	private static function validateDocumentId($documentId)
	{
		$type = null;
		if ($documentId && is_array($documentId))
		{
			try
			{
				$runtime = \CBPRuntime::getRuntime();
				$runtime->startRuntime();
				/** @var \CBPDocumentService $documentService */
				$documentService = $runtime->getService('DocumentService');
				$documentId = $documentService->normalizeDocumentId($documentId);
				$type = $documentService->getDocumentType($documentId);
			}
			catch (\CBPArgumentNullException $e) {}
		}

		if (!$type)
		{
			throw new RestException('Wrong DOCUMENT_ID!');
		}

		return $documentId;
	}

	private static function extractEventToken($token)
	{
		$data = \CBPRestActivity::extractToken($token);
		if (!$data)
			throw new AccessException();
		return $data;
	}

	/**
	 * @param \CRestServer $server
	 * @return array|bool|false|mixed|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function getApp($server)
	{
		if(self::$app == null)
		{
			if (Loader::includeModule('rest'))
			{
				$result = AppTable::getList(
					array(
						'filter' => array(
							'=CLIENT_ID' => $server->getClientId()
						)
					)
				);
				self::$app = $result->fetch();
			}
		}

		return self::$app;
	}
}