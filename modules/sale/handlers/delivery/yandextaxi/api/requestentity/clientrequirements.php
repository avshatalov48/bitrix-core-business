<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class ClientRequirements
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class ClientRequirements extends RequestEntity
{
	/** @var array */
	protected array $taxiClasses;

	/** @var bool */
	protected bool $skipDoorToDoor;

	/**
	 * @return array
	 */
	public function getTaxiClasses(): array
	{
		return $this->taxiClasses;
	}

	/**
	 * @param array $taxiClasses
	 * @return $this
	 */
	public function setTaxiClasses(array $taxiClasses): ClientRequirements
	{
		$this->taxiClasses = $taxiClasses;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSkipDoorToDoor(): bool
	{
		return $this->skipDoorToDoor;
	}

	/**
	 * @param bool $skipDoorToDoor
	 * @return ClientRequirements
	 */
	public function setSkipDoorToDoor(bool $skipDoorToDoor): ClientRequirements
	{
		$this->skipDoorToDoor = $skipDoorToDoor;

		return $this;
	}
}
