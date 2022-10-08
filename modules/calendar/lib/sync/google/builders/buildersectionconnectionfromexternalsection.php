<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Dictionary;

class BuilderSectionConnectionFromExternalSection implements Builder
{
	/**
	 * @var array
	 */
	private array $externalSection;
	/**
	 * @var Section
	 */
	private Section $section;
	/**
	 * @var Connection
	 */
	private Connection $connection;

	public function __construct(array $externalSection, Section  $section, Connection $connection)
	{
		$this->externalSection = $externalSection;
		$this->section = $section;
		$this->connection = $connection;
	}

	public function build()
	{
		return (new SectionConnection())
			->setVendorSectionId($this->externalSection['id'])
			->setConnection($this->connection)
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			->setVersionId($this->externalSection['etag'])
			->setSection($this->section)
			->setOwner($this->section->getOwner())
		;
	}
}
