<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

/**
 * Class ExtraService
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class ExtraService
{
	/** @var string */
	private $code;

	/** @var string */
	private $name;

	/** @var string */
	private $className;

	/** @var string */
	private $initValue;

	/** @var array */
	private $params = [];

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 * @return ExtraService
	 */
	public function setCode(string $code): ExtraService
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return ExtraService
	 */
	public function setName(string $name): ExtraService
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @param string $className
	 * @return ExtraService
	 */
	public function setClassName(string $className): ExtraService
	{
		$this->className = $className;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getInitValue()
	{
		return $this->initValue;
	}

	/**
	 * @param string $initValue
	 * @return ExtraService
	 */
	public function setInitValue(string $initValue): ExtraService
	{
		$this->initValue = $initValue;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * @param array $params
	 * @return ExtraService
	 */
	public function setParams(array $params): ExtraService
	{
		$this->params = $params;

		return $this;
	}
}
