<?php

namespace Sale\Handlers\Delivery\YandexTaxi\ClaimBuilder;

use Bitrix\Location\Entity\Address\FieldCollection;
use Bitrix\Location\Entity\Location;
use Bitrix\Location\Entity;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sale\Delivery\ExtraServices\Manager;
use Bitrix\Sale\Delivery\Services\RecipientDataProvider;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Address;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Claim;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\Contact;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\RoutePoint;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\RoutePoints;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\ShippingItem;
use Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity\TransportClassification;
use Sale\Handlers\Delivery\YandexTaxi\Api\Tariffs\Repository;
use Sale\Handlers\Delivery\YandexTaxi\Common\OrderEntitiesCodeDictionary;
use Sale\Handlers\Delivery\YandexTaxi\Common\ReferralSourceBuilder;
use Bitrix\Main\PhoneNumber;
use Sale\Handlers\Delivery\YandexTaxi;
use Bitrix\Location\Entity\Address\Field;

/**
 * Class ClaimBuilder
 * @package Sale\Handlers\Delivery\YandexTaxi\ClaimBuilder
 * @internal
 */
final class ClaimBuilder
{
	public const NEED_CONTACT_TO_EVENT_CODE = 'OnDeliveryYandexTaxiNeedContactTo';

	/** @var Repository */
	protected $tariffsRepository;

	/** @var ReferralSourceBuilder */
	protected $referralSourceBuilder;

	/** @var Result */
	protected $result;

	/**
	 * ClaimBuilder constructor.
	 * @param Repository $tariffsRepository
	 * @param ReferralSourceBuilder $referralSourceBuilder
	 */
	public function __construct(
		Repository $tariffsRepository,
		ReferralSourceBuilder $referralSourceBuilder
	)
	{
		$this->tariffsRepository = $tariffsRepository;
		$this->referralSourceBuilder = $referralSourceBuilder;
	}

	/**
	 * @param Shipment $shipment
	 * @return Result
	 */
	public function build(Shipment $shipment): Result
	{
		$this->result = new Result();

		$claim = new Claim();

		/**
		 * Contacts
		 */
		$contactFrom = $this->buildContactFrom($shipment);
		if (is_null($contactFrom))
		{
			return $this->result;
		}

		$contactTo = $this->buildContactTo($shipment);
		if (is_null($contactTo))
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
		$commentForDriver = $this->getPropertyValue(
			$shipment,
			OrderEntitiesCodeDictionary::COMMENT_FOR_DRIVER_PROPERTY_CODE
		);
		if ($commentForDriver)
		{
			$claim->setComment($commentForDriver);
		}

		$buildClientReqResult = $this->buildClientRequirements($shipment);
		if (!$buildClientReqResult->isSuccess())
		{
			return $this->result->addErrors($buildClientReqResult->getErrors());
		}
		$clientRequirements = $buildClientReqResult->getData()['REQUIREMENTS'];

		/**
		 * Door Delivery
		 */
		if (!$this->isDoorDeliveryRequired($shipment))
		{
			$claim->setSkipDoorToDoor(true);
		}

		$claim
			->setEmergencyContact($contactFrom)
			->setClientRequirements($clientRequirements)
			->setRoutePoints(
				(new RoutePoints())
					->setSource(
						(new RoutePoint())
							->setContact($contactFrom)
							->setAddress($addressFrom)
							->setSkipConfirmation(true)
					)
					->setDestination(
						(new RoutePoint())
							->setContact($contactTo)
							->setAddress($addressTo)
							->setSkipConfirmation(true)
					)
			)
			->setReferralSource(
				$this->referralSourceBuilder->getReferralSourceValue()
			);

		$shippingItemCollection = $this->getShippingItemCollection($shipment);
		$validationResult = $shippingItemCollection->isValid();
		if (!$validationResult->isSuccess())
		{
			return $this->result->addErrors($validationResult->getErrors());
		}

		foreach ($shippingItemCollection as $shippingItem)
		{
			$claim->addItem($shippingItem);
		}

		return $this->result->setData(['RESULT' => $claim]);
	}

	/**
	 * @param Shipment $shipment
	 * @return ShippingItemCollection
	 */
	public function getShippingItemCollection(Shipment $shipment): ShippingItemCollection
	{
		$result = new ShippingItemCollection();

		/** @var ShipmentItem $shipmentItem */
		foreach($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();
			if(!$basketItem)
			{
				continue;
			}

			$result->addItem(
				(new ShippingItem())
					->setTitle((string)$basketItem->getField('NAME'))
					->setCostValue((string)((float)$basketItem->getPriceWithVat() * $shipmentItem->getQuantity()))
					->setCostCurrency((string)$basketItem->getCurrency())
					->setQuantity((int)ceil($shipmentItem->getQuantity()))
			);
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return array
	 */
	private function getExtraServiceValues(Shipment $shipment): array
	{
		$result = [];

		$extraServicesValues = is_array($shipment->getExtraServices()) ? $shipment->getExtraServices() : [];

		$services = Manager::getExtraServicesList($shipment->getDeliveryId());
		foreach ($services as $serviceId => $serviceItem)
		{
			$initValue = isset($serviceItem['INIT_VALUE']) ? $serviceItem['INIT_VALUE'] : null;

			$value = isset($extraServicesValues[$serviceId]) ? $extraServicesValues[$serviceId] : $initValue;

			$result[$serviceItem['CODE']] = [
				'VALUE' => $value,
				'SERVICE' => $serviceItem,
			];
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return bool
	 */
	public function isDoorDeliveryRequired(Shipment $shipment)
	{
		$extraServiceValues = $this->getExtraServiceValues($shipment);

		foreach ($extraServiceValues as $code => $item)
		{
			if ($code != OrderEntitiesCodeDictionary::DOOR_DELIVERY_EXTRA_SERVICE_CODE)
			{
				continue;
			}

			return ($item['VALUE'] === 'Y');
		}

		return false;
	}

	/**
	 * @param Shipment $shipment
	 * @return Result
	 */
	public function buildClientRequirements(Shipment $shipment): Result
	{
		$requirements = new TransportClassification();
		$result = new Result();

		$deliveryService = $shipment->getDelivery();
		if (!$deliveryService)
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_SERVICE_NOT_FOUND'))
			);
		}

		$deliveryServiceConfig = $deliveryService->getConfig();
		if (!isset($deliveryServiceConfig['MAIN']['ITEMS']['PROFILE_TYPE']['VALUE']))
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_TARIFF_IS_NOT_SPECIFIED'))
			);
		}
		$tariffCode = $deliveryServiceConfig['MAIN']['ITEMS']['PROFILE_TYPE']['VALUE'];

		$tariff = null;
		$availableTariffs = $this->tariffsRepository->getTariffs();
		foreach ($availableTariffs as $availableTariff)
		{
			if ($availableTariff['name'] === $tariffCode)
			{
				$tariff = $availableTariff;
				break;
			}
		}

		if (is_null($tariff))
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_TARIFF_HAS_NOT_BEEN_FOUND'))
			);
		}
		$requirements->setTaxiClass($tariffCode);

		$extraServiceValues = $this->getExtraServiceValues($shipment);
		$options = [];
		foreach ($tariff['supported_requirements'] as $supportedRequirement)
		{
			if ($supportedRequirement['type'] === 'multi_select')
			{
				foreach ($supportedRequirement['options'] as $srOption)
				{
					if (isset($extraServiceValues[$srOption['value']])
						&& $extraServiceValues[$srOption['value']]['VALUE'] === 'Y'
					)
					{
						if (!is_array($options[$supportedRequirement['name']]))
						{
							$options[$supportedRequirement['name']] = [];
						}
						$options[$supportedRequirement['name']][] = $srOption['value'];
					}
				}
			}
			elseif ($supportedRequirement['type'] === 'select')
			{
				if (isset($extraServiceValues[$supportedRequirement['name']])
					&& !empty($extraServiceValues[$supportedRequirement['name']]['VALUE'])
				)
				{
					foreach ($supportedRequirement['options'] as $srOption)
					{
						/**
						 * Non-strict comparison is required
						 */
						if ($srOption['value'] == $extraServiceValues[$supportedRequirement['name']]['VALUE'])
						{
							$options[$supportedRequirement['name']] = $srOption['value'];
							break;
						}
					}
				}
			}
		}
		$requirements->setOptions($options);

		$result->setData(['REQUIREMENTS' => $requirements]);

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return Contact|null
	 */
	protected function buildContactFrom(Shipment $shipment): ?Contact
	{
		$responsibleUser = $this->getResponsibleUser($shipment);

		if (!$responsibleUser)
		{
			$this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RESPONSIBLE_NOT_SPECIFIED')));
			return null;
		}

		$responsibleUserName = $this->getResponsibleUserName($responsibleUser);
		$responsibleUserEmail = $this->getResponsibleUserEmail($responsibleUser);
		$responsibleUserPhone = $this->getResponsibleUserPhone($responsibleUser);

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

		$oResponsibleUserPhone = PhoneNumber\Parser::getInstance()->parse($responsibleUserPhone);
		if (!$oResponsibleUserPhone->isValid())
		{
			$this->result->addError(
				new Error(
					sprintf(
						'%s: %s',
						Loc::getMessage('SALE_YANDEX_TAXI_RESPONSIBLE_PHONE_NOT_VALID'),
						(string)$oResponsibleUserPhone->format()
					)
				)
			);
			return null;
		}

		if (!$responsibleUserEmail)
		{
			$this->result->addError(new Error(Loc::getMessage('SALE_YANDEX_TAXI_RESPONSIBLE_EMAIL_NOT_SPECIFIED')));
			return null;
		}

		return (new Contact())
			->setName($responsibleUserName)
			->setPhone(
				PhoneNumber\Formatter::format($oResponsibleUserPhone, PhoneNumber\Format::E164)
			)
			->setEmail($responsibleUserEmail);
	}

	/**
	 * @param Shipment $shipment
	 * @return Contact|null
	 */
	protected function buildContactTo(Shipment $shipment): ?Contact
	{
		$recipientContact = RecipientDataProvider::getContact($shipment);

		if (is_null($recipientContact))
		{
			$this->result->addError(
				new Error(
					Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_CLIENT_CONTACT_NOT_FOUND')
				)
			);
			return null;
		}

		if (!$recipientContact->getName())
		{
			$this->result->addError(
				new Error(
					Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_FULL_NAME_NOT_SPECIFIED')
				)
			);
			return null;
		}

		$recipientContactPhones = $recipientContact->getPhones();
		if (empty($recipientContactPhones))
		{
			$this->result->addError(
				new Error(
					Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_SPECIFIED')
				)
			);
			return null;
		}
		else
		{
			$oPhone = PhoneNumber\Parser::getInstance()->parse($recipientContactPhones[0]->getValue());
			if (!$oPhone->isValid())
			{
				$this->result->addError(
					new Error(
						sprintf(
							'%s: %s',
							Loc::getMessage('SALE_YANDEX_TAXI_CLIENT_PHONE_NOT_VALID'),
							(string)$oPhone->format()
						)
					)
				);
				return null;
			}
		}

		return (new Contact())
			->setName($recipientContact->getName())
			->setPhone(PhoneNumber\Formatter::format($oPhone, PhoneNumber\Format::E164));
	}

	/**
	 * @param array $responsibleUser
	 * @return string
	 */
	private function getResponsibleUserName(array $responsibleUser)
	{
		return trim(sprintf('%s %s', $responsibleUser['NAME'], $responsibleUser['LAST_NAME']));
	}

	/**
	 * @param array $responsibleUser
	 * @return string|null
	 */
	private function getResponsibleUserEmail(array $responsibleUser)
	{
		return isset($responsibleUser['EMAIL']) ? (string)$responsibleUser['EMAIL'] : null;
	}

	/**
	 * @param array $responsibleUser
	 * @return string|null
	 */
	private function getResponsibleUserPhone(array $responsibleUser)
	{
		if (isset($responsibleUser['WORK_PHONE']) && !empty($responsibleUser['WORK_PHONE']))
		{
			return (string)$responsibleUser['WORK_PHONE'];
		}
		elseif (isset($responsibleUser['PERSONAL_MOBILE']) && !empty($responsibleUser['PERSONAL_MOBILE']))
		{
			return (string)$responsibleUser['PERSONAL_MOBILE'];
		}
		elseif (isset($responsibleUser['PERSONAL_PHONE']) && !empty($responsibleUser['PERSONAL_PHONE']))
		{
			return (string)$responsibleUser['PERSONAL_PHONE'];
		}

		return null;
	}

	/**
	 * @param Shipment $shipment
	 * @return Result
	 */
	public function buildAddressFrom(Shipment $shipment): Result
	{
		return $this->buildAddress($shipment, 'IS_ADDRESS_FROM');
	}

	/**
	 * @param Shipment $shipment
	 * @return Result
	 */
	public function buildAddressTo(Shipment $shipment): Result
	{
		return $this->buildAddress($shipment, 'IS_ADDRESS_TO');
	}

	/**
	 * @param Shipment $shipment
	 * @param string $attribute
	 * @return Result
	 */
	private function buildAddress(Shipment $shipment, string $attribute)
	{
		$result = new Result();

		if (!Loader::includeModule('location'))
		{
			return $result->addError(
				new Error(Loc::getMessage('SALE_YANDEX_TAXI_LOCATION_MODULE_REQUIRED'))
			);
		}

		$property = $shipment->getPropertyCollection()->getAttribute($attribute);
		$addressArray = $property ? $property->getValue() : null;
		if (!is_array($addressArray) || empty($addressArray))
		{
			return $result->addError(
				new Error(
					Loc::getMessage(
						'SALE_YANDEX_TAXI_FIELD_VALUE_NOT_SPECIFIED',
						[
							'#FIELD_NAME#' => $property ? $property->getName() : ''
						]
					)
				)
			);
		}

		$address = Entity\Address::fromArray($addressArray);
		$addressFieldCollection = $address->getFieldCollection();

		/** @var Field $countryField */
		$countryField = $addressFieldCollection->getItemByType(Location\Type::COUNTRY);

		/** @var Field $cityField */
		$cityField = $addressFieldCollection->getItemByType(Location\Type::LOCALITY);

		/** @var Field $streetField */
		$streetField = $addressFieldCollection->getItemByType(Location\Type::STREET);

		/** @var Field $buildingField */
		$buildingField = $addressFieldCollection->getItemByType(Location\Type::BUILDING);

		return $result->setData(
			[
				'ADDRESS' => (new YandexTaxi\Api\RequestEntity\Address())
					->setCountry($countryField ? $countryField->getValue() : '')
					->setCity($cityField ? $cityField->getValue() : '')
					->setStreet($streetField ? $streetField->getValue() : '')
					->setBuilding($buildingField ? $buildingField->getValue() : '')
					->setComment(
						$this->getAddressFieldValues(
							$addressFieldCollection,
							function (Field $field)
							{
								return (
									$field->getType() > Location\Type::ADDRESS_LINE_1
								);
							}
						)
					)
					->setFullName(
						$this->getAddressFieldValues(
							$addressFieldCollection,
							function (Field $field)
							{
								return (
									$field->getType() >= Location\Type::COUNTRY
									&& $field->getType() <= Location\Type::ADDRESS_LINE_1
								);
							}
						)
					)
					->setCoordinates([(float)$address->getLongitude(), (float)$address->getLatitude()])
			]
		);
	}

	/**
	 * @param FieldCollection $addressFieldCollection
	 * @param \Closure $fieldsFilter
	 * @return string
	 */
	private function getAddressFieldValues(FieldCollection $addressFieldCollection, \Closure $fieldsFilter): string
	{
		return implode(
			', ',
			array_map(
				static function (Field $field)
				{
					return $field->getValue();
				},
				array_filter(
					$addressFieldCollection->getSortedItems(),
					$fieldsFilter
				)
			)
		);
	}

	/**
	 * @param Shipment $shipment
	 * @param string $propertyCode
	 * @return array|string|null
	 */
	private function getPropertyValue(Shipment $shipment, string $propertyCode)
	{
		$propertyValueObject = $this->getPropertyValueObject($shipment, $propertyCode);
		if (!$propertyValueObject)
		{
			return null;
		}

		return $propertyValueObject->getValue();
	}

	/**
	 * @param Shipment $shipment
	 * @param string $propertyCode
	 * @return \Bitrix\Sale\PropertyValue|null
	 */
	private function getPropertyValueObject(Shipment $shipment, string $propertyCode)
	{
		$propertyValueCollection = $shipment->getPropertyCollection();

		/** @var \Bitrix\Sale\PropertyValue $propertyValue */
		foreach ($propertyValueCollection as $propertyValue)
		{
			/** @var \Bitrix\Sale\Property $property */
			$property = $propertyValue->getPropertyObject();

			if ($property->getField('CODE') !== $propertyCode)
			{
				continue;
			}

			return $propertyValue;
		}

		return null;
	}

	/**
	 * @param Shipment $shipment
	 * @return array|null
	 */
	private function getResponsibleUser(Shipment $shipment): ?array
	{
		$result = null;

		$userId = (int)$shipment->getField('RESPONSIBLE_ID');
		if (!$userId && is_object($GLOBALS['USER']))
		{
			$userId = (int)$GLOBALS['USER']->getId();
		}

		$result = \CUser::GetList(
			'id',
			'asc',
			['ID' => $userId]
		)->fetch();

		return $result ?? null;
	}
}
