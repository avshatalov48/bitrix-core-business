<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/filter',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-FILTER--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/filter/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'empty',
		'ref' => array(),
	),
	'items' => array(
		0 => array(
			'code' => 'store.catalog.filter',
			'cards' => array(),
			'nodes' => array(),
			'style' => array(
				'#wrapper' => array(
					0 => 'landing-block g-pt-40 g-pb-0 g-pl-40 g-pr-40',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => '27.one_col_fix_title_and_text_2',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-title' => array(
					0 => '<span style="font-weight: bold;">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_1").'</span>',
				),
				'.landing-block-node-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_1"),
				),
			),
			'style' => array(
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title g-font-weight-400 g-font-size-20',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-pb-1 g-font-size-14',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation fadeInUp g-pt-20 g-pb-20 animated g-pl-40 g-pr-40 l-d-xs-none l-d-md-none',
				),
			),
			'attrs' => array(),
		),
		2 => array(
			'code' => '14.2contacts_3_cols',
			'anchor' => '',
			'repo_block' => array(),
			'cards' => array(
				'.landing-block-card' => 3
			),
			'nodes' => array(
				'.landing-block-node-linkcontact-icon' => array(
					0 => 'landing-block-node-linkcontact-icon icon-call-in',
					1 => 'landing-block-node-linkcontact-icon icon-envelope',
				),
				'.landing-block-node-linkcontact-link' => array(
					0 => array(
						'href' => 'tel:+74952128506'
					),
					1 => array(
						'href' => 'mailto:info@company24.com',
					),
				),
				'.landing-block-node-linkcontact-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_2"),
					1 => 'Email',
				),
				'.landing-block-node-linkcontact-text' => array(
					0 => '+7 (495) 212 85 06',
					1 => 'info@company24.com',
				),
				'.landing-block-node-contact-icon' => array(
					0 => 'landing-block-node-contact-icon icon-earphones-alt d-inline-block',
				),
				'.landing-block-node-contact-title' => array(
					0 => 'Toll free',
				),
				'.landing-block-node-contact-text' => array(
					0 => '@company24',
				),
			),
			'style' => array(
				'.landing-block-card' => array(
					0 => 'landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 g-px-15 g-py-30 g-py-0--md g-mb-15 col-lg-12  g-brd-white',
				),
				'.landing-block-node-contact-title' => array(
					0 => 'landing-block-node-contact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5',
				),
				'.landing-block-node-contact-text' => array(
					0 => 'landing-block-node-contact-text g-font-size-14 g-font-weight-700 ',
				),
				'.landing-block-node-linkcontact-title' => array(
					0 => 'landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5',
				),
				'.landing-block-node-linkcontact-text' => array(
					0 => 'landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700 ',
				),
				'.landing-block-node-contact-icon-container' => array(
					0 => 'landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-40 g-pb-25 text-center landing-adjusted g-pl-40 g-pr-40 l-d-xs-none l-d-md-none',
				),
			),
			'attrs' => array(),
		),
		
		
		3 => array(
			'code' => '15.2.social_circles',
			'anchor' => '',
			'repo_block' => array(),
			'cards' => array(
				'.landing-block-node-list-item' => 5,
			),
			'nodes' => array(
				'.landing-block-node-list-link' => array(
					0 => array(
						'text' => '<i class="landing-block-node-list-icon fa fa-facebook"></i>',
						'href' => 'https://facebook.com/',
						'target' => '_blank',
					),
					1 => array(
						'text' => '<i class="landing-block-node-list-icon fa fa-instagram"></i>',
						'href' => 'https://instagram.com/',
						'target' => '_blank',
					),
					2 => array(
						'text' => '<i class="landing-block-node-list-icon fa fa-twitter"></i>',
						'href' => 'https://twitter.com/',
						'target' => '_blank',
					),
					3 => array(
						'text' => '<i class="landing-block-node-list-icon fa fa-youtube"></i>',
						'href' => 'https://youtube.com/',
						'target' => '_blank',
					),
					4 => array(
						'text' => '<i class="landing-block-node-list-icon fa fa-telegram"></i>',
						'href' => 'https://telegram.org/',
						'target' => '_blank',
					),
				),
				'.landing-block-node-list-icon' => array(
					0 => 'landing-block-node-list-icon fa fa-facebook',
					1 => 'landing-block-node-list-icon fa fa-instagram',
					2 => 'landing-block-node-list-icon fa fa-twitter',
					3 => 'landing-block-node-list-icon fa fa-youtube',
					4 => 'landing-block-node-list-icon fa fa-telegram',
				),
			),
			'style' => array(
				'.landing-block-node-list' => array(
					0 => 'landing-block-node-list row no-gutters list-inline g-mb-0 justify-content-center',
				),
				'#wrapper' => array(
					0 => 'landing-block g-pt-20 g-pb-20 g-pl-40 g-pr-40 l-d-xs-none l-d-md-none',
				),
			),
			'attrs' => array(),
		),
		
		4 => array(
			'code' => '06.2.features_4_cols',
			'cards' => array(
				'.landing-block-card' => 1,
			),
			'nodes' => array(
				'.landing-block-node-element-icon' => array(
					0 => 'landing-block-node-element-icon icon-fire',
				),
				'.landing-block-node-element-title' => array(
					0 => '<span style="font-weight: bold;">'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_3").'</span>',
				),
				'.landing-block-node-element-text' => array(
					0 => ' ',
				),
				'.landing-block-node-element-list' => array(
					0 => '<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 text-left"><a href="#landing@landing[clothes/faq]" target="_self">
							'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_4").'
						</a></li>
						<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 text-left"><a href="#landing@landing[clothes/delivery]" target="_self">
							'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_5").'
						</a></li>
						<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 text-left"><a href="#landing@landing[clothes/about]" target="_self">
							'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_6").'
						</a></li>
						<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 text-left"><a href="#landing@landing[clothes/guarantee]" target="_self">
							'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_7").'
						</a></li>
						<li class="landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 text-left"><a href="#landing@landing[clothes/contacts]" target="_self">
							'. Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-FILTER--TEXT_8").'
						</a></li>',
				),
			),
			'style' => array(
				'.landing-block-node-element' => array(
					0 => 'landing-block-node-element js-animation landing-block-card col-md-6 g-parent g-brd-around g-brd-gray-light-v4 g-brd-bottom-primary--hover g-brd-bottom-2--hover g-mb-30 g-mb-0--lg g-transition-0_2 g-transition--ease-in col-lg-12 animated fadeInUp',
				),
				'.landing-block-node-element-title' => array(
					0 => 'landing-block-node-element-title h5 g-color-black g-mb-10 g-text-transform-none g-font-size-20',
				),
				'.landing-block-node-element-text' => array(
					0 => 'landing-block-node-element-text g-color-gray-dark-v4',
				),
				'.landing-block-node-element-list-item' => array(
					0 => 'landing-block-node-element-list-item g-brd-bottom g-brd-gray-light-v3 g-py-10 g-text-transform-none g-font-size-14 text-left',
				),
				'.landing-block-node-element-icon' => array(
					0 => 'landing-block-node-element-icon icon-fire',
				),
				'.landing-block-node-separator' => array(
					0 => 'landing-block-node-separator d-inline-block g-width-40 g-brd-bottom g-brd-2 g-brd-primary g-my-15',
				),
				'#wrapper' => array(
					0 => 'landing-block landing-adjusted g-pt-50 g-pb-50 g-pl-20 g-pr-20 l-d-xs-none l-d-md-none',
				),
			),
			'attrs' => array(),
		),
	),
);