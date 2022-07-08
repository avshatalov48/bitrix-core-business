<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:mail.usersignature.edit',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $arResult,
		'USE_UI_TOOLBAR' => 'N',
		'USE_PADDING' => false,
		'PLAIN_VIEW' => false,
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => "/mail/signatures",
		'BUTTONS' => [
			[
				'TYPE' => 'save',
				'ONCLICK' => 'BX.Mail.UserSignature.Edit.save(true)',
			],
			[
				'TYPE' => 'close',
			]
		]
	]
);