<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--NAME'),
		'section' => array('team'),
	),
	'cards' => array(
		'.landing-block-card-member' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER'),
			'label' => array('.landing-block-node-member-photo', '.landing-block-node-member-name'),
		),
	),
	'nodes' => array(
		'.landing-block-node-member-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_PHOTO'),
			'type' => 'img',
			'dimensions' => array('width' => 270, 'height' => 450),
		),
		'.landing-block-node-member-post' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_POST'),
			'type' => 'text',
		),
		'.landing-block-node-member-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_NAME'),
			'type' => 'text',
		),
		'.landing-block-node-member-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-card-member' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER'),
			'type' => array('animation'),
		),
		'.landing-block-card-member-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_HOVER'),
			'type' => array('bg'),
		),
		'.landing-block-node-member-post' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_POST'),
			'type' => 'typo',
		),
		'.landing-block-node-member-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_NAME'),
			'type' => 'typo',
		),
		'.landing-block-node-member-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_4_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => 'typo',
		),
	),
	'assets' => array(
	    'ext' => array('landing_carousel'),
	),
);