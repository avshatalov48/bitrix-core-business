<?php

namespace Bitrix\Im\V2\Chat;

class OpenLineLiveChat extends EntityChat
{
	protected $entityMap = [
		'entityId' => [
			'connectorId',
			'lineId',
		],
		'entityData1' => [
			'readed',
			'readedId',
			'readedTime',
			'sessionId',
			'showForm',
			'welcomeFormNeeded',
			'welcomeFormSent'
		],
		'entityData2' => [],
		'entityData3' => [],
	];

	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_LIVECHAT;
	}

	public function setEntityMap(array $entityMap): EntityChat
	{
		return $this;
	}
}