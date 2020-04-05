<?php

namespace Bitrix\UI\Buttons;

final class JsCode implements \JsonSerializable
{
	/**
	 * @var string
	 */
	private $code;

	/**
	 *
	 * @param string $code
	 */
	public function __construct($code)
	{
		$this->setCode($code);
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
	 *
	 * @return $this
	 */
	public function setCode($code)
	{
		if (is_string($code))
		{
			$this->code = $code;
		}

		return $this;
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return [
			'code' => $this->getCode(),
		];
	}
}