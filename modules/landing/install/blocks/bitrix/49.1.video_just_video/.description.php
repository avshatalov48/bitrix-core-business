<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_49_JUST_VIDEO_NAME'),
		'section' => array('video'),
		'version' => '18.5.0',
	),
	'nodes' => array(
		'.landing-block-node-embed' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_49_JUST_VIDEO_EMBED'),
			'type' => 'embed',
		),
	),
	'style' => array(),
	'assets' => array(
		'ext' => array('landing_inline_video'),
	),
);