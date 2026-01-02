<?php

namespace Bitrix\UI\Buttons;

use Bitrix\Main\Localization\Loc;

class FeedbackButton extends Button
{
	/**
	 * @return array
	 */
	protected function getDefaultParameters(): array
	{
		return [
			'text' => Loc::getMessage('UI_BUTTONS_FEEDBACK_BTN_TEXT'),
			'color' => Color::LIGHT_BORDER,
			'dataset' => [
				'toolbar-collapsed-icon' => Icon::MAIL,
			],
		];
	}

	protected function init(array $params = []): void
	{
		$params['highlight'] = (bool)($params['highlight'] ?? false);
		if ($params['highlight'])
		{
			$params['styles'] ??= [];
			$params['styles']['z-index'] = 140;
			$params['styles']['background-color'] = '#fff';
		}
		unset($params['highlight']);

		parent::init($params);
	}

	/**
	 * @return string
	 */
	public static function getJsClass(): string
	{
		return 'BX.UI.Button';
	}
}
