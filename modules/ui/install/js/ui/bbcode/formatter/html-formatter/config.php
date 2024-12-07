<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;

$allowedSchemes = Option::get(
	'main',
	'~parser_anchor_schemes',
	'http|https|news|ftp|aim|mailto|file|tel|callto|skype|viber',
);

$userProfileUrlTemplate = '';
$departmentUrlTemplate = '';
$projectUrlTemplate = '';
if (\Bitrix\Main\Loader::includeModule('socialnetwork'))
{
	$userProfileUrlTemplate = \Bitrix\Socialnetwork\Helper\Path::get('user_profile');
	$departmentUrlTemplate = \Bitrix\Socialnetwork\Helper\Path::get('department_path_template');
	$projectUrlTemplate = \Bitrix\Socialnetwork\Helper\Path::get('group_livefeed_path_template');
}

return [
	'css' => 'dist/html-formatter.bundle.css',
	'js' => 'dist/html-formatter.bundle.js',
	'rel' => [
		'ui.smiley',
		'ui.code-parser',
		'ui.bbcode.model',
		'ui.video-service',
		'ui.bbcode.formatter',
		'ui.typography',
		'main.core',
	],
	'settings' => [
		'linkSettings' => [
			'allowedSchemes' => $allowedSchemes,
			'defaultScheme' => 'https',
			'defaultTarget' => '_blank',
			'shortLink' => [
				'enabled' => true,
				'maxLength' => 40,
				'lastFragmentLength' => 10,
			],
		],
		'mention' => [
			'urlTemplate' => [
				'user' => $userProfileUrlTemplate,
				'project' => $projectUrlTemplate,
				'department' => $departmentUrlTemplate,
			],
		],
	],
	'skip_core' => false,
];
