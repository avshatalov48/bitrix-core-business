<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Socialnetwork\ComponentHelper;

\Bitrix\Main\Loader::includeModule('socialnetwork');

return array(
	'js' => '/bitrix/js/socialnetwork/slider/socialnetwork.slider.js',
	'lang_additional' => array(
		'SONET_SLIDER_USER_SEF' => ComponentHelper::getUserSEFUrl(),
		'SONET_SLIDER_GROUP_SEF' => ComponentHelper::getWorkgroupSEFUrl(),
	),
	'rel' => array('sidepanel', 'socialnetwork.common')
);