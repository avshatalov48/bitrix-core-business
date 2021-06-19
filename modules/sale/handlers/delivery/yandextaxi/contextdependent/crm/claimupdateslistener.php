<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;

use Bitrix\Crm\Activity\Provider\Sms;
use Bitrix\Crm\Integration\NotificationsManager;
use Bitrix\Crm\Integration\SmsManager;
use Bitrix\Crm\MessageSender\MessageSender;
use Bitrix\Crm\Order\BindingsMaker\ActivityBindingsMaker;
use Bitrix\Crm\Order\Company;
use Bitrix\Crm\Order\Contact;
use Bitrix\Crm\Order\Order;
use Bitrix\Crm\Timeline\DeliveryController;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Internals\LocalDeliveryRequestTable;
use Bitrix\Crm\Order\Shipment;
use Bitrix\Sale\Repository\ShipmentRepository;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\StatusDictionary;
use Sale\Handlers\Delivery\YandexTaxi\Common\ShipmentDataExtractor;
use Sale\Handlers\Delivery\YandexTaxi\Common\StatusMapper;
use Sale\Handlers\Delivery\YandexTaxi\Internals\ClaimsTable;
use Bitrix\Crm\Activity;

/**
 * Class ClaimUpdatesListener
 * @package Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm
 * @internal
 */
final class ClaimUpdatesListener
{
	/** @var ActivityManager */
	protected $activityManager;

	/** @var StatusMapper */
	protected $statusMapper;

	/** @var Api */
	protected $api;

	/** @var ShipmentDataExtractor */
	protected $extractor;

	/** @var BindingsMaker */
	protected $bindingsMaker;

	/** @var Base */
	protected $deliveryService;

	/**
	 * ClaimUpdatesListener constructor.
	 * @param ActivityManager $activityManager
	 * @param StatusMapper $statusMapper
	 * @param Api $api
	 * @param ShipmentDataExtractor $extractor
	 * @param BindingsMaker $bindingsMaker
	 */
	public function __construct(
		ActivityManager $activityManager,
		StatusMapper $statusMapper,
		Api $api,
		ShipmentDataExtractor $extractor,
		BindingsMaker $bindingsMaker
	) {
		$this->activityManager = $activityManager;
		$this->statusMapper = $statusMapper;
		$this->api = $api;
		$this->extractor = $extractor;
		$this->bindingsMaker = $bindingsMaker;
	}

	/**
	 * @param Event $event
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listen(Event $event)
	{
		/** @var int $id */
		$id = $event->getParameter('ID');

		/** @var array $fields */
		$fields = $event->getParameter('FIELDS');

		$claim = ClaimsTable::getById($id)->fetch();
		if (!$claim)
		{
			return;
		}

		$shipment = ShipmentRepository::getInstance()->getById($claim['SHIPMENT_ID']);
		if (!$shipment || !$shipment instanceof Shipment)
		{
			return;
		}

		/**
		 * @TODO LocalDeliveryRequestTable usage should be moved to lib
		 */
		$deliveryServiceIds = array_column(
			\Bitrix\Sale\Delivery\Services\Manager::getList(
				[
					'select' => ['ID'],
					'filter' => ['PARENT_ID' => $this->deliveryService->getId()]
				]
			)->fetchAll(),
			'ID'
		);
		$deliveryServiceIds[] = $this->deliveryService->getId();

		$request = LocalDeliveryRequestTable::getList(
			[
				'filter' => [
					'DELIVERY_SERVICE_ID' => $deliveryServiceIds,
					'SHIPMENT_ID' => $claim['SHIPMENT_ID'],
					'EXTERNAL_ID' => $claim['EXTERNAL_ID'],
				],
			]
		)->fetch();

		if (!$request)
		{
			return;
		}

		/**
		 * Accept claim automatically
		 */
		if (isset($fields['EXTERNAL_STATUS'])
			&& $fields['EXTERNAL_STATUS'] == StatusDictionary::READY_FOR_APPROVAL
		)
		{
			$this->api->acceptClaim($claim['EXTERNAL_ID'], 1);
		}

		$this->activityManager->updateActivity(
			$request['SHIPMENT_ID'],
			[
				'STATUS' => $this->statusMapper->getMappedStatus($claim['EXTERNAL_STATUS']),
				'REQUEST_CANCELLATION_AVAILABLE' => is_null($claim['EXTERNAL_RESOLUTION']),
			],
			$request['ID']
		);

		switch ($claim['EXTERNAL_STATUS'])
		{
			case StatusDictionary::PERFORMER_FOUND:
				$performer = $this->getPerformer($claim);

				if ($performer)
				{
					$this->updateTaxiActivityWithPerformer($request, $performer);
				}

				$this->sendSmsToClient($shipment, $claim);
				break;
			case StatusDictionary::PERFORMER_NOT_FOUND:
			case StatusDictionary::FAILED:
			case StatusDictionary::ESTIMATING_FAILED:
				DeliveryController::getInstance()->createTaxiPerformerNotFoundMessage(
					$claim['SHIPMENT_ID'],
					[
						'SETTINGS' => [
							'FIELDS' => [
								'DELIVERY_SYSTEM_NAME' => $this->extractor->getDeliverySystemName($shipment),
								'DELIVERY_SYSTEM_LOGO' => $this->extractor->getDeliverySystemLogo($shipment),
								'DELIVERY_METHOD' => $this->extractor->getDeliveryMethod($shipment),
							]
						],
						'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
						'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment),
					]
				);
				$this->activityManager->resetActivity($request['SHIPMENT_ID'], $request['ID']);
				break;
			case StatusDictionary::CANCELLED_BY_TAXI:
				DeliveryController::getInstance()->createTaxiCancelledByDriverMessage(
					$claim['SHIPMENT_ID'],
					[
						'SETTINGS' => [
							'FIELDS' => [
								'FIELDS' => [
									'DELIVERY_SYSTEM_NAME' => $this->extractor->getDeliverySystemName($shipment),
									'DELIVERY_SYSTEM_LOGO' => $this->extractor->getDeliverySystemLogo($shipment),
									'DELIVERY_METHOD' => $this->extractor->getDeliveryMethod($shipment),
								]
							]
						],
						'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
						'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment),
					]
				);
				$this->activityManager->resetActivity($request['SHIPMENT_ID'], $request['ID']);
				break;
			case StatusDictionary::RETURNED_FINISH:
				DeliveryController::getInstance()->createTaxiReturnedFinish(
					$claim['SHIPMENT_ID'],
					[
						'SETTINGS' => [
							'FIELDS' => [
								'FIELDS' => [
									'DELIVERY_SYSTEM_NAME' => $this->extractor->getDeliverySystemName($shipment),
									'DELIVERY_SYSTEM_LOGO' => $this->extractor->getDeliverySystemLogo($shipment),
									'DELIVERY_METHOD' => $this->extractor->getDeliveryMethod($shipment),
								]
							]
						],
						'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
						'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment),
					]
				);
				$this->activityManager->resetActivity($request['SHIPMENT_ID'], $request['ID']);
				break;
			case StatusDictionary::DELIVERED_FINISH:
				$this->activityManager->completeActivity($request['SHIPMENT_ID'], $request['ID']);
				break;
		}

		/**
		 * Finalize
		 */
		if (!is_null($claim['EXTERNAL_RESOLUTION'])
			|| in_array($claim['EXTERNAL_STATUS'], [StatusDictionary::PERFORMER_NOT_FOUND]))
		{
			ClaimsTable::update($claim['ID'], ['FURTHER_CHANGES_EXPECTED' => 'N']);

			if (isset($claim['EXTERNAL_RESOLUTION'])
				&& $claim['EXTERNAL_RESOLUTION'] === ClaimsTable::EXTERNAL_STATUS_SUCCESS
			)
			{
				if ($shipment->setField('DEDUCTED', 'Y')->isSuccess())
				{
					$shipment->getOrder()->save();
				}
			}
		}
	}

	/**
	 * @param Shipment $shipment
	 * @param array $claim
	 */
	private function sendSmsToClient(Shipment $shipment, array $claim)
	{
		/** @var Order $order */
		$order = $shipment->getOrder();

		$entityCommunication = $order->getEntityCommunication();
		$phoneTo = $order->getEntityCommunicationPhone();

		if ($entityCommunication && $phoneTo)
		{
			$sendResult = MessageSender::send(
				[
					NotificationsManager::getSenderCode() => [
						'ACTIVITY_PROVIDER_TYPE_ID' => Activity\Provider\Notification::PROVIDER_TYPE_NOTIFICATION,
						'TEMPLATE_CODE' => 'ORDER_IN_TRANSIT',
						'PLACEHOLDERS' => [
							'NAME' => $entityCommunication->getCustomerName(),
							'ORDER' => $order->getField('ACCOUNT_NUMBER')
						]
					],
					SmsManager::getSenderCode() => [
						'ACTIVITY_PROVIDER_TYPE_ID' => Sms::PROVIDER_TYPE_SALESCENTER_DELIVERY,
						'MESSAGE_BODY' => $this->getSmsBody($claim),
					]
				],
				[
					'COMMON_OPTIONS' => [
						'PHONE_NUMBER' => $phoneTo,
						'USER_ID' => $this->extractor->getResponsibleUserId($shipment),
						'ADDITIONAL_FIELDS' => [
							'ENTITY_TYPE' => $entityCommunication::getEntityTypeName(),
							'ENTITY_TYPE_ID' => $entityCommunication::getEntityType(),
							'ENTITY_ID' => $entityCommunication->getField('ENTITY_ID'),
							'BINDINGS' => ActivityBindingsMaker::makeByShipment(
								$shipment,
								[
									'extraBindings' => [
										[
											'TYPE_ID' => $entityCommunication::getEntityType(),
											'ID' => $entityCommunication->getField('ENTITY_ID')
										]
									]
								]
							),
						]
					]
				]
			);

			if (!$sendResult->isSuccess())
			{
				DeliveryController::getInstance()->createTaxiSmsProviderIssueMessage(
					$claim['SHIPMENT_ID'],
					[
						'SETTINGS' => [
							'FIELDS' => [
								'SMS_PROVIDER_SETUP_LINK' => $this->getSmsProviderSetupLink()->getLocator()
							]
						],
						'AUTHOR_ID' => $this->extractor->getResponsibleUserId($shipment),
						'BINDINGS' => $this->bindingsMaker->makeByShipment($shipment),
					]
				);
			}
		}
	}

	/**
	 * @param Order $order
	 * @return Company|Contact|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getEntityCommunication(Order $order)
	{
		/** @var Contact $contact */
		$contact = $order->getContactCompanyCollection()->getPrimaryContact();
		if ($contact)
		{
			return $contact;
		}

		/** @var Company $company */
		$company = $order->getContactCompanyCollection()->getPrimaryCompany();
		if ($company)
		{
			return $company;
		}

		return null;
	}

	/**
	 * @param array $claim
	 * @return array
	 */
	private function getPerformer(array $claim)
	{
		$result = [];

		$getClaimResult = $this->api->getClaim($claim['EXTERNAL_ID']);
		$remoteClaim = $getClaimResult->getClaim();

		if ($getClaimResult->isSuccess() && !is_null($remoteClaim))
		{
			$performerInfo = $remoteClaim->getPerformerInfo();

			if ($performerInfo)
			{
				$result['PERFORMER_NAME'] = $performerInfo->getCourierName();
				$result['PERFORMER_CAR'] = sprintf(
					'%s %s',
					$performerInfo->getCarModel(),
					$performerInfo->getCarNumber()
				);
			}
		}

		$getPhoneResult = $this->api->getPhone($claim['EXTERNAL_ID']);
		if ($getPhoneResult->isSuccess())
		{
			$result['PERFORMER_PHONE'] = $getPhoneResult->getPhone();
			$result['PERFORMER_PHONE_EXT'] = $getPhoneResult->getExt();
		}

		return $result;
	}

	/**
	 * @param array $request
	 * @param array $performer
	 */
	private function updateTaxiActivityWithPerformer(array $request, array $performer)
	{
		$this->activityManager->updateActivity(
			$request['SHIPMENT_ID'],
			$performer,
			$request['ID']
		);
	}

	/**
	 * @param string $claimId
	 * @return string
	 */
	private function getTrackingLink(string $claimId): string
	{
		return sprintf(
			'https://taxi.yandex.ru/route/%s',
			$claimId
		);
	}

	/**
	 * @return Uri
	 */
	private function getSmsProviderSetupLink()
	{
		return new Uri(
			getLocalPath(
				'components' . \CComponentEngine::makeComponentPath('bitrix:salescenter.smsprovider.panel') . '/slider.php'
			)
		);
	}

	/**
	 * @param array $claim
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private function getSmsBody(array $claim): string
	{
		return Loc::getMessage('SALE_YANDEX_TAXI_YOUR_ORDER_IS_ON_ITS_WAY');
	}

	/**
	 * @param Base $deliveryService
	 * @return ClaimUpdatesListener
	 */
	public function setDeliveryService(Base $deliveryService): ClaimUpdatesListener
	{
		$this->deliveryService = $deliveryService;

		return $this;
	}
}
