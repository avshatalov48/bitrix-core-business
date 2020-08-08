<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Location\Entity\Address\Converter\StringConverter;
use Bitrix\Location\Service\FormatService;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sale\Delivery\ExtraServices\Manager;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Address;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\Contact;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\RoutePoint;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\RoutePoints;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\ShippingItem;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity\TransportClassification;

/**
 * Class ClaimBuilder
 * @package Sale\Handlers\Delivery\Taxi\Yandex\ClaimBuilder
 */
class ClaimBuilder
{
	const NEED_CONTACT_TO_EVENT_CODE = 'OnNeedContactTo';

	/** @var EntityProvider */
	protected $entityProvider;

	/** @var ShipmentDataExtractor */
	protected $extractor;

	/** @var Result */
	protected $result;

	/** @var array|null */
	protected $extraServices;

	/**
	 * ClaimBuilder constructor.
	 * @param EntityProvider $entityProvider
	 * @param ShipmentDataExtractor $extractor
	 */
	public function __construct(EntityProvider $entityProvider, ShipmentDataExtractor $extractor)
	{
		$this->entityProvider = $entityProvider;
		$this->extractor = $extractor;
	}

	/**
	 * @param Shipment $shipment
	 * @return ClaimBuildingResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public function build(Shipment $shipment): ClaimBuildingResult
	{
		$this->result = new ClaimBuildingResult();

		$claim = new Claim();

		/**
		 * Contacts
		 */
		$contactFrom = $this->buildContactFrom($shipment);
		if (is_null($contactFrom))
		{
			return $this->result;
		}

		/**
		 * Building Contact To
		 */
		$event = new Event('sale', static::NEED_CONTACT_TO_EVENT_CODE, ['SHIPMENT' => $shipment]);
		$event->send();

		$eventResults = $event->getResults();

		if (is_array($eventResults) && !empty($eventResults))
		{
			foreach ($eventResults as &$eventResult)
			{
				if ($eventResult->getType() == EventResult::ERROR)
				{
					$this->result->addError(new Error($eventResult->getParameters()));
				}
				elseif ($eventResult->getType() == EventResult::SUCCESS)
				{
					/** @var Contact $contactTo */
					$contactTo = $eventResult->getParameters();
				}
			}
		}
		if (!$this->result->isSuccess())
		{
			return $this->result;
		}

		/**
		 * Addresses
		 */
		$addressFromResult = $this->buildAddressFrom($shipment);
		if (!$addressFromResult->isSuccess())
		{
			return $this->result->addErrors($addressFromResult->getErrors());
		}
		/** @var Address $addressFrom */
		$addressFrom = $addressFromResult->getData()['ADDRESS'];

		$addressToResult = $this->buildAddressTo($shipment);
		if (!$addressToResult->isSuccess())
		{
			return $this->result->addErrors($addressToResult->getErrors());
		}
		/** @var Address $addressFrom */
		$addressTo = $addressToResult->getData()['ADDRESS'];

		/**
		 * General comment
		 */
		$commentForDriver = $this->getPropertyValueByShipment(
			$shipment,
			$this->entityProvider->getCommentProperty()->getCode()
		);
		if ($commentForDriver)
		{
			$claim->setComment($commentForDriver);
		}

		/**
		 * Taxi class
		 */
		$taxiClass = $this->getTaxiClass($shipment);

		if (!$taxiClass)
		{
			return $this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_AUTO_CLASS_NOT_SPECIFIED')));
		}

		/**
		 * Door Delivery
		 */
		if (!$this->isDoorDeliveryRequired($shipment))
		{
			$claim->setSkipDoorToDoor(true);
		}

		$claim
			->setEmergencyContact($contactFrom)
			->setClientRequirements((new TransportClassification())->setTaxiClass($taxiClass))
			->setRoutePoints(
				(new RoutePoints())
					->setSource(
						(new RoutePoint())
							->setContact($contactFrom)
							->setAddress($addressFrom)
					)
					->setDestination(
						(new RoutePoint())
							->setContact($contactTo)
							->setAddress($addressTo)
					)
			)
			->setReferralSource('api_1c-bitrix');

		$getShippingItemsResult = $this->getShippingItems($shipment);
		if (!$getShippingItemsResult->isSuccess())
		{
			return $this->result->addErrors($getShippingItemsResult->getErrors());
		}

		$shippingItems =  $getShippingItemsResult->getItems();
		if (!$shippingItems)
		{
			return $this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_EMPTY_PRODUCT_LIST')));
		}

		foreach ($shippingItems as $shippingItem)
		{
			$claim->addItem($shippingItem);
		}

		return $this->result->setClaim($claim);
	}

	/**
	 * @param Shipment $shipment
	 * @return GetShippingItemsResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public function getShippingItems(Shipment $shipment): GetShippingItemsResult
	{
		$result = new GetShippingItemsResult();

		/** @var ShipmentItem $item */
		foreach($shipment->getShipmentItemCollection() as $item)
		{
			$basketItem = $item->getBasketItem();

			if(!$basketItem)
			{
				continue;
			}

			$title = (string)$basketItem->getField('NAME');
			$price = (float)$basketItem->getField('PRICE');
			$currency = (string)$basketItem->getField('CURRENCY');
			$quantity = (int)$basketItem->getField('QUANTITY');

			if ($price <= 0)
			{
				return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_PRODUCT_EMPTY_PRICE')));
			}
			if ($quantity <= 0)
			{
				return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_PRODUCT_EMPTY_QUANTITY')));
			}
			if (empty($title))
			{
				return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_PRODUCT_EMPTY_NAME')));
			}
			if (empty($currency))
			{
				return $result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_CURRENCY_NOT_SPECIFIED')));
			}

			$result->addItem(
				(new ShippingItem())
					->setTitle($title)
					->setCostValue((string)($price * $quantity))
					->setCostCurrency($currency)
					->setQuantity($quantity)
			);
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function initExtraServices(Shipment $shipment)
	{
		if (!is_null($this->extraServices))
		{
			return;
		}

		$this->extraServices = [];

		/**
		 * @TODO needs to be revisit
		 * Maybe we should move this logic to order builder
		 * and initialize extra service values with their default values
		 */
		$extraServicesValues = is_array($shipment->getExtraServices()) ? $shipment->getExtraServices() : [];

		$services = Manager::getExtraServicesList($shipment->getDeliveryId());
		foreach ($services as $serviceId => $serviceItem)
		{
			$initValue = isset($serviceItem['INIT_VALUE']) ? $serviceItem['INIT_VALUE'] : null;

			$value = isset($extraServicesValues[$serviceId]) ? $extraServicesValues[$serviceId] : $initValue;

			$this->extraServices[$serviceItem['CODE']] = $value;
		}
	}

	/**
	 * @param Shipment $shipment
	 * @return bool
	 */
	public function isDoorDeliveryRequired(Shipment $shipment)
	{
		$this->initExtraServices($shipment);

		foreach ($this->extraServices as $code => $value)
		{
			if ($code != $this->entityProvider->getDoorDeliveryExtraService()->getCode())
			{
				continue;
			}

			return ($value == 'Y');
		}

		return false;
	}

	/**
	 * @param Shipment $shipment
	 * @return bool|string
	 */
	public function getTaxiClass(Shipment $shipment)
	{
		$this->initExtraServices($shipment);

		foreach ($this->extraServices as $code => $value)
		{
			if ($code != $this->entityProvider->getVehicleTypeExtraService()->getCode())
			{
				continue;
			}

			return (string)$value;
		}

		return false;
	}

	/**
	 * @param Shipment $shipment
	 * @return Contact|null
	 */
	protected function buildContactFrom(Shipment $shipment)
	{
		$responsibleUser = $this->extractor->getResponsibleUser($shipment);

		if (!$responsibleUser)
		{
			$this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RESPONSIBLE_NOT_SPECIFIED')));
			return null;
		}

		$responsibleUserName = $this->extractor->getResponsibleUserName($responsibleUser);
		$responsibleUserEmail = $this->extractor->getResponsibleUserEmail($responsibleUser);
		$responsibleUserPhone = $this->extractor->getResponsibleUserPhone($responsibleUser);

		/**
		 * Validate responsible person contact information
		 */
		if (!$responsibleUserName)
		{
			$this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RESPONSIBLE_FULL_NAME_NOT_SPECIFIED')));
			return null;
		}
		if (!$responsibleUserPhone)
		{
			$this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RESPONSIBLE_PHONE_NOT_SPECIFIED')));
			return null;
		}
		if (!$responsibleUserEmail)
		{
			$this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RESPONSIBLE_EMAIL_NOT_SPECIFIED')));
			return null;
		}

		return (new Contact())
			->setName($responsibleUserName)
			->setPhone($responsibleUserPhone)
			->setEmail($responsibleUserEmail);
	}

	/**
	 * @param Shipment $shipment
	 * @return Result
	 */
	public function buildAddressFrom(Shipment $shipment): Result
	{
		return $this->buildAddress(
			$shipment,
			$this->entityProvider->getPropertyFrom()
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return Result
	 */
	public function buildAddressTo(Shipment $shipment): Result
	{
		return $this->buildAddress(
			$shipment,
			$this->entityProvider->getPropertyTo()
		);
	}

	/**
	 * @param Shipment $shipment
	 * @param OrderProperty $addressProperty
	 * @return Result
	 */
	private function buildAddress(Shipment $shipment, OrderProperty $addressProperty)
	{
		$result = new Result();

		$addressArray = $this->getPropertyValueByShipment($shipment, $addressProperty->getCode());
		if (!$addressArray)
		{
			return $result->addError(
				new Error(
					Loc::getMessage(
						'SALE_YANDEX_TAXI_FIELD_VALUE_NOT_SPECIFIED',
						[
							'#FIELD_NAME#' => $addressProperty->getName()
						]
					)
				)
			);
		}

		$address = \Bitrix\Location\Entity\Address::fromArray($addressArray);

		return $result->setData(
			[
				'ADDRESS' => (new Address())
					->setFullName(
						$address->toString(
							FormatService::getInstance()->findDefault(LANGUAGE_ID),
							StringConverter::STRATEGY_TYPE_FIELD_SORT,
							StringConverter::CONTENT_TYPE_TEXT
						)
					)
					->setCoordinates([(float)$address->getLongitude(), (float)$address->getLatitude()])
			]
		);
	}

	/**
	 * @param Shipment $shipment
	 * @param string $propertyCode
	 * @return mixed
	 */
	private function getPropertyValueByShipment(Shipment $shipment, string $propertyCode)
	{
		$propertyValueCollection = $shipment->getOrder()->getPropertyCollection();
		if (!$propertyValueCollection)
		{
			return null;
		}

		/** @var \Bitrix\Sale\PropertyValue $propertyValue */
		foreach ($propertyValueCollection as $propertyValue)
		{
			/** @var \Bitrix\Sale\Property $property */
			$property = $propertyValue->getPropertyObject();

			if ($property->getField('CODE') == $propertyCode)
			{
				return $propertyValue->getValue();
			}
		}

		return null;
	}
}
