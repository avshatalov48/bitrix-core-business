<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class ErrorMessage
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class ErrorMessage extends RequestEntity
{
	/** @var  string */
	protected $code;

	/** @var string */
	protected $message;

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
