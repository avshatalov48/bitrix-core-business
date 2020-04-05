<?php

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

global $APPLICATION, $USER;

$SITE_ID = '';
if (isset($_REQUEST["SITE_ID"]) && is_string($_REQUEST["SITE_ID"]))
	$SITE_ID = substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["SITE_ID"]), 0, 2);

if ($SITE_ID != '')
	define("SITE_ID", $SITE_ID);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

if (!check_bitrix_sessid() || !$USER->IsAuthorized())
	die();

if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'delegate')
{
	CModule::IncludeModule('bizproc');
	$isAdmin = $USER->IsAdmin() || (CModule::IncludeModule('bitrix24') && CBitrix24::IsPortalAdmin($USER->GetID()));
	$errors = array();

	$allowedDelegationType = array(CBPTaskDelegationType::AllEmployees);
	if ($isAdmin)
	{
		$allowedDelegationType = null;
	}
	elseif (CBPHelper::checkUserSubordination($USER->GetID(), $_REQUEST['to_user_id']))
	{
		$allowedDelegationType[] = CBPTaskDelegationType::Subordinate;
	}

	if (!empty($_REQUEST['task_id']) && !empty($_REQUEST['from_user_id']) && !empty($_REQUEST['to_user_id']))
	{
		if (!CBPDocument::delegateTasks(
			$_REQUEST['from_user_id'],
			$_REQUEST['to_user_id'],
			$_REQUEST['task_id'],
			$errors,
			$allowedDelegationType
		))
		{
			$errors[] = GetMessage('BPAT_DELEGATE_NOTASKS');
		}
	}
	else
	{
		$errors[] = 'System error';
	}

	$message = $errors? $errors[0] : GetMessage('BPAT_DELEGATE_SUCCESS');
	echo CUtil::PhpToJSObject(array('message' => $message, 'success' => empty($errors)));
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