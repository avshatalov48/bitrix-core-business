<?
if (!defined('CALL_AJAX_INIT'))
{
	define("CALL_AJAX_INIT", true);
	define("PUBLIC_AJAX_MODE", true);
	define("NO_KEEP_STATISTIC", "Y");
	define("NO_AGENT_STATISTIC","Y");
	define("NO_AGENT_CHECK", true);
	define("DisableEventsCheck", true);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
}

if (!CModule::IncludeModule("pull"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'PULL_MODULE_NOT_INSTALLED'));
	CMain::FinalActions();
	die();
}

if (intval($USER->GetID()) <= 0)
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'AUTHORIZE_ERROR'));
	CMain::FinalActions();
	die();
}

if (check_bitrix_sessid())
{
	$errorMessage = "";
	if ($_POST['COMMAND'] == 'signaling')
	{
		\Bitrix\Pull\Event::add(intval($_POST['USER_ID']), Array(
			'module_id' => 'ycp',
			'command' => 'call',
			'params' => Array(
				'senderId' => $USER->GetID(),
				'command' => 'signaling',
				'peer' => $_POST['PEER'],
			),
		));
	}
	else
	{
		\Bitrix\Pull\Event::add(intval($_POST['USER_ID']), Array(
			'module_id' => 'ycp',
			'command' => 'call',
			'params' => Array(
				'senderId' => $USER->GetID(),
				'command' => $_POST['COMMAND']
			),
		));
	}
	echo CUtil::PhpToJsObject(Array(
		'ERROR' => ''
	));
}
else
{
	echo CUtil::PhpToJsObject(Array(
		'BITRIX_SESSID' => bitrix_sessid(),
		'ERROR' => 'SESSION_ERROR'
	));
}
CMain::FinalActions();
die();