<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var \LandingBindingGroupComponent $component */
/** @var array $arParams */

use Bitrix\Landing\Manager;
use Bitrix\Landing\Rights;
use Bitrix\Main\Localization\Loc;
use bitrix\Main\HttpContext;

Loc::loadMessages(__FILE__);

Manager::setPageTitle(Loc::getMessage('LANDING_TPL_BINDING_TITLE'));
$request = HttpContext::getCurrent()->getRequest();

if (!empty($arResult['ERRORS']))
{
	showError(implode("\n", $arResult['ERRORS']));
	return;
}

if ($template = $request->get('tpl'))
{
	$APPLICATION->includeComponent(
		'bitrix:landing.demo_preview',
		'.default',
		[
			'CODE' => $template,
			'TYPE' => $arParams['TYPE'],
			'DONT_LEAVE_FRAME' => 'Y',
			'BINDING_TYPE' => 'GROUP',
			'BINDING_ID' => $arParams['GROUP_ID']
		],
		$component
	);
}
else
{
	// yes, we can set off of access checking, because we already checked this in $component
	Rights::setGlobalOff();

	$APPLICATION->includeComponent(
		'bitrix:landing.demo',
		'.default',
		[
			'TYPE' => $arParams['TYPE'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PATH_AFTER_CREATE'],
			'DONT_LEAVE_FRAME' => 'Y',
			'BINDING_TYPE' => 'GROUP',
			'BINDING_ID' => $arParams['GROUP_ID']
		],
		$component
	);

	Rights::setGlobalOn();
}
