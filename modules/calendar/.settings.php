<?php

use Bitrix\Calendar\Integration\UI\EntitySelector;
use Bitrix\Calendar\Integration;
use Bitrix\Calendar\Internals\Container;
use Bitrix\Calendar\Internals\EventManager\EventManagerInterface;

return [
	'controllers' => [
		'value' => [
			'namespaces' => [
				'\\Bitrix\\Calendar\\Controller' => 'api',
				'\\Bitrix\\Calendar\\OpenEvents\\Controller' => 'open-events',
			],
		],
		'readonly' => true,
	],
	'ui.uploader' => [
		'value' => [
			'allowUseControllers' => true,
		],
		'readonly' => true,
	],
	'services' => [
		'value' => [
			'calendar.service.office365.helper' => [
				'className' => '\\Bitrix\\Calendar\\Sync\\Office365\\Helper',
			],
			'calendar.service.google.helper' => [
				'className' => '\\Bitrix\\Calendar\\Sync\\Google\\Helper',
			],
			'calendar.service.icloud.helper' => [
				'className' => '\\Bitrix\\Calendar\\Sync\\ICloud\\Helper'
			],
			'calendar.service.caldav.helper' => [
				'className' => '\\Bitrix\\Calendar\\Sync\\Caldav\\Helper',
			],
			'calendar.service.handlers' => [
				'className' => '\\Bitrix\\Calendar\\Core\\Handlers\\HandlersMap',
			],
			'calendar.service.mappers.factory' => [
				'className' => '\\Bitrix\\Calendar\\Core\\Mappers\\Factory',
			],
			Integration\Im\EventCategoryServiceInterface::class => [
				'className' => '\\Bitrix\\Calendar\\Integration\\Im\\EventCategoryService',
			],
			EventManagerInterface::class => [
				'className' => '\\Bitrix\\Calendar\\Internals\\EventManager\\EventManager',
			],
			'calendar.container' => [
				'className' => Container::class,
			],
		],
		'readonly' => true,
	],
	'ui.entity-selector' => [
		'value' => [
			'entities' => [
				[
					'entityId' => 'room',
					'provider' => [
						'moduleId' => 'calendar',
						'className' => '\\Bitrix\\Calendar\\Integration\\UI\\EntitySelector\\RoomProvider'
					],
				],
				[
					'entityId' => 'event-category',
					'provider' => [
						'moduleId' => 'calendar',
						'className' => EntitySelector\OpenEvents\CategoryProvider::class,
					],
				],
				[
					'entityId' => 'im-channel',
					'provider' => [
						'moduleId' => 'calendar',
						'className' => EntitySelector\OpenEvents\ChannelProvider::class,
					],
				],
			],
			// 'extensions' => ['calendar.entity-selector'],
			'filters' => [
				[
					'id' => 'calendar.roomFilter',
					'entityId' => 'room',
					'className' => '\\Bitrix\\Calendar\\Integration\\UI\\EntitySelector\\RoomFilter',
				],
				[
					'id' => 'calendar.attendeeFilter',
					'entityId' => 'user',
					'className' => '\\Bitrix\\Calendar\\Integration\\UI\\EntitySelector\\Attendee\\Filter',
				],
				[
					'id' => 'calendar.jointSharingFilter',
					'entityId' => 'user',
					'className' => '\\Bitrix\\Calendar\\Integration\\UI\\EntitySelector\\JointSharing\\Filter',
				],
			],
		],
		'readonly' => true,
	],
	'messenger' => [
		'value' => [
			'brokers' => [
				'calendar_db' => [
					'type' => \Bitrix\Main\Messenger\Internals\Broker\DbBroker::TYPE_CODE,
					'params' => [
						'table' => \Bitrix\Calendar\Synchronization\Internal\Model\MessengerTable::class,
						'module' => 'calendar',
					]
				]
			],
			'queues' => [
				'google_section_sync' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\GoogleSectionReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1200
					],
				],
				'icloud_section_sync' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\ICloudSectionReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1200
					],
				],
				'office365_section_sync' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\Office365SectionReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1200
					],
				],
				'google_event_sync' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\GoogleEventReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1800
					],
				],
				'icloud_event_sync' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\ICloudEventReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1800
					],
				],
				'office365_event_sync' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\Office365EventReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1800
					],
				],
				'google_push' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\GooglePushReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1200
					],
				],
				'office365_push' => [
					'broker' => 'calendar_db',
					'handler' => \Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Receiver\Office365PushReceiver::class,
					'retry_strategy' => [
						'max_retries' => 30,
						'delay' => 5,
						'multiplier' => 2,
						'max_delay' => 1200
					],
				],
			],
		],
		'readonly' => true,
	],
];
