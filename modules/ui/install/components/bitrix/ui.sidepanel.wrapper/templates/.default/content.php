<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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
	foreach ($arResult['SLIDER_COMPONENT_NAME_LIST'] as $key => $componentName)
	{
		$APPLICATION->IncludeComponent(
			$componentName,
			$arResult['SLIDER_COMPONENT_TEMPLATE_LIST'][$key],
			$arResult['SLIDER_COMPONENT_PARAMS_LIST'][$key],
			$arParams['POPUP_COMPONENT_PARENT']
		);
	}
}
