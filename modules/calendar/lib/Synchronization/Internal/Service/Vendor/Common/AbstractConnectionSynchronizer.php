<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common;

use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;

abstract class AbstractConnectionSynchronizer
{
	public function __construct(
		private readonly ConnectionProvider $connectionProvider,
		protected readonly RequestLogger $logger
	)
	{
	}

	protected function getConnection(int $userId, string $vendorCode): ?Connection
	{
		try
		{
			$connections = $this->connectionProvider->getActiveConnections($userId, User::TYPE, [$vendorCode]);
		}
		catch (\Exception $e)
		{
			$this->logger->error(
				'Unable to get connections of user ' . $userId,
				[
					'message' => $e->getMessage(),
					'code' => $e->getCode(),
					'trace' => $e->getTraceAsString(),
					'type' => $vendorCode,
					'userId' => $userId,
				]
			);
		}

		if (empty($connections))
		{
			return null;
		}

		if (count($connections) === 1)
		{
			return $connections[0];
		}

		$this->logger->notice(
			sprintf(
				'The user %d has %d connections with Office 365',
				$userId,
				count($connections)
			),
			[
				'userId' => $userId,
				'type' => $vendorCode,
			]
		);

		return null;
	}
}
