<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Integration\Dav\ConnectionProvider;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Sync\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\GoneException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Exceptions\RemoteAccountException;
use Bitrix\Calendar\Sync\Internals\HasContextTrait;
use Bitrix\Calendar\Sync\Managers\IncomingManager;
use Bitrix\Calendar\Sync\Managers\SectionManagerInterface;
use Bitrix\Calendar\Sync\Office365\Converter\ColorConverter;
use Bitrix\Calendar\Sync\Office365\Dto\SectionDto;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Sync\Util\SectionContext;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\AbstractOffice365Synchronizer;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Office365SectionSynchronizer;
use Bitrix\Calendar\Synchronization\Public\Service\SynchronizationFeature;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Exception;
use Throwable;

class SectionManager extends AbstractManager implements SectionManagerInterface
{
	use HasContextTrait;

	private const IMPORT_SECTIONS_LIMIT = 10;

	public function __construct(Office365Context $context)
	{
		$this->context = $context;

		parent::__construct($context->getConnection());
	}

	/**
	 * @param Section $section
	 * @param SectionContext $context
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 * @throws GoneException
	 */
	public function create(Section $section, SectionContext $context): Result
	{
		$result = new Result();

		$dto = new SectionDto([
			'name' => $section->getExternalName(),
			'color' => ColorConverter::toOffice($section->getColor()),
		]);
		$sectionDto = $this->context->getVendorSyncService()->createSection($dto);
		if (!empty($sectionDto->id) && !empty($sectionDto->changeKey))
		{
			$sectionConnection = (new SectionConnection())
				->setSection($section)
				->setConnection($this->connection)
				->setVendorSectionId($sectionDto->id)
				->setVersionId($sectionDto->changeKey)
				->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['success'])
				->setOwner($section->getOwner())
				->setActive(true)
			;
			$syncSection = (new SyncSection())
				->setSection($section)
				->setSectionConnection($sectionConnection)
				->setVendorName($section->getExternalType())
				->setAction(Sync\Dictionary::SYNC_STATUS['success'])
			;
			$result->setData([
				$sectionDto->id => $syncSection,
				'id' => $sectionDto->id,
				'version' => $sectionDto->changeKey,
				'syncSection' => $syncSection,
			]);
		}
		else
		{
			$result->addError(new Error('Error of create section into Office365'));
		}

		return $result;
	}

	/**
	 * @throws NotFoundException
	 */
	public function update(Section $section, SectionContext $context): Result
	{
		$result = new Result();
		$sectionLink = $context->getSectionConnection();

		$dto = new SectionDto([
			'name' => $sectionLink->getSection()->getExternalName(),
			'id' => $context->getSectionConnection()->getVendorSectionId(),
			// 'color' => ColorConverter::toOffice(
			// 	$sectionLink->getSection()->getColor()
			// )
		]);
		try
		{
			if ($sectionLink->isPrimary())
			{
				$dto->name = null;
			}
			$sectionDto = $this->context->getVendorSyncService()->updateSection($dto);
			$result->setData([
				'id' => $sectionDto->id,
				'version' => $sectionDto->changeKey,
				'sectionConnection' => $sectionLink,
			]);
		}
		catch (NotFoundException $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	public function delete(Section $section, SectionContext $context): Result
	{
		$result = new Result();
		$sectionLink = $context->getSectionConnection();

		$dto = new SectionDto([
			'id' => $sectionLink->getVendorSectionId(),
		]);
		try
		{
			$this->context->getVendorSyncService()->deleteSection($dto);
			$result->setData([
				'sectionConnection' => $sectionLink,
			]);
		}
		catch (Exception $e)
		{
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	/**
	 * @param $connection
	 *
	 * @return array
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws AuthException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws LoaderException
	 * @throws NotFoundException
	 * @throws RemoteAccountException
	 * @todo todo maybe use array of object without array of array
	 */
	public function getSections($connection): array
	{
		$result = array();
		$converter = $this->context->getConverter();
		$sections = $this->context->getVendorSyncService()->getSections();
		foreach ($sections as $sectionDto)
		{
			if ($sectionDto->canShare)
			{
				$result[] = [
					'section' => $converter->convertSection($sectionDto),
					'id' => $sectionDto->id,
					'version' => $sectionDto->changeKey,
					'is_primary' => $sectionDto->isDefaultCalendar,
				];
			}
		}

		return $result;
	}

	/**
	 * @param SectionConnection $link
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws Exception
	 *
	 * @todo Unused?
	 */
	public function subscribe(SectionConnection $link): Result
	{
		$makeDateTime = static function (string $date)
		{
			$phpDateTime = new \DateTime($date);
			return DateTime::createFromPhp($phpDateTime);
		};

		$result = new Result();
		/**
		 *
		 *
		 */
		/** @var array $data
			"@odata.context": "https://graph.microsoft.com/v1.0/$metadata#subscriptions/$entity",
			"id": "6417dbea-4b53-42f1-9312-859ee5a4f614",
			"resource": "me/events",
			"applicationId": "ee900ae3-2cc9-4615-94b0-683a9bf45dbd",
			"changeType": "created,updated,deleted",
			"clientState": "special",
			"notificationUrl": "https://work24.savecard.ru/bitrix/tools/calendar/push.php",
			"notificationQueryOptions": null,
			"lifecycleNotificationUrl": null,
			"expirationDateTime": "2022-03-16T18:23:45.9356913Z",
			"creatorId": "e65624ee-8b9b-4041-b314-3c1a125b078a",
			"includeResourceData": null,
			"latestSupportedTlsVersion": "v1_2",
			"encryptionCertificate": null,
			"encryptionCertificateId": null,
			"notificationUrlAppId": null
		 */
		$data = $this->context->getVendorSyncService()->subscribeSection($link);

		if ($data)
		{
			$result->setData([
				'CHANNEL_ID' => $data['channelId'],
				'RESOURCE_ID' => $data['id'],
				'EXPIRES' => $makeDateTime($data['expirationDateTime']),
			]);
		}
		else
		{
			$result->addError(new Error('Error of create subscription.'));
		}

		return $result;
	}

	/**
	 * @param Push $push
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws Exception
	 */
	public function resubscribe(Push $push): Result
	{
		$result = new Result();
		$data = $this->context->getVendorSyncService()->resubscribe($push->getResourceId());

		$result->setData([
			'EXPIRES' => DateTime::createFromPhp(new \DateTime($data['expirationDateTime'])),
		]);
		return $result;
	}

	public static function updateSectionsAgent(): string
	{
		try
		{
			if (!Loader::includeModule('dav') || !Loader::includeModule('calendar'))
			{
				throw new SystemException('Module not found');
			}

			$connections = self::getConnections();

			foreach ($connections as $connection)
			{
				SynchronizationFeature::setUserId($connection->getOwner()->getId());

				if (SynchronizationFeature::isOn())
				{
					self::runNewImport($connection);
				}
				else
				{
					self::runOldImport($connection);
				}
			}


		}
		catch (Throwable)
		{
			// TODO: write into log
		}

		return __METHOD__ . '();';
	}

	/**
	 * @return Connection\Connection[]
	 *
	 * @throws LoaderException
	 */
	private static function getConnections(): array
	{
		$connectionProvider = ServiceLocator::getInstance()->get(ConnectionProvider::class);

		return $connectionProvider->getActiveByProvider(AbstractOffice365Synchronizer::VENDOR_CODE);
	}

	/**
	 * @throws ArgumentException
	 * @throws SystemException
	 * @throws ObjectPropertyException
	 */
	private static function runOldImport(Connection\Connection $connection): void
	{
		try
		{
			$manager = new IncomingManager($connection);

			$result = $manager->importSections();

			if ($result->isSuccess())
			{
				DavConnectionTable::update($connection->getId(), [
					'SYNCHRONIZED' => new DateTime(),
					'LAST_RESULT' => '[200] OK',
				]);
			}
			else
			{
				DavConnectionTable::update($connection->getId(), [
					'SYNCHRONIZED' => new DateTime(),
					'LAST_RESULT' => '[400] Error.',
				]);
			}
		}
		catch (Exception)
		{
			DavConnectionTable::update($connection->getId(), [
				'SYNCHRONIZED' => new DateTime(),
				'LAST_RESULT' => '[400] Error.',
			]);
		}
	}

	private static function runNewImport(Connection\Connection $connection): void
	{
		$synchronizer = ServiceLocator::getInstance()->get(Office365SectionSynchronizer::class);

		$logger = ServiceLocator::getInstance()->get(RequestLogger::class);

		$logger
			->setType(AbstractOffice365Synchronizer::VENDOR_CODE)
			->setUserId($connection->getOwner()->getId())
			->setEntityId($connection->getId())
		;

		$logger->debug('Import sections of connection ' . $connection->getName() . ' has been started');

		try
		{
			$synchronizer->importSections($connection->getOwner()->getId(), $connection->getToken());
		}
		catch (\Exception $e)
		{
			$logger->error(
				sprintf(
					'Error in import sections of connection %s: "%s"',
					$connection->getName(),
					$e->getMessage()
				),
				[
					'error' => [
						'message' => $e->getMessage(),
						'code' => $e->getCode(),
						'trace' => $e->getTraceAsString(),
					]
				]
			);

			$connection
				->setLastSyncTime(new Date())
				->setStatus('[400] Error')
			;

			(new \Bitrix\Calendar\Core\Mappers\Connection())->update($connection);
		}

		$logger->debug('Import sections of connection ' . $connection->getName() . ' has been completed');
	}

	public function getAvailableExternalType(): array
	{
		return [Helper::ACCOUNT_TYPE];
	}
}
