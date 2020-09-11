<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'contact-link' => [
		'name' => Loc::getMessage("LANDING_BLOCK_MENU15__CONTACTS__PRESET_LINK"),
		'html' => '
			<div class="landing-block-node-card-menu-contact d-inline-block g-mb-8 g-mb-0--md g-mr-10 g-mr-30--sm" data-card-preset="contact-link">
						<div class="landing-block-node-menu-contact-title d-inline-block g-color-gray-dark-v5">
							Phone Number:
						</div>
						<a href="tel:+4554554554" class="landing-block-node-menu-contact-link d-inline-block g-font-weight-700 g-color-gray-dark-v2">
							+4 554 554 554
						</a>
					</div>',
		'values' => [
			'.landing-block-node-menu-contact-link' => [
				'href' => 'tel:+4554554554',
				'text' => '+4 554 554 554',
			],
			'.landing-block-node-menu-contact-title' => 'Phone Number:',
			
		],
	],
	
	'contact-text' => [
		'name' => Loc::getMessage("LANDING_BLOCK_MENU15__CONTACTS__PRESET_TEXT"),
		'html' => '
			<div class="landing-block-node-card-menu-contact d-inline-block g-mb-8 g-mb-0--md g-mr-10 g-mr-30--sm" data-card-preset="contact-text">
						<div class="landing-block-node-menu-contact-title d-inline-block g-color-gray-dark-v5">
							Opening time:
						</div>
						<div class="landing-block-node-menu-contact-text d-inline-block g-font-weight-700 g-color-gray-dark-v2">
							Mon-Sat: 08.00 -18.00
						</div>
					</div>',
		'values' => [
			'.landing-block-node-menu-contact-title' => 'Opening time:',
			'.landing-block-node-menu-contact-text' => 'Mon-Sat: 08.00 -18.00',
		],
	],
];