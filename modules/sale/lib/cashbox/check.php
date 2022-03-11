<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Cashbox\Internals\CheckRelatedEntitiesTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\PayableBasketItem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Registry;
use Bitrix\Sale\Result;
use Bitrix\Sale\Helpers\Admin;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;

/**
 * Class SaleCheck
 * @package Bitrix\Sale\Cashbox
 */
abstract class Check extends AbstractCheck
{
	public const PAYMENT_OBJECT_COMMODITY = 'commodity';
	public const PAYMENT_OBJECT_EXCISE = 'excise';
	public const PAYMENT_OBJECT_JOB = 'job';
	public const PAYMENT_OBJECT_SERVICE = 'service';
	public const PAYMENT_OBJECT_PAYMENT = 'payment';
	public const PAYMENT_OBJECT_GAMBLING_BET = 'gambling_bet';
	public const PAYMENT_OBJECT_GAMBLING_PRIZE = 'gambling_prize';
	public const PAYMENT_OBJECT_LOTTERY = 'lottery';
	public const PAYMENT_OBJECT_LOTTERY_PRIZE = 'lottery_prize';
	public const PAYMENT_OBJECT_INTELLECTUAL_ACTIVITY = 'intellectual_activity';
	public const PAYMENT_OBJECT_AGENT_COMMISSION = 'agent_commission';
	public const PAYMENT_OBJECT_COMPOSITE = 'composite';
	public const PAYMENT_OBJECT_ANOTHER = 'another';
	public const PAYMENT_OBJECT_PROPERTY_RIGHT = 'property_right';
	public const PAYMENT_OBJECT_NON_OPERATING_GAIN = 'non-operating_gain';
	public const PAYMENT_OBJECT_SALES_TAX = 'sales_tax';
	public const PAYMENT_OBJECT_RESORT_FEE = 'resort_fee';
	public const PAYMENT_OBJECT_DEPOSIT = 'deposit';
	public const PAYMENT_OBJECT_EXPENSE = 'expense';
	public const PAYMENT_OBJECT_PENSION_INSURANCE_IP = 'pension_insurance_ip';
	public const PAYMENT_OBJECT_PENSION_INSURANCE = 'pension_insurance';
	public const PAYMENT_OBJECT_MEDICAL_INSURANCE_IP = 'medical_insurance_ip';
	public const PAYMENT_OBJECT_MEDICAL_INSURANCE = 'medical_insurance';
	public const PAYMENT_OBJECT_SOCIAL_INSURANCE = 'social_insurance';
	public const PAYMENT_OBJECT_CASINO_PAYMENT = 'casino_payment';
	public const PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING_EXCISE = 'commodity_marking_no_marking_excise';
	public const PAYMENT_OBJECT_COMMODITY_MARKING_EXCISE = 'commodity_marking_excise';
	public const PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING = 'commodity_marking_no_marking';
	public const PAYMENT_OBJECT_COMMODITY_MARKING = 'commodity_marking';

	private const MARKING_TYPE_CODE = '444D';

	/** @var array $relatedEntities */
	private $relatedEntities = array();

	/**
	 * @param array $entities
	 * @throws Main\NotImplementedException
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
	 * @return Main\ORM\Data\AddResult|Main\ORM\Data\UpdateResult
	 * @throws \Exception
	 */
	public function save()
	{
		$isNew = (int)$this->fields['ID'] === 0;

		$result = parent::save();
		if (!$result->isSuccess())
		{
			return $result;
		}

		if ($isNew)
		{
			foreach ($this->relatedEntities as $checkType => $entities)
			{
				foreach ($entities as $entity)
				{
					if ($entity instanceof Payment)
					{
						$entityType = CheckRelatedEntitiesTable::ENTITY_TYPE_PAYMENT;
					}
					else
					{
						$entityType = CheckRelatedEntitiesTable::ENTITY_TYPE_SHIPMENT;
					}

					CheckRelatedEntitiesTable::add([
						'CHECK_ID' => $this->fields['ID'],
						'ENTITY_ID' => $entity->getId(),
						'ENTITY_TYPE' => $entityType,
						'ENTITY_CHECK_TYPE' => $checkType,
					]);
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getDataForCheck()
	{
		$result = [
			'type' => static::getType(),
			'calculated_sign' => static::getCalculatedSign(),
			'unique_id' => $this->getField('ID'),
			'items' => [],
			'date_create' => new Main\Type\DateTime()
		];

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
						'measure_code' => $product['MEASURE_CODE'],
						'vat' => $product['VAT'] ?? 0,
						'vat_sum' => $product['VAT_SUM'] ?? 0,
						'payment_object' => $product['PAYMENT_OBJECT'],
						'properties' => $product['PROPERTIES'],
					];

					if (isset($product['NOMENCLATURE_CODE']))
					{
						$item['nomenclature_code'] = $product['NOMENCLATURE_CODE'];
					}

					if (isset($product['MARKING_CODE']))
					{
						$item['marking_code'] = $product['MARKING_CODE'];
					}

					if (isset($product['BARCODE']))
					{
						$item['barcode'] = $product['BARCODE'];
					}

					if ($product['DISCOUNT'])
					{
						$item['discount'] = [
							'discount' => $product['DISCOUNT']['PRICE'],
							'discount_type' => $product['DISCOUNT']['TYPE'],
						];
					}

					if (isset($product['SUPPLIER_INFO']))
					{
						$item['supplier_info'] = [
							'phones' => $product['SUPPLIER_INFO']['PHONES'] ?? [],
							'name' => $product['SUPPLIER_INFO']['NAME'] ?? '',
							'inn' => $product['SUPPLIER_INFO']['INN'] ?? '',
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
						'vat_sum' => $delivery['VAT_SUM'],
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

				if ($this->isShipmentExists())
				{
					continue;
				}

				/** @var PayableBasketItem $payableItem */
				foreach ($entity->getPayableItemCollection()->getBasketItems() as $payableItem)
				{
					/** @var BasketItem $basketItem */
					$basketItem = $payableItem->getEntityObject();

					$item = $this->extractDataFromBasketItem($basketItem);

					$item['SUM'] = PriceMaths::roundPrecision($basketItem->getPriceWithVat() * $payableItem->getQuantity());
					$item['QUANTITY'] = (float)$payableItem->getQuantity();

					$result['PRODUCTS'][] = $item;
				}

				foreach ($entity->getPayableItemCollection()->getShipments() as $payableItem)
				{
					$item = $this->extractDataFromShipment($payableItem->getEntityObject());
					if ($item)
					{
						$item['QUANTITY'] = (float)$payableItem->getQuantity();

						$result['DELIVERY'][] = $item;
					}
				}
			}
			elseif ($entity instanceof Shipment)
			{
				$shipmentItemCollection = $entity->getShipmentItemCollection();
				$sellableItems = $shipmentItemCollection->getSellableItems();

				/** @var ShipmentItem $shipmentItem */
				foreach ($sellableItems as $shipmentItem)
				{
					$basketItem = $shipmentItem->getBasketItem();

					$item = $this->extractDataFromBasketItem($basketItem);

					if ($this->needPrintMarkingCode($basketItem))
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
							$item['BARCODE'] = $itemStore->getBarcode();
							$item['MARKING_CODE'] = $itemStore->getMarkingCode();

							$result['PRODUCTS'][] = $item;
						}

						$diff = $shipmentItem->getQuantity() - $collection->count();
						if ($diff)
						{
							for ($i = 0; $i < $diff; $i++)
							{
								$item['NOMENCLATURE_CODE'] = '';
								$item['MARKING_CODE'] = '';

								$result['PRODUCTS'][] = $item;
							}
						}
					}
					else
					{
						$item['SUM'] = PriceMaths::roundPrecision($basketItem->getPriceWithVat() * $shipmentItem->getQuantity());
						$item['QUANTITY'] = (float)$shipmentItem->getQuantity();

						$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
						if (isset($shipmentItemStoreCollection[0]))
						{
							$item['BARCODE'] = $shipmentItemStoreCollection[0]->getField('BARCODE');
						}

						$result['PRODUCTS'][] = $item;
					}
				}

				$item = $this->extractDataFromShipment($entity);
				if ($item)
				{
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

	protected function needPrintMarkingCode($basketItem) : bool
	{
		return $basketItem->isSupportedMarkingCode();
	}

	private function isShipmentExists(): bool
	{
		foreach ($this->getEntities() as $entity)
		{
			if ($entity instanceof Shipment)
			{
				return true;
			}
		}

		foreach ($this->getRelatedEntities() as $relatedEntities)
		{
			foreach ($relatedEntities as $entity)
			{
				if ($entity instanceof Shipment)
				{
					return true;
				}
			}
		}

		return false;
	}

	private function extractDataFromShipment(Shipment $shipment) : array
	{
		$priceDelivery = (float)$shipment->getPrice();
		if ($priceDelivery > 0)
		{
			$data = [
				'ENTITY' => $shipment,
				'NAME' => Main\Localization\Loc::getMessage('SALE_CASHBOX_CHECK_DELIVERY'),
				'BASE_PRICE' => (float)$shipment->getField('BASE_PRICE_DELIVERY'),
				'PRICE' => (float)$shipment->getPrice(),
				'SUM' => (float)$shipment->getPrice(),
				'QUANTITY' => 1,
				'VAT' => $this->getDeliveryVatId($shipment),
				'PAYMENT_OBJECT' => static::PAYMENT_OBJECT_SERVICE
			];

			if ($shipment->isCustomPrice())
			{
				$data['BASE_PRICE'] = $shipment->getPrice();
			}
			else
			{
				if ((float)$shipment->getField('DISCOUNT_PRICE') != 0)
				{
					$data['DISCOUNT'] = array(
						'PRICE' => $shipment->getField('DISCOUNT_PRICE'),
						'TYPE' => 'C',
					);
				}
			}

			return $data;
		}

		return [];
	}

	private function extractDataFromBasketItem(BasketItem $basketItem) : array
	{
		static $shopPrices = [];

		$order = $basketItem->getBasket()->getOrder();

		if ($order)
		{
			$discounts = $order->getDiscount();
			if (!$shopPrices)
			{
				$shopPrices = $discounts->getShowPrices();
			}
		}

		$basketCode = $basketItem->getBasketCode();
		if (!empty($shopPrices['BASKET'][$basketCode]))
		{
			$basketItem->setFieldNoDemand('BASE_PRICE', $shopPrices['BASKET'][$basketCode]['SHOW_BASE_PRICE']);
			$basketItem->setFieldNoDemand('PRICE', $shopPrices['BASKET'][$basketCode]['SHOW_PRICE']);
			$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $shopPrices['BASKET'][$basketCode]['SHOW_DISCOUNT']);
		}
		unset($basketCode);

		$data = [
			'ENTITY' => $basketItem,
			'PRODUCT_ID' => $basketItem->getProductId(),
			'NAME' => $basketItem->getField('NAME'),
			'BASE_PRICE' => $basketItem->getBasePriceWithVat(),
			'PRICE' => $basketItem->getPriceWithVat(),
			'SUM' => $basketItem->getFinalPrice(),
			'QUANTITY' => (float)$basketItem->getQuantity(),
			'MEASURE_CODE' => $basketItem->getField('MEASURE_CODE'),
			'VAT' => $this->getProductVatId($basketItem),
			'PAYMENT_OBJECT' => $this->needPrintMarkingCode($basketItem)
				? static::PAYMENT_OBJECT_COMMODITY_MARKING
				: static::PAYMENT_OBJECT_COMMODITY
		];

		if ($order)
		{
			$siteId = $order->getSiteId();
			$propertiesCodes = ['ARTNUMBER'];
			$itemProperties = self::getCatalogPropertiesForItem($basketItem->getProductId(), $propertiesCodes, $siteId);
			$data['PROPERTIES'] = $itemProperties;
		}
		else
		{
			$data['PROPERTIES'] = [];
		}

		if ($basketItem->isCustomPrice())
		{
			$data['BASE_PRICE'] = $basketItem->getPriceWithVat();
		}
		else
		{
			if ((float)$basketItem->getDiscountPrice() != 0)
			{
				$data['DISCOUNT'] = [
					'PRICE' => (float)$basketItem->getDiscountPrice(),
					'TYPE' => 'C',
				];
			}
		}

		return $data;
	}

	/**
	 * @param $itemId
	 * @param $itemPropertiesCodes
	 * @param $siteId
	 * @return array
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 */
	private static function getCatalogPropertiesForItem($itemId, $itemPropertiesCodes, $siteId): array
	{
		$propertiesFieldNames = [];
		foreach ($itemPropertiesCodes as $propertyCode)
		{
			$propertiesFieldNames[] = 'PROPERTY_' . $propertyCode;
		}

		$result = [];
		$catalogData = Admin\Product::getData([$itemId], $siteId, $propertiesFieldNames);
		foreach ($catalogData as $item)
		{
			foreach ($itemPropertiesCodes as $propertyCode)
			{
				if (
					isset($item['PRODUCT_PROPS_VALUES']['PROPERTY_' .  $propertyCode . '_VALUE'])
					&& $item['PRODUCT_PROPS_VALUES']['PROPERTY_' . $propertyCode . '_VALUE'] !== '&nbsp'
				)
				{
					$result[$propertyCode] = $item['PRODUCT_PROPS_VALUES']['PROPERTY_' .  $propertyCode . '_VALUE'];
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $markingCode
	 * @param string $markingCodeGroup
	 * @return string
	 */
	protected function buildTag1162(string $markingCode, string $markingCodeGroup) : string
	{
		[$gtin, $serial] = $this->parseMarkingCode($markingCode, $markingCodeGroup);

		$hex =
			self::MARKING_TYPE_CODE.
			$this->convertToBinaryFormat($gtin, 6).
			$this->convertCharsToHex($serial)
		;

		return hex2bin($hex);
	}

	/**
	 * @param string $code
	 * @param string $group
	 * @return array
	 */
	private function parseMarkingCode(string $code, string $group) : array
	{
		$gtin = mb_substr($code, 2, 14);
		$serial = mb_substr($code, 18, $this->getSnLength($group));

		return [$gtin, $serial];
	}

	/**
	 * @param string $group
	 * @return int
	 */
	private function getSnLength(string $group) : int
	{
		if ((string)$group === '9840')
		{
			return 20;
		}

		return 13;
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
		if ($calcDeliveryTax === 'Y' && $service = $shipment->getDelivery())
		{
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
	 * @return Result
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
				if (isset($product['MARKING_CODE']) && $product['MARKING_CODE'] === '')
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
		if (!empty($data['PRODUCTS']))
		{
			foreach ($data['PRODUCTS'] as $item)
				$productSum += $item['SUM'];
		}

		if (!empty($data['DELIVERY']))
		{
			foreach ($data['DELIVERY'] as $delivery)
			{
				$productSum += $delivery['PRICE'];
			}
		}

		$paymentSum = 0;
		if (!empty($data['PAYMENTS']))
		{
			foreach ($data['PAYMENTS'] as $payment)
			{
				$paymentSum += $payment['SUM'];
			}
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
