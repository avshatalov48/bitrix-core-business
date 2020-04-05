<?php
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

$siteId = '';
if (isset($_REQUEST['site_id']) && is_string($_REQUEST['site_id']))
	$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site_id']), 0, 2);

if (!$siteId)
	define('SITE_ID', $siteId);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

/**
 * @global CUser $USER
 */

if(!CModule::IncludeModule('bizproc') || !CModule::IncludeModule('bizproc'))
	die();

global $USER, $DB, $APPLICATION;

$curUser = isset($USER) && is_object($USER) ? $USER : null;
if (!$curUser || !$curUser->IsAuthorized() || !check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	die();
}

CUtil::JSPostUnescape();

$action = !empty($_REQUEST['ajax_action']) ? $_REQUEST['ajax_action'] : null;

if (empty($action))
	die('Unknown action!');

$APPLICATION->ShowAjaxHead();
$action = strtoupper($action);

$sendResponse = function($data, array $errors = array(), $plain = false)
{
	if ($data instanceof Bitrix\Main\Result)
	{
		$errors = $data->getErrorMessages();
		$data = $data->getData();
	}

	$result = array('DATA' => $data, 'ERRORS' => $errors);
	$result['SUCCESS'] = count($errors) === 0;
	if(!defined('PUBLIC_AJAX_MODE'))
	{
		define('PUBLIC_AJAX_MODE', true);
	}
	$GLOBALS['APPLICATION']->RestartBuffer();
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

	if ($plain)
	{
		$result = $result['DATA'];
	}

	echo \Bitrix\Main\Web\Json::encode($result);
	CMain::FinalActions();
	die();
};
$sendError = function($error) use ($sendResponse)
{
	$sendResponse(array(), array($error));
};

$sendHtmlResponse = function($html)
{
	if(!defined('PUBLIC_AJAX_MODE'))
	{
		define('PUBLIC_AJAX_MODE', true);
	}
	header('Content-Type: text/html; charset='.LANG_CHARSET);
	echo $html;
	CMain::FinalActions();
	die();
};

CBitrixComponent::includeComponentClass('bitrix:bizproc.automation');

$documentInformation = \BizprocAutomationComponent::unSignDocument($_POST['document_signed']);

if (!$documentInformation)
{
	$sendError('Invalid request [document_signed]');
}
list($documentType, $documentCategoryId, $documentId) = $documentInformation;

try
{
	$documentType = \CBPHelper::ParseDocumentId($documentType);
}
catch (\CBPArgumentNullException $e)
{
	$sendError('Invalid request [document_type]');
}

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();

$documentService = $runtime->GetService('DocumentService');

$target = $documentService->createAutomationTarget($documentType);

if (!$target || !$target->isAvailable())
{
	$sendError('Automation target is not supported for this document');
}

$checkConfigWritePerms = function() use ($documentType, $documentCategoryId, $curUser, $sendError)
{
	$tplUser = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);
	if ($tplUser->isAdmin())
	{
		return true;
	}

	$canWrite = CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateAutomation,
		$curUser->getId(),
		$documentType,
		['DocumentCategoryId' => $documentCategoryId]
	);
	if (!$canWrite)
	{
		$sendError('Access denied!');
	}
};

$checkReadPerms = function($documentId) use ($documentType, $curUser, $sendError)
{
	$tplUser = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);
	if ($tplUser->isAdmin())
	{
		return true;
	}

	$documentId = [$documentType[0], $documentType[1], $documentId];
	$canRead = CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::ViewWorkflow,
		$curUser->getId(),
		$documentId
	);
	if (!$canRead)
	{
		$sendError('Access denied!');
	}
};

switch ($action)
{
	case 'GET_ROBOT_DIALOG':
		//Check permissions.
		$checkConfigWritePerms();

		$robotData = isset($_REQUEST['robot']) && is_array($_REQUEST['robot']) ? $_REQUEST['robot'] : null;
		if (!$robotData)
			$sendError('Empty robot data.');

		$context = isset($_REQUEST['context']) && is_array($_REQUEST['context']) ? $_REQUEST['context'] : null;

		ob_start();
		$APPLICATION->includeComponent(
			'bitrix:bizproc.automation',
			'',
			array(
				'ACTION' => 'ROBOT_SETTINGS',
				'DOCUMENT_TYPE' => $documentType,
				'DOCUMENT_CATEGORY_ID' => $documentCategoryId,
				'ROBOT_DATA' => $robotData,
				'REQUEST' => $_REQUEST['form_name'],
				'CONTEXT' => $context
			)
		);
		$dialog = ob_get_clean();

		$sendHtmlResponse($dialog);
		break;

	case 'SAVE_ROBOT_SETTINGS':
		//Check permissions.
		$checkConfigWritePerms();

		$robotData = isset($_REQUEST['robot']) && is_array($_REQUEST['robot']) ? $_REQUEST['robot'] : null;
		if (!$robotData)
			$sendError('Empty robot data.');

		$requestData = isset($_POST['form_data']) && is_array($_POST['form_data']) ? $_POST['form_data'] : array();

		$template = new \Bitrix\Bizproc\Automation\Engine\Template($documentType);
		$saveResult = $template->saveRobotSettings($robotData, $requestData);

		if ($saveResult->isSuccess())
		{
			$data = $saveResult->getData();
			CBitrixComponent::includeComponentClass('bitrix:bizproc.automation');
			$data['robot']['viewData'] = \BizprocAutomationComponent::getRobotViewData($data['robot'], $documentType);

			$sendResponse(array('robot' => $data['robot']));
		}
		else
		{
			$sendError($saveResult->getErrorMessages());
		}
		break;

	case 'SAVE_AUTOMATION':

		//Check permissions.
		$checkConfigWritePerms();

		//save Templates and Robots
		$templates = isset($_REQUEST['templates']) && is_array($_REQUEST['templates']) ? $_REQUEST['templates'] : [];
		$errors = array();

		//save Triggers
		$updatedTriggers = [];
		$triggers = isset($_REQUEST['triggers']) && is_array($_REQUEST['triggers']) ? $_REQUEST['triggers'] : [];

		$updatedTriggers = $target->setTriggers($triggers);

		$updatedTemplates = array();
		foreach ($templates as $templateData)
		{
			$template = new \Bitrix\Bizproc\Automation\Engine\Template($documentType, $templateData['DOCUMENT_STATUS']);

			if (empty($templateData['IS_EXTERNAL_MODIFIED']))
			{
				$robots = isset($templateData['ROBOTS']) && is_array($templateData['ROBOTS']) ? $templateData['ROBOTS'] : array();

				$result = $template->save($robots, $curUser->GetID());
				if ($result->isSuccess())
				{
					$updatedTemplates[] = $template->toArray();
				}
				else
				{
					$errors = array_merge($errors, $result->getErrorMessages());
				}
			}
			else
			{
				$updatedTemplates[] = $template->toArray();
			}
		}

		$sendResponse(array('templates' => $updatedTemplates, 'triggers' => $updatedTriggers), $errors);

		break;

	case 'GET_DESTINATION_DATA':
		//Check permissions.
		$checkConfigWritePerms();

		CBitrixComponent::includeComponentClass('bitrix:bizproc.automation');
		$result = \BizprocAutomationComponent::getDestinationData($documentType);
		$sendResponse($result);
		break;

	case 'GET_LOG':
		//Check permissions.
		if (empty($documentId))
		{
			$sendError('Wrong document id.');
		}

		$checkReadPerms($documentId);

		/** @var \Bitrix\Bizproc\Automation\Target\BaseTarget $target */
		$target = $documentService->createAutomationTarget($documentType);

		if (!$target)
		{
			$sendError('Wrong document type.');
		}

		$target->setDocumentId($documentId);
		$statusList = $target->getDocumentStatusList($documentCategoryId);
		$tracker = new \Bitrix\Bizproc\Automation\Tracker($target);

		$sendResponse(array('LOG' => $tracker->getLog(array_keys($statusList))));
		break;

	case 'GET_AVAILABLE_TRIGGERS':
		//Check permissions.
		$checkConfigWritePerms();

		/** @var \Bitrix\Bizproc\Automation\Target\BaseTarget $target */
		$target = $documentService->createAutomationTarget($documentType);

		if (!$target)
		{
			$sendError('Wrong document type.');
		}

		$sendResponse($target->getAvailableTriggers());
		break;

	default:
		$sendError('Unknown action!');
		break;
}