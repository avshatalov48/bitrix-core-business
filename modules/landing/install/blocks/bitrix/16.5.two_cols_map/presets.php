<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'contact-link' => [
		'name' => Loc::getMessage('LANDING_BLOCK_16_5_TWO_COLS_MAP--PRESET_LINK'),
		'html' => '<div class="landing-block-card-address g-mb-20" data-card-preset="contact-link">
							<div class="landing-block-card-address-title d-inline-block text-uppercase g-font-size-14 g-color-white-opacity-0_7 g-pr-5">Email:</div>
							<a class="landing-block-card-address-link d-inline-block g-color-white text-uppercase g-font-size-14 g-font-weight-700" href="mailto:info@company24.com">
								info@company24.com
							</a>
						</div>',
		'values' => [
			'.landing-block-card-address-title' => 'Email:',
			'.landing-block-card-address-link' => [
				'text' => 'info@company24.com',
				'href' => 'mailto:info@company24.com',
				'target' => '_blank',
			],
		],
	],
	'contact-text' => [
		'name' => Loc::getMessage('LANDING_BLOCK_16_5_TWO_COLS_MAP--PRESET_TEXT'),
		'html' => '<div class="landing-block-card-address g-mb-20" data-card-preset="contact-text">
							<div class="landing-block-card-address-title d-inline-block text-uppercase g-font-size-14 g-color-white-opacity-0_7 g-pr-5">Address:</div>
							<div class="landing-block-card-address-text d-inline-block text-uppercase g-font-size-14 g-color-white g-font-weight-700">
								In sed lectus tincidunt
							</div>
						</div>',
		'values' => [
			'.landing-block-card-address-title' => 'Address:',
			'.landing-block-card-address-text' => 'In sed lectus tincidunt',
		],
	],
];