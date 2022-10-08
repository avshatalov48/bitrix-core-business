<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Socialnetwork\ComponentHelper;

if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
{
	return [];
}

return [
	'css' => './css/slider.css',
	'js' => '/bitrix/js/socialnetwork/slider/socialnetwork.slider.js',
	'lang_additional' => [
		'SONET_SLIDER_USER_SEF' => ComponentHelper::getUserSEFUrl(),
		'SONET_SLIDER_GROUP_SEF' => ComponentHelper::getWorkgroupSEFUrl(),
		'SONET_SLIDER_SITE_TEMPLATE_ID' => SITE_TEMPLATE_ID,
		'SONET_SLIDER_INTRANET_INSTALLED' => (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') ? 'Y' : 'N'),
	],
	'rel' => [ 'sidepanel', 'socialnetwork.common', 'ui.fonts.opensans' ],
];
