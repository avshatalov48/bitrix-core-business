<?php

namespace Bitrix\UI\Buttons;

class SettingsButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters()
	{
		return [
			'icon' => Icon::SETTING,
			'color' => Color::LIGHT_BORDER,
			'dropdown' => false,
		];
	}

	/**
	 * @return string
	 */
	public static function getJsClass()
	{
		return 'BX.UI.Button';
	}
}