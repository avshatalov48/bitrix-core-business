<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule('webdav') && class_exists('CWebDavInterface'))
{
	$arResult['VALUE'] = $arResult['~VALUE'];
	CWebDavInterface::UserFieldView($arParams, $arResult);
}
?>
