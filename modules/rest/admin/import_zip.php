<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$request = Application::getInstance()->getContext()->getRequest();

global $APPLICATION;
global $USER;

if (!$USER->isAdmin())
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

Loader::includeModule('rest');

$APPLICATION->showHeadStrings();
$APPLICATION->showHeadScripts();
$APPLICATION->showCSS();

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.configuration.import',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '.default',
		'POPUP_COMPONENT_PARAMS' => [
			'ZIP_ID' => (int)$request->get('id'),
			'ADDITIONAL' => $request->get('additional'),
			'MODE' => 'ZIP',
			'SET_TITLE' => 'Y',
		],
		'USE_PADDING' => false,
		'PAGE_MODE' => false,
		'USE_UI_TOOLBAR' => 'N',
		'PLAIN_VIEW' => \CRestUtil::isSlider() ? 'Y' : 'N'
	]
);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
