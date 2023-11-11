<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store_v3/payment',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_PAYMENT_NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 3,
	'lock_delete' => true,
	'fields' => [
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => [
			'B24BUTTON_CODE' => 'N',
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/store_v3/payment/preview.jpg',
		],
	],
	'layout' => [
		'code' => 'header_footer',
		'ref' => [
			1 => 'store_v3/header',
			2 => 'store_v3/footer',
		],
	],
	'items' => [
		0 => [
			'code' => '52.5.link_back',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => Loc::getMessage('LANDING_DEMO_STORE_PAYMENT_TEXT_1'),
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-bg-white l-d-xs-none l-d-md-none',
				],
			],
		],
		1 => [
			'code' => '27.3.one_col_fix_title',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '#title#',
				],
			],
			'style' => [
				'#wrapper' => [
					0 => 'landing-block text-center container g-pb-25 g-pt-0 l-d-xs-none l-d-md-none',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-my-0 container g-pl-0 g-pr-0 text-left g-font-size-30 g-font-weight-500',
				],
			],
		],
		2 => [
			'code' => 'store.salescenter.payment.pay_store_v3',
			'nodes' => [],
			'access' => 'W',
			'style' => [
				'#wrapper' => [
					0 => 'landing-block g-pb-40',
				],
			],
		],
	],
];