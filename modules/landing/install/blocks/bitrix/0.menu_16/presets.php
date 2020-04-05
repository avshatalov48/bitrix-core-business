<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'contact-link' => [
		'name' => '<span class="icon icon-envelope"></span> '.Loc::getMessage("LANDING_BLOCK_MENU16__CONTACTS__PRESET_LINK"),
		'html' => '
			<div class="landing-block-card-menu-contact col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
									 data-card-preset="contact-link">

									<a href="mailto:info@company24.com" class="landing-block-node-menu-contactlink-link g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start g-text-decoration-none--hover">
										<span class="landing-block-node-menu-contact-img-container text-left text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
											<i class="landing-block-node-menu-contactlink-img icon icon-envelope"></i>
										</span>
										<span class="landing-block-node-menu-contactlink-text-container d-block text-center text-sm-left text-md-center text-lg-left d-inline-block">
											<span class="landing-block-node-menu-contactlink-title landing-block-node-menu-contact-title-style g-color-main d-block text-uppercase g-font-size-13">
												Email us
											</span>
											<span class="landing-block-node-menu-contactlink-text landing-block-node-menu-contact-text-style d-block g-color-gray-dark-v2 g-font-weight-700 g-text-decoration-none g-text-underline--hover">
												info@company24.com
											</span>
										</span>
									</a>
									
								</div>',
		'values' => [
			'.landing-block-node-linkcontact-link' => [
				'href' => 'mailto:info@company24.com',
			],
			'.landing-block-node-menu-contactlink-title' => 'Email us',
			'.landing-block-node-menu-contactlink-text' => 'info@company24.com',
			'.landing-block-node-menu-contactlink-img' => [
				'type' => 'icon',
				'classList' => ['icon','icon-envelope'],
			],
			
		],
	],
	
	'contact-text' => [
		'name' => '<span class="icon icon-clock"></span> '.Loc::getMessage("LANDING_BLOCK_MENU16__CONTACTS__PRESET_TEXT"),
		'html' => '
			<div class="landing-block-card-menu-contact col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
									 data-card-preset="contact-text">
									<div class="g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start">
										<div class="landing-block-node-menu-contact-img-container text-left text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
											<i class="landing-block-node-menu-contact-img icon icon-clock"></i>
										</div>
										<div class="landing-block-node-menu-contact-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
											<div class="landing-block-node-menu-contact-title landing-block-node-menu-contact-title-style g-color-main text-uppercase g-font-size-13">
												Opening time
											</div>
											<div class="landing-block-node-menu-contact-value landing-block-node-menu-contact-text-style g-color-gray-dark-v2 g-font-weight-700">
												Mon-Sat: 08.00 -18.00
											</div>
										</div>
									</div>
								</div>',
		'values' => [
			'.landing-block-node-menu-contact-img' => [
				'type' => 'icon',
				'classList' => ['icon','icon-clock'],
			],
			'.landing-block-node-menu-contact-title' => 'Opening time',
			'.landing-block-node-menu-contact-value' => 'Mon-Sat: 08.00 -18.00',
		],
	],
];