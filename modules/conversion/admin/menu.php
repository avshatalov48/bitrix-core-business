<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight('conversion') == 'D')
{
	return false;
}
else
{
	$menu = array(
		array(
			'parent_menu' => 'global_menu_marketing',
			'section' => 'conversion',
			'sort' => 100,
			'text' => Loc::getMessage('CONVERSION_MENU_TEXT'),
			'title' => Loc::getMessage('CONVERSION_MENU_TITLE'),
			'icon' => 'conversion_pulse_menu_icon',
			'page_icon' => 'conversion_page_icon',
			'items_id' => 'menu_conversion_pulse',
			'url' => 'conversion_summary.php?lang='.LANGUAGE_ID,
			'more_url' => array(
				'conversion_detailed.php',
			),
//			'items' => array(
//				array(
//					'text' => Loc::getMessage('CONVERSION_MENU_SUMMARY_TEXT'),
//					'title' => Loc::getMessage('CONVERSION_MENU_SUMMARY_TEXT'),
//					'url' => 'conversion_summary.php?lang='.LANGUAGE_ID,
//				),
//			),
		),
	);

	if (ModuleManager::isModuleInstalled('sale'))
	{
		$menu []= array(
			'parent_menu' => 'global_menu_marketing',
			'section' => 'conversion',
			'sort' => 200,
			'text' => Loc::getMessage('CONVERSION_MENU2_TEXT'),
			'title' => Loc::getMessage('CONVERSION_MENU2_TITLE'),
			'icon' => 'conversion_model_menu_icon',
			'page_icon' => 'conversion_page_icon',
			'items_id' => 'menu_conversion_model',
			'url' => 'conversion_calc.php?lang='.LANGUAGE_ID,
		);
	}

	return $menu;
}
