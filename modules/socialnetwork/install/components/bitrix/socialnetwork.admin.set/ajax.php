<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);

$site_id = (isset($_POST["site"]) && is_string($_POST["site"])) ? trim($_POST["site"]) : "";
$site_id = substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$rsSite = CSite::GetByID($site_id);
if ($arSite = $rsSite->Fetch())
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
else
	define("LANGUAGE_ID", "en");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if (!CModule::IncludeModule("socialnetwork"))
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SONET_MODULE_NOT_INSTALLED'));
	die();
}

if (!$GLOBALS["USER"]->IsAuthorized())
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'CURRENT_USER_NOT_AUTH'));
	die();
}

if (check_bitrix_sessid())
{

	if (CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false))
	{
		if ($_POST["ACTION"] == "SET")
		{
			if (isset($_SESSION["SONET_ADMIN"]))
				unset($_SESSION["SONET_ADMIN"]);
			else
				$_SESSION["SONET_ADMIN"] = "Y";
		}
		echo CUtil::PhpToJsObject(Array('SUCCESS' => 'Y'));
	}
	else
		echo CUtil::PhpToJsObject(Array('ERROR' => 'CURRENT_USER_NOT_ADMIN'));
	
}
else
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>