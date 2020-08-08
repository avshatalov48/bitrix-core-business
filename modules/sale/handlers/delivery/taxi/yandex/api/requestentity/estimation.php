<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class Estimation
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class Estimation implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var ShippingItem[] */
	private $items = [];

	/** @var TransportClassification */
	private $requirements;

	/** @var Address[] */
	private $routePoints;

	/** @var bool */
	private $skipDoorToDoor;

	/**
	 * @return ShippingItem[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param ShippingItem $shippingItem
	 * @return Estimation
	 */
	public function addItem(ShippingItem $shippingItem): Estimation
	{
		$this->items[] = $shippingItem;

		return $this;
	}

	/**
	 * @return TransportClassification
	 */
	public function getRequirements()
	{
		return $this->requirements;
	}

	/**
	 * @param TransportClassification $requirements
	 * @return $this
	 */
	public function setRequirements(TransportClassification $requirements): Estimation
	{
		$this->requirements = $requirements;

		return $this;
	}

	/**
	 * @return Address[]
	 */
	public function getRoutePoints()
	{
		return $this->routePoints;
	}

	/**
	 * @param Address $address
	 * @return $this
	 */
	public function addRoutePoint(Address $address): Estimation
	{
		$this->routePoints[] = $address;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSkipDoorToDoor()
	{
		return $this->skipDoorToDoor;
	}

	/**
	 * @param bool $skipDoorToDoor
	 * @return Estimation
	 */
	public function setSkipDoorToDoor(bool $skipDoorToDoor): Estimation
	{
		$this->skipDoorToDoor = $skipDoorToDoor;

		return $this;
	}
}
