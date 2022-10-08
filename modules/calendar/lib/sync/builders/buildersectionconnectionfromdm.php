<?php

namespace Bitrix\Calendar\Sync\Builders;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Util;
use Bitrix\Main\ObjectException;
use DateTime;

class BuilderSectionConnectionFromDM implements Builder
{

	private EO_SectionConnection $link;

	/**
	 * @param EO_SectionConnection $link
	 */
	public function __construct(EO_SectionConnection $link)
	{
		$this->link = $link;
	}

	/**
	 * @throws ObjectException
	 */
	public function build()
	{
		return (new SectionConnection())
			->setActive($this->getIsActive())
			->setId($this->getId())
			->setLastSyncDate($this->getLastSyncDate())
			->setLastSyncStatus($this->getLastSyncStatus())
			->setPageToken($this->getPageToken())
			->setSection($this->getSection())
			->setSyncToken($this->getSyncToken())
			->setVersionId($this->getVersionId())
			->setVendorSectionId($this->getVendorSectionId())
			->setConnection($this->getConnection())
		;
	}

	/**
	 * @return bool
	 */
	private function getIsActive(): bool
	{
		return $this->link->getActive();
	}

	/**
	 * @return int
	 */
	private function getId(): int
	{
		return $this->link->getId();
	}

	/**
	 * @return Date
	 * @throws ObjectException
	 */
	private function getLastSyncDate(): Date
	{
		return new Date(Util::getDateObject(
			$this->link->getLastSyncDate(),
			false,
			(new DateTime)->getTimezone()->getName()
		));
	}

	/**
	 * @return string|null
	 */
	private function getLastSyncStatus(): ?string
	{
		return $this->link->getLastSyncStatus();
	}

	/**
	 * @return string|null
	 */
	private function getPageToken(): ?string
	{
		return $this->link->getPageToken();
	}

	/**
	 * @return Section|null
	 */
	private function getSection(): ?Section
	{
		if ($section = $this->link->getSection())
		{
			return (new SectionBuilderFromDataManager($section))->build();
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	private function getSyncToken(): ?string
	{
		return $this->link->getSyncToken();
	}

	/**
	 * @return string|null
	 */
	private function getVersionId(): ?string
	{
		return $this->link->getVersionId();
	}

	/**
	 * @return string|null
	 */
	private function getVendorSectionId(): ?string
	{
		return $this->link->getVendorSectionId();
	}

	/**
	 * @return Connection
	 */
	private function getConnection(): ?Connection
	{
		if ($connection = $this->link->getConnection())
		{
			return (new BuilderConnectionFromDM($connection))->build();
		}

		return null;
	}

}
