<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
use Bitrix\Main\Authentication\ApplicationPasswordTable as ApplicationPasswordTable;
use Bitrix\Main\Context;

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
{
	header('Access-Control-Allow-Methods: POST, OPTIONS');
	header('Access-Control-Max-Age: 60');
	header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
	die('');
}

define("BX_SKIP_USER_LIMIT_CHECK", true);
define("ADMIN_SECTION",false);
require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/login/helper.php");

if (!defined("BX_FORCE_DISABLE_SEPARATED_SESSION_MODE"))
{
	if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('%Bitrix24.Disk/([0-9.]+)%i', $_SERVER['HTTP_USER_AGENT']))
	{
		define("BX_FORCE_DISABLE_SEPARATED_SESSION_MODE", true);
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule('im'))
{
	sendResponse(["success" => false, "code" => "module_not_installed", "reason" => 'Im module is not installed'], "403 Forbidden");
	exit;
}

if (!IsModuleInstalled('bitrix24'))
{
	header('Access-Control-Allow-Origin: *');
}

if ($_POST['action'] != 'login')
{
	sendResponse(["success" => false, "code" => "method_not_permitted", "reason" => 'Method not permitted'], "403 Forbidden");
	exit;
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

	if (\Bitrix\Main\Loader::includeModule('bitrix24') && ($captchaInfo = CBitrix24::getStoredCaptcha()))
	{
		$answer["captchaCode"] = $captchaInfo["captchaCode"];
		$answer["captchaURL"] = $captchaInfo["captchaURL"];
	}
	elseif ($APPLICATION->NeedCAPTHAForLogin($_POST['login']))
	{
		$answer["captchaCode"] = $APPLICATION->CaptchaGetCode();
	}

	if(CModule::IncludeModule("security") && \Bitrix\Security\Mfa\Otp::isOtpRequired())
	{
		//user must enter OTP
		$answer["needOtp"] = true;
	}

	if ($result && $result["CODE"])
	{
		if ($result["CODE"] === 'ERROR_NETWORK')
		{
			$answer["code"] = "network_error";
			sendResponse($answer, "521 Internal Bitrix24.Network error");

			if (!empty($_POST['LOGIN']))
			{
				$dbRes = CUser::GetList('', '', array("LOGIN_EQUAL_EXACT" => $_POST['LOGIN']), array('FIELDS' => array('ID')));
				$arUser = $dbRes->fetch();
				if ($arUser)
				{
					$user = new CUser;
					$user->Update($arUser['ID'], ["LOGIN_ATTEMPTS" => 0]);
				}
			}

			exit;
		}

		$answer["code"] = $result["CODE"];
	}

	sendResponse($answer, "401 Unauthorized");
	exit;
}

if ($USER->IsAuthorized() && !isAccessAllowed())
{
	sendResponse(["success" => false, "code" => "blocked_type", "reason" => 'Access denied for this type of user'], "401 Unauthorized");
	exit;
}

if (
	\Bitrix\Main\Loader::includeModule('bitrix24') &&
	mb_strpos(Context::getCurrent()->getRequest()->getUserAgent(), 'Bitrix24.Disk') !== false &&
	\Bitrix\Bitrix24\Limits\User::isUserRestricted($USER->GetID())
)
{
	header('Access-Control-Allow-Origin: *');
	sendResponse(["success" => false, "code" => "restricted_access"], "401 Unauthorized");
	exit;
}

$answer = array(
	"success" => true,
	"desktopRevision" => \Bitrix\Im\Revision::getDesktop(),
	"userId" => $USER->GetID(),
	"sessionId" => session_id(),
	"bitrixSessionId" => bitrix_sessid()
);

if(
	($_POST['renew_password'] == 'y' || $_POST['otp'] <> '')
	&& $USER->GetParam("APPLICATION_ID") === null
)
{
	$code = '';
	if ($_POST['user_os_mark'] <> '')
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
		'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
		'CODE' => $code,
		'COMMENT' => GetMessage('DESKTOP_APP_GENERATOR'),
		'SYSCOMMENT' => GetMessage('DESKTOP_APP_TITE'),
	));
	if($res->isSuccess())
	{
		$answer["appPassword"] = $password;
	}
}

sendResponse($answer);
