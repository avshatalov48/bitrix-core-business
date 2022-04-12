<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'parent' => 'store-instagram',
	'code' => 'store-instagram/footer',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--FOOTER--NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--FOOTER--NAME'),
	'active' => \LandingSiteDemoComponent::checkActive([
		'ONLY_IN' => [],
		'EXCEPT' => ['ru'],
	]),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'TITLE' => Loc::getMessage('LANDING_DEMO_STORE_INSTAGRAM--FOOTER--NAME'),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => '1construction',

		),
	),
	'layout' => array(),
	'items' => array(
		'#block7876' => array(
			'code' => '17.copyright',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-text' => array(
					0 => '
				<p>&copy 2021 All rights reserved.</p>
			',
				),
			),
			'style' => array(
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text js-animation animation-none g-font-size-12 ',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animation-none',
				),
			),
		),
	),
);