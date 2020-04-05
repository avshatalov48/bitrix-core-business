<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'search-result2',
	'type' => ['page', 'knowledge', 'group'],
	'section' => ['dynamic'],
	'name' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT2-NAME'),
	'description' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT2-DESCRIPTION'),
	'publication' => true,
	'version' => 3,
	'active' => false,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'METAOG_TITLE' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT2-NAME'),
			'METAOG_DESCRIPTION' => Loc::getMessage('LANDING_DEMO_SEARCH-RESULT2-DESCRIPTION'),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/search-result2/preview.jpg',
			'VIEW_USE' => 'N',
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
		],
	],
	'layout' => [
	],

	'items' => [
		'0' => [
			'code' => '59.2.search_sidebar',
			'access' => 'X',
		],
		'#block3431' => [
			'old_id' => 3431,
			'code' => '27.2.one_col_full_title',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-title' => [
					0 => '<span bxstyle="font-weight: bold;">Here is what we have found</span><br />',
				],
			],
			'style' => [
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title g-font-weight-400 g-my-0 g-font-size-48 text-left g-font-montserrat container g-max-width-100x g-pl-0 g-pr-0',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInUp g-pl-auto g-pb-auto g-pt-30 text-center',
				],
			],
		],
		'#block3432' => [
			'old_id' => 3432,
			'code' => '18.2.two_cols_fix_img_text_button_with_cards',
			'access' => 'X',
			'dynamic' => [
				'.landing-block-card' => [
					'settings' => [
						'source' => [
							'source' => 'landing:landing',
							'sort' => [
								'by' => 'TITLE',
								'order' => 'DESC',
							],
						],
						'pagesCount' => '6',
					],
					'references' => [
						'.landing-block-node-title@0' => array(
							'id' => 'TITLE',
							'link' => 'true',
						),
						'.landing-block-node-text@0' => array(
							'id' => 'DESCRIPTION',
							'link' => 'true',
						),
						// todo: how set link to detail?
						'.landing-block-node-link@0' => array(
							'id' => 'LINK',
							'text' => 'Read more ...',
							'action' => 'landing',
							'link' => array(
								'href' => '#',
								'target' => '_self',
							),
						),
						'.landing-block-node-img@0' => array(
							'id' => 'IMAGE',
							'link' => 'true',
						),
					],
					'source' => 'landing:landing',
				],
			],
		],
	],
];
