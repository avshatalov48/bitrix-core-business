<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'contact-link' => [
		'name' => '<span class="icon icon-envelope"></span> '.Loc::getMessage("LANDING_BLOCK_35.3.HEADER_PRESET__LINK"),
		'html' => '
			<div class="landing-block-node-card col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
						 data-card-preset="contact-link">

						<a href="mailto:info@company24.com"
						   class="landing-block-node-card-contactlink-link g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start g-text-decoration-none--hover">
							<span class="landing-block-node-card-icon-container text-left text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
								<i class="landing-block-node-card-contactlink-icon icon icon-envelope"></i>
							</span>
							<span class="landing-block-node-card-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
								<span class="landing-block-node-menu-contactlink-title landing-block-node-card-title-style g-color-main d-block text-uppercase g-font-size-13">
									Email us
								</span>
								<span class="landing-block-node-card-contactlink-text landing-block-node-card-text-style d-block g-color-gray-dark-v2 g-font-weight-700 g-text-decoration-none g-text-underline--hover">
									info@company24.com
								</span>
							</span>
						</a>

					</div>',
		'values' => [
			'.landing-block-node-card-contactlink-link' => [
				'href' => 'mailto:info@company24.com',
			],
			'.landing-block-node-menu-contactlink-title' => 'Email us',
			'.landing-block-node-card-contactlink-text' => 'info@company24.com',
			'.landing-block-node-card-contactlink-icon' => [
				'type' => 'icon',
				'classList' => ['icon','icon-envelope'],
			],
			
		],
	],
	
	'contact-text' => [
		'name' => '<span class="icon icon-clock"></span> '.Loc::getMessage("LANDING_BLOCK_35.3.HEADER_PRESET__TEXT"),
		'html' => '
			<div class="landing-block-node-card col-md g-mb-10 g-mb-0--md g-brd-right--md g-brd-gray-light-v4"
						 data-card-preset="contact-text">
						<div class="g-pa-10--md row align-items-center justify-content-center justify-content-sm-start justify-content-md-center justify-content-lg-start">
							<div class="landing-block-node-card-icon-container text-left text-md-center text-lg-left w-auto g-width-100x--md g-width-auto--lg g-font-size-18 g-line-height-1 d-none d-sm-inline-block g-valign-top g-color-primary g-mr-10 g-mr-0--md g-mr-10--lg">
								<i class="landing-block-node-card-icon icon icon-clock"></i>
							</div>
							<div class="landing-block-node-card-text-container text-center text-sm-left text-md-center text-lg-left d-inline-block">
								<div class="landing-block-node-card-title landing-block-node-card-title-style g-color-main text-uppercase g-font-size-13">
									Opening time
								</div>
								<div class="landing-block-node-card-text landing-block-node-card-text-style g-color-gray-dark-v2 g-font-weight-700">
									Mon-Sat: 08.00 -18.00
								</div>
							</div>
						</div>
					</div>',
		'values' => [
			'.landing-block-node-card-icon' => [
				'type' => 'icon',
				'classList' => ['icon','icon-clock'],
			],
			'.landing-block-node-card-title' => 'Opening time',
			'.landing-block-node-card-text' => 'Mon-Sat: 08.00 -18.00',
		],
	],
];