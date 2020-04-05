<?
define("BX_IM_FULLSCREEN", true);
define("EXTRANET_NO_REDIRECT", true);

if (isset($_GET['alias']))
{
	$widgetUserLangPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/im/lang/';
	if (
		isset($_GET['widget_user_lang']) 
		&& preg_match("/^[a-z]{2,2}$/", $_GET['widget_user_lang']) 
		&& strlen($_GET['widget_user_lang']) == 2 
		&& @is_dir($widgetUserLangPath . $_GET['widget_user_lang'])
	)
	{
		setcookie("WIDGET_USER_LANG", $_GET['widget_user_lang'], time()+9999999, "/");
		define("LANGUAGE_ID", $_GET['widget_user_lang']);
	}
	elseif (
		isset($_COOKIE['WIDGET_USER_LANG'])
		&& preg_match("/^[a-z]{2,2}$/", $_COOKIE['WIDGET_USER_LANG'])
		&& strlen($_COOKIE['WIDGET_USER_LANG']) == 2 
		&& @is_dir($widgetUserLangPath . $_COOKIE['WIDGET_USER_LANG'])
	)
	{
		define("LANGUAGE_ID", $_COOKIE['WIDGET_USER_LANG']);
	}

	define("BX_SKIP_SESSION_EXPAND", true);
	if (!isset($_GET['iframe']))
	{
		define("BX_PULL_SKIP_INIT", true);
	}
	define("BX_PULL_COMMAND_PATH", "/desktop_app/pull.ajax.php");
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public/desktop_app/router.php");

$APPLICATION->SetTitle(GetMessage("IM_ROUTER_PAGE_TITLE"));
$APPLICATION->IncludeComponent("bitrix:im.router", "", Array(), false, Array("HIDE_ICONS" => "Y"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
