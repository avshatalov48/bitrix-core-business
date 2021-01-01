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
	/** @var array */
	private $tariffs = [];

	public function addTariff(string $tariff)
	{
		$this->tariffs[] = $tariff;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getTariffs(): array
	{
		return $this->tariffs;
	}
}
