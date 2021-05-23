<?php

namespace Sale\Handlers\Delivery\YandexTaxi;

use Bitrix\Location\Entity\Address;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\TariffsOptions;
use Sale\Handlers\Delivery\YandexTaxi\Common\ShipmentDataExtractor;

/**
 * Class TariffsChecker
 * @package Sale\Handlers\Delivery
 * @internal
 */
final class TariffsChecker
{
	/** @var Api */
	protected $api;

	/** @var ShipmentDataExtractor */
	protected $shipmentDataExtractor;

	/** @var array|null */
	private $availableTariffs;

	/**
	 * TariffsChecker constructor.
	 * @param Api $api
	 * @param ShipmentDataExtractor $shipmentDataExtractor
	 */
	public function __construct(Api $api, ShipmentDataExtractor $shipmentDataExtractor)
	{
		$this->api = $api;
		$this->shipmentDataExtractor = $shipmentDataExtractor;
	}

	/**
	 * @param array $coordinates
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getAvailableTariffs(array $coordinates): ?array
	{
		if (!is_null($this->availableTariffs))
		{
			return $this->availableTariffs;
		}

		$tariffsResult = $this->api->getTariffs(
			(new TariffsOptions)->setStartPoint(
				array_map('floatval', $coordinates)
			)
		);

		if (!$tariffsResult->isSuccess())
		{
			return null;
		}

		$this->availableTariffs = $tariffsResult->getTariffs();

		return $this->availableTariffs;
	}

	/**
	 * @param string $tariff
	 * @param array $coordinates
	 * @return bool|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function isTariffAvailable(string $tariff, array $coordinates): ?bool
	{
		$tariffs = $this->getAvailableTariffs($coordinates);

		if (is_null($tariffs))
		{
			return null;
		}

		return in_array($tariff, $tariffs, true);
	}

	/**
	 * @param string $tariff
	 * @param Shipment $shipment
	 * @return bool|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function isTariffAvailableByShipment(string $tariff, Shipment $shipment): ?bool
	{
		$addressFrom = $this->shipmentDataExtractor->getAddressFrom($shipment);
		if (is_null($addressFrom))
		{
			return null;
		}

		return $this->isTariffAvailableByAddress($tariff, $addressFrom);
	}

	/**
	 * @param string $tariff
	 * @param Address $address
	 * @return bool|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function isTariffAvailableByAddress(string $tariff, Address $address): ?bool
	{
		if (!$address->getLatitude() || !$address->getLongitude())
		{
			return null;
		}

		return $this->isTariffAvailable(
			$tariff,
			[
				$address->getLongitude(),
				$address->getLatitude(),
			]
		);
	}
}
