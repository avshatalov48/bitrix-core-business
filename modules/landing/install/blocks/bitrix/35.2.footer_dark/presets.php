<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'text' => [
		'name' => '<span class="fa fa-home"></span> '.Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT__PRESET__TEXT'),
		'html' => '<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-light-v1">
							<i class="landing-block-node-card-contact-icon fa fa-home g-pr-5"></i>
						</div>
						<div class="landing-block-node-card-contact-text landing-semantic-text-medium g-color-gray-light-v1">
							Address: <span style="font-weight: bold;">In sed lectus tincidunt</span>
						</div>
					</div>',
		'values' => [
			'.landing-block-node-card-contact-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-home', 'g-pr-5'],
			],
			'.landing-block-node-card-contact-text' => ['Address: In sed lectus tincidunt'],
		],
	],
	
	'link' => [
		'name' => '<span class="fa fa-envelope"></span> '.Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT__PRESET__LINK'),
		'html' => '<div class="landing-block-card-contact d-flex g-pos-rel g-mb-7" data-card-preset="link">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-light-v1">
							<i class="landing-block-node-card-contact-icon fa fa-envelope g-pr-5"></i>
						</div>
						<div>
							<span class="landing-block-node-card-contact-text landing-semantic-text-medium g-color-gray-light-v1">Email: </span>
							<a class="landing-block-node-card-contact-link landing-semantic-link-medium g-color-gray-light-v1 g-font-weight-700" href="mailto:info@company24.com" target="_blank">info@company24.com</a>
						</div>
					</div>',
		'values' => [
			'.landing-block-node-card-contact-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-envelope', 'g-pr-5'],
			],
			'.landing-block-node-card-contact-text' => ['Email: '],
			'.landing-block-node-card-contact-link' => [
				'type' => 'link',
				'href' => 'mailto:info@company24.com',
				'target' => '_blank',
				'text' => 'info@company24.com',
			],
		],
	],
];