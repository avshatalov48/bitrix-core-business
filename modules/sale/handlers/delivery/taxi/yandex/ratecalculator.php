<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\Api;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Address;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Estimation;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\TransportClassification;

/**
 * Class RateCalculator
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class RateCalculator
{
	/** @var Api */
	protected $api;

	/** @var ClaimBuilder */
	protected $claimBuilder;

	/**
	 * RateCalculator constructor.
	 * @param Api $api
	 * @param ClaimBuilder $claimBuilder
	 */
	public function __construct(Api $api, ClaimBuilder $claimBuilder)
	{
		$this->api = $api;
		$this->claimBuilder = $claimBuilder;
	}

	/**
	 * @param Shipment $shipment
	 * @return CalculateRateResult
	 */
	public function calculateRate(Shipment $shipment): CalculateRateResult
	{
		$result = new CalculateRateResult();

		$addressFromResult = $this->claimBuilder->buildAddressFrom($shipment);
		if (!$addressFromResult->isSuccess())
		{
			return $result->addErrors($addressFromResult->getErrors());
		}
		/** @var Address $addressFrom */
		$addressFrom = $addressFromResult->getData()['ADDRESS'];

		$addressToResult = $this->claimBuilder->buildAddressTo($shipment);
		if (!$addressToResult->isSuccess())
		{
			return $result->addErrors($addressToResult->getErrors());
		}
		/** @var Address $addressFrom */
		$addressTo = $addressToResult->getData()['ADDRESS'];

		$taxiClass = $this->claimBuilder->getTaxiClass($shipment);
		if (!$taxiClass)
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_AUTO_CLASS_NOT_SPECIFIED')));
		}

		$getShippingItemsResult = $this->claimBuilder->getShippingItems($shipment);
		if (!$getShippingItemsResult->isSuccess())
		{
			return $result->addErrors($getShippingItemsResult->getErrors());
		}

		$shippingItems =  $getShippingItemsResult->getItems();
		if (!$shippingItems)
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_EMPTY_PRODUCT_LIST')));
		}

		$estimationRequest = (new Estimation())
			->addRoutePoint($addressFrom)
			->addRoutePoint($addressTo)
			->setRequirements((new TransportClassification())->setTaxiClass($taxiClass));

		foreach ($shippingItems as $shippingItem)
		{
			$estimationRequest->addItem($shippingItem);
		}

		if (!$this->claimBuilder->isDoorDeliveryRequired($shipment))
		{
			$estimationRequest->setSkipDoorToDoor(true);
		}

		$checkPriceResult = $this->api->checkPrice($estimationRequest);
		if (!$checkPriceResult->isSuccess())
		{
			return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RATE_CALCULATE_ERROR')));
		}

		if ($checkPriceResult->getCurrency() !== $shipment->getCollection()->getOrder()->getCurrency())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('SALE_YANDEX_TAXI_RATE_CALCULATE_CURRENCY_MISMATCH_ERROR')
				)
			);
		}

		return $result->setRate($checkPriceResult->getPrice());
	}
}
