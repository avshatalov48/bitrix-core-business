<?php

use Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Class CCatalogViewedProductsComponent
 *
 * No longer used by internal code and not recommended. Use "catalog.products.viewed" instead.
 *
 * @deprecated deprecated since catalog 17.0.5
 * @use \CatalogProductsViewedComponent
 */
class CCatalogViewedProductsComponent extends CBitrixComponent
{
	const ACTION_BUY = 'BUY';
	const ACTION_ADD_TO_BASKET = 'ADD2BASKET';
	const ACTION_SUBSCRIBE = 'SUBSCRIBE_PRODUCT';
	const ACTION_ADD_TO_COMPARE_LIST = 'ADD_TO_COMPARE_LIST';

	/**
	 * Primary data - viewed product.
	 * @var array[]
	 */
	protected $items = array();

	/**
	 * Viewed products ids.
	 * @var integer[]
	 */
	private $productIds = array();

	/**
	 * Helper array map: array("SKU_ID" => "PRODUCT_ID", ...)
	 * @var array
	 */
	private $productIdsMap = array();

	/**
	 * Filter to fetch items.
	 * Used in CIBlockElement::getList()
	 * @var string[]
	 */
	protected $filter = array();

	/**
	 * Select fields for items.
	 * Used in CIBlockElement::getList()
	 * @var string[]
	 */
	private $selectFields = array();

	/**
	 * Wether module Sale included?
	 * @var bool
	 */
	protected $isSale = true;

	/**
	 * Wether module Currency included?
	 * @var bool
	 */
	protected $isCurrency = true;

	/**
	 * Errors list.
	 * @var string[]
	 */
	protected $errors = array();

	/**
	 * Warnings list.
	 * @var string[]
	 */
	protected $warnings = array();

	/**
	 * Util data for template.
	 * @var array
	 */
	protected $data = array();

	/**
	 * Items separate by iblocks
	 *
	 * @var array
	 */
	protected $iblockItems = array();

	/**
	 * Link to items.
	 *
	 * @var array
	 */
	protected $linkItems = array();

	/**
	 * Url templates for items
	 *
	 * @var array
	 */
	protected $urlTemplates = array();

	protected $needItemProperties = array();

	protected $oldPriceFields = [];

	/**
	 * Load language file.
	 */
	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	/**
	 * Is AJAX Request?
	 * @return bool
	 */
	protected function isAjax()
	{
		return isset($_REQUEST['ajax_basket']) && $_REQUEST['ajax_basket'] == 'Y';
	}

	protected function getUserId()
	{
		if (isset($this->arParams['USER_ID']))
			return $this->arParams['USER_ID'];

		global $USER;
		if (isset($USER) && $USER instanceof CUser)
			return (int)$USER->getId();

		return 0;
	}

	/**
	 * Return product quantity from request string
	 * @return integer
	 */
	protected function getProductQuantityFromRequest()
	{
		$quantity = 0;
		if ($this->arParams['USE_PRODUCT_QUANTITY'])
		{
			if (isset($_REQUEST[$this->arParams['PRODUCT_QUANTITY_VARIABLE']]))
				$quantity = (float)$_REQUEST[$this->arParams['PRODUCT_QUANTITY_VARIABLE']];
		}
		return $quantity;
	}

	/**
	 * Return product product properties to add in basket
	 * @return array
	 */
	protected function getProductPropertiesFromRequest()
	{
		$values = array();
		if (
			isset($_REQUEST[$this->arParams['PRODUCT_PROPS_VARIABLE']])
			&& is_array($_REQUEST[$this->arParams['PRODUCT_PROPS_VARIABLE']])
		)
			$values = $_REQUEST[$this->arParams["PRODUCT_PROPS_VARIABLE"]];
		return $values;
	}

	/**
	 * Process buy action.
	 */
	protected function processBuyAction()
	{
		global $APPLICATION;
		if (
			!isset($_REQUEST[$this->arParams['ACTION_VARIABLE']])
			|| $_REQUEST[$this->arParams['ACTION_VARIABLE']] != self::ACTION_BUY
		)
			return;

		$productID = 0;
		if (isset($_REQUEST[$this->arParams['PRODUCT_ID_VARIABLE']]))
			$productID = (int)$_REQUEST[$this->arParams['PRODUCT_ID_VARIABLE']];
		if ($productID <= 0)
			throw new Main\SystemException(Loc::getMessage('CVP_ACTION_PRODUCT_ID_REQUIRED'));

		$this->addProductToBasket($productID, $this->getProductQuantityFromRequest(), $this->getProductPropertiesFromRequest());

		if (!$this->isAjax())
		{
			LocalRedirect($this->arParams['BASKET_URL']);
		}
		else
		{
			$APPLICATION->restartBuffer();
			header('Content-Type: application/json');
			echo Main\Web\Json::encode(array('STATUS' => 'OK', 'MESSAGE' => ''));
			die();
		}
	}

	/**
	 * Process buy action.
	 */
	protected function processAddToBasketAction()
	{
		global $APPLICATION;
		if (
			!isset($_REQUEST[$this->arParams['ACTION_VARIABLE']])
			|| $_REQUEST[$this->arParams['ACTION_VARIABLE']] != self::ACTION_ADD_TO_BASKET
		)
			return;

		$productID = 0;
		if (isset($_REQUEST[$this->arParams["PRODUCT_ID_VARIABLE"]]))
			$productID = (int)$_REQUEST[$this->arParams["PRODUCT_ID_VARIABLE"]];
		if ($productID <= 0)
			throw new Main\SystemException(Loc::getMessage("CVP_ACTION_PRODUCT_ID_REQUIRED"));

		$this->addProductToBasket($productID, $this->getProductQuantityFromRequest(), $this->getProductPropertiesFromRequest());

		if (!$this->isAjax())
		{
			LocalRedirect($APPLICATION->GetCurPageParam('', array($this->arParams['PRODUCT_ID_VARIABLE'], $this->arParams['ACTION_VARIABLE'])));
		}
		else
		{
			$APPLICATION->restartBuffer();
			header('Content-Type: application/json');
			echo Main\Web\Json::encode(array('STATUS' => 'OK', 'MESSAGE' => Loc::getMessage("CVP_PRODUCT_ADDED")));
			die();
		}
	}

	/**
	 * Process buy action.
	 */
	protected function processSubscribeAction()
	{
		global $APPLICATION;
		if (
			!isset($_REQUEST[$this->arParams['ACTION_VARIABLE']])
			|| $_REQUEST[$this->arParams['ACTION_VARIABLE']] != self::ACTION_SUBSCRIBE
		)
			return;

		$productID = 0;
		if (isset($_REQUEST[$this->arParams["PRODUCT_ID_VARIABLE"]]))
			$productID = (int)$_REQUEST[$this->arParams["PRODUCT_ID_VARIABLE"]];
		if ($productID <= 0)
			throw new Main\SystemException(Loc::getMessage("CVP_ACTION_PRODUCT_ID_REQUIRED"));

		$rewriteFields = array('SUBSCRIBE' => 'Y', 'CAN_BUY' => 'N');

		$this->addProductToBasket($productID, $this->getProductQuantityFromRequest(), $this->getProductPropertiesFromRequest(), $rewriteFields);

		if (!$this->isAjax())
		{
			LocalRedirect($APPLICATION->GetCurPageParam("", array($this->arParams["PRODUCT_ID_VARIABLE"], $this->arParams["ACTION_VARIABLE"])));
		}
		else
		{
			$APPLICATION->restartBuffer();
			header('Content-Type: application/json');
			echo Main\Web\Json::encode(array('STATUS' => 'OK', 'MESSAGE' => Loc::getMessage("CVP_PRODUCT_SUBSCIBED")));
			die();
		}
	}

	/**
	 * Process request actions list
	 * @return void
	 */
	protected function doActionsList()
	{
		$this->processBuyAction();
		$this->processAddToBasketAction();
		$this->processSubscribeAction();
	}

	/**
	 * Process incoming request.
	 * @return void
	 */
	protected function processRequest()
	{
		global $APPLICATION;
		try
		{
			$this->doActionsList();
		}
		catch (Main\SystemException $e)
		{
			if ($this->isAjax())
			{
				$APPLICATION->restartBuffer();
				header('Content-Type: application/json');
				echo Main\Web\Json::encode(array('STATUS' => 'ERROR', 'MESSAGE' => $e->getMessage()));
				die();
			}
			else
			{
				$this->warnings[] = Main\Text\HtmlFilter::encode($e->getMessage());
			}
		}
	}

	/**
	 * Process Puy Product.
	 *
	 * @param int $productID
	 * @param float $quantity
	 * @param array $values
	 * @param array $arRewriteFields
	 * @throws void|Bitrix\Main\SystemException
	 */
	protected function addProductToBasket($productID, $quantity, $values = array(), $arRewriteFields = array())
	{
		$productProperties = array();
		$productID = (int)$productID;
		$intProductIBlockID = (int)CIBlockElement::GetIBlockByID($productID);

		if ($intProductIBlockID > 0)
		{
			$productCatalogInfo = CCatalogSKU::getInfoByIblock($intProductIBlockID);
			$isOffer = CCatalogSKU::TYPE_OFFERS == $productCatalogInfo['CATALOG_TYPE'];

			if ($this->arParams['ADD_PROPERTIES_TO_BASKET'] == 'Y')
			{
				// Is not offer
				if (!$isOffer)
				{
					// Props not empty
					if (!empty($this->arParams['CART_PROPERTIES'][$intProductIBlockID]))
					{
						$productProperties = CIBlockPriceTools::CheckProductProperties(
							$intProductIBlockID,
							$productID,
							$this->arParams['CART_PROPERTIES'][$intProductIBlockID],
							$values,
							$this->arParams['PARTIAL_PRODUCT_PROPERTIES'] == 'Y'
						);

						if (!is_array($productProperties))
						{
							throw new Main\SystemException(Loc::getMessage("CVP_PARTIAL_BASKET_PROPERTIES_ERROR"));
						}
					}
				}
				else
				{
					if (!empty($this->arParams['CART_PROPERTIES'][$intProductIBlockID]))
					{
						$productProperties = CIBlockPriceTools::GetOfferProperties(
							$productID,
							$productCatalogInfo['PRODUCT_IBLOCK_ID'],
							$this->arParams['CART_PROPERTIES'][$intProductIBlockID]
						);
					}
				}
			}

			if (0 >= $quantity)
			{
				$rsRatios = CCatalogMeasureRatio::getList(
					array(),
					array('PRODUCT_ID' => $productID),
					false,
					false,
					array('PRODUCT_ID', 'RATIO')
				);
				if ($arRatio = $rsRatios->Fetch())
				{
					$intRatio = (int)$arRatio['RATIO'];
					$dblRatio = (float)$arRatio['RATIO'];
					$quantity = ($dblRatio > $intRatio ? $dblRatio : $intRatio);
				}
			}
			if (0 >= $quantity)
				$quantity = 1;
		}
		else // Cannot  define product catalog
		{
			throw new Main\SystemException(Loc::getMessage('CVP_CATALOG_PRODUCT_NOT_FOUND') . ".");
		}

		if (!Add2BasketByProductID($productID, $quantity, $arRewriteFields, $productProperties))
			throw new Main\SystemException(Loc::getMessage("CVP_CATALOG_ERROR2BASKET") . ".");
	}

	/**
	 * Check Required Modules
	 * @throws Exception
	 */
	protected function checkModules()
	{
		if (!Main\Loader::includeModule('catalog'))
			throw new Main\SystemException(Loc::getMessage('CVP_CATALOG_MODULE_NOT_INSTALLED'));
		$this->isCurrency = true;
		if (!Main\Loader::includeModule('sale'))
			$this->isSale = false;
	}

	/**
	 * Prepare Component Params.
	 *
	 * @param array $params			Component parameters.
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		if (isset($params['CUSTOM_SITE_ID']))
		{
			$this->setSiteId($params['CUSTOM_SITE_ID']);
		}

		$params["DETAIL_URL"] = trim($params["DETAIL_URL"]);
		$params["BASKET_URL"] = trim($params["BASKET_URL"]);

		$params["CACHE_TIME"] = intval($params["CACHE_TIME"]);
		if ($params["CACHE_TIME"] <= 0)
			$params["CACHE_TIME"] = 3600;

		if ($params["BASKET_URL"] === '')
			$params["BASKET_URL"] = "/personal/basket.php";

		$params['ACTION_VARIABLE'] = $this->prepareActionVariable($params);

		$params["PRODUCT_ID_VARIABLE"] = trim($params["PRODUCT_ID_VARIABLE"]);
		if ($params["PRODUCT_ID_VARIABLE"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $params["PRODUCT_ID_VARIABLE"]))
			$params["PRODUCT_ID_VARIABLE"] = "id";

		$params["USE_PRODUCT_QUANTITY"] = $params["USE_PRODUCT_QUANTITY"] === "Y";
		$params["PRODUCT_QUANTITY_VARIABLE"] = trim($params["PRODUCT_QUANTITY_VARIABLE"]);
		if ($params["PRODUCT_QUANTITY_VARIABLE"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $params["PRODUCT_QUANTITY_VARIABLE"]))
			$params["PRODUCT_QUANTITY_VARIABLE"] = "quantity";

		$params["PRODUCT_PROPS_VARIABLE"] = trim($params["PRODUCT_PROPS_VARIABLE"]);
		if ($params["PRODUCT_PROPS_VARIABLE"] === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $params["PRODUCT_PROPS_VARIABLE"]))
			$params["PRODUCT_PROPS_VARIABLE"] = "prop";

		$params['ADD_PROPERTIES_TO_BASKET'] = (isset($params['ADD_PROPERTIES_TO_BASKET']) && $params['ADD_PROPERTIES_TO_BASKET'] == 'N' ? 'N' : 'Y');
		$params['PARTIAL_PRODUCT_PROPERTIES'] = (isset($params['PARTIAL_PRODUCT_PROPERTIES']) && $params['PARTIAL_PRODUCT_PROPERTIES'] === 'Y' ? 'Y' : 'N');
		$params["SET_TITLE"] = $params["SET_TITLE"] != "N";
		$params["DISPLAY_COMPARE"] = $params["DISPLAY_COMPARE"] == "Y";

		$params["PAGE_ELEMENT_COUNT"] = intval($params["PAGE_ELEMENT_COUNT"]);
		if ($params["PAGE_ELEMENT_COUNT"] <= 0)
			$params["PAGE_ELEMENT_COUNT"] = 20;
		$params["LINE_ELEMENT_COUNT"] = intval($params["LINE_ELEMENT_COUNT"]);
		if ($params["LINE_ELEMENT_COUNT"] <= 0)
			$params["LINE_ELEMENT_COUNT"] = 3;

		$params["OFFERS_LIMIT"] = intval($params["OFFERS_LIMIT"]);
		if ($params["OFFERS_LIMIT"] < 0)
			$params["OFFERS_LIMIT"] = 5;
		elseif ($params['OFFERS_LIMIT'] == 0)
			$params["OFFERS_LIMIT"] = PHP_INT_MAX;

		$params['MESS_BTN_BUY'] = trim($params['MESS_BTN_BUY']);
		$params['MESS_BTN_ADD_TO_BASKET'] = trim($params['MESS_BTN_ADD_TO_BASKET']);
		$params['MESS_BTN_SUBSCRIBE'] = trim($params['MESS_BTN_SUBSCRIBE']);
		$params['MESS_BTN_DETAIL'] = trim($params['MESS_BTN_DETAIL']);
		$params['MESS_NOT_AVAILABLE'] = trim($params['MESS_NOT_AVAILABLE']);

		if ('Y' != $params['SHOW_DISCOUNT_PERCENT'])
			$params['SHOW_DISCOUNT_PERCENT'] = 'N';
		if ('Y' != $params['SHOW_OLD_PRICE'])
			$params['SHOW_OLD_PRICE'] = 'N';
		if ('Y' != $params['PRODUCT_SUBSCRIPTION'])
			$params['PRODUCT_SUBSCRIPTION'] = 'N';

		$params['PROPERTY_CODE'] = array();
		$params['ADDITIONAL_PICT_PROP'] = array();
		$params['LABEL_PROP'] = array();
		$params['OFFER_TREE_PROPS'] = array();
		$params['CART_PROPERTIES'] = array();
		$params['SHOW_PRODUCTS'] = array();

		foreach ($params as $name => $prop)
		{
			// Property code
			if (preg_match("/^PROPERTY_CODE_(\d+)$/", $name, $arMatches))
			{
				$iBlockID = (int)$arMatches[1];
				if ($iBlockID <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
						if ($v === "")
							unset($params[$name][$k]);
					$params['PROPERTY_CODE'][$iBlockID] = $params[$name];
				}
				unset($params[$arMatches[0]]);
			} // Additional Picture property
			elseif (preg_match("/^ADDITIONAL_PICT_PROP_(\d+)$/", $name, $arMatches))
			{
				$iBlockID = (int)$arMatches[1];
				if ($iBlockID <= 0)
					continue;

				if ($params[$name] != "" && $params[$name] != "-")
				{
					$params['ADDITIONAL_PICT_PROP'][$iBlockID] = $params[$name];
				}
				unset($params[$arMatches[0]]);
			} //
			elseif (preg_match("/^LABEL_PROP_(\d+)$/", $name, $arMatches))
			{
				$iBlockID = (int)$arMatches[1];
				if ($iBlockID <= 0)
					continue;

				if (is_array($params[$name]))
				{
					$value = '';
					foreach ($params[$name] as $rawValue)
					{
						if ($rawValue != '' && $rawValue != '-')
						{
							$value = $rawValue;
							break;
						}
					}
				}
				else
				{
					$value = $params[$name];
				}

				if ($value != "" && $value != "-")
				{
					$params['LABEL_PROP'][$iBlockID] = $value;
				}
				unset($value);

				unset($params[$arMatches[0]]);
			} // Offer Group property
			elseif (preg_match("/^OFFER_TREE_PROPS_(\d+)$/", $name, $arMatches))
			{
				$iBlockID = (int)$arMatches[1];
				if ($iBlockID <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
						if ($v == "" || $v == "-")
							unset($params[$name][$k]);
					$params['OFFER_TREE_PROPS'][$iBlockID] = $params[$name];
				}
				unset($params[$arMatches[0]]);
			} // Add to Basket Props
			elseif (preg_match("/^CART_PROPERTIES_(\d+)$/", $name, $arMatches))
			{
				$iBlockID = (int)$arMatches[1];
				if($iBlockID <= 0)
					continue;

				if (!empty($params[$name]) && is_array($params[$name]))
				{
					foreach ($params[$name] as $k => $v)
						if ($v == "" || $v == "-")
							unset($params[$name][$k]);
				}

				$params['CART_PROPERTIES'][$iBlockID] = $params[$name];
				unset($params[$arMatches[0]]);
			}
			// Show products
			elseif (preg_match("/^SHOW_PRODUCTS_(\d+)$/", $name, $arMatches))
			{
				$iBlockID = (int)$arMatches[1];
				if($iBlockID <= 0)
					continue;

				if ($params[$name] == "Y")
					$params['SHOW_PRODUCTS'][$iBlockID] = true;

				unset($params[$arMatches[0]]);
			}
		}

		if (!is_array($params["PRICE_CODE"]))
			$params["PRICE_CODE"] = array();

		$params["SHOW_PRICE_COUNT"] = intval($params["SHOW_PRICE_COUNT"]);
		if ($params["SHOW_PRICE_COUNT"] <= 0)
			$params["SHOW_PRICE_COUNT"] = 1;


		if (empty($params['HIDE_NOT_AVAILABLE']))
			$params['HIDE_NOT_AVAILABLE'] = 'N';
		elseif ('Y' != $params['HIDE_NOT_AVAILABLE'])
			$params['HIDE_NOT_AVAILABLE'] = 'N';

		if (empty($params['SHOW_IMAGE']))
			$params['SHOW_IMAGE'] = 'Y';

		if (empty($params['SHOW_NAME']))
			$params['SHOW_NAME'] = 'Y';

		$params["PRICE_VAT_INCLUDE"] = $params["PRICE_VAT_INCLUDE"] !== "N";
		$params['CONVERT_CURRENCY'] = (isset($params['CONVERT_CURRENCY']) && 'Y' == $params['CONVERT_CURRENCY'] ? 'Y' : 'N');
		$params['CURRENCY_ID'] = trim(strval($params['CURRENCY_ID']));
		if ('' == $params['CURRENCY_ID'])
		{
			$params['CONVERT_CURRENCY'] = 'N';
		}
		elseif ('N' == $params['CONVERT_CURRENCY'])
		{
			$params['CURRENCY_ID'] = '';
		}

		$params['SECTION_CODE'] = (isset($params['SECTION_CODE']) ? trim($params['SECTION_CODE']) : '');
		$params['SECTION_ID'] = (isset($params['SECTION_ID']) ? (int)$params['SECTION_ID'] : 0);
		$params['IBLOCK_ID'] = (isset($params['IBLOCK_ID']) ? (int)$params['IBLOCK_ID'] : 0);
		$params['SECTION_ELEMENT_ID'] = (isset($params['SECTION_ELEMENT_ID']) ? (int)$params['SECTION_ELEMENT_ID'] : 0);
		$params['SECTION_ELEMENT_CODE'] = (isset($params['SECTION_ELEMENT_CODE']) ? trim($params['SECTION_ELEMENT_CODE']) : '');

		if (isset($params['USER_ID']))
		{
			$params['USER_ID'] = (int)$params['USER_ID'];
			if ($params['USER_ID'] < 0)
				$params['USER_ID'] = 0;
		}

		return $params;
	}

	protected function getSectionIdByCode($sectionCode = "")
	{
		$sectionId = 0;

		$sectionCode = (string)$sectionCode;
		if ($sectionCode === '')
			return $sectionId;

		$sectionFilter = array(
			'@IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			'=IBLOCK.ACTIVE' => 'Y',
			'=CODE' => $sectionCode
		);

		$section = Iblock\SectionTable::getList(array(
			'select' => array('ID'),
			'filter' => $sectionFilter
		))->fetch();
		if (!empty($section))
			$sectionId = (int)$section['ID'];
		unset($section, $sectionFilter);

		return $sectionId;
	}

	protected function getSectionIdByElement($elementId, $elementCode = '')
	{
		$sectionId = 0;
		$elementId = (int)$elementId;
		$elementCode = (string)$elementCode;
		$filter = array('=IBLOCK_ID' => $this->arParams['IBLOCK_ID']);

		if ($elementId > 0)
			$filter['=ID'] = $elementId;
		elseif ($elementCode !== '')
			$filter['=CODE'] = $elementCode;
		else
			return $sectionId;

		$itemIterator = Iblock\ElementTable::getList(array(
			'select' => array('ID', 'IBLOCK_SECTION_ID'),
			'filter' => $filter
		));
		if ($item = $itemIterator->fetch())
			$sectionId = (int)$item['IBLOCK_SECTION_ID'];

		return $sectionId;
	}

	protected function getProductIds()
	{
		return null;
	}

	/**
	 * Return viewed product ids map.
	 *
	 * @return array("KEY" => "VALUE")
	 */
	protected function getProductIdsMap()
	{
		$map = array();

		if (!Main\Loader::includeModule('sale'))
			return array();

		$skipUserInit = false;
		if (!Catalog\Product\Basket::isNotCrawler())
			$skipUserInit = true;

		$basketUserId = (int)CSaleBasket::GetBasketUserID($skipUserInit);
		if ($basketUserId <= 0)
			return array();

		$useSectionFilter = ($this->arParams["SHOW_FROM_SECTION"] == "Y");

		$sectionSearch = $this->arParams["SECTION_ID"] > 0 || $this->arParams["SECTION_CODE"] !== '';
		$sectionByItemSearch = $this->arParams["SECTION_ELEMENT_ID"] > 0 || $this->arParams["SECTION_ELEMENT_CODE"] !== '';

		if ($useSectionFilter && ($sectionSearch || $sectionByItemSearch ))
		{
			if ($sectionSearch)
				$sectionId = ($this->arParams["SECTION_ID"] > 0) ? $this->arParams["SECTION_ID"] : $this->getSectionIdByCode($this->arParams["SECTION_CODE"]);
			else
				$sectionId = $this->getSectionIdByElement($this->arParams["SECTION_ELEMENT_ID"], $this->arParams["SECTION_ELEMENT_CODE"]);

			$map = Catalog\CatalogViewedProductTable::getProductSkuMap(
				$this->arParams['IBLOCK_ID'],
				$sectionId,
				$basketUserId,
				$this->arParams['SECTION_ELEMENT_ID'],
				$this->arParams['PAGE_ELEMENT_COUNT'],
				$this->arParams['DEPTH']
			);
		}
		else
		{
			$emptyProducts = array();
			$siteId = $this->getSiteId();

			$filter = array('=FUSER_ID' => $basketUserId, '=SITE_ID' => $siteId);
			if ($this->arParams['SECTION_ELEMENT_ID'] > 0)
				$filter['!=ELEMENT_ID'] = $this->arParams['SECTION_ELEMENT_ID'];

			$viewedIterator = Catalog\CatalogViewedProductTable::getList(array(
				'select' => array('PRODUCT_ID', 'ELEMENT_ID'),
				'filter' => $filter,
				'order' => array('DATE_VISIT' => 'DESC'),
				'limit' => $this->arParams['PAGE_ELEMENT_COUNT']
			));
			unset($filter);

			while ($viewedProduct = $viewedIterator->fetch())
			{
				$viewedProduct['ELEMENT_ID'] = (int)$viewedProduct['ELEMENT_ID'];
				$viewedProduct['PRODUCT_ID'] = (int)$viewedProduct['PRODUCT_ID'];
				$map[$viewedProduct['PRODUCT_ID']] = $viewedProduct['ELEMENT_ID'];
				if ($viewedProduct['ELEMENT_ID'] <= 0)
					$emptyProducts[] = $viewedProduct['PRODUCT_ID'];
			}

			if (!empty($emptyProducts))
			{
				$emptyProducts = Catalog\CatalogViewedProductTable::getProductsMap($emptyProducts);
				if (!empty($emptyProducts))
				{
					foreach ($emptyProducts as $product => $parent)
					{
						if ($parent == $this->arParams['SECTION_ELEMENT_ID'])
							unset($map[$product]);
						else
							$map[$product] = $parent;
					}
				}
			}
		}

		return $map;
	}

	/**
	 * Returns catalog prices data by product.
	 * @param array $item Product.
	 * @return array
	 */
	protected function getPriceDataByItem(array $item)
	{
		$prices = CIBlockPriceTools::GetItemPrices($item['IBLOCK_ID'], $this->data['CATALOG_PRICES'], $item, $this->arParams['PRICE_VAT_INCLUDE'], $this->data['CONVERT_CURRENCY']);
		return array(
			'PRICES' => $prices,
			'MIN_PRICE' => CIBlockPriceTools::getMinPriceFromList($prices),
		);
	}

	/**
	 * Resort $items field according to input ids parameter
	 *
	 * @param $productIds
	 */
	protected function resortItemsByIds($productIds)
	{
		$tmpItems = array();

		foreach ($productIds as $prodId)
		{
			$parentId = $this->productIdsMap[$prodId];

			if (isset($this->items[$parentId])) // always
				$tmpItems[$parentId] = $this->items[$parentId];
		}

		$this->items = $tmpItems;
		$this->makeItemsLinks();
	}

	/**
	 * Create items links
	 *
	 * @return void
	 */
	protected function makeItemsLinks()
	{
		$this->linkItems = array();
		if (empty($this->items))
			return;

		foreach ($this->items as $index => $item)
			$this->linkItems[$item['ID']] = &$this->items[$index];
		unset($index, $item);
	}

	protected function separateItemsByIblock()
	{
		$this->iblockItems = array();
		$this->needItemProperties = array();
		if (empty($this->productIdsMap))
			return;

		$itemsIterator = Iblock\ElementTable::getList(array(
			'select' => array('ID', 'IBLOCK_ID'),
			'filter' => array('@ID' => $this->productIdsMap)
		));
		while ($item = $itemsIterator->fetch())
		{
			$item['ID'] = (int)$item['ID'];
			$item['IBLOCK_ID'] = (int)$item['IBLOCK_ID'];
			if (!isset($this->iblockItems[$item['IBLOCK_ID']]))
				$this->iblockItems[$item['IBLOCK_ID']] = array();
			$this->iblockItems[$item['IBLOCK_ID']][] = $item['ID'];
			if (!isset($this->needItemProperties[$item['IBLOCK_ID']]))
			{
				$this->needItemProperties[$item['IBLOCK_ID']] = (
					(isset($this->arParams['PROPERTY_CODE'][$item['IBLOCK_ID']]) && !empty($this->arParams['PROPERTY_CODE'][$item['IBLOCK_ID']])) ||
					isset($this->arParams['ADDITIONAL_PICT_PROP'][$item['IBLOCK_ID']]) ||
					isset($this->arParams['LABEL_PROP'][$item['IBLOCK_ID']])
				);
			}
		}
		unset($item, $itemsIterator);
	}

	/**
	 * Get additional data for cache
	 *
	 * @return array
	 */
	protected function getAdditionalReferences()
	{
		return array();
	}
	/**
	 * Get common data from cache.
	 * @return mixed[]
	 */
	protected function getReferences()
	{
		$this->arParams['CACHE_GROUPS'] = (isset($this->arParams['CACHE_GROUPS']) && $this->arParams['CACHE_GROUPS'] == 'N' ? 'N' : 'Y');
		$obCache = new CPHPCache;

		if ($this->arParams['CACHE_GROUPS'] == 'Y')
		{
			$userGroups = implode(",", Main\UserTable::getUserGroupIds($this->getUserId()));
			$cacheId = implode("-", array(__CLASS__, $this->getLanguageId(), $this->getSiteId(), $userGroups));
		}
		else
			$cacheId = implode("-", array(__CLASS__, $this->getLanguageId(), $this->getSiteId()));

		$cached = array();
		if ($obCache->StartDataCache($this->arParams["CACHE_TIME"], $cacheId, $this->getSiteId().'/'.$this->getRelativePath().'/reference'))
		{
			// Catalog Groups
			$cached['CATALOG_GROUP'] = array();
			$catalogGroupList = CCatalogGroup::GetListArray();
			if (!empty($catalogGroupList))
			{
				foreach ($catalogGroupList as $catalogGroup)
					$cached['CATALOG_GROUP'][$catalogGroup['NAME']] = $catalogGroup;
				unset($catalogGroup);
			}
			unset($catalogGroupList);

			// Catalog Prices
			$cached['CATALOG_PRICE'] = CIBlockPriceTools::GetCatalogPrices(false, array_keys($cached['CATALOG_GROUP']));

			// Catalog Currency
			$cached['CURRENCY'] = array();
			if ($this->isCurrency)
			{
				$currencyIterator = CCurrency::getList("currency", "asc");
				while ($currency = $currencyIterator->fetch())
				{
					$cached['CURRENCY'][$currency['CURRENCY']] = $currency;
				}
			}

			// Catalogs list
			$cached['CATALOG'] = array();
			$catalogIterator = CCatalog::getList(array("IBLOCK_ID" => "ASC"));
			while ($catalog = $catalogIterator->fetch())
			{
				$info = CCatalogSku::getInfoByIblock($catalog['IBLOCK_ID']);
				$catalog['CATALOG_TYPE'] = $info['CATALOG_TYPE'];
				$cached['CATALOG'][$catalog['IBLOCK_ID']] = $catalog;
			}
			unset($catalog, $catalogIterator);

			// Measure list
			$cached['MEASURE'] = array();
			$measureIterator = CCatalogMeasure::getList(array("CODE" => "ASC"));
			while ($measure = $measureIterator->fetch())
				$cached['MEASURE'][$measure['ID']] = $measure;
			unset($measure, $measureIterator);

			// Default Measure
			$cached['DEFAULT_MEASURE'] = CCatalogMeasure::getDefaultMeasure(true, true);

			$additionalCache = $this->getAdditionalReferences();
			if (!empty($additionalCache) && is_array($additionalCache))
			{
				foreach ($additionalCache as $cacheKey => $cacheData)
					$cached[$cacheKey] = $cacheData;
				unset($cacheKey, $cacheData);
			}
			unset($additionalCache);

			$obCache->EndDataCache($cached);
		}
		else
		{
			$cached = $obCache->GetVars();
		}

		return $cached;
	}

	protected function fillUrlTemplates()
	{
		global $APPLICATION;

		$currentPath = CHTTP::urlDeleteParams(
			$APPLICATION->GetCurPageParam(),
			array($this->arParams['PRODUCT_ID_VARIABLE'], $this->arParams['ACTION_VARIABLE'], ''),
			array('delete_system_params' => true)
		);
		$currentPath .= (mb_stripos($currentPath, '?') === false ? '?' : '&');
		if ($this->arParams['COMPARE_PATH'] == '')
		{
			$comparePath = $currentPath;
		}
		else
		{
			$comparePath = CHTTP::urlDeleteParams(
				$this->arParams['COMPARE_PATH'],
				array($this->arParams['PRODUCT_ID_VARIABLE'], $this->arParams['ACTION_VARIABLE'], ''),
				array('delete_system_params' => true)
			);
			$comparePath .= (mb_stripos($comparePath, '?') === false ? '?' : '&');
		}
		$this->arParams['COMPARE_PATH'] = $comparePath.$this->arParams['ACTION_VARIABLE'].'=COMPARE';

		$this->urlTemplates['~BUY_URL_TEMPLATE'] = $currentPath.$this->arParams['ACTION_VARIABLE'].'='.self::ACTION_BUY.'&'.$this->arParams['PRODUCT_ID_VARIABLE'].'=';
		$this->urlTemplates['BUY_URL_TEMPLATE'] = htmlspecialcharsbx($this->urlTemplates['~BUY_URL_TEMPLATE']);
		$this->urlTemplates['~ADD_URL_TEMPLATE'] = $currentPath.$this->arParams['ACTION_VARIABLE'].'='.self::ACTION_ADD_TO_BASKET.'&'.$this->arParams['PRODUCT_ID_VARIABLE'].'=';
		$this->urlTemplates['ADD_URL_TEMPLATE'] = htmlspecialcharsbx($this->urlTemplates['~ADD_URL_TEMPLATE']);
		$this->urlTemplates['~SUBSCRIBE_URL_TEMPLATE'] = $currentPath.$this->arParams['ACTION_VARIABLE'].'='.self::ACTION_SUBSCRIBE.'&'.$this->arParams["PRODUCT_ID_VARIABLE"].'=';
		$this->urlTemplates['SUBSCRIBE_URL_TEMPLATE'] = htmlspecialcharsbx($this->urlTemplates['~SUBSCRIBE_URL_TEMPLATE']);
		$this->urlTemplates['~COMPARE_URL_TEMPLATE'] = $comparePath.$this->arParams['ACTION_VARIABLE'].'='.self::ACTION_ADD_TO_COMPARE_LIST.'&'.$this->arParams['PRODUCT_ID_VARIABLE'].'=';
		$this->urlTemplates['COMPARE_URL_TEMPLATE'] = htmlspecialcharsbx($this->urlTemplates['~COMPARE_URL_TEMPLATE']);
		unset($comparePath, $currentPath);
	}

	/**
	 * Get items for view.
	 * @return mixed[]  array('ID' => array(), 'ID' => array(), ...)
	 */
	protected function getItems()
	{
		if (empty($this->productIdsMap) || empty($this->arParams['SHOW_PRODUCTS']))
			return array();
		$this->separateItemsByIblock();

		$defaultMeasure = $this->data['DEFAULT_MEASURE'];

		$usePropertyFeatures = Iblock\Model\PropertyFeature::isEnabledFeatures();
		$items = array();
		foreach (array_keys($this->arParams['SHOW_PRODUCTS']) as $iblock)
		{
			$this->linkItems = array();
			$itemsIds = [];
			if (empty($this->iblockItems[$iblock]))
				continue;

			if ($usePropertyFeatures)
			{
				$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
					$iblock,
					['CODE' => 'Y']
				);
				if (!empty($list))
					$this->arParams['PROPERTY_CODE'][$iblock] = $list;
				if ($this->arParams['ADD_PROPERTIES_TO_BASKET'] == 'Y')
				{
					$list = Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
						$iblock,
						['CODE' => 'Y']
					);
					if (!empty($list))
						$this->arParams['CART_PROPERTIES'][$iblock] = $list;
				}
				$sku = \CCatalogSku::GetInfoByProductIBlock($iblock);
				if (!empty($sku))
				{
					$offersId = $sku['IBLOCK_ID'];
					$list = Iblock\Model\PropertyFeature::getListPageShowPropertyCodes(
						$offersId,
						['CODE' => 'Y']
					);
					if (!empty($list))
						$this->arParams['PROPERTY_CODE'][$offersId] = $list;
					if ($this->arParams['ADD_PROPERTIES_TO_BASKET'] == 'Y')
					{
						$list = Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
							$offersId,
							['CODE' => 'Y']
						);
						if (!empty($list))
							$this->arParams['CART_PROPERTIES'][$offersId] = $list;
					}
					$list = Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes(
						$offersId,
						['CODE' => 'Y']
					);
					if (!empty($list))
						$params['OFFER_TREE_PROPS'][$offersId] = $list;
				}
				unset($list);
			}

			$filter = $this->filter;
			$filter['IBLOCK_ID'] = $iblock;
			$filter['ID'] = $this->iblockItems[$iblock];

			$priceIds = $this->getPriceIds();

			$elementIterator = CIBlockElement::GetList(array(), $filter, false, false, $this->getElementSelectFields());
			$elementIterator->SetUrlTemplates($this->arParams['DETAIL_URL']);
			while ($element = $elementIterator->GetNext())
			{
				$element['ID'] = (int)$element['ID'];

				$element['ACTIVE_FROM'] = $element['DATE_ACTIVE_FROM'];
				$element['ACTIVE_TO'] = $element['DATE_ACTIVE_TO'];

				$buttons = CIBlock::GetPanelButtons(
					$element['IBLOCK_ID'],
					$element['ID'],
					0,
					array("SECTION_BUTTONS" => false, "SESSID" => false, "CATALOG" => true)
				);
				$element['EDIT_LINK'] = $buttons['edit']['edit_element']['ACTION_URL'];
				$element['DELETE_LINK'] = $buttons['edit']['delete_element']['ACTION_URL'];

				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($element["IBLOCK_ID"], $element["ID"]);
				$element["IPROPERTY_VALUES"] = $ipropValues->getValues();

				$element["PREVIEW_PICTURE"] = ($element["PREVIEW_PICTURE"] > 0 ? CFile::GetFileArray($element["PREVIEW_PICTURE"]) : false);
				if ($element["PREVIEW_PICTURE"])
				{
					$element["PREVIEW_PICTURE"]["ALT"] = $element["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"];
					if ($element["PREVIEW_PICTURE"]["ALT"] == "")
						$element["PREVIEW_PICTURE"]["ALT"] = $element["NAME"];
					$element["PREVIEW_PICTURE"]["TITLE"] = $element["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"];
					if ($element["PREVIEW_PICTURE"]["TITLE"] == "")
						$element["PREVIEW_PICTURE"]["TITLE"] = $element["NAME"];
				}
				$element["DETAIL_PICTURE"] = ($element["DETAIL_PICTURE"] > 0 ? CFile::GetFileArray($element["DETAIL_PICTURE"]) : false);
				if ($element["DETAIL_PICTURE"])
				{
					$element["DETAIL_PICTURE"]["ALT"] = $element["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"];
					if ($element["DETAIL_PICTURE"]["ALT"] == "")
						$element["DETAIL_PICTURE"]["ALT"] = $element["NAME"];
					$element["DETAIL_PICTURE"]["TITLE"] = $element["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"];
					if ($element["DETAIL_PICTURE"]["TITLE"] == "")
						$element["DETAIL_PICTURE"]["TITLE"] = $element["NAME"];
				}

				$element["PROPERTIES"] = array();
				$element["DISPLAY_PROPERTIES"] = array();
				$element["PRODUCT_PROPERTIES"] = array();
				$element['PRODUCT_PROPERTIES_FILL'] = array();

				if (!isset($element["CATALOG_MEASURE_RATIO"]))
					$element["CATALOG_MEASURE_RATIO"] = 1;
				if (!isset($element['CATALOG_MEASURE']))
					$element['CATALOG_MEASURE'] = 0;
				$element['CATALOG_MEASURE'] = (int)$element['CATALOG_MEASURE'];
				if ($element['CATALOG_MEASURE'] < 0)
					$element['CATALOG_MEASURE'] = 0;
				if (!isset($element['CATALOG_MEASURE_NAME']))
					$element['CATALOG_MEASURE_NAME'] = '';

				$element['CATALOG_MEASURE_NAME'] = $defaultMeasure['SYMBOL_RUS'];
				$element['~CATALOG_MEASURE_NAME'] = $defaultMeasure['~SYMBOL_RUS'];

				if (!empty($priceIds))
				{
					$element = $element + $this->oldPriceFields;
				}

				$items[$element['ID']] = $element;
				$this->linkItems[$element['ID']] = &$items[$element['ID']];
				$itemsIds[] = $element['ID'];
			}
			unset($element, $elementIterator);

			if (!empty($itemsIds))
			{
				Main\Type\Collection::normalizeArrayValuesByInt($itemsIds, true);
			}
			if (!empty($itemsIds))
			{
				if (!empty($priceIds))
				{
					foreach (array_chunk($itemsIds, 500) as $pageIds)
					{
						$priceIterator = Catalog\PriceTable::getList([
							'select' => [
								'ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID',
								'PRICE', 'CURRENCY',
								'QUANTITY_FROM', 'QUANTITY_TO',
								'EXTRA_ID'
							],
							'filter' => ['@PRODUCT_ID' => $pageIds, '@CATALOG_GROUP_ID' => $priceIds]
						]);
						while ($row = $priceIterator->fetch())
						{
							$id = (int)$row['PRODUCT_ID'];
							$priceType = $row['CATALOG_GROUP_ID'];
							$this->linkItems[$id]['CATALOG_PRICE_ID_'.$priceType] = $row['ID'];
							$this->linkItems[$id]['~CATALOG_PRICE_ID_'.$priceType] = $row['ID'];
							$this->linkItems[$id]['CATALOG_PRICE_'.$priceType] = $row['PRICE'];
							$this->linkItems[$id]['~CATALOG_PRICE_'.$priceType] = $row['PRICE'];
							$this->linkItems[$id]['CATALOG_CURRENCY_'.$priceType] = $row['CURRENCY'];
							$this->linkItems[$id]['~CATALOG_CURRENCY_'.$priceType] = $row['CURRENCY'];
							$this->linkItems[$id]['CATALOG_QUANTITY_FROM_'.$priceType] = $row['QUANTITY_FROM'];
							$this->linkItems[$id]['~CATALOG_QUANTITY_FROM_'.$priceType] = $row['QUANTITY_FROM'];
							$this->linkItems[$id]['CATALOG_QUANTITY_TO_'.$priceType] = $row['QUANTITY_TO'];
							$this->linkItems[$id]['~CATALOG_QUANTITY_TO_'.$priceType] = $row['QUANTITY_TO'];
							$this->linkItems[$id]['CATALOG_EXTRA_ID_'.$priceType] = $row['EXTRA_ID'];
							$this->linkItems[$id]['~CATALOG_EXTRA_ID_'.$priceType] = $row['EXTRA_ID'];
						}
						unset($row, $priceIterator);
					}
					unset($pageIds);
				}
				unset($priceIds);

				$propFilter = array(
					'ID' => $this->iblockItems[$iblock],
					'IBLOCK_ID' => $iblock
				);
				CIBlockElement::GetPropertyValuesArray($this->linkItems, $iblock, $propFilter);
				unset($propFilter);

				foreach ($this->linkItems as &$element)
				{
					CCatalogDiscount::SetProductPropertiesCache($element['ID'], $element['PROPERTIES']);

					if (isset($this->arParams['PROPERTY_CODE'][$iblock]))
					{
						$properties = $this->arParams['PROPERTY_CODE'][$iblock];
						foreach ($properties as $propertyName)
						{
							if (!isset($element['PROPERTIES'][$propertyName]))
								continue;

							$prop = &$element['PROPERTIES'][$propertyName];
							$boolArr = is_array($prop["VALUE"]);
							if (
								($boolArr && !empty($prop["VALUE"]))
								|| (!$boolArr && $prop["VALUE"] <> '')
							)
							{
								$element['DISPLAY_PROPERTIES'][$propertyName] = CIBlockFormatProperties::GetDisplayValue($element, $prop, 'catalog_out');
							}
							unset($prop);
						}
					}

					if ($this->arParams['ADD_PROPERTIES_TO_BASKET'] == 'Y' && !empty($this->arParams['CART_PROPERTIES'][$iblock]))
					{
						$element["PRODUCT_PROPERTIES"] = CIBlockPriceTools::GetProductProperties(
							$element['IBLOCK_ID'],
							$element["ID"],
							$this->arParams['CART_PROPERTIES'][$iblock],
							$element["PROPERTIES"]
						);

						if (!empty($element["PRODUCT_PROPERTIES"]))
							$element['PRODUCT_PROPERTIES_FILL'] = CIBlockPriceTools::getFillProductProperties($element['PRODUCT_PROPERTIES']);
					}
				}
			}
			unset($element, $this->linkItems);
		}
		unset($iblock);
		unset($elementKey);

		return $items;
	}

	/**
	 * Gets catalog prices needed for component.
	 *
	 * @param array $priceCodes
	 * @return array
	 */
	protected function getCatalogPrices(array $priceCodes = array())
	{
		$catalogPrices = array();
		foreach ($priceCodes as $code)
		{
			if (isset($this->data['CATALOG_PRICE'][$code]))
				$catalogPrices[$code] = $this->data['CATALOG_PRICE'][$code];
		}
		return $catalogPrices;
	}

	/**
	 * Get main data - viewed products.
	 * @return void
	 */
	protected function prepareData()
	{
		$this->fillUrlTemplates();

		$this->data = $this->getReferences();
		$this->prepareSystemData();

		$this->productIds = $this->getProductIds();
		if (is_null($this->productIds))
		{
			$this->productIdsMap = $this->getProductIdsMap();
			$this->productIds = array_keys($this->productIdsMap);
		}
		else
		{
			$this->productIdsMap = Catalog\CatalogViewedProductTable::getProductsMap($this->productIds);
		}

		$this->iblockItems = array();
		$this->linkItems = array();

		$this->prepareFilter();
		$this->prepareSelectFields();
		$this->fillOldPriceFields();
		$this->items = $this->getItems();
		$this->resortItemsByIds($this->productIds);

		$this->setItemsMeasure();
		$this->setItemsOffers();
		$this->setItemsPrices();
	}

	/**
	 * Fill system data.
	 *
	 * return void
	 */
	protected function prepareSystemData()
	{
		$this->data['CATALOG_PRICES'] = $this->getCatalogPrices($this->arParams['PRICE_CODE']);
		$this->data['CONVERT_CURRENCY'] = array();
		if ($this->arParams['CONVERT_CURRENCY'] == 'Y' && $this->arParams['CURRENCY_ID'] != '')
		{
			if (!$this->isCurrency)
			{
				$this->arParams['CONVERT_CURRENCY'] = 'N';
				$this->arParams['CURRENCY_ID'] = '';
			}
			else
			{
				if (isset($this->data['CURRENCY'][$this->arParams['CURRENCY_ID']]))
				{
					$this->data['CONVERT_CURRENCY'] = array(
						'CURRENCY_ID' => $this->arParams['CURRENCY_ID']
					);
				}
				else
				{
					$this->arParams['CONVERT_CURRENCY'] = 'N';
					$this->arParams['CURRENCY_ID'] = '';
				}
			}
		}
	}
	/**
	 * Prepare data to render.
	 * @return void
	 */
	protected function formatResult()
	{
		$this->arResult['ITEMS'] = $this->items;
		$this->arResult['CONVERT_CURRENCY'] = $this->data['CONVERT_CURRENCY'];
		$this->arResult['CATALOGS'] = $this->data['CATALOG'];
		$this->arResult['ERRORS'] = $this->errors;
		$this->arResult['WARNINGS'] = $this->warnings;
	}

	/**
	 * set prices for all items
	 * @return void
	 */
	protected function setItemsPrices()
	{
		//  Set items Prices
		foreach ($this->items as &$item)
		{
			$item["PRICES"] = array();
			$item['MIN_PRICE'] = false;
			$item["CAN_BUY"] = false;

			$item['~BUY_URL'] = $this->urlTemplates['~BUY_URL_TEMPLATE'].$item['ID'];
			$item['BUY_URL'] = $this->urlTemplates['BUY_URL_TEMPLATE'].$item['ID'];
			$item['~ADD_URL'] = $this->urlTemplates['~ADD_URL_TEMPLATE'].$item['ID'];
			$item['ADD_URL'] = $this->urlTemplates['ADD_URL_TEMPLATE'].$item['ID'];
			$item['~COMPARE_URL'] = $this->urlTemplates['~COMPARE_URL_TEMPLATE'].$item['ID'];
			$item['COMPARE_URL'] = $this->urlTemplates['COMPARE_URL_TEMPLATE'].$item['ID'];
			$item['~SUBSCRIBE_URL'] = $this->urlTemplates['~SUBSCRIBE_URL_TEMPLATE'].$item['ID'];
			$item['SUBSCRIBE_URL'] = $this->urlTemplates['SUBSCRIBE_URL_TEMPLATE'].$item['ID'];

			if (!empty($item['OFFERS']))
				continue;

			$priceDataByItem = $this->getPriceDataByItem($item);

			$item['PRICES'] = $priceDataByItem['PRICES'];
			$item['MIN_PRICE'] = $priceDataByItem['MIN_PRICE'];
			$item['CAN_BUY'] = CIBlockPriceTools::CanBuy($item['IBLOCK_ID'], $this->data['CATALOG_PRICES'], $item);
		}
		unset($item);
	}

	/**
	 * Sets measure for all viewed products.
	 * @return void
	 */
	protected function setItemsMeasure()
	{
		if (!count($this->productIdsMap))
			return;

		$measures = $this->data['MEASURE'];
		foreach ($this->items as &$item)
		{
			if (array_key_exists($item['CATALOG_MEASURE'], $measures))
			{
				$measure = $measures[$item['CATALOG_MEASURE']];
				$item['~CATALOG_MEASURE_NAME'] = ($this->getLanguageId() == "ru") ? $measure["SYMBOL_RUS"] : $measure["SYMBOL_INTL"];
				$item['CATALOG_MEASURE_NAME'] = Main\Text\HtmlFilter::encode($item['~CATALOG_MEASURE_NAME']);
			}
		}

		// Ratios
		$ratioIterator = CCatalogMeasureRatio::getList(
			array(),
			array('@PRODUCT_ID' => array_values($this->productIdsMap)),
			false,
			false,
			array('PRODUCT_ID', 'RATIO')
		);

		while ($ratio = $ratioIterator->fetch())
		{
			if (isset($this->items[$ratio['PRODUCT_ID']]))
			{
				$intRatio = (int)$ratio['RATIO'];
				$dblRatio = (float)$ratio['RATIO'];
				$mxRatio = ($dblRatio > $intRatio ? $dblRatio : $intRatio);
				if ($mxRatio < CATALOG_VALUE_EPSILON)
					$mxRatio = 1;
				$this->items[$ratio['PRODUCT_ID']]['CATALOG_MEASURE_RATIO'] = $mxRatio;
			}
		}
		unset($ratio, $ratioIterator);
	}

	/**
	 * Add offers for each catalog product.
	 * @return void
	 */
	protected function setItemsOffers()
	{
		// filter items to get only product type (not offers)
		$productIblocks = array();
		foreach ($this->data['CATALOG'] as $catalog)
		{
			if ($catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_FULL || $catalog['CATALOG_TYPE'] == CCatalogSKU::TYPE_PRODUCT)
				$productIblocks[] = $catalog;
		}

		// Get total offers for all catalog products
		foreach ($productIblocks as &$iblock)
		{
			if (empty($this->iblockItems[$iblock['IBLOCK_ID']]))
				continue;

			//if(empty($this->arParams['OFFER_TREE_PROPS'][$iblock['OFFERS_IBLOCK_ID']]) || empty($this->arParams['PROPERTY_CODE'][$iblock['OFFERS_IBLOCK_ID']]))
			//	continue;

			if(!isset($this->arParams['PROPERTY_CODE'][$iblock['OFFERS_IBLOCK_ID']]) && !is_array($this->arParams['PROPERTY_CODE'][$iblock['OFFERS_IBLOCK_ID']]))
				$this->arParams['PROPERTY_CODE'][$iblock['OFFERS_IBLOCK_ID']] = array();

			if(!isset($this->arParams['OFFER_TREE_PROPS'][$iblock['OFFERS_IBLOCK_ID']]) && !is_array($this->arParams['OFFER_TREE_PROPS'][$iblock['OFFERS_IBLOCK_ID']]))
				$this->arParams['OFFER_TREE_PROPS'][$iblock['OFFERS_IBLOCK_ID']] = array();

			$selectProperties = array_merge($this->arParams['PROPERTY_CODE'][$iblock['OFFERS_IBLOCK_ID']], $this->arParams['OFFER_TREE_PROPS'][$iblock['OFFERS_IBLOCK_ID']]);
			$offers = CIBlockPriceTools::GetOffersArray(
				array(
					'IBLOCK_ID' => $iblock['IBLOCK_ID'],
					'HIDE_NOT_AVAILABLE' => $this->arParams['HIDE_NOT_AVAILABLE'],
				)
				, $this->iblockItems[$iblock['IBLOCK_ID']]
				, array()
				, array("ID", "CODE", "NAME", "SORT", "PREVIEW_PICTURE", "DETAIL_PICTURE")
				, $selectProperties
				, $this->arParams["OFFERS_LIMIT"]
				, $this->data['CATALOG_PRICES']
				, $this->arParams['PRICE_VAT_INCLUDE']
				, $this->data['CONVERT_CURRENCY']
			);
			if (empty($offers))
				continue;

			foreach ($offers as &$offer)
			{
				$linkId = (int)$offer['LINK_ELEMENT_ID'];
				if (!isset($this->linkItems[$linkId]))
					continue;

				$offer['~BUY_URL'] = $this->urlTemplates['~BUY_URL_TEMPLATE'].$offer['ID'];
				$offer['BUY_URL'] = $this->urlTemplates['BUY_URL_TEMPLATE'].$offer['ID'];
				$offer['~ADD_URL'] = $this->urlTemplates['~ADD_URL_TEMPLATE'].$offer['ID'];
				$offer['ADD_URL'] = $this->urlTemplates['ADD_URL_TEMPLATE'].$offer['ID'];
				$offer['~COMPARE_URL'] = $this->urlTemplates['~COMPARE_URL_TEMPLATE'].$offer['ID'];
				$offer['COMPARE_URL'] = $this->urlTemplates['COMPARE_URL_TEMPLATE'].$offer['ID'];
				$offer['~SUBSCRIBE_URL'] = $this->urlTemplates['~SUBSCRIBE_URL_TEMPLATE'].$offer['ID'];
				$offer['SUBSCRIBE_URL'] = $this->urlTemplates['SUBSCRIBE_URL_TEMPLATE'].$offer['ID'];

				if (!isset($this->linkItems[$linkId]['OFFERS']))
					$this->linkItems[$linkId]['OFFERS'] = array();
				$this->linkItems[$linkId]['OFFERS'][] = $offer;
			}
			unset($offer);
		}
		unset($iblock);

		// set selected flag
		foreach ($this->items as $key => &$item)
		{
			$index = 0;
			if (empty($item['OFFERS']))
				continue;
			foreach ($item['OFFERS'] as $offerKey => &$offer)
			{
				$offer['SELECTED'] = ($offer['ID'] == $key);
				if ($offer['SELECTED'])
				{
					$index = $offerKey;
				}
			}
			$item['OFFERS_SELECTED'] = $index;
		}
		unset($item, $offer);
	}

	/**
	 * Prepares $this->filter for CIBlockElement::getList() method.
	 * @return void
	 */
	protected function prepareFilter()
	{
		$this->filter = array(
			"ID" => empty($this->productIdsMap) ? -1 : array_values($this->productIdsMap),
			"IBLOCK_LID" => $this->getSiteId(),
			"IBLOCK_ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => "R",
			"IBLOCK_ID" => array_keys($this->arParams['SHOW_PRODUCTS'])
		);

		if ($this->arParams['HIDE_NOT_AVAILABLE'] == 'Y')
			$this->filter['CATALOG_AVAILABLE'] = 'Y';

		$prices = $this->data['CATALOG_PRICES'];
		if (!empty($prices) && is_array($prices))
		{
			foreach ($prices as $value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;
				$this->filter["CATALOG_SHOP_QUANTITY_".$value["ID"]] = $this->arParams["SHOW_PRICE_COUNT"];
			}
			unset($value);
		}
		unset($prices);
	}

	/**
	 * Prepares $this->selectFields for CIBlockElement::getList() method.
	 * @return void
	 */
	protected function prepareSelectFields()
	{
		$this->selectFields = array_merge(
			$this->getElementSelectFields(),
			$this->getPriceSelectFields()
		);
	}

	/**
	 * Returns element fields and old product fields.
	 *
	 * @return array
	 */
	protected function getElementSelectFields(): array
	{
		return [
			"ID",
			"IBLOCK_ID",
			"CODE",
			"NAME",
			"ACTIVE",
			"DATE_ACTIVE_FROM",
			"DATE_ACTIVE_TO",
			"DETAIL_PAGE_URL",
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"CATALOG_TYPE" // compatibility
		];
	}

	protected function getPriceSelectFields(): array
	{
		$result = [];

		if (!empty($this->data['CATALOG_PRICES']))
		{
			foreach ($this->data['CATALOG_PRICES'] as $value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;
				$result[] = $value["SELECT"];
			}
			unset($value);
		}

		return $result;
	}

	protected function getPriceIds(): array
	{
		$result = [];

		if (!empty($this->data['CATALOG_PRICES']))
		{
			foreach ($this->data['CATALOG_PRICES'] as $value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;
				$result[] = (int)$value["ID"];
			}
			unset($value);
			if (!empty($result))
			{
				sort($result);
			}
		}

		return $result;
	}

	protected function fillOldPriceFields(): void
	{
		$this->oldPriceFields = [];
		if (!empty($this->data['CATALOG_PRICES']))
		{
			foreach ($this->data['CATALOG_PRICES'] as $value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;

				$priceType = $value['ID'];
				$this->oldPriceFields['CATALOG_GROUP_ID_'.$priceType] = $priceType;
				$this->oldPriceFields['~CATALOG_GROUP_ID_'.$priceType] = $priceType;
				$this->oldPriceFields['CATALOG_GROUP_NAME_'.$priceType] = $value['TITLE'];
				$this->oldPriceFields['~CATALOG_GROUP_NAME_'.$priceType] = $value['~TITLE'];
				$this->oldPriceFields['CATALOG_CAN_ACCESS_'.$priceType] = ($value['CAN_VIEW'] ? 'Y' : 'N');
				$this->oldPriceFields['~CATALOG_CAN_ACCESS_'.$priceType] = ($value['CAN_VIEW'] ? 'Y' : 'N');
				$this->oldPriceFields['CATALOG_CAN_BUY_'.$priceType] = ($value['CAN_BUY'] ? 'Y' : 'N');
				$this->oldPriceFields['~CATALOG_CAN_BUY_'.$priceType] = ($value['CAN_BUY'] ? 'Y' : 'N');
				$this->oldPriceFields['CATALOG_PRICE_ID_'.$priceType] = null;
				$this->oldPriceFields['~CATALOG_PRICE_ID_'.$priceType] = null;
				$this->oldPriceFields['CATALOG_PRICE_'.$priceType] = null;
				$this->oldPriceFields['~CATALOG_PRICE_'.$priceType] = null;
				$this->oldPriceFields['CATALOG_CURRENCY_'.$priceType] = null;
				$this->oldPriceFields['~CATALOG_CURRENCY_'.$priceType] = null;
				$this->oldPriceFields['CATALOG_QUANTITY_FROM_'.$priceType] = null;
				$this->oldPriceFields['~CATALOG_QUANTITY_FROM_'.$priceType] = null;
				$this->oldPriceFields['CATALOG_QUANTITY_TO_'.$priceType] = null;
				$this->oldPriceFields['~CATALOG_QUANTITY_TO_'.$priceType] = null;
				$this->oldPriceFields['CATALOG_EXTRA_ID_'.$priceType] = null;
				$this->oldPriceFields['~CATALOG_EXTRA_ID_'.$priceType] = null;
			}
		}
	}

	/**
	 * Extract data from cache. No action by default.
	 * @return bool
	 */
	protected function extractDataFromCache()
	{
		return false;
	}

	protected function putDataToCache()
	{
	}

	protected function abortDataCache()
	{
	}

	/**
	 * Check action variable.
	 *
	 * @param array $params			Component params.
	 * @return string
	 */
	protected function prepareActionVariable($params)
	{
		$actionVariable = (isset($params['ACTION_VARIABLE']) ? trim($params['ACTION_VARIABLE']) : '');
		if ($actionVariable === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $actionVariable))
			$actionVariable = 'action_cvp';
		return $actionVariable;
	}

	/**
	 * Start Component
	 */
	public function executeComponent()
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		try
		{
			$this->checkModules();
			$this->processRequest();
			if (!$this->extractDataFromCache())
			{
				$this->prepareData();
				$this->formatResult();
				$this->setResultCacheKeys(array());
				$this->includeComponentTemplate();
				$this->putDataToCache();
			}
		}
		catch (Main\SystemException $e)
		{
			$this->abortDataCache();

			if ($this->isAjax())
			{
				$APPLICATION->restartBuffer();
				header('Content-Type: application/json');
				echo Main\Web\Json::encode(array('STATUS' => 'ERROR', 'MESSAGE' => $e->getMessage()));
				die();
			}

			ShowError($e->getMessage());
		}
	}
}