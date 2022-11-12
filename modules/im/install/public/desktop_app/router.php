<?php
define("BX_SKIP_USER_LIMIT_CHECK", true);
if (isset($_GET['alias']))
{
	define("BX_IM_FULLSCREEN", true);
	define("EXTRANET_NO_REDIRECT", true);

	$widgetUserLangPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/im/lang/';
	if (
		isset($_GET['widget_user_lang'])
		&& preg_match("/^[a-z]{2,2}$/", $_GET['widget_user_lang'])
		&& mb_strlen($_GET['widget_user_lang']) == 2
		&& @is_dir($widgetUserLangPath . $_GET['widget_user_lang'])
	)
	{
		setcookie("WIDGET_USER_LANG", $_GET['widget_user_lang'], time()+9999999, "/");
		define("LANGUAGE_ID", $_GET['widget_user_lang']);
	}
	elseif (
		isset($_COOKIE['WIDGET_USER_LANG'])
		&& preg_match("/^[a-z]{2,2}$/", $_COOKIE['WIDGET_USER_LANG'])
		&& mb_strlen($_COOKIE['WIDGET_USER_LANG']) == 2
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

	if (isset($_GET['videoconf'], $_SERVER['HTTP_ACCEPT_LANGUAGE']) && strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) > 1)
	{
		$preferredLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		if (
			!defined("LANGUAGE_ID")
			&& preg_match("/^[a-z]{2}$/", $preferredLang)
			&& @is_dir($widgetUserLangPath . $preferredLang)
		)
		{
			define("LANGUAGE_ID", $preferredLang);
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/public/desktop_app/router.php");

$APPLICATION->SetTitle(GetMessage("IM_ROUTER_PAGE_TITLE"));
$APPLICATION->IncludeComponent("bitrix:im.router", "", Array(), false, Array("HIDE_ICONS" => "Y"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
