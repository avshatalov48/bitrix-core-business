<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'facebook' => [
		'name' => '<span class="fa fa-facebook"></span> Facebook',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="facebook">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-facebook--hover g-bg-facebook g-color-white text-center" href="https://www.facebook.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-facebook"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://www.facebook.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-facebook'],
			],
		],
	],
	
	'instagram' => [
		'name' => '<span class="fa fa-instagram"></span> Instagram',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="instagram">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-instagram--hover g-bg-instagram g-color-white text-center" href="https://www.instagram.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-instagram"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://www.instagram.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-instagram'],
			],
		],
	],
	
	'twitter' => [
		'name' => '<span class="fa fa-twitter"></span> Twitter',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="twitter">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-twitter--hover g-bg-twitter g-color-white text-center" href="https://www.twitter.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-twitter"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://www.twitter.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-twitter'],
			],
		],
	],
	
	'youtube' => [
		'name' => '<span class="fa fa-youtube"></span> YouTube',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="youtube">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-youtube--hover g-bg-youtube g-color-white text-center" href="https://www.youtube.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-youtube"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://www.youtube.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-youtube'],
			],
		],
	],
	
	'telegram' => [
		'name' => '<span class="fa fa-telegram"></span> Telegram',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="telegram">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-telegram--hover g-bg-telegram g-color-white text-center" href="https://telegram.org/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-telegram"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://telegram.org/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-telegram'],
			],
		],
	],
	
	'pinterest' => [
		'name' => '<span class="fa fa-pinterest"></span> Pinterest',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="pinterest">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-pinterest--hover g-bg-pinterest g-color-white text-center" href="https://pinterest.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-pinterest"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://pinterest.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-pinterest'],
			],
		],
	],
	
	'skype' => [
		'name' => '<span class="fa fa-skype"></span> Skype',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="skype">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-skype--hover g-bg-skype g-color-white text-center" href="https://skype.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-skype"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://skype.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-skype'],
			],
		],
	],
	
	'vine' => [
		'name' => '<span class="fa fa-vine"></span> Vine',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="vine">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-vine--hover g-bg-vine g-color-white text-center" href="https://vine.co/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-vine"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://vine.co/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-vine'],
			],
		],
	],
	
	'google-plus' => [
		'name' => '<span class="fa fa-google-plus"></span> Google Plus',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="google-plus">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-google-plus--hover g-bg-google-plus g-color-white text-center" href="https://plus.google.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-google-plus"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://plus.google.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-google-plus'],
			],
		],
	],
	
	'dribbble' => [
		'name' => '<span class="fa fa-dribbble"></span> Dribbble',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="dribbble">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-dribbble--hover g-bg-dribbble g-color-white text-center" href="https://dribbble.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-dribbble"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://dribbble.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-dribbble'],
			],
		],
	],
	
	'linkedin' => [
		'name' => '<span class="fa fa-linkedin"></span> LinkedIn',
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="linkedin">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-linkedin--hover g-bg-linkedin g-color-white text-center" href="https://www.linkedin.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-linkedin"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://www.linkedin.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-linkedin'],
			],
		],
	],
	
	'vk' => [
		'name' => '<span class="fa fa-vk"></span> '.Loc::getMessage('LANDING_BLOCK__SOCIAL_VK'),
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="vk">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-vk--hover g-bg-vk g-color-white text-center" href="https://vk.com/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-vk"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://vk.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-vk'],
			],
		],
	],
	
	'odnoklassniki' => [
		'name' => '<span class="fa fa-odnoklassniki"></span> '.Loc::getMessage('LANDING_BLOCK__SOCIAL_OK'),
		'html' => '<li class="landing-block-node-list-item col g-min-width-65 list-inline-item g-mr-0"
			data-card-preset="odnoklassniki">
			<a class="landing-block-node-list-item-link d-block g-py-15 g-px-30 g-bg-odnoklassniki--hover g-bg-odnoklassniki g-color-white text-center" href="https://ok.ru/" target="_blank">
				<i class="landing-block-node-list-item-icon fa fa-odnoklassniki"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-item-icon'],
		'values' => [
			'.landing-block-node-list-item-link' => [
				'href' => 'https://ok.ru/',
				'target' => '_blank',
			],
			'.landing-block-node-list-item-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-odnoklassniki'],
			],
		],
	],
];