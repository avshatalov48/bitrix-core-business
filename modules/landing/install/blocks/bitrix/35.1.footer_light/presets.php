<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'text' => [
		'name' => '<span class="fa fa-home"></span> '.Loc::getMessage('LANDING_BLOCK_35.1.FOOTER_LIGHT__PRESET__TEXT'),
		'html' => '<div class="landing-block-card-contact g-pos-rel g-pl-20 g-mb-7" data-card-preset="text">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-dark-v2 g-absolute-centered--y g-left-0">
							<i class="landing-block-node-card-contact-icon fa fa-home"></i>
						</div>
						<div class="landing-block-node-card-contact-text g-color-gray-dark-v2">Address: In sed lectus tincidunt</div>
					</div>',
		'values' => [
			'.landing-block-node-card-contact-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-home', 'g-absolute-centered--y', 'g-left-0'],
			],
			'.landing-block-node-card-contact-text' => ['Address: In sed lectus tincidunt'],
		],
	],
	
	'link' => [
		'name' => '<span class="fa fa-envelope"></span> '.Loc::getMessage('LANDING_BLOCK_35.1.FOOTER_LIGHT__PRESET__LINK'),
		'html' => '<div class="landing-block-card-contact g-pos-rel g-pl-20 g-mb-7" data-card-preset="link">
						<div class="landing-block-node-card-contact-icon-container g-color-gray-dark-v2 g-absolute-centered--y g-left-0">
							<i class="landing-block-node-card-contact-icon fa fa-envelope"></i>
						</div>
						<div>
							<span class="landing-block-node-card-contact-text g-color-gray-dark-v2">Email:</span>
							<a class="landing-block-node-card-contact-link g-color-gray-dark-v2 g-font-weight-700" href="mailto:info@company24.com" target="_blank">info@company24.com</a>
						</div>
					</div>',
		'values' => [
			'.landing-block-node-card-contact-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-envelope', 'g-absolute-centered--y', 'g-left-0'],
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