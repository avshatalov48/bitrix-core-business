<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Crm;

use Bitrix\Crm\Timeline\DeliveryController;
use Bitrix\Main\Event;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\SendTaxiRequestResult;
use Sale\Handlers\Delivery\Taxi\Yandex\RateCalculator;
use Sale\Handlers\Delivery\Taxi\Yandex\ShipmentDataExtractor;

/**
 * Class ClaimCreatedListener
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Crm
 */
class ClaimCreatedListener
{
	/** @var ActivityManager */
	protected $activityManager;

	/** @var ShipmentDataExtractor */
	protected $extractor;

	/** @var RateCalculator */
	protected $calculator;

	/** @var BindingsMaker */
	protected $bindingsMaker;

	/**
	 * ClaimCreatedListener constructor.
	 * @param ActivityManager $activityManager
	 * @param ShipmentDataExtractor $extractor
	 * @param RateCalculator $calculator
	 * @param BindingsMaker $bindingsMaker
	 */
	public function __construct(
		ActivityManager $activityManager,
		ShipmentDataExtractor $extractor,
		RateCalculator $calculator,
		BindingsMaker $bindingsMaker
	) {
		$this->activityManager = $activityManager;
		$this->extractor = $extractor;
		$this->calculator = $calculator;
		$this->bindingsMaker = $bindingsMaker;
	}

	/**
	 * @param Event $event
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listen(Event $event)
	{
		/** @var Shipment $shipment */
		$shipment = $event->getParameter('SHIPMENT');

		$this->createCallMessage($shipment);

		/** @var SendTaxiRequestResult $result */
		$result = $event->getParameter('RESULT');

		$this->updateActivity(
			$shipment,
			$result
		);
	}

	/**
	 * @param Shipment $shipment
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function createCallMessage(Shipment $shipment)
	{
		$fields = [
			'DELIVERY_SYSTEM_NAME' => $this->extractor->getDeliverySystemName($shipment),
			'DELIVERY_METHOD' => $this->extractor->getDeliveryMethod($shipment),
			'DELIVERY_SYSTEM_LOGO' => $this->extractor->getDeliverySystemLogo($shipment),
		];

		$calculateRateResult = $this->calculator->calculateRate($shipment);
		if ($calculateRateResult->isSuccess())
		{
			$fields['EXPECTED_PRICE_DELIVERY'] = SaleFormatCurrency(
				$calculateRateResult->getRate(),
				$shipment->getOrder()->getCurrency()
			);
		}

		DeliveryController::getInstance()->createTaxiCallHistoryMessage(
			$shipment->getId(),
			[
				'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
				'SETTINGS' => ['FIELDS' => $fields],
				'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment)
			]
		);
	}

	/**
	 * @param Shipment $shipment
	 * @param SendTaxiRequestResult $sendTaxiRequestResult
	 */
	private function updateActivity(Shipment $shipment, SendTaxiRequestResult $sendTaxiRequestResult)
	{
		$this->activityManager->updateActivity(
			$shipment->getId(),
			[
				'REQUEST_ID' => $sendTaxiRequestResult->getRequestId(),
				'STATUS' => $sendTaxiRequestResult->getStatus()->getCode(),
				'REQUEST_CANCELLATION_AVAILABLE' => true,
			]
		);
	}
}
