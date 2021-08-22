<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult;

use Bitrix\Main\Result;

/**
 * Class TariffsResult
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 * @internal
 */
final class TariffsResult extends Result
{
	/** @var Tariff[] */
	private $tariffs = [];

	/**
	 * @param Tariff $tariff
	 * @return $this
	 */
	public function addTariff(Tariff $tariff): TariffsResult
	{
		$this->tariffs[] = $tariff;

		return $this;
	}

	/**
	 * @return Tariff[]
	 */
	public function getTariffs(): array
	{
		return $this->tariffs;
	}
}
