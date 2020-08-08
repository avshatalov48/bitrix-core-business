<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class ErrorMessage
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class ErrorMessage implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var  string */
	private $code;

	/** @var string */
	private $message;

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 * @return ErrorMessage
	 */
	public function setCode(string $code): ErrorMessage
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
	 * @return ErrorMessage
	 */
	public function setMessage(string $message): ErrorMessage
	{
		$this->message = $message;

		return $this;
	}
}
