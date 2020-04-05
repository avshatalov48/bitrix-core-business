<?php

namespace Bitrix\UI\Buttons;

use Bitrix\Main\Localization\Loc;

class SaveButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [
			'text' => Loc::getMessage('UI_BUTTONS_SAVE_BTN_TEXT'),
			'color' => Color::SUCCESS,
		];
	}
}