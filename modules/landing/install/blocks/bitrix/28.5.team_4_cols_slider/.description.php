<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--NAME'),
		'section' => array('team'),
	),
	'cards' => array(
		'.landing-block-card-member' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER'),
			'label' => array('.landing-block-node-member-photo', '.landing-block-node-member-name'),
		),
	),
	'nodes' => array(
		'.landing-block-node-member-photo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_PHOTO'),
			'type' => 'img',
			'useInDesigner' => false,
			'dimensions' => array('width' => 260),
			'create2xByDefault' => false,
		),
		'.landing-block-node-member-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_NAME'),
			'type' => 'text',
		),
		'.landing-block-node-member-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-member-contact-icon1' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-member-contact-icon2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-member-contact-text1' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-member-contact-text2' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-card-member' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER'),
			'type' => array('animation', 'align-self'),
		),
		'.landing-block-card-member-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER'),
			'type' => array('background-color', 'background-hover'),
		),
		'.landing-block-node-member-name' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_NAME'),
			'type' => 'typo',
		),
		'.landing-block-node-member-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-member-contact-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_ICON'),
			'type' => 'color',
		),
		'.landing-block-node-member-contact-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => 'color',
		),
		'.landing-block-text-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS--MEMBER_TEXT'),
			'type' => array('padding-top', 'padding-bottom', 'padding-left', 'padding-right', 'background-color'),
		),
		'.landing-block-slider' => [
			'additional' => [
				'name' => Loc::getMessage('LANDING_BLOCK_28_5_TEAM_4_COLS_SLIDER'),
				'attrsType' => ['autoplay', 'autoplay-speed', 'animation', 'pause-hover', 'slides-show-extended', 'arrows', 'dots'],
			]
		],
	),
	'assets' => array(
	    'ext' => array('landing_carousel'),
	),
);