<?php

namespace Bitrix\Calendar\Core\Queue\Processor;

use Bitrix\Calendar\Core\Queue\Interfaces;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Watcher\Membership\Handler\Handler;
use Bitrix\Main\Application;

class EventsWithEntityAttendeesFind implements Interfaces\Processor
{
	const CONVERT_DATA = [
		Handler::WORK_GROUP_TYPE => 'SG',
		Handler::DEPARTMENT_TYPE => 'DR',
		Handler::ALL_USERS_TYPE => 'UA',
	];

	/**
	 * @param Interfaces\Message $message
	 * @return string
	 */
	public function process(Interfaces\Message $message): string
	{
		$data = $message->getBody();

		if (empty($data['entityType']))
		{
			return self::REJECT;
		}

		$attendeeCode = $this->convertEntityToAttendeeCode($data['entityType'], (int)($data['entityId'] ?? null));

		$helper = Application::getConnection()->getSqlHelper();

		$events = EventTable::getList([
			'select' => ['ID'],
			'filter' => \Bitrix\Main\ORM\Query\Query::filter()
				->where('DELETED', 'N')
				->whereExpr($helper->getRegexpOperator('%s', "'{$attendeeCode}" . "(_|$|,)'"), ['ATTENDEES_CODES'])
				->whereColumn('ID', 'PARENT_ID')
				//do subtract to make sampling more accurate
				->where('DATE_TO_TS_UTC', '>=', time() - \CCalendar::GetDayLen())
			,
			'order' => [
				'ID' => 'ASC',
			],
		])->fetchAll();

		if (count($events) === 1)
		{
			$this->sendMessageToQueue($events[0]['ID']);
		}
		else
		{
			$this->sendBatchOfMessagesToQueue($events);
		}

		return self::ACK;
	}

	/**
	 * @param string $entityType
	 * @param int|null $entityId
	 * @return string
	 */
	protected function convertEntityToAttendeeCode(string $entityType, int $entityId = null): string
	{
		$attendeeCodeType = self::CONVERT_DATA[$entityType] ?? null;

		return $attendeeCodeType ? $attendeeCodeType . ($entityId ?: '')  : '';
	}

	/**
	 * @param int $eventId
	 * @return void
	 */
	protected function sendMessageToQueue(int $eventId): void
	{
		$message = (new \Bitrix\Calendar\Core\Queue\Message\Message())
			->setBody([
				'eventId' => $eventId,
			])
			->setRoutingKey('calendar:update_event_attendees');

		(new \Bitrix\Calendar\Core\Queue\Producer\Producer())->send($message);
	}

	/**
	 * @param array $events
	 * @return void
	 */
	protected function sendBatchOfMessagesToQueue(array $events): void
	{
		$messages = [];

		foreach ($events as $event)
		{
			if (!empty($event['ID']))
			{
				$messages[] = (new \Bitrix\Calendar\Core\Queue\Message\Message())
					->setBody([
						'eventId' => $event['ID'],
					])
					->setRoutingKey('calendar:update_event_attendees')
				;
			}
		}

		(new \Bitrix\Calendar\Core\Queue\Producer\Producer())->sendBatch($messages);
	}
}