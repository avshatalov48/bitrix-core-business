<?
if (!defined('PULL_AJAX_INIT'))
{
	define("PULL_AJAX_INIT", true);
	define("PUBLIC_AJAX_MODE", true);
	define("NO_AGENT_STATISTIC","Y");
	define("NO_AGENT_CHECK", true);
	define("NOT_CHECK_PERMISSIONS", true);
	define("DisableEventsCheck", true);
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
}
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

// NOTICE
// Before execute next code, execute file /module/pull/ajax_hit.php
// for skip onProlog events
if (!CModule::IncludeModule("pull"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'PULL_MODULE_IS_NOT_INSTALLED'));
	CMain::FinalActions();
	die();
}

if (defined('PULL_USER_ID'))
{
	$userId = PULL_USER_ID;
}
else if (!$USER->IsAuthorized() && intval($_SESSION["SESS_SEARCHER_ID"]) <= 0 && intval($_SESSION["SESS_GUEST_ID"]) > 0 && \CPullOptions::GetGuestStatus())
{
	$userId = intval($_SESSION["SESS_GUEST_ID"])*-1;
}
else
{
	if(!$USER->IsAuthorized())
	{
		$USER->LoginByCookies();
	}

	$userId = intval($USER->GetID());
	if ($userId <= 0)
	{
		// TODO need change AUTHORIZE ERROR callbacks
		//header("HTTP/1.0 401 Not Authorized");
		//header("Content-Type: application/x-javascript");
		//header("BX-Authorize: ".bitrix_sessid());

		echo CUtil::PhpToJsObject(Array(
			'ERROR' => 'AUTHORIZE_ERROR',
			'BITRIX_SESSID' => bitrix_sessid()
		));
		CMain::FinalActions();
		die();
	}
}

if (check_bitrix_sessid())
{
	if ($_POST['PULL_GET_CHANNEL'] == 'Y')
	{
		session_write_close();

		$arConfig = CPullChannel::GetConfig($userId, ($_POST['CACHE'] == 'Y'), $_POST['CACHE'] == 'Y'? false: true, ($_POST['MOBILE'] == 'Y'));
		if (is_array($arConfig))
		{
			echo CUtil::PhpToJsObject($arConfig);
		}
		else
		{
			echo CUtil::PhpToJsObject(Array('ERROR' => 'ERROR_OPEN_CHANNEL'));
		}
	}
	elseif ($_POST['PULL_UPDATE_WATCH'] == 'Y')
	{
		$arResult = CPullWatch::Extend($userId, $_POST['WATCH']);

		echo CUtil::PhpToJsObject(Array('RESULT' => $arResult, 'ERROR' => ''));
	}
	elseif ($_POST['PULL_UPDATE_STATE'] == 'Y')
	{
		$serverTime = date('c');
		$serverTimeUnix = microtime(true);
		$arMessage = CPullStack::Get($_POST['CHANNEL_ID'], intval($_POST['CHANNEL_LAST_ID']));

		if (!empty($counters))
		{
			$arMessage[] = Array(
				'module_id' => 'main',
				'command' => 'user_counter',
				'params' => $counters,
				'extra' => Array(
					'server_time' => $serverTime,
					'server_time_unix' => $serverTimeUnix,
					'server_name' => COption::GetOptionString('main', 'server_name', $_SERVER['SERVER_NAME']),
					'revision_web' => PULL_REVISION_WEB,
					'revision_mobile' => PULL_REVISION_MOBILE,
				),
			);
		}
		echo CUtil::PhpToJsObject(Array('MESSAGE' => $arMessage, 'ERROR' => ''));
	}
	else
	{
		echo CUtil::PhpToJsObject(Array('ERROR' => 'UNKNOWN_ERROR'));
	}
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