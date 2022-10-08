<?php

namespace Bitrix\Location\Entity\Format;

use Bitrix\Location\Entity\Generic\IField;

/**
 * Class Field
 * @package Bitrix\Location\Entity\Format
 * @internal
 */
final class Field implements IField
{
	/** @var int  */
	private $type;
	/** @var int  */
	private $sort = 100;
	/** @var string  */
	private $name = '';
	/** @var string  */
	private $description = '';

	/**
	 * Field constructor.
	 * @param int $type Field type. See \Bitrix\Location\Entity\Address\FieldType
	 */
	public function __construct(int $type)
	{
		$this->type = $type;
	}

	/**
	 * @return int
	 * @see \Bitrix\Location\Entity\Address\FieldType
	 */
	public function getType(): int
	{
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getSort(): int
	{
		return $this->sort;
	}

	/**
	 * @param int $sort
	 * @return $this
	 */
	public function setSort(int $sort): self
	{
		$this->sort = $sort;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return $this
	 */
	public function setDescription(string $description): self
	{
		$this->description = $description;
		return $this;
	}
}
