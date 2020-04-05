<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
use Bitrix\Main;
use Bitrix\Main\Authentication\ApplicationPasswordTable as ApplicationPasswordTable;

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
{
	header('Access-Control-Allow-Methods: POST, OPTIONS');
	header('Access-Control-Max-Age: 60');
	header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
	die('');
}

define("ADMIN_SECTION",false);
require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!IsModuleInstalled('bitrix24'))
{
	header('Access-Control-Allow-Origin: *');
}

if ($_POST['action'] != 'login')
{
	CHTTP::SetStatus("403 Forbidden");
	die();
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public/desktop_app/login/index.php");

$result = $USER->Login($_POST['login'], $_POST['password']);
if ($_POST['otp'])
{
	$result = $USER->LoginByOtp($_POST['otp']);
}

if ($result !== true || !$USER->IsAuthorized())
{
	if (IsModuleInstalled('bitrix24'))
	{
		header('Access-Control-Allow-Origin: *');
	}

	$answer = array(
		"success" => false,
	);

	if ($APPLICATION->NeedCAPTHAForLogin($_POST['login']))
	{
		$answer["captchaCode"] = $APPLICATION->CaptchaGetCode();
	}

	if(CModule::IncludeModule("security") && \Bitrix\Security\Mfa\Otp::isOtpRequired())
	{
		//user must enter OTP
		$answer["needOtp"] = true;
	}

	CHTTP::SetStatus("401 Unauthorized");
}
else
{
	$answer = array(
		"success" => true,
		"sessionId" => session_id(),
		"bitrixSessionId" => bitrix_sessid()
	);

	if(
		($_POST['renew_password'] == 'y' || $_POST['otp'] <> '')
		&& $USER->GetParam("APPLICATION_ID") === null
	)
	{
		$code = '';
		if (strlen($_POST['user_os_mark']) > 0)
		{
			$code = md5($_POST['user_os_mark'].$_POST['user_account']);
		}

		if ($code <> '')
		{
			$orm = ApplicationPasswordTable::getList(Array(
				'select' => Array('ID'),
				'filter' => Array(
					'=USER_ID' => $USER->GetID(),
					'=CODE' => $code
				)
			));
			if($row = $orm->fetch())
			{
				ApplicationPasswordTable::delete($row['ID']);
			}
		}

		$password = ApplicationPasswordTable::generatePassword();

		$res = ApplicationPasswordTable::add(array(
			'USER_ID' => $USER->GetID(),
			'APPLICATION_ID' => 'desktop',
			'PASSWORD' => $password,
			'DATE_CREATE' => new Main\Type\DateTime(),
			'CODE' => $code,
			'COMMENT' => GetMessage('DESKTOP_APP_GENERATOR'),
			'SYSCOMMENT' => GetMessage('DESKTOP_APP_TITE'),
		));
		if($res->isSuccess())
		{
			$answer["appPassword"] = $password;
		}
	}
}

if (isset($_REQUEST['json']) && $_REQUEST['json'] == 'y')
{
	header('Content-Type: application/json');
	echo Main\Web\Json::encode($answer);
}
else
{
	echo toJsObject($answer);
}

function toJsObject(array $answer)
{
	$answerParts = array();
	foreach($answer as $attr => $value)
	{
		switch(gettype($value))
		{
			case 'string':
				$value = "'".CUtil::JSEscape($value)."'";
				break;
			case 'boolean':
				$value = ($value === true? 'true': 'false');
				break;
			case 'array':
				$value = toJsObject($value);
				break;
		}

		$answerParts[] = $attr.": ".$value;	
	}

	return "{".implode(", ", $answerParts)."}";
}