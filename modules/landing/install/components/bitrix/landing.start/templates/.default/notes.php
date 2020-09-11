<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'landing-slider-no-background');

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$createMode = $request->get('create') == 'Y';

if ($template = $request->get('tpl'))
{
	$APPLICATION->includeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:landing.demo_preview',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '.default',
			'POPUP_COMPONENT_PARAMS' => [
				'CODE' => $template,
				'TYPE' => $arParams['TYPE'],
				'DISABLE_REDIRECT' => 'Y',
				'DONT_LEAVE_FRAME' => 'Y'
			],
			'POPUP_COMPONENT_PARENT' => $component,
			'USE_PADDING' => false,
			'PAGE_MODE' => false
		]
	);
}
else if ($createMode)
{
	$APPLICATION->includeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:landing.demo',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '.default',
			'POPUP_COMPONENT_PARAMS' => [
				'TYPE' => $arParams['TYPE'],
				'DISABLE_REDIRECT' => 'Y'
			],
			'POPUP_COMPONENT_PARENT' => $component,
			'USE_PADDING' => false,
			'PAGE_MODE' => false
		]
	);
}
else
{
	$APPLICATION->includeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:landing.empty',
			'POPUP_COMPONENT_TEMPLATE_NAME' => 'notes',
			'POPUP_COMPONENT_PARAMS' => [
				'TYPE' => $arParams['TYPE'],
				'SITE_ID' => $arResult['VARS']['site_edit']
			],
			'POPUP_COMPONENT_PARENT' => $component,
			'USE_PADDING' => false,
			'PAGE_MODE' => false
		]
	);
}