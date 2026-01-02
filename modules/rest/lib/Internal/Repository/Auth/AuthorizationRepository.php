<?php

declare(strict_types=1);

namespace Bitrix\Rest\Internal\Repository\Auth;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Rest\Internal\Model;

class AuthorizationRepository
{
	protected const LOCK_NAME = 'rest_auth_log';
	protected const LOCK_LIMIT = 0;

	private const CACHE_ID = 'rest_auth_log';
	private const CACHE_TTL = 3600;
	private Main\Data\ManagedCache $managedCache;

	public function __construct()
	{
		$this->managedCache = Application::getInstance()->getManagedCache();
	}

	public function saveAuthorization(int $userId, int $applicationId, Main\Type\DateTime $time): void
	{
		$cacheId = $this->getCacheId($userId, $applicationId, $time);
		$cacheIsSet = $this->managedCache->read(static::CACHE_TTL, $cacheId) === true;

		if ($cacheIsSet)
		{
			return;
		}

		$this->managedCache->setImmediate($cacheId, 'Y');

		if ($this->lock($userId))
		{
			\CEventLog::Log(
				\CEventLog::SEVERITY_SECURITY,
				'USER_AUTHORIZE',
				'rest',
				$userId,
				json_encode([
					'userId' => $userId,
					'applicationId' => $applicationId,
					'timePeriod' => $time->format('Y-m-d H'),
				])
			);

			$this->unlock($userId);
		}
	}

	private function lock(int $userId): bool
	{
		$connection = Application::getConnection();

		return $connection->lock(self::LOCK_NAME . '_ ' . $userId, self::LOCK_LIMIT);
	}

	private function unlock(int $userId): void
	{
		$connection = Application::getConnection();

		$connection->unlock(self::LOCK_NAME . '_ ' . $userId);
	}

	private function getCacheId(int $userId, int $applicationId, Main\Type\DateTime $time): string
	{
		return static::CACHE_ID . '_' . $userId . '_' . $applicationId . '_' . $time->format('Y-m-d H');
	}
}
