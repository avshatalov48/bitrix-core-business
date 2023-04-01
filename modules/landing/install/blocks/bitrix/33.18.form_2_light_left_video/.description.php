<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.18--NAME'),
		'section' => array('video', 'forms'),
		'dynamic' => false,
		'subtype' => 'form',
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-video' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.18_NODE--VIDEO'),
			'type' => 'embed',
		),
	),
	'style' => array(
		'.landing-block-node-video-col' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.18_NODE--VIDEO'),
			'type' => 'animation',
		),
		'.landing-block-node-row' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33_18_NODE_BLOCK'),
			'type' => 'align-items',
		),
		'.landing-block-node-video-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.18_NODE--VIDEO'),
			'type' => ['orientation', 'video-scale'],
		],
	),
	'assets' => array(
		'ext' => array('landing_inline_video', 'landing_form'),
	),
);