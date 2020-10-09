<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

$arTemplate = Array(
	"NAME" => \Bitrix\Main\Localization\Loc::getMessage("LEARNING_TEMPLATE_DESCRIPTION_NAME"),
	"DESCRIPTION" => \Bitrix\Main\Localization\Loc::getMessage("LEARNING_TEMPLATE_DESCRIPTION_DESC")
);
?>