<?php

namespace Bitrix\Calendar\Core\Builders;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Internals\EO_Section;

class SectionBuilderFromDataManager implements Builder
{
	/**
	 * @var EO_Section
	 */
	private $sectionDM;

	/**
	 * @param EO_Section $sectionDM
	 */
	public function __construct(EO_Section $sectionDM)
	{
		$this->sectionDM = $sectionDM;
	}

	/**
	 * @return Section
	 */
	public function build(): Section
	{
		return (new Section())
			->setId($this->getId())
			->setName($this->getName())
			->setColor($this->getColor())
			->setGoogleId($this->getGoogleId())
			->setSyncToken($this->getSyncToken())
			->setPageToken($this->getPageToken())
			->setCalDavConnectionId($this->getCalDavConnectionId())
			->setDescription($this->getDescription())
			->setExternalType($this->getExternalType())
			->setType($this->getType())
			->setIsActive($this->getIsActive())
			->setXmlId($this->getXmlId())
			->setOwner($this->getOwner())
			->setCreator($this->getCreator())
		;
	}

	/**
	 * @return int
	 */
	private function getId(): int
	{
		return $this->sectionDM->getId();
	}

	/**
	 * @return string
	 */
	private function getGoogleId(): ?string
	{
		return $this->sectionDM->getGapiCalendarId();
	}

	/**
	 * @return string|null
	 */
	private function getSyncToken(): ?string
	{
		return $this->sectionDM->getSyncToken();
	}

	/**
	 * @return string|null
	 */
	private function getPageToken(): ?string
	{
		return $this->sectionDM->getPageToken();
	}

	/**
	 * @return int|null
	 */
	private function getCalDavConnectionId(): ?int
	{
		return (int)$this->sectionDM->getCalDavCon() ?: null;
	}

	/**
	 * @return string|null
	 */
	private function getDescription(): ?string
	{
		return $this->sectionDM->getDescription();
	}

	/**
	 * @return string|null
	 */
	private function getName(): ?string
	{
		return $this->sectionDM->getName();
	}

	/**
	 * @return string
	 */
	private function getColor(): string
	{
		return $this->sectionDM->getColor();
	}

	/**
	 * @return string|null
	 */
	private function getExternalType(): string
	{
		return $this->sectionDM->getExternalType();
	}

	/**
	 * @return bool
	 */
	private function getIsActive(): bool
	{
		return $this->sectionDM->getActive();
	}

	/**
	 * @return string
	 */
	private function getType(): string
	{
		return $this->sectionDM->getCalType();
	}

	/**
	 * @return string
	 */
	private function getXmlId(): string
	{
		return $this->sectionDM->getXmlId();
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getOwner(): ?Role
	{
		if ($id = $this->sectionDM->getOwnerId())
		{
			try
			{
				return Helper::getUserRole($id);
			}
			catch (BaseException $e)
			{
				return null;
			}
		}

		return null;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getCreator(): ?Role
	{
		if ($id = ($this->sectionDM->getCreatedBy() ??  $this->sectionDM->getOwnerId()))
		{
			try
			{
				return Helper::getUserRole($id);
			}
			catch (BaseException $e)
			{
				return null;
			}
		}

		return null;
	}
}
