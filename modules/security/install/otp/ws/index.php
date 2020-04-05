<?
header('Access-Control-Allow-Origin: *');
if($_SERVER["REQUEST_METHOD"]=="OPTIONS")
{
	header('Access-Control-Allow-Methods: POST, OPTIONS');
	header('Access-Control-Max-Age: 60');
	//header('Access-Control-Allow-Headers: *');
	header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
	die('');
}

define("NOT_CHECK_PERMISSIONS", true);
define("ADMIN_SECTION",false);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


if(($_POST['action']!='register' && $_POST['action']!='unregister') || $_POST['secret']=="")
{
	CHTTP::SetStatus("403 Forbidden");
	die();
}

if($USER->Login($_POST['login'], $_POST['password']) !== true)
{
	if($APPLICATION->NeedCAPTHAForLogin($_POST['login']))
	{
		$CAPTCHA_CODE = $APPLICATION->CaptchaGetCode();
		echo "{'captchaCode': '".$CAPTCHA_CODE."'};";
	}

	CHTTP::SetStatus("401 Unauthorized");
	die();
}


if(!CModule::IncludeModule("security"))
{
	CHTTP::SetStatus("403 Forbidden");
	$USER->Logout();
	die();
}

if(!\Bitrix\Security\Mfa\Otp::isOtpEnabled())
{
	CHTTP::SetStatus("403 Forbidden");
	$USER->Logout();
	die();
}

if($_POST['action']!='register')
	$_POST['secret']="";

$isUpdated = CSecurityUser::update(array(
	"USER_ID" => $USER->GetID(),
	"SECRET" => $_POST['secret'],
	"ACTIVE" => "Y",
	"TYPE" => \Bitrix\Security\Mfa\Otp::TYPE_HOTP // Bitrix.OTP use HOTP
));

if(!$isUpdated)
{
	//print_r($APPLICATION->GetException());
	CHTTP::SetStatus("403 Forbidden");
	$USER->Logout();
	die();
}

$USER->Logout();
?>