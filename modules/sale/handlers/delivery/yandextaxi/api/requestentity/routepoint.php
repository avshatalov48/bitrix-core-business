<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class RoutePoint
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class RoutePoint extends RequestEntity
{
	/** @var Contact */
	protected $contact;

	/** @var Address */
	protected $address;

	/** @var bool */
	protected $skipConfirmation;

	/**
	 * @return Contact
	 */
	public function getContact()
	{
		return $this->contact;
	}

	/**
	 * @param Contact $contact
	 * @return RoutePoint
	 */
	public function setContact(Contact $contact): RoutePoint
	{
		$this->contact = $contact;

		return $this;
	}

	/**
	 * @return Address
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * @param Address $address
	 * @return RoutePoint
	 */
	public function setAddress(Address $address): RoutePoint
	{
		$this->address = $address;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSkipConfirmation()
	{
		return $this->skipConfirmation;
	}

	/**
	 * @param bool $skipConfirmation
	 * @return RoutePoint
	 */
	public function setSkipConfirmation(bool $skipConfirmation): RoutePoint
	{
		$this->skipConfirmation = $skipConfirmation;

		return $this;
	}
}
