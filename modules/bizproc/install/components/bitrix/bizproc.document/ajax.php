<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site']) ? substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
if($siteID !== '')
{
	define('SITE_ID', $siteID);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CUser $user */
$user = $GLOBALS["USER"];

if (!check_bitrix_sessid() || !is_object($user) || !$user->IsAuthorized() || !CModule::IncludeModule('bizproc'))
{
	die();
}

CUtil::JSPostUnescape();

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$action = $request->getPost('ajax_action');

if (empty($action))
	die('Unknown action!');

$APPLICATION->ShowAjaxHead();
$action = strtoupper($action);

$writeResponse = function(\Bitrix\Main\Result $data)
{
	$errors = $data->getErrorMessages();
	$data = $data->getData();

	$result = array('data' => $data, 'errors' => $errors);
	$result['success'] = count($errors) === 0;
	if(!defined('PUBLIC_AJAX_MODE'))
	{
		define('PUBLIC_AJAX_MODE', true);
	}
	$GLOBALS['APPLICATION']->RestartBuffer();
	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

	echo \Bitrix\Main\Web\Json::encode($result);
	CMain::FinalActions();
	die();
};

$sendData = function (array $data) use ($writeResponse)
{
	$result = new \Bitrix\Main\Result();
	$result->setData($data);
	$writeResponse($result);
};

$sendError = function($error) use ($writeResponse)
{
	$result = new \Bitrix\Main\Result();
	$errors = (array)$error;
	foreach ($errors as $e)
	{
		$result->addError(new \Bitrix\Main\Error($e));
	}

	$writeResponse($result);
};

$getDocumentStates = function ($documentId) use ($user)
{
	$workflows = array();
	$states =CBPStateService::GetDocumentStates($documentId);
	$userId = $user->GetID();
	$userGroups = $user->GetUserGroupArray();

	foreach ($states as $state)
	{
		if (!CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::ViewWorkflow,
			$userId,
			$documentId,
			array(
				"DocumentStates" => $states,
				"WorkflowId" => $state["ID"]
			)
		))
		{
			continue;
		}

		$state['EVENTS'] = CBPDocument::GetAllowableEvents($userId, $userGroups, $state);
		$state['TASKS'] = CBPDocument::GetUserTasksForWorkflow($userId, $state["ID"]);
		$state['STATE_MODIFIED_FORMATTED'] = FormatDateFromDB($state["STATE_MODIFIED"]);

		$workflows[] = $state;
	}
	return $workflows;
};

$moduleId = $request->getPost('module_id');
$entity = $request->getPost('entity');
$paramDocumentType = $request->getPost('document_type');
$paramDocumentId = $request->getPost('document_id');

if (!$moduleId || !$entity || !$paramDocumentType || !$paramDocumentId)
{
	$sendError('Invalid request data');
}

$documentType = array($moduleId, $entity, $paramDocumentType);
$documentId = array($moduleId, $entity, $paramDocumentId);

$documentStates = CBPDocument::GetDocumentStates($documentType, $documentId);

switch ($action)
{
	case 'KILL_WORKFLOW':
		$canKill = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::CreateWorkflow,
			$user->GetID(),
			$documentId,
			array("DocumentStates" => $documentStates)
		);

		$workflowId = $request->getPost('workflow_id');

		if (!$canKill)
		{
			$sendError('Access Denied');
		}
		else
		{
			$terminateWorkflow = isset($documentStates[$workflowId]['WORKFLOW_STATUS']) && $documentStates[$workflowId]['WORKFLOW_STATUS'] !== null;
			$errors = CBPDocument::killWorkflow($workflowId, $terminateWorkflow, $documentId);

			if (count($errors) > 0)
			{
				$sendError($errors[0]);
			}
			else
			{
				$sendData(array(
					'killed' => true,
					'workflows' => $getDocumentStates($documentId)
				));
			}
		}
		break;


	case 'TERMINATE_WORKFLOW':
		$canTerminate = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::StartWorkflow,
			$user->GetID(),
			$documentId,
			array("DocumentStates" => $documentStates)
		);

		$workflowId = $request->getPost('workflow_id');

		if (!$canTerminate)
		{
			$sendError('Access Denied');
		}
		else
		{
			CBPDocument::TerminateWorkflow($workflowId, $documentId, $errors);

			if (count($errors) > 0)
			{
				$sendError($errors[0]);
			}
			else
			{
				$sendData(array(
					'terminated' => true,
					'workflows' => $getDocumentStates($documentId)
				));
			}
		}
		break;

	case 'SEND_EVENTS':
		$canView = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::ViewWorkflow,
			$user->GetID(),
			$documentId,
			array("DocumentStates" => $documentStates)
		);
		if (!$canView)
		{
			$sendError('Access Denied');
		}
		else
		{
			$errors = array();
			$events = $request->getPost('events');
			foreach ($events as $workflowId => $event)
			{
				$errorTmp = array();
				CBPDocument::SendExternalEvent(
					$workflowId,
					$event,
					array("Groups" => $user->getUserGroupArray(), "User" => $user->getId()),
					$errorTmp
				);

				if (count($errorTmp) > 0)
				{
					foreach ($errorTmp as $e)
					{
						$errors[] = $e["message"];
					}
				}
			}

			if (count($errors) > 0)
			{
				$sendError($errors);
			}
			else
			{
				$sendData(array(
					'events_sent' => true,
					'workflows' => $getDocumentStates($documentId)
				));
			}
		}

		break;

	case 'GET_WORKFLOWS':
		$canView = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::ViewWorkflow,
			$user->GetID(),
			$documentId,
			array("DocumentStates" => $documentStates)
		);
		if (!$canView)
		{
			$sendError('Access Denied');
		}
		else
		{
			$sendData(array(
				'workflows' => $getDocumentStates($documentId)
			));
		}
		break;
}
$sendError('Unknown action!');