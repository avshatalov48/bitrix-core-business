<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main,
	Bitrix\Sale;

class SaleBasketLineComponent extends CBitrixComponent
{
	protected $bUseCatalog = null;
	protected $readyForOrderFilter = array("CAN_BUY" => "Y", "DELAY" => "N", "SUBSCRIBE" => "N");
	protected $disableUseBasket = false;

	protected $currentFuser = null;

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

		// ajax

		if ($arParams['AJAX'] != 'Y')
			$arParams['AJAX'] = 'N';

		return $arParams;
	}

	protected function getUserFilter()
	{
		$fUserID = (int)$this->currentFuser;
		return ($fUserID > 0)
			? array("FUSER_ID" => $fUserID, "LID" => SITE_ID, "ORDER_ID" => "NULL")
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
				$this->arResult["TOTAL_PRICE"] = \Bitrix\Sale\BasketComponentHelper::getFUserBasketPrice($this->currentFuser, SITE_ID);
			}

			$this->arResult["NUM_PRODUCTS"] = \Bitrix\Sale\BasketComponentHelper::getFUserBasketQuantity($this->currentFuser, SITE_ID);
		}

		if($this->arParams["SHOW_TOTAL_PRICE"] == "Y")
			$this->arResult["TOTAL_PRICE"] = CCurrencyLang::CurrencyFormat($this->arResult["TOTAL_PRICE"], CSaleLang::GetLangCurrency(SITE_ID), true);

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

	private function calculateOrder($arBasketItems)
	{
		$totalPrice = 0;
		$totalWeight = 0;

		foreach ($arBasketItems as $arItem)
		{
			$totalPrice += $arItem["PRICE"] * $arItem["QUANTITY"];
			$totalWeight += $arItem["WEIGHT"] * $arItem["QUANTITY"];
		}

		$arOrder = array(
			'SITE_ID' => SITE_ID,
			'ORDER_PRICE' => $totalPrice,
			'ORDER_WEIGHT' => $totalWeight,
			'BASKET_ITEMS' => $arBasketItems
		);

		if (is_object($GLOBALS["USER"]))
		{
			$arOrder['USER_ID'] = $GLOBALS["USER"]->GetID();
			$arErrors = array();
			CSaleDiscount::DoProcessOrder($arOrder, array(), $arErrors);
		}

		return $arOrder;
	}

	private function getProducts()
	{
		if (!($arFilter = $this->getUserFilter()))
			return array();

		if ($this->arParams['SHOW_NOTAVAIL'] == 'N')
			$arFilter['CAN_BUY'] = 'Y';
		if ($this->arParams['SHOW_DELAY'] == 'N')
			$arFilter['DELAY'] = 'N';

		$dbItems = CSaleBasket::GetList(
			array("NAME" => "ASC", "ID" => "ASC"),
			$arFilter,
			false,
			false,
			array(
				"ID", "NAME", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY",
				"PRICE", "WEIGHT", "DETAIL_PAGE_URL", "CURRENCY", "VAT_RATE", "CATALOG_XML_ID", "MEASURE_NAME",
				"PRODUCT_XML_ID", "SUBSCRIBE", "DISCOUNT_PRICE", "PRODUCT_PROVIDER_CLASS", "TYPE", "SET_PARENT_ID", "BASE_PRICE",
				"PRODUCT_PRICE_ID", 'CUSTOM_PRICE'
			)
		);

		$arBasketItems = array();
		$arElementId = array();
		$arSku2Parent = array();

		while ($arItem = $dbItems->GetNext(true, false))
		{
			if (CSaleBasketHelper::isSetItem($arItem))
				continue;

			if (!isset($arItem['BASE_PRICE']) || (float)$arItem['BASE_PRICE'] <= 0)
				$arItem['BASE_PRICE'] = $arItem['PRICE'] + $arItem['DISCOUNT_PRICE'];
			$arItem["PRICE_FMT"] = CCurrencyLang::CurrencyFormat($arItem["PRICE"], $arItem["CURRENCY"], true);
			$arItem["FULL_PRICE"] = CCurrencyLang::CurrencyFormat($arItem["BASE_PRICE"], $arItem["CURRENCY"], true);
			$arItem['QUANTITY'] += 0; // remove excessive zeros after period
			if (!$arItem['MEASURE_NAME'])
				$arItem['MEASURE_NAME'] = GetMessage('TSB1_MEASURE_NAME');

			if ($this->arParams['SHOW_IMAGE'] == 'Y' && $this->bUseCatalog && $arItem["MODULE"] == 'catalog')
			{
				$arElementId[] = $arItem["PRODUCT_ID"];
				$arParent = CCatalogSku::GetProductInfo($arItem["PRODUCT_ID"]);
				if ($arParent)
				{
					$arElementId[] = $arParent["ID"];
					$arSku2Parent[$arItem["PRODUCT_ID"]] = $arParent["ID"];
				}
			}

			$arBasketItems[] = $arItem;
		}

		$arResult = array(
			'CATEGORIES' => array(),
			'TOTAL_PRICE' => 0
		);

		if ($arBasketItems)
		{
			if ($this->arParams['SHOW_IMAGE'] == 'Y')
				$this->setImgSrc($arBasketItems, $arElementId, $arSku2Parent);

			$arResult["CATEGORIES"] = array(
				"READY" => array(),
				"DELAY" => array(),
				"SUBSCRIBE" => array(),
				"NOTAVAIL" => array()
			);

			// fill item arrays for templates
			foreach ($arBasketItems as $arItem)
			{
				if ($arItem["CAN_BUY"] == "Y")
				{
					if ($arItem["DELAY"] == "Y")
						$arResult["CATEGORIES"]["DELAY"][] = $arItem;
					else
						$arResult["CATEGORIES"]["READY"][] = $arItem;
				}
				else
				{
					if ($arItem["SUBSCRIBE"] == "Y")
						$arResult["CATEGORIES"]["SUBSCRIBE"][] = $arItem;
					else
						$arResult["CATEGORIES"]["NOTAVAIL"][] = $arItem;
				}
			}

			if ($this->arParams['SHOW_PRICE'] == 'Y' ||
				$this->arParams['SHOW_SUMMARY'] == 'Y' ||
				$this->arParams['SHOW_TOTAL_PRICE'] == 'Y')
			{
				$arOrder = $this->calculateOrder($arResult["CATEGORIES"]["READY"]);
				$arResult["CATEGORIES"]["READY"] = $arOrder['BASKET_ITEMS'];

				if (!empty($arResult["CATEGORIES"]["DELAY"]) && is_array($arResult["CATEGORIES"]["DELAY"]))
				{
					Sale\DiscountCouponsManager::freezeCouponStorage();
					$orderDelay = $this->calculateOrder($arResult["CATEGORIES"]["DELAY"]);
					Sale\DiscountCouponsManager::unFreezeCouponStorage();
					$arResult["CATEGORIES"]["DELAY"] = $orderDelay['BASKET_ITEMS'];
				}

				foreach ($arResult["CATEGORIES"]["READY"] as &$arItem)
				{
					$arItem["SUM"] = CCurrencyLang::CurrencyFormat($arItem["PRICE"] * $arItem["QUANTITY"], $arItem["CURRENCY"], true);
					$arItem["PRICE_FMT"] = CCurrencyLang::CurrencyFormat($arItem["PRICE"], $arItem["CURRENCY"], true);
					$arItem["FULL_PRICE"] = CCurrencyLang::CurrencyFormat($arItem["BASE_PRICE"], $arItem["CURRENCY"], true);
				}
				unset($arItem);

				if (!empty($arResult["CATEGORIES"]["DELAY"]) && is_array($arResult["CATEGORIES"]["DELAY"]))
				{
					foreach ($arResult["CATEGORIES"]["DELAY"] as &$arItem)
					{
						$arItem["SUM"] = CCurrencyLang::CurrencyFormat($arItem["PRICE"] * $arItem["QUANTITY"], $arItem["CURRENCY"], true);
						$arItem["PRICE_FMT"] = CCurrencyLang::CurrencyFormat($arItem["PRICE"], $arItem["CURRENCY"], true);
						$arItem["FULL_PRICE"] = CCurrencyLang::CurrencyFormat($arItem["BASE_PRICE"], $arItem["CURRENCY"], true);
					}
					unset($arItem);
				}

				$arResult["TOTAL_PRICE"] = $arOrder['ORDER_PRICE'];
			}
		}

		return array(
			'NUM_PRODUCTS' => count($arResult["CATEGORIES"]["READY"]),
			'TOTAL_PRICE'  => $arResult["TOTAL_PRICE"],
			'CATEGORIES'   => $arResult["CATEGORIES"],
		);
	}

	private function setImgSrc(&$arBasketItems, $arElementId, $arSku2Parent)
	{
		$arImgFields = array ("PREVIEW_PICTURE", "DETAIL_PICTURE");
		$arProductData = getProductProps($arElementId, array_merge(array("ID"), $arImgFields));

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
					array("width" => "70", "height" =>"70"),
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

	protected function loadCurrentFuser()
	{
		$this->currentFuser = Sale\Fuser::getId(true);
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