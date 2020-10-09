<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (defined('NO_LANG_FILES') || !\Bitrix\Main\Loader::includeModule('translate'))
{
	return;
}
/** @global \CMain $APPLICATION */
if ($APPLICATION->GetGroupRight("translate") <= "D")
{
	return;
}

if (isset($_GET["show_lang_files"]) && in_array(mb_strtoupper($_GET["show_lang_files"]), ['Y', 'N']))
{
	$_SESSION["SHOW_LANG_FILES"] = mb_strtoupper($_GET["show_lang_files"]) === 'Y' ? 'Y' : 'N';
}

if (
	class_exists('\\Bitrix\\Translate\\Ui\\Panel') &&
	method_exists('\\Bitrix\\Translate\\Ui\\Panel', 'showLoadedFiles')
)
{
	\Bitrix\Translate\Ui\Panel::showLoadedFiles();
}