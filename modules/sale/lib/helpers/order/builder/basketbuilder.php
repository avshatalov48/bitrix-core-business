<?
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Catalog\Product;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemBase;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Helpers\Admin\Blocks\OrderBasket;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Order;
use Bitrix\Sale\Provider;
use Bitrix\Sale\ReserveQuantityCollection;

/**
 * Class BasketBuilder
 * @package Bitrix\Sale\Helpers\Order\Builder
 * @internal
 */
abstract class BasketBuilder
{
	const BASKET_CODE_NEW = 'new';

	/** @var IBasketBuilderDelegate */
	protected $delegate = null;
	/** @var OrderBuilder  */
	protected $builder = null;
	/** @var int  */
	protected $maxBasketCodeIdx = 0;
	/** @var array  */
	protected $formData = [];
	/** @var array  */
	protected $needDataUpdate = array();
	/** @var array  */
	protected $basketCodeMap = [];
	/** @var array  */
	protected $cacheProductProviderData = false;
	/** @var array  */
	protected $catalogProductsIds = [];
	/** @var array  */
	protected $catalogProductsData = [];
	/** @var array  */
	protected $providerData = [];
	/** @var array  */
	protected $trustData = [];
	/** @var bool */
	protected $isProductAdded = false;

	public function __construct(OrderBuilder $builder)
	{
		$this->builder = $builder;
		$this->cacheProductProviderData = $this->builder->getSettingsContainer()->getItemValue('cacheProductProviderData');
	}

	public function initBasket()
	{
		$this->formData = $this->builder->getFormData();
		$this->delegate = $this->getDelegate($this->formData['ID']);
		\Bitrix\Sale\ProviderBase::setUsingTrustData(true);
		return $this;
	}

	/**
	 * @param int $orderId
	 * @return IBasketBuilderDelegate
	 */
	protected function getDelegate($orderId)
	{
		return (int)$orderId > 0 ? new BasketBuilderExist($this) : new BasketBuilderNew($this);
	}

	/**
	 * @return bool
	 */
	public function isNeedUpdateNewProductPrice()
	{
		return $this->builder->getSettingsContainer()->getItemValue('needUpdateNewProductPrice');
	}

	public function checkProductData(array $productData)
	{
		$result = true;

		if((float)$productData['QUANTITY'] <= 0)
		{
			$this->getErrorsContainer()->addError(
				new Error(
					Loc::getMessage(
						"SALE_HLP_ORDERBUILDER_QUANTITY_ERROR",
						['#PRODUCT_NAME#' => $productData['NAME']]
			)));

			$result = false;
		}

		if((int)$productData['PRICE'] < 0)
		{
			$this->getErrorsContainer()->addError(
				new Error(
					Loc::getMessage(
						"SALE_HLP_ORDERBUILDER_PRICE_ERROR",
						['#PRODUCT_NAME#' => $productData['NAME']]
			)));

			$result = false;
		}

		return $result;
	}

	public function preliminaryDataPreparation()
	{
		foreach($this->formData["PRODUCT"] as $basketCode => $productData)
		{
			if(!$this->checkProductData($productData))
			{
				throw new BuildingException();
			}

			if(self::isBasketItemNew($basketCode))
			{
				$basketInternalId = intval(mb_substr($basketCode, 1));

				if($basketInternalId > $this->maxBasketCodeIdx)
					$this->maxBasketCodeIdx = $basketInternalId;

				// by the way let's mark rows for update data if need
				if($this->isNeedUpdateNewProductPrice()) //???is it right for edit orders
				{
					$this->needDataUpdate[] = $basketCode; //???is it needed by new orders
					unset($this->formData["PRODUCT"][$basketCode]["PROVIDER_DATA"]);
					unset($this->formData["PRODUCT"][$basketCode]["SET_ITEMS_DATA"]);
				}
			}
		}

		/*
		 * Because of one of discounts, require that the first product must be the most expencsve.
		 * If we want to save the sorting of the products we must use field "SORT" - fill it earlier
		 * and use it during layout.
		*/

		sortByColumn($this->formData["PRODUCT"], array("BASE_PRICE" => SORT_DESC, "PRICE" => SORT_DESC), '', null, true);
		return $this;
	}

	/**
	 * @deprecated
	 */
	protected function getExistsItem($moduleId, $productId, array $properties = array())
	{
		return $this->getBasket()->getExistsItem($moduleId, $productId, $properties);
	}

	public function removeDeletedItems()
	{
		$deleteBasketItemsIfNotExists = $this->builder->getSettingsContainer()->getItemValue('deleteBasketItemsIfNotExists');
		$itemsBasketCodes = [];

		foreach($this->formData["PRODUCT"] as $basketCode => $productData)
		{
			if (!isset($productData["PROPS"]))
			{
				$productData["PROPS"] = array();
			}

			$item = null;
			if ($basketCode != \Bitrix\Sale\Helpers\Admin\OrderEdit::BASKET_CODE_NEW)
			{
				$item = $this->getBasket()->getItemByBasketCode($basketCode);
			}

			if ($item == null && $deleteBasketItemsIfNotExists)
			{
				DiscountCouponsManager::useSavedCouponsForApply(false);
			}

			if($item && $item->isBundleChild())
			{
				continue;
			}

			if(!$item)
			{
				continue;
			}

			$itemsBasketCodes[] = $item->getBasketCode();
		}

		/** @var  \Bitrix\Sale\BasketItem  $item */
		$basketItems = $this->getBasket()->getBasketItems();

		foreach($basketItems as $item)
		{
			if(!in_array($item->getBasketCode(), $itemsBasketCodes))
			{
				if ($deleteBasketItemsIfNotExists)
				{
					$itemDeleteResult = $item->delete();

					if (!$itemDeleteResult->isSuccess())
					{
						$this->builder->getErrorsContainer()->addErrors($itemDeleteResult->getErrors());
						throw new BuildingException();
					}
				}
				elseif ($this->getSettingsContainer()->getItemValue('clearReservesIfEmpty') === true)
				{
					$this->clearReservesForItem($item);
				}
			}
		}

		return $this;
	}

	public function itemsDataPreparation()
	{
		foreach($this->formData["PRODUCT"] as $basketCode => $productData)
		{
			if (isset($productData["IS_SET_ITEM"]) && $productData["IS_SET_ITEM"] === "Y")
			{
				continue;
			}

			if(!isset($productData["PROPS"]) || !is_array($productData["PROPS"]))
				$productData["PROPS"] = array();

			/** @var BasketItem $item */
			$item = $this->getItemFromBasket($basketCode, $productData);

			if($item)
			{
				$this->setItemData($basketCode, $productData, $item);
			}
			else
			{
				$item = $this->createItem($basketCode, $productData);

				if(!$this->isProductAdded)
				{
					$this->isProductAdded = true;
				}
			}

			/*
			 * Could be deleted and than added one more time product.
			 * Or just added product.
			 */
			if($basketCode != $item->getBasketCode())
				$this->basketCodeMap[$basketCode] = $item->getBasketCode();

			if(!empty($productData["PROPS"]) && is_array($productData["PROPS"]))
			{
				/** @var \Bitrix\Sale\BasketPropertiesCollection $property */
				$property = $item->getPropertyCollection();
				if(!$property->isPropertyAlreadyExists($productData["PROPS"]))
				{
					$property->setProperty($productData["PROPS"]);
				}
			}
		}

		return $this;
	}

	public function basketCodeMap()
	{
		if(!empty($this->basketCodeMap))
		{
			foreach($this->basketCodeMap as $old => $new)
			{
				$this->formData['PRODUCT'][$new] = $this->formData['PRODUCT'][$old];
				unset($this->formData['PRODUCT'][$old]);
			}
		}

		return $this;
	}

	// Preparing fields needed by provider
	protected function setItemsFieldsByFormData()
	{
		/** @var  \Bitrix\Sale\BasketItem  $item */
		$basketItems = $this->getBasket()->getBasketItems();

		foreach($basketItems as $item)
		{
			$basketCode = $item->getBasketCode();

			if(empty($this->formData['PRODUCT'][$basketCode]))
				continue;

			$productData = $this->formData['PRODUCT'][$basketCode];
			if (
				isset($productData['MODULE'])
				&& $productData['MODULE'] === 'catalog'
				&& empty($productData['PRODUCT_PROVIDER_CLASS'])
				&& Loader::includeModule('catalog')
			)
			{
				$productData['PRODUCT_PROVIDER_CLASS'] = '\\'.Product\CatalogProvider::class;
			}

			$isProductDataNeedUpdate = in_array($basketCode, $this->needDataUpdate);

			if(isset($productData["PRODUCT_PROVIDER_CLASS"]) && $productData["PRODUCT_PROVIDER_CLASS"] <> '')
			{
				$item->setField("PRODUCT_PROVIDER_CLASS", trim($productData["PRODUCT_PROVIDER_CLASS"]));
			}

			/*
			 * Let's extract cached provider product data from field
			 * in case activity is through ajax.
			 */
			if($this->cacheProductProviderData && !$isProductDataNeedUpdate)
			{
				self::sendProductCachedDataToProvider($item, $this->getOrder(), $productData);
			}

			if (isset($productData['NAME']))
			{
				$item->setField('NAME', $productData['NAME']);
			}

			$item->setField('TYPE', $productData['TYPE'] ?? null);

			if (isset($productData['CUSTOM_PRICE']) && $productData['CUSTOM_PRICE'] === 'Y')
			{
				$item->markFieldCustom('PRICE');
			}

			$res = $item->setField("QUANTITY", $productData["QUANTITY"]);

			if(!$res->isSuccess())
			{
				$this->getErrorsContainer()->addErrors($res->getErrors());
				throw new BuildingException();
			}

			if (isset($productData['VAT_RATE']))
			{
				$item->markFieldCustom('VAT_RATE');
				$item->setField('VAT_RATE', $productData['VAT_RATE']);
			}

			if (isset($productData['VAT_INCLUDED']))
			{
				$item->markFieldCustom('VAT_INCLUDED');
				$item->setField('VAT_INCLUDED', $productData['VAT_INCLUDED']);
			}

			if(isset($productData["MODULE"]) && $productData["MODULE"] == "catalog")
			{
				$this->catalogProductsIds[] = $item->getField('PRODUCT_ID');
			}
			elseif(empty($productData["PRODUCT_PROVIDER_CLASS"]))
			{
				$availableFields = BasketItemBase::getAvailableFields();
				$availableFields = array_fill_keys($availableFields, true);
				$fillFields = array_intersect_key($productData, $availableFields);

				$orderCurrency = $this->getOrder()->getCurrency();
				if (
					isset($fillFields['CURRENCY'])
					&& $fillFields['CURRENCY'] !== $orderCurrency
				)
				{
					$fillFields['PRICE'] = \CCurrencyRates::ConvertCurrency(
						(float)$fillFields['PRICE'],
						$fillFields['CURRENCY'],
						$orderCurrency
					);
					$fillFields['BASE_PRICE'] = \CCurrencyRates::ConvertCurrency(
						(float)$fillFields['BASE_PRICE'],
						$fillFields['CURRENCY'],
						$orderCurrency
					);
					$fillFields['CURRENCY'] = $orderCurrency;
				}

				if (!empty($fillFields))
				{
					$r = $item->setFields($fillFields);

					if(!$r->isSuccess())
					{
						$this->getErrorsContainer()->getErrors($r->addErrors());
					}
				}
			}
		}
	}

	protected function obtainCatalogProductsData(array $fields = array())
	{
		if(empty($this->catalogProductsIds))
			return;

		$order = $this->getOrder();
		//$this->catalogProductsIds = array();

		foreach($this->catalogProductsIds as  $id)
		{
			$details = OrderEdit::getProductDetails($id, $order->getUserId(), $order->getSiteId());

			if($details !== false)
				$this->catalogProductsData[$id] = $details;
		}

		$noCachedProductIds = array_diff($this->catalogProductsIds, array_keys($this->catalogProductsData));

		if(!empty($noCachedProductIds))
		{
			$noCachedData = \Bitrix\Sale\Helpers\Admin\Product::getData($noCachedProductIds, $order->getSiteId(), array_keys($fields));

			foreach($noCachedData as $productId => $productData)
			{
				$this->catalogProductsData[$productId] = $productData;
				OrderEdit::setProductDetails($productId, $order->getUserId(), $order->getSiteId(), $this->catalogProductsData[$productId]);
			}

			$emptyData = array_diff($this->catalogProductsIds, array_keys($this->catalogProductsData));

			foreach($emptyData as $productId)
				$this->catalogProductsData[$productId] = array();
		}
	}

	protected function obtainProviderProductsData()
	{
		$order = $this->getOrder();
		$basketItems = $this->getBasket()->getBasketItems();

		if($this->cacheProductProviderData && empty($this->needDataUpdate) && !$this->isNeedUpdateNewProductPrice())
			return;

		$params = array("AVAILABLE_QUANTITY");

		if($order->getId() <= 0)
			$params[] = "PRICE";

		$this->providerData = Provider::getProductData($this->getBasket(), $params);

		/** @var BasketItem $item */
		foreach($basketItems as $item)
		{
			$basketCode = $item->getBasketCode();

			if($order->getId() <= 0 && !empty($this->providerData[$basketCode]) && empty($this->providerData[$basketCode]['QUANTITY']))
			{

				$this->getErrorsContainer()->addError(
					new Error(
						Loc::getMessage(
							"SALE_ORDEREDIT_PRODUCT_QUANTITY_IS_EMPTY",
							array(
								"#NAME#" => $item->getField('NAME')
							)
						),
						'SALE_ORDEREDIT_PRODUCT_QUANTITY_IS_EMPTY'
					)
				);

				throw new BuildingException();
			}
		}
	}

	protected function isProductAvailable($basketCode, $productFormData, $productProviderData, $isProductDataNeedUpdate)
	{
		$result = true;

		if($this->getOrder()->getId() <= 0 && (empty($productProviderData[$basketCode]) || !$this->cacheProductProviderData || $isProductDataNeedUpdate))
		{
			if(empty($productProviderData[$basketCode]) && $productFormData["PRODUCT_PROVIDER_CLASS"] <> '')
			{
				$result = false;
			}
		}

		return $result;
	}

	//todo: \Bitrix\Catalog\Product\Basket::addProductToBasket()
	public function setItemsFields()
	{
		$order = $this->getOrder();
		$basketItems = $this->getBasket()->getBasketItems();

		$this->setItemsFieldsByFormData();
		$this->obtainCatalogProductsData();
		$this->obtainProviderProductsData();

		$productProviderData = array();

		/** @var BasketItem $firstBasketItem */
		$firstBasketItem = $basketItems[0] ?? null;
		$vatIncludedByFirstItem = $firstBasketItem ? $firstBasketItem->getField('VAT_INCLUDED') : 'N';

		/** @var BasketItem $item */
		foreach($basketItems as $item)
		{
			$basketCode = $item->getBasketCode();
			$productFormData = $this->formData['PRODUCT'][$basketCode] ?? [];
			$isProductDataNeedUpdate = in_array($basketCode, $this->needDataUpdate);
			$productProviderData[$basketCode] = $item->getFieldValues();

			if(empty($productFormData))
				continue;

			if(!empty($this->providerData[$basketCode]))
			{
				if ($this->builder->getSettingsContainer()->getItemValue('isRefreshData') === true)
				{
					unset($this->providerData[$basketCode]['QUANTITY']);
				}

				$productProviderData[$basketCode] = $this->providerData[$basketCode];
			}
			elseif(!empty($trustData[$basketCode]))
			{
				$productProviderData[$basketCode] = $trustData[$basketCode];
			}
			else
			{
				$productProviderData = Provider::getProductData($this->getBasket(), array("PRICE", "AVAILABLE_QUANTITY"), $item);

				if(is_array($productProviderData[$basketCode]) && !empty($productProviderData[$basketCode]))
					OrderEdit::setProviderTrustData($item, $order, $productProviderData[$basketCode]);
			}

			/* Get actual info from provider
			 *	cases:
			 *	 1) add new product to basket;
			 *	 2) saving operation;
			 * 	 3) changing quantity;
			 *   4) changing buyerId
			 */

			if(!$this->isProductAvailable($basketCode, $productFormData, $productProviderData, $isProductDataNeedUpdate))
			{
				$this->getErrorsContainer()->addError(
					new Error(
						Loc::getMessage(
							"SALE_HLP_ORDERBUILDER_PRODUCT_NOT_AVILABLE",
							array(
								"#NAME#" => $productFormData["NAME"] . (!empty($productFormData["PRODUCT_ID"]) ? " (".$productFormData['PRODUCT_ID'].")" : "")
							)
						)
					)
				);
			}

			$product = array();

			//merge catalog data
			if($item->getField("MODULE") == "catalog")
			{
				if(!empty($catalogData[$item->getProductId()]))
				{
					$product = array_merge($product, $catalogData[$item->getProductId()]);
					unset($productFormData["CURRENCY"]);
				}
			}

			//merge form data
			if(!$this->cacheProductProviderData || $isProductDataNeedUpdate)
			{
				$product = array_merge($productFormData, $product);
			}
			else
			{
				$basePrice = (float)($productFormData['BASE_PRICE'] ?? 0);
				$price = (float)($productFormData['PRICE'] ?? 0);

				$needUpdateItemPrice = $this->isNeedUpdateNewProductPrice() && $this->isBasketItemNew($basketCode);
				$isPriceCustom = isset($productFormData['CUSTOM_PRICE']) && $productFormData['CUSTOM_PRICE'] == 'Y';

				if ($isPriceCustom)
				{
					$productFormData['DISCOUNT_PRICE'] = 0;
					if ($basePrice > $price)
					{
						$productFormData['DISCOUNT_PRICE'] = $basePrice - $price;
					}
				}

				if (($order->getId() === 0 && !$isPriceCustom) || $needUpdateItemPrice)
				{
					unset($productFormData['PRICE'], $productFormData['PRICE_BASE'], $productFormData['BASE_PRICE']);
				}

				$product = array_merge($product, $productFormData);
			}

			if (isset($product["OFFER_ID"]) && intval($product["OFFER_ID"]) > 0)
			{
				$product["PRODUCT_ID"] = $product["OFFER_ID"];
			}

			//discard BasketItem redundant fields
			$product = array_intersect_key($product, array_flip($item::getAvailableFields()));

			if (isset($product["MEASURE_CODE"]) && $product["MEASURE_CODE"] <> '')
			{
				$measures = OrderBasket::getCatalogMeasures();

				if (!empty($measures[$product["MEASURE_CODE"]]))
				{
					$product["MEASURE_NAME"] = $measures[$product["MEASURE_CODE"]];
				}
			}

			if (empty($product["CURRENCY"]))
			{
				$product["CURRENCY"] = $order->getCurrency();
			}

			if (
				isset($productFormData["IS_SET_PARENT"])
				&& $productFormData["IS_SET_PARENT"] === "Y"
			)
			{
				$product["TYPE"] = BasketItem::TYPE_SET;
			}

			OrderEdit::setProductDetails(
				$productFormData["OFFER_ID"],
				$order->getUserId(),
				$order->getSiteId(),
				array_merge($product, $productFormData)
			);

			if ($product["CURRENCY"] !== $order->getCurrency())
			{
				$product["PRICE"] = \CCurrencyRates::ConvertCurrency($product["PRICE"], $product["CURRENCY"], $order->getCurrency());
				if ($product["BASE_PRICE"] > 0)
				{
					$product["BASE_PRICE"] = \CCurrencyRates::ConvertCurrency($product["BASE_PRICE"], $product["CURRENCY"], $order->getCurrency());
				}
				if ($product["DISCOUNT_PRICE"] > 0)
				{
					$product["DISCOUNT_PRICE"] = \CCurrencyRates::ConvertCurrency($product["DISCOUNT_PRICE"], $product["CURRENCY"], $order->getCurrency());
				}
				if ($product["VAT_RATE"] > 0)
				{
					$product["VAT_RATE"] = \CCurrencyRates::ConvertCurrency($product["VAT_RATE"], $product["CURRENCY"], $order->getCurrency());
				}

				$product["CURRENCY"] = $order->getCurrency();
			}

			if (
				$product['VAT_INCLUDED'] !== $vatIncludedByFirstItem
				||
				(
					isset($productProviderData[$basketCode]['VAT_INCLUDED'])
					&&
					$product['VAT_INCLUDED'] !== $productProviderData[$basketCode]['VAT_INCLUDED']
				)
			)
			{
				if ($product['VAT_INCLUDED'] === 'Y')
				{
					$product['PRICE'] = $product['PRICE'] / (1 + $product["VAT_RATE"]);
					$product['BASE_PRICE'] = $product['BASE_PRICE'] / (1 + $product["VAT_RATE"]);
				}

				// There can be only one value for all of them
				$product['VAT_INCLUDED'] = $vatIncludedByFirstItem;
			}

			$this->setBasketItemFields($item, $product);

			if ($item->isReservableItem())
			{
				if (!empty($productFormData['RESERVE']) && is_array($productFormData['RESERVE']))
				{
					$reserveData = $productFormData['RESERVE'];
					$this->setReserveDataForItem($item, $reserveData);
				}
				elseif ($this->getSettingsContainer()->getItemValue('clearReservesIfEmpty') === true)
				{
					$this->clearReservesForItem($item);
				}
			}
		}

		return $this;
	}

	protected function clearReservesForItem(BasketItem $item)
	{
		if (!$item->isReservableItem())
		{
			return;
		}

		/** @var ReserveQuantityCollection $reserveCollection */
		$reserveCollection = $item->getReserveQuantityCollection();
		if ($reserveCollection)
		{
			$reserveCollection->clearCollection();
		}
	}

	protected function setReserveDataForItem(BasketItem $item, array $reserveData)
	{
		if (!$item->isReservableItem())
		{
			return;
		}

		$reserveCollection = $item->getReserveQuantityCollection();
		if (!$reserveCollection)
		{
			return;
		}

		// if some reserves were created upon order creation, we have to clear them and set the manual reserves
		if ($this->getOrder()->isNew())
		{
			$this->clearReservesForItem($item);
		}

		foreach ($reserveData as $reserveCode => $reserve)
		{
			$isNewReserve = mb_strpos($reserveCode, 'n') === 0;
			if ($isNewReserve)
			{
				$reserveCollectionItem = $reserveCollection->create();
			}
			else
			{
				$reserveCollectionItem = $reserveCollection->getItemById($reserveCode);
				if (!$reserveCollectionItem)
				{
					continue;
				}
			}

			if (isset($reserve['STORE_ID']) && (int)$reserve['STORE_ID'] !== $reserveCollectionItem->getStoreId())
			{
				if (!$isNewReserve)
				{
					// drop the old reserve and create a new one instead since we can't just change the store id like that
					$deleteResult = $reserveCollectionItem->delete();
					if (!$deleteResult->isSuccess())
					{
						$this->getErrorsContainer()->addErrors($deleteResult->getErrors());
						continue;
					}

					$reserveCollectionItem = $reserveCollection->create();
				}

				$reserveCollectionItem->setStoreId((int)$reserve['STORE_ID']);
			}

			if (isset($reserve['QUANTITY']))
			{
				$quantity = (float)$reserve['QUANTITY'];
				if ($quantity < 0)
				{
					$quantity = 0;
				}
				$reserveCollectionItem->setQuantity($quantity);
			}

			if (isset($reserve['DATE_RESERVE_END']) && $reserve['DATE_RESERVE_END'] !== '')
			{
				$dateReserveEnd = new Date($reserve['DATE_RESERVE_END']);
				$reserveCollectionItem->setField('DATE_RESERVE_END', $dateReserveEnd);
			}

			if (isset($reserve['RESERVED_BY']))
			{
				$reserveCollectionItem->setField('RESERVED_BY', $reserve['RESERVED_BY']);
			}
		}
	}

	public function getOrder()
	{
		return $this->builder->getOrder();
	}

	public function getSettingsContainer()
	{
		return $this->builder->getSettingsContainer();
	}

	public function getErrorsContainer()
	{
		return $this->builder->getErrorsContainer();
	}

	public function getFormData()
	{
		return $this->formData;
	}

	public function getBasket()
	{
		return $this->builder->getOrder()->getBasket();
	}

	public static function isBasketItemNew($basketCode)
	{
		return (mb_strpos($basketCode, 'n') === 0) && ($basketCode != self::BASKET_CODE_NEW);
	}

	protected function getItemFromBasket($basketCode, $productData)
	{
		/** @var BasketItem $item */
		$item = $this->delegate->getItemFromBasket($basketCode, $productData);

		if($item && $item->isBundleChild())
			$item = null;

		return $item;
	}

	protected function setItemData($basketCode, &$productData, &$item)
	{
		return $this->delegate->setItemData($basketCode, $productData, $item);
	}

	protected function createItem($basketCode, &$productData)
	{
		//todo: is it stil working?
		if($basketCode != self::BASKET_CODE_NEW)
			$setBasketCode = $basketCode;
		elseif(intval($this->maxBasketCodeIdx) > 0)
			$setBasketCode = 'n'.strval($this->maxBasketCodeIdx+1); //Fix collision part 2.
		else
			$setBasketCode = null;

		$item = $this->getBasket()->createItem(
			$productData["MODULE"] ?? '',
			$productData["OFFER_ID"],
			$setBasketCode
		);

		if (
			!isset($productData['BASKET_CODE'])
			|| $basketCode != $productData['BASKET_CODE']
		)
		{
			$productData["BASKET_CODE"] = $item->getBasketCode();
		}

		if($basketCode == self::BASKET_CODE_NEW)
		{
			//$result->setData(array("NEW_ITEM_BASKET_CODE" => $productData["BASKET_CODE"]));
			$this->needDataUpdate[] = $item->getBasketCode();
		}

		if(!empty($productData['REPLACED']) && $productData['REPLACED'] == 'Y')
			$this->needDataUpdate[] = $item->getBasketCode();

		return $item;
	}

	public static function getCatalogMeasures()
	{
		static $result = null;
		$catalogIncluded = null;

		if(!is_array($result))
		{
			$result = array();

			if ($catalogIncluded === null)
			{
				$catalogIncluded = Loader::includeModule('catalog');
			}

			if ($catalogIncluded)
			{
				$dbList = \CCatalogMeasure::getList();

				while($arList = $dbList->Fetch())
				{
					$result[$arList["CODE"]] = ($arList["SYMBOL_RUS"] != '' ? $arList["SYMBOL_RUS"] : $arList["SYMBOL_INTL"]);
				}
			}

			if (empty($result))
			{
				$result[796] = GetMessage("SALE_ORDER_BASKET_SHTUKA");
			}
		}

		return $result;
	}

	public function sendProductCachedDataToProvider(BasketItem $item, Order $order, array &$productData)
	{
		if(empty($productData["PROVIDER_DATA"]) || !CheckSerializedData($productData["PROVIDER_DATA"]))
			return;

		$trustData = unserialize($productData["PROVIDER_DATA"], ['allowed_classes' => false]);

		//quantity was changed so data must be changed
		if(empty($trustData) || $trustData["QUANTITY"] == $productData["QUANTITY"])
			return;

		Provider::setTrustData($order->getSiteId(), $item->getField('MODULE'), $item->getProductId(), $trustData);

		if ($item->isBundleParent())
		{
			if ($bundle = $item->getBundleCollection())
			{
				/** @var \Bitrix\Sale\BasketItem $bundleItem */
				foreach ($bundle as $bundleItem)
				{
					$bundleItemData = $bundleItem->getFields()->getValues();
					Provider::setTrustData($order->getSiteId(), $bundleItem->getField('MODULE'), $bundleItem->getProductId(), $bundleItemData);
				}
			}
		}

		$this->trustData[$item->getBasketCode()] = $trustData;
	}

	public function setBasketItemFields(\Bitrix\Sale\BasketItem &$item, array $fields = array())
	{
		$result = $item->setFields($fields);

		if(!$result->isSuccess())
		{
			foreach($result->getErrors() as $error)
			{
				$containerErrors = $this->getErrorsContainer()->getErrors();

				//avoid duplication
				if(is_array($containerErrors) && !empty($containerErrors))
				{
					foreach($this->getErrorsContainer()->getErrors() as $existError)
					{
						if($error->getMessage() !== $existError->getMessage())
						{
							$this->getErrorsContainer()->addError($error);
						}
					}
				}
				else
				{
					$this->getErrorsContainer()->addError($error);
				}
			}

			throw new BuildingException();
		}
	}

	public function finalActions()
	{
		$this->delegate->finalActions();
		return $this;
	}

	public function isProductAdded()
	{
		return $this->isProductAdded;
	}

	/**
	 * Filling fuser of basket is needed.
	 *
	 * If empty get by user id of order.
	 *
	 * @return self
	 */
	public function fillFUser()
	{
		$basket = $this->getBasket();
		if ($basket && !$basket->getFUserId(true))
		{
			$fuserId = null;

			$order = $this->getOrder();
			if ($order && $order->getUserId())
			{
				$fuserId = Fuser::getUserIdById($order->getUserId());
			}

			$basket->setFUserId(
				$fuserId ?: Fuser::getId(false)
			);
		}

		return $this;
	}
}
