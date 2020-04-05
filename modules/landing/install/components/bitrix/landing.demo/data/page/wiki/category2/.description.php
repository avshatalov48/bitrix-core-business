<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'old_id' => '232',
	'code' => 'wiki/category2',
	'name' => Loc::getMessage("LANDING_DEMO_WIKI_CATEGORY2-TITLE"),
	'description' => Loc::getMessage("LANDING_DEMO_WIKI_CATEGORY2-DESCRIPTION"),
	'preview' => '',
	'preview2x' => '',
	'preview3x' => '',
	'preview_url' => '',
	'show_in_list' => 'N',
	'type' => ['knowledge', 'group'],
	'version' => 3,
	'fields' => [
		'TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_CATEGORY2-TITLE"),
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [
			'THEME_CODE' => '3corporate',
			'THEME_CODE_TYPO' => 'app',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'METAMAIN_USE' => 'N',
			'METAMAIN_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_CATEGORY2-TITLE"),
			'METAMAIN_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_CATEGORY2-DESCRIPTION"),
			'METAOG_TITLE' => Loc::getMessage("LANDING_DEMO_WIKI_CATEGORY2-TITLE"),
			'METAOG_DESCRIPTION' => Loc::getMessage("LANDING_DEMO_WIKI_CATEGORY2-DESCRIPTION"),
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/wiki/category2/preview.jpg',
			'PIXELFB_USE' => 'N',
			'GACOUNTER_USE' => 'N',
			'GACOUNTER_SEND_CLICK' => 'N',
			'GACOUNTER_SEND_SHOW' => 'N',
			'METAROBOTS_INDEX' => 'Y',
			'BACKGROUND_USE' => 'N',
			'BACKGROUND_POSITION' => 'center',
			'YACOUNTER_USE' => 'N',
			'GTM_USE' => 'N',
			'PIXELVK_USE' => 'N',
			'HEADBLOCK_USE' => 'N',
			'CSSBLOCK_USE' => 'N',
		],
	],
	'layout' => [],
	'items' => [
		'#block3524' => [
			'old_id' => 3524,
			'code' => '04.4.one_col_big_with_img',
			'access' => 'X',
			'nodes' => [
				'.landing-block-node-subtitle' => [
					0 => ' ',
				],
				'.landing-block-node-title' => [
					0 => '<h2 class="landing-block-node-title u-heading-v2__title g-line-height-1_1 g-font-weight-700 g-font-size-40 g-color-white g-mb-minus-10 g-font-montserrat">
					Another category</h2>',
				],
			],
			'style' => [
				'.landing-block-node-inner' => [
					0 => 'landing-block-node-inner text-center u-heading-v2-4--bottom g-brd-transparent',
				],
				'#wrapper' => [
					0 => 'landing-block js-animation fadeInLeft landing-block-node-mainimg u-bg-overlay g-bg-img-hero g-bg-primary-opacity-0_6--after g-pt-15 g-pb-10',
				],
			],
		],
		'#block3428' => [
			'old_id' => 3428,
			'code' => '31.6.two_cols_title_text_button_and_img',
			'access' => 'X',
			'cards' => [
				'.landing-block-card' => [
					'source' => [
						0 => [
							'value' => 0,
							'type' => 'card',
						],
						1 => [
							'value' => 0,
							'type' => 'card',
						],
						2 => [
							'value' => 0,
							'type' => 'card',
						],
					],
				],
			],
			'nodes' => [
				'.landing-block-node-subtitle' => [
					0 => '<span bxstyle="font-weight: normal;">
					Alex Teseira &mdash;&nbsp; 5 June 2017
				</span>',
					1 => '<span bxstyle="font-weight: normal;">
					William Sh. &mdash;&nbsp; 1 June 2017
				</span>',
					2 => '<span bxstyle="font-weight: normal;">
					William Sh. &mdash;&nbsp; 1 June 2017
				</span>',
				],
				'.landing-block-node-title' => [
					0 => 'Exclusive interview with InVision&#039;s CEO',
					1 => 'We build your website to realise your vision, project and more',
					2 => 'Government plans to test new primary school of programming',
				],
				'.landing-block-node-text' => [
					0 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a
						fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra
						eros, fringilla
						porttitor lorem eros vel odio.</p>
						<p>	In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.
						Vivamus at risus quis leo tincidunt scelerisque non et erat.</p>',
					1 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a
						fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra
						eros, fringilla
						porttitor lorem eros vel odio.</p>
						<p>	In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.
						Vivamus at risus quis leo tincidunt scelerisque non et erat.</p>',
					2 => '<p>Sed feugiat porttitor nunc, non dignissim ipsum vestibulum in. Donec in blandit dolor. Vivamus a
						fringilla lorem, vel faucibus ante. Nunc ullamcorper, justo a iaculis elementum, enim orci viverra
						eros, fringilla
						porttitor lorem eros vel odio.</p>
						<p>	In rutrum tellus vitae blandit lacinia. Phasellus eget sapien odio. Phasellus eget sapien odio.
						Vivamus at risus quis leo tincidunt scelerisque non et erat.</p>',
				],
				'.landing-block-node-link' => [
					0 => [
						'href' => '#',
						'target' => '_self',
						'text' => 'Read more ...',
					],
					1 => [
						'href' => '#',
						'target' => '_self',
						'text' => 'Read more ...',
					],
					2 => [
						'href' => '#',
						'target' => '_self',
						'text' => 'Read more ...',
					],
				],
				'.landing-block-node-img' => [
					0 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x335/img1.jpg',
					],
					1 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x335/img2.jpg',
					],
					2 => [
						'alt' => '',
						'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/business/540x335/img3.jpg',
					],
				],
			],
			'style' => [
				'.landing-block-card' => [
					0 => 'landing-block-card row no-gutters g-mb-0--last landing-card g-mb-15 align-items-center',
					1 => 'landing-block-card row no-gutters g-mb-0--last landing-card g-mb-15 align-items-center',
					2 => 'landing-block-card row no-gutters g-mb-0--last landing-card g-mb-15 align-items-center',
				],
				'.landing-block-node-subtitle' => [
					0 => 'landing-block-node-subtitle g-font-weight-600 g-font-size-12 font-italic g-font-montserrat g-theme-event-color-gray-dark-v1',
					1 => 'landing-block-node-subtitle g-font-weight-600 g-font-size-12 font-italic g-font-montserrat g-theme-event-color-gray-dark-v1',
					2 => 'landing-block-node-subtitle g-font-weight-600 g-font-size-12 font-italic g-font-montserrat g-theme-event-color-gray-dark-v1',
				],
				'.landing-block-node-title' => [
					0 => 'landing-block-node-title h3 g-color-black g-font-weight-600 mb-4 g-font-montserrat g-font-size-23',
					1 => 'landing-block-node-title h3 g-color-black g-font-weight-600 mb-4 g-font-montserrat g-font-size-23',
					2 => 'landing-block-node-title h3 g-color-black g-font-weight-600 mb-4 g-font-montserrat g-font-size-23',
				],
				'.landing-block-node-text' => [
					0 => 'landing-block-node-text g-font-open-sans g-color-black-opacity-0_8 g-font-size-16',
					1 => 'landing-block-node-text g-font-open-sans g-color-black-opacity-0_8 g-font-size-16',
					2 => 'landing-block-node-text g-font-open-sans g-color-black-opacity-0_8 g-font-size-16',
				],
				'.landing-block-node-link' => [
					0 => 'landing-block-node-link g-font-weight-600 g-font-size-12 g-text-underline--none--hover text-uppercase g-font-open-sans g-color-primary g-color-teal--hover',
					1 => 'landing-block-node-link g-font-weight-600 g-font-size-12 g-text-underline--none--hover text-uppercase g-font-open-sans g-color-primary g-color-teal--hover',
					2 => 'landing-block-node-link g-font-weight-600 g-font-size-12 g-text-underline--none--hover text-uppercase g-font-open-sans g-color-primary g-color-teal--hover',
				],
			],
		],
	],
];
