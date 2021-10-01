<?php

namespace Sale\Handlers\Delivery\Rest\DataProviders;

use Bitrix\Location\Entity\Address;
use Bitrix\Main\Loader;
use Bitrix\Sale;

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
			'DELIVERY_SERVICE' => self::getDeliveryService($shipment->getDeliveryId()),
			'PRICE' =>  $shipment->getShipmentItemCollection()->getPrice(),
			'CURRENCY' => $shipment->getCurrency(),
			'WEIGHT' => $shipment->getWeight(),
			'PROPERTIES' => self::getProperties($shipment),
			'ITEMS' => self::getItems($shipment),
			'EXTRA_SERVICES' => self::getExtraServices($shipment),
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
			'CONFIG' => $delivery->getConfigValues(),
		];
		$parentDelivery = $delivery->getParentService();
		if ($parentDelivery)
		{
			$result['PARENT'] = [
				'CONFIG' => $parentDelivery->getConfigValues(),
			];
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

		foreach ($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			if (!$basketItem = $shipmentItem->getBasketItem())
			{
				continue;
			}

			$dimension = $basketItem->getField('DIMENSIONS');

			$result[] = [
				'NAME' => $basketItem->getField('NAME'),
				'PRICE' => $basketItem->getPrice(),
				'WEIGHT' => $basketItem->getWeight(),
				'CURRENCY' => $basketItem->getCurrency(),
				'QUANTITY' => $shipmentItem->getQuantity(),
				'DIMENSIONS' => ($dimension && is_string($dimension) && \CheckSerializedData($dimension))
					? unserialize($dimension, ['allowed_classes' => false])
					: $dimension,
			];
		}

		return $result;
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
				'ID' => $extraService->getId(),
				'CODE' => $extraService->getCode(),
				'NAME' => $extraService->getName(),
				'VALUE' => $extraService->getValue(),
				'INIT_VALUE' => $extraService->getInitial(),
				'PRICE' => $extraService->getPriceShipment(),
			];
		}

		return $result;
	}

	/**
	 * @param Sale\Shipment $shipment
	 * @return array
	 */
	private static function getProperties(Sale\Shipment $shipment): array
	{
		$result = [];

		$propertyCollection = $shipment->getPropertyCollection();
		/** @var Sale\PropertyValue $property */
		foreach ($propertyCollection as $property)
		{
			$propertyId = $property->getPropertyId();

			$result[$propertyId] = [
				'PROPERTY' => [
					'ID' => $propertyId,
				],
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

		return [
			'LATITUDE' => $address->getLatitude(),
			'LONGITUDE' => $address->getLongitude(),
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
