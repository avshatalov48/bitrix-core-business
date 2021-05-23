<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

Manager::setPageTitle(Loc::getMessage('LANDING_TPL_BINDING_TITLE'));
$request = \bitrix\Main\HttpContext::getCurrent()->getRequest();

if (!empty($arResult['ERRORS']))
{
	showError(implode("\n", $arResult['ERRORS']));
	return;
}
?>

<?if ($template = $request->get('tpl')):?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.demo_preview',
		'.default',
		array(
			'CODE' => $template,
			'TYPE' => $arParams['TYPE'],
			'DONT_LEAVE_FRAME' => 'Y',
			'BINDING_TYPE' => 'GROUP',
			'BINDING_ID' => $arParams['GROUP_ID']
		),
		$component
	);?>

<?else:?>

	<?$APPLICATION->IncludeComponent(
		'bitrix:landing.demo',
		'.default',
		array(
			'TYPE' => $arParams['TYPE'],
			'PAGE_URL_LANDING_VIEW' => $arParams['PATH_AFTER_CREATE'],
			'DONT_LEAVE_FRAME' => 'Y',
			'BINDING_TYPE' => 'GROUP',
			'BINDING_ID' => $arParams['GROUP_ID']
		),
		$component
	);?>

<?endif;?>
