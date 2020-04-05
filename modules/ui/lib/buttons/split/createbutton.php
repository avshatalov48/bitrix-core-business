<?php

namespace Bitrix\UI\Buttons\Split;

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Buttons\Color;

class CreateButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [
			'text' => Loc::getMessage('UI_BUTTONS_CREATE_BTN_TEXT'),
			'color' => Color::SUCCESS,
		];
	}
}