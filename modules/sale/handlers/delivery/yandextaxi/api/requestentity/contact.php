<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class Contact
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class Contact extends RequestEntity
{
	/** @var string */
	protected $phone;

	/** @var string */
	protected $name;

	/** @var string */
	protected $email;

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
		$this->phone = $phone;

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
