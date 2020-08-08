<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Crm;

use Bitrix\Crm\Timeline\DeliveryController;
use Bitrix\Main\Event;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Sale\Handlers\Delivery\Taxi\Status\Initial;
use Sale\Handlers\Delivery\Taxi\Yandex\CanGetShipment;
use Sale\Handlers\Delivery\Taxi\Yandex\ShipmentDataExtractor;

/**
 * Class NewOrderListener
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Crm
 */
class NewOrderListener
{
	use CanGetShipment;

	/** @var ActivityManager */
	protected $activityManager;

	/** @var ShipmentDataExtractor */
	protected $extractor;

	/** @var BindingsMaker */
	protected $bindingsMaker;

	/**
	 * NewOrderListener constructor.
	 * @param ActivityManager $activityManager
	 * @param ShipmentDataExtractor $extractor
	 * @param BindingsMaker $bindingsMaker
	 */
	public function __construct(
		ActivityManager $activityManager,
		ShipmentDataExtractor $extractor,
		BindingsMaker $bindingsMaker
	) {
		$this->activityManager = $activityManager;
		$this->extractor = $extractor;
		$this->bindingsMaker = $bindingsMaker;
	}

	/**
	 * @param Event $event
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listen(Event $event)
	{
		/** @var Order $order */
		$order = $event->getParameter('ORDER');

		$shipment = $this->getByOrder($order);

		$this->createEstimationMessage($shipment);
		$this->createActivity($shipment);
	}

	/**
	 * @param Shipment $shipment
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function createEstimationMessage(Shipment $shipment)
	{
		DeliveryController::getInstance()->createTaxiEstimationReceivedHistoryMessage(
			$shipment->getId(),
			[
				'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
				'SETTINGS' => ['FIELDS' => $this->makeSharedFields($shipment)],
				'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment)
			]
		);
	}

	/**
	 * @param Shipment $shipment
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function createActivity(Shipment $shipment)
	{
		$this->activityManager->createActivity(
			$shipment,
			$this->extractor->getResponsibleUserId($shipment),
			array_merge($this->makeSharedFields($shipment), ['STATUS' => (new Initial())->getCode()])
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function makeSharedFields(Shipment $shipment)
	{
		$result = [
			'SHIPMENT_ID' => $shipment->getId(),
			'DELIVERY_SYSTEM_NAME' => $this->extractor->getDeliverySystemName($shipment),
			'DELIVERY_SYSTEM_LOGO' => $this->extractor->getDeliverySystemLogo($shipment),
			'DELIVERY_METHOD' => $this->extractor->getDeliveryMethod($shipment),
			'ADDRESS_FROM' => $this->extractor->getShortenedAddressFrom($shipment),
			'ADDRESS_TO' => $this->extractor->getShortenedAddressTo($shipment),
			'DELIVERY_PRICE' => $this->extractor->getDeliveryPriceFormatted($shipment),
		];

		$expectedDeliveryPriceFormatted = $this->extractor->getExpectedDeliveryPriceFormatted($shipment);

		if (!is_null($expectedDeliveryPriceFormatted))
		{
			$result['EXPECTED_PRICE_DELIVERY'] = $expectedDeliveryPriceFormatted;
		}

		return $result;
	}
}
