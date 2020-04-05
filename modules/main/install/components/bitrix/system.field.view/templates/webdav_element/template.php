<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule('webdav') && class_exists('CWebDavInterface'))
	CWebDavInterface::UserFieldView($arParams, $arResult);
?>
