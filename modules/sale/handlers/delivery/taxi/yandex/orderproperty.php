<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

/**
 * Class OrderProperty
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class OrderProperty
{
	/** @var string */
	private $code;

	/** @var string */
	private $type;

	/** @var string */
	private $name;

	/** @var bool */
	private $isRequired = false;

	/** @var bool */
	private $isMultiple = false;

	/** @var array */
	private $settings = [];

	/**
	 * OrderProperty constructor.
	 * @param string $code
	 * @param string $type
	 * @param string $name
	 */
	public function __construct(string $code, string $type, string $name)
	{
		$this->code = $code;
		$this->type = $type;
		$this->name = $name;
	}

	/**
	 * @param bool $isRequired
	 * @return OrderProperty
	 */
	public function setIsRequired(bool $isRequired): OrderProperty
	{
		$this->isRequired = $isRequired;

		return $this;
	}

	/**
	 * @param bool $isMultiple
	 * @return OrderProperty
	 */
	public function setIsMultiple(bool $isMultiple): OrderProperty
	{
		$this->isMultiple = $isMultiple;

		return $this;
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
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function isRequired(): bool
	{
		return $this->isRequired;
	}

	/**
	 * @return bool
	 */
	public function isMultiple(): bool
	{
		return $this->isMultiple;
	}

	/**
	 * @return array
	 */
	public function getSettings(): array
	{
		return $this->settings;
	}

	/**
	 * @param array $settings
	 * @return OrderProperty
	 */
	public function setSettings(array $settings): OrderProperty
	{
		$this->settings = $settings;

		return $this;
	}
}
