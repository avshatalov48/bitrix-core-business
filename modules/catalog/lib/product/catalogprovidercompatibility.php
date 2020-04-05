<?php


namespace Bitrix\Catalog\Product;

use Bitrix\Currency;
use Bitrix\Sale;

/**
 * Class CatalogProviderCompatibility
 *
 * @package Bitrix\Catalog\Product
 */
class CatalogProviderCompatibility
{
	/**
	 * @param array $basketItemData
	 *
	 * @return array|bool
	 */
	public static function getProductData(array $basketItemData)
	{
		$context = array();

		if (!empty($basketItemData['SITE_ID']))
		{
			$context['SITE_ID'] = $basketItemData['SITE_ID'];
		}

		if (!empty($basketItemData['USER_ID']))
		{
			$context['USER_ID'] = $basketItemData['USER_ID'];
		}

		$providerClass = static::getProviderClass($context);
		if (!$providerClass)
		{
			return false;
		}

		$productId = $basketItemData['PRODUCT_ID'];
		$transfer = Sale\Internals\TransferProvider::create($providerClass, $context);

		$products = array(
			$productId => array(
				'ITEM_CODE' => $productId,
				'QUANTITY_LIST' => array(
					$basketItemData['BASKET_ID'] => $basketItemData['QUANTITY'])
				,
			));

		if (!empty($basketItemData['BASKET_ID']))
		{
			$products[$productId]['BASKET_ID'] = $basketItemData['BASKET_ID'];
		}

		$r = $transfer->getProductData($products);

		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (!empty($data['PRODUCT_DATA_LIST']))
			{
				$productDataList = $data['PRODUCT_DATA_LIST'];
				if (isset($productDataList[$productId]))
				{
					$productData = $productDataList[$productId];

					if (!empty($productData['PRICE_LIST']))
					{
						$basketItemCode = $basketItemData['BASKET_ID'];

						if (!empty($productData['PRICE_LIST'][$basketItemCode]))
						{
							$priceData = $productData['PRICE_LIST'][$basketItemCode];

							if (!isset($priceData['QUANTITY']) && isset($priceData['AVAILABLE_QUANTITY']))
							{
								$priceData['QUANTITY'] = $priceData['AVAILABLE_QUANTITY'];
							}

							$productData = $priceData + $productData;
						}
					}

					return $productData;
				}
			}
		}

		return false;
	}

	/**
	 * @param array $basketItemData
	 *
	 * @return array|bool
	 */
	public static function orderProduct(array $basketItemData)
	{
		return static::getProductData($basketItemData);
	}

	/**
	 * @param $productId
	 *
	 * @return array|bool
	 */
	public static function getSetItems($productId)
	{
		$resultList = array();

		$providerClass = static::getProviderClass();
		if (!$providerClass)
		{
			return false;
		}

		$transfer = Sale\Internals\TransferProvider::create($providerClass, static::getContext());

		$products = array($productId => array());
		$r = $transfer->getBundleItems($products);

		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (!empty($data['BUNDLE_LIST']))
			{
				$resultList = $data['BUNDLE_LIST'];
			}
		}

		return $resultList;
	}

	/**
	 * @param array $values
	 *
	 * @return array|bool
	 */
	public static function reserveProduct(array $values)
	{
		$providerClass = static::getProviderClass();
		if (!$providerClass)
		{
			return false;
		}

		$productId = $values['PRODUCT_ID'];
		$transfer = Sale\Internals\TransferProvider::create($providerClass, static::getContext());

		$products = array(
			$productId => array(
				'ITEM_CODE' => $productId,
				'QUANTITY' => $values['QUANTITY'] * ($values['UNDO_RESERVATION'] == 'Y'? -1 : 1),
			));

		$r = $transfer->reserve($products);
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (!empty($data))
			{
				$result = new Sale\Result();

				$result->setData(array(
					'RESERVED_PRODUCTS_LIST' => array(
						$productId => $data
					)
				));

				$r = $transfer->setItemsResultAfterReserve($products, $result);
			}
		}

		return false;
	}

	/**
	 * @param array $values
	 *
	 * @return array|bool
	 */
	public static function deductProduct(array $values)
	{
		$providerClass = static::getProviderClass();
		if (!$providerClass)
		{
			return false;
		}

		$productId = $values['PRODUCT_ID'];
		$transfer = Sale\Internals\TransferProvider::create($providerClass, static::getContext());

		$products = array(
			$productId => array(
				'ITEM_CODE' => $productId,
				'PRODUCT_ID' => $productId,
				'QUANTITY' => $values['QUANTITY'] * ($values['UNDO_DEDUCTION'] == 'Y'? -1 : 1),
			));

		$r = $transfer->ship($products);
		if ($r->isSuccess())
		{
			$data = $r->getData();
			if (!empty($data))
			{
				$result = new Sale\Result();

				$result->setData(array(
					'SHIPPED_PRODUCTS_LIST' => array(
						$productId => $data
					)
				));

				$r = $transfer->setItemsResultAfterShip($products, $result);
			}
		}

		return false;
	}

	/**
	 * @param array $values
	 *
	 * @return array|bool
	 */
	public static function viewProduct(array $values)
	{
		$result = false;
		$providerClass = static::getProviderClass();
		if (!$providerClass)
		{
			return $result;
		}

		$productId = $values['PRODUCT_ID'];
		$transfer = Sale\Internals\TransferProvider::create($providerClass, static::getContext());

		if (empty($values['SITE_ID']))
		{
			$values['SITE_ID'] = SITE_ID;
		}

		$products = array(
			$productId => array(
				"PRODUCT_ID" => $productId,
				"USER_ID"    => $values["USER_ID"],
				"SITE_ID"    => $values["SITE_ID"]
			));

		$r = $transfer->viewProduct($products);
		if ($r->isSuccess())
		{
			$data = $r->getData();

			if (!empty($data['VIEW_PRODUCTS_LIST']) && array_key_exists($productId, $data['VIEW_PRODUCTS_LIST']))
			{
				return $data['VIEW_PRODUCTS_LIST'][$productId];
			}
		}

		return $result;
	}

	/**
	 * @param array $values
	 *
	 * @return array|bool
	 */
	public static function getProductStores(array $values)
	{
		$result = false;
		$providerClass = static::getProviderClass();
		if (!$providerClass)
		{
			return $result;
		}

		$context = static::getContext();

		if (!empty($values['SITE_ID']))
		{
			$context['SITE_ID'] = $values['SITE_ID'];
		}

		$productId = $values['PRODUCT_ID'];
		$transfer = Sale\Internals\TransferProvider::create($providerClass, $context);

		$products = array(
			$productId => array(
				"PRODUCT_ID" => $productId,
				"BASKET_ID"    => $values["BASKET_ID"],
				"SITE_ID"    => $values["SITE_ID"]
			));

		$r = $transfer->getProductListStores($products);
		if ($r->isSuccess())
		{
			$data = $r->getData();

			if (!empty($data['PRODUCT_STORES_LIST']) && array_key_exists($productId, $data['PRODUCT_STORES_LIST']))
			{
				return $data['PRODUCT_STORES_LIST'][$productId];
			}
		}

		return $result;
	}

	/**
	 * @param array $values
	 *
	 * @return array|bool
	 */
	public static function recurringOrderProduct(array $values)
	{
		$result = false;
		$providerClass = static::getProviderClass();
		if (!$providerClass)
		{
			return $result;
		}

		$context = static::getContext();

		if (!empty($values['SITE_ID']))
		{
			$context['SITE_ID'] = $values['SITE_ID'];
		}

		$productId = $values['PRODUCT_ID'];
		$transfer = Sale\Internals\TransferProvider::create($providerClass, $context);

		$products = array(
			$productId => array(
				"PRODUCT_ID" => $productId,
				"USER_ID"    => $values["USER_ID"],
			));

		$r = $transfer->recurring($products);
		if ($r->isSuccess())
		{
			$data = $r->getData();

			if (!empty($data['RECURRING_PRODUCTS_LIST']) && array_key_exists($productId, $data['RECURRING_PRODUCTS_LIST']))
			{
				return $data['RECURRING_PRODUCTS_LIST'][$productId];
			}
		}

		return $result;
	}

	/**
	 * @param array $values
	 *
	 * @return array|bool
	 */
	public static function getStoresCount(array $values)
	{
		$result = false;
		$context = static::getContext();

		if (isset($values['SITE_ID']))
		{
			$context['SITE_ID'] = $values['SITE_ID'];
		}

		$providerClass = static::getProviderClass($context);
		if (!$providerClass)
		{
			return $result;
		}

		$transfer = Sale\Internals\TransferProvider::create($providerClass, $context);
		$r = $transfer->getStoresCount();
		if ($r->isSuccess())
		{
			$data = $r->getData();

			if (isset($data['STORES_COUNT']))
			{
				return $data['STORES_COUNT'];
			}
		}

		return $result;
	}

	public static function DeliverProduct(array $values)
	{
		$result = false;
		$providerClass = static::getProviderClass();
		if (!$providerClass)
		{
			return $result;
		}

		$productId = $values['PRODUCT_ID'];
		$transfer = Sale\Internals\TransferProvider::create($providerClass, static::getContext());

		if (empty($values['SITE_ID']))
		{
			$values['SITE_ID'] = SITE_ID;
		}

		$products = array(
			$productId => array(
				"PRODUCT_ID" => $productId,
				"USER_ID"    => $values["USER_ID"],
				"ORDER_ID"    => $values["ORDER_ID"],
				"PAID"    => $values["PAID"],
				"BASKET_ID"    => $values["BASKET_ID"],
			));

		$r = $transfer->deliver($products);
		if ($r->isSuccess())
		{
			$data = $r->getData();

			if (!empty($data['DELIVER_PRODUCTS_LIST']) && array_key_exists($productId, $data['DELIVER_PRODUCTS_LIST']))
			{
				return $data['DELIVER_PRODUCTS_LIST'][$productId];
			}
		}

		return $result;
	}

	/**
	 * @param array $context
	 *
	 * @return null|Sale\SaleProviderBase
	 */
	private static function getProviderClass($context = array())
	{
		$providerName = static::getProviderName();
		$providerClass = null;

		$setContext = static::getContext();

		if (!empty($context))
		{
			$setContext = $context + $setContext;
		}

		if (class_exists($providerName))
		{
			$providerClass = new $providerName($setContext);
		}

		return $providerClass;
	}

	/**
	 * @return array
	 */
	private static function getContext()
	{
		global $USER;
		return array(
			'SITE_ID' => SITE_ID,
			'USER_ID' => isset($USER) && $USER instanceof \CUser ? (int)$USER->GetID() : 0,
			'CURRENCY' => Currency\CurrencyManager::getBaseCurrency(),
		);
	}
	/**
	 * @return string
	 */
	private static function getProviderName()
	{
		return "\Bitrix\Catalog\Product\CatalogProvider";
	}

}
