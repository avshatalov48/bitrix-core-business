<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

$result = [
	'facebook' => [
		'name' => '<span class="fa fa-facebook"></span> Facebook',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="facebook">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-facebook--hover g-bg-facebook g-color-white text-center" href="https://www.facebook.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-facebook"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.facebook.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-facebook'],
			],
		],
	],

	'instagram' => [
		'name' => '<span class="fa fa-instagram"></span> Instagram',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="instagram">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-instagram--hover g-bg-instagram g-color-white text-center" href="https://www.instagram.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-instagram"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.instagram.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-instagram'],
			],
		],
	],

	'whatsapp' => [
		'name' => '<span class="fa fa-whatsapp"></span> WhatsApp',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="whatsapp">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-whatsapp--hover g-bg-whatsapp g-color-white text-center" href="https://whatsapp.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-whatsapp"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://whatsapp.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-whatsapp'],
			],
		],
	],

	'viber' => [
		'name' => '<span class="fab fa-viber g-pr-5"></span> Viber',
		'html' => '<li
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="viber">
			<a
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-viber g-bg-viber--hover"
				href="https://www.viber.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-viber"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.viber.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-viber'],
			],
		],
	],

	'telegram' => [
		'name' => '<span class="fa fa-telegram"></span> Telegram',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="telegram">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-telegram--hover g-bg-telegram g-color-white text-center" href="https://telegram.org/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-telegram"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://telegram.org/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-telegram'],
			],
		],
	],

	'facebook-messenger' => [
		'name' => '<span class="fab fa-facebook-messenger g-pr-5"></span> Facebook Messenger',
		'html' => '<li
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="facebook-messenger">
			<a
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-facebook-messenger g-bg-facebook-messenger--hover"
				href="https://www.messenger.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-facebook-messenger"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.messenger.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-facebook-messenger'],
			],
		],
	],

	'tiktok' => [
		'name' => '<span class="fab fa-tiktok g-pr-5"></span> TikTok',
		'html' => '<li
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="tiktok">
			<a
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-black g-bg-black--hover"
				href="https://www.tiktok.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-tiktok"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.tiktok.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-tiktok'],
			],
		],
	],

	'youtube' => [
		'name' => '<span class="fa fa-youtube"></span> YouTube',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="youtube">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-youtube--hover g-bg-youtube g-color-white text-center" href="https://www.youtube.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-youtube"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.youtube.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-youtube'],
			],
		],
	],

	'vk' => [
		'name' => '<span class="fa fa-vk"></span> '.Loc::getMessage('LANDING_BLOCK__SOCIAL_VK'),
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="vk">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-vk--hover g-bg-vk g-color-white text-center" href="https://vk.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-vk"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://vk.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-vk'],
			],
		],
	],

	'odnoklassniki' => [
		'name' => '<span class="fa fa-odnoklassniki"></span> '.Loc::getMessage('LANDING_BLOCK__SOCIAL_OK'),
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="odnoklassniki">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-odnoklassniki--hover g-bg-odnoklassniki g-color-white text-center" href="https://ok.ru/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-odnoklassniki"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://ok.ru/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-odnoklassniki'],
			],
		],
	],

	'skype' => [
		'name' => '<span class="fa fa-skype"></span> Skype',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="skype">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-skype--hover g-bg-skype g-color-white text-center" href="https://skype.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-skype"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://skype.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-skype'],
			],
		],
	],

	'twitter' => [
		'name' => '<span class="fa fa-twitter"></span> Twitter',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="twitter">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-twitter--hover g-bg-twitter g-color-white text-center" href="https://www.twitter.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-twitter"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.twitter.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-twitter'],
			],
		],
	],

	'tumblr' => [
		'name' => '<span class="fab fa-tumblr g-pr-5"></span> Tumblr',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="tumblr">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-tumblr g-bg-tumblr--hover"
				href="https://www.tumblr.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-tumblr"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.tumblr.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-tumblr'],
			],
		],
	],

	'discord' => [
		'name' => '<span class="fab fa-discord g-pr-5"></span> Discord',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="discord">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-discord g-bg-discord--hover"
				href="https://www.discord.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-discord"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.discord.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-discord'],
			],
		],
	],

	'wechat' => [
		'name' => '<span class="fab fa-weixin g-pr-5"></span> WeChat',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="wechat">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-wechat g-bg-wechat--hover"
				href="https://www.wechat.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-weixin"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.wechat.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-weixin'],
			],
		],
	],

	'pinterest' => [
		'name' => '<span class="fa fa-pinterest"></span> Pinterest',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="pinterest">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-pinterest--hover g-bg-pinterest g-color-white text-center" href="https://pinterest.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-pinterest"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://pinterest.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-pinterest'],
			],
		],
	],

	'twitch' => [
		'name' => '<span class="fab fa-twitch g-pr-5"></span> Twitch',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="twitch">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-twitch g-bg-twitch--hover"
				href="https://www.twitch.tv/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-twitch"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.twitch.tv/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-twitch'],
			],
		],
	],

	'linkedin' => [
		'name' => '<span class="fa fa-linkedin"></span> LinkedIn',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="linkedin">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-linkedin--hover g-bg-linkedin g-color-white text-center" href="https://www.linkedin.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-linkedin"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.linkedin.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-linkedin'],
			],
		],
	],

	'snapchat' => [
		'name' => '<span class="fab fa-snapchat g-pr-5"></span> Snapchat',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="snapchat">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-snapchat g-bg-snapchat--hover"
				href="https://www.snapchat.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-snapchat"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.snapchat.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-snapchat'],
			],
		],
	],

	'flickr' => [
		'name' => '<span class="fab fa-flickr g-pr-5"></span> Flickr',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="flickr">
			<a
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-flickr g-bg-flickr--hover"
				href="https://www.flickr.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-flickr"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.flickr.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-flickr'],
			],
		],
	],

	'soundcloud' => [
		'name' => '<span class="fab fa-soundcloud g-pr-5"></span> Soundcloud',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="soundcloud">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-soundcloud g-bg-soundcloud--hover"
				href="https://soundcloud.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-soundcloud"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://soundcloud.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-soundcloud'],
			],
		],
	],

	'dribbble' => [
		'name' => '<span class="fa fa-dribbble"></span> Dribbble',
		'html' => '<li class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="dribbble">
			<a class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-bg-dribbble--hover g-bg-dribbble g-color-white text-center" href="https://dribbble.com/" target="_blank">
				<i class="landing-block-node-list-icon fa fa-dribbble"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://dribbble.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fa', 'fa-dribbble'],
			],
		],
	],

	'rocketchat' => [
		'name' => '<span class="fab fa-rocketchat g-pr-5"></span> Rocket.chat',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="rocketchat">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-rocket-chat g-bg-rocket-chat--hover"
				href="https://rocket.chat/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-rocketchat"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://rocket.chat/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-rocketchat'],
			],
		],
	],
];

$resultCnZone = [
	'renren' => [
		'name' => '<span class="fab fa-renren g-pr-5"></span> Renren',
		'html' => '<li 
			class="landing-block-node-list-item col g-valign-middle g-flex-grow-0 list-inline-item g-mr-6 g-mb-6"
			data-card-preset="renren">
			<a 
				class="landing-block-node-list-link d-block u-icon-v3 u-icon-size--sm g-rounded-50x g-color-white text-center g-bg-renren g-bg-renren--hover"
				href="https://www.renren.com/"
				target="_blank">
				<i class="landing-block-node-list-icon fab fa-renren"></i>
			</a>
		</li>',
		'disallow' => ['.landing-block-node-list-icon'],
		'values' => [
			'.landing-block-node-list-link' => [
				'href' => 'https://www.renren.com/',
				'target' => '_blank',
			],
			'.landing-block-node-list-icon' => [
				'type' => 'icon',
				'classList' => ['fab', 'fa-renren'],
			],
		],
	],
];

if (!in_array(Manager::getZone(), ['ru', 'kz', 'by']))
{
	unset($result['vk'], $result['odnoklassniki']);
}

if (Manager::getZone() === 'cn')
{
	$result = array_merge($result, $resultCnZone);
}

return $result;