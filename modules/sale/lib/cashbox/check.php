<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Sale\Cashbox\Internals\Check2CashboxTable;
use Bitrix\Sale\Cashbox\Internals\CheckRelatedEntitiesTable;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentCollection;
use Bitrix\Sale\ShipmentItem;
use Bitrix\Sale\ShipmentItemStore;

/**
 * Class Check
 * @package Bitrix\Sale\Cashbox
 */
abstract class Check
{
	const EVENT_ON_CHECK_PREPARE_DATA = 'OnSaleCheckPrepareData';

	const PARAM_FISCAL_DOC_NUMBER = 'fiscal_doc_number';
	const PARAM_FISCAL_DOC_ATTR = 'fiscal_doc_attribute';
	const PARAM_FISCAL_RECEIPT_NUMBER = 'fiscal_receipt_number';
	const PARAM_FN_NUMBER = 'fn_number';
	const PARAM_SHIFT_NUMBER = 'shift_number';
	const PARAM_REG_NUMBER_KKT = 'reg_number_kkt';
	const PARAM_DOC_TIME = 'doc_time';
	const PARAM_DOC_SUM = 'doc_sum';
	const PARAM_CALCULATION_ATTR = 'calculation_attribute';

	const CALCULATED_SIGN_INCOME = 'income';
	const CALCULATED_SIGN_CONSUMPTION = 'consumption';

	const SHIPMENT_TYPE_NONE = '';
	const PAYMENT_TYPE_CASH = 'cash';
	const PAYMENT_TYPE_ADVANCE = 'advance';
	const PAYMENT_TYPE_CASHLESS = 'cashless';
	const PAYMENT_TYPE_CREDIT = 'credit';

	const PAYMENT_OBJECT_COMMODITY = 'commodity';
	const PAYMENT_OBJECT_EXCISE = 'excise';
	const PAYMENT_OBJECT_JOB = 'job';
	const PAYMENT_OBJECT_SERVICE = 'service';
	const PAYMENT_OBJECT_PAYMENT = 'payment';

	const SUPPORTED_ENTITY_TYPE_PAYMENT = 'payment';
	const SUPPORTED_ENTITY_TYPE_SHIPMENT = 'shipment';
	const SUPPORTED_ENTITY_TYPE_ALL = 'all';
	const SUPPORTED_ENTITY_TYPE_NONE = 'none';

	/** @var array $fields */
	private $fields = array();

	/** @var array $cashboxList */
	private $cashboxList = array();

	/** @var CollectableEntity[] $entities */
	private $entities = array();

	/** @var array $relatedEntities */
	private $relatedEntities = array();

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		throw new Main\NotImplementedException();
	}
	
	/**
	 * @param string $handler
	 * @return null|Check
	 */
	public static function create($handler)
	{
		if (class_exists($handler))
			return new $handler();

		return null;
	}

	/**
	 * Check constructor.
	 */
	private function __construct() {}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getField($name)
	{
		return $this->fields[$name];
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setField($name, $value)
	{
		$this->fields[$name] = $value;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		if (!$this->getField('LINK_PARAMS'))
		{
			return '';
		}

		$cashbox = Manager::getObjectById($this->getField('CASHBOX_ID'));
		if (!$cashbox)
		{
			return '';
		}

		$ofd = $cashbox->getOfd();
		if (!$ofd)
		{
			return '';
		}

		return $ofd->generateCheckLink($this->getField('LINK_PARAMS'));
	}

	/**
	 * @param array $cashboxList
	 */
	public function setAvailableCashbox(array $cashboxList)
	{
		$this->cashboxList = $cashboxList;
	}

	/**
	 * @param array $entities
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectNotFoundException
	 */
	public function setEntities(array $entities)
	{
		$this->entities = $entities;

		$orderId = null;
		$entityRegistryType = null;

		foreach ($this->entities as $entity)
		{
			if ($entity instanceof Payment)
			{
				$this->fields['PAYMENT_ID'] = $entity->getId();
				$this->fields['SUM'] = $entity->getSum();
				$this->fields['CURRENCY'] = $entity->getField('CURRENCY');
			}

			// compatibility
			if ($entity instanceof Shipment)
			{
				$this->fields['SHIPMENT_ID'] = $entity->getId();
			}

			if ($entityRegistryType === null)
			{
				$entityRegistryType = $entity::getRegistryType();
			}
			elseif ($entityRegistryType !== $entity::getRegistryType())
			{
				throw new Main\ArgumentTypeException('entities');
			}

			/** @var PaymentCollection|ShipmentCollection $collection */
			$collection = $entity->getCollection();

			if ($orderId === null)
			{
				$orderId = $collection->getOrder()->getId();
			}
			elseif ($orderId != $collection->getOrder()->getId())
			{
				throw new Main\ArgumentTypeException('entities');
			}
		}

		$this->fields['ORDER_ID'] = $orderId;
		$this->fields['ENTITY_REGISTRY_TYPE'] = $entityRegistryType;
	}

	/**
	 * @param array $entities
	 * @throws Main\NotSupportedException
	 */
	public function setRelatedEntities(array $entities)
	{
		$this->checkRelatedEntities($entities);

		$this->relatedEntities = $entities;

		foreach ($this->relatedEntities as $type => $entityList)
		{
			foreach ($entityList as $entity)
			{
				if ($entity instanceof Payment)
				{
					$this->fields['SUM'] += $entity->getSum();
					$this->fields['CURRENCY'] = $entity->getField('CURRENCY');
				}
			}
		}
	}

	/**
	 * @param $entities
	 * @throws Main\NotImplementedException
	 * @throws Main\NotSupportedException
	 */
	protected function checkRelatedEntities($entities)
	{
		foreach ($entities as $type => $entityList)
		{
			foreach ($entityList as $entity)
			{
				if (static::getSupportedRelatedEntityType() === self::SUPPORTED_ENTITY_TYPE_NONE)
				{
					throw new Main\NotSupportedException(static::getType().' is not supported any related entities');
				}

				if (static::getSupportedRelatedEntityType() === self::SUPPORTED_ENTITY_TYPE_PAYMENT
					&& !($entity instanceof Payment)
				)
				{
					throw new Main\NotSupportedException(static::getType().' is not supported payment as related entity');
				}

				if (static::getSupportedRelatedEntityType() === self::SUPPORTED_ENTITY_TYPE_SHIPMENT
					&& !($entity instanceof Shipment)
				)
				{
					throw new Main\NotSupportedException(static::getType().' is not supported shipment as related entity');
				}
			}
		}
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getRelatedEntities()
	{
		if ($this->relatedEntities)
		{
			return $this->relatedEntities;
		}

		$registry = Registry::getInstance($this->fields['ENTITY_REGISTRY_TYPE']);
		$order = null;

		$dbRes = CheckRelatedEntitiesTable::getList(array('filter' => array('CHECK_ID' => $this->getField('ID'))));
		while ($entity = $dbRes->fetch())
		{
			if ($order === null)
			{
				if ($entity['ENTITY_TYPE'] === CheckRelatedEntitiesTable::ENTITY_TYPE_PAYMENT)
				{
					/** @var Payment $paymentClassName */
					$paymentClassName = $registry->getPaymentClassName();
					$dbResPayment = $paymentClassName::getList(array(
						'select' => array('ORDER_ID'),
						'filter' => array('ID' => $entity['ENTITY_ID'])
					));
					if ($data = $dbResPayment->fetch())
					{
						/** @var Order $orderClass */
						$orderClass = $registry->getOrderClassName();
						$order = $orderClass::load($data['ORDER_ID']);
					}
				}
				elseif ($entity['ENTITY_TYPE'] === CheckRelatedEntitiesTable::ENTITY_TYPE_SHIPMENT)
				{
					/** @var Shipment $shipmentClassName */
					$shipmentClassName = $registry->getShipmentClassName();
					$dbResShipment = $shipmentClassName::getList(array(
						'select' => array('ORDER_ID'),
						'filter' => array('ID' => $entity['ENTITY_ID'])
					));
					if ($data = $dbResShipment->fetch())
					{
						/** @var Order $orderClass */
						$orderClass = $registry->getOrderClassName();
						$order = $orderClass::load($data['ORDER_ID']);
					}
				}

				if ($order === null)
				{
					continue;
				}
			}

			if ($entity['ENTITY_TYPE'] === CheckRelatedEntitiesTable::ENTITY_TYPE_PAYMENT)
			{
				$paymentCollection = $order->getPaymentCollection();
				$this->relatedEntities[$entity['ENTITY_CHECK_TYPE']][] = $paymentCollection->getItemById($entity['ENTITY_ID']);
			}
			elseif ($entity['ENTITY_TYPE'] === CheckRelatedEntitiesTable::ENTITY_TYPE_SHIPMENT)
			{
				$shipmentCollection = $order->getShipmentCollection();
				$this->relatedEntities[$entity['ENTITY_CHECK_TYPE']][] = $shipmentCollection->getItemById($entity['ENTITY_ID']);
			}
		}

		return $this->relatedEntities;
	}

	/**
	 * @return array|CollectableEntity[]
	 * @throws Main\SystemException
	 */
	public function getEntities()
	{
		if ($this->entities)
			return $this->entities;

		$registry = Registry::getInstance($this->fields['ENTITY_REGISTRY_TYPE']);

		if ($this->fields['ORDER_ID'] > 0)
		{
			$orderId = $this->fields['ORDER_ID'];
		}
		elseif ($this->fields['PAYMENT_ID'] > 0)
		{
			/** @var Payment $paymentClassName */
			$paymentClassName = $registry->getPaymentClassName();
			$dbRes = $paymentClassName::getList(array('filter' => array('ID' => $this->fields['PAYMENT_ID'])));
			$data = $dbRes->fetch();
			$orderId = $data['ORDER_ID'];
		}
		elseif ($this->fields['SHIPMENT_ID'] > 0)
		{
			/** @var Shipment $shipmentClassName */
			$shipmentClassName = $registry->getShipmentClassName();
			$dbRes = $shipmentClassName::getList(array('filter' => array('ID' => $this->fields['SHIPMENT_ID'])));
			$data = $dbRes->fetch();
			$orderId = $data['ORDER_ID'];
		}
		else
		{
			throw new Main\SystemException();
		}

		if ($orderId > 0)
		{
			$orderClassName = $registry->getOrderClassName();
			/** @var Order $order */
			$order = $orderClassName::load($orderId);
			if ($order)
			{
				if ($this->fields['PAYMENT_ID'] > 0)
				{
					$paymentCollection = $order->getPaymentCollection();
					if ($paymentCollection)
					{
						$payment = $paymentCollection->getItemById($this->fields['PAYMENT_ID']);
						if ($payment)
							$this->entities[] = $payment;
					}
				}

				if ($this->fields['SHIPMENT_ID'] > 0)
				{
					$shipmentCollection = $order->getShipmentCollection();
					if ($shipmentCollection)
					{
						$shipment = $shipmentCollection->getItemById($this->fields['SHIPMENT_ID']);
						if ($shipment)
							$this->entities[] = $shipment;
					}
				}
			}
		}

		return $this->entities;
	}

	/**
	 * @return Main\Entity\AddResult|Main\Entity\UpdateResult
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws \Exception
	 */
	public function save()
	{
		if ((int)$this->fields['ID'] > 0)
		{
			return CashboxCheckTable::update($this->fields['ID'], $this->fields);
		}

		$this->fields['TYPE'] = static::getType();
		$this->fields['DATE_CREATE'] = new Main\Type\DateTime();

		$result = CashboxCheckTable::add($this->fields);
		$checkId = $result->getId();
		$this->fields['ID'] = $checkId;
		foreach ($this->cashboxList as $cashbox)
		{
			Check2CashboxTable::add(array('CHECK_ID' => $checkId, 'CASHBOX_ID' => $cashbox['ID']));
		}

		foreach ($this->relatedEntities as $checkType => $entities)
		{
			foreach ($entities as $entity)
			{
				if ($entity instanceof Payment)
					$entityType = CheckRelatedEntitiesTable::ENTITY_TYPE_PAYMENT;
				else
					$entityType = CheckRelatedEntitiesTable::ENTITY_TYPE_SHIPMENT;

				CheckRelatedEntitiesTable::add(array(
					'CHECK_ID' => $checkId,
					'ENTITY_ID' => $entity->getId(),
					'ENTITY_TYPE' => $entityType,
					'ENTITY_CHECK_TYPE' => $checkType,
				));
			}
		}

		return $result;
	}

	/**
	 * @param $cashboxId
	 */
	public function linkCashbox($cashboxId)
	{
		$this->fields['CASHBOX_ID'] = $cashboxId;
	}

	/**
	 * @param $settings
	 */
	public function init($settings)
	{
		$this->fields = $settings;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getDataForCheck()
	{
		$result = array(
			'type' => static::getType(),
			'unique_id' => $this->getField('ID'),
			'items' => array(),
			'date_create' => new Main\Type\DateTime()
		);

		$data = $this->extractData();

		if ($data)
		{
			if (isset($data['ORDER']))
			{
				$result['order'] = $data['ORDER'];
			}

			foreach ($data['PAYMENTS'] as $payment)
			{
				$item = [
					'entity' => $payment['ENTITY'],
					'type' => $payment['TYPE'],
					'is_cash' => $payment['IS_CASH'],
					'sum' => $payment['SUM']
				];

				if (isset($payment['ADDITIONAL_PARAMS']))
				{
					$item['additional_params'] = $payment['ADDITIONAL_PARAMS'];
				}

				$result['payments'][] = $item;
			}

			if (isset($data['PRODUCTS']))
			{
				foreach ($data['PRODUCTS'] as $product)
				{
					$item = [
						'entity' => $product['ENTITY'],
						'name' => $product['NAME'],
						'base_price' => $product['BASE_PRICE'],
						'price' => $product['PRICE'],
						'sum' => $product['SUM'],
						'quantity' => $product['QUANTITY'],
						'vat' => $product['VAT'],
						'payment_object' => $product['PAYMENT_OBJECT'],
					];

					if (isset($product['NOMENCLATURE_CODE']))
					{
						$item['nomenclature_code'] = $product['NOMENCLATURE_CODE'];
					}

					if ($product['DISCOUNT'])
					{
						$item['discount'] = [
							'discount' => $product['DISCOUNT']['PRICE'],
							'discount_type' => $product['DISCOUNT']['TYPE'],
						];
					}

					if (isset($product['ADDITIONAL_PARAMS']))
					{
						$item['additional_params'] = $product['ADDITIONAL_PARAMS'];
					}

					$result['items'][] = $item;
				}
			}

			if (isset($data['DELIVERY']))
			{
				foreach ($data['DELIVERY'] as $delivery)
				{
					$item = [
						'entity' => $delivery['ENTITY'],
						'name' => $delivery['NAME'],
						'base_price' => $delivery['BASE_PRICE'],
						'price' => $delivery['PRICE'],
						'sum' => $delivery['SUM'],
						'quantity' => $delivery['QUANTITY'],
						'vat' => $delivery['VAT'],
						'payment_object' => $delivery['PAYMENT_OBJECT'],
					];

					if ($delivery['DISCOUNT'])
					{
						$item['discount'] = [
							'discount' => $delivery['DISCOUNT']['PRICE'],
							'discount_type' => $delivery['DISCOUNT']['TYPE'],
						];
					}

					if (isset($delivery['ADDITIONAL_PARAMS']))
					{
						$item['additional_params'] = $delivery['ADDITIONAL_PARAMS'];
					}

					$result['items'][] = $item;
				}
			}

			if (isset($data['BUYER']))
			{
				if (isset($data['BUYER']['EMAIL']))
					$result['client_email'] = $data['BUYER']['EMAIL'];

				if (isset($data['BUYER']['PHONE']))
					$result['client_phone'] = $data['BUYER']['PHONE'];
			}

			if (isset($data['ADDITIONAL_PARAMS']))
			{
				$result['additional_params'] = $data['ADDITIONAL_PARAMS'];
			}

			$result['total_sum'] = $data['TOTAL_SUM'];
		}

		return $result;
	}

	/**
	 * @param array $entities
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function extractDataFromEntitiesInternal(array $entities)
	{
		$result = array();

		$order = null;
		$discounts = null;
		$shopPrices = null;
		$totalSum = 0;

		foreach ($entities as $entity)
		{
			if ($order === null)
			{
				$order = CheckManager::getOrder($entity);
				$discounts = $order->getDiscount();
				$shopPrices = $discounts->getShowPrices();
				$result['ORDER'] = $order;
			}

			if ($entity instanceof Payment)
			{
				$service = $entity->getPaySystem();
				$type = $service->getField('IS_CASH') === 'Y' ? static::PAYMENT_TYPE_CASH : static::PAYMENT_TYPE_CASHLESS;

				$result['PAYMENTS'][] = array(
					'ENTITY' => $entity,
					'IS_CASH' => $service->getField('IS_CASH'),
					'TYPE' => $type,
					'SUM' => $entity->getSum()
				);

				$totalSum += $entity->getSum();
			}
			elseif ($entity instanceof Shipment)
			{
				$shipmentItemCollection = $entity->getShipmentItemCollection();
				$sellableItems = $shipmentItemCollection->getSellableItems();

				/** @var ShipmentItem $shipmentItem */
				foreach ($sellableItems as $shipmentItem)
				{
					$basketItem = $shipmentItem->getBasketItem();
					$basketCode = $basketItem->getBasketCode();
					if (!empty($shopPrices['BASKET'][$basketCode]))
					{
						$basketItem->setFieldNoDemand('BASE_PRICE', $shopPrices['BASKET'][$basketCode]['SHOW_BASE_PRICE']);
						$basketItem->setFieldNoDemand('PRICE', $shopPrices['BASKET'][$basketCode]['SHOW_PRICE']);
						$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $shopPrices['BASKET'][$basketCode]['SHOW_DISCOUNT']);
					}
					unset($basketCode);

					$item = array(
						'ENTITY' => $basketItem,
						'PRODUCT_ID' => $basketItem->getProductId(),
						'NAME' => $basketItem->getField('NAME'),
						'BASE_PRICE' => $basketItem->getBasePriceWithVat(),
						'PRICE' => $basketItem->getPriceWithVat(),
						'SUM' => PriceMaths::roundPrecision($basketItem->getPriceWithVat() * $shipmentItem->getQuantity()),
						'QUANTITY' => (float)$shipmentItem->getQuantity(),
						'VAT' => $this->getProductVatId($basketItem),
						'PAYMENT_OBJECT' => static::PAYMENT_OBJECT_COMMODITY
					);

					if ($basketItem->isCustomPrice())
					{
						$item['BASE_PRICE'] = $basketItem->getPriceWithVat();
					}
					else
					{
						if ((float)$basketItem->getDiscountPrice() != 0)
						{
							$item['DISCOUNT'] = array(
								'PRICE' => (float)$basketItem->getDiscountPrice(),
								'TYPE' => 'C',
							);
						}
					}

					if ($basketItem->isSupportedMarkingCode())
					{
						$item['QUANTITY'] = 1;
						$item['SUM'] = $basketItem->getPriceWithVat();

						$collection = $shipmentItem->getShipmentItemStoreCollection();
						foreach ($collection as $itemStore)
						{
							$item['NOMENCLATURE_CODE'] = $this->buildTag1162(
								$itemStore->getMarkingCode(),
								$basketItem->getMarkingCodeGroup()
							);

							$result['PRODUCTS'][] = $item;
						}

						$diff = $shipmentItem->getQuantity() - $collection->count();
						if ($diff)
						{
							for ($i = 0; $i < $diff; $i++)
							{
								$item['NOMENCLATURE_CODE'] = '';

								$result['PRODUCTS'][] = $item;
							}
						}
					}
					else
					{
						$result['PRODUCTS'][] = $item;
					}
				}

				$priceDelivery = (float)$entity->getPrice();
				if ($priceDelivery > 0)
				{
					$item = array(
						'ENTITY' => $entity,
						'NAME' => Main\Localization\Loc::getMessage('SALE_CASHBOX_CHECK_DELIVERY'),
						'BASE_PRICE' => (float)$entity->getField('BASE_PRICE_DELIVERY'),
						'PRICE' => (float)$entity->getPrice(),
						'SUM' => (float)$entity->getPrice(),
						'QUANTITY' => 1,
						'VAT' => $this->getDeliveryVatId($entity),
						'PAYMENT_OBJECT' => static::PAYMENT_OBJECT_SERVICE
					);

					if ($entity->isCustomPrice())
					{
						$item['BASE_PRICE'] = $entity->getPrice();
					}
					else
					{
						if ((float)$entity->getField('DISCOUNT_PRICE') != 0)
						{
							$item['DISCOUNT'] = array(
								'PRICE' => $entity->getField('DISCOUNT_PRICE'),
								'TYPE' => 'C',
							);
						}
					}

					$result['DELIVERY'][] = $item;
				}
			}
		}

		if ($order !== null)
		{
			$result['BUYER'] = array();

			$properties = $order->getPropertyCollection();
			$email = $properties->getUserEmail();
			if ($email && $email->getValue())
			{
				$result['BUYER']['EMAIL'] = $email->getValue();
			}

			$phone = $properties->getPhone();
			if ($phone && $phone->getValue())
			{
				$result['BUYER']['PHONE'] = $phone->getValue();
			}

			if (!$result['BUYER'])
			{
				$result['BUYER']['EMAIL'] = Main\Config\Option::get("main", "email_from", "admin@".$_SERVER['SERVER_NAME']);
			}
		}

		$result['TOTAL_SUM'] = $totalSum;

		unset($shopPrices, $discounts);

		return $result;
	}

	/**
	 * @param string $markingCode
	 * @param string $markingCodeGroup
	 * @return string
	 */
	protected function buildTag1162(string $markingCode, string $markingCodeGroup) : string
	{
		[$gtin, $serial, ] = $this->parseMarkingCode($markingCode);

		$hex =
			$this->convertToBinaryFormat($markingCodeGroup, 2).
			$this->convertToBinaryFormat($gtin, 6).
			$this->convertCharsToHex($serial)
		;

		return hex2bin($hex);
	}

	/**
	 * @param $code
	 * @return array
	 */
	private function parseMarkingCode(string $code) : array
	{
		$gtin = mb_substr($code, 2, 14);
		$serial = mb_substr($code, 18, 13);
		$reserve = mb_substr($code, 27);

		return [$gtin, $serial, $reserve];
	}

	/**
	 * @param $string
	 * @param $size
	 * @return string
	 */
	protected function convertToBinaryFormat($string, $size) : string
	{
		$result = '';

		for ($i = 0; $i < $size; $i++)
		{
			$hex = dechex(($string >> (8 * $i)) & 0xFF);
			if (mb_strlen($hex) === 1)
			{
				$hex = '0'.$hex;
			}

			$result = ToUpper($hex).$result;
		}

		return $result;
	}

	/**
	 * @param $string
	 * @return string
	 */
	protected function convertCharsToHex($string) : string
	{
		$result = '';

		for ($i = 0, $len = mb_strlen($string); $i < $len; $i++)
		{
			$hex = dechex(ord($string[$i]));
			if (mb_strlen($hex) === 1)
			{
				$hex = '0'.$hex;
			}

			$result .= ToUpper($hex);
		}

		return $result;
	}

	/**
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function extractData()
	{
		$result = $this->extractDataInternal();

		$event = new Main\Event('sale', static::EVENT_ON_CHECK_PREPARE_DATA, array($result, static::getType()));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() !== Main\EventResult::ERROR)
				{
					$result = $eventResult->getParameters();
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function extractDataInternal()
	{
		$entities = $this->getEntities();
		$result = $this->extractDataFromEntitiesInternal($entities);

		$relatedEntities = $this->getRelatedEntities();
		if ($relatedEntities)
		{
			foreach ($this->relatedEntities as $type => $relatedEntities)
			{
				$data = $this->extractDataFromEntitiesInternal($relatedEntities);

				if (isset($data['PAYMENTS']))
				{
					foreach ($data['PAYMENTS'] as $item)
					{
						$item['TYPE'] = $type;
						$result['PAYMENTS'][] = $item;
					}
				}

				if (isset($data['PRODUCTS']))
				{
					foreach ($data['PRODUCTS'] as $item)
						$result['PRODUCTS'][] = $item;
				}

				if (isset($data['DELIVERY']))
				{
					foreach ($data['DELIVERY'] as $item)
						$result['DELIVERY'][] = $item;
				}

				$result['TOTAL_SUM'] += $data['TOTAL_SUM'];
			}
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return int
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\SystemException
	 */
	protected function getDeliveryVatId(Shipment $shipment)
	{
		$calcDeliveryTax = Main\Config\Option::get("sale", "COUNT_DELIVERY_TAX", "N");
		if ($calcDeliveryTax === 'Y')
		{
			$service = $shipment->getDelivery();
			return $service->getVatId();
		}

		return 0;
	}

	/**
	 * @param BasketItem $basketItem
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getProductVatId(BasketItem $basketItem)
	{
		static $vatList = array();

		if (!isset($vatList[$basketItem->getProductId()]))
		{
			$vatId = $this->getVatIdByProductId($basketItem->getProductId());
			if ($vatId === 0)
			{
				$vatRate = (int)($basketItem->getVatRate() * 100);
				if ($vatRate > 0)
				{
					$vatId = $this->getVatIdByVatRate($vatRate);
				}
			}

			$vatList[$basketItem->getProductId()] = (int)$vatId;
		}

		return $vatList[$basketItem->getProductId()];
	}

	/**
	 * @param $productId
	 * @return int
	 * @throws Main\LoaderException
	 */
	private function getVatIdByProductId($productId)
	{
		$vatId = 0;
		if (Main\Loader::includeModule('catalog'))
		{
			$dbRes = \CCatalogProduct::GetVATInfo($productId);
			$vat = $dbRes->Fetch();
			if ($vat)
			{
				$vatId = (int)$vat['ID'];
			}
		}

		return $vatId;
	}

	/**
	 * @param $vatRate
	 * @return int|mixed
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getVatIdByVatRate($vatRate)
	{
		static $vatList = array();

		if (!$vatList)
		{
			if (Main\Loader::includeModule('catalog'))
			{
				$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
				while ($data = $dbRes->fetch())
				{
					$vatList[(int)$data['RATE']] = (int)$data['ID'];
				}
			}
		}

		if (!isset($vatList[$vatRate]))
		{
			return 0;
		}

		return $vatList[$vatRate];
	}

	/**
	 * @return Result
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function validate()
	{
		$result = new Result();

		$data = $this->extractData();

		if (!isset($data['PRODUCTS']))
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_CHECK_ERROR_NO_PRODUCTS')));
		}
		else
		{
			$errors = [];

			foreach ($data['PRODUCTS'] as $product)
			{
				if (isset($product['NOMENCLATURE_CODE']) && $product['NOMENCLATURE_CODE'] === '')
				{
					if (isset($errors[$product['PRODUCT_ID']]))
					{
						continue;
					}

					$errors[$product['PRODUCT_ID']] = new Main\Error(
						Main\Localization\Loc::getMessage(
							'SALE_CASHBOX_CHECK_ERROR_NO_NOMENCLATURE_CODE',
							[
								'#PRODUCT_NAME#' => $product['NAME']
							]
						)
					);
				}
			}

			if ($errors)
			{
				$result->addErrors($errors);
			}
		}

		if (!$this->isCorrectSum($data))
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_CHECK_ERROR_CHECK_SUM')));
		}

		if (!isset($data['BUYER']) || !$data['BUYER'])
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_CHECK_ERROR_NO_BUYER_INFO')));
		}

		return $result;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	private function isCorrectSum($data)
	{
		$eps = 0.00001;

		$productSum = 0;
		foreach ($data['PRODUCTS'] as $item)
			$productSum += $item['SUM'];

		if (isset($data['DELIVERY']))
		{
			foreach ($data['DELIVERY'] as $delivery)
				$productSum += $delivery['PRICE'];
		}

		$paymentSum = 0;
		foreach ($data['PAYMENTS'] as $payment)
		{
			$paymentSum += $payment['SUM'];
		}

		return abs($productSum - $paymentSum) < $eps;
	}

	/**
	 * @return string
	 */
	public static function getSupportedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_PAYMENT;
	}

	/**
	 * @return string
	 */
	public static function getSupportedRelatedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_PAYMENT;
	}

	/**
	 * @deprecated use method extractData() instead
	 *
	 * @param array $entities
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function extractDataFromEntities(array $entities)
	{
		$result = $this->extractDataFromEntitiesInternal($entities);

		$event = new Main\Event('sale', static::EVENT_ON_CHECK_PREPARE_DATA, array($result, static::getType()));
		$event->send();

		if ($event->getResults())
		{
			/** @var Main\EventResult $eventResult */
			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() !== Main\EventResult::ERROR)
				{
					$result = $eventResult->getParameters();
				}
			}
		}

		return $result;
	}
}
