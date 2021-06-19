<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Common;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Service\FormatService;
use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\Services\OrderPropsDictionary;
use Bitrix\Sale\EntityPropertyValue;
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
	 * @return Address
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAddressFrom(Shipment $shipment): ?Address
	{
		return $this->getAddress(
			$shipment->getPropertyCollection()->getItemByOrderPropertyCode(
				OrderPropsDictionary::ADDRESS_FROM_PROPERTY_CODE
			)
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return Address
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAddressTo(Shipment $shipment): ?Address
	{
		return $this->getAddress(
			$shipment->getPropertyCollection()->getItemByOrderPropertyCode(
				OrderPropsDictionary::ADDRESS_TO_PROPERTY_CODE
			)
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getShortenedAddressFrom(Shipment $shipment)
	{
		return $this->getShortenedAddressString(
			$this->getAddressFrom($shipment)
		);
	}

	/**
	 * @param Shipment $shipment
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getShortenedAddressTo(Shipment $shipment)
	{
		return $this->getShortenedAddressString(
			$this->getAddressTo($shipment)
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
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getDeliverySystemName(Shipment $shipment)
	{
		$parentDeliveryService = $this->getParentDelivery($shipment);

		return $parentDeliveryService ? $parentDeliveryService->getName() : '';
	}

	/**
	 * @param Shipment $shipment
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getDeliverySystemLogo(Shipment $shipment)
	{
		$parentDeliveryService = $this->getParentDelivery($shipment);

		return $parentDeliveryService ? $parentDeliveryService->getLogotipPath() : '';
	}

	/**
	 * @param Shipment $shipment
	 * @return \Bitrix\Sale\Delivery\Services\Base|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getParentDelivery(Shipment $shipment)
	{
		$deliveryService = $shipment->getDelivery();
		if (!$deliveryService)
		{
			return null;
		}

		return $deliveryService->getParentService();
	}

	/**
	 * @param Shipment $shipment
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getDeliveryMethod(Shipment $shipment)
	{
		$deliveryService = $shipment->getDelivery();
		if (!$deliveryService)
		{
			return null;
		}

		return $deliveryService->getName();
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
			'id',
			'asc',
			['ID' => $responsibleUserId]
		)->fetch();

		return $responsibleUser ? $responsibleUser : null;
	}

	/**
	 * @param EntityPropertyValue $propertyValue
	 * @return Address|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getAddress($propertyValue): ?Address
	{
		if (!Loader::includeModule('location'))
		{
			return null;
		}

		if (is_null($propertyValue))
		{
			return null;
		}

		$addressArray = $propertyValue->getValue();
		if (!is_array($addressArray))
		{
			return null;
		}

		return Address::fromArray($addressArray);
	}

	/**
	 * @param Address|null $address
	 * @return string
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getShortenedAddressString(?Address $address): string
	{
		if (is_null($address))
		{
			return '';
		}

		return Address\Converter\StringConverter::convertToString(
			$address,
			FormatService::getInstance()->findDefault(LANGUAGE_ID),
			\Bitrix\Location\Entity\Address\Converter\StringConverter::STRATEGY_TYPE_TEMPLATE_COMMA,
			\Bitrix\Location\Entity\Address\Converter\StringConverter::CONTENT_TYPE_TEXT
		);
	}
}
