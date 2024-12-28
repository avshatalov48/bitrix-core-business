<?php

use Bitrix\Calendar\Integration\SocialNetwork\Collab\UserCollabs;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/collabmanager.bundle.css',
	'js' => 'dist/collabmanager.bundle.js',
	'rel' => [
		'main.core',
	],
	'settings' => [
		'collabs' => array_values(UserCollabs::getInstance()->get(\CCalendar::GetUserId())),
	],
	'skip_core' => false,
];
