<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Core\User\Creator;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Google\Factory;

class BuilderSyncSectionFromExternalData implements Builder
{
	private array $item;
	private Connection $connection;

	public function __construct(array $item, Connection $connection)
	{
		$this->item = $item;
		$this->connection = $connection;
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 * @throws Core\Base\BaseException
	 */
	public function build()
	{
		if ($this->connection->getOwner() === null)
		{
			throw new Core\Base\BaseException('The connection must have an owner');
		}

		$section = (new Section())
			->setName($this->item['summary'])
			->setColor($this->item['backgroundColor'])
			->setOwner($this->connection->getOwner())
			->setCreator($this->connection->getOwner())
			->setExternalType(Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE[$this->item['accessRole']])
			->setType(Core\Event\Tools\Dictionary::CALENDAR_TYPE[Core\Role\User::TYPE])
			->setIsActive(true)
			->setDescription($this->item['description'] ?? null)
		;

		$sectionConnection = (new SectionConnection())
			->setVendorSectionId($this->item['id'])
			->setActive(true)
			->setLastSyncDate(null)
			->setPrimary($this->item['primary'] ?? false)
			->setSection($section)
			->setOwner($this->connection->getOwner())
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			->setConnection($this->connection)
			->setVersionId($this->item['etag'])
		;

		$syncSection = (new SyncSection())
			->setSection($section)
			->setSectionConnection($sectionConnection)
			->setVendorName(Factory::SERVICE_NAME)
		;

		if (!empty($this->item['deleted']))
		{
			$syncSection->setAction(Dictionary::SYNC_SECTION_ACTION['delete']);
		}
		else
		{
			$syncSection->setAction(Dictionary::SYNC_SECTION_ACTION['success']);
		}

		return $syncSection;
	}
}
