<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'text' => [
		'name' => '<span class="fa fa-home"></span> '.Loc::getMessage('LANDING_BLOCK_35_6_HEADER--PRESET_TEXT'),
		'html' => '<div class="landing-block-node-card col-sm g-brd-right--sm g-brd-gray-light-v4 g-mb-15 g-mb-0--sm" data-card-preset="text">
						<div class="g-pa-10--lg">
							<div class="landing-block-node-card-icon-container d-lg-inline-block g-valign-top g-color-primary g-mr-5 g-font-size-18 g-line-height-1">
								<i class="landing-block-node-card-icon icon icon-clock"></i>
							</div>
							<div class="landing-block-node-card-text-container d-inline-block">
								<div class="landing-block-node-card-title text-uppercase g-font-size-13">
									Opening time
								</div>
								<div class="landing-block-node-card-text g-font-size-14 g-font-weight-700">
									Mon-Sat: 08.00 -18.00
								</div>
							</div>
						</div>
					</div>',
		'values' => [
			'.landing-block-node-card-icon' => [
				'type' => 'icon',
				'classList' => ['icon', 'icon-clock', 'g-valign-middle', 'g-font-size-18', 'g-color-primary', 'g-mr-5'],
			],
			'.landing-block-node-card-title' => ['Opening time'],
			'.landing-block-node-card-text' => ['Mon-Sat: 08.00 - 18.00'],
		],
	],
	
	'link' => [
		'name' => '<span class="fa fa-envelope"></span>'.Loc::getMessage('LANDING_BLOCK_35_6_HEADER--PRESET_LINK'),
		'html' => '<div class="landing-block-node-card col-sm g-brd-right--sm g-brd-gray-light-v4 g-mb-15 g-mb-0--sm" data-card-preset="link">
						<div class="g-pa-10--lg">
							<div class="landing-block-node-card-icon-container d-lg-inline-block g-valign-top g-color-primary g-mr-5 g-font-size-18 g-line-height-1">
								<i class="landing-block-node-card-icon icon icon-envelope"></i>
							</div>
							<div class="landing-block-node-card-text-container d-inline-block">
								<div class="landing-block-node-card-title text-uppercase g-font-size-13">
									Email us
								</div>
								<a class="landing-block-node-card-link g-color-primary g-font-size-14 g-font-weight-700"
									 href="mailto:info@company24.com"
									 target="_blank">
									info@company24.com
								</a>
							</div>
						</div>
					</div>',
		'values' => [
			'.landing-block-node-card-icon' => [
				'type' => 'icon',
				'classList' => ['icon', 'icon-envelope', 'g-valign-middle', 'g-font-size-18', 'g-color-primary', 'g-mr-5'],
			],
			'.landing-block-node-card-title' => ['Email us'],
			'.landing-block-node-card-link' => [
				'type' => 'link',
				'href' => 'mailto:info@company24.com',
				'target' => '_self',
				'text' => 'info@company24.com',
			],
		],
	],
];