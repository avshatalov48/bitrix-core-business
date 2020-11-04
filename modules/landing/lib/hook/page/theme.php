<?php

namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Landing\Manager;

Loc::loadMessages(__FILE__);

class Theme extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'CODE' => new Field\Select('CODE', array(
				'title' => Loc::getMessage('LANDING_HOOK_THEMECODE_NEW'),
				'options' => array_merge(
					array(
						'' => array(
							'name' => Loc::getMessage('LANDING_HOOK_THEMECODE_DEF'),
							'color' => '#f0f0f0',
						)
					),
					self::getColorCodes()
				),
			)),
		);
	}

	/**
	 * Get all themes colors.
	 * @return array
	 */
	public static function getColorCodes()
	{
		static $colors = array();

		if (!empty($colors))
		{
			return $colors;
		}

		$colors = array(
			'2business' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE_BUSINESS_NEW'),
				'color' => '#3949a0',
				'base' => true
			),
			'gym' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-GYM'),
				'color' => '#6b7de0',
			),
			'3corporate' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE_CORPORATE_NEW'),
				'color' => '#6ab8ee',
				'base' => true
			),
			'app' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-APP'),
				'color' => '#4fd2c2',
				'base' => true
			),
			'consulting' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-CONSULTING'),
				'color' => '#21a79b',
			),
			'accounting' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-ACCOUNTING'),
				'color' => '#a5c33c',
				'base' => true
			),
			'courses' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-COURSES'),
				'color' => '#6bda95',
			),
			'spa' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-SPA'),
				'color' => '#9dba04',
			),
			'charity' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-CHARITY'),
				'color' => '#f5f219',
			),
			'1construction' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE_CONSTRUCTION_NEW'),
				'color' => '#f7b70b',
				'base' => true
			),
			'travel' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-TRAVEL'),
				'color' => '#ee4136',
			),
			'architecture' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-ARCHITECTURE'),
				'color' => '#c94645',
			),
			'event' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-EVENT'),
				'color' => '#f73859',
			),
			'lawyer' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-LAWYER'),
				'color' => '#e74c3c',
			),
			'music' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-MUSIC'),
				'color' => '#fe6466',
			),
			'real-estate' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-REALESTATE'),
				'color' => '#f74c3c',
				'base' => true
			),
			'restaurant' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-RESTAURANT'),
				'color' => '#e6125d',
			),
			'shipping' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-SHIPPING'),
				'color' => '#ff0000',
			),
			'agency' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-AGENCY'),
				'color' => '#fe6466',
			),
			'wedding' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-WEDDING'),
				'color' => '#d65779',
			),
			'photography' => array(
				'name' => Loc::getMessage('LANDING_HOOK_THEMECODE-PHOTOGRAPHY'),
				'color' => '#333333',
				'base' => true
			),
		);

		$event = new \Bitrix\Main\Event('landing', 'onGetThemeColors', array(
			'colors' => $colors
		));
		$event->send();
		foreach ($event->getResults() as $result)
		{
			if ($result->getType() != \Bitrix\Main\EventResult::ERROR)
			{
				if (($modified = $result->getModified()))
				{
					if (isset($modified['colors']))
					{
						$colors = $modified['colors'];
					}
				}
			}
		}

		if (
			!is_array($colors) ||
			empty($colors)
		)
		{
			$colors = [
				'1construction' => [
					'name' => Loc::getMessage('LANDING_HOOK_THEMECODE_CONSTRUCTION_NEW'),
					'color' => '#f7b70b',
					'base' => true
				]
			];
		}

		return $colors;
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return trim($this->fields['CODE']) != '';
	}

	/**
	 * @param string $hexColor
	 * @return array
	 */
	protected static function convertHexToRgb($hexColor)
	{
		if ($hexColor[0] !== '#')
		{
			$hexColor = '#'.$hexColor;
		}
		if (strlen($hexColor) === 4)
		{
			$hexColor = $hexColor[0]. $hexColor[1]. $hexColor[1]. $hexColor[2]. $hexColor[2]. $hexColor[3]. $hexColor[3];
		}
		if (!(strlen($hexColor) === 7))
		{
			$hexColor = '#34bcf2';
		}
		$red = hexdec(substr($hexColor, 1, 2));
		$green = hexdec(substr($hexColor, 3, 2));
		$blue = hexdec(substr($hexColor, 5, 2));
		return [$red, $green, $blue];
	}

	/**
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 * @return array
	 */
	protected static function convertRgbToHsl($red, $green, $blue)
	{
		$red /= 255;
		$green /= 255;
		$blue /= 255;
		$max = max($red, $green, $blue);
		$min = min($red, $green, $blue);
		$lightness = ($max + $min) / 2;
		$d = $max - $min;
		if($d === 0)
		{
			$hue = $saturate = 0;
		}
		else
		{
			$hue = 0;
			$saturate = $d / (1 - abs(2 * $lightness - 1));
			switch($max)
			{
				case $red:
					$hue = 60 * fmod((($green - $blue) / $d), 6);
					if ($blue > $green)
					{
						$hue += 360;
					}
					break;
				case $green:
					$hue = 60 * (($blue - $red) / $d + 2);
					break;
				case $blue:
					$hue = 60 * (($red - $green) / $d + 4);
					break;
				default:
					break;
			}
		}
		$hue = round($hue, 2);
		$saturate = round($saturate, 2) * 100;
		$lightness = round($lightness, 2) * 100;
		return [$hue, $saturate, $lightness];
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$code = \htmlspecialcharsbx(trim($this->fields['CODE']));
		Manager::setThemeId($code);

		$request = Application::getInstance()->getContext()->getRequest();
		$color = false;
		if ($request->get('color'))
		{
			$color = $request->get('color');
		}

		if (is_string($color))
		{
			if ($color[0] !== '#')
			{
				$color = '#'.$color;
			}

			$rgbColor = self::convertHexToRgb($color);
			$rgbTemplate = $rgbColor[0].', '.$rgbColor[1].', '.$rgbColor[2];

			$hslColor = self::convertRgbToHsl($rgbColor[0], $rgbColor[1],$rgbColor[2]);
			$hslaColorDarken1 = 'hsl('.$hslColor[0].', '.$hslColor[1].'%, '.min($hslColor[2]+2, 100).'%)';
			$hslaColorDarken2 = 'hsl('.$hslColor[0].', '.$hslColor[1].'%, '.min($hslColor[2]+5, 100).'%)';
			$hslaColorDarken3 = 'hsl('.$hslColor[0].', '.$hslColor[1].'%, '.min($hslColor[2]+10, 100).'%)';
			$hslaColorLighten1 = 'hsl('.$hslColor[0].', '.$hslColor[1].'%, '.max($hslColor[2]-10, 0).'%)';

			$colorMain = 'hsl('.$hslColor[0].', 20%, 20%)';
			$colorSecondary = 'hsl('.$hslColor[0].', 20%, 80%)';

			Asset::getInstance()->addString(
				'<style type="text/css">
					:root {
						--theme-color-primary: ' . $color . ';
						--theme-color-primary-darken-1: ' . $hslaColorDarken1 . ';
						--theme-color-primary-darken-2: ' . $hslaColorDarken2 . ';
						--theme-color-primary-darken-3: ' . $hslaColorDarken3 . ';
						--theme-color-primary-lighten-1: ' . $hslaColorLighten1 . ';
						--theme-color-primary-opacity-0_1: rgba('.$rgbTemplate.', 0.1);
						--theme-color-primary-opacity-0_2: rgba('.$rgbTemplate.', 0.2);
						--theme-color-primary-opacity-0_3: rgba('.$rgbTemplate.', 0.3);
						--theme-color-primary-opacity-0_4: rgba('.$rgbTemplate.', 0.4);
						--theme-color-primary-opacity-0_6: rgba('.$rgbTemplate.', 0.6);
						--theme-color-primary-opacity-0_8: rgba('.$rgbTemplate.', 0.8);
						--theme-color-primary-opacity-0_9: rgba('.$rgbTemplate.', 0.9);
						--theme-color-main: ' . $colorMain . ';
						--theme-color-secondary: ' . $colorSecondary . ';
				</style>', false, AssetLocation::BEFORE_CSS
			);
		}
	}
}
