<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Exceptions\ApiException;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
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
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
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
	 * @param SectionContext|null $context
	 *
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws ArgumentNullException
	 * @throws BaseException
	 * @throws ConflictException
	 * @throws NotFoundException
	 * @throws AuthException
	 * @throws RemoteAccountException
	 * @throws LoaderException
	 */
	public function create(Section $section, SectionContext $context): Result
	{
		$result = new Result();

		$dto = new SectionDto([
			'name' => $this->getSectionName($section),
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
	 * @param Section $section
	 *
	 * @return string
	 */
	private function getSectionName(Section $section): string
	{
		if ($section->getExternalType() === Section::LOCAL_EXTERNAL_TYPE)
		{
			IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/classes/general/calendar.php');

			return Loc::getMessage('EC_CALENDAR_BITRIX24_NAME')
				. ' '
				. ($section->getName() ?: $section->getId());
		}

		return $section->getName() ?: $section->getId();
	}

	/**
	 * @throws NotFoundException
	 */
	public function update(Section $section, SectionContext $context): Result
	{
		$result = new Result();
		$sectionLink = $context->getSectionConnection();

		$dto = new SectionDto([
			'name' => $this->getSectionName($sectionLink->getSection()),
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

	/**
	 * @return string
	 */
	public static function updateSectionsAgent(): string
	{
		$agentName = __METHOD__ . '();';

		try
		{
			if (!Loader::includeModule('dav') || !Loader::includeModule('calendar'))
			{
				throw new SystemException('Module not found');
			}
			$connectionsEO = DavConnectionTable::query()
				->setSelect(['*'])
				->addFilter('=ACCOUNT_TYPE', [Helper::ACCOUNT_TYPE])
				->addFilter('=IS_DELETED', 'N')
				->addOrder('SYNCHRONIZED')
				->setLimit(self::IMPORT_SECTIONS_LIMIT)
				->exec();

			while ($connectionEO = $connectionsEO->fetchObject())
			{
				try
				{
					$connection = (new BuilderConnectionFromDM($connectionEO))->build();
					$manager    = new IncomingManager($connection);
					$result = $manager->importSections();
					if ($result->isSuccess())
					{
						DavConnectionTable::update($connectionEO->getId(), [
							'SYNCHRONIZED' => new DateTime(),
							'LAST_RESULT'  => '[200] OK',
						]);
					}
					else
					{
						DavConnectionTable::update($connectionEO->getId(), [
							'SYNCHRONIZED' => new DateTime(),
							'LAST_RESULT'  => '[400] Error.',
						]);
					}
				}
				catch (Exception $e)
				{
					DavConnectionTable::update($connectionEO->getId(), [
						'SYNCHRONIZED' => new DateTime(),
						'LAST_RESULT'  => '[400] Error.',
					]);
				}

			}
		} catch (BaseException|Throwable $e) {
			// TODO: write into log
		}

		return $agentName;
	}

	public function getAvailableExternalType(): array
	{
		return [Helper::ACCOUNT_TYPE];
	}
}
