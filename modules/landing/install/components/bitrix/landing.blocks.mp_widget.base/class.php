<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class LandingBlocksMainpageWidgetBase extends CBitrixComponent
{
	private const BASE_CSS_VAR_PROPERTIES = [
		'COLOR_TEXT' => '--widget-color',
		'COLOR_HEADERS' => '--widget-color-h',
		'COLOR_HEADERS_V2' => '--widget-color-h-v2',
		'COLOR_BUTTON' => '--widget-color-button',
		'COLOR_BUTTON_V2' => '--widget-color-button-v2',
	];

	protected array $cssVarProperties = [];

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.blocks.mp_widget.base/templates/.default/script.js');
		Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.blocks.mp_widget.base/templates/.default/style.css');

		foreach (self::BASE_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}

		$this->view();
	}

	protected function view(): void
	{
		ob_start();
		$this->IncludeComponentTemplate();
		$template = ob_get_clean();

		echo
			'<div class="landing-widget-wrapper" style="'
				. $this->getCssVarPropertiesString()
			. '">'
				. $template
			. '</div>'
		;
	}

	protected function addCssVarProperty(string $property, string $cssVar): void
	{
		$this->cssVarProperties[$property] = $cssVar;
	}

	protected function getCssVarPropertiesString(): string
	{
		$result = '';

		foreach ($this->cssVarProperties as $property => $cssVar)
		{
			if (
				isset($this->arParams[$property])
				&& (string)$this->arParams[$property] !== ''
			)
			{
				$param = (string)$this->arParams[$property];
				$result .= "{$cssVar}: {$param};";
			}
		}

		return $result;
	}

	/**
	 * @param array|int $userId - id or ids of users
	 * @param array $avatarSize - array of sizes like [width, height]
	 * @return array - [userId => userData]
	 */
	protected static function getUserData(array|int $userId, array $avatarSize = [100, 100]): array
	{
		$query = \CUser::getList(
			'ID', 'ASC',
			[
				'ACTIVE' => 'Y',
				'ID' => implode('|', array_unique((array)$userId)),
				'!EXTERNAL_AUTH_ID' => \Bitrix\Main\UserTable::getExternalUserTypes(),
			],
			[
				'SELECT' => ['*'],
				'FIELDS' => ['*'],
			]
		);

		$data = [];
		while ($user = $query->Fetch())
		{
			$width = $avatarSize[0] ?? 100;
			$height = $avatarSize[1] ?? 100;

			$user['NAME'] = \CUser::FormatName(\CSite::GetNameFormat(), $user, true);
			$user['PERSONAL_PHOTO'] = [
				'FILE_ID' => $user['PERSONAL_PHOTO'],
				'IMG' => CFile::ResizeImageGet(
					$user['PERSONAL_PHOTO'],
					["width" => (int)$width, "height" => (int)$height],
					BX_RESIZE_IMAGE_EXACT,
					true
				),
			];

			$data[$user['ID']] = $user;
		}

		return $data;
	}

	/**
	 * Check var in arParams. If no exists, create with default val.
	 * @param int|string $var Variable.
	 * @param mixed $default Default value.
	 * @return void
	 */
	protected function checkParam(int|string $var, mixed $default): void
	{
		if (!isset($this->arParams[$var]))
		{
			$this->arParams[$var] = $default;
		}
		if (is_int($default))
		{
			$this->arParams[$var] = (int)$this->arParams[$var];
		}
		if (mb_substr($var, 0, 1) !== '~')
		{
			$this->checkParam('~' . $var, $default);
		}
	}

	/**
	 * Converts date and time from a given format to a string with the date.
	 * Accepts a date in the format "dd.mm.yyyy hh:mm:ss" and returns a string in the format "hh:mm dd month yyyy".
	 * If the date cannot be converted, returns null.
	 *
	 * @param string $inputDate Date and time in the format "dd.mm.yyyy hh:mm:ss".
	 *
	 * @return string|null String with the converted date in the specified format or null on conversion error.
	 */
	protected function convertDateFormat(string $inputDate, $convertedType = 'hidmy', $inputDateType = 'd.m.Y H:i:s'): ?string
	{
		$date = DateTime::createFromFormat($inputDateType, $inputDate);

		if (!$date)
		{
			return null;
		}

		$monthPhrase = Loc::getMessage('LANDING_MPWIDGET_MONTH_' . (int)$date->format('m'));
		$convertedDate = null;
		if ($convertedType === 'hidmy')
		{
			$convertedDate = $date->format('H:i') . ' ' . $date->format('d') . ' ' . $monthPhrase . ' ' . $date->format('Y');
		}
		if ($convertedType === 'dmy')
		{
			$convertedDate = $date->format('d') . ' ' . $monthPhrase . ' ' . $date->format('Y');
		}
		if ($convertedType === 'dm')
		{
			$convertedDate = $date->format('d') . ' ' . $monthPhrase;
		}
		if ($convertedType === 'H:i d.m.Y')
		{
			$convertedDate = $date->format($convertedType);
		}
		if ($convertedType === 'd.m.Y')
		{
			$convertedDate = $date->format($convertedType);
		}

		return $convertedDate;
	}

	protected function getNavigatorButtonPhrases(): array
	{
		return [
			'EXTEND' => Loc::getMessage('LANDING_WIDGET_BASE_CLASS_EXTEND_BUTTON_TEXT'),
			'VIEW_ALL' => Loc::getMessage('LANDING_WIDGET_BASE_CLASS_VIEW_ALL_BUTTON_TEXT'),
		];
	}
}