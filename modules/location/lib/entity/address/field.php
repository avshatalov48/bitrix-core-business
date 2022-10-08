<?php

namespace Bitrix\Location\Entity\Address;

use Bitrix\Location\Entity\Location\Type;
use Bitrix\Location\Entity\Generic\IField;

/**
 * Class Field
 * @package Bitrix\Location\Entity\Address
 * @internal
 */
final class Field implements IField
{
	/** @var int See \Bitrix\Location\Entity\Address\FieldType */
	private $type;

	/** @var string  */
	private $value = '';

	public function __construct(int $type, string $value = '')
	{
		$this->type = $type;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 * @return $this
	 */
	public function setValue(string $value): self
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @return int Field type.
	 * @see Type
	 */
	public function getType(): int
	{
		return $this->type;
	}
}
