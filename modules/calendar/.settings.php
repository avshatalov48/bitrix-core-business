<?php
	return [
		'controllers' => [
			'value' => [
				'namespaces' => [
					'\\Bitrix\\Calendar\\Controller' => 'api',
				],
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
	];