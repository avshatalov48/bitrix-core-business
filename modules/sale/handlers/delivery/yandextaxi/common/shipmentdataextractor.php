<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Common;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Service\FormatService;
use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\ExtraServices\Manager;
use Bitrix\Sale\Delivery\Services\OrderPropsDictionary;
use Bitrix\Sale\PropertyValueBase;
use Bitrix\Sale\Shipment;

/**
 * Class ShipmentDataExtractor
 * @package Sale\Handlers\Delivery\YandexTaxi\Common
 * @internal
 */
final class ShipmentDataExtractor
{
	/**
	 * @param Shipment $shipment
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getShortenedAddressFrom(Shipment $shipment)
	{
		return $this->getShortenedAddress(
			$shipment->getOrder()->getPropertyCollection()->getItemByOrderPropertyCode(
				OrderPropsDictionary::ADDRESS_FROM_PROPERTY_CODE
			)
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getShortenedAddressTo(Shipment $shipment)
	{
		return $this->getShortenedAddress(
			$shipment->getOrder()->getPropertyCollection()->getItemByOrderPropertyCode(
				OrderPropsDictionary::ADDRESS_TO_PROPERTY_CODE
			)
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return bool|mixed|string|string[]|null
	 */
	public function getDeliveryPriceFormatted(Shipment $shipment)
	{
		return SaleFormatCurrency(
			$shipment->getField('PRICE_DELIVERY'),
			$shipment->getOrder()->getCurrency()
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return float|null
	 */
	public function getExpectedDeliveryPrice(Shipment $shipment): ?float
	{
		$expectedPriceDelivery = $shipment->getField('EXPECTED_PRICE_DELIVERY');

		return is_null($expectedPriceDelivery) ? $expectedPriceDelivery : (float)$expectedPriceDelivery;
	}

	/**
	 * @param Shipment $shipment
	 * @return bool|mixed|string|string[]|null
	 */
	public function getExpectedDeliveryPriceFormatted(Shipment $shipment)
	{
		$expectedDeliveryPrice = $this->getExpectedDeliveryPrice($shipment);

		if (is_null($expectedDeliveryPrice))
		{
			return null;
		}

		return SaleFormatCurrency($expectedDeliveryPrice, $shipment->getOrder()->getCurrency());
	}

	/**
	 * @param Shipment $shipment
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getDeliverySystemName(Shipment $shipment)
	{
		return $shipment->getDelivery()->getName();
	}

	/**
	 * @param Shipment $shipment
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getDeliverySystemLogo(Shipment $shipment)
	{
		return $shipment->getDelivery()->getLogotipPath();
	}

	/**
	 * @param Shipment $shipment
	 * @return mixed
	 */
	public function getDeliveryMethod(Shipment $shipment)
	{
		$extraServiceManager = new Manager($shipment->getDeliveryId());
		$extraServiceManager->setValues($shipment->getExtraServices());

		$items = $extraServiceManager->getItems();

		foreach ($items as $item)
		{
			$params = $item->getParams();

			if ($item->getCode() != OrderEntitiesCodeDictionary::VEHICLE_TYPE_EXTRA_SERVICE_CODE)
			{
				continue;
			}

			$value = $item->getValue();
			foreach ($params['PRICES'] as $id => $priceItem)
			{
				if ($id == $value)
				{
					return (string)$priceItem['TITLE'];
				}
			}
		}

		return null;
	}

	/**
	 * @param Shipment $shipment
	 * @return int
	 */
	public function getResponsibleUserId(Shipment $shipment)
	{
		global $USER;

		$responsibleId = $shipment->getField('RESPONSIBLE_ID');

		if (!$responsibleId)
		{
			$responsibleId = $shipment->getField('EMP_RESPONSIBLE_ID');
		}

		if (!$responsibleId)
		{
			$responsibleId = $USER->getId();
		}

		return (int)$responsibleId;
	}

	/**
	 * @param Shipment $shipment
	 * @return array|null
	 */
	public function getResponsibleUser(Shipment $shipment)
	{
		$responsibleUserId = $this->getResponsibleUserId($shipment);

		if (!$responsibleUserId)
		{
			return null;
		}

		$responsibleUser = \CUser::GetList(
			($by = 'id'),
			($order = 'asc'),
			['ID' => $responsibleUserId]
		)->fetch();

		return $responsibleUser ? $responsibleUser : null;
	}
	
	/**
	 * @param PropertyValueBase $propertyValue
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getShortenedAddress($propertyValue): string
	{
		if (!Loader::includeModule('location'))
		{
			return '';
		}

		if (is_null($propertyValue))
		{
			return '';
		}

		$addressArray = $propertyValue->getValue();
		if (!is_array($addressArray))
		{
			return '';
		}

		$address = Address::fromArray($addressArray);

		return Address\Converter\StringConverter::convertToString(
			$address,
			FormatService::getInstance()->findDefault(LANGUAGE_ID),
			'template',
			'html'
		);
	}
}
