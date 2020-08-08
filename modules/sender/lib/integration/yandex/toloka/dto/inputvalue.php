<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class InputValue implements TolokaTransferObject
{
	/**
	 * @var string
	 */
	private $identificator;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @return string
	 */
	public function getIdentificator(): string
	{
		return $this->identificator;
	}

	/**
	 * @param string $identificator
	 *
	 * @return InputValue
	 */
	public function setIdentificator(string $identificator): InputValue
	{
		$this->identificator = $identificator;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 *
	 * @return InputValue
	 */
	public function setValue(string $value): InputValue
	{
		$this->value = $value;

		return $this;
	}



	public function toArray(): array
	{
		return [
			$this->identificator => $this->value
		];
	}
}