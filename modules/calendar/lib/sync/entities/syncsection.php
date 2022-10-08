<?php
namespace Bitrix\Calendar\Sync\Entities;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\SectionConnection;

class SyncSection
{
	protected Section $section;
	protected ?SectionConnection $sectionConnection = null;

	protected string $action = '';
	protected string $vendorName;

	/**
	 * @return SectionConnection
	 */
	public function getSectionConnection(): ?SectionConnection
	{
		return $this->sectionConnection;
	}

	/**
	 * @param SectionConnection|null $sectionConnection
	 *
	 * @return SyncSection
	 */
	public function setSectionConnection(?SectionConnection $sectionConnection): SyncSection
	{
		$this->sectionConnection = $sectionConnection;

		return $this;
	}

	/**
	 * @return Section
	 */
	public function getSection(): Section
	{
		return $this->section;
	}

	/**
	 * @param Section $section
	 * @return SyncSection
	 */
	public function setSection(Section $section): SyncSection
	{
		$this->section = $section;

		return $this;
	}

	/**
	 * @param string $action
	 * @return SyncSection
	 */
	public function setAction(string $action): SyncSection
	{
		$this->action = $action;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction(): string
	{
		return $this->action;
	}

	/**
	 * @param string $vendorName
	 * @return SyncSection
	 */
	public function setVendorName(string $vendorName): SyncSection
	{
		$this->vendorName = $vendorName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getVendorName(): string
	{
		return $this->vendorName;
	}

}
