<?php

namespace Sale\Handlers\Delivery\Rest\DataProviders;

use Bitrix\Location\Entity\Address;
use Bitrix\Main\Loader;
use Bitrix\Sale;
use Bitrix\Sale\BasketItem;

/**
 * Class Shipment
 * @package Sale\Handlers\Delivery\Rest\DataProviders
 * @final
 */
final class Shipment
{
	/**
	 * @param Sale\Shipment $shipment
	 * @return array
	 */
	public static function getData(Sale\Shipment $shipment): array
	{
		return [
			'ID' => $shipment->getId() ?: null,
			'DELIVERY_SERVICE' => self::getDeliveryService($shipment->getDeliveryId()),
			'PRICE' =>  $shipment->getShipmentItemCollection()->getPrice(),
			'CURRENCY' => $shipment->getCurrency(),
			'WEIGHT' => $shipment->getWeight(),
			'PROPERTY_VALUES' => self::getPropertyValues($shipment),
			'ITEMS' => self::getItems($shipment),
			'EXTRA_SERVICES_VALUES' => self::getExtraServices($shipment),
			'RESPONSIBLE_CONTACT' => ResponsibleContact::getData($shipment),
			'RECIPIENT_CONTACT' => RecipientContact::getData($shipment),
		];
	}

	/**
	 * @param int $deliveryId
	 * @return array
	 */
	private static function getDeliveryService(int $deliveryId): ?array
	{
		$delivery = Sale\Delivery\Services\Manager::getObjectById($deliveryId);
		if (!$delivery)
		{
			return null;
		}

		$result = [
			'ID' => (int)$delivery->getId(),
			'CONFIG' => self::getConfigValues($delivery->getConfigValues()),
		];
		$parentDelivery = $delivery->getParentService();
		if ($parentDelivery)
		{
			$result['PARENT'] = [
				'ID' => (int)$parentDelivery->getId(),
				'CONFIG' => self::getConfigValues($parentDelivery->getConfigValues()),
			];
		}

		return $result;
	}

	/**
	 * @param array $rawConfig
	 * @return array
	 */
	private static function getConfigValues(array $rawConfig): array
	{
		$result = [];

		if (isset($rawConfig['MAIN']) && is_array($rawConfig['MAIN']))
		{
			foreach ($rawConfig['MAIN'] as $name => $value)
			{
				if ($name === 'REST_CODE')
				{
					continue;
				}

				$result[] = [
					'CODE' => $name,
					'VALUE' => $value
				];
			}
		}

		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @return array
	 */
	private static function getItems(Sale\Shipment $shipment): array
	{
		$result = [];

		/** @var Sale\ShipmentItem $shipmentItem */
		foreach ($shipment->getShipmentItemCollection()->getShippableItems() as $shipmentItem)
		{
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}

			$basketItemWeight = $basketItem->getWeight();

			$result[] = [
				'NAME' => $basketItem->getField('NAME'),
				'PRICE' => $basketItem->getPrice(),
				'WEIGHT' => $basketItemWeight === '' || is_null($basketItemWeight) ? null : (float)$basketItemWeight,
				'CURRENCY' => $basketItem->getCurrency(),
				'QUANTITY' => $shipmentItem->getQuantity(),
				'DIMENSIONS' => self::getDimensions($basketItem),
			];
		}

		return $result;
	}

	private static function getDimensions(BasketItem $basketItem): ?array
	{
		$dimension = $basketItem->getField('DIMENSIONS');

		$dimension =
			$dimension && is_string($dimension) && \CheckSerializedData($dimension)
				? unserialize($dimension, ['allowed_classes' => false])
				: $dimension
		;

		if (
			isset($dimension['WIDTH'])
			&& isset($dimension['HEIGHT'])
			&& isset($dimension['LENGTH'])
		)
		{
			return [
				'WIDTH' => (float)$dimension['WIDTH'],
				'HEIGHT' => (float)$dimension['HEIGHT'],
				'LENGTH' => (float)$dimension['LENGTH'],
			];
		}

		return null;
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @return array
	 */
	private static function getExtraServices(Sale\Shipment $shipment): array
	{
		$result = [];

		$extraServiceManager = new Sale\Delivery\ExtraServices\Manager($shipment->getDeliveryId());
		$extraServiceManager->setOperationCurrency($shipment->getField('CURRENCY'));
		$extraServiceManager->setValues($shipment->getExtraServices());

		foreach ($extraServiceManager->getItems() as $extraService)
		{
			$result[] = [
				'ID' => (int)$extraService->getId(),
				'CODE' => $extraService->getCode(),
				'VALUE' => $extraService->getValue(),
			];
		}

		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @return array
	 */
	private static function getPropertyValues(Sale\Shipment $shipment): array
	{
		$result = [];

		$propertyCollection = $shipment->getPropertyCollection();
		/** @var Sale\PropertyValue $property */
		foreach ($propertyCollection as $property)
		{
			if (!in_array($property->getType(), ['STRING', 'ADDRESS']))
			{
				continue;
			}
			
			$propertyId = $property->getPropertyId();

			$result[] = [
				'ID' => (int)$propertyId,
				'TYPE' => $property->getType(),
				'VALUE' => self::getPropertyValue($property),
			];
		}

		return $result;
	}

	/**
	 * @param Sale\EntityPropertyValue $propertyValue
	 * @return mixed
	 */
	private static function getPropertyValue(Sale\EntityPropertyValue $propertyValue)
	{
		if ($propertyValue->getType() === 'ADDRESS')
		{
			return self::getAddressPropertyValue($propertyValue);
		}

		return $propertyValue->getValue();
	}

	/**
	 * @param Sale\EntityPropertyValue $propertyValue
	 * @return array|null
	 */
	private static function getAddressPropertyValue(Sale\EntityPropertyValue $propertyValue): ?array
	{
		$value = $propertyValue->getValue();
		if (!$value || !is_array($value))
		{
			return null;
		}

		$fieldsTypeMap = self::getAddressFieldsTypeMap();
		$address = Address::fromArray($value);

		$fieldCollection = $address->getFieldCollection();

		$addressFields = [];
		/** @var Address\Field $field */
		foreach ($fieldCollection as $field)
		{
			$fieldType = $field->getType();
			if (!isset($fieldsTypeMap[$fieldType]))
			{
				continue;
			}

			$addressFields[$fieldsTypeMap[$fieldType]] = $field->getValue();
		}

		$latitude = $address->getLatitude();
		$longitude = $address->getLongitude();

		return [
			'LATITUDE' => $latitude === '' ? null : (float)$latitude,
			'LONGITUDE' => $longitude === '' ? null : (float)$longitude,
			'FIELDS' => $addressFields,
		];
	}

	/**
	 * @return array
	 */
	private static function getAddressFieldsTypeMap(): array
	{
		if (!Loader::includeModule('location'))
		{
			return [];

		}

		return array_flip((new \ReflectionClass(Address\FieldType::class))->getConstants());
	}
}
