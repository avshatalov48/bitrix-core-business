<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Integration\UI\EntitySelector\ProjectProvider;
use Bitrix\Socialnetwork\Integration\UI\EntitySelector\UserProvider;

if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
{
	return [];
}

Loc::loadMessages(__DIR__.'/options.php');

$userOptions = [
	'dynamicLoad' => true,
	'dynamicSearch' => true,
	'searchFields' => [
		[
			'name' => 'position',
			'type' => 'string',
		],
		[
			'name' => 'email',
			'type' => 'email'
		],
	],
	'searchCacheLimits' => [
		'^[=_0-9a-z+~\'!\\$&*^`|\\#%\\/?{}-]+(\\.[=_0-9a-z+~\'!\\$&*^`|\\#%\\/?{}-]+)*@'
	],
	'badgeOptions' => [
		[
			'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_ON_VACATION_BADGE'),
			'bgColor' => '#b4f4e6',
			'textColor' => '#27a68a',
			'conditions' => [
				'isOnVacation' =>  true,
			],
		],
		[
			'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_INVITED_USER_BADGE'),
			'textColor' => '#23a2ca',
			'bgColor' => '#dcf6fe',
			'conditions' => [
				'invited' =>  true,
			],
		],
	],
	'itemOptions' => [
		'default' => [
			'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/default-user.svg',
			'link' => UserProvider::getUserUrl(),
			'linkTitle' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_USER_LINK_TITLE'),
		],
		'extranet' => [
			'textColor' => '#ca8600',
			'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/extranet-user.svg',
			'badges' => [
				[
					'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_EXTRANET_BADGE'),
					'textColor' => '#bb8412',
					'bgColor' => '#fff599',
				],
			],
		],
		'email' => [
			'textColor' => '#ca8600',
			'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/email-user.svg',
			'badges' => [
				[
					'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_GUEST_USER_BADGE'),
					'textColor' => '#bb8412',
					'bgColor' => '#fff599',
				],
			],
		],
		'inactive' => [
			'badges' => [
				[
					'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_INACTIVE_INTRANET_USER_BADGE'),
					'textColor' => '#828b95',
					'bgColor' => '#eaebec',
				],
			],
		],
		'integrator' => [
			'badges' => [
				[
					'title' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_INTEGRATOR_USER_BADGE'),
					'textColor' => '#668d13',
					'bgColor' => '#e6f4b9',
				],
			],
		]
	],
	'tagOptions' => [
		'default' => [
			'textColor' => '#1066bb',
			'bgColor' => '#bcedfc',
			'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/default-tag-user.svg',
		],
		'extranet' => [
			'textColor' => '#a9750f',
			'bgColor' => '#ffec91',
			'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/extranet-user.svg',
		],
		'email' => [
			'textColor' => '#a26b00',
			'bgColor' => '#ffec91',
			'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/email-user.svg',
		],
		'inactive' => [
			'textColor' => '#5f6670',
			'bgColor' => '#ecedef',
		],
	]
];

return [
	'css' => 'dist/sonet-entity-selector.bundle.css',
	'js' => 'dist/sonet-entity-selector.bundle.js',
	'rel' => [
		'main.core',
		'sidepanel',
		'ui.entity-selector',
	],
	'skip_core' => false,
	'settings' => [
		'entities' => [
			[
				'id' => 'user',
				'options' => $userOptions,
			],
			[
				'id' => 'fired-user',
				'options' => $userOptions,
			],
			[
				'id' => 'project',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/project.svg',
							'link' => ProjectProvider::getProjectUrl().'card/',
							'linkTitle' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_LINK_TITLE'),
							'supertitle' => Loc::getMessage('SOCNET_ENTITY_SELECTOR_PROJECT_SUPER_TITLE')
						],
						'extranet' => [
							'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/extranet-project.svg',
							'textColor' => '#ca8600',
							'badges' => [
								[
									'title' =>  Loc::getMessage('SOCNET_ENTITY_SELECTOR_EXTRANET_BADGE'),
									'textColor' => '#bb8412',
									'bgColor' => '#fff599',
								]
							],
						]
					],
					'tagOptions' => [
						'default' => [
							'textColor' => '#207976',
							'bgColor' => '#ade7e4',
						],
						'extranet' => [
							'textColor' => '#a9750f',
							'bgColor' => '#ffec91',
						]
					]
				]
			],
			[
				'id' => 'meta-user',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => false,
					'itemOptions' => [
						'all-users' => [
							'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/meta-user-all.svg',
						],
						'other-users' => [
							'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/meta-user-other.svg',
						]
					],
					'tagOptions' => [
						'all-users' => [
							'textColor' => '#5f6670',
							'bgColor' => '#dbf087',
							'avatar' => ''
						],
						'other-users' => [
							'textColor' => '#5f6670',
							'bgColor' => '#dbf087',
							'avatar' => ''
						],
					]
				]
			],
			[
				'id' => 'project-tag',
				'options' => [
					'dynamicLoad' => true,
					'dynamicSearch' => true,
					'itemOptions' => [
						'default' => [
							'avatar' => '/bitrix/js/socialnetwork/entity-selector/src/images/default-tag.svg',
						],
					],
				],
			],
		],
	]
];
