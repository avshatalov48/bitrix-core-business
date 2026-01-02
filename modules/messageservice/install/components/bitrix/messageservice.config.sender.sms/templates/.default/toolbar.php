<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

if (($showToolbar ?? false) || $sender->isRegistered())
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton([
		'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
		'icon' => \Bitrix\UI\Buttons\Icon::SETTING,
		'dropdown' => false,
		'menu' => [
			'items' => [
				[
					'text' => Loc::getMessage('MESSAGESERVICE_CONFIG_SENDER_SMS_CLEAR_OPTIONS'),
					'onclick' => new \Bitrix\UI\Buttons\JsCode('BX.MessageService.ConfigSenderSms&&BX.MessageService.ConfigSenderSms.clearOptions?BX.MessageService.ConfigSenderSms.clearOptions():null;')
				]
			]
		]
	]);
}
