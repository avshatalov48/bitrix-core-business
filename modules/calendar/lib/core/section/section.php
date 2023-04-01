<?php

namespace Bitrix\Calendar\Core\Section;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Main\Text\Emoji;

class Section implements EntityInterface
{
	public const LOCAL_EXTERNAL_TYPE = 'local';

	/**
	 * @var int|null
	 */
	protected ?int $id = null;
	/**
	 * @var string|null
	 */
	protected ?string $googleId = null;
	/**
	 * @var string|null
	 */
	protected ?string $syncToken = null;
	/**
	 * @var string|null
	 */
	protected ?string $pageToken = null;
	/**
	 * @var int|null
	 */
	protected ?int $calDavConnectionId = null;
	/**
	 * @var Role|null
	 */
	protected ?Role $creator = null;
	/**
	 * @var Role|null
	 */
	protected ?Role $owner = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $dateCreate = null;
	/**
	 * @var Date|null
	 */
	protected ?Date $dateModified = null;
	/**
	 * @var bool
	 */
	protected bool $isActive = false;
	/**
	 * @var string|null
	 */
	protected ?string $description = null;
	/**
	 * @var string|null
	 */
	protected ?string $color = null;
	/**
	 * @var string|null
	 */
	protected ?string $textColor = null;
	/**
	 * @var string|null
	 */
	protected ?string $type = null;
	/**
	 * @var int|null
	 */
	protected ?int $sort = null;
	/**
	 * @var string|null
	 */
	protected ?string $externalType = null;
	/**
	 * @var string|null
	 */
	protected ?string $name = null;
	/**
	 * @var SectionSyncDataCollection|null
	 */
	protected ?SectionSyncDataCollection $syncDataCollection = null;
	protected ?string $xmlId = null;
	protected ?string $externalId = null;
	protected ?string $export = null;
	protected ?string $parentId = null;
	protected ?string $davExchangeCal = null;
	protected ?string $davExchangeMod = null;
	protected ?string $calDavCal = null;
	protected ?string $calDavMod = null;

	/**
	 * @param int $id
	 * @return $this
	 */
	public function setId(int $id): Section
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @param string|null $googleId
	 * @return $this
	 */
	public function setGoogleId(?string $googleId): Section
	{
		$this->googleId = $googleId;

		return $this;
	}

	/**
	 * @param string|null $token
	 * @return $this
	 */
	public function setSyncToken(?string $token): Section
	{
		$this->syncToken = $token;

		return $this;
	}

	/**
	 * @param string|null $token
	 * @return $this
	 */
	public function setPageToken(?string $token): Section
	{
		$this->pageToken = $token;

		return $this;
	}

	/**
	 * @param int|null $calDavConnectionId
	 * @return $this
	 */
	public function setCalDavConnectionId(?int $calDavConnectionId): Section
	{
		$this->calDavConnectionId = $calDavConnectionId;

		return $this;
	}

	/**
	 * @param Role|null $creator
	 * @return Section
	 */
	public function setCreator(?Role $creator): Section
	{
		$this->creator = $creator;

		return $this;
	}

	/**
	 * @param Role|null $owner
	 * @return Section
	 */
	public function setOwner(?Role $owner): Section
	{
		$this->owner = $owner;

		return $this;
	}

	/**
	 * @param Date|null $dateCreate
	 * @return Section
	 */
	public function setDateCreate(?Date $dateCreate): Section
	{
		$this->dateCreate = $dateCreate;

		return $this;
	}

	/**
	 * @param Date|null $dateModified
	 * @return Section
	 */
	public function setDateModified(?Date $dateModified): Section
	{
		$this->dateModified = $dateModified;

		return $this;
	}

	/**
	 * @param string|null $name
	 * @return Section
	 */
	public function setName(?string $name): Section
	{
		$this->name = $name ? Emoji::decode($name) : $name;

		return $this;
	}

	/**
	 * @param bool $isActive
	 * @return Section
	 */
	public function setIsActive(bool $isActive): Section
	{
		$this->isActive = $isActive;

		return $this;
	}

	/**
	 * @param string|null $description
	 * @return Section
	 */
	public function setDescription(?string $description): Section
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @param string|null $color
	 * @return Section
	 */
	public function setColor(?string $color): Section
	{
		$this->color = $color;

		return $this;
	}

	/**
	 * @param string|null $type
	 * @return Section
	 */
	public function setType(?string $type): Section
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @param int|null $sort
	 * @return Section
	 */
	public function setSort(?int $sort): Section
	{
		$this->sort = $sort;

		return $this;
	}

	/**
	 * @param string|null $externalType
	 * @return Section
	 */
	public function setExternalType(?string $externalType): Section
	{
		$this->externalType = $externalType;

		return $this;
	}

	/**
	 * @param SectionSyncDataCollection $syncDataCollection
	 * @return Section
	 */
	public function setSyncDataCollection(SectionSyncDataCollection $syncDataCollection): Section
	{
		$this->syncDataCollection = $syncDataCollection;

		return $this;
	}

	/**
	 * @param string|null $xmlId
	 * @return Section
	 */
	public function setXmlId(?string $xmlId): Section
	{
		$this->xmlId = $xmlId;

		return $this;
	}

	/**
	 * @param string|null $externalId
	 * @return Section
	 */
	public function setExternalId(?string $externalId): Section
	{
		$this->externalId = $externalId;
		return $this;
	}

	/**
	 * @param string|null $export
	 * @return Section
	 */
	public function setExport(?string $export): Section
	{
		$this->export = $export;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return string|null
	 */
	public function getGoogleId(): ?string
	{
		return $this->googleId;
	}

	/**
	 * @return string|null
	 */
	public function getSyncToken(): ?string
	{
		return $this->syncToken;
	}

	/**
	 * @return string|null
	 */
	public function getPageToken(): ?string
	{
		return $this->pageToken;
	}

	/**
	 * @return int|null
	 */
	public function getCalDavConnectionId(): ?int
	{
		return $this->calDavConnectionId;
	}

	/**
	 * @return Role|null
	 */
	public function getCreator(): ?Role
	{
		return $this->creator;
	}

	/**
	 * @return Role|null
	 */
	public function getOwner(): ?Role
	{
		return $this->owner;
	}

	/**
	 * @return Date|null
	 */
	public function getDateCreate(): ?Date
	{
		return $this->dateCreate;
	}

	/**
	 * @return Date|null
	 */
	public function getDateModified(): ?Date
	{
		return $this->dateModified;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->isActive;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @return string|null
	 */
	public function getColor(): ?string
	{
		return $this->color;
	}

	/**
	 * @return string|null
	 */
	public function getTextColor(): ?string
	{
		return $this->textColor;
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * @return int|null
	 */
	public function getSort(): ?int
	{
		return $this->sort;
	}

	/**
	 * @return string|null
	 */
	public function getExternalType(): ?string
	{
		return $this->externalType;
	}

	public function getPathByServiceName(string $serviceName): string
	{
		return '';
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return SectionSyncDataCollection
	 */
	public function getSyncDataCollection(): SectionSyncDataCollection
	{
		return $this->syncDataCollection;
	}

	/**
	 * @return string|null
	 */
	public function getXmlId(): ?string
	{
		return $this->xmlId;
	}

	/**
	 * @return string|null
	 */
	public function getExternalId(): ?string
	{
		return $this->externalId;
	}

	/**
	 * @return string|null
	 */
	public function getExport(): ?string
	{
		return $this->export;
	}

	/**
	 * @return string|null
	 */
	public function getParentId(): ?string
	{
		return $this->parentId;
	}

	public function getDavExchangeCal(): ?string
	{
		return $this->davExchangeCal;
	}

	public function getDavExchangeMod(): ?string
	{
		return $this->davExchangeMod;
	}

	public function getCalDavCal(): ?string
	{
		return $this->calDavCal;
	}

	public function getCalDavMod(): ?string
	{
		return $this->calDavMod;
	}

	public function isExchange(): bool
	{
		return false;
	}

	public function isLocal(): bool
	{
		return $this->externalType === Section::LOCAL_EXTERNAL_TYPE;
	}

	/**
	 * @return bool
	 */
	public function isExternal(): bool
	{
		return !$this->isLocal();
	}

	/**
	 * @return bool
	 */
	public function isNew(): bool
	{
		return $this->id === null;
	}
}