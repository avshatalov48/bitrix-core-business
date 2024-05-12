<?php

namespace Bitrix\Bizproc;

use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Main\Loader;
use Bitrix\Rest\AppLangTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\HandlerHelper;
use Bitrix\Rest\PlacementTable;
use Bitrix\Rest\RestException;
use Bitrix\Rest\AccessException;

Loader::includeModule('rest');

class RestService extends \IRestService
{
	public const SCOPE = 'bizproc';
	public const PLACEMENT_ACTIVITY_PROPERTIES_DIALOG = 'BIZPROC_ACTIVITY_PROPERTIES_DIALOG';

	protected static $app;
	private static $allowedOperations = ['', '!', '<', '<=', '>', '>='];
	//, '><', '!><', '?', '=', '!=', '%', '!%', ''); May be later?

	const ERROR_ACTIVITY_ALREADY_INSTALLED = 'ERROR_ACTIVITY_ALREADY_INSTALLED';
	const ERROR_ACTIVITY_ADD_FAILURE = 'ERROR_ACTIVITY_ADD_FAILURE';
	const ERROR_ACTIVITY_VALIDATION_FAILURE = 'ERROR_ACTIVITY_VALIDATION_FAILURE';
	const ERROR_ACTIVITY_NOT_FOUND = 'ERROR_ACTIVITY_NOT_FOUND';
	const ERROR_EMPTY_LOG_MESSAGE = 'ERROR_EMPTY_LOG_MESSAGE';
	const ERROR_WRONG_WORKFLOW_ID = 'ERROR_WRONG_WORKFLOW_ID';

	const ERROR_TEMPLATE_VALIDATION_FAILURE = 'ERROR_TEMPLATE_VALIDATION_FAILURE';

	const ERROR_TASK_VALIDATION = 'ERROR_TASK_VALIDATION';
	const ERROR_TASK_NOT_FOUND = 'ERROR_TASK_NOT_FOUND';
	const ERROR_TASK_TYPE = 'ERROR_TASK_TYPE';
	const ERROR_TASK_COMPLETED = 'ERROR_TASK_COMPLETED';
	const ERROR_TASK_EXECUTION = 'ERROR_TASK_EXECUTION';

	private const ALLOWED_TASK_ACTIVITIES = [
		'ReviewActivity',
		'ApproveActivity',
		'RequestInformationActivity',
		'RequestInformationOptionalActivity'
	];

	public static function onRestServiceBuildDescription()
	{
		$map = [];

		if (self::isEnabled())
		{
			$map = [
				//activity
				'bizproc.activity.add' => [__CLASS__, 'addActivity'],
				'bizproc.activity.update' => [__CLASS__, 'updateActivity'],
				'bizproc.activity.delete' => [__CLASS__, 'deleteActivity'],
				'bizproc.activity.log' => [__CLASS__, 'writeActivityLog'],
				'bizproc.activity.list' => [__CLASS__, 'getActivityList'],

				//event
				'bizproc.event.send' => [__CLASS__, 'sendEvent'],

				//task
				'bizproc.task.list' => [__CLASS__, 'getTaskList'],
				'bizproc.task.complete' => [__CLASS__, 'completeTask'],

				//workflow
				'bizproc.workflow.terminate' => [__CLASS__, 'terminateWorkflow'],
				'bizproc.workflow.kill' => [__CLASS__, 'killWorkflow'],
				'bizproc.workflow.start' => [__CLASS__, 'startWorkflow'],

				//workflow.instance
				'bizproc.workflow.instance.list' => [__CLASS__, 'getWorkflowInstances'],

				//workflow.template
				'bizproc.workflow.template.list' => [__CLASS__, 'getWorkflowTemplates'],
				'bizproc.workflow.template.add' => [__CLASS__, 'addWorkflowTemplate'],
				'bizproc.workflow.template.update' => [__CLASS__, 'updateWorkflowTemplate'],
				'bizproc.workflow.template.delete' => [__CLASS__, 'deleteWorkflowTemplate'],

				//aliases
				'bizproc.workflow.instances' => [__CLASS__, 'getWorkflowInstances'],
			];
		}

		if (
			self::isEnabled()
			|| self::isEnabled('crm_automation_lead')
			|| self::isEnabled('crm_automation_deal')
			|| self::isEnabled('crm_automation_order')
			|| self::isEnabled('tasks_automation')
		)
		{
			$map = array_merge($map, array(
				'bizproc.event.send' => [__CLASS__, 'sendEvent'],
				'bizproc.activity.log' => [__CLASS__, 'writeActivityLog'],

				//robot
				'bizproc.robot.add' => array(__CLASS__, 'addRobot'),
				'bizproc.robot.update' => array(__CLASS__, 'updateRobot'),
				'bizproc.robot.delete' => array(__CLASS__, 'deleteRobot'),
				'bizproc.robot.list' => array(__CLASS__, 'getRobotList'),

				//provider
				'bizproc.provider.add' => [__CLASS__, 'addProvider'],
				'bizproc.provider.delete' => [__CLASS__, 'deleteProvider'],
				'bizproc.provider.list' => [__CLASS__, 'getProviderList'],
			));
		}

		//placements
		$map[\CRestUtil::PLACEMENTS] = [
			static::PLACEMENT_ACTIVITY_PROPERTIES_DIALOG => ['private' => true],
		];

		return [
			static::SCOPE => $map,
		];
	}

	private static function isEnabled(string $feature = 'bizproc'): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			return \Bitrix\Bitrix24\Feature::isFeatureEnabled($feature);
		}

		return true;
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

		self::deleteAppPlacement($app['ID']);
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

		$appId = self::getAppId($server->getClientId());
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
		$params['USE_PLACEMENT'] = (isset($params['USE_PLACEMENT']) && $params['USE_PLACEMENT'] === 'Y') ? 'Y' : 'N';

		if ($params['USE_PLACEMENT'] === 'Y')
		{
			self::validateActivityHandler($params['PLACEMENT_HANDLER'] ?? null, $server);
			self::upsertAppPlacement($appId, $params['CODE'], $params['PLACEMENT_HANDLER'] ?? null);
		}

		$result = RestActivityTable::add($params);

		if ($result->getErrors())
		{
			if ($params['USE_PLACEMENT'] === 'Y')
			{
				self::deleteAppPlacement($appId, $params['CODE']);
			}

			throw new RestException('Activity save error!', self::ERROR_ACTIVITY_ADD_FAILURE);
		}

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function updateActivity($params, $n, $server)
	{
		return self::updateActivityInternal($params, $server, false);
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
	public static function updateRobot($params, $n, $server)
	{
		return self::updateActivityInternal($params, $server, true);
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
		self::deleteAppPlacement(self::getAppId($params['APP_ID']), $params['CODE']);

		return true;
	}

	/**
	 * @param array $params
	 * @param \CRestServer $server
	 * @param bool $isRobot
	 * @return bool
	 * @throws AccessException
	 * @throws RestException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function updateActivityInternal($params, $server, $isRobot = false)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$params = self::prepareActivityData($params);
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

		$fields = (isset($params['FIELDS']) && is_array($params['FIELDS'])) ? $params['FIELDS'] : null;

		if (!$fields)
		{
			throw new RestException('No fields to update', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		}

		$toUpdate = [];

		if (isset($fields['HANDLER']))
		{
			self::validateActivityHandler($fields['HANDLER'], $server);
			$toUpdate['HANDLER'] = $fields['HANDLER'];
		}

		if (isset($fields['AUTH_USER_ID']))
		{
			$toUpdate['AUTH_USER_ID'] = (int) $fields['AUTH_USER_ID'];
		}

		if (isset($fields['USE_SUBSCRIPTION']))
		{
			$toUpdate['USE_SUBSCRIPTION'] = (string) $fields['USE_SUBSCRIPTION'];
		}

		if (isset($fields['USE_PLACEMENT']))
		{
			$toUpdate['USE_PLACEMENT'] = ($fields['USE_PLACEMENT'] === 'Y') ? 'Y' : 'N';
		}

		if (!empty($fields['NAME']))
		{
			$toUpdate['NAME'] = $fields['NAME'];
		}

		if (isset($fields['DESCRIPTION']))
		{
			$toUpdate['DESCRIPTION'] = $fields['DESCRIPTION'];
		}

		if (isset($fields['PROPERTIES']))
		{
			self::validateActivityProperties($fields['PROPERTIES']);
			$toUpdate['PROPERTIES'] = $fields['PROPERTIES'];
		}

		if (isset($fields['RETURN_PROPERTIES']))
		{
			self::validateActivityProperties($fields['RETURN_PROPERTIES']);
			$toUpdate['RETURN_PROPERTIES'] = $fields['RETURN_PROPERTIES'];
		}

		if (isset($fields['DOCUMENT_TYPE']))
		{
			if (empty($fields['DOCUMENT_TYPE']))
			{
				$toUpdate['DOCUMENT_TYPE'] = null;
			}
			else
			{
				static::validateActivityDocumentType($fields['DOCUMENT_TYPE']);
				$toUpdate['DOCUMENT_TYPE'] = $fields['DOCUMENT_TYPE'];
			}
		}

		if (isset($fields['FILTER']))
		{
			if (empty($fields['FILTER']))
			{
				$toUpdate['FILTER'] = null;
			}
			else
			{
				if (!is_array($fields['FILTER']))
				{
					throw new RestException('Wrong activity FILTER!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
				}
				$toUpdate['FILTER'] = $fields['FILTER'];
			}
		}

		if (!$toUpdate)
		{
			throw new RestException('No fields to update', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		}

		if (isset($fields['PLACEMENT_HANDLER']))
		{
			self::validateActivityHandler($fields['PLACEMENT_HANDLER'], $server);
			self::upsertAppPlacement(self::getAppId($params['APP_ID']), $params['CODE'], $fields['PLACEMENT_HANDLER']);
		}

		if (isset($toUpdate['USE_PLACEMENT']) && $toUpdate['USE_PLACEMENT'] === 'N')
		{
			self::deleteAppPlacement(self::getAppId($params['APP_ID']), $params['CODE']);
		}

		$updateResult = RestActivityTable::update($result['ID'], $toUpdate);

		if (!$updateResult->isSuccess())
		{
			throw new RestException(
				implode('; ', $updateResult->getErrorMessages()),
				self::ERROR_ACTIVITY_VALIDATION_FAILURE
			);
		}

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
		[$workflowId, $activityName, $eventId] = self::extractEventToken($params['EVENT_TOKEN']);

		$errors = [];
		\CBPDocument::sendExternalEvent(
			$workflowId,
			$activityName,
			[
				'EVENT_ID' => $eventId,
				'RETURN_VALUES' => $params['RETURN_VALUES'] ?? [],
				'LOG_MESSAGE' => $params['LOG_MESSAGE'] ?? '',
			],
			$errors,
		);

		if ($errors)
		{
			$error = current($errors);
			throw new RestException($error['message'], $error['code']);
		}

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
		[$workflowId, $activityName, $eventId] = self::extractEventToken($params['EVENT_TOKEN']);

		$logMessage = isset($params['LOG_MESSAGE']) ? $params['LOG_MESSAGE'] : '';

		if (empty($logMessage))
			throw new RestException('Empty log message!', self::ERROR_EMPTY_LOG_MESSAGE);

		$errors = [];
		\CBPDocument::sendExternalEvent(
			$workflowId,
			$activityName,
			[
				'EVENT_ID' => $eventId,
				'LOG_ACTION' => true,
				'LOG_MESSAGE' => $logMessage
			],
			$errors,
		);

		if ($errors)
		{
			$error = current($errors);
			throw new RestException($error['message'], $error['code']);
		}

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
			'MODULE_ID' => 'MODULE_ID',
			'ENTITY' => 'ENTITY',
			'DOCUMENT_ID' => 'DOCUMENT_ID',
			'STARTED' => 'STARTED',
			'STARTED_BY' => 'STARTED_BY',
			'TEMPLATE_ID' => 'WORKFLOW_TEMPLATE_ID',
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

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool True on success.
	 * @throws AccessException
	 * @throws RestException
	 */
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

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool True on success.
	 * @throws AccessException
	 * @throws RestException
	 */
	public static function killWorkflow($params, $n, $server)
	{
		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		if (empty($params['ID']))
		{
			throw new RestException('Empty workflow instance ID', self::ERROR_WRONG_WORKFLOW_ID);
		}

		$id = $params['ID'];
		$errors = \CBPDocument::killWorkflow($id);

		if ($errors)
		{
			throw new RestException($errors[0]['message']);
		}

		return true;
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return string Workflow ID.
	 * @throws AccessException
	 * @throws RestException
	 */
	public static function startWorkflow($params, $n, $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if (empty($params['TEMPLATE_ID']))
		{
			throw new RestException('Empty TEMPLATE_ID', self::ERROR_WRONG_WORKFLOW_ID);
		}
		$templateId = (int)$params['TEMPLATE_ID'];
		$tplDocumentType = self::getTemplateDocumentType($templateId);

		if (!$tplDocumentType)
		{
			throw new RestException('Template not found', self::ERROR_WRONG_WORKFLOW_ID);
		}

		//hotfix #0120474
		$getParams = array_change_key_case($_GET, CASE_UPPER);
		if (isset($getParams['DOCUMENT_ID']) && is_array($getParams['DOCUMENT_ID']))
		{
			$params['DOCUMENT_ID'] = $getParams['DOCUMENT_ID'];
		}

		$documentId = self::getDocumentId($params['DOCUMENT_ID']);

		if (!$documentId)
		{
			throw new RestException('Wrong DOCUMENT_ID!');
		}

		$documentType = self::getDocumentType($documentId);

		if (!$documentType)
		{
			throw new RestException('Incorrect document type!');
		}

		if (!\CBPHelper::isEqualDocument($tplDocumentType, $documentType))
		{
			throw new RestException('Template type and DOCUMENT_ID mismatch!');
		}

		self::checkStartWorkflowPermissions($documentId, $templateId);

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

	private static function checkStartWorkflowPermissions(array $documentId, $templateId)
	{
		if (static::isAdmin())
		{
			return true;
		}

		if (
			\CBPDocument::CanUserOperateDocument(
				\CBPCanUserOperateOperation::StartWorkflow,
				static::getCurrentUserId(),
				$documentId,
				['WorkflowTemplateId' => $templateId]
			)
		)
		{
			return true;
		}

		throw new AccessException();
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return mixed Templates collection.
	 * @throws AccessException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
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
		$filter['<AUTO_EXECUTE'] = \CBPDocumentEventType::Automation;

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
	 * @return bool
	 * @throws \Exception
	 */
	public static function addWorkflowTemplate($params, $n, $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		self::validateTemplateDocumentType($params['DOCUMENT_TYPE']);
		self::validateTemplateName($params['NAME']);

		$autoExecute = \CBPDocumentEventType::None;
		if (isset($params['AUTO_EXECUTE']))
		{
			self::validateTemplateAutoExecution($params['AUTO_EXECUTE']);
			$autoExecute = (int) $params['AUTO_EXECUTE'];
		}

		$data = self::prepareTemplateData($params['TEMPLATE_DATA']);

		return \CBPWorkflowTemplateLoader::ImportTemplate(
			0,
			$params['DOCUMENT_TYPE'],
			$autoExecute,
			$params['NAME'],
			isset($params['DESCRIPTION']) ? (string) $params['DESCRIPTION'] : '',
			$data,
			self::generateTemplateSystemCode($server)
		);
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function updateWorkflowTemplate($params, $n, $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		$fields = (isset($params['FIELDS']) && is_array($params['FIELDS'])) ? $params['FIELDS'] : null;

		if (!$fields)
		{
			throw new RestException("No fields to update.");
		}

		$tpl = WorkflowTemplateTable::getList(array(
			'select' => ['ID', 'SYSTEM_CODE', 'NAME', 'DESCRIPTION', 'AUTO_EXECUTE', 'MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'],
			'filter' => ['=ID' => (int) $params['ID']],
		))->fetch();

		if (!$tpl)
		{
			throw new RestException("Workflow template not found.");
		}

		if ($tpl['SYSTEM_CODE'] !== self::generateTemplateSystemCode($server))
		{
			throw new RestException("You can update ONLY templates created by current application");
		}

		if (isset($fields['NAME']))
		{
			self::validateTemplateName($fields['NAME']);
			$tpl['NAME'] = $fields['NAME'];
		}

		if (isset($fields['DESCRIPTION']))
		{
			$tpl['DESCRIPTION'] = (string) $fields['DESCRIPTION'];
		}

		if (isset($fields['AUTO_EXECUTE']))
		{
			self::validateTemplateAutoExecution($fields['AUTO_EXECUTE']);
			$tpl['AUTO_EXECUTE'] = (int) $fields['AUTO_EXECUTE'];
		}

		if (isset($fields['TEMPLATE_DATA']))
		{
			$data = self::prepareTemplateData($fields['TEMPLATE_DATA']);

			return \CBPWorkflowTemplateLoader::ImportTemplate(
				$tpl['ID'],
				[$tpl['MODULE_ID'], $tpl['ENTITY'], $tpl['DOCUMENT_TYPE']],
				$tpl['AUTO_EXECUTE'],
				$tpl['NAME'],
				$tpl['DESCRIPTION'],
				$data,
				$tpl['SYSTEM_CODE']
			);
		}
		else
		{
			return \CBPWorkflowTemplateLoader::Update($tpl['ID'], [
				'NAME' => $tpl['NAME'],
				'DESCRIPTION' => $tpl['DESCRIPTION'],
				'AUTO_EXECUTE' => $tpl['AUTO_EXECUTE'],
			]);
		}
	}

	/**
	 * @param array $params Input params.
	 * @param int $n Offset.
	 * @param \CRestServer $server Rest server instance.
	 * @return bool
	 * @throws \Exception
	 */
	public static function deleteWorkflowTemplate($params, $n, $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		self::checkAdminPermissions();
		$params = array_change_key_case($params, CASE_UPPER);

		$tpl = WorkflowTemplateTable::getList(array(
			'select' => ['ID', 'SYSTEM_CODE'],
			'filter' => ['=ID' => (int) $params['ID']],
		))->fetch();

		if (!$tpl)
		{
			throw new RestException("Workflow template not found.");
		}

		if ($tpl['SYSTEM_CODE'] !== self::generateTemplateSystemCode($server))
		{
			throw new RestException("You can delete ONLY templates created by current application");
		}

		\CBPWorkflowTemplateLoader::Delete($tpl['ID']);
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
			'ACTIVITY_NAME' => 'ACTIVITY_NAME',
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
				$row['PARAMETERS'] = static::prepareTaskParameters($row['PARAMETERS'], $row);
			}

			$result[] = $row;
		}

		return static::setNavData($result, $iterator);
	}

	private static function prepareTaskParameters(array $parameters, array $task)
	{
		$whiteList = [
			['CommentLabelMessage', 'CommentLabel'],
			'CommentRequired', 'ShowComment',
			['TaskButtonMessage', 'StatusOkLabel'],
			['TaskButton1Message', 'StatusYesLabel'],
			['TaskButton2Message', 'StatusNoLabel'],
			['TaskButtonCancelMessage', 'StatusCancelLabel'],
			['REQUEST', 'Fields'],
		];

		$filtered = [];

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

		if (isset($filtered['Fields']))
		{
			$filtered['Fields'] = self::externalizeRequestFields($task, $filtered['Fields']);
		}

		return $filtered;
	}

	private static function externalizeRequestFields($task, array $fields): array
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$result = [];
		foreach ($fields as $requestField)
		{
			$id = $requestField['Name'];
			$requestField['Name'] = $requestField['Title'];
			$property = FieldType::normalizeProperty($requestField);
			$property['Id'] = $id;

			$fieldTypeObject = $documentService->getFieldTypeObject($task["PARAMETERS"]["DOCUMENT_TYPE"], $property);
			if ($fieldTypeObject)
			{
				$fieldTypeObject->setDocumentId($task["PARAMETERS"]["DOCUMENT_ID"]);
				$property['Default'] = $fieldTypeObject->externalizeValue(
					FieldType::VALUE_CONTEXT_REST,
					$property['Default']
				);
			}

			$result[] = $property;
		}
		return $result;
	}

	private static function internalizeRequestFields($task, array $values): array
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$result = [];

		foreach ($task['PARAMETERS']['REQUEST'] as $property)
		{
			if (!isset($values[$property['Name']]))
			{
				continue;
			}

			$property = FieldType::normalizeProperty($property);
			$fieldTypeObject = $documentService->getFieldTypeObject($task["PARAMETERS"]["DOCUMENT_TYPE"], $property);
			if ($fieldTypeObject)
			{
				$fieldTypeObject->setDocumentId($task["PARAMETERS"]["DOCUMENT_ID"]);
				$result[$property['Name']] = $fieldTypeObject->internalizeValue(FieldType::VALUE_CONTEXT_REST, $values[$property['Name']]);
			}
		}
		return $result;
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

		if (!in_array($task['ACTIVITY'], self::ALLOWED_TASK_ACTIVITIES))
		{
			throw new RestException('Incorrect task type', self::ERROR_TASK_TYPE);
		}

		if (!empty($params['FIELDS']))
		{
			$params['FIELDS'] = self::internalizeRequestFields($task, $params['FIELDS']);
		}

		$errors = array();
		$request = array(
			'INLINE_USER_STATUS' => \CBPTaskUserStatus::resolveStatus($params['STATUS']),
			'task_comment' => !empty($params['COMMENT']) && is_string($params['COMMENT']) ? $params['COMMENT'] : null,
			'fields' => $params['FIELDS'] ?? null,
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
				$field = mb_strtoupper($field);
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
				$field = mb_strtoupper($field);
				$ordering = mb_strtoupper($ordering);
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
		return (isset($USER) && is_object($USER)) ? (int)$USER->getID() : 0;
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
			$result[mb_strtoupper($lang['LANGUAGE_ID'])] = $lang['MENU_NAME'];
		}

		return $result;
	}

	private static function getAppId($clientId)
	{
		if (!Loader::includeModule('rest'))
		{
			return null;
		}

		$iterator = AppTable::getList(
			array(
				'filter' => array(
					'=CLIENT_ID' => $clientId
				),
				'select' => array('ID'),
			)
		);
		$app = $iterator->fetch();

		return (int) $app['ID'];
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
			static::validateActivityProperties($data['PROPERTIES']);

		if (isset($data['RETURN_PROPERTIES']))
			static::validateActivityProperties($data['RETURN_PROPERTIES']);
		if (isset($data['FILTER']) && !is_array($data['FILTER']))
			throw new RestException('Wrong activity FILTER!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
	}

	private static function validateActivityCode($code)
	{
		if (empty($code))
		{
			throw new RestException('Empty activity code!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		}
		if (!is_string($code) || !preg_match('#^[a-z0-9\.\-_]+$#i', $code))
		{
			throw new RestException('Wrong activity code!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
		}
	}

	private static function validateActivityHandler($handler, $server)
	{
		HandlerHelper::checkCallback($handler);
	}

	private static function validateActivityProperties($properties)
	{
		if (!is_array($properties))
			throw new RestException('Wrong properties array!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);

		foreach ($properties as $key => $property)
		{
			if (!preg_match('#^[a-z][a-z0-9_]*$#i', $key))
				throw new RestException('Wrong property key ('.$key.')!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
			if (empty($property['NAME']))
				throw new RestException('Empty property NAME ('.$key.')!', self::ERROR_ACTIVITY_VALIDATION_FAILURE);
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

	private static function getDocumentId($documentId): ?array
	{
		try
		{
			$documentService = \CBPRuntime::getRuntime()->getDocumentService();
			$documentId = $documentService->normalizeDocumentId($documentId);
			if ($documentService->getDocument($documentId))
			{
				return $documentId;
			}
		}
		catch (\CBPArgumentException $exception) {}

		return null;
	}

	private static function getDocumentType(array $documentId): ?array
	{
		try
		{
			$documentId = \CBPHelper::parseDocumentId($documentId);
			$runtime = \CBPRuntime::getRuntime(true);
			$documentService = $runtime->getDocumentService();

			return $documentService->getDocumentType($documentId);
		}
		catch (\Exception $e) {}

		return null;
	}

	private static function getTemplateDocumentType(int $id): ?array
	{
		$tpl = WorkflowTemplateTable::getList([
			'select' => ['MODULE_ID', 'ENTITY', 'DOCUMENT_TYPE'],
			'filter' => ['=ID' => $id],
		])->fetch();

		if ($tpl)
		{
			return [$tpl['MODULE_ID'], $tpl['ENTITY'], $tpl['DOCUMENT_TYPE']];
		}
		return null;
	}

	private static function validateTemplateName($name)
	{
		if (empty($name))
		{
			throw new RestException('Empty activity code!', self::ERROR_TEMPLATE_VALIDATION_FAILURE);
		}
	}

	private static function upsertAppPlacement(int $appId, string $code,  string $handler)
	{
		$filter = [
			'=APP_ID' => $appId,
			'=ADDITIONAL' => $code,
			'=PLACEMENT' => static::PLACEMENT_ACTIVITY_PROPERTIES_DIALOG,
		];

		$dbRes = PlacementTable::getList(array(
			'filter' => $filter
		));

		$placementHandler = $dbRes->fetch();

		if ($placementHandler)
		{
			$result = PlacementTable::update($placementHandler['ID'], ['PLACEMENT_HANDLER' => $handler]);
		}
		else
		{
			$placementBind = array(
				'APP_ID' => $appId,
				'ADDITIONAL' => $code,
				'PLACEMENT' => static::PLACEMENT_ACTIVITY_PROPERTIES_DIALOG,
				'PLACEMENT_HANDLER' => $handler,
			);

			$result = PlacementTable::add($placementBind);
		}

		if(!$result->isSuccess())
		{
			$errorMessage = $result->getErrorMessages();
			throw new RestException(
				'Unable to set placement handler: '.implode(', ', $errorMessage),
				RestException::ERROR_CORE
			);
		}
	}

	private static function deleteAppPlacement(int $appId, string $code = null)
	{
		$filter = [
			'=APP_ID' => $appId,
			'=PLACEMENT' => static::PLACEMENT_ACTIVITY_PROPERTIES_DIALOG,
		];

		if ($code)
		{
			$filter['=ADDITIONAL'] = $code;
		}

		$dbRes = PlacementTable::getList(array(
			'filter' => $filter
		));

		while($placementHandler = $dbRes->fetch())
		{
			PlacementTable::delete($placementHandler["ID"]);
		}
	}

	private static function prepareTemplateData($data)
	{
		if (!empty($data))
		{
			$fileFields = \CRestUtil::saveFile($data);

			if ($fileFields)
			{
				return file_get_contents($fileFields['tmp_name']);
			}
		}
		throw new RestException('Incorrect field TEMPLATE_DATA!', self::ERROR_TEMPLATE_VALIDATION_FAILURE);
	}

	private static function validateTemplateDocumentType($documentType)
	{
		try
		{
			$documentService = \CBPRuntime::getRuntime(true)->getDocumentService();
			$documentService->getDocumentFieldTypes($documentType);
		}
		catch (\CBPArgumentNullException $e)
		{
			throw new RestException('Incorrect field DOCUMENT_TYPE!', self::ERROR_TEMPLATE_VALIDATION_FAILURE);
		}
	}

	private static function validateTemplateAutoExecution($flag)
	{
		if ($flag === (string) (int) $flag)
		{
			$flag = (int) $flag;

			if (in_array(
					$flag,
					[
						\CBPDocumentEventType::None,
						\CBPDocumentEventType::Create,
						\CBPDocumentEventType::Edit,
						\CBPDocumentEventType::Create | \CBPDocumentEventType::Edit
					],
					true
				)
			)
			{
				return true;
			}
		}

		throw new RestException('Incorrect field AUTO_EXECUTE!', self::ERROR_TEMPLATE_VALIDATION_FAILURE);
	}

	private static function generateTemplateSystemCode(\CRestServer $server)
	{
		$appId = self::getAppId($server->getClientId());

		return 'rest_app_'.$appId;
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
