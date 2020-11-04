<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class PerformerInfo
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class PerformerInfo extends RequestEntity
{
	/** @var string */
	protected $courierName;

	/** @var string */
	protected $legalName;

	/** @var string */
	protected $carModel;

	/** @var string */
	protected $carNumber;

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
