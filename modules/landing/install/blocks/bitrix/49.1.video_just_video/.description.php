<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_49_JUST_VIDEO_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['video', 'recommended', 'widgets_video'],
		'dynamic' => false,
		'version' => '18.5.0',
		// old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	],
	'nodes' => [
		'.landing-block-node-embed' => [
			'name' => Loc::getMessage('LANDING_BLOCK_49_JUST_VIDEO_EMBED'),
			'type' => 'embed',
		],
	],
	'style' => [
		'block' => [],
		'nodes' => [
			'.landing-block-node-video' => [
				'name' => Loc::getMessage('LANDING_BLOCK_49_JUST_VIDEO'),
				'type' => ['orientation-free', 'video-scale'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_inline_video'],
	],
];