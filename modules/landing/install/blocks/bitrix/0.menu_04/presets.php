<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'logo' => [
		'name' => Loc::getMessage("LANDING_BLOCK_MENU_4-PRESET-LOGO"),
		'html' => '
			<li class="landing-block-node-menu-list-item landing-block-node-menu-list-logo g-hidden-lg-down nav-logo-item g-mx-15--lg" data-card-preset="logo">
							<a href="/" class="landing-block-node-menu-logo-link navbar-brand mr-0">
								<img class="landing-block-node-menu-logo d-block g-max-width-180"
									 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-light.png"
									 alt=""
									 data-header-fix-moment-exclude="d-block"
									 data-header-fix-moment-classes="d-none">

								<img class="landing-block-node-menu-logo-2 d-none g-max-width-180"
									 src="https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-dark.png"
									 alt=""
									 data-header-fix-moment-exclude="d-none"
									 data-header-fix-moment-classes="d-block">
							</a>
						</li>',
		'values' => [
			'.landing-block-node-menu-logo-link' => [
				'href' => '/',
			],
			'.landing-block-node-menu-logo' => [
				'type' => 'image',
				'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-light.png',
			],
			'.landing-block-node-menu-logo-2' => [
				'type' => 'image',
				'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/logos/architecture-logo-dark.png',
			],
			
		],
	],
	
	'link' => [
		'name' => Loc::getMessage("LANDING_BLOCK_MENU_4-PRESET-LINK"),
		'html' => '
			<li class="landing-block-node-menu-list-item nav-item g-mx-30--lg g-mb-7 g-mb-0--lg" data-card-preset="link">
							<a href="/" class="landing-block-node-menu-list-item-link nav-link p-0">Home</a>
						</li>',
		'values' => [
			'.landing-block-node-menu-list-item-link' => [
				'type' => 'link',
				'href' => '/',
				'target' => '_self',
				'text' => 'Home',
			],
		],
	],
];