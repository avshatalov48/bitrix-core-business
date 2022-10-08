<?php

namespace Bitrix\Location\Entity\Location;

use Bitrix\Location\Entity\Generic\IField;

/**
 * Class Field
 * @package Bitrix\Location\Entity\Location;
 * @internal
 */
final class Field implements IField
{
	/** @var int  */
	private $type;
	/** @var string  */
	private $value;

	/**
	 * Field constructor.
	 * @param int $type Field type. See \Bitrix\Location\Entity\Location\Type
	 * @param string $value
	 */
	public function __construct(int $type, string $value = '')
	{
		$this->type = $type;
		$this->value = $value;
	}

	/**
	 * @param string $value
	 */
	public function setValue(string $value): void
	{
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
	 * @return int.
	 * @see Dynamic
	 */
	public function getType(): int
	{
		return $this->type;
	}
}
