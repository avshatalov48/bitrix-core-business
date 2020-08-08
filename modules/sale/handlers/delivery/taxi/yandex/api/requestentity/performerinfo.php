<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class PerformerInfo
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class PerformerInfo implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var string */
	private $courierName;

	/** @var string */
	private $legalName;

	/** @var string */
	private $carModel;

	/** @var string */
	private $carNumber;

	/**
	 * @return string
	 */
	public function getCourierName()
	{
		return $this->courierName;
	}

	/**
	 * @param string $courierName
	 * @return PerformerInfo
	 */
	public function setCourierName(string $courierName): PerformerInfo
	{
		$this->courierName = $courierName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLegalName()
	{
		return $this->legalName;
	}

	/**
	 * @param string $legalName
	 * @return PerformerInfo
	 */
	public function setLegalName(string $legalName): PerformerInfo
	{
		$this->legalName = $legalName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCarModel()
	{
		return $this->carModel;
	}

	/**
	 * @param string $carModel
	 * @return PerformerInfo
	 */
	public function setCarModel(string $carModel): PerformerInfo
	{
		$this->carModel = $carModel;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCarNumber()
	{
		return $this->carNumber;
	}

	/**
	 * @param string $carNumber
	 * @return PerformerInfo
	 */
	public function setCarNumber(string $carNumber): PerformerInfo
	{
		$this->carNumber = $carNumber;

		return $this;
	}
}
