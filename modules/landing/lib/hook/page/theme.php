<?php

namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

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
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$code = \htmlspecialcharsbx(trim($this->fields['CODE']));
		\Bitrix\Landing\Manager::setThemeId($code);
	}
}
