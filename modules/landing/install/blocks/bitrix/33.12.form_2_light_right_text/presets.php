<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'link' => [
		'name' => '<span class="icon-call-in"></span> '.Loc::getMessage("LANDING_BLOCK_FORM_33_12--PRESET_LINK"),
		'html' => '
			<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25"  data-card-preset="link">
								<a href="tel:+18006434500" class="landing-block-card-linkcontact-link g-text-decoration-none--hover" target="_blank">
									<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
										<i class="landing-block-card-linkcontact-icon icon-call-in"></i>
									</span>
									<span class="landing-block-card-linkcontact-title d-block text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0">
										Phone number
									</span>
									<span class="landing-block-card-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-weight-700 g-font-size-11 g-color-gray-dark-v1">
										+1-800-643-4500
									</span>
								</a>
							</div>',
		'values' => [
			'.landing-block-card-linkcontact-link' => [
				'href' => 'tel:1-800-643-4500',
				'target' => '_blank',
			],
			'.landing-block-card-linkcontact-text' => '1-800-643-4500',
			'.landing-block-card-linkcontact-icon' => [
				'type' => 'icon',
				'classList' => ['landing-block-card-linkcontact-icon', 'icon-call-in'],
			],
			'.landing-block-card-linkcontact-title' => 'Phone number',
		],
	],
	
	'text' => [
		'name' => '<span class="icon-anchor"></span> '.Loc::getMessage("LANDING_BLOCK_FORM_33_12--PRESET_TEXT"),
		'html' => '
			<div class="landing-block-card-contact js-animation fadeIn col-sm-6 g-brd-left g-brd-bottom g-brd-gray-light-v4 g-px-15 g-py-25" data-card-preset="text">
								<span class="landing-block-card-contact-icon-container g-color-primary g-line-height-1 d-inline-block g-font-size-50 g-mb-30">
									<i class="landing-block-card-contact-icon icon-anchor"></i>
								</span>
								<span class="landing-block-card-contact-title d-block text-uppercase g-font-size-11 g-color-gray-dark-v5 mb-0">
									Address</span>
								<span class="landing-block-card-contact-text g-font-weight-700 g-font-size-11 g-color-gray-dark-v1">
									Sit amet adipiscing
								</span>
							</div>',
		'values' => [
			'.landing-block-card-contact-icon' => [
				'type' => 'icon',
				'classList' => ['landing-block-card-contact-icon', 'icon-anchor'],
			],
			'.landing-block-card-contact-title' => 'Address',
			'.landing-block-card-contact-text' => 'Sit amet adipiscing',
		],
	],
];