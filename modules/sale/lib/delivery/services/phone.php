<?php

namespace Bitrix\Sale\Delivery\Services;

/**
 * Class Phone
 * @package Bitrix\Sale\Delivery\Services
 * @internal
 */
final class Phone
{
	/** @var string */
	private $type;

	/** @var string */
	private $value;

	/**
	 * Phone constructor.
	 * @param string $type
	 * @param string $value
	 */
	public function __construct(string $type, string $value)
	{
		$this->type = $type;
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}
}
