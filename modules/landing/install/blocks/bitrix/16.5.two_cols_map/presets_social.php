<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'facebook' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-facebook"></i> Facebook',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="facebook">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-facebook"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-facebook'],
			],
		],
	],
	'instagram' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-instagram"></i> Instagram',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="instagram">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-instagram"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-instagram'],
			],
		],
	],
	'twitter' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-twitter"></i> Twitter',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="twitter">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-twitter"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-twitter'],
			],
		],
	],
	'youtube' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-youtube"></i> Youtube',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="youtube">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-youtube"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-youtube'],
			],
		],
	],
	'telegram' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-telegram"></i> Telegram',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="telegram">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-telegram"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-telegram'],
			],
		],
	],
	'pinterest' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-pinterest"></i> Pinterest',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="pinterest">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-pinterest"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-pinterest'],
			],
		],
	],
	'skype' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-skype"></i> Skype',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="skype">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-skype"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-skypeer'],
			],
		],
	],
	'vine' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-vine"></i> Vine',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="vine">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-vine"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-vineter'],
			],
		],
	],
	'google-plus' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-google-plus"></i> Google-plus',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="google-plus">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-google-plus"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-google-plus'],
			],
		],
	],
	'dribbble' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-dribbble"></i> Dribbble',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="dribbble">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-dribbble"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-dribbble'],
			],
		],
	],
	'linkedin' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-linkedin"></i> Linkedin',
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="linkedin">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-linkedin"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-linkedin'],
			],
		],
	],
	'vk' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-vk"></i> '.Loc::getMessage('LANDING_BLOCK_16_5_TWO_COLS_MAP--PRESET_VK'),
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="vk">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-vk"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-vk'],
			],
		],
	],
	'odnoklassniki' => [
		'name' => '<i class="landing-block-card-social-icon fa fa-odnoklassniki"></i> '.Loc::getMessage('LANDING_BLOCK_16_5_TWO_COLS_MAP--PRESET_OK'),
		'html' => '<li class="landing-block-card-social list-inline-item g-mr-10" data-card-preset="odnoklassniki">
							<a class="landing-block-card-social-link u-icon-v3 u-icon-size--xs g-width-35 g-height-35 g-color-primary g-color-white--hover g-bg-white g-bg-gray-dark-v2--hover g-transition-0_2 g-transition--ease-in" href=""><i class="landing-block-card-social-icon fa fa-odnoklassniki"></i></a>
						</li>',
		'disallow' => ['.landing-block-card-social-icon'],
		'values' => [
			'.landing-block-card-social-link' => [
				'href' => '#',
				'target' => '_blank',
			],
			'.landing-block-card-social-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-odnoklassniki'],
			],
		],
	],
];