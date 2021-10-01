<?php

namespace Sale\Handlers\Delivery\YandexTaxi;

use Bitrix\Location\Entity\Address;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\TariffsResult;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\TariffsOptions;

/**
 * Class TariffsChecker
 * @package Sale\Handlers\Delivery
 * @internal
 */
final class TariffsChecker
{
	/** @var Api */
	protected $api;

	/** @var array */
	private $results = [];

	/**
	 * TariffsChecker constructor.
	 * @param Api $api
	 */
	public function __construct(Api $api)
	{
		$this->api = $api;
	}

	/**
	 * @param array $coordinates
	 * @return TariffsResult
	 */
	private function getTariffsResult(array $coordinates): TariffsResult
	{
		$resultHash = $this->getCoordinatesHash($coordinates);
		if (isset($this->results[$resultHash]))
		{
			return $this->results[$resultHash];
		}

		$this->results[$resultHash] = $this->api->getTariffs(
			(new TariffsOptions)->setStartPoint(
				array_map('floatval', $coordinates)
			)
		);

		return $this->results[$resultHash];
	}

	/**
	 * @param array $coordinates
	 * @return array|null
	 */
	public function getAvailableTariffs(array $coordinates): ?array
	{
		$tariffsResult = $this->getTariffsResult($coordinates);
		if (!$tariffsResult->isSuccess())
		{
			return null;
		}

		return array_map(
			function ($tariff)
			{
				return $tariff->getCode();
			},
			$tariffsResult->getTariffs()
		);
	}

	/**
	 * @param string $tariffCode
	 * @param Shipment $shipment
	 * @return array
	 */
	public function getSupportedRequirementsByTariff(string $tariffCode, Shipment $shipment): array
	{
		$result = [];

		$coordinates = $this->getSourceCoordinatesByShipment($shipment);
		if (!$coordinates)
		{
			return $result;
		}

		$tariffsResult = $this->getTariffsResult($coordinates);
		if (!$tariffsResult->isSuccess())
		{
			return $result;
		}

		$tariffs = $tariffsResult->getTariffs();
		foreach ($tariffs as $tariff)
		{
			if ($tariff->getCode() === $tariffCode)
			{
				$supportedRequirements = $tariff->getSupportedRequirements();
				foreach ($supportedRequirements as $supportedRequirement)
				{
					$result[] = $supportedRequirement;
				}
				break;
			}
		}

		return $result;
	}

	/**
	 * @param string $tariff
	 * @param Shipment $shipment
	 * @return bool|null
	 */
	public function isTariffAvailableByShipment(string $tariff, Shipment $shipment): ?bool
	{
		$coordinates = $this->getSourceCoordinatesByShipment($shipment);
		if (!$coordinates)
		{
			return null;
		}

		$tariffs = $this->getAvailableTariffs($coordinates);
		if (is_null($tariffs))
		{
			return null;
		}

		return in_array($tariff, $tariffs, true);
	}

	/**
	 * @param Shipment $shipment
	 * @return array|null
	 */
	private function getSourceCoordinatesByShipment(Shipment $shipment): ?array
	{
		$addressFrom = $shipment->getPropertyCollection()->getAddressFrom();
		if (!$addressFrom)
		{
			return null;
		}

		$addressFromValue = $addressFrom->getValue();
		if (!$addressFromValue)
		{
			return null;
		}

		$address = Address::fromArray($addressFromValue);
		if (!$address->getLatitude() || !$address->getLongitude())
		{
			return null;
		}

		return [
			$address->getLongitude(),
			$address->getLatitude(),
		];
	}

	/**
	 * @param array $coordinates
	 * @return string
	 */
	private function getCoordinatesHash(array $coordinates): string
	{
		return md5(implode(';', $coordinates));
	}
}
