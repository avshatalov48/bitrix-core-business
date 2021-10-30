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

/**
 * Typographic settings
 */
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
			'CODE_H' => new Field\Text('CODE_H', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_BASE_2'),
				'default' => 'Open Sans',
			]),
			'CODE' => new Field\Text('CODE', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_BASE_2'),
				'default' => 'Open Sans',
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
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_WEIGHT_2'),
				'default' => '400',
				'options' => self::getFontWeightOptions(),
				'help' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_WEIGHT_HELP_2'),
			]),
			'FONT_WEIGHT_H' => new Field\Select('FONT_WEIGHT_H', [
				'title' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_WEIGHT_H_2'),
				'default' => '400',
				'options' => self::getFontWeightOptions(),
				'help' => Loc::getMessage('LNDNGHOOK_THEMEFONTS_FONT_WEIGHT_HELP_H'),
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
			'100' => '100',
			'200' => '200',
			'300' => '300',
			'400' => '400',
			'500' => '500',
			'600' => '600',
			'700' => '700',
			'800' => '800',
			'900' => '900',
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

		$this->setThemeFont();
		$this->setHFontTheme();
		$this->setSize();
		$this->setColors();
		$this->setTypo();
	}

	protected function getField(string $name): ?string
	{
		if ($field = $this->fields[$name]->getValue())
		{
			return HtmlFilter::encode(trim($field));
		}

		return self::getDefaultValues()[$name];
	}

	protected static function getDefaultValues(): array
	{
		return [
			'CODE' => 'Open Sans',
			'CODE_H' => 'Open Sans',
			'SIZE' => '1',
			'LINE_HEIGHT' => '1.6',
			'FONT_WEIGHT' => '400',
			'FONT_WEIGHT_H' => '400',
		];
	}

	/**
	 * Set the main font to the page
	 */
	protected function setThemeFont(): void
	{
		$font = $this->getField('CODE');
		$font = self::convertFont($font);

		$assets = Assets\Manager::getInstance();
		if ($this->fields['CODE']->getValue() !== null)
		{
			$assets->addString(
				"<style>
					body {
						--landing-font-family: {$font}
					}
				</style>"
			);
		}

		$assets->addString(self::getFontLink($font));
		$assets->addString(
			'<style>
				body {
					font-weight: 400;
					font-family: ' . $font . ';
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
					-moz-font-feature-settings: "liga", "kern";
					text-rendering: optimizelegibility;
				}
			</style>'
		);
	}

	/**
	 * Set fonts for headers, add style string
	 */
	protected function setHFontTheme(): void
	{
		$font = $this->getField('CODE_H');
		$font = self::convertFont($font);

		$assets = Assets\Manager::getInstance();
		$assets->addString(self::getFontLink($font));
		$assets->addString(
			'<style>
				h1, h2, h3, h4, h5, h6 {
					font-family: ' . $font . ';
				}
			</style>'
		);
	}

	/**
	 * Convert to correct font name
	 * @param string $fontName
	 * @return string
	 */
	protected static function convertFont(string $fontName): string
	{
		$fontName = str_replace(['g-font-', '-', 'ibm ', 'pt '], ['', ' ', 'IBM ', 'PT '], $fontName);

		$pattern = [
			'/sc(?:(?![a-z]))/i',
			'/jp(?:(?![a-z]))/i',
			'/kr(?:(?![a-z]))/i',
			'/tc(?:(?![a-z]))/i',
		];
		$replace = ['SC', 'JP', 'KR', 'TC'];
		$fontNameNew = preg_filter($pattern, $replace, $fontName);
		if ($fontNameNew)
		{
			$fontName = $fontNameNew;
		}

		$fontName = ucwords($fontName);

		return $fontName;
	}

	protected static function getFontLink(string $font): string
	{
		$fontLink = '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=';
		$fontLink .= str_replace(" ", "+", $font);
		$fontLink .= ':wght@100;200;300;400;500;600;700;800;900">';

		return $fontLink;
	}

	/**
	 * Set base font size for ALL text in body, add style string
	 */
	protected function setSize(): void
	{
		$size = $this->getField('SIZE');

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
	 * Set colors for text and headers
	 */
	protected function setColors(): void
	{
		$color = $this->getField('COLOR');
		$hColor = $this->getField('COLOR_H');
		$css = '';

		if ($color && Theme::isHex($color))
		{
			$css .= "--theme-color-main: {$color} !important;";
		}
		if ($hColor && Theme::isHex($hColor))
		{
			$css .= "--theme-color-title: {$hColor} !important;";
		}

		if (!empty($css))
		{
			Asset::getInstance()->addString(
				"<style>:root {{$css}}</style>",
				false,
				AssetLocation::BEFORE_CSS
			)
			;
		}
	}

	/**
	 * Set weight and line-height for text and headers
	 */
	protected function setTypo(): void
	{
		$lineHeight = $this->getField('LINE_HEIGHT');
		$fontWeight = $this->getField('FONT_WEIGHT');
		$hFontWeight = $this->getField('FONT_WEIGHT_H');

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
				'CODE' => 'Alegreya Sans',
				'CODE_H' => 'Alegreya Sans',
				'SIZE' => '1.14286',
			],
			'2business' => [
				'CODE' => 'Roboto',
				'CODE_H' => 'Roboto',
				'SIZE' => '1',
			],
			'3corporate' => [
				'CODE' => 'Roboto',
				'CODE_H' => 'Roboto',
				'SIZE' => '1',
			],
			'accounting' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1',
			],
			'agency' => [
				'CODE' => 'Roboto',
				'CODE_H' => 'Roboto',
				'SIZE' => '1',
			],
			'app' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1.14286',
			],
			'architecture' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1',
			],
			'charity' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '0.92857',
			],
			'consulting' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1',
			],
			'courses' => [
				'CODE' => 'Alegreya Sans',
				'CODE_H' => 'Alegreya Sans',
				'SIZE' => '1',
			],
			'event' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1.14286',
			],
			'gym' => [
				'CODE' => 'Roboto',
				'CODE_H' => 'Roboto',
				'SIZE' => '1',
			],
			'lawyer' => [
				'CODE' => 'Roboto',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1',
			],
			'music' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '0.92857',
			],
			'photography' => [
				'CODE' => 'Roboto',
				'CODE_H' => 'Roboto',
				'SIZE' => '0.92857',
			],
			'real-estate' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1',
			],
			'restaurant' => [
				'CODE' => 'Montserrat',
				'CODE_H' => 'Montserrat',
				'SIZE' => '0.92857',
			],
			'shipping' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1',
			],
			'spa' => [
				'CODE' => 'Open Sans',
				'CODE_H' => 'Open Sans',
				'SIZE' => '1',
			],
			'travel' => [
				'CODE' => 'Roboto',
				'CODE_H' => 'Roboto',
				'SIZE' => '1',
			],
			'wedding' => [
				'CODE' => 'Montserrat',
				'CODE_H' => 'Montserrat',
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
			);

		while ($old = $queryOld->fetch())
		{
			// check exist
			$existing = HookDataTable::query()
				->addSelect('ID')
				->where('HOOK', 'THEMEFONTS')
				->where('ENTITY_ID', $old['ENTITY_ID'])
				->where('ENTITY_TYPE', $old['ENTITY_TYPE'])
				->where('PUBLIC', $old['PUBLIC'])
				->fetch();
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