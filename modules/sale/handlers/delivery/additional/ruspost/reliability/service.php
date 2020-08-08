<?php

namespace Sale\Handlers\Delivery\Additional\RusPost\Reliability;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\Internals\ReliabilityTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\PropertyValueCollectionBase;
use Bitrix\Sale\Shipment;

Loader::registerAutoLoadClasses(
	'sale',
	array(
		__NAMESPACE__.'\Reliability' => 'handlers/delivery/additional/ruspost/reliability/reliability.php',
		__NAMESPACE__.'\ReliabilityCollection' => 'handlers/delivery/additional/ruspost/reliability/reliabilitycollection.php',
		__NAMESPACE__.'\Requester' => 'handlers/delivery/additional/ruspost/reliability/requester.php'
	)
);

/**
 * Class Service
 * @package Sale\Handlers\Delivery\Additional\RusPost\Reliability
 */
class Service
{
	const UNKNOWN = 0;
	const RELIABLE = 10;
	const FRAUD = 20;

	/**
	 * @param int $deliveryId
	 * @param ReliabilityCollection $collection
	 * @return ReliabilityCollection
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getReliabilityCollection(int $deliveryId, ReliabilityCollection $collection)
	{
		if($deliveryId <= 0)
		{
			throw new ArgumentNullException('deliveryId');
		}

		if($collection->count() <= 0)
		{
			return $collection;
		}

		$notFoundInDb = $collection->getHashList();

		/** @var ReliabilityCollection $stored */
		$stored = self::getTableClass()::query()
			->addFilter('HASH', $collection->getHashList())
			->whereNotNull("RELIABILITY")
			->addSelect('*')
			->fetchCollection();

		if($stored)
		{
			$notFoundInDb = array_diff($collection->getHashList(), $stored->getHashList());

			if (empty($notFoundInDb))
			{
				return $stored;
			}
		}

		$requested = self::requestReliability(
			$deliveryId,
			$collection->filterByHashes($notFoundInDb)
		);

		$result = new ReliabilityCollection();
		$result->setItems($collection->getAll());

		if($stored)
		{
			$result->setItems($stored->getAll());
		}

		if($requested)
		{
			$result->setItems($requested->getAll());
		}

		$result->saveItems();
		return $result;
	}

	/**
	 * @param int $deliveryId
	 * @param string $fullName
	 * @param string $address
	 * @param string $phone
	 * @return int
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getReliabilityValue(int $deliveryId, string $fullName, string $address, string $phone)
	{
		$collection = new ReliabilityCollection();
		$collection->add(Reliability::create($fullName, $address, $phone));
		$hash = self::createHash($fullName, $address, $phone);

		$resultCollection = self::getReliabilityCollection(
			$deliveryId,
			$collection
		);

		if($reliability = $resultCollection->getByPrimary($hash))
		{
			return $reliability->getReliability();
		}

		return self::UNKNOWN;
	}

	/**
	 * @param int $deliveryId
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function prepareData(int $deliveryId, int $attempt = 0)
	{
		if($deliveryId <= 0)
		{
			return '';
		}

		/** @var ReliabilityCollection $collection */
		$collection = self::getTableClass()::query()
			->whereNull('RELIABILITY')
			->addSelect('*')
			->fetchCollection();

		if($collection->count() <= 0)
		{
			return '';
		}

		/** @var ReliabilityCollection $requestedCollection */
		if($requestedCollection = self::requestReliability($deliveryId, $collection))
		{
			$requestedCollection->saveItems();
			return '';
		}

		if($attempt > 0)
		{
			return self::createAgentName($deliveryId, --$attempt);
		}

		return '';
	}

	/**
	 * @param Shipment $shipment
	 * @return PropertyValueCollectionBase|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	private static function extractProperties(Shipment $shipment)
	{
		if (!($order = $shipment->getOrder()))
		{
			return null;
		}

		if (!($userId = (int)$order->getUserId()) || $userId === (int)\CSaleUser::GetAnonymousUserID())
		{
			return null;
		}

		if(!$props = $order->getPropertyCollection())
		{
			return null;
		}

		return $props;
	}

	/**
	 * @param PropertyValueCollectionBase $props
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private static function extractDataFromProps(PropertyValueCollectionBase $props)
	{
		$addressValue = '';
		$fullNameValue = '';
		$phoneValue = '';

		if($address = $props->getAddress())
		{
			if($address->getValue() <> '')
			{
				$addressValue = (string)$address->getValue();
			}
		}

		if($payerName = $props->getPayerName())
		{
			if($payerName->getValue() <> '' )
			{
				$fullNameValue = (string)$payerName->getValue();
			}
		}

		if($phone = $props->getPhone())
		{
			if($phone->getValue() <> '' )
			{
				$phoneValue = (string)$phone->getValue();
			}
		}

		if($addressValue == '' && $fullNameValue == '' && $phoneValue == '')
		{
			return null;
		}

		return[$fullNameValue, $addressValue, $phoneValue];
	}

	/**
	 * @param $expectedDeliveryId
	 * @param Event $event
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function onShipmentSave($expectedDeliveryId, Event $event)
	{
		/** @var Shipment $shipment */
		if (!($shipment = $event->getParameter('ENTITY')))
		{
			return;
		}

		/** @var Delivery\Services\Base */
		if (!($delivery = $shipment->getDelivery()))
		{
			return;
		}

		if(!self::isDeliverySuitable($delivery, $expectedDeliveryId))
		{
			return;
		}

		if(!$props = self::extractProperties($shipment))
		{
			return;
		}

		if(!($data = self::extractDataFromProps($props)))
		{
			return;
		}

		list($fullName, $address, $phone) = $data;
		$hash = self::createHash($fullName, $address, $phone);

		if(!($reliability = self::getTableClass()::getByPrimary($hash)->fetchObject()))
		{
			$reliability = Reliability::create($fullName, $address, $phone);
			$reliability->save();
		}

		self::addAgent($delivery->getId(), 3);
	}

	/**
	 * @param $expectedDeliveryId
	 * @param Event $event
	 * @return \Bitrix\Main\EventResult
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function onSaleAdminOrderInfoBlockShow($expectedDeliveryId, Event $event)
	{
		/** @var Order $order */
		$order = $event->getParameter("ORDER");

		$findSuitable = false;

		/** @var Shipment $shipment */
		foreach ($order->getShipmentCollection() as $shipment)
		{
			if($shipment->isSystem())
			{
				continue;
			}

			if(!($delivery = $shipment->getDelivery()))
			{
				continue;
			}

			if (self::isDeliverySuitable($delivery, $expectedDeliveryId))
			{
				$findSuitable = true;
				break;
			}
		}

		$result = new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);

		if(!$findSuitable)
		{
			return $result;
		}

		if(!($props = $order->getPropertyCollection()))
		{
			return $result;
		}

		if(!($data = self::extractDataFromProps($props)))
		{
			return $result;
		}

		list($fullName, $address, $phone) = $data;
		$reliability = self::getReliabilityValue($delivery->getId(), $fullName, $address, $phone);

		global $APPLICATION;

		ob_start();

		$APPLICATION->IncludeComponent(
			'bitrix:sale.delivery.ruspost.reliability',
			'.default',
			['RELIABILITY' => $reliability]
		);

		$value = ob_get_contents();
		ob_end_clean();

		return new \Bitrix\Main\EventResult(
			\Bitrix\Main\EventResult::SUCCESS,
			[
				[
					'TITLE' => Loc::getMessage('SALE_DLVRS_RELIABILITY_TITLE'),
					'VALUE' => $value
				]
			],
			'sale'
		);
	}


	/**
	 * @param int $deliveryId
	 * @param ReliabilityCollection $collection
	 * @return bool|ReliabilityCollection
	 * @throws ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function requestReliability(int $deliveryId, ReliabilityCollection $collection)
	{
		if($deliveryId <= 0)
		{
			return $collection;
		}

		if($collection->count() <= 0)
		{
			return $collection;
		}

		$delivery = Delivery\Services\Manager::getObjectById($deliveryId);

		if(!self::isDeliverySuitable($delivery))
		{
			return $collection;
		}

		/** @var \Sale\Handlers\Delivery\Additional\DeliveryRequests\RusPost\Handler $deliveryRequest */
		if(!($deliveryRequest = $delivery->getDeliveryRequestHandler()))
		{
			return $collection;
		}

		$deliveryRequest->setHttpClient(self::createHttpClient());

		return (new Requester($deliveryRequest))
			->request($collection);
	}

	/**
	 * @param Delivery\Services\Base $delivery
	 * @return bool
	 */
	private static function isDeliverySuitable(Delivery\Services\Base $delivery, $expectedDeliveryId = 0)
	{
		if (get_class($delivery) !== 'Sale\Handlers\Delivery\AdditionalProfile' || $delivery->getId() <= 0)
		{
			return false;
		}

		/** @var \Sale\Handlers\Delivery\AdditionalProfile $delivery */

		if ($delivery->getParentService()->getServiceType() !== 'RUSPOST')
		{
			return false;
		}

		if($expectedDeliveryId > 0)
		{
			if(!($deliveryRequest = $delivery->getDeliveryRequestHandler()))
			{
				return false;
			}

			if($deliveryRequest->getHandlingDeliveryServiceId() != $expectedDeliveryId)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param string $address
	 * @param string $fullName
	 * @param string $phone
	 * @return string
	 */
	public static function createHash(string $fullName, string $address, string $phone)
	{
		return md5(trim($fullName).trim($address).trim($phone));
	}

	/**
	 * @param int $deliveryId
	 */
	public static function install(int $deliveryId)
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('sale', 'OnSaleShipmentEntitySaved' , 'sale', '\Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service', 'onShipmentSave', 100, "", [$deliveryId]);
		$eventManager->registerEventHandler('sale', 'onSaleAdminOrderInfoBlockShow' , 'sale', '\Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service', 'onSaleAdminOrderInfoBlockShow', 100, "", [$deliveryId]);
	}

	/**
	 * @param int $deliveryId
	 */
	public static function unInstall(int $deliveryId)
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('sale', 'OnSaleShipmentEntitySaved' , 'sale', '\Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service', 'onShipmentSave', "",  [$deliveryId]);
		$eventManager->unRegisterEventHandler('sale', 'onSaleAdminOrderInfoBlockShow' , 'sale', '\Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service', 'onSaleAdminOrderInfoBlockShow', "",  [$deliveryId]);
	}

	/**
	 * @return HttpClient
	 */
	private static function createHttpClient()
	{
		return new HttpClient(array(
			"version" => "1.1",
			"socketTimeout" => 5,
			"streamTimeout" => 5,
			"redirect" => true,
			"redirectMax" => 3,
		));
	}

	/**
	 * @param int $deliveryId
	 */
	private static function addAgent(int $deliveryId, int $attempts)
	{
		if($attempts <= 0)
		{
			return;
		}

		\CAgent::AddAgent(self::createAgentName($deliveryId, $attempts), "sale", "N", 60, "", "Y");
	}

	/**
	 * @param int $deliveryId
	 * @param int $attempts
	 * @return string
	 */
	private static function createAgentName(int $deliveryId, int $attempts)
	{
		return '\Sale\Handlers\Delivery\Additional\RusPost\Reliability\Service::prepareData('.$deliveryId.', '.$attempts.');';
	}

	/**
	 * @return ReliabilityTable
	 */
	private static function getTableClass()
	{
		return ReliabilityTable::class;
	}
}