<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var $this \CBitrixComponentTemplate */
/** @var \CAllMain $APPLICATION */
/** @var array $arResult*/
/** @var array $arParams*/

if (!empty($arParams['CONTENT_VIEW_TARGET']))
{
	$APPLICATION->ShowViewContent($arParams['CONTENT_VIEW_TARGET']);
}
elseif (!empty($arParams['~CONTENT']))
{
	echo $arParams['~CONTENT'];
}
else
{
	$APPLICATION->IncludeComponent(
		$arParams['POPUP_COMPONENT_NAME'],
		$arParams['POPUP_COMPONENT_TEMPLATE_NAME'],
		$arParams['POPUP_COMPONENT_PARAMS'],
		$arParams['POPUP_COMPONENT_PARENT']
	);
}