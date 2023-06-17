<?php

namespace Bitrix\Calendar\Access\Model;

use Bitrix\Calendar\Access\AccessibleType;
use Bitrix\Main\Access\AccessibleItem;

class TypeModel implements AccessibleType
{
	private static array $cache = [];

	private int $id = 0;
	private string $xmlId = '';

	public static function createFromId(int $itemId = 0): AccessibleItem
	{
		return new self();
	}

	public static function createFromXmlId(string $xmlId): AccessibleItem
	{
		if (!isset(static::$cache[$xmlId]))
		{
			$model = new self();
			$model->setXmlId($xmlId);
			static::$cache[$xmlId] = $model;
		}

		return static::$cache[$xmlId];
	}

	public static function createNew(): self
	{
		return new self();
	}

	public static function createFromSectionModel(SectionModel $sectionModel): self
	{
		if ($sectionModel->getType() !== '')
		{
			$model = self::createFromXmlId($sectionModel->getType());
		}
		else
		{
			$model = self::createNew();
		}

		return $model;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setXmlId(string $xmlId): self
	{
		$this->xmlId = $xmlId;
		return $this;
	}

	public function getXmlId(): string
	{
		return $this->xmlId;
	}
}