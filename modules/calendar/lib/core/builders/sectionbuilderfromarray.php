<?php

namespace Bitrix\Calendar\Core\Builders;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Role\Helper;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Calendar\Core\Section\Section;

class SectionBuilderFromArray implements Builder
{
	/** @var array $fields */
	private $fields;

	/**
	 * @param array $fields
	 */
	public function __construct(array $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * @return Section
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function build(): Section
	{
		return (new Section())
			->setId($this->getId())
			->setName($this->getName())
			->setColor($this->getColor())
			->setDescription($this->getDescription())
			->setGoogleId($this->getGoogleId())
			->setSyncToken($this->getSyncToken())
			->setCalDavConnectionId($this->getCalDavConnectionId())
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
		return (int)($this->fields['ID'] ?? null);
	}

	/**
	 * @return string|null
	 */
	private function getName(): ?string
	{
		return $this->fields['NAME'] ?? null;
	}

	/**
	 * @return string
	 */
	private function getColor(): ?string
	{
		return $this->fields['COLOR'] ?? null;
	}

	/**
	 * @return string|null
	 */
	private function getDescription(): ?string
	{
		return $this->fields['DESCRIPTION'] ?? null;
	}

	/**
	 * @return string|null
	 */
	private function getGoogleId(): ?string
	{
		return $this->fields['GAPI_CALENDAR_ID'] ?? null;
	}

	/**
	 * @return string|null
	 */
	private function getSyncToken(): ?string
	{
		return $this->fields['CAL_DAV_MOD'] ?? null;
	}

	/**
	 * @return int|null
	 */
	private function getCalDavConnectionId(): ?int
	{
		return (int)$this->fields['CAL_DAV_CON'] ?: null;
	}

	/**
	 * @return string|null
	 */
	private function getExternalType(): ?string
	{
		return $this->fields['EXTERNAL_TYPE'] ?? null;
	}

	/**
	 * @return string
	 */
	private function getType(): ?string
	{
		return $this->fields['CAL_TYPE'] ?? null;
	}

	/**
	 * @return bool
	 */
	private function getIsActive(): bool
	{
		return ($this->fields['ACTIVE'] ?? null) === 'Y';
	}

	/**
	 * @return string
	 */
	private function getXmlId(): ?string
	{
		return $this->fields['XML_ID'] ?? null;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getOwner(): ?Role
	{
		try
		{
			return Helper::getUserRole($this->fields['OWNER_ID'] ?? 0);
		}
		catch (BaseException $e)
		{
			return null;
		}
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getCreator(): ?Role
	{
		try
		{
			return Helper::getUserRole($this->fields['CREATED_BY'] ?? 0);
		}
		catch (BaseException $e)
		{
			return null;
		}
	}
}
