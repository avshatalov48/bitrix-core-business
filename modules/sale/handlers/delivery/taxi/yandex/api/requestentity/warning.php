<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class Warning
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class Warning implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var  string */
	private $source;

	/** @var  string */
	private $code;

	/** @var string */
	private $message;

	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param string $source
	 * @return Warning
	 */
	public function setSource(string $source): Warning
	{
		$this->source = $source;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 * @return Warning
	 */
	public function setCode(string $code): Warning
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 * @return Warning
	 */
	public function setMessage(string $message): Warning
	{
		$this->message = $message;

		return $this;
	}
}
