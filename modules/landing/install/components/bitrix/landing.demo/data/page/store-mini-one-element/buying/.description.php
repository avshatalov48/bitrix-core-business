<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-mini-one-element/buying',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_MINI_ONE_ELEMENT_BYING_TXT_1'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' =>array(
			'RULE' => NULL,
			'ADDITIONAL_FIELDS' =>array(
					'VIEW_USE' => 'N',
					'VIEW_TYPE' => 'no',
				'THEME_CODE' => 'event',

				),
		),
	'layout' =>array(),
	'items' =>array(
			0 =>array(
					'code' => 'store.order',
					'cards' =>array(),
					'nodes' =>array(),
					'style' =>array(
							'#wrapper' =>array(
									0 => 'landing-block g-pt-20 g-pb-0',
								),
						),
					'attrs' =>array(),
				),
		),
);