<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!isset($_REQUEST["ldap_user_id"]) || strlen($_REQUEST["ldap_user_id"]) != 32)
	LocalRedirect("/");

IncludeModuleLangFile(__FILE__);

$bCgi = (stristr(php_sapi_name(), "cgi") !== false);

if ($USER->IsAuthorized())
{
	if (isset($_REQUEST["back_url"]) && strlen($_REQUEST["back_url"]) > 0)
		LocalRedirect($_REQUEST["back_url"]);
	else
		LocalRedirect("/");
}
elseif (!$bCgi)
{
	$USER->RequiredHTTPAuthBasic($Realm = "Bitrix");
	echo GetMessage("LDAP_ENTER_LOGIN_AND_PASS");
	die();
}
else
{
	$APPLICATION->AuthForm(GetMessage("LDAP_ENTER_LOGIN_AND_PASS2"));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>