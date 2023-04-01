<?php

namespace Bitrix\Calendar\Watcher\Membership\Handler;

abstract class Handler
{
	const WORK_GROUP_TYPE = 'project';
	const DEPARTMENT_TYPE = 'department';
	const ALL_USERS_TYPE = 'all-users';

	protected static array $storedData = [];

	/**
	 * @param array $data
	 * @return void
	 */
	protected static function sendBatchOfMessagesToQueue(array $data): void
	{
		$messages = [];
		foreach ($data as $datum)
		{
			if (!empty($datum['entityType']) && !empty($datum['entityId']))
			{
				$messages[] = (new \Bitrix\Calendar\Core\Queue\Message\Message())
					->setBody([
						'entityType' => $datum['entityType'],
						'entityId' => $datum['entityId'],
					])
					->setRoutingKey('calendar:find_events_with_entity_attendees')
				;
			}
		}

		(new \Bitrix\Calendar\Core\Queue\Producer\Producer())->sendBatch($messages);
	}

	/**
	 * @param string $entityType
	 * @param $entityId
	 * @return void
	 */
	protected static function sendMessageToQueue(string $entityType, $entityId = null): void
	{
		$message = (new \Bitrix\Calendar\Core\Queue\Message\Message())
			->setBody([
				'entityType' => $entityType,
				'entityId' => $entityId,
			])
			->setRoutingKey('calendar:find_events_with_entity_attendees')
		;

		(new \Bitrix\Calendar\Core\Queue\Producer\Producer())->send($message);
	}
}