<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Service;

final class SynchronizationFeature
{
	private const MODULE_ID = 'calendar';
	private const FEATURE_ID = 'new_sync';

	private static ?int $userId = null;

	public static function isOn(): bool
	{
		return true;
	}

	public static function isOnForUser(int $userId): bool
	{
		return true;
	}

	public static function setUserId(?int $userId): void
	{
		self::$userId = $userId;
	}
}
