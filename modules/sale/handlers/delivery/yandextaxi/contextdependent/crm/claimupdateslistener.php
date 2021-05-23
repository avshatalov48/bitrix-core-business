<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ContextDependent\Crm;

use Bitrix\Crm\Activity\Provider\Sms;
use Bitrix\Crm\Order\Company;
use Bitrix\Crm\Order\Contact;
use Bitrix\Crm\Order\ContactCompanyEntity;
use Bitrix\Crm\Order\Order;
use Bitrix\Crm\Timeline\DeliveryController;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\Web\Uri;
use Bitrix\MessageService\Sender\SmsManager;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Internals\LocalDeliveryRequestTable;
use Bitrix\Crm\Order\Shipment;
use Bitrix\Sale\Repository\ShipmentRepository;
use Sale\Handlers\Delivery\YandexTaxi\Api\Api;
use Sale\Handlers\Delivery\YandexTaxi\Api\StatusDictionary;
use Sale\Handlers\Delivery\YandexTaxi\Common\ShipmentDataExtractor;
use Sale\Handlers\Delivery\YandexTaxi\Common\StatusMapper;
use Sale\Handlers\Delivery\YandexTaxi\Internals\ClaimsTable;

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

				$this->sendSmsToClient($shipment, $performer, $claim);
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
		}
	}

	/**
	 * @param Shipment $shipment
	 * @param array $performer
	 * @param array $claim
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function sendSmsToClient(Shipment $shipment, array $performer, array $claim)
	{
		$deliveryResponsibleId = $this->extractor->getResponsibleUserId($shipment);

		if (!Loader::includeModule('messageservice')
			|| !SmsManager::getUsableSender()
		)
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

			return;
		}

		/** @var ContactCompanyEntity|null $entityCommunication */
		$entityCommunication = $this->getEntityCommunication(Order::load($shipment->getOrder()->getId()));

		if ($entityCommunication)
		{
			$bindings = $this->bindingsMaker->makeByShipment($shipment, 'OWNER');
			$messageBody = $this->getSmsBody($claim);
			$messageTo = $this->getEntityCommunicationPhone($entityCommunication);

			$result =  SmsManager::sendMessage([
				'MESSAGE_FROM' => '',
				'AUTHOR_ID' => $deliveryResponsibleId,
				'MESSAGE_TO' => $messageTo,
				'MESSAGE_BODY' => $messageBody,
				'MESSAGE_HEADERS' => [
					'module_id' => 'crm',
					'bindings' => $bindings
				]
			]);

			if($result->isSuccess())
			{
				Sms::addActivity([
					'AUTHOR_ID' => $deliveryResponsibleId,
					'DESCRIPTION' => $messageBody,
					'ASSOCIATED_ENTITY_ID' => $result->getId(),
					'BINDINGS' => $bindings,
					'COMMUNICATIONS' => [
						[
							'ENTITY_TYPE' => $entityCommunication ? $entityCommunication::getEntityTypeName() : '',
							'ENTITY_TYPE_ID' => $entityCommunication ? $entityCommunication::getEntityType() : '',
							'ENTITY_ID' => $entityCommunication ? $entityCommunication->getField('ENTITY_ID') : '',
							'TYPE' => \CCrmFieldMulti::PHONE,
							'VALUE' => $messageTo
						]
					]
				]);
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
	 * @param ContactCompanyEntity $entity
	 * @return mixed|string
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	private function getEntityCommunicationPhone(ContactCompanyEntity $entity)
	{
		$phoneList = \CCrmFieldMulti::GetEntityFields(
			$entity::getEntityTypeName(),
			$entity->getField('ENTITY_ID'),
			'PHONE',
			true,
			false
		);
		foreach ($phoneList as $phone)
		{
			return Parser::getInstance()->parse($phone['VALUE'])->format();
		}

		return '';
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
		return Loc::getMessage('SALE_YANDEX_TAXI_PARCEL_ON_ITS_WAY_SMS');
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
