<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class OfferEstimation
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class OfferEstimation extends RequestEntity
{
	/** @var ShippingItem[] */
	protected array $items = [];

	/** @var ClientRequirements */
	protected ClientRequirements $requirements;

	/** @var Address[] */
	protected array $routePoints;

	/**
	 * @return ShippingItem[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param ShippingItem $shippingItem
	 * @return OfferEstimation
	 */
	public function addItem(ShippingItem $shippingItem): OfferEstimation
	{
		$this->items[] = $shippingItem;

		return $this;
	}

	/**
	 * @return ClientRequirements
	 */
	public function getRequirements(): ClientRequirements
	{
		return $this->requirements;
	}

	/**
	 * @param ClientRequirements $requirements
	 * @return OfferEstimation
	 */
	public function setRequirements(ClientRequirements $requirements): OfferEstimation
	{
		$this->requirements = $requirements;

		return $this;
	}

	/**
	 * @return Address[]
	 */
	public function getRoutePoints(): array
	{
		return $this->routePoints;
	}

	/**
	 * @param Address $address
	 * @return OfferEstimation
	 */
	public function addRoutePoint(Address $address): OfferEstimation
	{
		$this->routePoints[] = $address;

		return $this;
	}
}
