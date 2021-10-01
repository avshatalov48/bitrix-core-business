<?php

namespace Bitrix\Sale\Delivery\Services;

/**
 * Class Contact
 * @package Bitrix\Sale\Delivery\Services
 * @internal
 */
final class Contact
{
	/** @var string */
	private $name;

	/** @var Phone[] */
	private $phones = [];

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return Contact
	 */
	public function setName(string $name): Contact
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return Phone[]
	 */
	public function getPhones(): array
	{
		return $this->phones;
	}

	/**
	 * @param Phone $phone
	 * @return $this
	 */
	public function addPhone(Phone $phone): Contact
	{
		$this->phones[] = $phone;
		return $this;
	}
}
