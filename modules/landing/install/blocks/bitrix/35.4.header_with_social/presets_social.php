<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use Bitrix\Landing\Manager;

$result = [
	'facebook' => [
		'name' => '<i class="fa fa-facebook"></i> Facebook',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="facebook">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://facebook.com">
					<i class="landing-block-node-social-icon fa fa-facebook"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://facebook.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-facebook'],
			],
		],
	],
	
	'instagram' => [
		'name' => '<i class="fa fa-instagram"></i> Instagram',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="instagram">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://instagram.com">
					<i class="landing-block-node-social-icon fa fa-instagram"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://instagram.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-instagram'],
			],
		],
	],
	
	'twitter' => [
		'name' => '<i class="fa fa-twitter"></i> Twitter',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="twitter">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://twitter.com">
					<i class="landing-block-node-social-icon fa fa-twitter"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://twitter.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-twitter'],
			],
		],
	],
	
	'youtube' => [
		'name' => '<i class="fa fa-youtube"></i> Youtube',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="youtube">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://youtube.com">
					<i class="landing-block-node-social-icon fa fa-youtube"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://youtube.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-youtube'],
			],
		],
	],
	
	'telegram' => [
		'name' => '<i class="fa fa-telegram"></i> Telegram',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="telegram">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://telegram.com">
					<i class="landing-block-node-social-icon fa fa-telegram"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://telegram.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-telegram'],
			],
		],
	],
	
	'pinterest' => [
		'name' => '<i class="fa fa-pinterest"></i> Pinterest',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="pinterest">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://pinterest.com">
					<i class="landing-block-node-social-icon fa fa-pinterest"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://pinterest.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-pinterest'],
			],
		],
	],
	
	'skype' => [
		'name' => '<i class="fa fa-skype"></i> Skype',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="skype">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://skype.com">
					<i class="landing-block-node-social-icon fa fa-skype"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://skype.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-skype'],
			],
		],
	],
	
	'dribbble' => [
		'name' => '<i class="fa fa-dribbble"></i> Dribbble',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="dribbble">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://dribbble.com">
					<i class="landing-block-node-social-icon fa fa-dribbble"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://dribbble.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-dribbble'],
			],
		],
	],
	
	'linkedin' => [
		'name' => '<i class="fa fa-linkedin"></i> Linkedin',
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="linkedin">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://linkedin.com">
					<i class="landing-block-node-social-icon fa fa-linkedin"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://linkedin.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-linkedin'],
			],
		],
	],
	
	'vk' => [
		'name' => '<i class="fa fa-vk"></i> '.Loc::getMessage('LANDING_BLOCK_HEADER_35_4__SOCIALS__PRESET_VK'),
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="vk">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://vk.com">
					<i class="landing-block-node-social-icon fa fa-vk"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://vk.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-vk'],
			],
		],
	],
	
	'odnoklassniki' => [
		'name' => '<i class="fa fa-odnoklassniki"></i> '.Loc::getMessage('LANDING_BLOCK_HEADER_35_4__SOCIALS__PRESET_OK'),
		'html' => '
			<li class="landing-block-node-social-item list-inline-item g-valign-middle g-mx-3 g-mb-6"
				data-card-preset="odnoklassniki">
				<a class="landing-block-node-social-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-gray-light-v4 g-color-gray-light-v1 g-bg-primary--hover g-color-white--hover g-font-size-14"
				   href="https://odnoklassniki.com">
					<i class="landing-block-node-social-icon fa fa-odnoklassniki"></i>
				</a>
			</li>',
		'disallow' => ['.landing-block-node-social-icon'],
		'values' => [
			'.landing-block-node-social-link' => [
				'href' => 'https://odnoklassniki.com',
			],
			'.landing-block-node-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa','fa-odnoklassniki'],
			],
		],
	],
];

if (!in_array(Manager::getZone(), ['ru', 'kz', 'by']))
{
	unset($result['vk'], $result['odnoklassniki']);
}

return $result;