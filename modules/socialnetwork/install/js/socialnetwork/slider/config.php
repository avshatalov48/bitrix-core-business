<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
use Bitrix\Socialnetwork\ComponentHelper;

if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
{
	return [];
}

return [
	'js' => '/bitrix/js/socialnetwork/slider/socialnetwork.slider.js',
	'lang_additional' => [
		'SONET_SLIDER_USER_SEF' => ComponentHelper::getUserSEFUrl(),
		'SONET_SLIDER_GROUP_SEF' => ComponentHelper::getWorkgroupSEFUrl(),
	],
	'rel' => [ 'sidepanel', 'socialnetwork.common' ]
];