<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class ProviderBuilderBase
 * @package Bitrix\Sale\Internals
 */
abstract class ProviderBuilderBase
{
	protected $items = array();
	protected $providerClass = null;
	protected $callbackFunction = null;
	protected $context = array();

	/**
	 * @param $providerClass
	 * @param $context
	 *
	 * @return ProviderBuilderBase
	 */
	public static function createBuilder($providerClass, $context)
	{
		if ($providerClass && ($providerClass instanceof Sale\SaleProviderBase))
		{
			$builder = ProviderBuilder::create($providerClass, $context);
		}
		else
		{
			$builder = ProviderBuilderCompatibility::create($providerClass, $context);
		}

		return $builder;
	}

	/**
	 * @param $providerClass
	 * @param $context
	 *
	 * @return ProviderBuilderBase
	 */
	public static function create($providerClass, $context)
	{
		$builder = new static();
		$builder->providerClass = $providerClass;
		$builder->context = $context;

		return $builder;
	}

	/**
	 * @param string $className
	 * @param string $methodName
	 * @param Sale\Result|null $result
	 *
	 * @return Sale\Result
	 * @throws Main\ArgumentOutOfRangeException
	 */
	protected function callTransferMethod($className, $methodName, Sale\Result $result = null)
	{
		if (!class_exists($className))
		{
			throw new Main\ArgumentOutOfRangeException('className');
		}

		/**
		 * @var TransferProviderBase $transfer
		 * @var TransferProviderBase $className
		 */
		$transfer = $className::create($this->getProviderClass(), $this->getContext());
		if (!method_exists($transfer, $methodName))
		{
			throw new Main\ArgumentOutOfRangeException('methodName');
		}

		if ($result)
		{
			$r = $transfer->$methodName($this->getItems(), $result);
		}
		else
		{
			$r = $transfer->$methodName($this->getItems());
		}

		return $r;

	}

	/**
	* @param Sale\BasketItemBase $basketItem
	*/
	abstract public function addProductByBasketItem(Sale\BasketItemBase $basketItem);

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 */
	abstract public function addProductByShipmentItem(Sale\ShipmentItem $shipmentItem);

	/**
	 * @param array $shipmentProductData
	 */
	abstract public function addProductByShipmentProductData(array $shipmentProductData);

	/**
	 * @param array $barcodeParams
	 */
	public function addBasketItemBarcodeData(array $barcodeParams)
	{
		$this->addItem($barcodeParams['PRODUCT_ID'], $barcodeParams);
	}

	/**
	 * @param int $productId
	 */
	public function addProductById($productId)
	{
		$fields = array(
			'PRODUCT_ID' => $productId,
		);

		$this->addItem($productId, $fields);
	}

	/**
	 * @param $outputName
	 *
	 * @return Sale\Result
	 */
	public function getProductData($outputName)
	{
		$r = static::callTransferMethod($this->getTransferClassName(), 'getProductData');
		if (!$r->isSuccess())
		{
			return $r;
		}

		return $this->decomposeIntoProvider($r, $outputName);
	}

	/**
	 * @param $outputName
	 *
	 * @return Sale\Result
	 */
	public function getAvailableQuantity($outputName)
	{
		$r = static::callTransferMethod($this->getTransferClassName(), 'getAvailableQuantity');
		if (!$r->isSuccess())
		{
			return $r;
		}

		return $this->decomposeIntoProvider($r, $outputName);
	}

	/**
	 * @param $outputName
	 *
	 * @return Sale\Result
	 */
	public function getAvailableQuantityAndPrice($outputName)
	{
		$r = static::callTransferMethod($this->getTransferClassName(), 'getAvailableQuantityAndPrice');
		if (!$r->isSuccess())
		{
			return $r;
		}

		return $this->decomposeIntoProvider($r, $outputName);
	}

	/**
	 * @param Sale\Result $resultProvider
	 * @param $outputName
	 *
	 * @return Sale\Result
	 */
	protected function decomposeIntoProvider(Sale\Result $resultProvider, $outputName)
	{
		$result = new Sale\Result();
		$providerData = $resultProvider->getData();

		if (empty($providerData[$outputName]))
		{
			return $result;
		}

		$result->setData($providerData);
		return $result;
	}

	/**
	 * @return Sale\Result
	 */
	public function tryShip()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'tryShip');
	}

	/**
	 * @return Sale\Result
	 */
	public function isNeedShip()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'isNeedShip');
	}

	/**
	 * @return Sale\Result
	 */
	public function getBundleItems()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'getBundleItems');
	}

	/**
	 * @return Sale\Result
	 */
	public function deliver()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'deliver');
	}

	/**
	 * @return Sale\Result
	 */
	public function viewProduct()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'viewProduct');
	}

	/**
	 * @return Sale\Result
	 */
	public function getProductStores()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'getProductStores');
	}

	/**
	 * @return Sale\Result
	 */
	public function checkBarcode()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'checkBarcode');
	}


	/**
	 * @return Sale\Result
	 */
	public function recurring()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'recurring');
	}

	/**
	 * @param PoolQuantity $pool
	 * @param array $productTryShipList
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	abstract public function setItemsResultAfterTryShip(PoolQuantity $pool, array $productTryShipList);

	/**
	 * @param Sale\Result $result
	 *
	 * @return Sale\Result
	 */
	public function setItemsResultAfterReserve(Sale\Result $result)
	{
		return static::callTransferMethod($this->getTransferClassName(), 'setItemsResultAfterReserve', $result);
	}

	/**
	 * @param Sale\Result $result
	 *
	 * @return Sale\Result
	 */
	public function setItemsResultAfterShip(Sale\Result $result)
	{
		return static::callTransferMethod($this->getTransferClassName(), 'setItemsResultAfterShip', $result);
	}

	/**
	 * @return Sale\Result
	 */
	public function reserve()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'reserve');
	}

	/**
	 * @return Sale\Result
	 */
	public function ship()
	{
		return static::callTransferMethod($this->getTransferClassName(), 'ship');
	}

	/**
	 * @param Sale\Result $resultAfterDeliver
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public function createItemsResultAfterDeliver(Sale\Result $resultAfterDeliver)
	{
		$result = new Sale\Result();
		$resultList = array();
		$products = $this->getItems();

		if (empty($products))
		{
			return $result;
		}

		$resultDeliverData = $resultAfterDeliver->getData();

		foreach ($products as $productId => $productData)
		{
			$providerName = $this->getProviderName();
			if (empty($resultDeliverData['DELIVER_PRODUCTS_LIST']) ||
				empty($resultDeliverData['DELIVER_PRODUCTS_LIST'][$providerName]) ||
				!array_key_exists($productId, $resultDeliverData['DELIVER_PRODUCTS_LIST'][$providerName]))
			{
				continue;
			}

			if (empty($productData['SHIPMENT_ITEM_LIST']))
			{
				continue;
			}

			/**
			 * @var int $shipmentItemIndex
			 * @var Sale\ShipmentItem $shipmentItem
			 */
			foreach ($productData['SHIPMENT_ITEM_LIST'] as $shipmentItemIndex => $shipmentItem)
			{
				$basketItem = $shipmentItem->getBasketItem();

				if (!$basketItem)
				{
					throw new Main\ObjectNotFoundException('Entity "BasketItem" not found');
				}

				$resultList[$basketItem->getBasketCode()] = $resultDeliverData['DELIVER_PRODUCTS_LIST'][$providerName][$productId];
			}
		}

		if (!empty($resultList))
		{
			$result->setData(
				array(
					'RESULT_AFTER_DELIVER_LIST' => $resultList
				)
			);
		}

		return $result;
	}

	/**
	 * @param array $productData
	 */
	protected function addProduct(array $productData)
	{
		$this->items[$productData['PRODUCT_ID']] = $productData;
	}

	/**
	 * @internal
	 * @return mixed
	 */
	public function getProviderClass()
	{
		return $this->providerClass;
	}

	/**
	 * @internal
	 * @return string
	 */
	public function getProviderName()
	{
		$providerName = null;
		$providerClass = $this->getProviderClass();
		if ($providerClass)
		{
			$reflect = new \ReflectionClass($this->getProviderClass());
			$providerName = $reflect->getName();
		}

		return  $this->clearProviderName($providerName);
	}

	/**
	 * @param $providerName
	 *
	 * @return string
	 */
	protected function clearProviderName($providerName)
	{
		if (substr($providerName, 0, 1) == "\\")
		{
			$providerName = substr($providerName, 1);
		}

		return $providerName;
	}


	/**
	 * @internal
	 * @return array
	 */
	protected function getContext()
	{
		return $this->context;
	}

	/**
	 * @return array
	 */
	protected function getItems()
	{
		return $this->items;
	}

	/**
	 * @param integer $productId
	 * @param array $productData
	 */
	protected function addItem($productId, array $productData)
	{
		$fields = array();
		if (isset($this->items[$productId]))
		{
			$fields = $this->items[$productId];
		}

		$fields = $productData + $fields;

		if (isset($fields['QUANTITY_LIST'][$productData['BASKET_CODE']]))
		{
			$fields['QUANTITY_LIST'][$productData['BASKET_CODE']] += floatval($productData['QUANTITY']);
		}
		else
		{
			$fields['QUANTITY_LIST'][$productData['BASKET_CODE']] = floatval($productData['QUANTITY']);
		}

		unset($fields['QUANTITY']);

		if (isset($fields['RESERVED_QUANTITY_LIST'][$productData['BASKET_CODE']]))
		{
			$fields['RESERVED_QUANTITY_LIST'][$productData['BASKET_CODE']] += floatval($productData['RESERVED_QUANTITY']);
		}
		else
		{
			$fields['RESERVED_QUANTITY_LIST'][$productData['BASKET_CODE']] = floatval($productData['RESERVED_QUANTITY']);
		}

		unset($fields['RESERVED_QUANTITY']);

		if (isset($productData['SHIPMENT_ITEM']))
		{
			/** @var Sale\ShipmentItem $shipmentItem */
			$shipmentItem = $productData['SHIPMENT_ITEM'];
			unset($fields['SHIPMENT_ITEM']);

			$fields['SHIPMENT_ITEM_LIST'][$shipmentItem->getInternalIndex()] = $shipmentItem;
			$fields['SHIPMENT_ITEM_QUANTITY_LIST'][$shipmentItem->getInternalIndex()] = floatval($productData['QUANTITY']);
		}

		if (isset($productData['STORE_DATA']))
		{
			if (!isset($fields['STORE_DATA_LIST']))
			{
				$fields['STORE_DATA_LIST'] = array();
			}

			$fields['STORE_DATA_LIST'] = $productData['STORE_DATA'] + $fields['STORE_DATA_LIST'];
			unset($fields['STORE_DATA']);
		}

		if (isset($productData['IS_BARCODE_MULTI']) && !isset($fields['IS_BARCODE_MULTI']))
		{
			$fields['IS_BARCODE_MULTI'] = $productData['IS_BARCODE_MULTI'];
		}

		if (isset($productData['SHIPMENT_ITEM_DATA']))
		{
			if (!isset($fields['SHIPMENT_ITEM_DATA_LIST']))
			{
				$fields['SHIPMENT_ITEM_DATA_LIST'] = array();
			}

			$fields['SHIPMENT_ITEM_DATA_LIST'] = $productData['SHIPMENT_ITEM_DATA'] + $fields['SHIPMENT_ITEM_DATA_LIST'];
			unset($fields['SHIPMENT_ITEM_DATA']);
		}

		if (isset($productData['NEED_RESERVE']))
		{
			if (!isset($fields['NEED_RESERVE_LIST']))
			{
				$fields['NEED_RESERVE_LIST'] = array();
			}

			$fields['NEED_RESERVE_LIST'] = $productData['NEED_RESERVE'] + $fields['NEED_RESERVE_LIST'];
			unset($fields['NEED_RESERVE']);
		}
		$this->items[$productId] = $fields;
	}

	/**
	 * @param $productId
	 *
	 * @return bool|array
	 */
	protected function getItem($productId)
	{
		$item = false;
		if ($this->isExistsProductIdInItems($productId))
		{
			$item = $this->items[$productId];
		}
		return $item;
	}

	/**
	 * @param $productId
	 *
	 * @return bool
	 */
	protected function isExistsProductIdInItems($productId)
	{
		return (isset($this->items[$productId]));
	}

	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	abstract public function getTransferClassName();
}