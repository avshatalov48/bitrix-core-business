<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'link' => [
		'name' => '<span class="icon-call-in"></span> '.Loc::getMessage("LANDING_BLOCK_FORM_33_2--PRESET_LINK"),
		'html' => '
			<div class="landing-block-node-card-contact" data-card-preset="link">
						<div class="media align-items-center mb-4">
							<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
								  <i class="landing-block-card-contact-icon icon-communication-033 u-line-icon-pro"></i>
								</span>
							</div>
							<a href="tel:+32(0)333444777" class="landing-block-card-linkcontact-link g-color-white-opacity-0_6">+32 (0) 333 444 777</a>
						</div>
					</div>',
		'values' => [
			'.landing-block-card-linkcontact-link' => [
				'href' => 'tel:32(0)333444777',
				'text' => '32 (0) 333 444 777'
			],
			'.landing-block-card-contact-icon' => [
				'type' => 'icon',
				'classList' => ['landing-block-card-contact-icon', 'icon-communication-033', 'u-line-icon-pro'],
			],
		],
	],

	'text' => [
		'name' => '<span class="icon-anchor"></span> '.Loc::getMessage("LANDING_BLOCK_FORM_33_2--PRESET_TEXT"),
		'html' => '
			<div class="landing-block-node-card-contact" data-card-preset="text">
						<div class="media align-items-center mb-4">
							<div class="d-flex">
								<span class="landing-block-card-contact-icon-container u-icon-v1 u-icon-size--sm g-color-white mr-2">
									<i class="landing-block-card-contact-icon icon-hotel-restaurant-235 u-line-icon-pro"></i>
								</span>
							</div>
							<div class="media-body">
								<div class="landing-block-node-contact-text g-color-white-opacity-0_6 mb-0">5B Streat, City
									50987 New Town US
								</div>
							</div>
						</div>
					</div>',
		'values' => [
			'.landing-block-card-contact-icon' => [
				'type' => 'icon',
				'classList' => ['landing-block-card-contact-icon', 'icon-hotel-restaurant-235', 'u-line-icon-pro'],
			],
			'.landing-block-node-contact-text' => '5B Streat, City 50987 New Town US',
		],
	],
];