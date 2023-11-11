<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Mappers\Connection;
use Bitrix\Calendar\Core\Mappers\SectionConnection;
use Bitrix\Calendar\Core\Queue;
use Bitrix\Calendar\Core\Queue\Exception\InvalidDestinationException;
use Bitrix\Calendar\Core\Queue\Exception\InvalidMessageException;
use Bitrix\Calendar\Internals\EO_Push;
use Bitrix\Calendar\Core\Base\Mutex;
use Bitrix\Calendar\Internals\PushTable;
use Bitrix\Calendar\Sync\Builders\BuilderPushFromDM;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Factories\FactoryBuilder;
use Bitrix\Calendar\Sync\Factories\SyncSectionFactory;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Throwable;

class PushManager
{
	public const TYPE_CONNECTION = 'CONNECTION';
	public const TYPE_SECTION_CONNECTION = 'SECTION_CONNECTION';
	public const TYPE_SECTION = 'SECTION';

	private const LOCK_CONNECTION_TIME = 20;

	public const QUEUE_ROUTE_KEY_SECTION = 'calendar:SyncSectionPush';
	public const QUEUE_ROUTE_KEY_CONNECTION = 'calendar:SyncConnectionPush';

	/**
	 * @param string $entityType
	 * @param int $entityId
	 *
	 * @return Push|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getPush(string $entityType, int $entityId): ?Push
	{
		$data = PushTable::query()
			->setSelect(['*'])
			->addFilter('=ENTITY_TYPE', $entityType)
			->addFilter('ENTITY_ID', $entityId)
			->exec()->fetchObject();
		if ($data)
		{
			return (new BuilderPushFromDM($data))->build();
		}

		return null;
	}

	/**
	 * @param string $entityType
	 * @param int $entityId
	 * @param array $data
	 *
	 * @return Result
	 *
	 * @throws ObjectException
	 * @throws Exception
	 */
	public function addPush(string $entityType, int $entityId, array $data): Result
	{
		$result = new Result();
		$data['ENTITY_TYPE'] = $entityType;
		$data['ENTITY_ID'] = $entityId;

		/** @var EO_Push $addRsult */
		if ($addResult = PushTable::add($data)->getObject())
		{
			$result->setData([
				'push' => (new BuilderPushFromDM($addResult))->build(),
			]);
		}
		else
		{
			$result->addError(new Error('Error of add push info into db.'));
		}

		return $result;
	}

	/**
	 * @param Push $push
	 * @param array $data
	 *
	 * @return Result
	 *
	 * @throws ObjectException
	 * @throws Exception
	 */
	public function renewPush(Push $push, array $data): Result
	{
		$result = new Result();

		// TODO: move this logic to push-mapper
		$updateResult = PushTable::update([
			'ENTITY_TYPE' => $push->getEntityType(),
			'ENTITY_ID' => $push->getEntityId(),
		], $data);

		if ($updateResult->isSuccess())
		{
			$push->setExpireDate(new Date($data['EXPIRES']));
			$result->setData([
				'push' => $push,
			]);
		}
		else
		{
			$result->addError(new Error('Error of update push in db.'));
		}

		return $result;
	}

	/**
	 * @param Push $pushChannel
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function updatePush(Push $pushChannel): void
	{
		$data = [
			'CHANNEL_ID' => $pushChannel->getChannelId(),
			'RESOURCE_ID' => $pushChannel->getResourceId(),
			'EXPIRES' => $pushChannel->getExpireDate()
				? $pushChannel->getExpireDate()->getDate()
				: null
			,
			'NOT_PROCESSED' => $pushChannel->getProcessStatus(),
			'FIRST_PUSH_DATE' => $pushChannel->getFirstPushDate()
				? $pushChannel->getFirstPushDate()->getDate()
				: null
		];
		PushTable::update(
			[
				'ENTITY_TYPE' => $pushChannel->getEntityType(),
				'ENTITY_ID' => $pushChannel->getEntityId(),
			],
			$data
		);
	}

	/**
	 * @param Push $push
	 * @return void
	 * @throws Exception
	 */
	public function deletePush(Push $push): void
	{
		PushTable::delete([
			'ENTITY_TYPE' => $push->getEntityType(),
			'ENTITY_ID' => $push->getEntityId(),
		]);
	}

	/**
	 * @param string $channel
	 * @param string $resourceId
	 * @param bool $forceUnprocessedPush
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function handlePush(string $channel, string $resourceId, bool $forceUnprocessedPush = false): Result
	{
		$result = new Result();
		$row = PushTable::query()
			->setSelect(['*'])
			->addFilter('=CHANNEL_ID', $channel)
			->addFilter('=RESOURCE_ID', $resourceId)
			->exec()->fetchObject()
		;
		if ($row)
		{
			$push = (new BuilderPushFromDM($row))->build();

			if ($push->isBlocked())
			{
				$this->setUnprocessedPush($push);

				return new Result();
			}

			if (!$forceUnprocessedPush && $push->isUnprocessed())
			{
				return new Result();
			}

			try
			{
				$this->blockPush($push);
				if ($push->getEntityType() === self::TYPE_SECTION_CONNECTION)
				{
					$this->syncSection($push);
				}
				elseif ($push->getEntityType() === self::TYPE_CONNECTION)
				{
					$this->syncConnection($push);
				}

				if ($this->getPushState($push->getEntityType(), $push->getEntityId())
					=== Dictionary::PUSH_STATUS_PROCESS['unprocessed'])
				{
					$this->handlePush($channel, $resourceId, true);
				}
			}
			catch(Throwable $e)
			{
			}
			finally
			{
				$this->setUnblockPush($push);
			}


		}

		return $result;
	}

	/**
	 * @param string $entityType
	 * @param string $entityId
	 *
	 * @return mixed|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function getPushState(string $entityType, string $entityId)
	{
		$row = PushTable::query()
			->setSelect(['NOT_PROCESSED'])
			->addFilter('=ENTITY_TYPE', $entityType)
			->addFilter('=ENTITY_ID', $entityId)
			->exec()->fetch();

		return $row['NOT_PROCESSED'] ?? null;
	}

	/**
	 * @param Push $push
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws Exception
	 * @throws Queue\Interfaces\Exception
	 */
	private function syncSection(Push $push): void
	{
		/** @var Sync\Connection\SectionConnection $sectionLink
		 */
		$sectionLink = (new SectionConnection())->getById($push->getEntityId());

		if ($sectionLink)
		{
			try
			{
				if (!$this->lockConnection($sectionLink->getConnection(), self::LOCK_CONNECTION_TIME))
				{
					$this->pushSectionToQueue($sectionLink);
					return;
				}
				$syncSectionMap = new SyncSectionMap();
				$syncSection = (new SyncSection())
					->setSection($sectionLink->getSection())
					->setSectionConnection($sectionLink)
					->setVendorName($sectionLink->getConnection()->getVendor()->getCode());

				$syncSectionMap->add(
					$syncSection,
					$syncSection->getSectionConnection()->getVendorSectionId()
				);

				$factory = FactoryBuilder::create(
					$sectionLink->getConnection()->getVendor()->getCode(),
					$sectionLink->getConnection(),
					new Sync\Util\Context()
				);

				$manager = new VendorDataExchangeManager($factory, $syncSectionMap);

				$manager
					->importEvents()
					->updateConnection($sectionLink->getConnection());

				$this->markPushSuccess($push, true);
			}
			catch(BaseException $e)
			{
			    $this->markPushSuccess($push, false);
			}
			finally
			{
				$this->unLockConnection($sectionLink->getConnection());
			}
		}
		else
		{
			$this->deletePush($push);
		}
	}

	/**
	 * @param Push $push
	 *
	 * @return void
	 */
	private function syncConnection(Push $push): void
	{
		try
		{
			/** @var Sync\Connection\Connection $connection */
			$connection = (new Connection())->getById($push->getEntityId());
			if (!$connection || $connection->isDeleted())
			{
				return;
			}
		}
		catch (ArgumentException $e)
		{
			return;
		}

		try
		{

			if (!$this->lockConnection($connection, self::LOCK_CONNECTION_TIME))
			{
				$this->pushConnectionToQueue($connection);
				return;
			}

			$factory = FactoryBuilder::create(
				$connection->getVendor()->getCode(),
				$connection,
				new Sync\Util\Context()
			);
			if ($factory)
			{
				$manager = new VendorDataExchangeManager(
					$factory,
					(new SyncSectionFactory())->getSyncSectionMapByFactory($factory)
				);
				$manager
					->importSections()
					->updateConnection($factory->getConnection())
				;
			}
		}
		catch(\Exception $e)
		{
		}
		finally
		{
			$this->unLockConnection($connection);
		}

	}

	/**
	 * @param Push $push
	 * @param bool $success
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function markPushSuccess(Push $push, bool $success): void
	{
		if (!$success)
		{
			$push->setProcessStatus(Dictionary::PUSH_STATUS_PROCESS['unblocked']);
			$this->updatePush($push);
		}
		elseif(!$push->getFirstPushDate())
		{
			$push->setFirstPushDate(new Date());
			$this->updatePush($push);
		}
	}

	/**
	 * @param Push|null $push
	 *
	 * @return bool
	 */
	public function setBlockPush(?Push $push): bool
	{
		if (!$push || $push->isProcessed())
		{
			return false;
		}

		try
		{
			return $this->blockPush($push);
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * simple method without check anything
	 *
	 * @param Push $push
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	private function blockPush(Push $push): bool
	{
		return PushTable::update(
			[
				'ENTITY_TYPE' => $push->getEntityType(),
				'ENTITY_ID' => $push->getEntityId(),
			],
			[
				'NOT_PROCESSED' => Dictionary::PUSH_STATUS_PROCESS['block']
			]
		)->isSuccess();
	}

	/**
	 * @param Push|null $push
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public function setUnblockPush(?Push $push): void
	{
		if (!$push)
		{
			return;
		}

		PushTable::update(
			[
				'ENTITY_TYPE' => $push->getEntityType(),
				'ENTITY_ID' => $push->getEntityId(),
			],
			[
				'NOT_PROCESSED' => Dictionary::PUSH_STATUS_PROCESS['unblocked']
			]
		);

		if ($push->isUnprocessed())
		{
			$this->handlePush($push->getChannelId(), $push->getResourceId());
		}
	}

	/**
	 * @param Push|null $push
	 * @throws Exception
	 */
	public function setUnprocessedPush(?Push $push): void
	{
		if (!$push || $push->isUnprocessed())
		{
			return;
		}

		PushTable::update(
			[
				'ENTITY_TYPE' => $push->getEntityType(),
				'ENTITY_ID' => $push->getEntityId(),
			],
			[
				'NOT_PROCESSED' => Dictionary::PUSH_STATUS_PROCESS['unprocessed']
			]
		);
	}

	/**
	 * @param Sync\Connection\Connection $connection
	 *
	 * @param int $time
	 *
	 * @return bool
	 */
	public function lockConnection(Sync\Connection\Connection $connection, int $time = 30): bool
	{
		return $this->getMutex($connection)->lock($time);
	}

	/**
	 * @param Sync\Connection\Connection $connection
	 *
	 * @return bool
	 */
	public function unLockConnection(Sync\Connection\Connection $connection): bool
	{
		return $this->getMutex($connection)->unlock();
	}

	/**
	 * @param Sync\Connection\Connection $connection
	 *
	 * @return Mutex
	 */
	private function getMutex(Sync\Connection\Connection $connection): Mutex
	{
		$key = 'lockPushForConnection_' . $connection->getId();
		return new Mutex($key);
	}

	/**
	 * @param Sync\Connection\SectionConnection $sectionLink
	 *
	 * @return void
	 *
	 * @throws Queue\Exception\InvalidDestinationException
	 * @throws Queue\Exception\InvalidMessageException
	 * @throws Queue\Interfaces\Exception
	 */
	private function pushSectionToQueue(Sync\Connection\SectionConnection $sectionLink): void
	{
		$message = (new Queue\Message\Message())
			->setBody([
				Sync\Push\Dictionary::PUSH_TYPE['sectionConnection'] => $sectionLink->getId(),
			])
			->setRoutingKey(self::QUEUE_ROUTE_KEY_SECTION);
		Queue\Producer\Factory::getProduser()->send($message);
	}

	/**
	 * @param Sync\Connection\Connection $connection
	 *
	 * @return void
	 *
	 * @throws InvalidDestinationException
	 * @throws InvalidMessageException
	 * @throws Queue\Interfaces\Exception
	 */
	private function pushConnectionToQueue(Sync\Connection\Connection $connection): void
	{
		$message = (new Queue\Message\Message())
			->setBody([
				Sync\Push\Dictionary::PUSH_TYPE['connection'] => $connection->getId(),
			])
			->setRoutingKey(self::QUEUE_ROUTE_KEY_CONNECTION);
		Queue\Producer\Factory::getProduser()->send($message);
	}
}
