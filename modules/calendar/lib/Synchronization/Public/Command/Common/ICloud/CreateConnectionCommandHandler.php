<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\ICloud;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Role\Helper as RoleHelper;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Integration\Dav\Service\ConnectionManager;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Core\Mappers\Connection as ConnectionMapper;
use Bitrix\Calendar\Sync\Handlers\MasterPushHandler;
use Bitrix\Calendar\Sync\Icloud\Factory;
use Bitrix\Calendar\Sync\Icloud\Helper;
use Bitrix\Calendar\Sync\Icloud\VendorSyncService;
use Bitrix\Calendar\Sync\Vendor\Vendor;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Throwable;

class CreateConnectionCommandHandler
{
	public function __construct(
		private readonly ConnectionProvider $connectionProvider,
		private readonly ConnectionManager $connectionManager,
		private readonly ConnectionMapper $connectionMapper,
		private readonly VendorSyncService $vendorSyncService,
		private readonly Helper $helper,
	)
	{
	}

	/**
	 * @throws Exception
	 */
	public function __invoke(CreateConnectionCommand $command): ?Connection
	{
		try
		{
			$owner = RoleHelper::getRole($command->userId, User::TYPE);
		}
		catch (SystemException $e)
		{
			throw new Exception('Unable to create owner of connection for iCloud', $e->getCode(), $e);
		}

		try
		{
			$connection = $this->createConnection(
				$owner,
				$command->appleId,
				$command->applicationPassword,
			);
		}
		catch (Throwable $e)
		{
			throw new Exception('Unable to create connection for iCloud', $e->getCode(), $e);
		}

		if ($connection)
		{
			$this->addPullEvent($connection, MasterPushHandler::MASTER_STAGE[0]);
		}

		return $connection;
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws Exception
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws SqlQueryException
	 * @throws ObjectException
	 */
	private function createConnection(
		Role $owner,
		string $appleId,
		string $applicationPassword,
	): ?Connection
	{
		$connections = $this->connectionProvider->getConnections(
			$owner->getId(),
			$owner->getType(),
			[Factory::SERVICE_NAME],
		);

		$this->deactivateCurrentConnections($connections);

		$existingConnection = null;

		$authorizationData = [
			'SERVER_USERNAME' => $appleId,
			'SERVER_PASSWORD' => $applicationPassword,
		];

		$serverPath = $this->vendorSyncService->getCalendarServerPath($authorizationData);

		foreach ($connections as $connection)
		{
			$currentServerPath = $connection->getServer()->getFullPath();

			if ($currentServerPath !== $serverPath)
			{
				continue;
			}

			if ($existingConnection === null)
			{
				$existingConnection = $connection;
			}
			else
			{
				$this->deleteConnectionData((int)$connection->getId());
			}
		}

		if ($existingConnection)
		{
			$existingConnection->setDeleted(false);

			$existingConnection->getServer()->setPassword($applicationPassword);

			$this->connectionMapper->update($existingConnection);

			return $existingConnection;
		}

		$parsedServerPath = $this->parseServerPath($serverPath);

		$name = $this->helper->getConnectionName($appleId);

		$vendor = new Vendor([
			'ENTITY_ID' => (int)$owner->getId(),
			'ENTITY_TYPE' => $owner->getType(),
			'SERVER_SCHEME' => $parsedServerPath['scheme'] ?? null,
			'SERVER_HOST' => $parsedServerPath['host'] ?? null,
			'SERVER_PORT' => $parsedServerPath['port'] ?? null,
			'SERVER_PATH' => $parsedServerPath['path'] ?? null,
			'SERVER_USERNAME' => $appleId,
			'SERVER_PASSWORD' => $applicationPassword,
			'NAME' => $name,
			'ACCOUNT_TYPE' => Helper::ACCOUNT_TYPE,
			...$authorizationData,
		]);

		$newConnection =
			(new Connection())
				->setVendor($vendor)
				->setOwner($owner)
				->setName($name)
		;

		return $this->connectionMapper->create($newConnection);
	}

	/**
	 * @param Connection[] $connections
	 */
	private function deactivateCurrentConnections(array $connections): void
	{
		foreach ($connections as $connection)
		{
			if (!$connection->isDeleted())
			{
				$this->connectionManager->deactivateConnection($connection);
			}
		}
	}

	/**
	 * @throws SqlQueryException
	 */
	private function deleteConnectionData(int $connectionId): void
	{
		global $DB;

		// TODO: Use repository
		$DB->Query(sprintf('DELETE FROM b_calendar_event_connection WHERE CONNECTION_ID = %d;', $connectionId));
		$DB->Query(sprintf('DELETE FROM b_calendar_section_connection WHERE CONNECTION_ID = %d;', $connectionId));
		$DB->Query(sprintf('DELETE FROM b_dav_connections WHERE ID = %d;', $connectionId));
	}

	private function addPullEvent(Connection $connection, string $stage): void
	{
		Util::addPullEvent(
			PushCommand::ProcessSyncConnection,
			(int)$connection->getOwner()?->getId(),
			[
				'vendorName' => Helper::ACCOUNT_TYPE,
				'accountName' => $connection->getServer()->getUserName(),
				'stage' => $stage,
			],
		);
	}

	private function parseServerPath(string $serverPath): array
	{
		$parsedUrl = parse_url($serverPath);

		if (empty($parsedUrl['port']))
		{
			$parsedUrl['port'] =
				$parsedUrl['scheme'] === 'https'
					? 443
					: 80
			;
		}

		return $parsedUrl;
	}
}
