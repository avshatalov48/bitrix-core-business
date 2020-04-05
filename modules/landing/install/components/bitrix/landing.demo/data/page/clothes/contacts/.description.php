<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'clothes/contacts',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CLOTHES-CONTACTS--NAME'),
	'description' => null,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => null,
		'ADDITIONAL_FIELDS' => array(
			'METAOG_IMAGE' => 'https://cdn.bitrix24.site/bitrix/images/demo/page/clothes/contacts/preview.jpg',
			'VIEW_USE' => 'N',
			'VIEW_TYPE' => 'no',
			'THEME_CODE' => 'travel',
			'THEME_CODE_TYPO' => 'travel',
		),
	),
	'layout' => array(
		'code' => 'header_footer',
		'ref' => array(
			1 => 'clothes/header',
			2 => 'clothes/footer',
		),
	),
	'items' => array(
		0 => array(
			'code' => '04.1.one_col_fix_with_title',
			'cards' => array(),
			'nodes' => array(
				'.landing-block-node-subtitle' => array(
					0 => ' ',
				),
				'.landing-block-node-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_1"),
				),
			),
			'style' => array(
				'.landing-block-node-subtitle' => array(
					0 => 'landing-block-node-subtitle h6 g-font-weight-800 g-font-size-12 g-letter-spacing-1 g-color-primary g-mb-20',
				),
				'.landing-block-node-title' => array(
					0 => 'landing-block-node-title h1 u-heading-v2__title g-line-height-1_3 g-font-weight-600 g-mb-minus-10 g-font-size-40 g-font-montserrat g-text-transform-none',
				),
				'#wrapper' => array(
					0 => 'landing-block js-animation animated g-pb-20 g-pt-40 animation-none',
				),
			),
			'attrs' => array(),
		),
		1 => array(
			'code' => 'html',
			'cards' => array(),
			'nodes' => array(
				'bitrix:landing.blocks.html' => array(
					'HTML_CODE' => '<center><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2304.4572457769546!2d20.486353715716902!3d54.71917157869677!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46e33df965fc0919%3A0x284054d2fac2875d!2z0JHQuNGC0YDQuNC60YEyNA!5e0!3m2!1sru!2sru!4v1525682284941" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe></center>',
				),
			),
			'style' => array(),
			'attrs' => array(),
		),
		2 => array(
			'code' => '33.12.form_2_light_right_text',
			'cards' => array(
				'.landing-block-card-contact' => 4,
			),
			'nodes' => array(
				'.landing-block-node-main-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_7"),
				),
				'.landing-block-node-text' => [
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_1"),
				],
				'.landing-block-card-contact-icon' => array(
					0 => 'landing-block-card-contact-icon fa fa-location-arrow',
					1 => 'landing-block-card-contact-icon fa fa-clock-o',
				),
				'.landing-block-card-linkcontact-icon' => array(
					0 => 'landing-block-card-linkcontact-icon fa fa-phone',
					1 => 'landing-block-card-linkcontact-icon fa fa-envelope',
				),
				'.landing-block-card-contact-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_2"),
					1 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_3"),
				),
				'.landing-block-card-linkcontact-title' => array(
					0 => Loc::getMessage("LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_4"),
					1 => 'Email',
				),
				'.landing-block-card-contact-text' => array(
					0 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_2"),
					1 => Loc::getMessage("NOTTRANSLATE--LANDING_DEMO_STORE_CLOTHES-CONTACTS--TEXT_3"),
				),
				'.landing-block-card-linkcontact-link' => array(
					0 => '+7 (495) 212 85 06</a>',
					1 => 'shipping.example.ru',
				),
				'.landing-block-card-linkcontact-text' => array(),
			),
			'style' => array(
				'.landing-block-node-main-title' => array(
					0 => 'landing-block-node-main-title h3 g-font-weight-700 g-mb-20 g-color-black g-font-montserrat g-text-transform-none g-font-size-30',
				),
				'.landing-block-node-text' => array(
					0 => 'landing-block-node-text g-line-height-1_5 text-left g-mb-40 g-font-open-sans g-color-black-opacity-0_9 g-font-size-16',
				),
				'.landing-block-card-contact-title' => array(
					0 => 'landing-block-card-contact-title h3 d-block text-uppercase g-font-size-11 mb-0 g-font-open-sans g-color-black-opacity-0_9',
				),
				'.landing-block-card-linkcontact-title' => array(
					0 => 'landing-block-card-linkcontact-title h3 d-block text-uppercase g-font-size-11 mb-0 g-font-open-sans g-color-black-opacity-0_9',
				),
				'.landing-block-card-contact-text' => array(
					0 => 'landing-block-card-contact-text g-font-weight-700 g-font-size-11 g-font-open-sans g-color-primary',
				),
				'.landing-block-card-linkcontact-text' => array(
					0 => 'landing-block-card-linkcontact-text g-font-weight-700 g-font-size-11 g-font-open-sans g-color-primary',
				),
				'#wrapper' => array(
					0 => 'g-pos-rel landing-block text-center g-pt-100 g-pb-100',
				),
			),
			'attrs' => array(
				'.bitrix24forms' => array(),
			),
		),
	),
);