<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class Warning
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class Warning extends RequestEntity
{
	/** @var  string */
	protected $source;

	/** @var  string */
	protected $code;

	/** @var string */
	protected $message;

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
