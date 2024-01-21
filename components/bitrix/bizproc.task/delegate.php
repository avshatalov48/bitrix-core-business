<?php

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

global $APPLICATION, $USER;

$SITE_ID = '';
if (isset($_REQUEST["SITE_ID"]) && is_string($_REQUEST["SITE_ID"]))
{
	$SITE_ID = mb_substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["SITE_ID"]), 0, 2);
}

if ($SITE_ID != '')
{
	define("SITE_ID", $SITE_ID);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!check_bitrix_sessid() || !$USER->IsAuthorized())
{
	die();
}

if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'delegate')
{
	CModule::IncludeModule('bizproc');

	$taskId = !empty($_REQUEST['task_id']) ? $_REQUEST['task_id'] : [];
	$taskIds = is_array($taskId) ? $taskId : [$taskId];

	$fromUserId =
		isset($_REQUEST['from_user_id']) && is_numeric($_REQUEST['from_user_id'])
			? (int)$_REQUEST['from_user_id']
			: 0
	;
	$toUserId =
		isset($_REQUEST['to_user_id']) && is_numeric($_REQUEST['to_user_id'])
			? (int)$_REQUEST['to_user_id']
			: 0
	;
	$currentUserId = (int)$USER->GetID();

	$taskService = new \Bitrix\Bizproc\Api\Service\TaskService(
		new \Bitrix\Bizproc\Api\Service\TaskAccessService($currentUserId)
	);

	$request = new \Bitrix\Bizproc\Api\Request\TaskService\DelegateTasksRequest($taskIds, $fromUserId, $toUserId, $currentUserId);
	$delegateTasksResult = $taskService->delegateTasks($request);

	$errors = $delegateTasksResult->getErrorMessages();
	$message = $errors ? $errors[0] : $delegateTasksResult->getSuccessDelegateTaskMessage();

	echo CUtil::PhpToJSObject(['message' => $message, 'success' => empty($errors)]);
}
else
{
	$APPLICATION->ShowAjaxHead();
	$APPLICATION->IncludeComponent(
		'bitrix:intranet.user.selector.new',
		'.default',
		array(
			'MULTIPLE'            => 'N',

			'NAME'                => 'bp_task_delegate',
			//'INPUT_NAME' => 'bp_task_delegate',
			'SHOW_EXTRANET_USERS' => 'NONE',
			//'POPUP' => 'Y',
			'NAME_TEMPLATE'       => COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID),
			'ON_SELECT'           => 'BX.Bizproc.delegationOnSelect',
			//'ON_CHANGE'           => $onChangeFunctionName,
			'SITE_ID'             => SITE_ID,
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
