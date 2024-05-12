<?php

use Bitrix\Main\Localization\Loc;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/rest/install/public/einvoice/install/index.php');
$APPLICATION->SetTitle(Loc::getMessage('EINVOICE_INSTALL_TITLE'));

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:rest.einvoice.installer',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [],
		'USE_PADDING' => false,
		'PLAIN_VIEW' => true,
		'PAGE_MODE' => false,
		'USE_BACKGROUND_CONTENT' => true,
	]
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
