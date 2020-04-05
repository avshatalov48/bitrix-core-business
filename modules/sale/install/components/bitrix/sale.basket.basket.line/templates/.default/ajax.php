<?php

define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid() &&
	isset($_POST["siteId"]) && ctype_alnum($_POST["siteId"]) && strlen($_POST["siteId"]) == 2)
{
	$path = realpath(dirname(__FILE__));
	require_once "$path/../../class.php";

	$cart = new SaleBasketLineComponent ();
	$cart->initComponent ('bitrix:sale.basket.basket.line');
	$cart->includeComponentLang();

	$lang = LangSubst(LANGUAGE_ID);
	__IncludeLang("$path/lang/$lang/template.php");
//	IncludeTemplateLangFile(__FILE__);

	$APPLICATION->RestartBuffer();
	header('Content-Type: text/html; charset='.LANG_CHARSET);
	$cart->executeAjax($_POST["siteId"]);

	die();
}