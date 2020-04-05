<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.16--NAME'),
		'section' => array('video', 'forms'),
		'subtype' => 'form',
		'version' => '18.5.0',
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-video' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.16_NODE--VIDEO'),
			'type' => 'embed',
		),
	),
	'style' => array(
		'.landing-block-node-video-col' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.16_NODE--VIDEO'),
			'type' => 'animation',
		),
	),
	'assets' => array(
		'ext' => array('landing_inline_video', 'landing_form'),
	),
);