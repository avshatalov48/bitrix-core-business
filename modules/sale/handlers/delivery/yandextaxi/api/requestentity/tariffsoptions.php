<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class TariffsOptions
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class TariffsOptions extends RequestEntity
{
	/** @var array */
	protected $startPoint;

	/**
	 * @return array|null
	 */
	public function getStartPoint(): ?array
	{
		return $this->startPoint;
	}

	/**
	 * @param array $startPoint
	 * @return TariffsOptions
	 */
	public function setStartPoint(array $startPoint): TariffsOptions
	{
		$this->startPoint = $startPoint;
		return $this;
	}
}
