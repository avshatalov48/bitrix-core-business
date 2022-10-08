<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Internals\PushTable;
use Bitrix\Calendar\Sync\Builders\BuilderPushFromDM;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Factories\FactoryBuilder;
use Bitrix\Calendar\Sync\Factories\SectionConnectionFactory;
use Bitrix\Calendar\Sync\Factories\FactoryInterface;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use CAgent;
use Exception;

class PushWatchingManager
{
	private const RENEW_LIMIT = 5;
	private const FIX_LIMIT = 5;
	private const RENEW_INTERVAL_CHANNEL = 14400;//60*60*4
	private const PAUSE_INTERVAL_CHANNEL = 72000; // 60*60*20
	private const TYPE_LINK = 'SECTION_CONNECTION';
	private const TYPE_CONNECTION = 'CONNECTION';

	private const RESULT_STATUS = [
		'done' => 'done', // nothing left to process
		'next' => 'next', // something left to process
	];
	/** @var Factory */
	private $mapperFactory;

	/**
	 * @throws SystemException
	 * @throws LoaderException
	 */
	public function __construct()
	{
		if (!Loader::includeModule('dav'))
		{
			throw new SystemException('Module dav not found');
		}

		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @return false|string
	 *
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public static function renewWatchChannels()
	{
		if (!Loader::includeModule('dav') || !Loader::includeModule('calendar'))
		{
			return false;
		}

		$agentName = __METHOD__ . '();';
		$manager = new static();

		$status = $manager->doRenewWatchChannels();

		$manager->doFixWatchSectionChannels();
		$manager->doFixWatchConnectionChannels();

		if ($status === self::RESULT_STATUS['done'])
		{
			$nextAgentDate = DateTime::createFromTimestamp(
				time() + self::PAUSE_INTERVAL_CHANNEL)->format(Date::convertFormatToPhp(FORMAT_DATETIME)
			);

			CAgent::removeAgent($agentName, "calendar");
			CAgent::addAgent($agentName, "calendar", "N", self::RENEW_INTERVAL_CHANNEL,"", "Y", $nextAgentDate);

			return false;
		}

		return $agentName;
	}

	/**
	 * @return string
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	private function doRenewWatchChannels(): string
	{
		$pushChannels = PushTable::getList([
			'filter' => [
				'ENTITY_TYPE' => [self::TYPE_LINK, self::TYPE_CONNECTION],
				'<=EXPIRES' => (new DateTime())->add('+1 day'),
			],
			'order' => [
				'EXPIRES' => 'ASC',
			],
			'limit' => self::RENEW_LIMIT,
		])->fetchCollection();

		foreach ($pushChannels as $pushChannelEO)
		{
			$pushChannel = (new BuilderPushFromDM($pushChannelEO))->build();

			if ($pushChannel->getEntityType() === self::TYPE_LINK)
			{
				$this->renewSectionPush($pushChannel);
			}
			elseif ($pushChannel->getEntityType() === self::TYPE_CONNECTION)
			{
				$this->renewConnectionPush($pushChannel);
			}
		}

		if ($pushChannels->count() < self::RENEW_LIMIT)
		{
			return self::RESULT_STATUS['done'];
		}

		return self::RESULT_STATUS['next'];
	}

	/**
	 * @param Push $pushChannel
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function deleteChannel(Push $pushChannel): void
	{
		(new PushManager())->deletePush($pushChannel);
	}

	/**
	 * @param Push $pushChannel
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function savePushChannel(Push $pushChannel): void
	{
		(new PushManager())->updatePush($pushChannel);
	}

	/**
	 * @param Connection $connection
	 *
	 * @return FactoryInterface|null
	 */
	private function getFactoryByConnection(Connection $connection): ?FactoryInterface
	{
		$context = new Context([
			'connection' => $connection,
		]);
		return FactoryBuilder::create($connection->getVendor()->getCode(), $connection, $context);
	}

	/**
	 * @param PushManagerInterface $vendorPushManager
	 * @param Push $pushChannel
	 * @return Result
	 * @throws Exception
	 */
	private function renewPushChannel(PushManagerInterface $vendorPushManager, Push $pushChannel): Result
	{
		$result = $vendorPushManager->renewPush($pushChannel);
		if ($result->isSuccess())
		{
			$this->savePushChannel($pushChannel);
		}
		else
		{
			$result->addError(new Error('Error of renew push channel.'));
		}
		return $result;
	}

	/**
	 * @param PushManagerInterface $vendorPushManager
	 * @param Push $pushChannel
	 * @param SectionConnection $sectionLink
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ObjectException
	 */
	private function recreateSectionPushChannel(
		PushManagerInterface $vendorPushManager,
		Push $pushChannel,
		SectionConnection $sectionLink
	): Result
	{
		$result = new Result();
		try
		{
			$vendorPushManager->deletePush($pushChannel);
			$result = $vendorPushManager->addSectionPush($sectionLink);
			if ($result->isSuccess())
			{
				$data = $result->getData();
				$pushChannel
					->setChannelId($data['CHANNEL_ID'])
					->setResourceId($data['RESOURCE_ID'])
					->setExpireDate(new \Bitrix\Calendar\Core\Base\Date($data['EXPIRES']));
				$this->savePushChannel($pushChannel);
			}
			else
			{
				$result->addError(new Error('Error of create push channel.'));
			}
		}
		catch(ApiException $e)
		{
			$result->addError(new Error('ApiException during creation of push channel.'));
		    if ($e->getMessage() === 'ExtensionError')
			{
				$this->deleteChannel($pushChannel);
			}
			else
			{
				throw $e;
			}
		}
		return $result;
	}

	/**
	 * @param Result $result
	 *
	 * @return bool
	 */
	private function isError405(Result $result): bool
	{
		$errors = $result->getErrors();
		if (empty($errors))
		{
			return false;
		}
		foreach ($errors as $error)
		{
			if ($error->getCode() == 405)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return SectionConnectionFactory
	 */
	private function getLinkFactory(): SectionConnectionFactory
	{
		static $linkFactory = null;
		if ($linkFactory === null)
		{
			$linkFactory = new SectionConnectionFactory();
		}

		return $linkFactory;
	}

	/**
	 * @param Push $pushChannel
	 *
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws BaseException
	 * @throws Exception
	 */
	private function renewSectionPush(Push $pushChannel): void
	{
		$sectionLink = $this->getLinkFactory()->getSectionConnection([
			'filter' => [
				'=ID' => $pushChannel->getEntityId(),
			]
		]);
		if (
			!empty($sectionLink)
			&& $sectionLink->isActive()
			&& ($sectionLink->getConnection() !== null)
			&& !$sectionLink->getConnection()->isDeleted()
		)
		{
			/** @var FactoryInterface $vendorFactory */
			$vendorFactory = $this->getFactoryByConnection($sectionLink->getConnection());
			/** @var PushManagerInterface $vendorPushManager */
			if ($vendorPushManager = $vendorFactory->getPushManager())
			{
				$now = new DateTime();
				if ($pushChannel->getExpireDate()->getDate() > $now)
				{
					$result = $this->renewPushChannel($vendorPushManager, $pushChannel);
					if (!$result->isSuccess() && $this->isError405($result))
					{
						$result = $this->recreateSectionPushChannel($vendorPushManager, $pushChannel, $sectionLink);
						if ($result->isSuccess())
						{
							return;
						}
					}
				}
				else
				{
					$result = $this->recreateSectionPushChannel($vendorPushManager, $pushChannel, $sectionLink);
					if ($result->isSuccess())
					{
						return;
					}
				}
			}
		}

		$this->deleteChannel($pushChannel);
	}

	/**
	 * @param Push $pushChannel
	 *
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	private function renewConnectionPush(Push $pushChannel)
	{
		/** @var Connection $connection */
		$connection = $this->getConnectionMapper()->getById($pushChannel->getEntityId());
		if (!empty($connection) && !$connection->isDeleted())
		{
			/** @var FactoryInterface $vendorFactory */
			$vendorFactory = $this->getFactoryByConnection($connection);
			/** @var PushManagerInterface $vendorPushManager */
			if ($vendorPushManager = $vendorFactory->getPushManager())
			{
				$result = $this->recreateConnectionPushChannel($vendorPushManager, $pushChannel, $connection);
				if ($result->isSuccess())
				{
					return;
				}
			}
		}

		$this->deleteChannel($pushChannel);
	}

	/**
	 * @return \Bitrix\Calendar\Core\Mappers\Connection
	 */
	private function getConnectionMapper(): \Bitrix\Calendar\Core\Mappers\Connection
	{
		return $this->mapperFactory->getConnection();
	}

	/**
	 * @param PushManagerInterface $vendorPushManager
	 * @param Push $pushChannel
	 * @param Connection $connection
	 *
	 * @return Result
	 *
	 * @throws ObjectException
	 */
	private function recreateConnectionPushChannel(
		PushManagerInterface $vendorPushManager,
		Push $pushChannel,
		Connection $connection
	): Result
	{
		$vendorPushManager->deletePush($pushChannel);
		$result = $vendorPushManager->addConnectionPush($connection);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$pushChannel
				->setResourceId($data['RESOURCE_ID'])
				->setExpireDate(new \Bitrix\Calendar\Core\Base\Date($data['EXPIRES']));
			$this->savePushChannel($pushChannel);
		}
		else
		{
			$result->addError(new Error('Error of create push channel.'));
		}
		return $result;
	}

	/**
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws ObjectNotFoundException
	 */
	private function doFixWatchSectionChannels()
	{
		// TODO: move it to D7
		$sql = "SELECT link.ID, link.CONNECTION_ID, link.SECTION_ID FROM b_calendar_section_connection link
		inner join b_dav_connections conn ON link.CONNECTION_ID = conn.ID 
		LEFT JOIN b_calendar_push push ON push.ENTITY_ID = link.ID AND push.ENTITY_TYPE = '". self::TYPE_LINK ."'
		WHERE link.ACTIVE = 'Y' AND conn.IS_DELETED='N' AND link.LAST_SYNC_STATUS='success'
		    AND conn.ACCOUNT_TYPE IN ('office365', 'google_api_oauth')
			AND push.ENTITY_TYPE IS NULL 
		LIMIT " . self::FIX_LIMIT . "
		;";
		global $DB;
		$result = $DB->Query($sql);
		if ($result)
		{
			while ($row = $result->Fetch())
			{
				$manager = $this->getOutgoingManager($row['CONNECTION_ID']);
				/** @var SectionConnection $link */
				$link = $this->mapperFactory->getSectionConnection()->getById($row['ID']);
				try
				{
					$manager->subscribeSection($link);
				}
				catch (Exception $e)
				{
					$link->setLastSyncStatus(Dictionary::SYNC_STATUS['failed']);
					$this->mapperFactory->getSectionConnection()->update($link);
				}
			}
		}
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 */
	private function doFixWatchConnectionChannels()
	{
		// TODO: move it to D7
		$sql = "SELECT conn.ID from b_dav_connections conn
		LEFT JOIN b_calendar_push push ON push.ENTITY_ID = conn.ID AND push.ENTITY_TYPE = '". self::TYPE_CONNECTION ."'
		WHERE conn.IS_DELETED='N' AND conn.LAST_RESULT IN ('success', '[200] OK')
		    AND conn.ACCOUNT_TYPE = 'google_api_oauth'
			AND push.ENTITY_TYPE IS NULL 
		LIMIT " . self::FIX_LIMIT . "
		;";
		global $DB;
		$result = $DB->Query($sql);
		if ($result)
		{
			while ($row = $result->Fetch())
			{
				try
				{
					$manager = $this->getOutgoingManager($row['ID']);
					$manager->subscribeConnection();
				}
				catch (Exception $e)
				{
					DavConnectionTable::update($row['ID'],[
						'LAST_RESULT' => '['. $e->getCode() .'] ERR'
					]);
				}
			}
		}
	}

	/**
	 * @param $connectionId
	 *
	 * @return OutgoingManager
	 *
	 * @throws ArgumentException
	 * @throws ObjectNotFoundException
	 */
	private function getOutgoingManager($connectionId): OutgoingManager
	{
		static $cache = [];
		if (empty($cache[$connectionId]))
		{
			$connection = $this->mapperFactory->getConnection()->getById($connectionId);
			$cache[$connectionId] = new OutgoingManager($connection);
		}

		return $cache[$connectionId];
	}
}
