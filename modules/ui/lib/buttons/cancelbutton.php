<?php
namespace Bitrix\UI\Buttons;


use Bitrix\Main\Localization\Loc;

class CancelButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [
			'text' => Loc::getMessage('UI_BUTTONS_CANCEL_BTN_TEXT'),
			'color' => Color::LINK,
		];
	}
}