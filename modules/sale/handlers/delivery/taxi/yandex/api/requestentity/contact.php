<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

use Bitrix\Main\PhoneNumber;

/**
 * Class Contact
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class Contact implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var string */
	private $phone;

	/** @var string */
	private $name;

	/** @var string */
	private $email;

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone
	 * @return Contact
	 */
	public function setPhone(string $phone): Contact
	{
		$this->phone = PhoneNumber\Formatter::format(
			PhoneNumber\Parser::getInstance()->parse($phone), PhoneNumber\Format::E164
		);

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
	 * @return Contact
	 */
	public function setName(string $name): Contact
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return Contact
	 */
	public function setEmail(string $email): Contact
	{
		$this->email = $email;

		return $this;
	}
}
