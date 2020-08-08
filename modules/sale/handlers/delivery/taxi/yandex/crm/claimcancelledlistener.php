<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Crm;

use Bitrix\Crm\Timeline\DeliveryController;
use Bitrix\Main\Event;
use Sale\Handlers\Delivery\Taxi\Yandex\CancellationResult;
use Sale\Handlers\Delivery\Taxi\Yandex\CanGetShipment;
use Sale\Handlers\Delivery\Taxi\Yandex\ShipmentDataExtractor;

/**
 * Class ClaimCancelledListener
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Crm
 */
class ClaimCancelledListener
{
	use CanGetShipment;

	/** @var ActivityManager */
	protected $activityManager;

	/** @var ShipmentDataExtractor */
	protected $extractor;

	/** @var BindingsMaker */
	protected $bindingsMaker;

	/**
	 * ClaimCancelledListener constructor.
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
	 */
	public function listen(Event $event)
	{
		$request = $event->getParameter('REQUEST');

		/** @var CancellationResult $cancellationResult */
		$cancellationResult = $event->getParameter('CANCELLATION_RESULT');

		$this->activityManager->resetActivity($request);
		$this->createCancelledMessage($request, $cancellationResult);
	}

	/**
	 * @param array $request
	 * @param CancellationResult $cancellationResult
	 */
	private function createCancelledMessage(array $request, CancellationResult $cancellationResult)
	{
		$shipment = $this->getByShipmentId($request['SHIPMENT_ID']);
		if (!$shipment)
		{
			return;
		}

		DeliveryController::getInstance()->createTaxiCancelledByManagerMessage(
			$shipment->getId(),
			[
				'SETTINGS' => [
					'FIELDS' => [
						'DELIVERY_SYSTEM_NAME' => $this->extractor->getDeliverySystemName($shipment),
						'DELIVERY_SYSTEM_LOGO' => $this->extractor->getDeliverySystemLogo($shipment),
						'DELIVERY_METHOD' => $this->extractor->getDeliveryMethod($shipment),
						'IS_PAID' => $cancellationResult->isPaid(),
					]
				],
				'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
				'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment),
			]
		);
	}
}
