<?php
namespace Bitrix\Landing\Connector;

use \Bitrix\Landing\Source;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;

Loc::loadMessages(__FILE__);

class Landing
{
	/**
	 * Register new source.
	 * @param Event $event Event instance.
	 * @return EventResult
	 */
	public static function onSourceBuildHandler(Event $event)
	{
		$result = [];

		// pages
		$result[] = [
			'SOURCE_ID' => 'landing',
			'TITLE' => Loc::getMessage('LANDING_CONNECTOR_SOURCE_TITLE'),
			'TYPE' => Source\Selector::SOURCE_TYPE_PRESET,
			'SETTINGS' => [
				'FILTER' => [
					[
						'key' => 'LANDING',
						'name' => Loc::getMessage('LANDING_CONNECTOR_SOURCE_TITLE'),
						'value' => ['VALUE' => '']
					]
				],
				'DETAIL_PAGE' => false
			],
			'SOURCE_FILTER' => [
				'=SYS' => 'N'
			],
			'DATA_SETTINGS' => [
				'FIELDS' => [
					[
						'id' => 'TITLE',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_TITLE'),
						'type' => \Bitrix\Landing\Node\Type::TEXT
					],
					[
						'id' => 'DESCRIPTION',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_DESCRIPTION'),
						'type' => \Bitrix\Landing\Node\Type::TEXT
					],
					[
						'id' => 'IMAGE',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_IMAGE'),
						'type' => \Bitrix\Landing\Node\Type::IMAGE
					],
					[
						'id' => 'LINK',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_LINK'),
						'type' => \Bitrix\Landing\Node\Type::LINK,
						'actions' => [
							[
								'type' => 'landing',
								'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_LINK')
							],
							[
								'type' => 'link',
								'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_LINK_LINK')
							]
						]
					]
				],
				'ORDER' => [
					[
						'id' => 'VIEWS',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_VIEWS')
					],
					[
						'id' => 'DATE_CREATE',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_DATE_CREATE')
					],
					[
						'id' => 'DATE_MODIFY',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_DATE_MODIFY')
					],
					[
						'id' => 'TITLE',
						'name' => Loc::getMessage('LANDING_CONNECTOR_FIELD_TITLE')
					]
				]
			],
			'DATA_LOADER' => '\Bitrix\Landing\DataLoader\Landing'
		];

		// chats (system item)
		if (false)
		$result[] = [
			'SOURCE_ID' => 'chat',
			'TITLE' => 'Chats',
			'TYPE' => Source\Selector::SOURCE_TYPE_PRESET,
			'SETTINGS' => [
				'FILTER' => [
					[
						'key' => 'NULL',
						'name' => 'Chats',
						'value' => ['VALUE' => '']
					]
				],
				'DETAIL_PAGE' => false
			],
			'DATA_SETTINGS' => [
				'FIELDS' => [
					[
						'id' => 'NAME',
						'name' => 'NAME',
						'type' => \Bitrix\Landing\Node\Type::TEXT
					],
					[
						'id' => 'WORK_POSITION',
						'name' => 'WORK_POSITION',
						'type' => \Bitrix\Landing\Node\Type::TEXT
					],
					[
						'id' => 'SEND',
						'name' => 'SEND',
						'type' => \Bitrix\Landing\Node\Type::LINK
					],
					[
						'id' => 'AVATAR',
						'name' => 'AVATAR',
						'type' => \Bitrix\Landing\Node\Type::IMAGE
					]
				],
				'ORDER' => [
					[
						'id' => 'NAME',
						'name' => 'NAME'
					]
				]
			],
			'DATA_LOADER' => '\Bitrix\Landing\DataLoader\Chat'
		];

		return new EventResult(EventResult::SUCCESS, $result, 'landing');
	}
}