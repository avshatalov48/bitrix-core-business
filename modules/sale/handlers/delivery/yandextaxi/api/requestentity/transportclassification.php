<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class TransportClassification
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class TransportClassification extends RequestEntity
{
	/** @var string */
	protected $taxiClass;

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
}
