<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'contact-link' => [
		'name' => '<span class="icon-call-in"></span> '.Loc::getMessage("LANDING_BLOCK__CONTACTS__PRESET_LINK"),
		'html' => '
			<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-4 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
					   data-card-preset="contact-link">
				<a class="landing-block-node-linkcontact-link g-text-decoration-none--hover" href="tel:1-800-643-4500" target="_blank">
					<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
						<i class="landing-block-node-linkcontact-icon icon-call-in"></i>
					</span>
					<span class="landing-block-node-linkcontact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5">
						Phone number</span>
					<span class="landing-block-node-linkcontact-text g-text-decoration-none g-text-underline--hover g-font-size-14 g-font-weight-700">
						1-800-643-4500
					</span>
				</a>
			</div>',
		'values' => [
			'.landing-block-node-linkcontact-link' => [
				'href' => 'tel:1-800-643-4500',
			],
			'.landing-block-node-linkcontact-text' => '1-800-643-4500',
			'.landing-block-node-linkcontact-icon' => [
				'type' => 'icon',
				'classList' => ['icon-call-in'],
			],
			'.landing-block-node-linkcontact-title' => 'Phone number',
		],
	],
	
	'contact-text' => [
		'name' => '<span class="icon-earphones-alt"></span> '.Loc::getMessage("LANDING_BLOCK__CONTACTS__PRESET_TEXT"),
		'html' => '
			<div class="landing-block-card js-animation fadeIn landing-block-node-contact g-brd-between-cols col-sm-6 col-md-6 col-lg-4 g-brd-primary g-px-15 g-py-30 g-py-0--md g-mb-15"
				 data-card-preset="contact-text">
				<div class="landing-block-node-contact-container">
					<span class="landing-block-node-contact-icon-container d-block g-color-primary g-font-size-50 g-line-height-1 g-mb-20">
						<i class="landing-block-node-contact-icon icon-earphones-alt"></i>
					</span>
					<span class="landing-block-node-contact-title d-block text-uppercase g-font-size-14 g-color-main g-mb-5">
						Toll free</span>
					<span class="landing-block-node-contact-text g-font-size-14 g-font-weight-700">
						@company24
					</span>
				</div>
			</div>',
		'values' => [
			'.landing-block-node-contact-icon' => [
				'type' => 'icon',
				'classList' => ['icon-earphones-alt'],
			],
			'.landing-block-node-contact-title' => 'Toll free',
			'.landing-block-node-contact-text' => '@company24',
		],
	],
];