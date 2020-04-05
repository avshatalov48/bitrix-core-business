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

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

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

if ($action === 'GET_DESTINATION_DATA')
{
	$result = array('users' => array(), 'last' => array());
	if (CModule::includeModule('socialnetwork'))
	{
		$arStructure = CSocNetLogDestination::GetStucture(array());
		$result['department'] = $arStructure['department'];
		$result['departmentRelation'] = $arStructure['department_relation'];

		$result['destSort'] = CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "BIZPROC_USER_SELECTOR",
		));

		CSocNetLogDestination::fillLastDestination(
			$result['destSort'],
			$result['last']
		);

		$users = array();
		if (isset($result["last"]["USERS"]) && is_array($result["last"]["USERS"]))
		{
			foreach ($result["last"]["USERS"] as $value)
			{
				$users[] = str_replace("U", "", $value);
			}
		}

		$result["users"] = \CSocNetLogDestination::getUsers(array("id" => $users));
	}
	$sendData($result);
}

$sendError('Unknown action!');