<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteID = isset($_REQUEST['site'])? mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site']), 0, 2) : '';
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

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$action = $request->getPost('ajax_action');

if (empty($action))
	die('Unknown action!');

$APPLICATION->ShowAjaxHead();
$action = mb_strtoupper($action);

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

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

	echo \Bitrix\Main\Web\Json::encode($result);
	\Bitrix\Main\Application::getInstance()->end();
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

if ($action === 'GET_DESTINATION_DATA')
{
	$result = array('USERS' => array(), 'LAST' => array());
	if (CModule::includeModule('socialnetwork'))
	{
		$arStructure = CSocNetLogDestination::GetStucture(array());
		$result['DEPARTMENT'] = $arStructure['department'];
		$result['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$result['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		$result['DEST_SORT'] = CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "BIZPROC_DESTINATION",
		));

		CSocNetLogDestination::fillLastDestination(
			$result['DEST_SORT'],
			$result['LAST']
		);

		$destUser = array();
		foreach ($result["LAST"]["USERS"] as $value)
		{
			$destUser[] = str_replace("U", "", $value);
		}

		$result["USERS"] = \CSocNetLogDestination::getUsers(array("id" => $destUser));
	}
	$sendData($result);
}

$moduleId = $request->getPost('module_id');
$entity = $request->getPost('entity');
$paramDocumentType = $request->getPost('document_type');
$paramDocumentId = $request->getPost('document_id');

if (!$moduleId || !$entity || !$paramDocumentType || (!$paramDocumentId && $action !== 'CHECK_PARAMETERS'))
{
	$sendError('Invalid request data');
}

$documentType = array($moduleId, $entity, $paramDocumentType);
$documentId = $paramDocumentId ? array($moduleId, $entity, $paramDocumentId) : null;

$documentStates = CBPDocument::GetDocumentStates($documentType, $documentId);
$userGroups = $user->GetUserGroupArray();

switch ($action)
{
	case 'GET_TEMPLATES':
		$templates = \CBPDocument::getTemplatesForStart($user->getId(), $documentType, $documentId, array(
			"UserGroups" => $userGroups,
			"DocumentStates" => $documentStates
		));
		$sendData(array(
			'templates' => $templates
		));
	break;

	case 'START_WORKFLOW':

		$templateId = $request->getPost('template_id');

		if (!CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::StartWorkflow,
			$user->getId(),
			$documentId,
			array(
				"UserGroups" => $userGroups,
				"DocumentStates" => $documentStates,
				"WorkflowTemplateId" => $templateId)
		))
		{
			$sendError('Access Denied!');
		}

		$arWorkflowTemplate = CBPWorkflowTemplateLoader::GetList(
			array(),
			array(
				'ID' => $templateId,
				"DOCUMENT_TYPE" => $documentType,
				"ACTIVE" => "Y",
				'!AUTO_EXECUTE' => CBPDocumentEventType::Automation
			),
			false,
			false,
			array("ID", "NAME", "DESCRIPTION", "PARAMETERS")
		)->fetch();

		if (!$arWorkflowTemplate)
		{
			$sendError('Access Denied!');
		}

		$arWorkflowParameters = array();
		$arErrorsTmp = array();

		if (count($arWorkflowTemplate["PARAMETERS"]) > 0)
		{
			$arRequest = $_POST;

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

			$arWorkflowParameters = CBPWorkflowTemplateLoader::CheckWorkflowParameters(
				$arWorkflowTemplate["PARAMETERS"],
				$arRequest,
				$documentType,
				$arErrorsTmp
			);

			if (count($arErrorsTmp) > 0)
			{
				$sendError($arErrorsTmp[0]['message']);
			}
		}

		$arWorkflowParameters[CBPDocument::PARAM_TAGRET_USER] = "user_".$user->getId();
		$arWorkflowParameters[CBPDocument::PARAM_DOCUMENT_EVENT_TYPE] = CBPDocumentEventType::Manual;

		$wfId = CBPDocument::StartWorkflow(
			$templateId,
			$documentId,
			$arWorkflowParameters,
			$arErrorsTmp
		);

		if (count($arErrorsTmp) > 0)
		{
			$sendError($arErrorsTmp[0]['message']);
		}
		else
		{
			$sendData(array('workflow_id' => $wfId));
		}
	break;

	case 'CHECK_PARAMETERS':
		if (!CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::StartWorkflow,
			$user->getId(),
			$documentType,
			array(
				"UserGroups" => $userGroups,
				"DocumentStates" => $documentStates
			)
		))
		{
			$sendError('Access Denied!');
		}

		$eventType = $request->getPost('auto_execute_type');

		$arDocumentStates = CBPWorkflowTemplateLoader::GetDocumentTypeStates(
			$documentType, $eventType
		);

		$parametersValues = array();
		$errors = array();

		foreach ($arDocumentStates as $template)
		{
			if (count($template['TEMPLATE_PARAMETERS']) > 0)
			{
				$parametersValues[$template['TEMPLATE_ID']] = CBPDocument::StartWorkflowParametersValidate(
					$template['TEMPLATE_ID'],
					$template['TEMPLATE_PARAMETERS'],
					$documentType,
					$errors
				);
				if ($errors)
				{
					break;
				}
			}
		}

		if ($errors)
		{
			$sendError($errors[0]['message']);
		}

		$sendData(array('parameters' => CBPDocument::signParameters($parametersValues)));
	break;
}
$sendError('Unknown action!');