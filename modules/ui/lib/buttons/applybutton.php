<?php

namespace Bitrix\UI\Buttons;

use Bitrix\Main\Localization\Loc;

class ApplyButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [
			'text' => Loc::getMessage('UI_BUTTONS_APPLY_BTN_TEXT'),
			'color' => Color::LIGHT_BORDER,
		];
	}
}