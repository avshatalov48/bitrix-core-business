<?php

namespace Bitrix\UI\Buttons;

use Bitrix\Main\Localization\Loc;

class SendButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [
			'text' => Loc::getMessage('UI_BUTTONS_SEND_BTN_TEXT'),
			'color' => Color::SUCCESS,
		];
	}
}