<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--NAME'),
		'section' => array('team'),
	),
	'cards' => array(
		'.landing-block-card-member' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER'),
			'label' => array('.landing-block-node-member-photo', '.landing-block-node-member-name'),
		),
	),
	'nodes' => array(
		'.landing-block-node-member-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_PHOTO'),
			'type' => 'img',
			'dimensions' => array('width' => 200, 'height' => 200),
		),
		'.landing-block-node-member-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_NAME'),
			'type' => 'text',
		),
		'.landing-block-node-member-post' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_POST'),
			'type' => 'text',
		),
		'.landing-block-node-member-email' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_EMAIL'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-card-member' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER'),
			'type' => array('columns', 'padding-bottom', 'animation'),
		),
		'.landing-block-node-member-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_PHOTO'),
			'type' => array('box-shadow', 'margin-bottom'),
		),
		'.landing-block-node-member-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_NAME'),
			'type' => 'typo',
		),
		'.landing-block-node-member-post' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_POST'),
			'type' => 'typo',
		),
		'.landing-block-node-member-line' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_LINE'),
			'type' => array('border-color'),
		),
		'.landing-block-node-member-email' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_6_TEAM_4_COLS--MEMBER_EMAIL'),
			'type' => 'typo-link',
		),
	),
	'assets' => array(
		'ext' => array('landing_carousel'),
	),
);