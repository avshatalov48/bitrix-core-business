<?
use Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/product.php");

class CCatalogProduct extends CAllCatalogProduct
{
	public function Add($arFields, $boolCheck = true)
	{
		global $DB;

		$existProduct = false;
		$boolCheck = ($boolCheck !== false);

		if (empty($arFields['ID']))
			return false;
		$arFields['ID'] = (int)$arFields['ID'];
		if ($arFields['ID'] <= 0)
			return false;

		if ($boolCheck)
			$existProduct = Catalog\ProductTable::isExistProduct($arFields['ID']);

		if ($existProduct)
		{
			return CCatalogProduct::Update($arFields['ID'], $arFields);
		}
		else
		{
			foreach (GetModuleEvents("catalog", "OnBeforeProductAdd", true) as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
					return false;
			}

			if (!CCatalogProduct::CheckFields("ADD", $arFields, 0))
				return false;

			$arInsert = $DB->PrepareInsert("b_catalog_product", $arFields);

			$strSql = "INSERT INTO b_catalog_product(".$arInsert[0].") VALUES(".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			Catalog\ProductTable::clearProductCache($arFields['ID']);

			foreach (GetModuleEvents("catalog", "OnProductAdd", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arFields["ID"], $arFields));
			// strange copy-paste bug
			foreach (GetModuleEvents("sale", "OnProductAdd", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arFields["ID"], $arFields));

			Catalog\Product\Sku::updateAvailable($arFields['ID'], 0, $arFields);
		}

		return true;
	}

	public function Update($ID, $arFields)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		foreach (GetModuleEvents("catalog", "OnBeforeProductUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;
		}

		if (array_key_exists('ID', $arFields))
			unset($arFields['ID']);

		if (!CCatalogProduct::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_product", $arFields);

		$boolSubscribe = false;
		if (!empty($strUpdate))
		{
			if(Catalog\SubscribeTable::checkPermissionSubscribe($arFields['SUBSCRIBE']))
			{
				$strQuery = 'select ID, QUANTITY, AVAILABLE from b_catalog_product where ID = '.$ID;
				$rsProducts = $DB->Query($strQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if ($arProduct = $rsProducts->Fetch())
				{
					$arFields["OLD_QUANTITY"] = (float)$arProduct['QUANTITY'];
					Catalog\SubscribeTable::setOldProductAvailable($ID, $arProduct['AVAILABLE']);
				}
				if (isset($arFields["OLD_QUANTITY"]))
				{
					$boolSubscribe = $arFields["OLD_QUANTITY"] <= 0;
				}
			}

			$strSql = "update b_catalog_product set ".$strUpdate." where ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (
				CBXFeatures::IsFeatureEnabled('CatCompleteSet')
				&& (
					isset($arFields['QUANTITY']) || isset($arFields['QUANTITY_TRACE']) || isset($arFields['CAN_BUY_ZERO']) || isset($arFields['WEIGHT'])
				)
			)
			{
				CCatalogProductSet::recalculateSetsByProduct($ID);
			}

			Catalog\Product\Sku::updateAvailable($ID, 0, $arFields);

			if (isset(self::$arProductCache[$ID]))
			{
				unset(self::$arProductCache[$ID]);
				if (defined('CATALOG_GLOBAL_VARS') && 'Y' == CATALOG_GLOBAL_VARS)
				{
					/** @var array $CATALOG_PRODUCT_CACHE */
					global $CATALOG_PRODUCT_CACHE;
					$CATALOG_PRODUCT_CACHE = self::$arProductCache;
				}
			}
		}

		foreach (GetModuleEvents("catalog", "OnProductUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		//call subscribe
		if ($boolSubscribe)
		{
			if (self::$saleIncluded === null)
				self::$saleIncluded = Loader::includeModule('sale');
			if (self::$saleIncluded)
				CSaleBasket::ProductSubscribe($ID, 'catalog');
		}

		return true;
	}

	public function Delete($ID)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		$DB->Query('delete from b_catalog_price where PRODUCT_ID = '.$ID, true);
		$DB->Query('delete from b_catalog_product2group where PRODUCT_ID = '.$ID, true);
		$DB->Query('delete from b_catalog_product_sets where ITEM_ID = '.$ID.' or OWNER_ID = '.$ID, true);
		$DB->Query('delete from b_catalog_measure_ratio where PRODUCT_ID = '.$ID, true);

		Catalog\ProductTable::clearProductCache($ID);
		if (isset(self::$arProductCache[$ID]))
		{
			unset(self::$arProductCache[$ID]);
			if (defined('CATALOG_GLOBAL_VARS') && CATALOG_GLOBAL_VARS == 'Y')
			{
				/** @var array $CATALOG_PRODUCT_CACHE */
				global $CATALOG_PRODUCT_CACHE;
				$CATALOG_PRODUCT_CACHE = self::$arProductCache;
			}
		}
		return $DB->Query("delete from b_catalog_product where ID = ".$ID, true);
	}

	public static function GetQueryBuildArrays($arOrder, $arFilter, $arSelect)
	{
		global $DB, $USER;

		$strDefQuantityTrace = ((string)Option::get('catalog', 'default_quantity_trace') == 'Y' ? 'Y' : 'N');
		$strDefCanBuyZero = ((string)Option::get('catalog', 'default_can_buy_zero') == 'Y' ? 'Y' : 'N');
		$strDefNegAmount = ((string)Option::get('catalog', 'allow_negative_amount') == 'Y' ? 'Y' : 'N');
		$strSubscribe = ((string)Option::get('catalog', 'default_subscribe') == 'N' ? 'N' : 'Y');

		$sResSelect = '';
		$sResFrom = '';
		$sResWhere = '';
		$arResOrder = array();
		$arJoinGroup = array();
		$arStoreWhere = array();
		$arStore = array();
		$arStoreOrder = array();

		$arSensID = array(
			'PRODUCT_ID' => true,
			'CATALOG_GROUP_ID' => true,
			'CURRENCY' => true,
			'SHOP_QUANTITY' => true,
			'PRICE' => true,
			'STORE_AMOUNT' => true,
			'PRICE_SCALE' => true
		);

		$arOrderTmp = array();
		foreach ($arOrder as $key => $val)
		{
			foreach ($val as $by => $order)
			{
				if ($arField = static::ParseQueryBuildField($by))
				{
					$res = '';
					$join = true;

					$inum = (int)$arField["NUM"];
					$by = (string)$arField["FIELD"];
					if ($by == '' || ($inum <= 0 && isset($arSensID[$by])))
						continue;

					switch ($by)
					{
						case 'PRICE':
						case 'PRICE_SCALE':
							$res = " ".CIBlock::_Order("CAT_P".$inum.".PRICE_SCALE", $order, "asc")." ";
							break;
						case 'CURRENCY':
							$res = " ".CIBlock::_Order("CAT_P".$inum.".CURRENCY", $order, "asc")." ";
							break;
						case 'QUANTITY':
							$arResOrder[$key] = " ".CIBlock::_Order("CAT_PR.QUANTITY", $order, "asc", false)." ";
							$join = false;
							break;
						case 'WEIGHT':
							$arResOrder[$key] = " ".CIBlock::_Order("CAT_PR.WEIGHT", $order, "asc", false)." ";
							$join = false;
							break;
						case 'AVAILABLE':
							$arResOrder[$key] = " ".CIBlock::_Order("CAT_PR.AVAILABLE", $order, "desc", false)." ";
							$join = false;
							break;
						case 'TYPE':
							$arResOrder[$key] = " ".CIBlock::_Order("CAT_PR.TYPE", $order, "asc", false)." ";
							$join = false;
							break;
						case 'BUNDLE':
							$arResOrder[$key] = " ".CIBlock::_Order("CAT_PR.BUNDLE", $order, "asc", false)." ";
							$join = false;
							break;
						case 'PURCHASING_PRICE':
							$arResOrder[$key] = " ".CIBlock::_Order("CAT_PR.PURCHASING_PRICE", $order, "asc")." ";
							$join = false;
							break;
						case 'PURCHASING_CURRENCY':
							$arResOrder[$key] = " ".CIBlock::_Order("CAT_PR.PURCHASING_CURRENCY", $order, "asc")." ";
							$join = false;
							break;
						case 'STORE_AMOUNT':
							$arStore[$inum] = true;
							if (!isset($arStoreOrder[$inum]))
								$arStoreOrder[$inum] = array();
							$arStoreOrder[$inum][$key] = " ".CIBlock::_Order("CAT_SP".$inum.".AMOUNT", $order, "asc")." ";
							$join = false;
							break;
						default:
							$res = " ".CIBlock::_Order("CAT_P".$inum.".ID", $order, "asc", false)." ";
							break;
					}
					if ($join)
					{
						if (!isset($arOrderTmp[$inum]))
							$arOrderTmp[$inum] = array();
						$arOrderTmp[$inum][$key] = $res;
						$arJoinGroup[$inum] = true;
					}
				}
			}
		}

		$productWhere = array();
		$arWhereTmp = array();
		$arAddJoinOn = array();

		$filter_keys = (!is_array($arFilter) ? array() : array_keys($arFilter));

		for ($i = 0, $cnt = count($filter_keys); $i < $cnt; $i++)
		{
			$key = strtoupper($filter_keys[$i]);
			$val = $arFilter[$filter_keys[$i]];

			$res = CIBlock::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			if ($arField = static::ParseQueryBuildField($key))
			{
				$res = '';
				$join = true;

				$key = (string)$arField["FIELD"];
				$inum = (int)$arField["NUM"];

				if ($key == '' || ($inum <= 0 && isset($arSensID[$key])))
					continue;

				switch($key)
				{
					case "PRODUCT_ID":
						$res = CIBlock::FilterCreate("CAT_P".$inum.".PRODUCT_ID", $val, "number", $cOperationType);
						break;
					case "CATALOG_GROUP_ID":
						$res = CIBlock::FilterCreate("CAT_P".$inum.".CATALOG_GROUP_ID", $val, "number", $cOperationType);
						break;
					case "CURRENCY":
						$res = CIBlock::FilterCreate("CAT_P".$inum.".CURRENCY", $val, "string", $cOperationType);
						break;
					case "SHOP_QUANTITY":
						$val = (int)$val;
						$res = ' 1=1 ';
						$arAddJoinOn[$inum] =
							(($cOperationType=="N") ? " NOT " : " ").
							" ((CAT_P".$inum.".QUANTITY_FROM <= ".$val." OR CAT_P".$inum.".QUANTITY_FROM IS NULL) AND (CAT_P".$inum.".QUANTITY_TO >= ".$val." OR CAT_P".$inum.".QUANTITY_TO IS NULL)) ";
						break;
					case "PRICE":
						$scale = static::getQueryBuildCurrencyScale($arFilter, $inum);
						if (empty($scale))
						{
							$res = CIBlock::FilterCreate("CAT_P".$inum.".PRICE", $val, "number", $cOperationType);
						}
						else
						{
							$val = static::getQueryBuildPriceScaled($val, $scale['BASE_RATE']);
							$res = CIBlock::FilterCreate("CAT_P".$inum.".PRICE_SCALE", $val, "number", $cOperationType);
						}
						unset($scale);
						break;
					case "PRICE_SCALE":
						$res = CIBlock::FilterCreate("CAT_P".$inum.".PRICE", $val, "number", $cOperationType);
						break;
					case "QUANTITY":
						$res = CIBlock::FilterCreate("CAT_PR.QUANTITY", $val, "number", $cOperationType);
						$join = false;
						break;
					case "AVAILABLE":
						if ('N' !== $val)
							$val = 'Y';
						$res = CIBlock::FilterCreate("CAT_PR.AVAILABLE", $val, "string_equal", $cOperationType);
						$join = false;
						break;
					case "SUBSCRIBE":
						if (is_string($val))
						{
							if ($val == $strSubscribe)
								$val = array($val, 'D');
							$res = CIBlock::FilterCreate("CAT_PR.SUBSCRIBE", $val, "string_equal", $cOperationType);
							$join = false;
						}
						break;
					case "WEIGHT":
						$res = CIBlock::FilterCreate("CAT_PR.WEIGHT", $val, "number", $cOperationType);
						$join = false;
						break;
					case 'TYPE':
						$res = CIBlock::FilterCreate("CAT_PR.TYPE", $val, "number", $cOperationType);
						$join = false;
						break;
					case "BUNDLE":
						if ('N' !== $val)
							$val = 'Y';
						$res = CIBlock::FilterCreate("CAT_PR.BUNDLE", $val, "string_equal", $cOperationType);
						$join = false;
						break;
					case 'PURCHASING_PRICE':
						$res = CIBlock::FilterCreate("CAT_PR.PURCHASING_PRICE", $val, "number", $cOperationType);
						$join = false;
						break;
					case 'PURCHASING_CURRENCY':
						$res = CIBlock::FilterCreate("CAT_PR.PURCHASING_PRICE", $val, "string", $cOperationType);
						$join = false;
						break;
					case 'STORE_AMOUNT':
						$arStore[$inum] = true;
						if (!isset($arStoreWhere[$inum]))
							$arStoreWhere[$inum] = array();
						$arStoreWhere[$inum][] = CIBlock::FilterCreate("CAT_SP".$inum.".AMOUNT", $val, "number", $cOperationType);
						$join = false;
						break;
				}

				if ($res == '')
					continue;

				if ($join)
				{
					if (!isset($arWhereTmp[$inum]))
						$arWhereTmp[$inum] = array();
					$arWhereTmp[$inum][] = $res;
					$arJoinGroup[$inum] = true;
				}
				else
				{
					$productWhere[] = $res;
				}
			}
		}

		if (!empty($arSelect))
		{
			foreach ($arSelect as &$strOneSelect)
			{
				$val = strtoupper($strOneSelect);
				if (strncmp($val, 'CATALOG_GROUP_', 14) == 0)
				{
					$num = (int)substr($val, 14);
					if ($num > 0)
						$arJoinGroup[$num] = true;
				}
				elseif (strncmp($val, 'CATALOG_STORE_AMOUNT_', 21) == 0)
				{
					$num = (int)substr($val, 21);
					if ($num > 0)
						$arStore[$num] = true;
				}
			}
			unset($strOneSelect);
		}

		if (!empty($arJoinGroup))
		{
			$strSubWhere = implode(',', array_keys($arJoinGroup));
			$strUserGroups = (CCatalog::IsUserExists() ? $USER->GetGroups() : '2');
			$arResult = array();
			$fullPriceTypeList = CCatalogGroup::GetListArray();
			if (!empty($fullPriceTypeList))
			{
				$priceTypeList = array();
				foreach (array_keys($fullPriceTypeList) as $priceId)
				{
					if (!isset($arJoinGroup[$priceId]))
						continue;
					$priceTypeList[$priceId] = array(
						'ID' => $fullPriceTypeList[$priceId]['ID'],
						'CATALOG_GROUP_NAME' => $fullPriceTypeList[$priceId]['NAME_LANG'],
						'CATALOG_CAN_ACCESS' => 'N',
						'CATALOG_CAN_BUY' => 'N'
					);
				}
				unset($priceId);
				$query = 'select CATALOG_GROUP_ID, BUY from b_catalog_group2group where GROUP_ID in ('.$strUserGroups.') and CATALOG_GROUP_ID in ('.$strSubWhere.')';
				$rightsIterator = $DB->Query($query, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while ($rights = $rightsIterator->Fetch())
				{
					$priceId = (int)$rights['CATALOG_GROUP_ID'];
					if (isset($priceTypeList[$priceId]))
					{
						if ($rights['BUY'] == 'Y')
							$priceTypeList[$priceId]['CATALOG_CAN_BUY'] = 'Y';
						else
							$priceTypeList[$priceId]['CATALOG_CAN_ACCESS'] = 'Y';
					}
					unset($priceId);
				}
				unset($rights, $rightsIterator);
				$arResult = array_values($priceTypeList);
				unset($priceTypeList);
			}
			unset($fullPriceTypeList);

			if (!empty($arResult))
			{
				foreach ($arResult as $row)
				{
					$i = (int)$row["ID"];

					if (!empty($arWhereTmp[$i]) && is_array($arWhereTmp[$i]))
						$sResWhere .= ' AND '.implode(' AND ', $arWhereTmp[$i]);

					if (!empty($arOrderTmp[$i]) && is_array($arOrderTmp[$i]))
					{
						foreach ($arOrderTmp[$i] as $k => $v)
							$arResOrder[$k] = $v;
						unset($k, $v);
					}

					$sResSelect .= ", CAT_P".$i.".ID as CATALOG_PRICE_ID_".$i.", ".
						" CAT_P".$i.".CATALOG_GROUP_ID as CATALOG_GROUP_ID_".$i.", ".
						" CAT_P".$i.".PRICE as CATALOG_PRICE_".$i.", ".
						" CAT_P".$i.".CURRENCY as CATALOG_CURRENCY_".$i.", ".
						" CAT_P".$i.".QUANTITY_FROM as CATALOG_QUANTITY_FROM_".$i.", ".
						" CAT_P".$i.".QUANTITY_TO as CATALOG_QUANTITY_TO_".$i.", ".
						" '".$DB->ForSql($row["CATALOG_GROUP_NAME"])."' as CATALOG_GROUP_NAME_".$i.", ".
						" '".$DB->ForSql($row["CATALOG_CAN_ACCESS"])."' as CATALOG_CAN_ACCESS_".$i.", ".
						" '".$DB->ForSql($row["CATALOG_CAN_BUY"])."' as CATALOG_CAN_BUY_".$i.", ".
						" CAT_P".$i.".EXTRA_ID as CATALOG_EXTRA_ID_".$i;

					$sResFrom .= ' left join b_catalog_price CAT_P'.$i.' on (CAT_P'.$i.'.PRODUCT_ID = BE.ID AND CAT_P'.$i.'.CATALOG_GROUP_ID = '.$row['ID'].') ';

					if (isset($arAddJoinOn[$i]))
						$sResFrom .= ' and '.$arAddJoinOn[$i];
				}
				unset($row);
			}
			unset($arResult);
		}

		$sResSelect .= ", CAT_PR.QUANTITY as CATALOG_QUANTITY, CAT_PR.QUANTITY_RESERVED as CATALOG_QUANTITY_RESERVED, ".
			" IF (CAT_PR.QUANTITY_TRACE = 'D', '".$strDefQuantityTrace."', CAT_PR.QUANTITY_TRACE) as CATALOG_QUANTITY_TRACE, ".
			" CAT_PR.QUANTITY_TRACE as CATALOG_QUANTITY_TRACE_ORIG, ".
			" IF (CAT_PR.CAN_BUY_ZERO = 'D', '".$strDefCanBuyZero."', CAT_PR.CAN_BUY_ZERO) as CATALOG_CAN_BUY_ZERO, ".
			" CAT_PR.CAN_BUY_ZERO as CATALOG_CAN_BUY_ZERO_ORIG, ".
			" IF (CAT_PR.NEGATIVE_AMOUNT_TRACE = 'D', '".$strDefNegAmount."', CAT_PR.NEGATIVE_AMOUNT_TRACE) as CATALOG_NEGATIVE_AMOUNT_TRACE, ".
			" CAT_PR.NEGATIVE_AMOUNT_TRACE as CATALOG_NEGATIVE_AMOUNT_ORIG, ".
			" IF (CAT_PR.SUBSCRIBE = 'D', '".$strSubscribe."', CAT_PR.SUBSCRIBE) as CATALOG_SUBSCRIBE, ".
			" CAT_PR.SUBSCRIBE as CATALOG_SUBSCRIBE_ORIG, ".
			" CAT_PR.AVAILABLE as CATALOG_AVAILABLE, ".
			" CAT_PR.WEIGHT as CATALOG_WEIGHT, CAT_PR.WIDTH as CATALOG_WIDTH, CAT_PR.LENGTH as CATALOG_LENGTH, CAT_PR.HEIGHT as CATALOG_HEIGHT, ".
			" CAT_PR.MEASURE as CATALOG_MEASURE, ".
			" CAT_VAT.RATE as CATALOG_VAT, CAT_PR.VAT_ID as CATALOG_VAT_ID, CAT_PR.VAT_INCLUDED as CATALOG_VAT_INCLUDED, ".
			" CAT_PR.PRICE_TYPE as CATALOG_PRICE_TYPE, CAT_PR.RECUR_SCHEME_TYPE as CATALOG_RECUR_SCHEME_TYPE, ".
			" CAT_PR.RECUR_SCHEME_LENGTH as CATALOG_RECUR_SCHEME_LENGTH, CAT_PR.TRIAL_PRICE_ID as CATALOG_TRIAL_PRICE_ID, ".
			" CAT_PR.WITHOUT_ORDER as CATALOG_WITHOUT_ORDER, CAT_PR.SELECT_BEST_PRICE as CATALOG_SELECT_BEST_PRICE, ".
			" CAT_PR.PURCHASING_PRICE as CATALOG_PURCHASING_PRICE, CAT_PR.PURCHASING_CURRENCY as CATALOG_PURCHASING_CURRENCY, ".
			" CAT_PR.TYPE as CATALOG_TYPE, CAT_PR.BUNDLE as CATALOG_BUNDLE ";

		$sResFrom .= " left join b_catalog_product CAT_PR on (CAT_PR.ID = BE.ID) ";
		$sResFrom .= " left join b_catalog_iblock CAT_IB on ((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0) AND CAT_IB.IBLOCK_ID = BE.IBLOCK_ID) ";
		$sResFrom .= " left join b_catalog_vat CAT_VAT on (CAT_VAT.ID = IF((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0), CAT_IB.VAT_ID, CAT_PR.VAT_ID)) ";

		if (!empty($productWhere))
			$sResWhere .= ' and '.implode(' and ', $productWhere);
		unset($productWhere);

		if (!empty($arStore))
		{
			foreach (array_keys($arStore) as $inum)
			{
				$sResFrom .= " left join b_catalog_store_product CAT_SP".$inum." on (CAT_SP".$inum.".PRODUCT_ID = BE.ID and CAT_SP".$inum.".STORE_ID = ".$inum.") ";
				$sResSelect  .= ", CAT_SP".$inum.".AMOUNT as CATALOG_STORE_AMOUNT_".$inum." ";
			}
			unset($inum);

			if (!empty($arStoreOrder))
			{
				foreach ($arStoreOrder as $oneStoreOrder)
				{
					if (!empty($oneStoreOrder) && is_array($oneStoreOrder))
					{
						foreach ($oneStoreOrder as $k => $v)
							$arResOrder[$k] = $v;
						unset($k, $v);
					}
				}
				unset($oneStoreOrder);
			}

			if (!empty($arStoreWhere))
			{
				foreach ($arStoreWhere as $where)
					$sResWhere .= ' and '.implode(' and ', $where);
			}
		}

		return array(
			'SELECT' => $sResSelect,
			'FROM' => $sResFrom,
			'WHERE' => $sResWhere,
			'ORDER' => $arResOrder
		);
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool|array $arGroupBy
	 * @param bool|array $arNavStartParams
	 * @param array $arSelectFields
	 * @return bool|CDBResult
	 */
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = (string)$arOrder;
			$arFilter = (string)$arFilter;
			$arOrder = ($arOrder != '' && $arFilter != '' ? array($arOrder => $arFilter) : array());
			$arFilter = (is_array($arGroupBy) ? $arGroupBy : array());
			$arGroupBy = false;
		}

		$defaultQuantityTrace = ((string)Option::get('catalog', 'default_quantity_trace') == 'Y' ? 'Y' : 'N');
		$defaultCanBuyZero = ((string)Option::get('catalog', 'default_can_buy_zero') == 'Y' ? 'Y' : 'N');
		$defaultNegativeAmount = ((string)Option::get('catalog', 'allow_negative_amount') == 'Y' ? 'Y' : 'N');
		$defaultSubscribe = ((string)Option::get('catalog', 'default_subscribe') == 'N' ? 'N' : 'Y');

		$arFields = array(
			"ID" => array("FIELD" => "CP.ID", "TYPE" => "int"),
			"QUANTITY" => array("FIELD" => "CP.QUANTITY", "TYPE" => "double"),
			"QUANTITY_RESERVED" => array("FIELD" => "CP.QUANTITY_RESERVED", "TYPE" => "double"),
			"QUANTITY_TRACE_ORIG" => array("FIELD" => "CP.QUANTITY_TRACE", "TYPE" => "char"),
			"CAN_BUY_ZERO_ORIG" => array("FIELD" => "CP.CAN_BUY_ZERO", "TYPE" => "char"),
			"NEGATIVE_AMOUNT_TRACE_ORIG" => array("FIELD" => "CP.NEGATIVE_AMOUNT_TRACE", "TYPE" => "char"),
			"QUANTITY_TRACE" => array("FIELD" => "IF (CP.QUANTITY_TRACE = 'D', '".$defaultQuantityTrace."', CP.QUANTITY_TRACE)", "TYPE" => "char"),
			"CAN_BUY_ZERO" => array("FIELD" => "IF (CP.CAN_BUY_ZERO = 'D', '".$defaultCanBuyZero."', CP.CAN_BUY_ZERO)", "TYPE" => "char"),
			"NEGATIVE_AMOUNT_TRACE" => array("FIELD" => "IF (CP.NEGATIVE_AMOUNT_TRACE = 'D', '".$defaultNegativeAmount."', CP.NEGATIVE_AMOUNT_TRACE)", "TYPE" => "char"),
			"SUBSCRIBE_ORIG" => array("FIELD" => "CP.SUBSCRIBE", "TYPE" => "char"),
			"SUBSCRIBE" => array("FIELD" => "IF (CP.SUBSCRIBE = 'D', '".$defaultSubscribe."', CP.SUBSCRIBE)", "TYPE" => "char"),
			"AVAILABLE" => array("FIELD" => "CP.AVAILABLE", "TYPE" => "char"),
			"BUNDLE" => array("FIELD" => "CP.BUNDLE", "TYPE" => "char"),
			"WEIGHT" => array("FIELD" => "CP.WEIGHT", "TYPE" => "double"),
			"WIDTH" => array("FIELD" => "CP.WIDTH", "TYPE" => "double"),
			"LENGTH" => array("FIELD" => "CP.LENGTH", "TYPE" => "double"),
			"HEIGHT" => array("FIELD" => "CP.HEIGHT", "TYPE" => "double"),
			"TIMESTAMP_X" => array("FIELD" => "CP.TIMESTAMP_X", "TYPE" => "datetime"),
			"PRICE_TYPE" => array("FIELD" => "CP.PRICE_TYPE", "TYPE" => "char"),
			"RECUR_SCHEME_TYPE" => array("FIELD" => "CP.RECUR_SCHEME_TYPE", "TYPE" => "char"),
			"RECUR_SCHEME_LENGTH" => array("FIELD" => "CP.RECUR_SCHEME_LENGTH", "TYPE" => "int"),
			"TRIAL_PRICE_ID" => array("FIELD" => "CP.TRIAL_PRICE_ID", "TYPE" => "int"),
			"WITHOUT_ORDER" => array("FIELD" => "CP.WITHOUT_ORDER", "TYPE" => "char"),
			"SELECT_BEST_PRICE" => array("FIELD" => "CP.SELECT_BEST_PRICE", "TYPE" => "char"),
			"VAT_ID" => array("FIELD" => "CP.VAT_ID", "TYPE" => "int"),
			"VAT_INCLUDED" => array("FIELD" => "CP.VAT_INCLUDED", "TYPE" => "char"),
			"TMP_ID" => array("FIELD" => "CP.TMP_ID", "TYPE" => "char"),
			"PURCHASING_PRICE" => array("FIELD" => "CP.PURCHASING_PRICE", "TYPE" => "double"),
			"PURCHASING_CURRENCY" => array("FIELD" => "CP.PURCHASING_CURRENCY", "TYPE" => "string"),
			"BARCODE_MULTI" => array("FIELD" => "CP.BARCODE_MULTI", "TYPE" => "char"),
			"MEASURE" => array("FIELD" => "CP.MEASURE", "TYPE" => "int"),
			"TYPE" => array("FIELD" => "CP.TYPE", "TYPE" => "int"),
			"ELEMENT_IBLOCK_ID" => array("FIELD" => "I.IBLOCK_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)"),
			"ELEMENT_XML_ID" => array("FIELD" => "I.XML_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)"),
			"ELEMENT_NAME" => array("FIELD" => "I.NAME", "TYPE" => "string", "FROM" => "INNER JOIN b_iblock_element I ON (CP.ID = I.ID)")
		);

		$arSqls = CCatalog::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (empty($arGroupBy) && is_array($arGroupBy))
		{
			$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_catalog_product CP ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return false;
		}

		$strSql = "SELECT ".$arSqls["SELECT"]." FROM b_catalog_product CP ".$arSqls["FROM"];
		if (!empty($arSqls["WHERE"]))
			$strSql .= " WHERE ".$arSqls["WHERE"];
		if (!empty($arSqls["GROUPBY"]))
			$strSql .= " GROUP BY ".$arSqls["GROUPBY"];
		if (!empty($arSqls["ORDERBY"]))
			$strSql .= " ORDER BY ".$arSqls["ORDERBY"];

		$intTopCount = 0;
		$boolNavStartParams = (!empty($arNavStartParams) && is_array($arNavStartParams));
		if ($boolNavStartParams && isset($arNavStartParams['nTopCount']))
			$intTopCount = (int)$arNavStartParams['nTopCount'];

		if ($boolNavStartParams && $intTopCount <= 0)
		{
			$strSql_tmp = "SELECT COUNT('x') as CNT FROM b_catalog_product CP ".$arSqls["FROM"];
			if (!empty($arSqls["WHERE"]))
				$strSql_tmp .= " WHERE ".$arSqls["WHERE"];
			if (!empty($arSqls["GROUPBY"]))
				$strSql_tmp .= " GROUP BY ".$arSqls["GROUPBY"];

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if (empty($arSqls["GROUPBY"]))
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if ($boolNavStartParams && $intTopCount > 0)
				$strSql .= " LIMIT ".$intTopCount;

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $dbRes;
	}

/*
* @deprecated deprecated since catalog 8.5.1
* @see CCatalogProduct::GetList()
*/
	public static function GetListEx($arOrder=array("SORT"=>"ASC"), $arFilter=array())
	{
		return false;
	}

	public static function GetVATInfo($PRODUCT_ID)
	{
		global $DB;

		$query = "
SELECT CAT_PR.ID as PRODUCT_ID, CAT_VAT.*, CAT_PR.VAT_INCLUDED
FROM b_catalog_product CAT_PR
LEFT JOIN b_iblock_element BE ON (BE.ID = CAT_PR.ID)
LEFT JOIN b_catalog_iblock CAT_IB ON ((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0) AND CAT_IB.IBLOCK_ID = BE.IBLOCK_ID)
LEFT JOIN b_catalog_vat CAT_VAT ON (CAT_VAT.ID = IF((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0), CAT_IB.VAT_ID, CAT_PR.VAT_ID))
WHERE CAT_PR.ID = '".intval($PRODUCT_ID)."'
AND CAT_VAT.ACTIVE='Y'
";
		return $DB->Query($query);
	}

	/**
	 * @param array $list
	 *
	 * @return array
	 */
	public static function GetVATDataByIDList(array $list)
	{
		$output = array();
		foreach ($list as $index => $id)
		{
			$output[$id] = false;
			$id = (int)$id;
			if ($id <= 0)
			{
				unset($list[$index]);
				continue;
			}

			if (!empty(static::$vatCache[$id]))
			{
				$output[$id] = static::$vatCache[$id];
				unset($list[$index]);
			}
		}

		if (!empty($list))
		{
			$vatDataList = static::loadVatInfoFromDB($list);
		}

		if (!empty($vatDataList) && is_array($vatDataList))
		{
			$output = $output + $vatDataList;
		}

		return $output;
	}

	/**
	 * @param $id
	 *
	 * @return bool|mixed
	 */
	public static function GetVATDataByID($id)
	{
		if (array_key_exists($id, static::$vatCache))
		{
			return static::$vatCache[$id];
		}
		$dataList = static::loadVatInfoFromDB(array($id));
		return (!empty($dataList[$id]) ? $dataList[$id] : false);
	}

	/**
	 * @param array $list
	 *
	 * @return array
	 */
	private static function loadVatInfoFromDB(array $list)
	{
		global $DB;
		$output = array();
		foreach ($list as $index => $id)
		{
			$output[$id] = false;
			$id = (int)$id;
			if ($id <= 0)
			{
				unset($list[$index]);
				continue;
			}

			if (!empty(static::$vatCache[$id]))
			{
				$output[$id] = static::$vatCache[$id];
				unset($list[$index]);
			}
			else
			{
				static::$vatCache[$id] = false;
			}
		}

		if (!empty($list))
		{
			$query = "
	SELECT CAT_PR.ID as PRODUCT_ID, CAT_VAT.*, CAT_PR.VAT_INCLUDED
	FROM b_catalog_product CAT_PR
	LEFT JOIN b_iblock_element BE ON (BE.ID = CAT_PR.ID)
	LEFT JOIN b_catalog_iblock CAT_IB ON ((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0) AND CAT_IB.IBLOCK_ID = BE.IBLOCK_ID)
	LEFT JOIN b_catalog_vat CAT_VAT ON (CAT_VAT.ID = IF((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0), CAT_IB.VAT_ID, CAT_PR.VAT_ID))
	WHERE CAT_PR.ID IN (".join(', ', $list).")
	AND CAT_VAT.ACTIVE='Y'
	";
			$res = $DB->Query($query);
			while ($data = $res->Fetch())
			{
				static::$vatCache[$data['PRODUCT_ID']] = $output[$data['PRODUCT_ID']] = $data;
			}
		}

		return $output;
	}

	public static function SetProductType($intID, $intTypeID)
	{
		global $DB;
		$intID = intval($intID);
		if (0 >= $intID)
			return false;
		$intTypeID = intval($intTypeID);
		if (self::TYPE_PRODUCT != $intTypeID && self::TYPE_SET != $intTypeID)
			return false;
		$strSql = 'update b_catalog_product set TYPE='.$intTypeID.' where ID='.$intID;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return true;
	}
}