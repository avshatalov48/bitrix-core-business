<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */

use Bitrix\Main,
	Bitrix\Main\Config,
	Bitrix\Main\Localization,
	Bitrix\Highloadblock as HL,
	Bitrix\Main\Loader,
	Bitrix\Sale,
	Bitrix\Iblock,
	Bitrix\Main\Data,
	Bitrix\Sale\Location,
	Bitrix\Sale\Cashbox\CheckManager;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CBitrixPersonalOrderDetailComponent extends CBitrixComponent
{
	const E_SALE_MODULE_NOT_INSTALLED 		= 10000;
	const E_ORDER_NOT_FOUND 				= 10001;
	const E_CATALOG_MODULE_NOT_INSTALLED 	= 10003;
	const E_NOT_AUTHORIZED					= 10004;

	/**
	 * Fatal error list. Any fatal error makes useless further execution of a component code.
	 * In most cases, there will be only one error in a list according to the scheme "one shot - one dead body"
	 *
	 * @var string[] Array of fatal errors.
	 */
	protected $errorsFatal = array();

	/**
	 * Non-fatal error list. Some non-fatal errors may occur during component execution, so certain functions of the component
	 * may became defunct. Still, user should stay informed.
	 * There may be several non-fatal errors in a list.
	 *
	 * @var string[] Array of non-fatal errors.
	 */
	protected $errorsNonFatal = array();

	/**
	 * Contains some valuable info from $_REQUEST
	 *
	 * @var object request info
	 */
	protected $requestData = array();

	/**
	 * Gathered options that are required
	 *
	 * @var string[] options
	 */
	protected $options = array();

	/**
	 * Variable remains true if there is 'catalog' module installed
	 *
	 * @var bool flag
	 */
	protected $useCatalog = true;

	/**
	 * Variable remains true if there is 'highloadiblocks' module installed
	 *
	 * @var bool flag
	 */
	protected $useHL = true;

	/**
	 * Variable remains true if there is 'iblock' module installed
	 *
	 * @var bool flag
	 */
	protected $useIBlock = true;

	/**@var Data\Cache $this->currentCache */
	protected $currentCache = null;

	/**
	 * Loaded order for displaying
	 *
	 * @var Sale\Order order
	 */
	protected $order = null;

	protected $dbResult = array();

	/**
	 * A convert map for method self::formatDate()
	 *
	 * @var string[] keys
	 */
	protected $orderDateFields2Convert = array(
		'DATE_INSERT',
		'DATE_STATUS',
		'PAY_VOUCHER_DATE',
		'DATE_DEDUCTED',
		'DATE_UPDATE',
		'PS_RESPONSE_DATE',
		'DATE_PAY_BEFORE',
		'DATE_BILL',
		'DATE_CANCELED',
		'DATE_PAYED'
	);

	protected $compatibilityPaymentFields = array(
		'DATE_PAID' => 'DATE_PAYED',
		'PAY_SYSTEM_ID',
		'EMP_PAID_ID' => 'EMP_PAYED_ID',
		'PAY_VOUCHER_NUM',
		'PAY_VOUCHER_DATE',
		'PS_STATUS',
		'PS_STATUS_CODE',
		'PS_STATUS_DESCRIPTION',
		'PS_STATUS_MESSAGE',
		'PS_SUM',
		'PS_CURRENCY',
		'PS_RESPONSE_DATE',
		'DATE_PAY_BEFORE',
		'DATE_BILL',
	);

	protected $compatibilityShipmentFields = array(
		'DELIVERY_ID',
		'TRACKING_NUMBER',
		'ALLOW_DELIVERY',
		'DATE_ALLOW_DELIVERY',
		'EMP_ALLOW_DELIVERY_ID',
		'DEDUCTED',
		'DATE_DEDUCTED',
		'EMP_DEDUCTED_ID',
		'REASON_UNDO_DEDUCTED',
		'RESERVED',
		'DELIVERY_DOC_NUM',
		'DELIVERY_DOC_DATE',
		'DELIVERY_DATE_REQUEST',
		'STORE_ID',
	);

	protected $compatibilityUserFields = array(
		'LOGIN',
		'NAME',
		'LAST_NAME',
		'EMAIL',
	);

	public function __construct($component = null)
	{
		parent::__construct($component);

		Localization\Loc::loadMessages(__FILE__);
	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		if (!Loader::includeModule('sale'))
			throw new Main\SystemException(Localization\Loc::getMessage("SPOD_SALE_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);

		$this->useCatalog = Loader::includeModule('catalog');
		$this->useHL = Loader::includeModule('highloadblock');
		$this->useIBlock = Loader::includeModule('iblock');
	}


	/**
	 * Function checks if user is authorized or not. If not, auth form will be shown.
	 * @return void
	 * @throws Main\SystemException
	 */
	protected function checkAuthorized()
	{
		global $USER, $APPLICATION;

		$context = \Bitrix\Main\Context::getCurrent();
		$request = $context->getRequest();

		if ($access = $request->get('access'))
		{
			$this->loadOrder(urldecode(urldecode($this->arParams["ID"])));
			if (
				$this->order &&
				$this->order->getHash() === $request->get('access') &&
				\Bitrix\Sale\Helpers\Order::isAllowGuestView($this->order)
			)
			{
				$this->requestData['hash'] = $request->get('access');
				$this->arParams['GUEST_MODE'] = 'Y';
				return;
			}
		}

		if (!$USER->IsAuthorized())
		{
			$msg = Localization\Loc::getMessage("SPOD_ACCESS_DENIED");

			// for compatibility reasons: by default AuthForm() is shown in class.php, as it used to be.
			// BUT the better way is to show it in template.php, as it required by MVC paradigm
			if(!$this->arParams['AUTH_FORM_IN_TEMPLATE'])
			{
				$APPLICATION->AuthForm($msg, false, false, 'N', false);
			}

			throw new Main\SystemException($msg, self::E_NOT_AUTHORIZED);
		}
	}

	/**
	 * Function checks and prepares all the parameters passed. Everything about $arParam modification is here.
	 * @param mixed[] $arParams List of unchecked parameters
	 * @return mixed[] Checked and valid parameters
	 */
	public function onPrepareComponentParams($arParams)
	{
		global $APPLICATION;

		$this->tryParseInt($arParams["CACHE_TIME"], 3600, true);

		$arParams['CACHE_GROUPS'] = (isset($arParams['CACHE_GROUPS']) && $arParams['CACHE_GROUPS'] == 'N' ? 'N' : 'Y');

		$this->tryParseString($arParams["PATH_TO_LIST"], $APPLICATION->GetCurPage());
		$this->tryParseString($arParams["PATH_TO_PAYMENT"], "payment.php");

		$this->tryParseString($arParams["PATH_TO_CANCEL"], $APPLICATION->GetCurPage()."?"."ID=#ID#");
		$arParams["PATH_TO_CANCEL"] .= (strpos($arParams["PATH_TO_CANCEL"], "?") === false ? "?" : "&");

		$this->tryParseString($arParams["ACTIVE_DATE_FORMAT"], "d.m.Y");

		// fields & props to select from IBlock
		if(!is_array($arParams["CUSTOM_SELECT_PROPS"]))
			$arParams["CUSTOM_SELECT_PROPS"] = array();
		else
			$this->tryParseArray($arParams["CUSTOM_SELECT_PROPS"]);

		// resample sizes
		$this->tryParseInt($arParams["PICTURE_WIDTH"], 110);
		$this->tryParseInt($arParams["PICTURE_HEIGHT"], 110);

		// resample type for images
		if(!in_array($arParams['RESAMPLE_TYPE'], array(BX_RESIZE_IMAGE_EXACT, BX_RESIZE_IMAGE_PROPORTIONAL, BX_RESIZE_IMAGE_PROPORTIONAL_ALT)))
			$arParams['RESAMPLE_TYPE'] = BX_RESIZE_IMAGE_PROPORTIONAL;

		$this->tryParseBoolean($arParams['AUTH_FORM_IN_TEMPLATE']);

		if (empty($arParams['REFRESH_PRICES']))
		{
			$arParams['REFRESH_PRICES'] = "N";
		}

		if (empty($arParams['ALLOW_INNER']))
		{
			$arParams['ALLOW_INNER'] = "N";
		}

		if (empty($arParams['ONLY_INNER_FULL']))
		{
			$arParams['ONLY_INNER_FULL'] = "Y";
		}

		if (!CBXFeatures::IsFeatureEnabled('SaleAccounts'))
		{
			$arParams['ALLOW_INNER'] = "N";
		}

		if (!is_array($arParams['HIDE_USER_INFO']))
		{
			$arParams['HIDE_USER_INFO'] = array();
		}
		
		return $arParams;
	}

	/**
	 * Function parses an array: strip empty values, duplicate ones
	 * @param mixed[] $fld Field value
	 * @return array Parsed value
	 */
	public static function tryParseArray(&$fld)
	{
		foreach($fld as $k => &$item)
		{
			$item = trim($item);
			if(!strlen($item))
				unset($fld[$k]);
		}

		$fld = array_unique($fld);

		return $fld;
	}

	/**
	 * Function reduces input value to integer type, and, if gets null, passes the default value.	 *
	 * @param int &$fld					Field value.
	 * @param int $default				Default value.
	 * @param bool $allowZero			Allows zero-value of the parameter
	 * @return int
	 */
	public static function tryParseInt(&$fld, $default, $allowZero = false)
	{
		$fld = (int)$fld;
		if (!$allowZero && !$fld && isset($default))
			$fld = $default;

		return $fld;
	}

	/**
	 * Function processes string value and, if gets null, passes the default value to it
	 * @param mixed &$fld Field value
	 * @param string $default Default value
	 * @return string Parsed value
	 */
	public static function tryParseString(&$fld, $default)
	{
		$fld = trim((string)$fld);
		if(!strlen($fld) && isset($default))
			$fld = htmlspecialcharsbx($default);

		return $fld;
	}

	/**
	 * Function forces 'Y'/'N' value to boolean
	 * @param mixed $fld Field value
	 * @return string parsed value
	 */
	public static function tryParseBoolean(&$fld)
	{
		$fld = $fld == 'Y';
		return $fld;
	}

	/**
	 * Function sets page title, if required
	 * @return void
	 */
	protected function setTitle()
	{
		global $APPLICATION;

		if ($this->arParams["SET_TITLE"] == 'Y')
			$APPLICATION->SetPageProperty('title',Localization\Loc::getMessage("SPOD_TITLE", array("#ID#" => $this->dbResult["ACCOUNT_NUMBER"])));
	}

	/**
	 * Function gets all options required for component
	 * @return void
	 */
	protected function loadOptions()
	{
		$this->options['USE_ACCOUNT_NUMBER'] = Config\Option::get("sale", "account_number_template", "") !== "";
		$this->options['WEIGHT_UNIT'] = Config\Option::get("sale", "weight_unit", "", SITE_ID);
		$this->options['WEIGHT_K'] = Config\Option::get("sale", "weight_koef", 1, SITE_ID);
	}

	/**
	 * Function could describe what to do when order ID not set. By default, component will redirect to list page.
	 * @return void
	 */
	protected function doCaseOrderIdNotSet()
	{
		LocalRedirect($this->arParams["PATH_TO_LIST"]);
	}

	/**
	 * Function processes and corrects $_REQUEST. Everything about $_REQUEST lies here.
	 * @return void
	 */
	protected function processRequest()
	{
		$this->requestData["ID"] = urldecode(urldecode($this->arParams["ID"]));

		if (!strlen($this->requestData["ID"]))
			$this->doCaseOrderIdNotSet();
	}

	/**
	 * Obtain names for properties passed in $arParams['CUSTOM_SELECT_PROPS']
	 * @param mixed[] Cached data taken from obtainDataCachedStructure()
	 */
	protected function obtainPropertyNames(&$cached)
	{
		if($this->useIBlock && !empty($this->arParams['CUSTOM_SELECT_PROPS']))
		{
			$props = array();

			foreach($this->arParams['CUSTOM_SELECT_PROPS'] as $prop)
			{
				if (strpos($prop, 'PROPERTY_') !== false)
				{
					$propId = str_replace('PROPERTY_', '', $prop);

					if ($propId == (string)intval($propId)) // obviously its an id
						$filter = array('ID' => intval($propId));
					else // its a code
						$filter = array('CODE' => $propId);

					$propertyList = Iblock\PropertyTable::getList(
						array('filter' => $filter)
					);

					if ($result = $propertyList->fetch())
					{
						$props[$result['IBLOCK_ID']][$prop] = $result;
					}
				}
			}

			$cached["PROPERTY_DESCRIPTION"] = $props;
		}
	}

	/**
	 * Return order tax list
	 * @param array &$cached		Cached data.
	 * @return void
	 */
	protected function obtainTaxes(&$cached)
	{
		/** @var Sale\Tax $tax */
		$tax = Sale\Tax::load($this->order);
		$cached['TAX_LIST'] = $tax->getTaxList();
	}

	/**
	 * Function fetches information about stores in the system, depending on the delivery system.
	 * This method should should be called only after obtainDataCachedStatic().
	 * @param mixed[] $cached Cached data taken from obtainDataCachedStructure()
	 * @return void
	 */
	protected function obtainDeliveryStore(&$cached)
	{
		if (empty($this->dbResult["ID"]))
			return;
		foreach ($this->dbResult['SHIPMENT'] as $shipment)
		{

			if (!empty($shipment["DELIVERY"]) && count($shipment["DELIVERY"]["STORE"]) > 0 && $this->useCatalog)
			{
				$storesIdList = $shipment["DELIVERY"]["STORE"];
				$resultStore = Bitrix\Catalog\StoreTable::getList(
					array(
						'order' => array(
							"SORT" => "DESC",
							"ID" => "DESC"),
						'filter' => array(
							"ACTIVE" => "Y",
							"ID" => $storesIdList,
							"ISSUING_CENTER" => "Y"
						),
						'select' => array(
							"ID",
							"TITLE",
							"ADDRESS",
							"DESCRIPTION",
							"IMAGE_ID",
							"PHONE",
							"SCHEDULE",
							"GPS_N",
							"GPS_S",
							"ISSUING_CENTER",
							"SITE_ID",
							"EMAIL"
						)
					)
				);

				while ($item = $resultStore->fetch())
				{
					$cached["DELIVERY_STORE_LIST"][$item['ID']] = $item;
				}
			}
		}
	}

	/**
	 * Function gets order basket info from the database
	 * @param mixed[] Cached data taken from obtainDataCachedStructure()
	 * @return void
	 */
	protected function obtainBasket(&$cached)
	{
		if (empty($this->dbResult["ID"]))
			return;

		$basket = array();
		$basketN = $this->order->getBasket();

		$basketItemsList = $basketN->getBasketItems();

		/**  @var Sale\BasketItem $basketItem*/
		foreach ($basketItemsList as $basketItem)
		{
			$basketValues = $basketItem->getFieldValues();

			$basketPropertyCollection = $basketItem->getPropertyCollection();

			if($this->useCatalog)
			{
				$parentList = CCatalogSku::GetProductInfo($basketValues["PRODUCT_ID"]);
				if(!empty($parentList))
					$basketValues['PARENT'] = $parentList;
			}

			/**  @var Sale\BasketPropertyItem $basketProperty*/
			foreach ($basketPropertyCollection as $basketProperty)
			{
				$basketPropertyList = $basketProperty->getFieldValues();
				if ($basketPropertyList['CODE'] !== "CATALOG.XML_ID"&&
					$basketPropertyList['CODE'] !== "PRODUCT.XML_ID"&&
					$basketPropertyList['CODE'] !== "SUM_OF_CHARGE"
				)
				{
					$basketValues['PROPS'][] = $basketPropertyList;
				}
			}

			$basketValues['FORMATED_SUM'] = SaleFormatCurrency($basketValues["PRICE"] * $basketValues['QUANTITY'], $basketValues["CURRENCY"]);

			$basket[$basketValues['ID']] = $basketValues;
		}

		// fetching all properties
		$this->obtainBasketProps($basket);

		$cached["BASKET"] = $basket;
	}

	/**
	 * Function fills all required data about basket item properties
	 *
	 * @param mixed[] $arBasketItems 		List of basket items
	 * @return mixed[] Basket items
	 */
	public function obtainBasketProps(&$arBasketItems)
	{
		// prepare some indexes
		$arElementIds = array(); // a collection of PRODUCT_IDs and parent PRODUCT_IDs
		$arSku2Parent = array(); // a mapping SKU PRODUCT_IDs to PARENT PRODUCT_IDs
		$arParents = array(); // also
		$arSkuProps = array();

		if(self::isNonemptyArray($arBasketItems))
		{
			foreach($arBasketItems as &$arItem)
			{
				if ($arItem['PARENT'])
				{
					$arElementIds[] = $arItem['PARENT']["ID"];
					$arSku2Parent[$arItem["PRODUCT_ID"]] = (int)$arItem['PARENT']["ID"];

					$arParents[$arItem["PRODUCT_ID"]]["PRODUCT_ID"] = (int)$arItem['PARENT']["ID"];
					$arParents[$arItem["PRODUCT_ID"]]["IBLOCK_ID"] = (int)$arItem['PARENT']["IBLOCK_ID"];
				}

				$arElementIds[] = (int)$arItem["PRODUCT_ID"];
				if (is_array($arItem['PROPS']))
				{
					foreach ($arItem['PROPS'] as $prop)
					{
						if (!empty($prop['CODE']) && !in_array($prop['CODE'], $arSkuProps))
						{
							$arSkuProps[] = $prop['CODE'];
						}
					}
				}
			}

			// fetching iblock props
			$this->obtainBasketPropsElement($arBasketItems, $arElementIds, $arSku2Parent);

			// fetching sku props, if any
			$this->obtainBasketPropsSKU($arBasketItems, $arSkuProps, $arParents);
		}

		return $arBasketItems;
	}

	/**
	 * For each basket items it fills information about properties stored in
	 *
	 * @param mixed[] $arBasketItems		List of basket items
	 * @param mixed[] $arElementIds			Array of element id
	 * @param mixed[] $arSku2Parent			Mapping between sku ids and their parent ids
	 * @return void
	 */
	public function obtainBasketPropsElement(&$arBasketItems, $arElementIds, $arSku2Parent)
	{
		$arImgFields = array("PREVIEW_PICTURE", "DETAIL_PICTURE");

		// get BASKET product properties data (from iblocks): id, pictures and some any PROPERTY_*
		$productProperties = $this->obtainProductProps($arElementIds, array_merge(array("ID"), $arImgFields, $this->arParams['CUSTOM_SELECT_PROPS']));

		if (self::isNonemptyArray($arBasketItems))
		{
			foreach ($arBasketItems as &$item)
			{
				// catalog-specific logic farther
				if(!$this->cameFromCatalog($item))
				{
					continue;
				}

				// merge items with properties we obtained by calling $this->obtainProductProps(): pictures and PROPERTY_*
				if (array_key_exists($item["PRODUCT_ID"], $productProperties) && is_array($productProperties[$item["PRODUCT_ID"]]))
				{
					foreach ($productProperties[$item["PRODUCT_ID"]] as $key => $value)
					{
						if (strpos($key, "PROPERTY_") !== false || in_array($key, $arImgFields))
						{
							$item[$key] = $value;
						}
					}
				}

				// if we have SKU product with parent...
				if (array_key_exists($item["PRODUCT_ID"], $arSku2Parent)) // if sku element doesn't have value of some property - we'll show parent element value instead
				{
					$arFieldsToFill = array_merge($this->arParams['CUSTOM_SELECT_PROPS'], $arImgFields); // fields to be filled with parents' values if empty
					foreach ($arFieldsToFill as $field)
					{
						if(!strlen($field)) continue;

						$fieldVal = (in_array($field, $arImgFields)) ? $field : $field."_VALUE";
						$parentId = $arSku2Parent[$item["PRODUCT_ID"]];

						if ((!isset($item[$fieldVal]) || (isset($item[$fieldVal]) && strlen($item[$fieldVal]) == 0))
							&& (isset($productProperties[$parentId][$fieldVal]) && !empty($productProperties[$parentId][$fieldVal]))) // can be array or string
						{
							$item[$fieldVal] = $productProperties[$parentId][$fieldVal];
						}
					}
				}

				// resampling picture
				if(intval($item["DETAIL_PICTURE"]))
				{
					$pict = $item["DETAIL_PICTURE"];
				}
				else
				{
					$pict = $item["PREVIEW_PICTURE"];
				}

				if($pict)
				{
					$arImage = CFile::GetFileArray($pict);
					if ($arImage && ($this->arParams['PICTURE_WIDTH'] || $this->arParams['PICTURE_HEIGHT']))
					{
						$arFileTmp = CFile::ResizeImageGet(
							$arImage,
							array("width" => $this->arParams['PICTURE_WIDTH'], "height" => $this->arParams['PICTURE_HEIGHT']),
							$this->arParams['PICTURE_RESAMPLE_TYPE'],
							true
						);

						$item["PICTURE"] = array_change_key_case($arFileTmp, CASE_UPPER);
					}
					else
					{
						$item["PICTURE"] = $arImage;
					}
				}
			}
		}
	}

	/**
	 * Creates an array of iBlock properties for the elements with certain IDs
	 *
	 * @param mixed[] $elementIdList 		$arElementIds Array of element id.
	 * @param mixed[] $select 			Fields to select.
	 * @return mixed[] 			Array of properties' values in the form of array("ELEMENT_ID" => array of props)
	 */
	public function obtainProductProps($elementIdList, $select)
	{
		if (!$this->useIBlock)
			return array();

		if (empty($elementIdList))
			return array();

		$productDataList = array();

		$productDataRow = \CIBlockElement::GetList(
			array("SORT" => "ASC"),
			array(
				"ID" => $elementIdList
			),
			false,
			false,
			$select
		);

		while ($product = $productDataRow->GetNext())
		{
			$productDataList[$product['ID']] = $product;
		}

		return $productDataList;
	}

	/**
	 * For each basket items it fills information about SKU properties stored in
	 *
	 * @param mixed[] $arBasketItems		List of basket items
	 * @param mixed[] $arSkuProps		Sku properties to search for
	 * @param mixed[] $arParents		Specially formed array, see code below
	 * @return void
	 */
	public function obtainBasketPropsSKU(&$arBasketItems, $arSkuProps, $arParents)
	{
		$arRes = array();
		$arSkuIblockID = array();

		if (self::isNonemptyArray($arBasketItems) && self::isNonemptyArray($arParents))
		{
			foreach ($arBasketItems as &$arItem)
			{
				// catalog-specific logic farther
				if(!$this->cameFromCatalog($arItem))
					continue;

				if (array_key_exists($arItem["PRODUCT_ID"], $arParents))
				{
					$arSKU = \CCatalogSku::GetInfoByProductIBlock($arParents[$arItem["PRODUCT_ID"]]["IBLOCK_ID"]);

					if (!array_key_exists($arSKU["IBLOCK_ID"], $arSkuIblockID))
						$arSkuIblockID[$arSKU["IBLOCK_ID"]] = $arSKU;

					$arItem["IBLOCK_ID"] = $arSKU["IBLOCK_ID"];
					$arItem["SKU_PROPERTY_ID"] = $arSKU["SKU_PROPERTY_ID"];
				}
			}
			unset($arItem);

			if($this->useIBlock)
			{
				if(!self::isNonemptyArray($arSkuProps))
				{
					$arSkuProps = array();
				}

				foreach ($arSkuIblockID as $skuIblockId => $arSKU)
				{
					// possible props values
					$rsProps = CIBlockProperty::GetList(
						array('SORT' => 'ASC', 'ID' => 'ASC'),
						array('IBLOCK_ID' => $skuIblockId, 'ACTIVE' => 'Y')
					);

					while ($arProp = $rsProps->Fetch())
					{
						if ($arProp['PROPERTY_TYPE'] == 'L' || $arProp['PROPERTY_TYPE'] == 'E'
							|| ($arProp['PROPERTY_TYPE'] == 'S' && $arProp['USER_TYPE'] == 'directory'))
						{
							if ($arProp['XML_ID'] == 'CML2_LINK')
								continue;

							if (!in_array($arProp['CODE'], $arSkuProps))
								continue;

							$arRes[$skuIblockId][$arProp['ID']] = array(
								'ID' => $arProp['ID'],
								'CODE' => $arProp['CODE'],
								'NAME' => $arProp['NAME'],
								'TYPE' => $arProp['PROPERTY_TYPE'],
								'VALUES' => array()
							);

							if ($arProp['PROPERTY_TYPE'] == 'L')
							{
								$arValues = array();
								$rsPropEnums = CIBlockProperty::GetPropertyEnum($arProp['ID']);
								while ($arEnum = $rsPropEnums->Fetch())
								{
									$arValues[$arEnum['ID']] = array(
										'ID' => $arEnum['ID'],
										'NAME' => $arEnum['VALUE'],
										'PICT' => false
									);
								}

								$arRes[$skuIblockId][$arProp['ID']]['VALUES'] = $arValues;
							}
							elseif ($arProp['PROPERTY_TYPE'] == 'E')
							{
								$arValues = array();
								$rsPropEnums = Iblock\ElementTable::getList(
									array(
										'order' => array('SORT' => 'ASC'),
										'filter' => array('IBLOCK_ID' => $arProp['LINK_IBLOCK_ID'], 'ACTIVE' => 'Y'),
										'select' => array('ID', 'NAME', 'PREVIEW_PICTURE')
									)
								);

								while ($arEnum = $rsPropEnums->fetch())
								{
									$arEnum['PREVIEW_PICTURE'] = CFile::GetFileArray($arEnum['PREVIEW_PICTURE']);

									if (!is_array($arEnum['PREVIEW_PICTURE']))
										continue;

									$productImg = CFile::ResizeImageGet($arEnum['PREVIEW_PICTURE'], array('width'=>80, 'height'=>80), BX_RESIZE_IMAGE_PROPORTIONAL, false, false);

									$arEnum['PREVIEW_PICTURE']['SRC'] = $productImg['src'];

									$arValues[$arEnum['ID']] = array(
										'ID' => $arEnum['ID'],
										'NAME' => $arEnum['NAME'],
										'SORT' => $arEnum['SORT'],
										'PICT' => $arEnum['PREVIEW_PICTURE']
									);
								}

								$arRes[$skuIblockId][$arProp['ID']]['VALUES'] = $arValues;
							}
							elseif ($arProp['PROPERTY_TYPE'] == 'S' && $arProp['USER_TYPE'] == 'directory')
							{
								$arValues = array();
								if ($this->useHL)
								{
									$hlBlockResult = HL\HighloadBlockTable::getList(array("filter" => array("TABLE_NAME" => $arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"])));

									$hlBlock = $hlBlockResult->fetch();

									if ($hlBlock)
									{
										$entity = HL\HighloadBlockTable::compileEntity($hlBlock);
										$entity_data_class = $entity->getDataClass();
										$rsData = $entity_data_class::getList();

										while ($arData = $rsData->fetch())
										{
											$arValues[$arData['ID']] = array(
												'ID' => $arData['ID'],
												'NAME' => $arData['UF_NAME'],
												'SORT' => $arData['UF_SORT'],
												'FILE' => $arData['UF_FILE'],
												'PICT' => '',
												'XML_ID' => $arData['UF_XML_ID']
											);
										}

										$arRes[$skuIblockId][$arProp['ID']]['VALUES'] = $arValues;
									}
								}
							}
						}
					}
				}

				foreach ($arBasketItems as &$arItem) // for each item in the basket
				{
					// catalog-specific logic farther: iblocks, catalogs and other friends
					if(!$this->cameFromCatalog($arItem))
						continue;

					$arSelectSkuProps = array();

					foreach ($arSkuProps as $prop)
						$arSelectSkuProps[] = "PROPERTY_".$prop;

					if (isset($arItem["IBLOCK_ID"]) && intval($arItem["IBLOCK_ID"]) > 0 && array_key_exists($arItem["IBLOCK_ID"], $arRes))
					{
						$arItem["SKU_DATA"] = $arRes[$arItem["IBLOCK_ID"]];

						$arUsedValues = array();
						$arTmpRes = array();

						$arOfFilter = array(
							"IBLOCK_ID" => $arItem["IBLOCK_ID"],
							"PROPERTY_".$arSkuIblockID[$arItem["IBLOCK_ID"]]["SKU_PROPERTY_ID"] => $arParents[$arItem["PRODUCT_ID"]]["PRODUCT_ID"]
						);

						$rsOffers = CIBlockElement::GetList(
							array(),
							$arOfFilter,
							false,
							false,
							array_merge(array("ID"), $arSelectSkuProps)
						);

						while ($arOffer = $rsOffers->fetch())
						{
							foreach ($arSkuProps as $prop)
							{
								if (!empty($arOffer["PROPERTY_".$prop."_VALUE"]) &&
									(!is_array($arUsedValues[$arItem["PRODUCT_ID"]][$prop]) || !in_array($arOffer["PROPERTY_".$prop."_VALUE"], $arUsedValues[$arItem["PRODUCT_ID"]][$prop])))
									$arUsedValues[$arItem["PRODUCT_ID"]][$prop][] = $arOffer["PROPERTY_".$prop."_VALUE"];
							}
						}

						if (!empty($arUsedValues))
						{
							// add only used values to the item SKU_DATA
							foreach ($arRes[$arItem["IBLOCK_ID"]] as $propId => $arProp)
							{
								if (!array_key_exists($arProp["CODE"], $arUsedValues[$arItem["PRODUCT_ID"]]))
									continue;

								$propValues = array();
								$skuType = '';
								foreach ($arProp["VALUES"] as $valId => $arValue)
								{
									// properties of various type have different values in the used values data
									if (($arProp["TYPE"] == "L" && in_array($arValue["NAME"], $arUsedValues[$arItem["PRODUCT_ID"]][$arProp["CODE"]]))
										|| ($arProp["TYPE"] == "E" && in_array($arValue["ID"], $arUsedValues[$arItem["PRODUCT_ID"]][$arProp["CODE"]]))
										|| ($arProp["TYPE"] == "S" && in_array($arValue["XML_ID"], $arUsedValues[$arItem["PRODUCT_ID"]][$arProp["CODE"]]))
									)
									{
										if ($arProp["TYPE"] == "S")
										{
											$arTmpFile = CFile::GetFileArray($arValue["FILE"]);
											$tmpImg = CFile::ResizeImageGet($arTmpFile, array('width'=>30, 'height'=>30), BX_RESIZE_IMAGE_PROPORTIONAL, true);
											$arValue['PICT'] = array_change_key_case($tmpImg, CASE_UPPER);

											$skuType = 'image';
										}
										else
											$skuType = 'link';

										$propValues[$valId] = $arValue;
									}
								}

								$arTmpRes['n'.$propId] = array(
									'CODE' => $arProp["CODE"],
									'NAME' => $arProp["NAME"],
									'SKU_TYPE' => $skuType,
									'VALUES' => $propValues
								);
							}
						}

						$arItem["SKU_DATA"] = $arTmpRes;
					}

					if(self::isNonemptyArray($arItem['PROPS']))
					{
						foreach($arItem['PROPS'] as $v => $prop) // for each property of basket item
						{
							// search for sku property that matches current one
							// establishing match based on codes even if the code may not set
							$code = $prop['CODE'];
							$arItem["PROPERTY_{$code}_VALUE"] = $prop['VALUE'];

							if(self::isNonemptyArray($arItem['SKU_DATA']))
							{
								foreach($arItem['SKU_DATA'] as $spIndex => $skuProp)
								{
									if($skuProp['CODE'] == $code) // if match found
									{
										$arItem['PROPS'][$v]['SKU_PROP'] = $spIndex;
										$arItem['PROPS'][$v]['SKU_TYPE'] = $skuProp['SKU_TYPE'];

										if(self::isNonemptyArray($skuProp['VALUES']))
										{
											foreach($skuProp['VALUES'] as $spValue) // search for a particular value of our property
											{
												if ($skuProp['SKU_TYPE'] == 'image')
													$match = $prop["VALUE"] == $spValue["NAME"] || $prop["VALUE"] == $spValue["XML_ID"]; // for "image" prop we got one condition
												else
													$match = $prop["VALUE"] == $spValue["NAME"]; // otherwise - the other

												if($match)
												{
													$arItem['PROPS'][$v]['SKU_VALUE'] = $spValue;
													break;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Function gets order properties from database
	 * @param mixed[] $cached Cached data taken from obtainDataCachedStructure()
	 * @return void
	 */
	protected function obtainProps(&$cached)
	{
		if (empty($this->dbResult["ID"]))
			return;

		$props = array();

		$groupList = array();
		$groupListActiveId = array();

		$order = $this->order;
		$propertyCollection = $order->getPropertyCollection();
		$groupData = $propertyCollection->getGroups();
		foreach ($groupData as $group)
		{
			$groupList[$group['ID']] = $group;
		}

		/**@var Bitrix\Sale\PropertyValue*/
		foreach ($propertyCollection as $property)
		{
			if (empty($this->arParams["PROP_" . $this->dbResult["PERSON_TYPE_ID"]])
				|| !in_array($property->getField("ORDER_PROPS_ID"), $this->arParams["PROP_" . $this->dbResult["PERSON_TYPE_ID"]])
			)
			{
				/**@var Bitrix\Sale\PropertyValue $property */
				$propertyList = array_merge($property->getFieldValues(), $property->getProperty());

				$propertyList['GROUP_NAME'] = $groupList[$propertyList['PROPS_GROUP_ID']]['NAME'];
				$propertyList['GROUP_SORT'] = $groupList[$propertyList['PROPS_GROUP_ID']]['SORT'];

				if (!in_array($propertyList["PROPS_GROUP_ID"], $groupListActiveId))
				{
					$propertyList['SHOW_GROUP_NAME'] = 'Y';
					$groupListActiveId[] = $propertyList['PROPS_GROUP_ID'];
				}

				/** For compatibility*/
				$propertyList['PROP_ID'] = $propertyList['ORDER_PROPS_ID'];
				$propertyList['PROP_SORT'] = $propertyList['SORT'];

				if ($propertyList["ACTIVE"] == "Y" && $propertyList["UTIL"] == "N")
				{
					if (empty($propertyList['VALUE']))
					{
						continue;
					}

					if ($propertyList['CODE'] === 'FIO')
					{
						$cached['FIO'] = $propertyList['VALUE'];
					}

					if ($propertyList['MULTIPLE'] === 'Y')
					{
						if ($propertyList['TYPE'] === 'FILE')
						{
							$fileList = "";
							foreach ($propertyList["VALUE"] as $fileElement)
							{
								if (is_array($fileElement))
									$fileId = $fileElement['ID'];
								else
									$fileId = $fileElement;

								if ((int)($fileId) > 0)
									$fileList .= CFile::ShowFile((int)$fileId, 0, 90, 90, true)."<br/>";
							}
							$propertyList["VALUE"] = $fileList;
						}
						elseif ($propertyList["TYPE"] === "LOCATION")
						{
							$location = '';
							foreach ($propertyList["VALUE"] as $locationElement)
							{
								$location = $location.Location\Admin\LocationHelper::getLocationStringByCode($locationElement)."<br/>";
							}
							$propertyList["VALUE"] = $location;
						}
						elseif ($propertyList["TYPE"] === 'ENUM')
						{
							$enumList = array();
							if (is_array($propertyList["VALUE"]))
							{
								foreach ($propertyList["VALUE"] as $value)
								{
									$enumList[] = $propertyList["OPTIONS"][$value];
								}
							}
							else
							{
								$enumList[] = $propertyList["OPTIONS"][$propertyList["VALUE"]];
							}
							$propertyList["VALUE"] = serialize($enumList);
						}
						else
						{
							$propertyList["VALUE"] = serialize($propertyList["VALUE"]);
						}
					}
					else
					{
						if ($propertyList['TYPE'] === 'FILE')
						{
							$propertyList["VALUE"] = CFile::ShowFile($propertyList["VALUE"]['ID'], 0, 90, 90, true);
						}
						elseif ($propertyList["TYPE"] === "LOCATION")
						{
							$locationName = Location\Admin\LocationHelper::getLocationStringByCode($propertyList["VALUE"]);
							$propertyList["VALUE"] = $locationName;
						}
						elseif ($propertyList["TYPE"] === 'ENUM')
						{
							$propertyList["VALUE"] = $propertyList["OPTIONS"][$propertyList["VALUE"]];
						}
					}

					$props[] = $propertyList;
				}
			}
		}

		$cached["ORDER_PROPS"] = $props;
	}

	protected function loadOrder($id)
	{
		if ($this->options['USE_ACCOUNT_NUMBER'])
		{
			$this->order = Sale\Order::loadByAccountNumber($id);
		}

		if ($this->order)
		{
			$this->requestData["ID"] = $this->order->getId();
		}
		elseif ((int)$id > 0)
		{
			$this->order = Sale\Order::load($id);
		}
	}

	/**
	 * @return void
	 */
	protected function checkOrder()
	{
		global $USER;

		if (!($this->order) || ($this->order->getUserId() !== $USER->GetID() && empty($this->requestData['hash'])))
		{
			$this->doCaseOrderIdNotSet();
		}
	}

	/**
	 * Perform reading main data from database, no cache is used for it
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function obtainDataOrder()
	{
		if (!($this->order))
		{
			$this->loadOrder($this->requestData["ID"]);
		}

		$this->checkOrder();

		$this->requestData["ID"] = $this->order->getId();

		$orderValues = $this->order->getFieldValues();

		if (empty($orderValues))
		{
			throw new Main\SystemException(
				str_replace("#ID#", $this->requestData["ID"], Localization\Loc::getMessage("SPOD_NO_ORDER")),
				self::E_ORDER_NOT_FOUND
			);
		}

		if (
			is_array($this->arParams['RESTRICT_CHANGE_PAYSYSTEM'])
			&& in_array($orderValues['STATUS_ID'], $this->arParams['RESTRICT_CHANGE_PAYSYSTEM'])
		)
		{
			$orderValues['LOCK_CHANGE_PAYSYSTEM'] = 'Y';
		}

		$shipmentOrder = array();
		/** @var Sale\Shipment $shipment*/
		$shipmentCollection = $this->order->getShipmentCollection();

		$trackingManager = Sale\Delivery\Tracking\Manager::getInstance();

		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem())
			{
				continue;
			}

			$shipmentItems = $shipment->getShipmentItemCollection();

			$shipmentFields = $shipment->getFieldValues();
			$shipmentFields['ITEMS'] = array();
			/** @var \Bitrix\Sale\ShipmentItem $item */
			foreach ($shipmentItems as $item)
			{
				$basketItem = $item->getBasketItem();
				if ($basketItem instanceof Sale\BasketItem)
				{
					$quantity = Sale\BasketItem::formatQuantity($item->getQuantity());
					$basketId =  $basketItem->getId();

					$shipmentFields['ITEMS'][$basketId] = array(
						'BASKET_ID' => $basketId,
						'QUANTITY' => $quantity
					);
				}
			}

			if ($shipmentFields["DELIVERY_ID"] > 0 && strlen($shipmentFields["TRACKING_NUMBER"]))
			{
				$shipmentFields["TRACKING_URL"] = $trackingManager->getTrackingUrl($shipmentFields["DELIVERY_ID"], $shipmentFields["TRACKING_NUMBER"]);
			}
			
			$shipmentOrder[] = $shipmentFields;
		}

		$orderValues['SHIPMENT'] = $shipmentOrder;

		$paymentOrder = array();

		$paymentCollection = $this->order->getPaymentCollection();

		/** @var \Bitrix\Sale\Payment $payment*/
		foreach ($paymentCollection as $payment)
		{
			$paymentFields = $payment->getFieldValues();
			$paymentFields['PAY_SYSTEM_NAME'] = htmlspecialcharsbx($paymentFields['PAY_SYSTEM_NAME']);
			$paymentFields['CHECK_DATA'] = CheckManager::getCheckInfo($payment);
			$paymentOrder[$paymentFields['ID']] = $paymentFields;
		}
		
		$orderValues['PAYMENT'] = $paymentOrder;

		$orderValues['IS_ALLOW_PAY'] = $this->order->isAllowPay() ? 'Y' : 'N';

		$this->dbResult = $orderValues;
	}

	/**
	 * Function gets user info from database, no cache is used for it
	 * @return void
	 */
	protected function obtainDataUser()
	{
		$resultUser = Main\UserTable::getById($this->dbResult["USER_ID"]);
		$user = $resultUser->fetch();

		foreach ($user as $key => $value)
		{
			if ($value instanceof Main\Type\Date
				|| $value instanceof Main\Type\DateTime)
			{
				$user[$key] = $value->toString();
			}
		}

		$this->dbResult["USER"] = $user;

	}

	/**
	 * Function accuires all required fine-cacheable information to form $arResult.
	 * To pick up some additional data to the cached part of $arResult, make another method that modifies $cachedData and call it here.
	 * This method should be called only after obtainDataCachedStatic()
	 *
	 * @param mixed[] $cachedData Cached data taken from getDataCached()
	 * @return void
	 */
	protected function obtainDataCachedStructure(&$cachedData)
	{
		$this->obtainProps($cachedData);
		$this->obtainBasket($cachedData);
		$this->obtainDeliveryStore($cachedData);
		$this->obtainPropertyNames($cachedData);
		$this->obtainTaxes($cachedData);
	}

	/**
	 * Function gets pay system info from database, no cache is used here
	 * @return void
	 */
	protected function obtainDataPaySystem()
	{
		if (empty($this->dbResult["ID"]))
			return;

		foreach ($this->dbResult['PAYMENT'] as &$payment)
		{
			if (intval($payment["PAY_SYSTEM_ID"]))
			{
				$payment["PAY_SYSTEM"] = \Bitrix\Sale\PaySystem\Manager::getById($payment["PAY_SYSTEM_ID"]);
				$payment["PAY_SYSTEM"]['NAME'] = htmlspecialcharsbx($payment["PAY_SYSTEM"]['NAME']);
				$payment["PAY_SYSTEM"]["SRC_LOGOTIP"] = CFile::GetPath($payment["PAY_SYSTEM"]['LOGOTIP']);
			}
			if ($payment["PAID"] != "Y" && $this->dbResult["CANCELED"] != "Y" &&  $this->dbResult["ALLOW_PAY"] != "N")
			{
				$payment['BUFFERED_OUTPUT'] = '';
				$payment['ERROR'] = '';
				$service = new \Bitrix\Sale\PaySystem\Service($payment["PAY_SYSTEM"]);
				if ($service)
				{
					$payment["CAN_REPAY"] = "Y";
					if ($service->getField("NEW_WINDOW") == "Y")
					{
						$payment["PAY_SYSTEM"]["PSA_ACTION_FILE"] = htmlspecialcharsbx($this->arParams["PATH_TO_PAYMENT"]).'?ORDER_ID='.urlencode(urlencode($this->dbResult["ACCOUNT_NUMBER"])).'&PAYMENT_ID='.$payment['ID'];
						if (!empty($this->requestData['hash']))
						{
							$payment["PAY_SYSTEM"]["PSA_ACTION_FILE"] .= '&HASH='.htmlspecialcharsbx($this->requestData['hash']);
						}
					}
					else
					{
						$handlerFolder = Sale\PaySystem\Manager::getPathToHandlerFolder($service->getField('ACTION_FILE'));
						$pathToAction = Main\Application::getDocumentRoot().$handlerFolder;
						$pathToAction = str_replace("\\", "/", $pathToAction);
						while (substr($pathToAction, strlen($pathToAction) - 1, 1) == "/")
							$pathToAction = substr($pathToAction, 0, strlen($pathToAction) - 1);
						if (file_exists($pathToAction))
						{
							if (is_dir($pathToAction) && file_exists($pathToAction."/payment.php"))
								$pathToAction .= "/payment.php";
							$payment["PAY_SYSTEM"]["PSA_ACTION_FILE"] = $pathToAction;
						}

						if ($payment["PAY_SYSTEM"]["NEW_WINDOW"] !== 'Y')
						{
							/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
							$paymentCollection =  $this->order->getPaymentCollection();

							if ($paymentCollection)
							{
								/** @var \Bitrix\Sale\Payment $paymentItem */
								$paymentItem = $paymentCollection->getItemById($payment['ID']);
								if ($paymentItem)
								{
									$initResult = $service->initiatePay($paymentItem, null, Sale\PaySystem\BaseServiceHandler::STRING);
									if ($initResult->isSuccess())
										$payment['BUFFERED_OUTPUT'] = $initResult->getTemplate();
									else
										$payment['ERROR'] = implode('\n', $initResult->getErrorMessages());
								}
							}
						}
					}

					$payment["PAY_SYSTEM"]["PSA_NEW_WINDOW"] = $payment["PAY_SYSTEM"]["NEW_WINDOW"];
				}
			}
		}
		unset($payment);

		// for compatibility
		$firstPaySystem = reset($this->dbResult['PAYMENT']);
		$this->dbResult['PAY_SYSTEM'] = $firstPaySystem['PAY_SYSTEM'];
		$this->dbResult['CAN_REPAY'] = $firstPaySystem['CAN_REPAY'];
	}

	/**
	 * Function performs a conversion between a shared cache and the particular structure of our $arResult
	 * @param mixed[] $cached Cached data taken from obtainDataReferences()
	 * @return mixed[] Data structure that is appropriate for our $arResult
	 */
	protected function adaptCachedReferences($cached)
	{
		$formed = array();

		// form person type
		$formed["PERSON_TYPE"] = $cached['PERSON_TYPE'][$this->dbResult["PERSON_TYPE_ID"]];

		// form status
		$formed['STATUS'] = $cached['STATUS'][$this->dbResult["STATUS_ID"]];

		// form delivery
		foreach ($this->dbResult['SHIPMENT'] as $shipment)
		{
			$shipment['DELIVERY'] = $cached['DELIVERY'][$shipment["DELIVERY_ID"]];
			$shipment['DELIVERY']['STORE'] = \Bitrix\Sale\Delivery\ExtraServices\Manager::getStoresList($shipment["DELIVERY_ID"]);
			$formed['SHIPMENT'][] = $shipment;
		}

		$formed['DELIVERY'] = $formed['SHIPMENT'][0]['DELIVERY'];

		return $formed;
	}

	/**
	 * Function returns reference data as shared cache between this component and sale.personal.order.list.
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function obtainDataReferences()
	{
		if ($this->startCache(array('spo-shared')))
		{
			try
			{
				$cachedData = array();

				// Person type
				$cachedData['PERSON_TYPE'] = Sale\PersonType::load($this->dbResult['LID']);

				// Save statuses for Filter form
				$cachedData['STATUS'] = array();

				$listStatusNames = Sale\OrderStatus::getAllStatusesNames(LANGUAGE_ID);

				foreach($listStatusNames as $key => $data)
				{
					$cachedData['STATUS'][$key] = array('ID'=>$key,'NAME'=>$data);
				}

				$cachedData['PAYSYS'] = array();

				$paySystemsList = Sale\PaySystem\Manager::getList(array());

				while ($paySystem = $paySystemsList->fetch())
				{
					$paySystem['NAME'] = htmlspecialcharsbx($paySystem['NAME']);
					$cachedData['PAYSYS'][$paySystem["ID"]] = $paySystem;
				}

				$cachedData['DELIVERY'] = array();
				$dbDelivery = \Bitrix\Sale\Delivery\Services\Table::getList();

				$deliveryService = array();
				while ($delivery = $dbDelivery->fetch())
					$deliveryService[$delivery['ID']] = $delivery;

				foreach ($deliveryService as $delivery)
				{
					$cachedData['DELIVERY'][$delivery["ID"]] = $delivery;

					if ($delivery['PARENT_ID'])
					{
						$cachedData['DELIVERY'][$delivery["ID"]]['NAME'] = htmlspecialcharsbx($deliveryService[$delivery['PARENT_ID']]['NAME'].':'.$delivery['NAME']);
						if (empty($delivery['LOGOTIP']))
							$cachedData['DELIVERY'][$delivery["ID"]]['LOGOTIP'] = $deliveryService[$delivery['PARENT_ID']]['LOGOTIP'];
					}
					else
					{
						$cachedData['DELIVERY'][$delivery["ID"]]['NAME'] = htmlspecialcharsbx($delivery['NAME']);
					}
				}
			}
			catch (Exception $e)
			{
				$this->abortCache();
				throw $e;
			}

			$this->endCache($cachedData);

		}
		else
			$cachedData = $this->getCacheData();

		$this->dbResult = array_merge($this->dbResult, $this->adaptCachedReferences($cachedData));
	}

	/**
	 * Function create cache id.
	 *
	 * @return array
	 */
	protected function createCacheId()
	{
		global $USER;
		global $APPLICATION;

		return array(
			$APPLICATION->GetCurPage(),
			$this->dbResult["ID"],
			$this->dbResult["PERSON_TYPE_ID"],
			$this->dbResult["DATE_UPDATE"]->toString(),
			$this->useCatalog,
			$this->arParams["CACHE_GROUPS"] === "N" ? false : $USER->GetGroups()
		);
	}

	/**
	 * Function contains a mechanism for cacheing data in the component
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function obtainDataCached()
	{
		if ($this->startCache($this->createCacheId()))
		{
			try
			{
				// so we got an array, which is stored in a cache. After all we merge $this->dbResult with $cachedData
				$cachedData = array();
				$this->obtainDataCachedStructure($cachedData);
			}
			catch (Exception $e)
			{
				$this->abortCache();
				throw $e;
			}

			$this->endCache($cachedData);
		}
		else
			$cachedData = $this->getCacheData();

		$this->dbResult = array_merge($this->dbResult, $cachedData);
	}

	/**
	 * Fetches all required data from database. Everything that connected with data obtaining lies here
	 *
	 * @return void
	 */
	protected function obtainDataShipmentBasket()
	{
		$basket = $this->dbResult['BASKET'];
		foreach ($this->dbResult['SHIPMENT'] as &$shipment)
		{
			if (!$shipment['ITEMS'])
			{
				continue;
			}
			
			foreach ($shipment['ITEMS'] as $i => &$item)
			{
				if (isset($basket[$item['BASKET_ID']]))
				{
					$item['NAME'] = $basket[$item['BASKET_ID']]['NAME'];
					$item['MEASURE_NAME'] = $basket[$item['BASKET_ID']]['MEASURE_NAME'];
				}
				else
				{
					unset($shipment['ITEMS'][$i]);
				}
			}
			unset($item);
		}
		unset($shipment);
	}


	/**
	 * Function aggregates basket's data from basket items
	 *
	 */
	protected function obtainDataBasket()
	{
		$this->dbResult["WEIGHT_UNIT"] = $this->options['WEIGHT_UNIT'];
		$this->dbResult["WEIGHT_KOEF"] = $this->options['WEIGHT_K'];

		if (self::isNonemptyArray($this->dbResult['BASKET']))
		{
			foreach ($this->dbResult['BASKET'] as &$arItem)
			{
				$this->dbResult['PRODUCT_SUM'] += $arItem["PRICE"] * $arItem['QUANTITY'];
				$arItem["QUANTITY"] = doubleval($arItem["QUANTITY"]);
				$this->dbResult["ORDER_WEIGHT"] += $arItem["WEIGHT"] * $arItem["QUANTITY"];
			}
		}
	}

	protected function obtainData()
	{
		// Do not reorder calls without a strong need.
		// Data obtain order is important and calls depend on each other.

		$this->obtainDataOrder();
		$this->obtainDataUser();

		// everything that can be well-cached is taken from the following calls:
		$this->obtainDataReferences(); // references
		$this->obtainDataCached(); // the rest of the important data

		// it depends on data taken from obtainDataCached(), so do not relocate
		$this->obtainDataPaySystem();
		$this->obtainDataBasket();
		$this->obtainDataShipmentBasket();
	}

	/**
	 * Function formats links in arResult
	 * @return void
	 */
	protected function formatResultUrls()
	{
		if ( $this->arResult["CAN_CANCEL"] === "Y")
		{
			$this->arResult["URL_TO_CANCEL"] = CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_CANCEL"], array("ID" => urlencode(urlencode( $this->arResult["ACCOUNT_NUMBER"])))).'CANCEL=Y';
		}
		if (empty ($this->arParams["PATH_TO_COPY"]))
		{
			$urlSign = (strstr($this->arParams["PATH_TO_LIST"], "?")) ? '&' : "?";
			$this->arResult["URL_TO_COPY"] = CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_LIST"].$urlSign.'ID=#ID#', array("ID" => urlencode(urlencode( $this->arResult["ACCOUNT_NUMBER"]))))."&amp;COPY_ORDER=Y";
		}
		else
		{
			$this->arResult["URL_TO_COPY"] = CComponentEngine::makePathFromTemplate($this->arParams["PATH_TO_COPY"], array("ID" => urlencode(urlencode( $this->arResult["ACCOUNT_NUMBER"]))));
		}
		$this->arResult["URL_TO_LIST"] = $this->arParams["PATH_TO_LIST"];
		$this->arResult["SITE_ID"] =  $this->arResult["LID"];
	}

	/**
	 * Function formats price info in arResult
	 * @return void
	 */
	protected function formatResultPrices()
	{
		$arResult =& $this->arResult;

		$arResult["PRICE_FORMATED"] = SaleFormatCurrency($arResult["PRICE"], $arResult["CURRENCY"]);

		$arResult["PRODUCT_SUM_FORMATED"] = SaleFormatCurrency($arResult["PRODUCT_SUM"], $arResult["CURRENCY"]);

		$arResult["PRICE_DELIVERY_FORMATED"] = SaleFormatCurrency($arResult['PRICE_DELIVERY'], $arResult["CURRENCY"]);
		foreach ($arResult['PAYMENT'] as &$payment)
		{
			$payment["PRICE_FORMATED"] = SaleFormatCurrency(floatval($payment['SUM']), $arResult["CURRENCY"]);
		}
		unset($payment);

		foreach ($arResult['SHIPMENT'] as &$shipment)
		{
			$shipment["PRICE_DELIVERY_FORMATED"] = SaleFormatCurrency(floatval($shipment['PRICE_DELIVERY']), $arResult["CURRENCY"]);
		}

		unset($shipment);

		if (doubleval($arResult["DISCOUNT_VALUE"]))
			$arResult["DISCOUNT_VALUE_FORMATED"] = SaleFormatCurrency($arResult["DISCOUNT_VALUE"], $arResult["CURRENCY"]);
		$arResult["CAN_CANCEL"] = (($arResult["CANCELED"]!="Y" && $arResult["STATUS_ID"]!="F" && $arResult["PAYED"]!="Y") ? "Y" : "N");
	}

	/**
	 * Function formats status info in arResult
	 * @return void
	 */
	protected function formatResultStatus()
	{
		$arResult =& $this->arResult;

		if (!empty($arResult["STATUS"]))
		{
			$arResult["STATUS"]["NAME"] = htmlspecialcharsEx($arResult["STATUS"]["NAME"]);
			if (doubleval($arResult["SUM_PAID"]))
				$arResult["SUM_PAID_FORMATED"] = SaleFormatCurrency($arResult["SUM_PAID"], $arResult["CURRENCY"]);
		}
	}

	/**
	 * Function formats user info in arResult
	 * @return void
	 */
	protected function formatResultUser()
	{
		$arResult =& $this->arResult;

		if (!empty($arResult['USER']) && is_array($arResult['USER']))
			$arResult["USER_NAME"] = CUser::FormatName(CSite::GetNameFormat(false), $arResult['USER'], true, false);
	}

	/**
	 * Function formats customer info in arResult
	 * @return void
	 */
	protected function formatResultPerson()
	{
		$arResult =& $this->arResult;

		if (!empty($arResult["PERSON_TYPE"]))
		{
			$arResult["PERSON_TYPE"]["NAME"] = htmlspecialcharsEx($arResult["PERSON_TYPE"]["NAME"]);
			$arResult["USER"]["PERSON_TYPE_NAME"] = htmlspecialcharsEx($arResult["PERSON_TYPE"]["NAME"]);

		}
	}

	/**
	 * Function formats pay system info in arResult
	 * @return void
	 */
	protected function formatResultPaySystem()
	{
		$arResult =& $this->arResult;

		if (!empty($arResult["PAY_SYSTEM"]))
			$arResult["PAY_SYSTEM"]["NAME"] = htmlspecialcharsEx($arResult["PAY_SYSTEM"]["NAME"]);
	}

	/**
	 * Function formats delivery system info in arResult
	 * @return void
	 */
	protected function formatResultDeliverySystem()
	{
		$arResult =& $this->arResult;

		$deliveryStatusList = Sale\DeliveryStatus::getAllStatusesNames(LANGUAGE_ID);

		foreach ($arResult['SHIPMENT'] as &$shipment)
		{
			if (!empty($shipment["DELIVERY_ID"]))
			{
				$shipment["DELIVERY"]["NAME"] = htmlspecialcharsEx($shipment["DELIVERY"]["NAME"]);
				$shipment["DELIVERY"]["SRC_LOGOTIP"] = CFile::GetPath($shipment["DELIVERY"]['LOGOTIP']);
				if (!strlen($shipment["DELIVERY"]["SRC_LOGOTIP"]))
				{
					$shipment["DELIVERY"]["SRC_LOGOTIP"] = '/bitrix/images/sale/logo-default-d.gif';
				}
			}

			$shipment['STORE_ID'] = Sale\Delivery\ExtraServices\Manager::getStoreIdForShipment($shipment['ID'], $shipment["DELIVERY_ID"]);

			// backward compatibility
			if ((int)$shipment['STORE_ID'] > 0)
			{
				$arResult['STORE_ID'] = $shipment['STORE_ID'];
			}

			$shipment['STATUS_NAME'] = $deliveryStatusList[$shipment['STATUS_ID']];
		}
		unset($shipment);

		if (!empty($arResult["DELIVERY"]))
		{
			if (!empty($arResult['DELIVERY_STORE_LIST']))
			{
				$arResult["DELIVERY"]['STORE_LIST'] = $arResult['DELIVERY_STORE_LIST'];
				unset($arResult['DELIVERY_STORE_LIST']);
			}
		}
	}

	/**
	 * Function formats order basket info in arResult
	 * @return void
	 */
	protected function formatResultBasket()
	{
		$arResult =& $this->arResult;

		if(self::isNonemptyArray($arResult['BASKET']))
		{
			foreach ($arResult["BASKET"] as $k => $arBasket)
			{
				$arBasket["WEIGHT_FORMATED"] = roundEx(doubleval($arBasket["WEIGHT"]/$arResult["WEIGHT_KOEF"]), SALE_WEIGHT_PRECISION)." ".$arResult["WEIGHT_UNIT"];
				$arBasket["PRICE_FORMATED"] = SaleFormatCurrency($arBasket["PRICE"], $arBasket["CURRENCY"]);
				$arBasket["BASE_PRICE_FORMATED"] = SaleFormatCurrency($arBasket["BASE_PRICE"], $arBasket["CURRENCY"]);

				if (doubleval($arBasket["DISCOUNT_PRICE"]))
				{
					$arBasket["DISCOUNT_PRICE_PERCENT"] = Sale\PriceMaths::roundPrecision($arBasket["DISCOUNT_PRICE"]*100 / ($arBasket["DISCOUNT_PRICE"] + $arBasket["PRICE"]));
					$arBasket["DISCOUNT_PRICE_PERCENT_FORMATED"] = Sale\BasketItem::formatQuantity($arBasket["DISCOUNT_PRICE_PERCENT"])."%";
					$arResult['SHOW_DISCOUNT_TAB'] = 'Y';
				}

				// backward compatibility
				$arBasket['MEASURE_TEXT'] = $arBasket['MEASURE_NAME'];

				$arResult["BASKET"][$k] = $arBasket;
			}
		}
	}

	/**
	 * Function formats taxes info in arResult
	 * @return void
	 */
	protected function formatResultTaxes()
	{
		$arResult =& $this->arResult;

		if(self::isNonemptyArray($arResult['TAX_LIST']))
			foreach ($arResult["TAX_LIST"] as $k => $tax)
			{
				$tax =& $arResult["TAX_LIST"][$k];

				if ($tax["IS_IN_PRICE"]=="Y")
					$tax["VALUE_FORMATED"] = " (".(($tax["IS_PERCENT"]=="Y") ? "".doubleval($tax["VALUE"])."%, " : "").Localization\Loc::getMessage("SPOD_SALE_TAX_INPRICE").")";
				else
					$tax["VALUE_FORMATED"] = " (".(($tax["IS_PERCENT"]=="Y") ? "".doubleval($tax["VALUE"])."%" : "").")";
				if (doubleval($tax["VALUE_MONEY"]))
					$tax["VALUE_MONEY_FORMATED"] = SaleFormatCurrency($tax["VALUE_MONEY"], $arResult["CURRENCY"]);
			}
		else
			$arResult["TAX_LIST"] = array();

		$arResult["TAX_VALUE_FORMATED"] = SaleFormatCurrency($arResult["TAX_VALUE"], $arResult["CURRENCY"]);
	}

	/**
	 * Function formats weight info in arResult
	 * @return void
	 */
	protected function formatResultWeight()
	{
		$arResult =& $this->arResult;

		$arResult["ORDER_WEIGHT_FORMATED"] = roundEx(
			doubleval($arResult["ORDER_WEIGHT"] / $arResult["WEIGHT_KOEF"]),
			SALE_WEIGHT_PRECISION)." ".$arResult["WEIGHT_UNIT"];
	}

	/**
	 * Move data read from database to a specially formatted $arResult
	 * @return void
	 */
	protected function formatResult()
	{
		$this->arResult = $this->dbResult;

		$this->formatDate($this->arResult);
		$this->formatResultPrices();
		$this->formatResultStatus();
		$this->formatResultUrls();
		$this->formatResultUser();
		$this->formatResultPerson();
		$this->formatResultDeliverySystem();
		$this->formatResultWeight();
		$this->formatResultBasket();
		$this->formatResultTaxes();
	}

	/**
	 * Move all errors to $arResult, if there were any
	 * @return void
	 */
	protected function formatResultErrors()
	{
		$errors = array();
		if (!empty($this->errorsFatal))
			$errors['FATAL'] = $this->errorsFatal;
		if (!empty($this->errorsNonFatal))
			$errors['NONFATAL'] = $this->errorsNonFatal;

		if (!empty($errors))
			$this->arResult['ERRORS'] = $errors;

		// backward compatiblity
		$error = each($this->errorsFatal);
		if (!empty($error))
			$this->arResult['ERROR_MESSAGE'] = $error['value'];
	}

	/**
	 * Function implements all the life cycle of the component
	 * @return void
	 */
	public function executeComponent()
	{
		try
		{
			$this->setFrameMode(false);
			$this->checkRequiredModules();

			$this->loadOptions();
			$this->checkAuthorized();
			$this->processRequest();

			$this->obtainData();
			$this->formatResult();

			$this->setTitle();
		}
		catch(Exception $e)
		{
			$this->errorsFatal[htmlspecialcharsEx($e->getCode())] = htmlspecialcharsEx($e->getMessage());
		}

		$this->formatResultErrors();

		$this->includeComponentTemplate();
	}

	/**
	 * Convert dates if date template set
	 * @param mixed[] array that date conversion performs in
	 * @return void
	 */
	protected function formatDate(&$arr)
	{
		if (strlen($this->arParams['ACTIVE_DATE_FORMAT']))
		{
			foreach ($this->orderDateFields2Convert as $fld)
			{
				if (!empty($arr[$fld]))
					$arr[$fld."_FORMATED"] = CIBlockFormatProperties::DateFormat($this->arParams['ACTIVE_DATE_FORMAT'], MakeTimeStamp($arr[$fld]));
			}
		}
	}

	/**
	 * Function checks whether a certain item came from 'catalog' module or not
	 * @param mixed[] $item An item from basket
	 * @return boolean
	 */
	public static function cameFromCatalog($item)
	{
		return $item['MODULE'] == 'catalog';
	}

	/**
	 * The callback that changes body encoding when nescessary. Feature doesn`t work here and in the previous version of the component. Left for backward compatibility.
	 * @param string $content page content
	 * @return void
	 */
	public static function changeBodyEncoding($content)
	{
		header("Content-Type: text/html; charset=".BX_SALE_ENCODING);
	}

	/**
	 * Function checks if it`s argument is a legal array for foreach() construction
	 * @param mixed $arr data to check
	 * @return boolean
	 */
	protected static function isNonemptyArray($arr)
	{
		return !empty($arr) && is_array($arr);
	}

	////////////////////////
	// Cache functions
	////////////////////////

	/**
	 * Function checks if cacheing is enabled in component parameters
	 * @return boolean
	 */
	final protected function getCacheNeed()
	{
		return	intval($this->arParams['CACHE_TIME']) > 0 &&
				$this->arParams['CACHE_TYPE'] != 'N' &&
				Config\Option::get("main", "component_cache_on", "Y") == "Y";
	}

	/**
	 * Function perform start of cache process, if needed
	 * @param mixed[]|string $cacheId An optional addition for cache key
	 * @return boolean True, if cache content needs to be generated, false if cache is valid and can be read
	 */
	final protected function startCache($cacheId = array())
	{
		if(!$this->getCacheNeed())
			return true;

		$this->currentCache = Data\Cache::createInstance();

		return $this->currentCache->startDataCache(intval($this->arParams['CACHE_TIME']), $this->getCacheKey($cacheId));
	}

	/**
	 * Function perform start of cache process, if needed
	 * @throws Main\SystemException
	 * @param bool|mixed[] $data Data to be stored in the cache
	 * @return void
	 */
	final protected function endCache($data = false)
	{
		if(!$this->getCacheNeed())
			return;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		$this->currentCache->endDataCache($data);
		$this->currentCache = null;
	}

	/**
	 * Function discard cache generation
	 * @throws Main\SystemException
	 * @return void
	 */
	final protected function abortCache()
	{
		if(!$this->getCacheNeed())
			return;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		$this->currentCache->abortDataCache();
		$this->currentCache = null;
	}

	/**
	 * Function return data stored in cache
	 * @throws Main\SystemException
	 * @return bool|mixed[] Data from cache
	 */
	final protected function getCacheData()
	{
		if(!$this->getCacheNeed())
			return false;

		if($this->currentCache == 'null')
			throw new Main\SystemException('Cache were not started');

		return $this->currentCache->getVars();
	}


	/**
	 * Function leaves the ability to modify cache key in future.
	 * @param array $cacheId
	 * @return string Cache key to be used in CPHPCache()
	 */
	final protected function getCacheKey($cacheId = array())
	{
		if(!is_array($cacheId))
			$cacheId = array((string) $cacheId);

		$cacheId['SITE_ID'] = SITE_ID;
		$cacheId['LANGUAGE_ID'] = LANGUAGE_ID;
		// if there are two or more caches with the same id, but with different cache_time, make them separate
		$cacheId['CACHE_TIME'] = intval($this->arResult['CACHE_TIME']);

		if(defined("SITE_TEMPLATE_ID"))
			$cacheId['SITE_TEMPLATE_ID'] = SITE_TEMPLATE_ID;

		return implode('|', $cacheId);
	}
}