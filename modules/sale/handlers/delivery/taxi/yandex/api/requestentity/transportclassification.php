<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class TransportClassification
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class TransportClassification implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var string */
	private $taxiClass;

	/** @var string */
	private $cargoType;

	/** @var int */
	private $cargoLoaders;

	/**
	 * @return string
	 */
	public function getTaxiClass()
	{
		return $this->taxiClass;
	}

	/**
	 * @param string $taxiClass
	 * @return TransportClassification
	 */
	public function setTaxiClass(string $taxiClass): TransportClassification
	{
		$this->taxiClass = $taxiClass;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCargoType()
	{
		return $this->cargoType;
	}

	/**
	 * @param string $cargoType
	 * @return TransportClassification
	 */
	public function setCargoType(string $cargoType): TransportClassification
	{
		$this->cargoType = $cargoType;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCargoLoaders()
	{
		return $this->cargoLoaders;
	}

	/**
	 * @param int $cargoLoaders
	 * @return TransportClassification
	 */
	public function setCargoLoaders(int $cargoLoaders): TransportClassification
	{
		$this->cargoLoaders = $cargoLoaders;

		return $this;
	}
}
