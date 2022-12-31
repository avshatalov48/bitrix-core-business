<?php

namespace Bitrix\Calendar\Core\Queue\Processor;

use Bitrix\Calendar\Core\Queue\Interfaces;

class EventDelayedSync implements Interfaces\Processor
{
	public function process(Interfaces\Message $message): string
	{
		$data = $message->getBody();

		if (!$data['parentId'])
		{
			return self::REJECT;
		}

		$event =\CCalendarEvent::GetById($data['parentId'], false);
		if (!$event)
		{
			return self::REJECT;
		}

		\CCalendar::SaveEvent([
			'arFields' => [
				'ID' => $data['parentId'],
			],
			'checkPermission' => false,
			'overSaving' => true,
		]);

		return self::ACK;
	}
}