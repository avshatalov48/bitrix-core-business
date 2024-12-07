<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Queue;

use Bitrix\Main\UserTable;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventTable;
use Bitrix\Socialnetwork\Internals\EventService\Exception\CounterQueuePopException;
use Bitrix\Socialnetwork\Internals\EventService\Processors\SpaceEventProcessor;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;

class Queue
{
	const PRIORITY_LOW = 2;
	const PRIORITY_MEDIUM = 1;
	const PRIORITY_HIGH = 0;

	private array $popped = [];
	private array $buffer = [];
	private static Queue|null $instance = null;

	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{

	}

	public function add(Event $event, Recepient $user): void
	{
		$this->buffer[] = [
			'EVENT_ID' => $event->getId(),
			'USER_ID' => $user->getId(),
			'PRIORITY' => $this->getPriority($user),
		];
	}

	public function save(): void
	{
		if (empty($this->buffer))
		{
			return;
		}

		QueueTable::addMulti($this->buffer, true);
		$this->buffer = [];
		(new Agent())->addAgent();
	}

	public function process(): int
	{
		$spaceProcessor = new SpaceEventProcessor();
		$queue = $this->get($spaceProcessor->getStepLimit());
		$events = $this->getEvents($queue['event_ids']);
		$users = $this->getUsers($queue['user_ids']);

		foreach ($queue['rows'] as $row)
		{
			$event = (isset($events[$row['event_id']])) ? $events[$row['event_id']] : null;
			$user = (isset($users[$row['user_id']])) ? $users[$row['user_id']] : null;

			if ($event && $user)
			{
				$spaceProcessor->processEventForUser($event, $user);
			}
		}

		$this->done();

		return count($queue['rows']);
	}

	private function get(int $limit): array
	{
		if (!empty($this->popped))
		{
			throw new CounterQueuePopException();
		}

		$iterator = QueueTable::getList([
			'select' => [
				'ID',
				'USER_ID',
				'EVENT_ID',
				'PRIORITY',
			],
			'order' => [
				'PRIORITY' => 'ASC',
				'DATETIME' => 'ASC',
			],
			'limit' => $limit,
		]);

		$queue = [
			'event_ids' => [],
			'user_ids' => [],
			'rows' => [],
		];

		while ($row = $iterator->fetch())
		{
			$this->popped[] = $row['ID'];

			$userId = (int) $row['USER_ID'];
			$eventId = (int) $row['EVENT_ID'];
			$queue['user_ids'][] = $userId;
			$queue['event_ids'][] = $eventId;
			$queue['rows'][] = [
				'user_id' => $userId,
				'event_id' => $eventId,
			];
		}

		return $queue;
	}

	private function done(): void
	{
		if (empty($this->popped))
		{
			return;
		}

		QueueTable::deleteByFilter([
			'ID' => $this->popped,
		]);

		$this->popped = [];
	}

	private function getPriority(Recepient $user): int
	{
		if (!$user->isOnline())
		{
			return self::PRIORITY_LOW;
		}

		if ($user->isWatchingSpaces())
		{
			return self::PRIORITY_HIGH;
		}

		return self::PRIORITY_MEDIUM;
	}

	private function getEvents(array $eventIds): array
	{
		if (empty($eventIds))
		{
			return [];
		}

		$res = EventTable::getList([
			'select' => ['ID', 'HID', 'TYPE', 'DATA'],
			'filter' => [
				'ID' => $eventIds,
			],
		]);

		$events = [];

		while ($row = $res->fetch())
		{
			$events[$row['ID']] = Event\Factory::buildEvent(
				$row['HID'],
				$row['TYPE'],
				Json::decode($row['DATA']),
				$row['ID']
			);
		}

		return $events;
	}

	private function getUsers(array $userIds): array
	{
		if (empty($userIds))
		{
			return [];
		}

		$res = UserTable::getList([
			'select' => ['ID', 'IS_ONLINE'],
			'filter' => [
				'ID' => $userIds,
			],
		]);

		$users = [];

		while ($row = $res->fetch())
		{
			$isOnline = ($user['IS_ONLINE'] ?? 'Y') === 'Y';
			$users[$row['ID']] = new Recepient($row['ID'], $isOnline);
		}

		return $users;
	}
}