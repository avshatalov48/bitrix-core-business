<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

/**
 * Class OrderPropertyGroup
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class OrderPropertyGroup
{
	/** @var string */
	private $code;

	/** @var string */
	private $name;

	/**
	 * OrderPropertyGroup constructor.
	 * @param string $code
	 * @param string $name
	 */
	public function __construct(string $code, string $name)
	{
		$this->code = $code;
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}
}
