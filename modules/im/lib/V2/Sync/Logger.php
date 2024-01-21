<?php

namespace Bitrix\Im\V2\Sync;

use Bitrix\Im\Model\EO_Log_Collection;
use Bitrix\Im\Model\LogTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Pull\Model\PushTable;

class Logger
{
	use ContextCustomer;

	public const DEFAULT_EXPIRY_INTERVAL = '+4 weeks';
	public const FAST_EXPIRY_INTERVAL = '+1 days';

	private static Logger $instance;

	private bool $isAlreadyPlanned = false;
	private array $events = [];
	private ?array $allowedUsers = null;

	private function __construct()
	{
	}

	public static function getInstance(): self
	{
		self::$instance ??= new Logger();

		return self::$instance;
	}

	public function add(Event $event, $userId): void
	{
		if (!SyncService::isEnable())
		{
			return;
		}

		$userId ??= $this->getContext()->getUserId();
		$this->events[] = ['event' => $event, 'user' => $userId];

		if (!$this->isAlreadyPlanned)
		{
			Application::getInstance()->addBackgroundJob(fn () => $this->addDeferred());
			$this->isAlreadyPlanned = true;
		}
	}

	public function updateDateDelete(EO_Log_Collection $logs, ?DateTime $dateDelete = null): void
	{
		Application::getInstance()->addBackgroundJob(fn () => $this->updateDateDeleteDeferred($logs, $dateDelete));
	}

	public static function cleanAgent(): string
	{
		return '';

		(new static())->clean();

		return __METHOD__ . '();';
	}

	public function clean(): void
	{
		$now = new DateTime();
		LogTable::deleteByFilter(['<=DATE_DELETE' => $now]);
	}

	private function addDeferred(): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		$this->runClosureInEvents();
		$groupedEvents = $this->getGroupedEvents();

		foreach ($groupedEvents as ['event' => $event, 'user' => $userId])
		{
			if (is_int($userId))
			{
				LogTable::merge(...$this->getMergeParam($event, $userId));
			}
			if (is_array($userId))
			{
				LogTable::multiplyMerge(...$this->getMultiplyMergeParam($event, $userId));
			}
		}
		$this->events = [];
		$this->isAlreadyPlanned = false;
	}

	private function updateDateDeleteDeferred(EO_Log_Collection $logs, ?DateTime $dateDelete): void
	{
		return;

		if ($dateDelete === null)
		{
			$dateDelete = new DateTime();
			$dateDelete->add(self::FAST_EXPIRY_INTERVAL);
		}

		$newDateDeleteTs = $dateDelete->getTimestamp();
		foreach ($logs as $log)
		{
			$oldDateDelete = $log->getDateDelete();
			if ($oldDateDelete === null || $oldDateDelete->getTimestamp() > $newDateDeleteTs)
			{
				$log->setDateDelete($dateDelete);
			}
		}

		$logs->save(true);
	}

	private function getGroupedEvents(): array
	{
		$result = [];

		/** @var Event $event */
		foreach ($this->events as ['event' => $event, 'user' => $userId])
		{
			$userId = $this->filterUsers($this->getUsersFromEvent(['user' => $userId]));

			if (empty($userId))
			{
				continue;
			}

			$implodedUserIds = implode('-', $userId);
			$key = "{$event->eventName}|{$event->entityType}|{$event->entityId}|{$implodedUserIds}";

			if (count($userId) === 1)
			{
				$userId = array_pop($userId);
			}

			$result[$key] = ['event' => $event, 'user' => $userId];
		}

		return array_values($result);
	}

	private function filterUsers(array $users): array
	{
		if (!isset($this->allowedUsers))
		{
			$this->fillAllowedUsers();
		}

		foreach ($users as $key => $userId)
		{
			if (!isset($this->allowedUsers[$userId]))
			{
				unset($users[$key]);
			}
		}

		return $users;
	}

	private function fillAllowedUsers(): void
	{
		$allUsers = $this->getUsers();

		//$usersWithMobile = $this->filterWithoutMobile($allUsers);
		$activeUsersWithMobile = $this->filterInactive($allUsers);
		$result = [];

		foreach ($activeUsersWithMobile as $userId)
		{
			$result[$userId] = $userId;
		}

		$this->allowedUsers = $result;
	}

	private function getUsers(): array
	{
		$users = [];

		foreach ($this->events as $event)
		{
			$eventUsers = $this->getUsersFromEvent($event);

			foreach ($eventUsers as $eventUser)
			{
				$users[$eventUser] = $eventUser;
			}
		}

		return $users;
	}

	private function getUsersFromEvent(array $eventItem): array
	{
		$users = $eventItem['user'] ?? [];

		if (is_int($users))
		{
			$users = [$users];
		}

		if (!is_array($users))
		{
			return [];
		}

		return $users;
	}

	private function filterWithoutMobile(array $userIds): array
	{
		if (empty($userIds))
		{
			return $userIds;
		}

		return PushTable::query()
			->setSelect(['USER_ID'])
			->whereIn('USER_ID', $userIds)
			->fetchCollection()
			->getUserIdList()
		;
	}

	private function filterInactive(array $userIds): array
	{
		if (empty($userIds))
		{
			return $userIds;
		}

		return UserTable::query()
			->setSelect(['ID'])
			->whereIn('ID', $userIds)
			->where('ACTIVE', true)
			->where('IS_REAL_USER', true)
			->fetchCollection()
			->getIdList()
		;
	}

	private function runClosureInEvents(): void
	{
		foreach ($this->events as $key => ['event' => $event, 'user' => $userId])
		{
			if (is_callable($userId))
			{
				$this->events[$key]['user'] = $userId();
			}
		}
	}

	private function getMergeParam(Event $event, int $userId): array
	{
		return [
			[
				'USER_ID' => $userId,
				'ENTITY_TYPE' => $event->entityType,
				'ENTITY_ID' => $event->entityId,
				'EVENT' => $event->eventName,
				'DATE_CREATE' => $event->getDateCreate(),
				'DATE_DELETE' => $event->getDateDelete(),
			],
			[
				'EVENT' => $event->eventName,
				'DATE_CREATE' => $event->getDateCreate(),
				'DATE_DELETE' => $event->getDateDelete(),
			]
		];
	}

	private function getMultiplyMergeParam(Event $event, array $userId): array
	{
		$insertFields = [];

		foreach ($userId as $id)
		{
			$insertFields[] = [
				'USER_ID' => $id,
				'ENTITY_TYPE' => $event->entityType,
				'ENTITY_ID' => $event->entityId,
				'EVENT' => $event->eventName,
				'DATE_CREATE' => $event->getDateCreate(),
				'DATE_DELETE' => $event->getDateDelete(),
			];
		}

		return [
			$insertFields,
			[
				'EVENT' => $event->eventName,
				'DATE_CREATE' => $event->getDateCreate(),
				'DATE_DELETE' => $event->getDateDelete(),
			],
		];
	}
}