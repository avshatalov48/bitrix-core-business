<?php

namespace Bitrix\Bizproc\Integration\Push;

abstract class BasePush
{
	public const EVENT_ADDED = 'ADDED';
	public const EVENT_UPDATED = 'UPDATED';
	public const EVENT_DELETED = 'DELETED';

	abstract protected static function getCommand(): string;

	public static function subscribeUser(int $userId): void
	{
		(new PushWorker())->subscribe($userId, static::getCommand());
	}

	public static function pushAdded(mixed $itemId, ...$userIds): void
	{
		static::pushLastEvent(static::EVENT_ADDED, $itemId, $userIds);
	}

	public static function pushUpdated(mixed $itemId, ...$userIds): void
	{
		static::pushLastEvent(static::EVENT_UPDATED, $itemId, $userIds);
	}

	public static function pushDeleted(mixed $itemId, ...$userIds): void
	{
		static::pushLastEvent(static::EVENT_DELETED, $itemId, $userIds);
	}

	public static function pushLastEvent(string $eventName, mixed $itemId, array $userIds): void
	{
		$userIds = array_unique(\CBPHelper::flatten($userIds));
		if (empty($userIds))
		{
			return;
		}

		$command = static::getCommand();

		$push = new PushWorker();
		$push->sendLast(
			"{$command}-{$itemId}-{$eventName}",
			$command,
			[
				'eventName' => $eventName,
				'items' => [
					[
						'id' => $itemId,
					],
				],
			],
			$userIds,
		);
	}
}
