<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main,
	Bitrix\Sale;

class SaleBasketLineComponent extends CBitrixComponent
{
	protected $bUseCatalog = null;
	protected $readyForOrderFilter = array("CAN_BUY" => "Y", "DELAY" => "N", "SUBSCRIBE" => "N");
	protected $disableUseBasket = false;

	protected $currentFuser = null;

	/** @var Sale\Basket\Storage $basketStorage */
	protected $basketStorage;	// temporary unused

	public function onPrepareComponentParams($arParams)
	{
		// common

		$arParams['PATH_TO_BASKET'] = trim($arParams['PATH_TO_BASKET']);
		if ($arParams['PATH_TO_BASKET'] == '')
			$arParams['PATH_TO_BASKET'] = SITE_DIR.'personal/cart/';

		$arParams['PATH_TO_ORDER'] = trim($arParams['PATH_TO_ORDER']);
		if ($arParams['PATH_TO_ORDER'] == '')
			$arParams['PATH_TO_ORDER'] = SITE_DIR.'personal/order/make/';

		$arParams["HIDE_ON_BASKET_PAGES"] = (isset($arParams["HIDE_ON_BASKET_PAGES"]) && $arParams["HIDE_ON_BASKET_PAGES"] == 'N' ? 'N' : 'Y');

		if ($arParams['SHOW_NUM_PRODUCTS'] != 'N')
			$arParams['SHOW_NUM_PRODUCTS'] = 'Y';

		if ($arParams['SHOW_TOTAL_PRICE'] != 'N')
			$arParams['SHOW_TOTAL_PRICE'] = 'Y';

		if ($arParams['SHOW_EMPTY_VALUES'] != 'N')
			$arParams['SHOW_EMPTY_VALUES'] = 'Y';

		// personal

		if ($arParams['SHOW_PERSONAL_LINK'] != 'Y')
			$arParams['SHOW_PERSONAL_LINK'] = 'N';

		$arParams['PATH_TO_PERSONAL'] = trim($arParams['PATH_TO_PERSONAL']);
		if ($arParams['PATH_TO_PERSONAL'] == '')
			$arParams['PATH_TO_PERSONAL'] = SITE_DIR.'personal/';

		// authorization

		if ($arParams['SHOW_AUTHOR'] != 'Y')
			$arParams['SHOW_AUTHOR'] = 'N';

		if ($arParams['SHOW_AUTHOR'] === 'Y')
		{
			$arParams['SHOW_REGISTRATION'] = isset($arParams['SHOW_REGISTRATION']) && $arParams['SHOW_REGISTRATION'] === 'N' ? 'N' : 'Y';
		}
		else
		{
			$arParams['SHOW_REGISTRATION'] = 'N';
		}

		$arParams['PATH_TO_REGISTER'] = (isset($arParams['PATH_TO_REGISTER']) ? trim($arParams['PATH_TO_REGISTER']) : '');
		if ($arParams['PATH_TO_REGISTER'] === '')
			$arParams['PATH_TO_REGISTER'] = (string)Main\Config\Option::get('main', 'custom_register_page');
		if ($arParams['PATH_TO_REGISTER'] === '')
			$arParams['PATH_TO_REGISTER'] = SITE_DIR.'login/';

		$arParams['PATH_TO_AUTHORIZE'] = (isset($arParams['PATH_TO_AUTHORIZE']) ? trim($arParams['PATH_TO_AUTHORIZE']) : '');
		if ($arParams['PATH_TO_AUTHORIZE'] === '')
			$arParams['PATH_TO_AUTHORIZE'] = $arParams['PATH_TO_REGISTER'];

		$arParams['PATH_TO_PROFILE'] = trim($arParams['PATH_TO_PROFILE']);
		if ($arParams['PATH_TO_PROFILE'] == '')
			$arParams['PATH_TO_PROFILE'] = SITE_DIR.'personal/';

		// list

		if ($arParams['SHOW_PRODUCTS'] != 'Y')
			$arParams['SHOW_PRODUCTS'] = 'N';

		if ($arParams['SHOW_DELAY'] != 'N')
			$arParams['SHOW_DELAY'] = 'Y';

		if ($arParams['SHOW_NOTAVAIL'] != 'N')
			$arParams['SHOW_NOTAVAIL'] = 'Y';

		if ($arParams['SHOW_IMAGE'] != 'N')
			$arParams['SHOW_IMAGE'] = 'Y';

		if ($arParams['SHOW_PRICE'] != 'N')
			$arParams['SHOW_PRICE'] = 'Y';

		if ($arParams['SHOW_SUMMARY'] != 'N')
			$arParams['SHOW_SUMMARY'] = 'Y';

		// Visual

		if ($arParams['POSITION_FIXED'] != 'Y')
			$arParams['POSITION_FIXED'] = 'N';

		if ($arParams['POSITION_VERTICAL'] != 'bottom' && $arParams['POSITION_VERTICAL'] != 'vcenter')
			$arParams['POSITION_VERTICAL'] = 'top';

		if ($arParams['POSITION_HORIZONTAL'] != 'left' && $arParams['POSITION_HORIZONTAL'] != 'hcenter')
			$arParams['POSITION_HORIZONTAL'] = 'right';

		$arParams['MAX_IMAGE_SIZE'] = (isset($arParams['MAX_IMAGE_SIZE']) ? (int)$arParams['MAX_IMAGE_SIZE'] : 70);
		if ($arParams['MAX_IMAGE_SIZE'] <= 0)
			$arParams['MAX_IMAGE_SIZE'] = 70;

		// ajax

		if ($arParams['AJAX'] != 'Y')
			$arParams['AJAX'] = 'N';

		return $arParams;
	}

	protected function getUserFilter()
	{
		$fUserID = (int)$this->currentFuser;
		return ($fUserID > 0)
			? array("=FUSER_ID" => $fUserID, "=LID" => SITE_ID, "ORDER_ID" => null)
			: null; // no basket for current user
	}

	protected function removeItemFromCart()
	{
		if (preg_match('/^[0-9]+$/', $_POST["sbblRemoveItemFromCart"]) !== 1)
			return;

		if (!($userFilter = $this->getUserFilter()))
			return;

		$numProducts = CSaleBasket::GetList(
			array(),
			$userFilter + array("ID" => $_POST['sbblRemoveItemFromCart']),
			array()
		);

		if ($numProducts > 0)
			CSaleBasket::Delete($_POST['sbblRemoveItemFromCart']);
	}

	public function executeComponent()
	{
		if ($this->arParams['HIDE_ON_BASKET_PAGES'] == 'Y')
		{
			$currentPage = strtolower(\Bitrix\Main\Context::getCurrent()->getRequest()->getRequestedPage());
			$basketPage = strtolower($this->arParams['PATH_TO_BASKET']);
			$orderPage = strtolower($this->arParams['PATH_TO_ORDER']);
			if (
				strncmp($currentPage, $basketPage, strlen($basketPage)) == 0
				|| strncmp($currentPage, $orderPage, strlen($orderPage)) == 0
			)
				$this->disableUseBasket = true;
		}
		if (
			$this->disableUseBasket
			&& $this->arParams['SHOW_AUTHOR'] == 'N'
			&& $this->arParams['SHOW_PERSONAL_LINK'] == 'N'
		)
			return;

		if(!\Bitrix\Main\Loader::includeModule('sale'))
		{
			ShowError(GetMessage('SALE_MODULE_NOT_INSTALL'));
			return;
		}

		$this->loadCurrentFuser();

		if (isset($_POST['sbblRemoveItemFromCart']))
			$this->removeItemFromCart();

		// prepare result

		if(!\Bitrix\Main\Loader::includeModule("currency"))
		{
			ShowError(GetMessage("CURRENCY_MODULE_NOT_INSTALLED"));
			return;
		}

		$this->bUseCatalog = \Bitrix\Main\Loader::includeModule('catalog');

		$this->arResult = array(
			"TOTAL_PRICE" => 0,
			"NUM_PRODUCTS" => 0,
			"CATEGORIES" => array(),
			"ERROR_MESSAGE" => '',
			"DISABLE_USE_BASKET" => $this->disableUseBasket
		);

		if (!$this->disableUseBasket)
			$this->arResult['ERROR_MESSAGE'] = GetMessage("TSB1_EMPTY"); // deprecated

		if ($this->disableUseBasket)
		{
			$this->arParams['SHOW_PRODUCTS'] = 'N';
			$this->arParams['SHOW_TOTAL_PRICE'] = 'N';
			$this->arParams['SHOW_NUM_PRODUCTS'] = 'N';
			if ($this->arParams['SHOW_AUTHOR'] == 'Y')
				$this->arParams['SHOW_PERSONAL_LINK'] = 'N';
		}

		if($this->arParams["SHOW_PRODUCTS"] == "Y")
		{
			$this->arResult = $this->getProducts() + $this->arResult;
		}
		else
		{
			if($this->arParams["SHOW_TOTAL_PRICE"] == "Y")
			{
				$this->arResult["TOTAL_PRICE"] = \Bitrix\Sale\BasketComponentHelper::getFUserBasketPrice($this->getFuserId(), $this->getSiteId());
			}

			$this->arResult["NUM_PRODUCTS"] = \Bitrix\Sale\BasketComponentHelper::getFUserBasketQuantity($this->getFuserId(), $this->getSiteId());
		}

		if($this->arParams["SHOW_TOTAL_PRICE"] == "Y")
			$this->arResult["TOTAL_PRICE"] = CCurrencyLang::CurrencyFormat($this->arResult["TOTAL_PRICE"], CSaleLang::GetLangCurrency($this->getSiteId()), true);

		$productS = BasketNumberWordEndings($this->arResult["NUM_PRODUCTS"]);
		$this->arResult["PRODUCT(S)"] = GetMessage("TSB1_PRODUCT") . $productS;

		// compatibility!
		$this->arResult["PRODUCTS"] = str_replace("#END#", $productS,
			str_replace("#NUM#", $this->arResult["NUM_PRODUCTS"], GetMessage("TSB1_BASKET_TEXT"))
		);

		// output
		if ($this->arParams['AJAX'] == 'Y')
			$this->includeComponentTemplate('ajax_template');
		else
			$this->includeComponentTemplate();
	}

	private static $nextNumber = 0;

	public static function getNextNumber()
	{
		return ++self::$nextNumber;
	}

	private function getProducts()
	{
		$result = [
			'NUM_PRODUCTS' => 0,
			'TOTAL_PRICE' => 0,
			'CATEGORIES' => [
				'READY' => []
			]
		];

		$currentFuser = (int)$this->getFuserId();
		if ($currentFuser <= 0)
			return $result;

		$fullBasket = Sale\Basket::loadItemsForFUser($currentFuser, $this->getSiteId());
		if ($fullBasket->isEmpty())
			return $result;

		$basketItemList = [];

		/** @var Sale\Basket $basketClone */
		$basketClone = $fullBasket->createClone();
		$orderableBasket = $basketClone->getOrderableItems();
		unset($basketClone);
		if (!$orderableBasket->isEmpty())
		{
			$onlySaleDiscounts = (string)Main\Config\Option::get('sale', 'use_sale_discount_only') == 'Y';
			if (!$onlySaleDiscounts)
			{
				$orderableBasket->refresh(Sale\Basket\RefreshFactory::create(Sale\Basket\RefreshFactory::TYPE_FULL));
			}
			$discounts = Sale\Discount::buildFromBasket(
				$orderableBasket,
				new Sale\Discount\Context\Fuser($this->getFuserId())
			);
			$discountResult = $discounts->calculate();
			if ($discountResult->isSuccess())
			{
				$showPrices = $discounts->getShowPrices();
				if (!empty($showPrices['BASKET']))
				{
					foreach ($showPrices['BASKET'] as $basketCode => $data)
					{
						$basketItem = $orderableBasket->getItemByBasketCode($basketCode);
						if ($basketItem instanceof Sale\BasketItemBase)
						{
							$basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
							$basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
							$basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
						}
					}
					unset($basketItem, $basketCode, $data);
				}
				unset($showPrices);
			}
			unset($discountResult);

			$result['TOTAL_PRICE'] = $orderableBasket->getPrice();
			$result['NUM_PRODUCTS'] = $orderableBasket->count();

			/** @var Sale\BasketItem $basketItem */
			foreach ($orderableBasket as $basketItem)
			{
				$basketItemList[] = $this->getItemData($basketItem);
			}
			unset($item, $basketItem);
		}
		unset($orderableBasket);

		/** @var Sale\BasketItem $basketItem */
		foreach ($fullBasket as $basketItem)
		{
			$skip = false;
			if ($basketItem->canBuy())
			{
				if (
					!$basketItem->isDelay()
					|| ($basketItem->isDelay() && $this->arParams['SHOW_DELAY'] == 'N')
				)
					$skip = true;
			}
			else
			{
				if ($this->arParams['SHOW_NOTAVAIL'] == 'N')
					$skip = true;
			}
			if ($skip)
				continue;
			$item = $this->getItemData($basketItem);
			$basketItemList[] = $item;
		}
		unset($basketItem, $fullBasket);

		if (empty($basketItemList))
			return $result;

		$this->loadProductPictures($basketItemList);

		if ($this->arParams['SHOW_DELAY'] != 'N')
			$result['CATEGORIES']['DELAY'] = [];
		if ($this->arParams['SHOW_NOTAVAIL'] != 'N')
		{
			$result['CATEGORIES']['SUBSCRIBE'] = [];
			$result['CATEGORIES']['NOTAVAIL'] = [];
		}

		foreach ($basketItemList as $item)
		{
			if ($item['CAN_BUY'] == 'Y')
			{
				if ($item['DELAY'] == 'Y')
					$result['CATEGORIES']['DELAY'][] = $item;
				else
					$result['CATEGORIES']['READY'][] = $item;
			}
			else
			{
				if ($item['SUBSCRIBE'] == 'Y')
					$result['CATEGORIES']['SUBSCRIBE'][] = $item;
				else
					$result['CATEGORIES']['NOTAVAIL'][] = $item;
			}
		}
		unset($basketItemList, $item);

		foreach (array_keys($result['CATEGORIES']) as $index)
		{
			if (empty($result['CATEGORIES'][$index]))
				unset($result['CATEGORIES'][$index]);
		}
		unset($index);

		return $result;
	}

	protected function loadProductPictures(array &$basketItemList)
	{
		if ($this->arParams['SHOW_IMAGE'] == 'Y' && $this->bUseCatalog)
		{
			$elementIdList = array();
			$productMap = array();
			foreach ($basketItemList as $item)
			{
				if ((string)$item['MODULE'] !== 'catalog')
					continue;
				$elementIdList[$item['PRODUCT_ID']] = $item['PRODUCT_ID'];
			}
			unset($item);
			if (!empty($elementIdList))
			{
				$productList = \CCatalogSku::getProductList($elementIdList);
				if (!empty($productList))
				{
					foreach ($productList as $offerId => $data)
						$productMap[$offerId] = $data['ID'];
					unset($offerId, $data);
				}
				unset($productList);
			}
			$this->setImgSrc($basketItemList, $elementIdList, $productMap);
			unset($productMap, $elementIdList);
		}
	}

	private function setImgSrc(&$arBasketItems, $arElementId, $arSku2Parent)
	{
		//TODO: need refactoring
		$arImgFields = array ("PREVIEW_PICTURE", "DETAIL_PICTURE", "PROPERTY_MORE_PHOTO");
		$arProductData = getProductProps(array_merge($arElementId, $arSku2Parent), array_merge(array("ID"), $arImgFields));

		foreach ($arBasketItems as &$arItem)
		{
			if (array_key_exists($arItem["PRODUCT_ID"], $arProductData) && is_array($arProductData[$arItem["PRODUCT_ID"]]))
			{
				foreach ($arProductData[$arItem["PRODUCT_ID"]] as $key => $value)
				{
					if (strpos($key, "PROPERTY_") !== false || in_array($key, $arImgFields))
						$arItem[$key] = $value;
				}
			}

			if (array_key_exists($arItem["PRODUCT_ID"], $arSku2Parent)) // if sku element doesn't have value of some property - we'll show parent element value instead
			{
				foreach ($arImgFields as $field) // fields to be filled with parents' values if empty
				{
					$fieldVal = (in_array($field, $arImgFields)) ? $field : $field."_VALUE";
					$parentId = $arSku2Parent[$arItem["PRODUCT_ID"]];

					if ((!isset($arItem[$fieldVal]) || (isset($arItem[$fieldVal]) && strlen($arItem[$fieldVal]) == 0))
						&& (isset($arProductData[$parentId][$fieldVal]) && !empty($arProductData[$parentId][$fieldVal]))) // can be array or string
					{
						$arItem[$fieldVal] = $arProductData[$parentId][$fieldVal];
					}
				}
			}

			$arItem["PICTURE_SRC"] = "";
			$arImage = null;
			if (isset($arItem["PREVIEW_PICTURE"]) && intval($arItem["PREVIEW_PICTURE"]) > 0)
				$arImage = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
			elseif (isset($arItem["DETAIL_PICTURE"]) && intval($arItem["DETAIL_PICTURE"]) > 0)
				$arImage = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
			if ($arImage)
			{
				$arFileTmp = CFile::ResizeImageGet(
					$arImage,
					array("width" => $this->arParams['MAX_IMAGE_SIZE'], "height" => $this->arParams['MAX_IMAGE_SIZE']),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);
				$arItem["PICTURE_SRC"] = $arFileTmp["src"];
			}
		}
	}

	/**
	 * @param \Bitrix\Main\Event $event
	 *
	 * @return \Bitrix\Main\EventResult
	 */
	public function onSaleBasketItemEntitySaved(\Bitrix\Main\Event $event)
	{
		return \Bitrix\Sale\BasketComponentHelper::onSaleBasketItemEntitySaved($event);
	}

	/**
	 * @param \Bitrix\Main\Event $event
	 *
	 * @return \Bitrix\Main\EventResult
	 */
	public function onSaleBasketItemDeleted(\Bitrix\Main\Event $event)
	{
		return \Bitrix\Sale\BasketComponentHelper::onSaleBasketItemDeleted($event);
	}

	/**
	 * @param \Bitrix\Sale\Basket $basket
	 *
	 * @return float
	 */
	protected static function getActualBasketPrice(\Bitrix\Sale\Basket $basket)
	{
		$basketPrice = 0;

		/** @var \Bitrix\Sale\Basket $basketOrderable */
		$basketOrderable = $basket->getOrderableItems();

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basketOrderable as $basketItem)
		{
			if (intval($basketItem->getField('ORDER_ID')) > 0)
			{
				continue;
			}

			if (!$basketItem->isBundleChild())
			{
				$basketPrice += $basketItem->getFinalPrice();
			}
		}

		return $basketPrice;
	}

	/**
	 * @param \Bitrix\Sale\Basket $basket
	 *
	 * @return float
	 */
	protected static function getActualBasketQuantity(\Bitrix\Sale\Basket $basket)
	{
		$basketQuantity = 0;

		/** @var \Bitrix\Sale\Basket $basketOrderable */
		$basketOrderable = $basket->getOrderableItems();
		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basketOrderable as $basketItem)
		{
			if (intval($basketItem->getField('ORDER_ID')) > 0)
			{
				continue;
			}

			if (!$basketItem->isBundleChild())
			{
				$basketQuantity++;
			}
		}

		return $basketQuantity;
	}

	protected function getFuserId()
	{
		if ($this->currentFuser === null)
			$this->loadCurrentFuser();

		return $this->currentFuser;
	}

	protected function loadCurrentFuser()
	{
		$this->currentFuser = Sale\Fuser::getId(true);
	}

	protected function getBasketStorage()
	{
		if (!isset($this->basketStorage))
		{
			$this->basketStorage = Sale\Basket\Storage::getInstance($this->currentFuser, $this->getSiteId());
		}

		return $this->basketStorage;
	}

	private function makeCompatibleArray(&$array)
	{
		if (empty($array) || !is_array($array))
			return;

		$arr = array();
		foreach ($array as $key => $value)
		{
			if (is_array($value) || preg_match("/[;&<>\"]/", $value))
			{
				$arr[$key] = htmlspecialcharsEx($value);
			}
			else
			{
				$arr[$key] = $value;
			}

			$arr['~'.$key] = $value;
		}

		$array = $arr;
	}

	private function getItemData(Sale\BasketItem $item)
	{
		$result = $item->getFieldValues();
		$this->makeCompatibleArray($result);
		$result['PRODUCT_ID'] = (int)$result['PRODUCT_ID'];
		$result['QUANTITY'] = $item->getQuantity();
		$result['MEASURE_NAME'] = (string)$result['MEASURE_NAME'];
		if ($result['MEASURE_NAME'] == '')
			$result['MEASURE_NAME'] = GetMessage('TSB1_MEASURE_NAME');
		$result['PRICE'] = Sale\PriceMaths::roundPrecision($result['PRICE']);
		$result['BASE_PRICE'] = Sale\PriceMaths::roundPrecision($result['BASE_PRICE']);
		$result['DISCOUNT_PRICE'] = Sale\PriceMaths::roundPrecision($result['DISCOUNT_PRICE']);
		$result['SUM_VALUE'] = $result['PRICE'] * $result['QUANTITY'];

		$result['SUM'] = \CCurrencyLang::CurrencyFormat($result['SUM_VALUE'], $result['CURRENCY'], true);
		$result['PRICE_FMT'] = \CCurrencyLang::CurrencyFormat($result['PRICE'], $result['CURRENCY'], true);
		$result['FULL_PRICE'] = \CCurrencyLang::CurrencyFormat($result['BASE_PRICE'], $result['CURRENCY'], true);

		// unused fields from \CSaleDiscount::DoProcessOrder - compatibility
		$result['PRICE_FORMATED'] = $result['PRICE_FMT'];
		$result['DISCOUNT_PRICE_PERCENT'] = Sale\Discount::calculateDiscountPercent(
			$result['BASE_PRICE'],
			$result['DISCOUNT_PRICE']
		);
		$result['DISCOUNT_PRICE_PERCENT_FORMATED'] = $result['DISCOUNT_PRICE_PERCENT'].'%';

		return $result;
	}
}

// Compatibility
if (!function_exists('BasketNumberWordEndings'))
{
	function BasketNumberWordEndings($num, $lang = false, $arEnds = false)
	{
		if ($lang===false)
			$lang = LANGUAGE_ID;

		if ($arEnds===false)
			$arEnds = array(GetMessage("TSB1_WORD_OBNOVL_END1"), GetMessage("TSB1_WORD_OBNOVL_END2"), GetMessage("TSB1_WORD_OBNOVL_END3"), GetMessage("TSB1_WORD_OBNOVL_END4"));

		if ($lang=="ru")
		{
			if (strlen($num)>1 && substr($num, strlen($num)-2, 1)=="1")
			{
				return $arEnds[0];
			}
			else
			{
				$c = IntVal(substr($num, strlen($num)-1, 1));
				if ($c==0 || ($c>=5 && $c<=9))
					return $arEnds[1];
				elseif ($c==1)
					return $arEnds[2];
				else
					return $arEnds[3];
			}
		}
		elseif ($lang=="en")
		{
			if (IntVal($num)>1)
			{
				return "s";
			}
			return "";
		}
		else
		{
			return "";
		}
	}
}