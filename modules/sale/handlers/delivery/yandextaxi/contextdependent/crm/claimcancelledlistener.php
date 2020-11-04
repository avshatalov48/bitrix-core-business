<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;

use Bitrix\Crm\Order\Shipment;
use Bitrix\Crm\Timeline\DeliveryController;
use Bitrix\Main\Event;
use Bitrix\Sale\Delivery\Services\Taxi\CancellationRequestResult;
use Bitrix\Sale\Repository\ShipmentRepository;
use Sale\Handlers\Delivery\YandexTaxi\Common\ShipmentDataExtractor;

/**
 * Class ClaimCancelledListener
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm
 * @internal
 */
final class ClaimCancelledListener
{
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
		$shipmentId = $event->getParameter('SHIPMENT_ID');
		$requestId = $event->getParameter('REQUEST_ID');

		/** @var CancellationRequestResult $cancellationResult */
		$cancellationResult = $event->getParameter('CANCELLATION_RESULT');

		$this->activityManager->resetActivity($shipmentId, $requestId);
		$this->createCancelledMessage($shipmentId, $cancellationResult);
	}

	/**
	 * @param int $shipmentId
	 * @param CancellationRequestResult $cancellationResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function createCancelledMessage(int $shipmentId, CancellationRequestResult $cancellationResult)
	{
		$shipment = ShipmentRepository::getInstance()->getById($shipmentId);
		if (!$shipment || !$shipment instanceof Shipment)
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
					],
				],
				'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
				'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment),
			]
		);
	}
}
