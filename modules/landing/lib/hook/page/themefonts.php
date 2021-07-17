<?php

namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Assets;
use Bitrix\Landing\Field;
use Bitrix\Landing\Hook;
use Bitrix\Landing\Internals\HookDataTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Text\HtmlFilter;

class ThemeFonts extends Hook\Page
{
	protected const BASE_HTML_SIZE = '14px';

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap(): array
	{
		return [
			'USE' => new Field\Checkbox('USE', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_USE_2'),
			]),
			'CODE_H' => new Field\Select('CODE_H', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_BASE_2'),
				'options' => self::getSelectOptions(),
			]),
			'CODE' => new Field\Select('CODE', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_BASE_2'),
				'options' => self::getSelectOptions(),
			]),
			'SIZE' => new Field\Select('SIZE', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_SIZE'),
				'default' => '1',
				'options' => [
					'0.92857' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_12'),
					'1' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_14'),
					'1.14286' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_16'),
				],
			]),
			'COLOR' => new Field\Text('COLOR', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_COLOR'),
				'help' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_COLOR_HELP'),
			]),
			'COLOR_H' => new Field\Text('COLOR_H', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_COLOR_H'),
				'help' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_COLOR_HELP_H'),
			]),
			'LINE_HEIGHT' => new Field\Select('LINE_HEIGHT', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_LINE_HEIGHT'),
				'default' => '1.6',
				'options' => self::getLineHeightOptions(),
			]),
			'FONT_WEIGHT' => new Field\Select('FONT_WEIGHT', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_WEIGHT'),
				'default' => '400',
				'options' => self::getFontWeightOptions(),
				'help' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_WEIGHT_HELP'),
			]),
			'FONT_WEIGHT_H' => new Field\Select('FONT_WEIGHT_H', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_WEIGHT_H'),
				'default' => '400',
				'options' => self::getFontWeightOptions(),
			]),
		];
	}

	protected static function getLineHeightOptions(): array
	{
		return [
			'0.7' => '0.7',
			'0.8' => '0.8',
			'0.9' => '0.9',
			'1' => '1',
			'1.1' => '1.1',
			'1.2' => '1.2',
			'1.3' => '1.3',
			'1.4' => '1.4',
			'1.5' => '1.5',
			'1.6' => '1.6',
			'1.7' => '1.7',
			'1.8' => '1.8',
			'2' => '2',
		];
	}

	protected static function getFontWeightOptions(): array
	{
		return [
			'300' => '300',
			'400' => '400',
			'500' => '500',
			'600' => '600',
			'700' => '700',
			'900' => '900',
		];
	}

	protected static function getSelectOptions(): ?array
	{
		// todo: add OS font (SanFrancisco -> Helvetica -> Roboto -> Arial). What if Roboto use separately by g-font-roboto?
		static $options = [];

		if (!empty($options))
		{
			return $options;
		}

		foreach (Hook\Page\Fonts::DEFAULT_FONTS as $fontClass => $font)
		{
			$options[$fontClass] = $font['name'];
		}

		return $options;
	}

	protected static function getDefaultValues(): array
	{
		$defaultFont = array_keys(Hook\Page\Fonts::DEFAULT_FONTS)[0];

		return [
			'baseFont' => $defaultFont,
			'hFont' => $defaultFont,
			'size' => '1',
			'textLineHeight' => '1.6',
		];
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled(): bool
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

		if ($this->isPage())
		{
			return $this->fields['USE']->getValue() === 'Y';
		}

		return true; //always enable on site to default value
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec(): void
	{
		if ($this->execCustom())
		{
			return;
		}

		$baseFont = HtmlFilter::encode(trim($this->fields['CODE']->getValue()));
		$hFont = HtmlFilter::encode(trim($this->fields['CODE_H']->getValue()));
		$size = HtmlFilter::encode(trim($this->fields['SIZE']->getValue()));
		$lineHeight = HtmlFilter::encode(trim($this->fields['LINE_HEIGHT']->getValue()));
		// todo: array_merge
		if (!$baseFont || !$size || !$hFont || !$lineHeight)
		{
			$defaultValues = self::getDefaultValues();
			$baseFont = $baseFont ?: $defaultValues['baseFont'];
			$hFont = $hFont ?: $defaultValues['hFont'];
			$size = $size ?: $defaultValues['size'];
			$lineHeight = $lineHeight ?: $defaultValues['textLineHeight'];
		}
		$this->setBaseFont($baseFont);
		$this->setHFont($hFont);
		$this->setSize($size);

		$fontWeight = HtmlFilter::encode(trim($this->fields['FONT_WEIGHT']->getValue()));
		$hFontWeight = HtmlFilter::encode(trim($this->fields['FONT_WEIGHT_H']->getValue()));
		$this->setTypo($lineHeight, $fontWeight, $hFontWeight);

		$color = HtmlFilter::encode(trim($this->fields['COLOR']->getValue()));
		$hColor = HtmlFilter::encode(trim($this->fields['COLOR_H']->getValue()));
		$this->setColors($color, $hColor);
	}

	/**
	 * Set fonts for ALL text in body, add style string
	 * @param string $font
	 */
	protected function setBaseFont(string $font): void
	{
		$assets = Assets\Manager::getInstance();
		$assets->addString(Hook\Page\Fonts::outputDefaultFont($font));
		$assets->addString(
			'<style>
				body {
					font-weight: 400;
					font-family: ' . Hook\Page\Fonts::DEFAULT_FONTS[$font]['family'] . ';
					
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
					-moz-font-feature-settings: "liga", "kern";
					text-rendering: optimizelegibility;
				}
			</style>'
		);
	}

	protected function setColors(string $color, string $hColor): void
	{
		$css = '';
		if($color && Theme::isHex($color))
		{
			$css .= "--theme-color-main: {$color} !important;";
		}
		if($hColor && Theme::isHex($hColor))
		{
			$css .= "--theme-color-title: {$hColor} !important;";
		}

		if(!empty($css))
		{
			Asset::getInstance()->addString(
				"<style>:root {{$css}}</style>",
				false,
				AssetLocation::BEFORE_CSS
			);
		}
	}

	protected function setTypo(string $lineHeight, string $fontWeight, string $hFontWeight): void
	{
		$assets = Assets\Manager::getInstance();
		$assets->addString(
			"<style>
				body {
					line-height: {$lineHeight};
					font-weight: {$fontWeight};
				}
				
				.h1, .h2, .h3, .h4, .h5, .h6, .h7,
				h1, h2, h3, h4, h5, h6 {
					font-weight: {$hFontWeight};
				}
			</style>"
		);
	}

	/**
	 * Set fonts for headers, add style string
	 * @param string $font
	 */
	protected function setHFont(string $font): void
	{
		$assets = Assets\Manager::getInstance();
		$assets->addString(Hook\Page\Fonts::outputDefaultFont($font));
		$assets->addString(
			'<style>
				h1, h2, h3, h4, h5, h6 {
					font-family: ' . Hook\Page\Fonts::DEFAULT_FONTS[$font]['family'] . ';
				}
			</style>'
		);
	}

	/**
	 * Set base font size for ALL text in body, add style string
	 * @param float $size
	 */
	protected function setSize(float $size): void
	{
		$assets = Assets\Manager::getInstance();
		$assets->addString(
			'<style>
			html {font-size: ' . self::BASE_HTML_SIZE . ';}
			body {font-size: ' . $size . 'rem;}
			.g-font-size-default {font-size: ' . $size . 'rem;}
		</style>'
		);
	}

	/**
	 * Replace THEME_CODE_TYPO hook to THEMEFONTS hooks.
	 * @param int $lid Landing id.
	 * @param int $siteId Site id.
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function migrateFromTypoThemes(int $lid, int $siteId): void
	{
		$migrations = [
			'1construction' => [
				'CODE' => 'g-font-alegreya-sans',
				'CODE_H' => 'g-font-alegreya-sans',
				'SIZE' => '1.14286',
			],
			'2business' => [
				'CODE' => 'g-font-roboto',
				'CODE_H' => 'g-font-roboto',
				'SIZE' => '1',
			],
			'3corporate' => [
				'CODE' => 'g-font-roboto',
				'CODE_H' => 'g-font-roboto',
				'SIZE' => '1',
			],
			'accounting' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1',
			],
			'agency' => [
				'CODE' => 'g-font-roboto',
				'CODE_H' => 'g-font-roboto',
				'SIZE' => '1',
			],
			'app' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1.14286',
			],
			'architecture' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1',
			],
			'charity' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '0.92857',
			],
			'consulting' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1',
			],
			'courses' => [
				'CODE' => 'g-font-alegreya-sans',
				'CODE_H' => 'g-font-alegreya-sans',
				'SIZE' => '1',
			],
			'event' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1.14286',
			],
			'gym' => [
				'CODE' => 'g-font-roboto',
				'CODE_H' => 'g-font-roboto',
				'SIZE' => '1',
			],
			'lawyer' => [
				'CODE' => 'g-font-roboto',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1',
			],
			'music' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '0.92857',
			],
			'photography' => [
				'CODE' => 'g-font-roboto',
				'CODE_H' => 'g-font-roboto',
				'SIZE' => '0.92857',
			],
			'real-estate' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1',
			],
			'restaurant' => [
				'CODE' => 'g-font-montserrat',
				'CODE_H' => 'g-font-montserrat',
				'SIZE' => '0.92857',
			],
			'shipping' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1',
			],
			'spa' => [
				'CODE' => 'g-font-open-sans',
				'CODE_H' => 'g-font-open-sans',
				'SIZE' => '1',
			],
			'travel' => [
				'CODE' => 'g-font-roboto',
				'CODE_H' => 'g-font-roboto',
				'SIZE' => '1',
			],
			'wedding' => [
				'CODE' => 'g-font-montserrat',
				'CODE_H' => 'g-font-montserrat',
				'SIZE' => '1',
			],
		];

		$queryOld = HookDataTable::query()
			->addSelect('ID')
			->addSelect('VALUE')
			->addSelect('PUBLIC')
			->addSelect('ENTITY_ID')
			->addSelect('ENTITY_TYPE')
			->where('HOOK', 'THEME')
			->where('CODE', 'CODE_TYPO')
			->where(Query::filter()
				->logic('or')
				->where(Query::filter()
					->where('ENTITY_ID', $lid)
					->where('ENTITY_TYPE', Hook::ENTITY_TYPE_LANDING)
				)
				->where(Query::filter()
					->where('ENTITY_ID', $siteId)
					->where('ENTITY_TYPE', Hook::ENTITY_TYPE_SITE)
				)
			)
		;

		while ($old = $queryOld->fetch())
		{
			// check exist
			$existing = HookDataTable::query()
				->addSelect('ID')
				->where('HOOK', 'THEMEFONTS')
				->where('ENTITY_ID', $old['ENTITY_ID'])
				->where('ENTITY_TYPE', $old['ENTITY_TYPE'])
				->where('PUBLIC', $old['PUBLIC'])
				->fetch()
			;
			if (!$existing)
			{
				//process
				$migrations[$old['VALUE']]['USE'] = 'Y';
				foreach ($migrations[$old['VALUE']] as $code => $value)
				{
					HookDataTable::add(
						[
							'ENTITY_ID' => $old['ENTITY_ID'],
							'ENTITY_TYPE' => $old['ENTITY_TYPE'],
							'PUBLIC' => $old['PUBLIC'],
							'HOOK' => 'THEMEFONTS',
							'CODE' => $code,
							'VALUE' => $value,
						]
					);
				}
			}

			HookDataTable::delete($old['ID']);
		}
	}
}