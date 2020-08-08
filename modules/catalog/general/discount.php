<?
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\ModuleManager,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\LanguageTable,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Catalog\Product\Price,
	Bitrix\Sale\DiscountCouponsManager,
	Bitrix\Sale\Discount\Context,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

class CAllCatalogDiscount
{
	const TYPE_PERCENT = 'P';
	const TYPE_FIX = 'F';
	const TYPE_SALE = 'S';

	const ENTITY_ID = 0;
	const CURRENT_FORMAT = 2;
	const OLD_FORMAT = 1;

	private const NOTIFY_DISCOUNT_REINDEX_ID = 'CATALOG_DISC_FORMAT';

	static protected $arCacheProduct = array();
	static protected $arCacheDiscountFilter = array();
	static protected $arCacheDiscountResult = array();
	static protected $arCacheProductSectionChain = array();
	static protected $arCacheProductSections = array();
	static protected $arCacheProductProperties = array();
	static protected $cacheDiscountHandlers = array();
	static protected $usedModules = array();

	static protected $existCouponsManager = null;
	static protected $useSaleDiscount = null;
	static protected $getPriceTypesOnly = false;
	static protected $getPercentFromBasePrice = null;
	static private $needDiscountCache = [];

	private static function calculatePriceByDiscount($basePrice, $currentPrice, $oneDiscount, &$needErase)
	{
		$calculatePrice = false;
		switch ($oneDiscount['VALUE_TYPE'])
		{
			case self::TYPE_PERCENT:
				$discountValue = Price\Calculation::roundPrecision(
					-(self::$getPercentFromBasePrice ? $basePrice : $currentPrice) * $oneDiscount['VALUE'] / 100
				);
				if (isset($oneDiscount['DISCOUNT_CONVERT']) && $oneDiscount['DISCOUNT_CONVERT'] > 0)
				{
					if ($discountValue + $oneDiscount['DISCOUNT_CONVERT'] <= 0)
						$discountValue = -$oneDiscount['DISCOUNT_CONVERT'];
				}
				$needErase = ($currentPrice + $discountValue < 0);
				if (!$needErase)
				{
					$calculatePrice = $currentPrice + $discountValue;
				}
				unset($discountValue);
				break;
			case self::TYPE_FIX:
				$needErase = ($oneDiscount['DISCOUNT_CONVERT'] > $currentPrice);
				if (!$needErase)
				{
					$calculatePrice = $currentPrice - $oneDiscount['DISCOUNT_CONVERT'];
				}
				break;
			case self::TYPE_SALE:
				$needErase = ($oneDiscount['DISCOUNT_CONVERT'] >= $currentPrice);
				if (!$needErase)
				{
					$calculatePrice = $oneDiscount['DISCOUNT_CONVERT'];
				}
				break;
			default:
				$needErase = true;
				break;
		}

		return $calculatePrice;
	}

	/**
	 * @return string
	 */
	public static function execAgent(): string
	{
		if (
			ModuleManager::isModuleInstalled('bitrix24')
			|| (string)Option::get('sale', 'use_sale_discount_only') !== 'N')
		{
			return '';
		}

		$iterator = \CAdminNotify::GetList(
			[],
			['MODULE_ID' => 'catalog', 'TAG' => self::NOTIFY_DISCOUNT_REINDEX_ID]
		);
		while ($row = $iterator->Fetch())
		{
			\CAdminNotify::Delete($row['ID']);
		}
		unset($row, $iterator);

		$defaultLang = '';
		$messages = [];
		$iterator = LanguageTable::getList([
			'select' => ['ID', 'DEF'],
			'filter' => ['=ACTIVE' => 'Y']
		]);
		while ($row = $iterator->fetch())
		{
			if ($defaultLang == '')
				$defaultLang = $row['ID'];
			if ($row['DEF'] == 'Y')
				$defaultLang = $row['ID'];
			$languageId = $row['ID'];
			Loc::loadLanguageFile(
				__FILE__,
				$languageId
			);
			$messages[$languageId] = Loc::getMessage(
				'CATALOG_DISCOUNT_REINDEX_MESS',
				['#LANGUAGE_ID#' => $languageId],
				$languageId
			);
		}
		unset($languageId, $row, $iterator);

		if (!empty($messages))
		{
			\CAdminNotify::Add([
				'MODULE_ID' => 'catalog',
				'TAG' => self::NOTIFY_DISCOUNT_REINDEX_ID,
				'ENABLE_CLOSE' => 'Y',
				'NOTIFY_TYPE' => \CAdminNotify::TYPE_NORMAL,
				'MESSAGE' => $messages[$defaultLang],
				'LANG' => $messages
			]);
		}
		unset($messages, $defaultLang);

		return '';
	}

	public static function GetDiscountTypes($boolFull = false)
	{
		$boolFull = ($boolFull === true);
		if ($boolFull)
		{
			return array(
				self::TYPE_PERCENT => Loc::getMessage('BT_CAT_DISCOUNT_TYPE_PERCENT'),
				self::TYPE_FIX => Loc::getMessage('BT_CAT_DISCOUNT_TYPE_FIX'),
				self::TYPE_SALE => Loc::getMessage('BT_CAT_DISCOUNT_TYPE_SALE_EXT'),
			);
		}
		return array(
			self::TYPE_PERCENT,
			self::TYPE_FIX,
			self::TYPE_SALE,
		);
	}

	public static function setSaleDiscountFilter($priceTypesOnly = false)
	{
		self::initDiscountSettings();
		if (self::$useSaleDiscount)
		{
			self::$getPriceTypesOnly = ($priceTypesOnly === true);
		}
	}

	/**
	 * Return calculate discount percent mode.
	 *
	 * @return bool
	 */
	public static function getUseBasePrice()
	{
		if (self::$getPercentFromBasePrice === null)
			self::initDiscountSettings();
		return self::$getPercentFromBasePrice;
	}

	/**
	 * Set calculate discount percent mode.
	 *
	 * @param bool $useBasePrice		Set calculate discount percent mode.
	 * @return void
	 */
	public static function setUseBasePrice($useBasePrice)
	{
		if ($useBasePrice !== true && $useBasePrice !== false)
			return;
		self::$getPercentFromBasePrice = $useBasePrice;
	}

	public function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $DB, $USER;

		$boolResult = true;
		$arMsg = array();

		$ACTION = mb_strtoupper($ACTION);
		if ($ACTION != 'UPDATE' && $ACTION != 'ADD')
			return false;

		if (!is_array($arFields))
			return false;

		$boolValueType = false;
		$boolValue = false;
		$arCurrent = array(
			'VALUE' => 0,
			'VALUE_TYPE' => ''
		);

		$clearFields = array(
			'ID',
			'~ID',
			'UNPACK',
			'~UNPACK',
			'~CONDITIONS',
			'~USE_COUPONS',
			'HANDLERS',
			'~HANDLERS',
			'ENTITY',
			'~ENTITY',
			'~TYPE',
			'~VERSION',
			'TIMESTAMP_X',
			'DATE_CREATE',
			'~DATE_CREATE',
			'~MODIFIED_BY',
			'~CREATED_BY'
		);
		if ($ACTION == 'UPDATE')
			$clearFields[] = 'CREATED_BY';
		$arFields = array_filter($arFields, 'CCatalogDiscount::clearFields');
		foreach ($clearFields as &$fieldName)
		{
			if (isset($arFields[$fieldName]))
				unset($arFields[$fieldName]);
		}
		unset($fieldName, $clearFields);

		$arFields['TYPE'] = self::ENTITY_ID;
		$arFields['VERSION'] = self::CURRENT_FORMAT;

		if ($ACTION == 'ADD')
		{
			$boolValueType = true;
			$boolValue = true;

			$defaultValues = array(
				'ACTIVE' => 'Y',
				'RENEWAL' => 'N',
				'MAX_USES' => 0,
				'COUNT_USES' => 0,
				'SORT' => 100,
				'MAX_DISCOUNT' => 0,
				'VALUE_TYPE' => self::TYPE_PERCENT,
				'MIN_ORDER_SUM' => 0,
				'PRIORITY' => 1,
				'LAST_DISCOUNT' => 'Y'
			);
			$arFields = array_merge($defaultValues, $arFields);
			unset($defaultValues);

			if (!isset($arFields['SITE_ID']))
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'SITE_ID', 'text' => Loc::getMessage("KGD_EMPTY_SITE"));
			}
			if (!isset($arFields['CURRENCY']))
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'CURRENCY', 'text' => Loc::getMessage('KGD_EMPTY_CURRENCY'));
			}
			if (!isset($arFields['NAME']))
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'NAME', 'text' => Loc::getMessage('KGD_EMPTY_NAME'));
			}
			if (!isset($arFields['VALUE']))
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'VALUE', 'text' => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_BAD_VALUE'));
			}
			if (!isset($arFields['CONDITIONS']))
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'CONDITIONS', 'text' => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_EMPTY_CONDITIONS'));
			}
			$arFields['USE_COUPONS'] = 'N';
		}

		if ($ACTION == 'UPDATE')
		{
			$ID = (int)$ID;
			if ($ID <= 0)
			{
				$boolResult = false;
				$arMsg[] = array('id' => 'ID', 'text' => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_BAD_ID', array('#ID#', $ID)));
			}
			else
			{
				$boolValueType = isset($arFields['VALUE_TYPE']);
				$boolValue = isset($arFields['VALUE']);
				if ($boolValueType != $boolValue)
				{
					$rsDiscounts = CCatalogDiscount::GetList(
						array(),
						array('ID' => $ID),
						false,
						false,
						array('ID', 'VALUE_TYPE', 'VALUE')
					);
					if ($arCurrent = $rsDiscounts->Fetch())
					{
						$arCurrent['VALUE'] = doubleval($arCurrent['VALUE']);
					}
					else
					{
						$boolResult = false;
						$arMsg[] = array('id' => 'ID', 'text' => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_BAD_ID', array('#ID#', $ID)));
					}
				}
			}
		}

		if ($boolResult)
		{
			if (isset($arFields['SITE_ID']))
			{
				if (empty($arFields['SITE_ID']))
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'SITE_ID', 'text' => Loc::getMessage('KGD_EMPTY_SITE'));
				}
			}
			if (isset($arFields['CURRENCY']))
			{
				if (empty($arFields['CURRENCY']))
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'CURRENCY', 'text' => Loc::getMessage('KGD_EMPTY_CURRENCY'));
				}
			}
			if (isset($arFields['NAME']))
			{
				$arFields['NAME'] = trim($arFields['NAME']);
				if ($arFields['NAME'] === '')
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'NAME', 'text' => Loc::getMessage('KGD_EMPTY_NAME'));
				}
			}
			if (isset($arFields['ACTIVE']))
			{
				$arFields['ACTIVE'] = ($arFields['ACTIVE'] != 'N' ? 'Y' : 'N');
			}
			if (isset($arFields['ACTIVE_FROM']))
			{
				if (!$DB->IsDate($arFields['ACTIVE_FROM'], false, LANGUAGE_ID, 'FULL'))
				{
					$arFields['ACTIVE_FROM'] = false;
				}
			}
			if (isset($arFields['ACTIVE_TO']))
			{
				if (!$DB->IsDate($arFields['ACTIVE_TO'], false, LANGUAGE_ID, 'FULL'))
				{
					$arFields['ACTIVE_TO'] = false;
				}
			}
			if (isset($arFields['RENEWAL']))
			{
				$arFields['RENEWAL'] = ($arFields['RENEWAL'] == 'Y' ? 'Y' : 'N');
			}
			if (isset($arFields['MAX_USES']))
			{
				$arFields['MAX_USES'] = (int)$arFields['MAX_USES'];
				if ($arFields['MAX_USES'] < 0)
					$arFields['MAX_USES'] = 0;
			}
			if (isset($arFields['COUNT_USES']))
			{
				$arFields['COUNT_USES'] = (int)$arFields['COUNT_USES'];
				if ($arFields['COUNT_USES'] < 0)
					$arFields['COUNT_USES'] = 0;
			}
			if (isset($arFields['CATALOG_COUPONS']))
			{
				if (empty($arFields['CATALOG_COUPONS']) && !is_array($arFields['CATALOG_COUPONS']))
					unset($arFields['CATALOG_COUPONS']);
			}
			if (isset($arFields['SORT']))
			{
				$arFields['SORT'] = (int)$arFields['SORT'];
				if ($arFields['SORT'] <= 0)
					$arFields['SORT'] = 100;
			}
			if (isset($arFields['MAX_DISCOUNT']))
			{
				$arFields['MAX_DISCOUNT'] = str_replace(',', '.', $arFields['MAX_DISCOUNT']);
				$arFields['MAX_DISCOUNT'] = doubleval($arFields['MAX_DISCOUNT']);
				if ($arFields['MAX_DISCOUNT'] < 0)
					$arFields['MAX_DISCOUNT'] = 0;
			}

			if ($boolValueType)
			{
				if (!in_array($arFields['VALUE_TYPE'], CCatalogDiscount::GetDiscountTypes()))
					$arFields['VALUE_TYPE'] = self::TYPE_PERCENT;
			}
			if ($boolValue)
			{
				$arFields['VALUE'] = str_replace(',', '.', $arFields['VALUE']);
				$arFields['VALUE'] = doubleval($arFields['VALUE']);
				if ($arFields['VALUE'] <= 0)
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'VALUE', 'text' => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_BAD_VALUE'));
				}
			}
			if ($ACTION == 'UPDATE')
			{
				if ($boolValue != $boolValueType)
				{
					if (!$boolValue)
					{
						$arFields['VALUE'] = $arCurrent['VALUE'];
						$boolValue = true;
					}
					if (!$boolValueType)
					{
						$arFields['VALUE_TYPE'] = $arCurrent['VALUE_TYPE'];
						$boolValueType = true;
					}
				}
			}
			if ($boolValue && $boolValueType)
			{
				if ($arFields['VALUE_TYPE'] == self::TYPE_PERCENT && $arFields['VALUE'] > 100)
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'VALUE', 'text' => Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_BAD_VALUE"));
				}
			}
			if (isset($arFields['MIN_ORDER_SUM']))
			{
				$arFields['MIN_ORDER_SUM'] = str_replace(',', '.', $arFields['MIN_ORDER_SUM']);
				$arFields['MIN_ORDER_SUM'] = doubleval($arFields['MIN_ORDER_SUM']);
			}
			if (isset($arFields['PRIORITY']))
			{
				$arFields['PRIORITY'] = (int)$arFields['PRIORITY'];
				if (0 >= $arFields['PRIORITY'])
					$arFields['PRIORITY'] = 1;
			}
			if (isset($arFields['LAST_DISCOUNT']))
			{
				$arFields['LAST_DISCOUNT'] = ($arFields['LAST_DISCOUNT'] != 'N' ? 'Y' : 'N');
			}
			if (isset($arFields['USE_COUPONS']))
				$arFields['USE_COUPONS'] = ($arFields['USE_COUPONS'] != 'Y' ? 'N' : 'Y');
		}
		if ($boolResult)
		{
			if (isset($arFields['CONDITIONS']))
			{
				if (empty($arFields['CONDITIONS']))
				{
					$boolResult = false;
					$arMsg[] = array('id' => 'CONDITIONS', 'text' => Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_EMPTY_CONDITIONS"));
				}
				else
				{
					$usedHandlers = array();
					$usedEntities = array();
					$boolCond = true;
					$strEval = '';
					if (!is_array($arFields['CONDITIONS']))
					{
						if (!CheckSerializedData($arFields['CONDITIONS']))
						{
							$boolCond = false;
							$boolResult = false;
							$arMsg[] = array('id' => 'CONDITIONS', 'text' => Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_BAD_CONDITIONS"));
						}
						else
						{
							$arFields['CONDITIONS'] = unserialize($arFields['CONDITIONS']);
							if (empty($arFields['CONDITIONS']) || !is_array($arFields['CONDITIONS']))
							{
								$boolCond = false;
								$boolResult = false;
								$arMsg[] = array('id' => 'CONDITIONS', 'text' => Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_BAD_CONDITIONS"));
							}
						}
					}
					if ($boolCond)
					{
						$obCond = new CCatalogCondTree();
						$boolCond = $obCond->Init(BT_COND_MODE_GENERATE, BT_COND_BUILD_CATALOG, array());
						if (!$boolCond)
						{
							return false;
						}
						$strEval = $obCond->Generate($arFields['CONDITIONS'], array('FIELD' => '$arProduct'));
						if (empty($strEval) || 'false' == $strEval)
						{
							$boolCond = false;
							$boolResult = false;
							$arMsg[] = array('id' => 'CONDITIONS', 'text' => Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_BAD_CONDITIONS"));
						}
						else
						{
							$usedHandlers = $obCond->GetConditionHandlers();
							$usedEntities = $obCond->GetUsedEntityList();
						}
					}
					if ($boolCond)
					{
						$arFields['UNPACK'] = $strEval;
						$arFields['CONDITIONS'] = serialize($arFields['CONDITIONS']);
						if (!empty($usedHandlers))
							$arFields['HANDLERS'] = $usedHandlers;
						if (!empty($usedEntities))
							$arFields['ENTITY'] = $usedEntities;

						if ($DB->type == 'MYSQL')
						{
							if (64000 < CUtil::BinStrlen($arFields['UNPACK']) || 64000 < CUtil::BinStrlen($arFields['CONDITIONS']))
							{
								$boolResult = false;
								$arMsg[] = array('id' => 'CONDITIONS', 'text' => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_CONDITIONS_TOO_LONG'));
								unset($arFields['UNPACK']);
								$arFields['CONDITIONS'] = unserialize($arFields['CONDITIONS']);
							}
						}
					}
				}
			}
		}

		$intUserID = 0;
		$boolUserExist = CCatalog::IsUserExists();
		if ($boolUserExist)
			$intUserID = (int)$USER->GetID();
		$strDateFunction = $DB->GetNowFunction();
		$arFields['~TIMESTAMP_X'] = $strDateFunction;
		if ($boolUserExist)
		{
			if (!isset($arFields['MODIFIED_BY']) || (int)$arFields["MODIFIED_BY"] <= 0)
				$arFields["MODIFIED_BY"] = $intUserID;
		}
		if ($ACTION == 'ADD')
		{
			$arFields['~DATE_CREATE'] = $strDateFunction;
			if ($boolUserExist)
			{
				if (!isset($arFields['CREATED_BY']) || (int)$arFields["CREATED_BY"] <= 0)
					$arFields["CREATED_BY"] = $intUserID;
			}
		}

		if (!$boolResult)
		{
			$obError = new CAdminException($arMsg);
			$APPLICATION->ResetException();
			$APPLICATION->ThrowException($obError);
		}
		return $boolResult;
	}

	public function Add($arFields)
	{
		foreach (GetModuleEvents("catalog", "OnBeforeDiscountAdd", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;
		}

		$mxRows = self::__ParseArrays($arFields);
		if (empty($mxRows) || !is_array($mxRows))
			return false;

		$boolNewVersion = true;
		if (!array_key_exists('CONDITIONS', $arFields))
		{
			self::__ConvertOldConditions('ADD', $arFields);
			$boolNewVersion = false;
		}

		$ID = CCatalogDiscount::_Add($arFields);
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if ($boolNewVersion)
		{
			$arValuesList = self::__GetConditionValues($arFields);
			if (is_array($arValuesList) && !empty($arValuesList))
			{
				self::__GetOldOneEntity($arFields, $arValuesList, 'IBLOCK_IDS', 'CondIBIBlock');
				self::__GetOldOneEntity($arFields, $arValuesList, 'SECTION_IDS', 'CondIBSection');
				self::__GetOldOneEntity($arFields, $arValuesList, 'PRODUCT_IDS', 'CondIBElement');
			}
		}

		if (!CCatalogDiscount::__UpdateSubdiscount($ID, $mxRows, $arFields['ACTIVE']))
			return false;

		CCatalogDiscount::__UpdateOldEntities($ID, $arFields, false);

		foreach ($arFields['ENTITY'] as $entity)
		{
			$fields = array(
				'DISCOUNT_ID' => $ID,
				'MODULE_ID' => $entity['MODULE'],
				'ENTITY' => $entity['ENTITY'],
				'FIELD_ENTITY' => $entity['FIELD_ENTITY'],
			);
			if (isset($entity['ENTITY_ID']))
				$fields['ENTITY_ID'] = $entity['ENTITY_ID'];
			if (isset($entity['ENTITY_VALUE']))
				$fields['ENTITY_VALUE'] = $entity['ENTITY_VALUE'];
			if (is_array($fields['FIELD_ENTITY']))
				$fields['FIELD_ENTITY'] = implode('-', $fields['FIELD_ENTITY']);
			if (isset($entity['FIELD_TABLE']) && is_array($entity['FIELD_TABLE']))
			{
				foreach ($entity['FIELD_TABLE'] as $oneField)
				{
					if (empty($oneField))
						continue;
					$fields['FIELD_TABLE'] = $oneField;
					$result = Catalog\DiscountEntityTable::add($fields);
				}
				unset($oneField);
			}
			else
			{
				$fields['FIELD_TABLE'] = (isset($entity['FIELD_TABLE']) ? $entity['FIELD_TABLE'] : $entity['FIELD_ENTITY']);
				$result = Catalog\DiscountEntityTable::add($fields);
			}
		}
		unset($entity);

		if (isset($arFields['CATALOG_COUPONS']))
		{
			if (!is_array($arFields["CATALOG_COUPONS"]))
			{
				$arFields["CATALOG_COUPONS"] = array(
					"DISCOUNT_ID" => $ID,
					"ACTIVE" => "Y",
					"ONE_TIME" => "Y",
					"COUPON" => $arFields["CATALOG_COUPONS"],
					"DATE_APPLY" => false
				);
			}

			$arKeys = array_keys($arFields["CATALOG_COUPONS"]);
			if (!is_array($arFields["CATALOG_COUPONS"][$arKeys[0]]))
				$arFields["CATALOG_COUPONS"] = array($arFields["CATALOG_COUPONS"]);

			foreach ($arFields["CATALOG_COUPONS"] as &$arOneCoupon)
			{
				if (!empty($arOneCoupon['COUPON']))
				{
					$arOneCoupon['DISCOUNT_ID'] = $ID;
					if (CCatalogDiscountCoupon::Add($arOneCoupon, false))
						$arFields['USE_COUPONS'] = 'Y';
				}
				if (isset($arOneCoupon))
					unset($arOneCoupon);
			}
		}

		foreach (GetModuleEvents("catalog", "OnDiscountAdd", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

		return $ID;
	}

	public function Update($ID, $arFields)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		foreach (GetModuleEvents("catalog", "OnBeforeDiscountUpdate", true) as $arEvent)
		{
			if (false === ExecuteModuleEventEx($arEvent, array($ID, &$arFields)))
				return false;
		}

		$boolExistUserGroups = (isset($arFields['GROUP_IDS']) && is_array($arFields['GROUP_IDS']));
		$boolExistPriceTypes = (isset($arFields['CATALOG_GROUP_IDS']) && is_array($arFields['CATALOG_GROUP_IDS']));
		$boolUpdateRestrictions = $boolExistUserGroups || $boolExistPriceTypes || isset($arFields['ACTIVE']);

		$mxRows = false;
		if ($boolUpdateRestrictions)
		{
			if (!$boolExistUserGroups)
			{
				if (!CCatalogDiscount::__FillArrays($ID, $arFields, 'GROUP_IDS'))
					return false;
			}
			if (!$boolExistPriceTypes)
			{
				if (!CCatalogDiscount::__FillArrays($ID, $arFields, 'CATALOG_GROUP_IDS'))
					return false;
			}
			$mxRows = self::__ParseArrays($arFields);
			if (empty($mxRows) || !is_array($mxRows))
				return false;
		}

		$boolNewVersion = true;
		if (!array_key_exists('CONDITIONS', $arFields))
		{
			self::__ConvertOldConditions('UPDATE', $arFields);
			$boolNewVersion = false;
		}

		if (!CCatalogDiscount::_Update($ID, $arFields))
			return false;

		if ($boolNewVersion)
		{
			$arValuesList = self::__GetConditionValues($arFields);
			if (is_array($arValuesList) && !empty($arValuesList))
			{
				self::__GetOldOneEntity($arFields, $arValuesList, 'IBLOCK_IDS', 'CondIBIBlock');
				self::__GetOldOneEntity($arFields, $arValuesList, 'SECTION_IDS', 'CondIBSection');
				self::__GetOldOneEntity($arFields, $arValuesList, 'PRODUCT_IDS', 'CondIBElement');
			}
		}

		if ($boolUpdateRestrictions)
		{
			if (!CCatalogDiscount::__UpdateSubdiscount($ID, $mxRows, (isset($arFields['ACTIVE']) ? $arFields['ACTIVE'] : '')))
				return false;
		}

		CCatalogDiscount::__UpdateOldEntities($ID, $arFields, true);

		if (isset($arFields['ENTITY']))
		{
			$iterator = Catalog\DiscountEntityTable::getList([
				'select' => ['ID'],
				'filter' => ['=DISCOUNT_ID' => $ID],
				'order' => ['ID' => 'ASC']
			]);
			$entityIds = $iterator->fetchAll();
			unset($iterator);
			foreach ($arFields['ENTITY'] as $entity)
			{
				$fields = array(
					'DISCOUNT_ID' => $ID,
					'MODULE_ID' => $entity['MODULE'],
					'ENTITY' => $entity['ENTITY'],
					'FIELD_ENTITY' => $entity['FIELD_ENTITY'],
				);
				if (isset($entity['ENTITY_ID']))
					$fields['ENTITY_ID'] = $entity['ENTITY_ID'];
				if (isset($entity['ENTITY_VALUE']))
					$fields['ENTITY_VALUE'] = $entity['ENTITY_VALUE'];
				if (is_array($fields['FIELD_ENTITY']))
					$fields['FIELD_ENTITY'] = implode('-', $fields['FIELD_ENTITY']);
				if (isset($entity['FIELD_TABLE']) && is_array($entity['FIELD_TABLE']))
				{
					foreach ($entity['FIELD_TABLE'] as $oneField)
					{
						if (empty($oneField))
							continue;
						$fields['FIELD_TABLE'] = $oneField;
						if (!empty($entityIds))
						{
							$rowId = array_shift($entityIds);
							$result = Catalog\DiscountEntityTable::update($rowId, $fields);
						}
						else
						{
							$result = Catalog\DiscountEntityTable::add($fields);
						}
					}
					unset($oneField);
				}
				else
				{
					$fields['FIELD_TABLE'] = (isset($entity['FIELD_TABLE']) ? $entity['FIELD_TABLE'] : $entity['FIELD_ENTITY']);
					if (!empty($entityIds))
					{
						$rowId = array_shift($entityIds);
						$result = Catalog\DiscountEntityTable::update($rowId, $fields);
					}
					else
					{
						$result = Catalog\DiscountEntityTable::add($fields);
					}
				}
			}
			unset($entity);
			if (!empty($entityIds))
			{
				foreach ($entityIds as $rowId)
				{
					$result = Catalog\DiscountEntityTable::delete($rowId);
				}
			}
			unset($entityIds);
		}

		if (isset($arFields['CATALOG_COUPONS']))
		{
			if (!is_array($arFields["CATALOG_COUPONS"]))
			{
				$arFields["CATALOG_COUPONS"] = array(
					"DISCOUNT_ID" => $ID,
					"ACTIVE" => "Y",
					"ONE_TIME" => "Y",
					"COUPON" => $arFields["CATALOG_COUPONS"],
					"DATE_APPLY" => false
				);
			}

			$arKeys = array_keys($arFields["CATALOG_COUPONS"]);
			if (!is_array($arFields["CATALOG_COUPONS"][$arKeys[0]]))
				$arFields["CATALOG_COUPONS"] = array($arFields["CATALOG_COUPONS"]);

			foreach ($arFields["CATALOG_COUPONS"] as &$arOneCoupon)
			{
				if (!empty($arOneCoupon['COUPON']))
				{
					$arOneCoupon['DISCOUNT_ID'] = $ID;
					CCatalogDiscountCoupon::Add($arOneCoupon, false);
				}
				if (isset($arOneCoupon))
					unset($arOneCoupon);
			}
		}

		foreach (GetModuleEvents("catalog", "OnDiscountUpdate", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));
		}

		return $ID;
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::SetCoupon()
	 *
	 * @param string $coupon
	 * @return bool
	 */
	public static function SetCoupon($coupon)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::SetCoupon($coupon);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::GetCoupons()
	 *
	 * @return array
	 */
	public static function GetCoupons()
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::GetCoupons();
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::EraseCoupon()
	 *
	 * @param string $strCoupon
	 * @return bool
	 */
	public static function EraseCoupon($strCoupon)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::EraseCoupon($strCoupon);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::ClearCoupon()
	 *
	 * @return void
	 */
	public static function ClearCoupon()
	{
		/** @noinspection PhpDeprecationInspection */
		CCatalogDiscountCoupon::ClearCoupon();
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::SetCouponByManage()
	 *
	 * @param int $intUserID
	 * @param string $strCoupon
	 * @return bool
	 */
	public static function SetCouponByManage($intUserID,$strCoupon)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::SetCouponByManage($intUserID,$strCoupon);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::GetCouponsByManage()
	 *
	 * @param int $intUserID
	 * @return array
	 */
	public static function GetCouponsByManage($intUserID)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::GetCouponsByManage($intUserID);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::EraseCouponByManage()
	 *
	 * @param int $intUserID
	 * @param string $strCoupon
	 * @return bool
	 */
	public static function EraseCouponByManage($intUserID, $strCoupon)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::EraseCouponByManage($intUserID,$strCoupon);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::ClearCouponsByManage()
	 *
	 * @param int $intUserID		User id.
	 * @return bool
	 */
	public static function ClearCouponsByManage($intUserID)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::ClearCouponsByManage($intUserID);
	}

	public static function OnCurrencyDelete($Currency)
	{
		if (empty($Currency)) return false;

		$dbDiscounts = CCatalogDiscount::GetList(array(), array("CURRENCY" => $Currency), false, false, array("ID"));
		while ($arDiscounts = $dbDiscounts->Fetch())
		{
			CCatalogDiscount::Delete($arDiscounts["ID"]);
		}

		return true;
	}

	public static function OnGroupDelete($GroupID)
	{
		global $DB;
		$GroupID = (int)$GroupID;
		if ($GroupID <= 0)
			return false;

		return $DB->Query("DELETE FROM b_catalog_discount2group WHERE GROUP_ID = ".$GroupID, true);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 *
	 * @param int $ID
	 * @return void
	 */
	public function GenerateDataFile($ID)
	{
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 *
	 * @param int $ID
	 * @param bool|string $strDataFileName
	 * @return void
	 */
	public function ClearFile($ID, $strDataFileName = false)
	{
	}

	public static function GetDiscountByPrice($productPriceID, $arUserGroups = array(), $renewal = "N", $siteID = false, $arDiscountCoupons = false)
	{
		global $APPLICATION;

		foreach (GetModuleEvents("catalog", "OnGetDiscountByPrice", true) as $arEvent)
		{
			$mxResult = ExecuteModuleEventEx($arEvent, array($productPriceID, $arUserGroups, $renewal, $siteID, $arDiscountCoupons));
			if (true !== $mxResult)
				return $mxResult;
		}

		$productPriceID = (int)$productPriceID;
		if ($productPriceID <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_PRICE_ID_ABSENT"), "NO_PRICE_ID");
			return false;
		}

		$dbPrice = CPrice::GetListEx(
			array(),
			array("ID" => $productPriceID),
			false,
			false,
			array("ID", "PRODUCT_ID", "CATALOG_GROUP_ID", "ELEMENT_IBLOCK_ID")
		);
		if ($arPrice = $dbPrice->Fetch())
		{
			return CCatalogDiscount::GetDiscount($arPrice["PRODUCT_ID"], $arPrice["ELEMENT_IBLOCK_ID"], array($arPrice["CATALOG_GROUP_ID"]), $arUserGroups, $renewal, $siteID, $arDiscountCoupons);
		}
		else
		{
			$APPLICATION->ThrowException(
				Loc::getMessage(
					'BT_MOD_CATALOG_DISC_ERR_PRICE_ID_NOT_FOUND',
					array(
						'#ID#' => $productPriceID
					)
				),
				'NO_PRICE'
			);
			return false;
		}
	}

	public static function GetDiscountByProduct($productID = 0, $arUserGroups = array(), $renewal = "N", $arCatalogGroups = array(), $siteID = false, $arDiscountCoupons = false)
	{
		global $APPLICATION;

		foreach (GetModuleEvents("catalog", "OnGetDiscountByProduct", true) as $arEvent)
		{
			$mxResult = ExecuteModuleEventEx($arEvent, array($productID, $arUserGroups, $renewal, $arCatalogGroups, $siteID, $arDiscountCoupons));
			if (true !== $mxResult)
				return $mxResult;
		}

		$productID = (int)$productID;
		if ($productID <= 0)
		{
			$APPLICATION->ThrowException(
				Loc::getMessage(
					'BT_MOD_CATALOG_DISC_ERR_ELEMENT_ID_NOT_FOUND',
					array(
						'#ID' => $productID
					)
				),
				'NO_ELEMENT');
			return false;
		}

		$intIBlockID = CIBlockElement::GetIBlockByID($productID);
		if ($intIBlockID === false)
		{
			$APPLICATION->ThrowException(
				Loc::getMessage(
					'BT_MOD_CATALOG_DISC_ERR_ELEMENT_ID_NOT_FOUND',
					array(
						'#ID#' => $productID
					)
				),
				'NO_ELEMENT'
			);
			return false;
		}

		return CCatalogDiscount::GetDiscount($productID, $intIBlockID, $arCatalogGroups, $arUserGroups, $renewal, $siteID, $arDiscountCoupons);
	}

	private static function getDiscountsFromApplyResult(array $calcResults, \Bitrix\Sale\BasketItemBase $basketItem)
	{
		$finalDiscountList = array();

		if($calcResults && !empty($calcResults['PRICES']['BASKET']) && count($calcResults['PRICES']['BASKET']) === 1)
		{
			if(isset($calcResults['PRICES']['BASKET'][$basketItem->getBasketCode()]))
			{
				$priceData = $calcResults['PRICES']['BASKET'][$basketItem->getBasketCode()];
				if (!empty($calcResults['RESULT']['BASKET'][$basketItem->getBasketCode()]))
				{
					foreach($calcResults['RESULT']['BASKET'][$basketItem->getBasketCode()] as $resultRow)
					{
						$realDiscountId = null;
						if(!isset($calcResults['DISCOUNT_LIST'][$resultRow['DISCOUNT_ID']]))
						{
							continue;
						}

						$realDiscountId = $calcResults['DISCOUNT_LIST'][$resultRow['DISCOUNT_ID']]['REAL_DISCOUNT_ID'];
						if(isset($finalDiscountList[$realDiscountId]))
						{
							continue;
						}

						$finalDiscountList[$realDiscountId] = array_merge(
							$calcResults['DISCOUNT_LIST'][$resultRow['DISCOUNT_ID']],
							$calcResults['FULL_DISCOUNT_LIST'][$realDiscountId]
						);
					}
				}
			}
		}

		return $finalDiscountList;
	}

	private static function getReformattedDiscounts(array $finalDiscountList, array $calcResults, $siteId, $isRenewal = false)
	{
		$reformatList = array();
		foreach($finalDiscountList as $discount)
		{
			if($discount['SHORT_DESCRIPTION_STRUCTURE'])
			{
				$actionConfiguration = $discount['SHORT_DESCRIPTION_STRUCTURE'];
			}
			else
			{
				$actionConfiguration = \Bitrix\Sale\Discount\Actions::getActionConfiguration($discount);
			}

			if(!$actionConfiguration || $actionConfiguration['VALUE_TYPE'] === \Bitrix\Sale\Discount\Actions::VALUE_TYPE_SUMM)
			{
				continue;
			}

			if($actionConfiguration['TYPE'] == 'Extra')
			{
				continue;
			}

			if ($actionConfiguration['TYPE'] == 'Closeout')
				$actionConfiguration['VALUE_TYPE'] = self::TYPE_SALE;

			$reformattedDiscount = array(
				'ID' => $discount['ID'],
				'TYPE' => CCatalogDiscount::ENTITY_ID,
				'SITE_ID' => $siteId,
				'ACTIVE' => 'Y',
				'ACTIVE_FROM' => empty($discount['ACTIVE_FROM']) ? '' : $discount['ACTIVE_FROM']->toString(),
				'ACTIVE_TO' => empty($discount['ACTIVE_TO']) ? '' : $discount['ACTIVE_TO']->toString(),
				'RENEWAL' => $isRenewal? 'Y' : 'N',
				'NAME' => $discount['NAME'],
				'SORT' => $discount['SORT'],
				'MAX_DISCOUNT' => $actionConfiguration['LIMIT_VALUE'],
				'VALUE_TYPE' => $actionConfiguration['VALUE_TYPE'],
				'VALUE' => $actionConfiguration['VALUE'],
				'CURRENCY' => $discount['CURRENCY'],
				'PRIORITY' => $discount['PRIORITY'],
				'LAST_DISCOUNT' => $discount['LAST_DISCOUNT'],
				'LAST_LEVEL_DISCOUNT' => $discount['LAST_LEVEL_DISCOUNT'],
				'COUPON' => '',
				'COUPON_ONE_TIME' => null,
				'COUPON_ACTIVE' => '',
				'UNPACK' => $discount['UNPACK'],
				'CONDITIONS' => serialize($discount['CONDITIONS']),
				'HANDLERS' => array(
					'MODULES' => array(),
					'EXT_FILES' => array(),
				),
				'MODULE_ID' => 'sale', //or catalog?
			);

			if($discount['USE_COUPONS'] === 'Y')
			{
				foreach($calcResults['COUPON_LIST'] as $coupon)
				{
					if($coupon['DATA']['DISCOUNT_ID'] != $discount['REAL_DISCOUNT_ID'])
					{
						continue;
					}

					$reformattedDiscount['COUPON'] = $coupon['COUPON'];
					$reformattedDiscount['COUPON_ACTIVE'] = $coupon['DATA']['ACTIVE'];
					if($coupon['TYPE'] == \Bitrix\Sale\Internals\DiscountCouponTable::TYPE_BASKET_ROW)
					{
						$reformattedDiscount['COUPON_ONE_TIME'] = 'Y';
					}
					elseif($coupon['TYPE'] == \Bitrix\Sale\Internals\DiscountCouponTable::TYPE_ONE_ORDER)
					{
						$reformattedDiscount['COUPON_ONE_TIME'] = 'O';
					}
					elseif($coupon['TYPE'] == \Bitrix\Sale\Internals\DiscountCouponTable::TYPE_MULTI_ORDER)
					{
						$reformattedDiscount['COUPON_ONE_TIME'] = 'N';
					}
				}
			}

			$reformatList[$discount['ID']] = $reformattedDiscount;
		}

		return $reformatList;
	}

	private static function getSaleDiscountsByProduct(array $product, $siteId, array $userGroups, array $priceRow, $isRenewal, $coupons)
	{
		if (empty($priceRow))
			return array();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		$freezeCoupons = (empty($coupons) && is_array($coupons));

		if ($freezeCoupons)
			Sale\DiscountCouponsManager::freezeCouponStorage();

		/** @var \Bitrix\Sale\Basket $basket */
		static $basket = null,
			/** @var \Bitrix\Sale\BasketItem $basketItem */
			$basketItem = null;

		if ($basket !== null)
		{
			if ($basket->getSiteId() != $siteId)
			{
				$basket = null;
				$basketItem = null;
			}
		}
		if ($basket !== null)
		{
			$orderedBasket = $basket->getOrder() !== null;
			if ($orderedBasket !== $isRenewal)
			{
				$basket = null;
				$basketItem = null;
			}
		}
		if ($basket === null)
		{
			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();

			$basket = $basketClass::create($siteId);
			$basketItem = $basket->createItem($product['MODULE'], $product['ID']);
		}

		$config = Catalog\Product\Price\Calculation::getConfig();
		if ($config['CURRENCY'] !== null && $config['CURRENCY'] != $priceRow['CURRENCY'])
		{
			$priceRow['PRICE'] = \CCurrencyRates::ConvertCurrency(
				$priceRow['PRICE'],
				$priceRow['CURRENCY'],
				$config['CURRENCY']
			);
			$priceRow['CURRENCY'] = $config['CURRENCY'];
		}

		$fields = array(
			'PRODUCT_ID' => $product['ID'],
			'QUANTITY' => 1,
			'LID' => $siteId,
			'PRODUCT_PRICE_ID' => $priceRow['ID'],
			'PRICE' => $priceRow['PRICE'],
			'BASE_PRICE' => $priceRow['PRICE'],
			'DISCOUNT_PRICE' => 0,
			'CURRENCY' => $priceRow['CURRENCY'],
			'CAN_BUY' => 'Y',
			'DELAY' => 'N',
			'PRICE_TYPE_ID' => (int)$priceRow['CATALOG_GROUP_ID']
		);

		$basketItem->setFieldsNoDemand($fields);

		if($isRenewal)
		{
			/** @var Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			/** @var \Bitrix\Sale\Order $order */
			$order = $orderClass::create($siteId);
			$order->setField('RECURRING_ID', 1);
			$order->setBasket($basket);

			$discount = $order->getDiscount();
		}
		else
		{
			$discount = Sale\Discount::buildFromBasket($basket, new Context\UserGroup($userGroups));
		}

		$discount->setExecuteModuleFilter(array('all', 'catalog'));
		$discount->calculate();

		$calcResults = $discount->getApplyResult(true);
		$finalDiscountList = static::getDiscountsFromApplyResult($calcResults, $basketItem);

		if ($freezeCoupons)
			Sale\DiscountCouponsManager::unFreezeCouponStorage();
		$discount->setExecuteModuleFilter(array('all', 'sale', 'catalog'));

		return static::getReformattedDiscounts($finalDiscountList, $calcResults, $siteId, $isRenewal);
	}

	/**
	 * @param int $intProductID
	 * @param int $intIBlockID
	 * @param array $arCatalogGroups
	 * @param array $arUserGroups
	 * @param string $strRenewal
	 * @param bool|string $siteID
	 * @param bool|array $arDiscountCoupons
	 * @param bool $boolSKU
	 * @param bool $boolGetIDS
	 * @return array|false
	 */
	public static function GetDiscount($intProductID, $intIBlockID, $arCatalogGroups = array(), $arUserGroups = array(), $strRenewal = "N", $siteID = false, $arDiscountCoupons = false, $boolSKU = true, $boolGetIDS = false)
	{
		static $eventOnGetExists = null;
		static $eventOnResultExists = null;

		/** @global CMain $APPLICATION */
		global $APPLICATION;

		self::initDiscountSettings();

		if ($eventOnGetExists === true || $eventOnGetExists === null)
		{
			foreach (GetModuleEvents("catalog", "OnGetDiscount", true) as $arEvent)
			{
				$eventOnGetExists = true;
				$mxResult = ExecuteModuleEventEx($arEvent, array($intProductID, $intIBlockID, $arCatalogGroups, $arUserGroups, $strRenewal, $siteID, $arDiscountCoupons, $boolSKU, $boolGetIDS));
				if ($mxResult !== true)
					return $mxResult;
			}
			if ($eventOnGetExists === null)
				$eventOnGetExists = false;
		}

		$boolSKU = ($boolSKU === true);
		$boolGetIDS = ($boolGetIDS === true);

		$intProductID = (int)$intProductID;
		if ($intProductID <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_PRODUCT_ID_ABSENT"), "NO_PRODUCT_ID");
			return false;
		}

		$intIBlockID = (int)$intIBlockID;
		if ($intIBlockID <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("BT_MOD_CATALOG_DISC_ERR_IBLOCK_ID_ABSENT"), "NO_IBLOCK_ID");
			return false;
		}

		if (!is_array($arUserGroups))
			$arUserGroups = array($arUserGroups);
		$arUserGroups[] = 2;
		if (!empty($arUserGroups))
			Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups, true);

		if (!is_array($arCatalogGroups))
			$arCatalogGroups = array($arCatalogGroups);
		if (empty($arCatalogGroups))
		{
			$catalogGroupIterator = CCatalogGroup::GetGroupsList(array(
				'GROUP_ID' => $arUserGroups,
				'BUY' => array('Y', 'N')
			));
			while ($catalogGroup = $catalogGroupIterator->Fetch())
				$arCatalogGroups[$catalogGroup['CATALOG_GROUP_ID']] = $catalogGroup['CATALOG_GROUP_ID'];
			unset($catalogGroup, $catalogGroupIterator);
		}
		if (!empty($arCatalogGroups))
		{
			$emptyExist = (in_array(-1, $arCatalogGroups));
			Main\Type\Collection::normalizeArrayValuesByInt($arCatalogGroups, true);
			if ($emptyExist)
				$arCatalogGroups = array_merge(array(-1), $arCatalogGroups);
			unset($emptyExist);
		}
		if (empty($arCatalogGroups))
			return false;

		$strRenewal = ((string)$strRenewal == 'Y' ? 'Y' : 'N');

		if ($siteID === false)
			$siteID = SITE_ID;

		$arSKUExt = false;
		if ($boolSKU)
		{
			$arSKUExt = CCatalogSku::GetInfoByOfferIBlock($intIBlockID);
			$boolSKU = !empty($arSKUExt);
		}

		$arResult = array();
		$arResultID = array();

		if (self::$useSaleDiscount && Loader::includeModule('sale'))
		{
			$cacheIndex = md5('S'.$siteID.'-U'.implode('_', $arUserGroups));
			if (!isset(self::$needDiscountCache[$cacheIndex]))
			{
				self::$needDiscountCache[$cacheIndex] = false;

				$cache = Sale\Discount\RuntimeCache\DiscountCache::getInstance();
				$ids = $cache->getDiscountIds($arUserGroups);
				if (!empty($ids))
				{
					$discountList = $cache->getDiscounts(
						$ids,
						['all', 'catalog'],
						$siteID,
						[]
					);
					if (!empty($discountList))
					{
						self::$needDiscountCache[$cacheIndex] = true;
					}
					unset($discountList);
				}
				unset($ids, $cache);
			}

			$product = array(
				'ID' => $intProductID,
				'MODULE' => 'catalog',
			);

			if (self::$needDiscountCache[$cacheIndex] && $arCatalogGroups !== array(-1))
			{
				Catalog\Product\Price\Calculation::pushConfig();
				Catalog\Product\Price\Calculation::setConfig([
					'CURRENCY' => Sale\Internals\SiteCurrencyTable::getSiteCurrency($siteID)
				]);

				$isCompatibilityUsed = Sale\Compatible\DiscountCompatibility::isUsed();
				Sale\Compatible\DiscountCompatibility::stopUsageCompatible();

				foreach ($arCatalogGroups as $catalogGroup)
				{
					$priceRow = Catalog\Discount\DiscountManager::getPriceDataByProductId($intProductID, $catalogGroup);
					if (!$priceRow)
						continue;

					$discountList = self::getSaleDiscountsByProduct(
						$product,
						$siteID,
						$arUserGroups,
						$priceRow,
						$strRenewal === 'Y',
						$arDiscountCoupons
					);
					foreach ($discountList as $discount)
					{
						if (isset($arResult[$discount['ID']]))
							continue;
						$arResult[$discount['ID']] = $discount;
						$arResultID[$discount['ID']] = $discount['ID'];
					}
				}

				if ($isCompatibilityUsed)
					Sale\Compatible\DiscountCompatibility::revertUsageCompatible();
				unset($isCompatibilityUsed);

				Catalog\Product\Price\Calculation::popConfig();
			}
			unset($cacheIndex);
		}
		else
		{
			$strCacheKey = md5('C'.implode('_', $arCatalogGroups).'-'.'U'.implode('_', $arUserGroups));
			if (!isset(self::$arCacheDiscountFilter[$strCacheKey]))
			{
				$arFilter = array(
					'PRICE_TYPE_ID' => $arCatalogGroups,
					'USER_GROUP_ID' => $arUserGroups,
				);
				$arDiscountIDs = CCatalogDiscount::__GetDiscountID($arFilter);
				if (!empty($arDiscountIDs))
					sort($arDiscountIDs);

				self::$arCacheDiscountFilter[$strCacheKey] = $arDiscountIDs;
			}
			else
			{
				$arDiscountIDs = self::$arCacheDiscountFilter[$strCacheKey];
			}

			$arProduct = array();
			if (!empty($arDiscountIDs))
			{
				$orderCoupons = array();
				if ($arDiscountCoupons === false)
				{
					if (self::$existCouponsManager && Loader::includeModule('sale'))
					{
						$arDiscountCoupons = DiscountCouponsManager::getForApply(
							array('MODULE_ID' => 'catalog', 'DISCOUNT_ID' => $arDiscountIDs),
							array('MODULE_ID' => 'catalog', 'PRODUCT_ID' => $intProductID, 'BASKET_ID' => '0'),
							true
						);
						if (!empty($arDiscountCoupons))
						{
							$orderCoupons = array_filter($arDiscountCoupons, '\Bitrix\Sale\DiscountCouponsManager::filterOrderCoupons');
							$arDiscountCoupons = array_keys($arDiscountCoupons);
						}
					}
					else
					{
						if (!isset($_SESSION['CATALOG_USER_COUPONS']) || !is_array($_SESSION['CATALOG_USER_COUPONS']))
							$_SESSION['CATALOG_USER_COUPONS'] = array();
						$arDiscountCoupons = $_SESSION["CATALOG_USER_COUPONS"];
					}
				}
				else
				{
					if (self::$existCouponsManager && Loader::includeModule('sale'))
					{
						$orderCoupons = DiscountCouponsManager::getOrderedCoupons(true, array('COUPON' => $arDiscountCoupons));
					}
				}
				if ($arDiscountCoupons === false)
					$arDiscountCoupons = array();
				$boolGenerate = false;
				if (empty(self::$cacheDiscountHandlers))
				{
					self::$cacheDiscountHandlers = CCatalogDiscount::getDiscountHandlers($arDiscountIDs);
				}
				else
				{
					$needDiscountHandlers = array();
					foreach ($arDiscountIDs as &$discountID)
					{
						if (!isset(self::$cacheDiscountHandlers[$discountID]))
							$needDiscountHandlers[] = $discountID;
					}
					unset($discountID);
					if (!empty($needDiscountHandlers))
					{
						$discountHandlersList = CCatalogDiscount::getDiscountHandlers($needDiscountHandlers);
						if (!empty($discountHandlersList))
						{
							foreach ($discountHandlersList as $discountID => $discountHandlers)
								self::$cacheDiscountHandlers[$discountID] = $discountHandlers;

							unset($discountHandlers, $discountID);
						}
						unset($discountHandlersList);
					}
					unset($needDiscountHandlers);
				}

				$strCacheKey = 'D'.implode('_', $arDiscountIDs).'-'.'S'.$siteID.'-R'.$strRenewal;
				if (!empty($arDiscountCoupons))
					$strCacheKey .= '-C'.implode('|', $arDiscountCoupons);

				$strCacheKey = md5($strCacheKey);

				if (!isset(self::$arCacheDiscountResult[$strCacheKey]))
				{
					$arDiscountList = array();

					$couponsDiscount = array();
					$couponsList = array();
					if (!empty($arDiscountCoupons) && is_array($arDiscountCoupons))
					{
						$iterator = Catalog\DiscountCouponTable::getList(array(
							'select' => array('DISCOUNT_ID', 'COUPON', 'ACTIVE', 'TYPE'),
							'filter' => array('@DISCOUNT_ID' => $arDiscountIDs,'@COUPON' => $arDiscountCoupons),
							'order' => array('DISCOUNT_ID' => 'ASC')
						));
						while ($row = $iterator->fetch())
						{
							$id = (int)$row['DISCOUNT_ID'];
							$couponsList[$row['COUPON']] = $row;
							if (isset($couponsDiscount[$id]))
								continue;
							$couponsDiscount[$id] = $row['COUPON'];
						}
						unset($id, $row, $iterator);
					}

					$select = array(
						'ID', 'TYPE', 'SITE_ID', 'ACTIVE', 'ACTIVE_FROM', 'ACTIVE_TO',
						'RENEWAL', 'NAME', 'SORT', 'MAX_DISCOUNT', 'VALUE_TYPE', 'VALUE', 'CURRENCY',
						'PRIORITY', 'LAST_DISCOUNT',
						'USE_COUPONS', 'UNPACK', 'CONDITIONS'
					);
					$currentDatetime = new Main\Type\DateTime();
					$discountRows = array_chunk($arDiscountIDs, 500);
					foreach ($discountRows as $pageIds)
					{
						$discountFilter = array(
							'@ID' => $pageIds,
							'=SITE_ID' => $siteID,
							'=TYPE' => Catalog\DiscountTable::TYPE_DISCOUNT,
							array(
								'LOGIC' => 'OR',
								'ACTIVE_FROM' => '',
								'<=ACTIVE_FROM' => $currentDatetime
							),
							array(
								'LOGIC' => 'OR',
								'ACTIVE_TO' => '',
								'>=ACTIVE_TO' => $currentDatetime
							),
							'=RENEWAL' => $strRenewal
						);
						if (empty($couponsDiscount))
						{
							$discountFilter['=USE_COUPONS'] = 'N';
						}
						else
						{
							$discountFilter[] = array(
								'LOGIC' => 'OR',
								'=USE_COUPONS' => 'N',
								array(
									'=USE_COUPONS' => 'Y',
									'@ID' => array_keys($couponsDiscount)
								)
							);
						}
						CTimeZone::Disable();
						$iterator = Catalog\DiscountTable::getList(array(
							'select' => $select,
							'filter' => $discountFilter
						));
						while ($row = $iterator->fetch())
						{
							$row['HANDLERS'] = array();
							$row['MODULE_ID'] = 'catalog';
							$row['TYPE'] = (int)$row['TYPE'];
							if ($row['ACTIVE_FROM'] instanceof Main\Type\DateTime)
								$row['ACTIVE_FROM'] = $row['ACTIVE_FROM']->toString();
							if ($row['ACTIVE_TO'] instanceof Main\Type\DateTime)
								$row['ACTIVE_TO'] = $row['ACTIVE_TO']->toString();
							if ($row['USE_COUPONS'] == 'N')
							{
								$row['COUPON_ACTIVE'] = '';
								$row['COUPON'] = '';
								$row['COUPON_ONE_TIME'] = null;
							}
							else
							{
								$id = (int)$row['ID'];
								if (isset($couponsDiscount[$id]))
								{
									$coupon = $couponsDiscount[$id];
									$row['COUPON'] = $coupon;
									$row['COUPON_ACTIVE'] = $couponsList[$coupon]['ACTIVE'];
									$row['COUPON_ONE_TIME'] = $couponsList[$coupon]['TYPE'];
									unset($coupon);
								}
								else
								{
									continue;
								}
							}
							$arDiscountList[] = $row;
						}
						unset($row, $iterator);
						CTimeZone::Enable();
					}
					unset($pageIds, $discountRows);

					self::$arCacheDiscountResult[$strCacheKey] = $arDiscountList;
				}
				else
				{
					$arDiscountList = self::$arCacheDiscountResult[$strCacheKey];
				}

				if (!empty($arDiscountList))
				{
					$discountApply = array();
					foreach ($arDiscountList as &$arPriceDiscount)
					{
						if (isset($discountApply[$arPriceDiscount['ID']]))
							continue;

						if (
							$arPriceDiscount['COUPON'] != ''
							&& $arPriceDiscount['COUPON_ACTIVE'] == 'N'
							&& !isset($orderCoupons[$arPriceDiscount['COUPON']])
						)
							continue;

						if (!$boolGenerate)
						{
							if (!isset(self::$arCacheProduct[$intProductID]))
							{
								$arProduct = array('ID' => $intProductID, 'IBLOCK_ID' => $intIBlockID);
								if (!self::__GenerateFields($arProduct))
									return false;
								if ($boolSKU)
								{
									if (!self::__GenerateParent($arProduct, $arSKUExt))
										$boolSKU = false;
								}
								$boolGenerate = true;
								self::$arCacheProduct[$intProductID] = $arProduct;
							}
							else
							{
								$boolGenerate = true;
								$arProduct = self::$arCacheProduct[$intProductID];
							}
						}
						$discountApply[$arPriceDiscount['ID']] = true;
						$applyFlag = true;
						if (isset(self::$cacheDiscountHandlers[$arPriceDiscount['ID']]))
						{
							$arPriceDiscount['HANDLERS'] = self::$cacheDiscountHandlers[$arPriceDiscount['ID']];
							$moduleList = self::$cacheDiscountHandlers[$arPriceDiscount['ID']]['MODULES'];
							if (!empty($moduleList))
							{
								foreach ($moduleList as &$moduleID)
								{
									if (!isset(self::$usedModules[$moduleID]))
										self::$usedModules[$moduleID] = Loader::includeModule($moduleID);

									if (!self::$usedModules[$moduleID])
									{
										$applyFlag = false;
										break;
									}
								}
								unset($moduleID);
							}
							unset($moduleList);
						}
						if ($applyFlag && CCatalogDiscount::__Unpack($arProduct, $arPriceDiscount['UNPACK']))
						{
							$arResult[] = $arPriceDiscount;
							$arResultID[] = $arPriceDiscount['ID'];
						}
					}
					unset($arPriceDiscount);
					unset($discountApply);
				}
			}

			if (!$boolGetIDS)
			{
				$arDiscSave = CCatalogDiscountSave::GetDiscount(array(
					'USER_ID' => 0,
					'USER_GROUPS' => $arUserGroups,
					'SITE_ID' => $siteID
				));
				if (!empty($arDiscSave))
					$arResult = (!empty($arResult) ? array_merge($arResult, $arDiscSave) : $arDiscSave);
			}
			else
			{
				$arResult = $arResultID;
			}
		}

		if ($eventOnResultExists === true || $eventOnResultExists === null)
		{
			foreach (GetModuleEvents("catalog", "OnGetDiscountResult", true) as $arEvent)
			{
				$eventOnResultExists = true;
				ExecuteModuleEventEx($arEvent, array(&$arResult));
			}
			if ($eventOnResultExists === null)
				$eventOnResultExists = false;
		}

		return $arResult;
	}

	public static function HaveCoupons($ID, $excludeID = 0)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		$arFilter = array("DISCOUNT_ID" => $ID);

		$excludeID = (int)$excludeID;
		if ($excludeID > 0)
			$arFilter['!ID'] = $excludeID;

		$dbRes = CCatalogDiscountCoupon::GetList(array(), $arFilter, false, array('nTopCount' => 1), array("ID"));
		if ($dbRes->Fetch())
			return true;
		else
			return false;
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::OnSetCouponList()
	 *
	 * @param int $intUserID
	 * @param array|string $arCoupons
	 * @param array $arModules
	 * @return bool
	 */
	public static function OnSetCouponList($intUserID, $arCoupons, $arModules)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::OnSetCouponList($intUserID, $arCoupons, $arModules);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::OnClearCouponList()
	 *
	 * @param int $intUserID
	 * @param array|string $arCoupons
	 * @param array $arModules
	 * @return bool
	 */
	public static function OnClearCouponList($intUserID, $arCoupons, $arModules)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::OnClearCouponList($intUserID, $arCoupons, $arModules);
	}

	/**
	 * @deprecated deprecated since catalog 12.0.0
	 * @see CCatalogDiscountCoupon::OnDeleteCouponList()
	 *
	 * @param int $intUserID
	 * @param array $arModules
	 * @return bool
	 */
	public static function OnDeleteCouponList($intUserID, $arModules)
	{
		/** @noinspection PhpDeprecationInspection */
		return CCatalogDiscountCoupon::OnDeleteCouponList($intUserID, $arModules);
	}

	/**
	 * @param array $arProduct
	 * @param bool|array $arParams
	 * @return array
	 */
	public static function GetDiscountForProduct($arProduct, $arParams = false)
	{
		global $DB;

		self::initDiscountSettings();

		$arResult = array();

		if (empty($arProduct) || !is_array($arProduct))
			return $arResult;

		if (!is_array($arParams))
			$arParams = array();

		if (!isset($arProduct['ID']))
			$arProduct['ID'] = 0;
		$arProduct['ID'] = (int)$arProduct['ID'];
		if (!isset($arProduct['IBLOCK_ID']))
			$arProduct['IBLOCK_ID'] = 0;
		$arProduct['IBLOCK_ID'] = (int)$arProduct['IBLOCK_ID'];
		if ($arProduct['IBLOCK_ID'] <= 0 || $arProduct['ID'] <= 0)
			return $arResult;

		$strRenewal = (isset($arParams['RENEWAL']) ? $arParams['RENEWAL'] : 'N');
		$strRenewal = ($strRenewal == 'Y' ? 'Y' : 'N');

		$siteList = array();
		if (isset($arParams['SITE_ID']))
		{
			if (!is_array($arParams['SITE_ID']))
				$arParams['SITE_ID'] = array($arParams['SITE_ID']);
			if (!empty($arParams['SITE_ID']))
				$siteList = $arParams['SITE_ID'];
		}
		if (empty($siteList))
		{
			$iterator = Iblock\IblockSiteTable::getList(array(
				'select' => array('SITE_ID'),
				'filter' => array('=IBLOCK_ID' => $arProduct['IBLOCK_ID'])
			));
			while ($row = $iterator->fetch())
				$siteList[] = $row['SITE_ID'];
			unset($row, $iterator);
		}

		if (self::$useSaleDiscount && Loader::includeModule('sale'))
		{
			$groupList = CCatalogGroup::GetListArray();
			if (empty($groupList))
				return $arResult;
			$prices = array();
			foreach (array_keys($groupList) as $groupId)
			{
				$priceRow = Catalog\Discount\DiscountManager::getPriceDataByProductId($arProduct['ID'], $groupId);
				if (!empty($priceRow))
					$prices[$groupId] = $priceRow;
				unset($priceRow);
			}
			unset($groupId, $groupList);
			if (empty($prices))
				return $arResult;

			$renewal = ($strRenewal == 'Y');
			$product = array(
				'ID' => $arProduct['ID'],
				'MODULE' => 'catalog',
			);

			$allUserGroupsId = array_keys(static::getAllUserGroups());
			foreach ($siteList as $siteId)
			{
				foreach ($prices as $priceRow)
				{
					if(!$priceRow)
					{
						continue;
					}

					$siteResult = self::getSaleDiscountsByProduct(
						$product,
						$siteId,
						$allUserGroupsId,
						$priceRow,
						$renewal,
						[]
					);
					if (empty($siteResult))
						continue;

					foreach ($siteResult as $discount)
					{
						if (isset($arResult[$discount['ID']]))
							continue;
						$arResult[$discount['ID']] = $discount;
					}
					unset($discount, $siteResult);
				}
				unset($priceRow);
			}
			unset($siteId);
		}
		else
		{
			$arSKUExt = false;
			if (isset($arParams['SKU']) && $arParams['SKU'] == 'Y')
				$arSKUExt = CCatalogSku::GetInfoByOfferIBlock($arProduct['IBLOCK_ID']);

			$arFieldsParams = array();
			if (isset($arParams['TIME_ZONE']))
				$arFieldsParams['TIME_ZONE'] = $arParams['TIME_ZONE'];
			if (isset($arParams['PRODUCT']))
				$arFieldsParams['PRODUCT'] = $arParams['PRODUCT'];
			$boolGenerate = false;

			$arSelect = array('ID', 'SITE_ID', 'SORT', 'NAME', 'VALUE_TYPE', 'VALUE', 'MAX_DISCOUNT', 'CURRENCY', 'UNPACK', 'NOTES');
			if (isset($arParams['DISCOUNT_FIELDS']) && !empty($arParams['DISCOUNT_FIELDS']) && is_array($arParams['DISCOUNT_FIELDS']))
				$arSelect = $arParams['DISCOUNT_FIELDS'];
			if (!in_array('UNPACK', $arSelect))
				$arSelect[] = 'UNPACK';

			$strDate = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));
			if (isset($arParams['CURRENT_DATE']))
				$strDate = $arParams['CURRENT_DATE'];

			$strRenewal = (isset($arParams['RENEWAL']) ? $arParams['RENEWAL'] : 'N');
			$strRenewal = ($strRenewal == 'Y' ? 'Y' : 'N');

			$arFilter = array(
				'SITE_ID' => $siteList,
				'TYPE' => self::ENTITY_ID,
				'ACTIVE' => "Y",
				'RENEWAL' => $strRenewal,
				'+<=ACTIVE_FROM' => $strDate,
				'+>=ACTIVE_TO' => $strDate,
				'USE_COUPONS' => 'N'
			);
			CTimeZone::Disable();
			$rsPriceDiscounts = CCatalogDiscount::GetList(
				array(),
				$arFilter,
				false,
				false,
				$arSelect
			);
			CTimeZone::Enable();
			while ($arPriceDiscount = $rsPriceDiscounts->Fetch())
			{
				if (!$boolGenerate)
				{
					if (!isset(self::$arCacheProduct[$arProduct['ID']]))
					{
						if (!self::__GenerateFields($arProduct, $arFieldsParams))
							return $arResult;
						if (!empty($arSKUExt))
							self::__GenerateParent($arProduct, $arSKUExt);

						$boolGenerate = true;
						self::$arCacheProduct[$arProduct['ID']] = $arProduct;
					}
					$arProduct = self::$arCacheProduct[$arProduct['ID']];
				}
				if (!self::__Unpack($arProduct, $arPriceDiscount['UNPACK']))
					continue;

				unset($arPriceDiscount['UNPACK']);
				$arResult[] = $arPriceDiscount;
			}
			unset($arPriceDiscount, $rsPriceDiscounts);
		}

		return $arResult;
	}

	protected static function getAllUserGroups()
	{
		static $groups = array();

		if(!$groups)
		{
			$dbGroupsList = \CGroup::GetListEx(Array("ID" => "DESC"), array("ACTIVE" => "Y"));
			while ($group = $dbGroupsList->Fetch())
			{
				$groups[$group["ID"]] = $group["NAME"];
			}
		}

		return $groups;
	}

	public static function GetRestrictions($arParams, $boolKeys = true, $boolRevert = true)
	{
		$boolKeys = !!$boolKeys;
		$boolRevert = !!$boolRevert;
		if (!is_array($arParams) || empty($arParams))
			return array();
		$arFilter = array('RESTRICTIONS' => true);
		if (isset($arParams['USER_GROUPS']) && !empty($arParams['USER_GROUPS']))
			$arFilter['USER_GROUP_ID'] = $arParams['USER_GROUPS'];

		if (isset($arParams['PRICE_TYPES']) && !empty($arParams['PRICE_TYPES']))
			$arFilter['PRICE_TYPE_ID'] = $arParams['PRICE_TYPES'];

		if ($boolKeys)
		{
			return CCatalogDiscount::__GetDiscountID($arFilter);
		}
		else
		{
			$arResult = CCatalogDiscount::__GetDiscountID($arFilter);
			if (!empty($arResult) && !empty($arResult['RESTRICTIONS']))
			{
				if ($boolRevert)
				{
					foreach ($arResult['RESTRICTIONS'] as &$arOneDiscount)
					{
						$arOneDiscount['USER_GROUP'] = array_keys($arOneDiscount['USER_GROUP']);
						$arOneDiscount['PRICE_TYPE'] = array_keys($arOneDiscount['PRICE_TYPE']);
					}
					unset($arOneDiscount);
				}
			}
			return $arResult;
		}
	}

	public static function CheckDiscount($arProduct, $arDiscount)
	{
		if (empty($arProduct) || !is_array($arProduct))
			return false;
		if (empty($arDiscount) || !is_array($arDiscount) || !isset($arDiscount['UNPACK']))
			return false;
		return self::__Unpack($arProduct, $arDiscount['UNPACK']);
	}

	public static function applyDiscountList($price, $currency, &$discountList)
	{
		$price = (float)$price;
		$currency = CCurrency::checkCurrencyID($currency);
		if ($currency === false || !is_array($discountList))
			return false;

		if (self::$useSaleDiscount === null)
			self::initDiscountSettings();

		$currentPrice = $price;
		$applyDiscountList = array();

		$result = array(
			'PRICE' => $price,
			'CURRENCY' => $currency,
			'DISCOUNT_LIST' => array()
		);
		if ($price <= 0 || empty($discountList))
			return $result;

		$accumulativeDiscountMode = (string)Option::get('catalog', 'discsave_apply');
		$productDiscountList = array();
		$accumulativeDiscountList = array();

		self::primaryDiscountFilter(
			$price,
			$currency,
			$discountList,
			$productDiscountList,
			$accumulativeDiscountList
		);
		if (!empty($productDiscountList))
		{
			if (self::$useSaleDiscount && Loader::includeModule('sale'))
			{
				Catalog\Product\Price\Calculation::pushConfig();
				Catalog\Product\Price\Calculation::setConfig([
					'PRECISION' => (int)Main\Config\Option::get('sale', 'value_precision')
				]);
				$applyDiscountList = array();
				foreach ($productDiscountList as $priority => $discounts)
				{
					foreach ($discounts as $discount)
					{
						$currentPrice = self::calculatePriceByDiscount($price, $currentPrice, $discount, $needErase);
						if ($currentPrice !== false)
						{
							$applyDiscountList[] = $discount;
							if($discount['LAST_DISCOUNT'] === 'Y')
							{
								break 2;
							}
							if($discount['LAST_LEVEL_DISCOUNT'] === 'Y')
							{
								break;
							}
						}
					}
				}
				Catalog\Product\Price\Calculation::popConfig();
			}
			else
			{
				foreach ($productDiscountList as &$priority)
				{
					$currentPrice = self::calculatePriorityLevel($price, $currentPrice, $currency, $priority, $applyDiscountList);
					if ($currentPrice === false)
						return false;
					if (!empty($applyDiscountList))
					{
						$lastDiscount = end($applyDiscountList);
						reset($applyDiscountList);
						if (isset($lastDiscount['LAST_DISCOUNT']) && $lastDiscount['LAST_DISCOUNT'] == 'Y')
							break;
					}
				}
				unset($priority);
			}
		}

		if (!empty($accumulativeDiscountList))
		{
			switch ($accumulativeDiscountMode)
			{
				case CCatalogDiscountSave::APPLY_MODE_REPLACE:
					$applyAccumulativeList = array();
					$accumulativePrice = self::calculateDiscSave(
						$price,
						$price,
						$currency,
						$accumulativeDiscountList,
						$applyAccumulativeList
					);
					if ($accumulativePrice === false)
						return false;
					if (!empty($applyAccumulativeList) && $accumulativePrice < $currentPrice)
					{
						$currentPrice = $accumulativePrice;
						$applyDiscountList = $applyAccumulativeList;
					}
					break;
				case CCatalogDiscountSave::APPLY_MODE_ADD:
					$currentPrice = self::calculateDiscSave(
						$price,
						$currentPrice,
						$currency,
						$accumulativeDiscountList,
						$applyDiscountList
					);
					if ($currentPrice === false)
						return false;
					break;
				case CCatalogDiscountSave::APPLY_MODE_DISABLE:
					if (empty($applyDiscountList))
					{
						$currentPrice = self::calculateDiscSave(
							$price,
							$currentPrice,
							$currency,
							$accumulativeDiscountList,
							$applyDiscountList
						);
						if ($currentPrice === false)
							return false;
					}
					break;
			}
		}

		$result = array(
			'PRICE' => $currentPrice,
			'CURRENCY' => $currency,
			'DISCOUNT_LIST' => $applyDiscountList
		);
		return $result;
	}

	public static function calculateDiscountList($priceData, $currency, &$discountList, $getWithVat = true)
	{
		$getWithVat = ($getWithVat !== false);
		$result = array();
		if (empty($priceData) || !is_array($priceData))
			return $result;
		$priceData['PRICE'] = (float)$priceData['PRICE'];
		$priceData['CURRENCY'] = CCurrency::checkCurrencyID($priceData['CURRENCY']);
		$currency = CCurrency::checkCurrencyID($currency);
		if ($priceData['CURRENCY'] === false || $currency === false || !is_array($discountList))
			return $result;

		//$discountVat = ((string)Option::get('catalog', 'discount_vat') != 'N');
		$discountVat = true;

		$currentPrice = (
			$priceData['CURRENCY'] == $currency
			? $priceData['PRICE']
			: CCurrencyRates::ConvertCurrency($priceData['PRICE'], $priceData['CURRENCY'], $currency)
		);
		$priceData['ORIG_VAT_INCLUDED'] = $priceData['VAT_INCLUDED'];
		if ($discountVat)
		{
			if ($priceData['VAT_INCLUDED'] == 'N')
			{
				$currentPrice *= (1 + $priceData['VAT_RATE']);
				$priceData['VAT_INCLUDED'] = 'Y';
			}
		}
		else
		{
			if ($priceData['VAT_INCLUDED'] == 'Y')
			{
				$currentPrice /= (1 + $priceData['VAT_RATE']);
				$priceData['VAT_INCLUDED'] = 'N';
			}
		}
		$currentPrice = Price\Calculation::roundPrecision($currentPrice);
		$calculatePrice = $currentPrice;
		foreach ($discountList as $discount)
		{
			switch ($discount['VALUE_TYPE'])
			{
				case self::TYPE_FIX:
					if ($discount['CURRENCY'] == $currency)
						$currentDiscount = $discount['VALUE'];
					else
						$currentDiscount = CCurrencyRates::ConvertCurrency($discount['VALUE'], $discount['CURRENCY'], $currency);
					$currentDiscount = Price\Calculation::roundPrecision($currentDiscount);
					$currentPrice = $currentPrice - $currentDiscount;
					break;
				case self::TYPE_PERCENT:
					$currentDiscount = $currentPrice*$discount['VALUE']/100.0;
					if ($discount['MAX_DISCOUNT'] > 0)
					{
						if ($discount['CURRENCY'] == $currency)
							$maxDiscount = $discount['MAX_DISCOUNT'];
						else
							$maxDiscount = CCurrencyRates::ConvertCurrency($discount['MAX_DISCOUNT'], $discount['CURRENCY'], $currency);
						if ($currentDiscount > $maxDiscount)
							$currentDiscount = $maxDiscount;
					}
					$currentDiscount = Price\Calculation::roundPrecision($currentDiscount);
					$currentPrice = $currentPrice - $currentDiscount;
					break;
				case self::TYPE_SALE:
					if ($discount['CURRENCY'] == $currency)
						$currentPrice = $discount['VALUE'];
					else
						$currentPrice = CCurrencyRates::ConvertCurrency($discount['VALUE'], $discount['CURRENCY'], $currency);
					$currentPrice = Price\Calculation::roundPrecision($currentPrice);
					break;
			}
		}
		unset($discount);

		$vatRate = (1 + $priceData['VAT_RATE']);
		if ($discountVat)
		{
			if (!$getWithVat)
			{
				$calculatePrice /= $vatRate;
				$currentPrice /= $vatRate;
			}
		}
		else
		{
			if ($getWithVat)
			{
				$calculatePrice *= $vatRate;
				$currentPrice *= $vatRate;
			}
		}
		unset($vatRate);
		unset($priceData['ORIG_VAT_INCLUDED']);
		$unroundBasePrice = $calculatePrice;
		$unroundPrice = $currentPrice;
		if (Catalog\Product\Price\Calculation::isComponentResultMode())
		{
			$calculatePrice = Catalog\Product\Price::roundPrice(
				$priceData['CATALOG_GROUP_ID'],
				$calculatePrice,
				$currency
			);
			$currentPrice = Catalog\Product\Price::roundPrice(
				$priceData['CATALOG_GROUP_ID'],
				$currentPrice,
				$currency
			);
			if (
				empty($discountList)
				|| Catalog\Product\Price\Calculation::compare($result['BASE_PRICE'], $result['PRICE'], '<=')
			)
			{
				$result['BASE_PRICE'] = $result['PRICE'];
			}
		}
		$currentDiscount = ($calculatePrice - $currentPrice);

		$result = array(
			'PRICE_TYPE_ID' => $priceData['CATALOG_GROUP_ID'],
			'BASE_PRICE' => $calculatePrice,
			'DISCOUNT_PRICE' => $currentPrice,
			'UNROUND_BASE_PRICE' => $unroundBasePrice,
			'UNROUND_DISCOUNT_PRICE' => $unroundPrice,
			'CURRENCY' => $currency,
			'DISCOUNT' => $currentDiscount,
			'PERCENT' => (
				$calculatePrice > 0 && $currentDiscount > 0
				? round((100*$currentDiscount)/$calculatePrice, 0)
				: 0
			),
			'VAT_RATE' => $priceData['VAT_RATE'],
			'VAT_INCLUDED' => ($getWithVat ? 'Y' : 'N')
		);
		return $result;
	}

	public static function getDiscountDescription(array $discount)
	{
		$result = array(
			'ID' => $discount['ID'],
			'NAME' => $discount['NAME'],
			'COUPON' => '',
			'COUPON_TYPE' => '',
			'USE_COUPONS' => (isset($discount['USE_COUPONS']) ? $discount['USE_COUPONS'] : 'N'),
			'MODULE_ID' => (isset($discount['MODULE_ID']) ? $discount['MODULE_ID'] : 'catalog'),
			'TYPE' => $discount['TYPE'],
			'VALUE' => $discount['VALUE'],
			'VALUE_TYPE' => $discount['VALUE_TYPE'],
			'LAST_DISCOUNT' => $discount['LAST_DISCOUNT'],
			'MAX_VALUE' => (
				$discount['VALUE_TYPE'] == Catalog\DiscountTable::VALUE_TYPE_PERCENT
				? $discount['MAX_DISCOUNT']
				: 0
			),
			'CURRENCY' => $discount['CURRENCY'],
			'HANDLERS' => (isset($discount['HANDLERS']) ? $discount['HANDLERS'] : array())
		);
		if (!empty($discount['COUPON']))
		{
			$result['USE_COUPONS'] = 'Y';
			$result['COUPON'] = $discount['COUPON'];
			$result['COUPON_TYPE'] = $discount['COUPON_ONE_TIME'];
		}
		return $result;
	}

	public static function ExtendBasketItems(&$arBasket, $arExtend)
	{
		$arFields = array(
			'ID',
			'IBLOCK_ID',
			'CODE',
			'XML_ID',
			'NAME',
			'DATE_ACTIVE_FROM',
			'DATE_ACTIVE_TO',
			'SORT',
			'PREVIEW_TEXT',
			'DETAIL_TEXT',
			'DATE_CREATE',
			'CREATED_BY',
			'TIMESTAMP_X',
			'MODIFIED_BY',
			'TAGS',
			'TIMESTAMP_X_UNIX',
			'DATE_CREATE_UNIX'
		);
		$arCatFields = array(
			'ID',
			'TYPE',
			'QUANTITY',
			'WEIGHT',
			'VAT_ID',
			'VAT_INCLUDED',
		);

		$boolFields = false;
		if (isset($arExtend['catalog']['fields']))
			$boolFields = (boolean)$arExtend['catalog']['fields'];
		$boolProps = false;
		if (isset($arExtend['catalog']['props']))
			$boolProps = (boolean)$arExtend['catalog']['props'];

		$boolPrice = !empty($arExtend['catalog']['price']);
		$productPriceIds = array();
		$basketItemIds = array();

		if ($boolFields || $boolProps || $boolPrice)
		{
			$arMap = array();
			$arIDS = array();
			foreach ($arBasket as $strKey => $arOneRow)
			{
				if (isset($arOneRow['MODULE']) && 'catalog' == $arOneRow['MODULE'])
				{
					$intProductID = (int)$arOneRow['PRODUCT_ID'];
					if ($intProductID > 0)
					{
						$arIDS[$intProductID] = true;
						if (!isset($arMap[$intProductID]))
							$arMap[$intProductID] = array();
						$arMap[$intProductID][] = $strKey;
					}

					if($boolPrice)
					{
						if(isset($arOneRow['PRODUCT_PRICE_ID']))
						{
							$productPriceIds[] = $arOneRow['PRODUCT_PRICE_ID'];
						}
						else
						{
							$basketItemIds[] = $arOneRow['ID'];
						}
					}
				}
			}

			if($boolPrice && empty($productPriceIds) && $basketItemIds)
			{
				//we have old basket.basket component without PRODUCT_PRICE_ID field in basket item
				$basketFilter = array(
					'filter' => array(
						'@ID' => $basketItemIds
					),
					'select' => array('ID', 'PRODUCT_PRICE_ID',),
				);
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Basket $basketClass */
				$basketClass = $registry->getBasketClassName();

				$res = $basketClass::getList($basketFilter);
				while($basketItem = $res->fetch())
				{
					$productPriceIds[] = $basketItem['PRODUCT_PRICE_ID'];
				}
			}

			if (!empty($arIDS))
			{
				$offerIds = array();
				$arBasketResult = array();
				$iblockGroup = array();
				$arIDS = array_keys($arIDS);
				self::SetProductSectionsCache($arIDS);
				CTimeZone::Disable();
				$rsItems = CIBlockElement::GetList(array(), array('ID' => $arIDS), false, false, $arFields);
				while ($arItem = $rsItems->Fetch())
				{
					$arBasketData = array();
					$arItem['ID'] = (int)$arItem['ID'];
					$arItem['IBLOCK_ID'] = (int)$arItem['IBLOCK_ID'];
					if (!isset($iblockGroup[$arItem['IBLOCK_ID']]))
						$iblockGroup[$arItem['IBLOCK_ID']] = array();
					$iblockGroup[$arItem['IBLOCK_ID']][] = $arItem['ID'];
					if ($boolFields)
					{
						$arBasketData['ID'] = $arItem['ID'];
						$arBasketData['IBLOCK_ID'] = $arItem['IBLOCK_ID'];
						$arBasketData['NAME'] = $arItem['NAME'];
						$arBasketData['XML_ID'] = (string)$arItem['XML_ID'];
						$arBasketData['CODE'] = (string)$arItem['CODE'];
						$arBasketData['TAGS'] = (string)$arItem['TAGS'];
						$arBasketData['SORT'] = (int)$arItem['SORT'];
						$arBasketData['PREVIEW_TEXT'] = (string)$arItem['PREVIEW_TEXT'];
						$arBasketData['DETAIL_TEXT'] = (string)$arItem['DETAIL_TEXT'];
						$arBasketData['CREATED_BY'] = (int)$arItem['CREATED_BY'];
						$arBasketData['MODIFIED_BY'] = (int)$arItem['MODIFIED_BY'];

						$arBasketData['DATE_ACTIVE_FROM'] = (string)$arItem['DATE_ACTIVE_FROM'];
						if (!empty($arBasketData['DATE_ACTIVE_FROM']))
							$arBasketData['DATE_ACTIVE_FROM'] = (int)MakeTimeStamp($arBasketData['DATE_ACTIVE_FROM']);

						$arBasketData['DATE_ACTIVE_TO'] = (string)$arItem['DATE_ACTIVE_TO'];
						if (!empty($arBasketData['DATE_ACTIVE_TO']))
							$arBasketData['DATE_ACTIVE_TO'] = (int)MakeTimeStamp($arBasketData['DATE_ACTIVE_TO']);

						if (isset($arItem['DATE_CREATE_UNIX']))
						{
							$arBasketData['DATE_CREATE'] = (string)$arItem['DATE_CREATE_UNIX'];
							if ($arBasketData['DATE_CREATE'] != '')
								$arBasketData['DATE_CREATE'] = (int)$arBasketData['DATE_CREATE'];
						}
						else
						{
							$arBasketData['DATE_CREATE'] = (string)$arItem['DATE_CREATE'];
							if ($arBasketData['DATE_CREATE'] != '')
								$arBasketData['DATE_CREATE'] = (int)MakeTimeStamp($arBasketData['DATE_CREATE']);
						}

						if (isset($arItem['TIMESTAMP_X_UNIX']))
						{
							$arBasketData['TIMESTAMP_X'] = (string)$arItem['TIMESTAMP_X_UNIX'];
							if ($arBasketData['TIMESTAMP_X'] != '')
								$arBasketData['TIMESTAMP_X'] = (int)$arBasketData['TIMESTAMP_X'];
						}
						else
						{
							$arBasketData['TIMESTAMP_X'] = (string)$arItem['TIMESTAMP_X'];
							if ($arBasketData['TIMESTAMP_X'] != '')
								$arBasketData['TIMESTAMP_X'] = (int)MakeTimeStamp($arBasketData['TIMESTAMP_X']);
						}

						$arProductSections = self::__GetSectionList($arItem['IBLOCK_ID'], $arItem['ID']);
						if ($arProductSections !== false)
							$arBasketData['SECTION_ID'] = $arProductSections;
						else
							$arBasketData['SECTION_ID'] = array();
					}
					if ($boolProps)
					{
						$arBasketData['PROPERTIES'] = array();
					}
					$arBasketResult[$arItem['ID']] = $arBasketData;
				}
				$propertyFilter = array();
				if (isset($arExtend['iblock']['props']) && is_array($arExtend['iblock']['props']))
					$propertyFilter = array('ID' => $arExtend['iblock']['props']);
				if (!empty($iblockGroup))
				{
					$catalogIterator = Catalog\CatalogIblockTable::getList(array(
						'select' => array('IBLOCK_ID', 'SKU_PROPERTY_ID', 'PRODUCT_IBLOCK_ID'),
						'filter' => array('@IBLOCK_ID' => array_keys($iblockGroup))
					));
					while ($catalog = $catalogIterator->fetch())
					{
						$skuPropertyId = (int)$catalog['SKU_PROPERTY_ID'];
						if ($skuPropertyId > 0)
						{
							if (!isset($propertyFilter['ID']))
								$propertyFilter['ID'] = array();
							$propertyFilter['ID'][] = $skuPropertyId;
							if (!empty($iblockGroup[$catalog['IBLOCK_ID']]))
							{
								foreach ($iblockGroup[$catalog['IBLOCK_ID']] as $itemId)
								{
									if (!isset($arBasketResult[$itemId]['PROPERTIES']))
										$arBasketResult[$itemId]['PROPERTIES'] = array();
								}
								unset($itemId);
							}
							$boolProps = true;
						}
					}
					unset($catalog, $catalogIterator);
				}
				CTimeZone::Enable();
				if ($boolProps && !empty($iblockGroup))
				{
					foreach ($iblockGroup as $iblockID => $iblockItems)
					{
						$filter = array(
							'ID' => $iblockItems,
							'IBLOCK_ID' =>$iblockID
						);
						CIBlockElement::GetPropertyValuesArray(
							$arBasketResult,
							$iblockID,
							$filter,
							$propertyFilter,
							array(
								'ID' => true,
								'PROPERTY_TYPE' => true,
								'MULTIPLE' => true,
								'USER_TYPE' => true,
							)
						);
					}
					unset($iblockItems, $iblockID);
					foreach ($arBasketResult as &$basketItem)
					{
						self::__ConvertProperties($basketItem, $basketItem['PROPERTIES'], array('TIME_ZONE' => 'N'));
					}
					unset($basketItem);
				}

				if($boolPrice)
				{
					$priceList = Catalog\PriceTable::getList(array(
						'select' => array(
							'PRODUCT_ID',
							'CATALOG_GROUP_ID',
						),
						'filter' => array('@ID' => $productPriceIds),
					));
					while($price = $priceList->fetch())
					{
						if(!isset($arBasketResult[$price['PRODUCT_ID']]))
						{
							$arBasketResult[$price['PRODUCT_ID']] = array();
						}
						$arBasketResult[$price['PRODUCT_ID']]['CATALOG_GROUP_ID'] = $price['CATALOG_GROUP_ID'];
					}

				}

				$rsProducts = CCatalogProduct::GetList(array(), array('@ID' => $arIDS), false, false, $arCatFields);
				while ($arProduct = $rsProducts->Fetch())
				{
					$productId = (int)$arProduct['ID'];
					$arProduct['TYPE'] = (int)$arProduct['TYPE'];
					if ($arProduct['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
						$offerIds[$productId] = $productId;
					if (!isset($arBasketResult[$productId]))
						$arBasketResult[$productId] = array();
					unset($arProduct['ID'], $arProduct['TYPE']);

					foreach ($arProduct as $productKey => $productValue)
						$arBasketResult[$productId]['CATALOG_'.$productKey] = $productValue;
					unset($productKey, $productValue);
				}
				unset($productId, $arProduct, $rsProducts);

				if (!empty($offerIds))
				{
					$products = array();
					$productIds = array();
					$productList = CCatalogSku::getProductList($offerIds);
					if (!empty($productList))
					{
						foreach (array_keys($productList) as $index)
						{
							$id = $productList[$index]['ID'];
							$iblockId = $productList[$index]['IBLOCK_ID'];
							if (!isset($products[$iblockId]))
							{
								$products[$iblockId] = array();
								$productIds[$iblockId] = array();
							}
							$products[$iblockId][$id] = array();
							$productIds[$iblockId][] = $id;
						}
						unset($iblockId, $id, $index);
					}
					unset($productList);
					if (!empty($products))
					{
						self::initDiscountSettings();
						$stackData = self::$useSaleDiscount;
						self::$useSaleDiscount = false;
						foreach (array_keys($products) as $iblockId)
						{
							if (!empty($propertyFilter))
							{
								$arPropFilter = array(
									'ID' => $productIds[$iblockId],
									'IBLOCK_ID' => $iblockId
								);
								CIBlockElement::GetPropertyValuesArray(
									$products[$iblockId],
									$iblockId,
									$arPropFilter,
									$propertyFilter,
									array(
										'ID' => true,
										'PROPERTY_TYPE' => true,
										'MULTIPLE' => true,
										'USER_TYPE' => true,
									)
								);
							}

							foreach (array_keys($products[$iblockId]) as $id)
								CCatalogDiscount::SetProductPropertiesCache($id, $products[$iblockId][$id]);
							unset($id);

							CCatalogDiscount::SetProductSectionsCache($productIds[$iblockId]);
							CCatalogDiscount::SetDiscountProductCache($productIds[$iblockId], array('IBLOCK_ID' => $iblockId, 'GET_BY_ID' => 'Y'));
						}
						self::$useSaleDiscount = $stackData;
					}
				}

				if (!empty($iblockGroup))
				{
					foreach ($iblockGroup as $iblockID => $iblockItems)
					{
						$sku = CCatalogSku::GetInfoByOfferIBlock($iblockID);
						if (!empty($sku))
						{
							foreach ($iblockItems as $itemID)
							{
								$isSku = self::__GenerateParent($arBasketResult[$itemID], $sku);
							}
							unset($isSku, $itemID);
						}
					}
					unset($sku, $iblockItems, $iblockID);
				}

				if (!empty($arBasketResult))
				{
					foreach ($arBasketResult as $intProductID => $arBasketData)
					{
						foreach ($arMap[$intProductID] as $mxRowID)
						{
							$arBasket[$mxRowID]['CATALOG'] = $arBasketData;
						}
					}
				}
				CCatalogDiscount::ClearDiscountCache(array(
					'PRODUCT' => true,
					'SECTIONS' => true,
					'PROPERTIES' => true
				));
			}
		}
	}

	/**
	 * @param array $arProduct
	 * @param bool|array $arParams
	 * @return bool
	 */
	protected static function __GenerateFields(&$arProduct, $arParams = false)
	{
		$boolResult = false;
		if (!empty($arProduct) && is_array($arProduct))
		{
			if (!isset($arProduct['IBLOCK_ID']))
				$arProduct['IBLOCK_ID'] = 0;
			$arProduct['IBLOCK_ID'] = (int)$arProduct['IBLOCK_ID'];
			if ($arProduct['IBLOCK_ID'] > 0)
			{
				if (!is_array($arParams))
					$arParams = array();

				if (!isset($arProduct['ID']))
					$arProduct['ID'] = 0;
				$arProduct['ID'] = (int)$arProduct['ID'];
				if ($arProduct['ID'] > 0)
				{
					if (isset($arParams['PRODUCT']) && $arParams['PRODUCT'] == 'Y')
					{
						$arDefaultProduct = array(
							'DATE_ACTIVE_FROM' => '',
							'DATE_ACTIVE_TO' => '',
							'SORT' => 0,
							'PREVIEW_TEXT' => '',
							'DETAIL_TEXT' => '',
							'TAGS' => '',
							'DATE_CREATE' => '',
							'TIMESTAMP_X' => '',
							'CREATED_BY' => 0,
							'MODIFIED_BY' => 0,
							'CATALOG_QUANTITY' => '',
							'CATALOG_WEIGHT' => '',
							'CATALOG_VAT_ID' => '',
							'CATALOG_VAT_INCLUDED' => ''
						);
						$arProduct = array_merge($arDefaultProduct, $arProduct);

						static $intTimeOffset = false;
						if (false === $intTimeOffset)
							$intTimeOffset = CTimeZone::GetOffset();
						if (isset($arParams['TIME_ZONE']) && 'N' == $arParams['TIME_ZONE'])
							$intTimeOffset = 0;

						if (!isset($arProduct['SECTION_ID']))
						{
							$arProductSections = self::__GetSectionList($arProduct['IBLOCK_ID'], $arProduct['ID']);
							if (false !== $arProductSections)
								$arProduct['SECTION_ID'] = $arProductSections;
							else
								$arProduct['SECTION_ID'] = array();
						}
						else
						{
							if (!is_array($arProduct['SECTION_ID']))
								$arProduct['SECTION_ID'] = array($arProduct['SECTION_ID']);
							Main\Type\Collection::normalizeArrayValuesByInt($arProduct['SECTION_ID']);
						}

						if (!empty($arProduct['DATE_ACTIVE_FROM']))
						{
							$intStackTimestamp = (int)$arProduct['DATE_ACTIVE_FROM'];
							if ($intStackTimestamp.'!' != $arProduct['DATE_ACTIVE_FROM'].'!')
								$arProduct['DATE_ACTIVE_FROM'] = (int)MakeTimeStamp($arProduct['DATE_ACTIVE_FROM']) - $intTimeOffset;
							else
								$arProduct['DATE_ACTIVE_FROM'] = $intStackTimestamp;
						}

						if (!empty($arProduct['DATE_ACTIVE_TO']))
						{
							$intStackTimestamp = (int)$arProduct['DATE_ACTIVE_TO'];
							if ($intStackTimestamp.'!' != $arProduct['DATE_ACTIVE_TO'].'!')
								$arProduct['DATE_ACTIVE_TO'] = (int)MakeTimeStamp($arProduct['DATE_ACTIVE_TO']) - $intTimeOffset;
							else
								$arProduct['DATE_ACTIVE_TO'] = $intStackTimestamp;
						}

						$arProduct['SORT'] = (int)$arProduct['SORT'];

						if (!empty($arProduct['DATE_CREATE']))
						{
							$intStackTimestamp = (int)$arProduct['DATE_CREATE'];
							if ($intStackTimestamp.'!' != $arProduct['DATE_CREATE'].'!')
								$arProduct['DATE_CREATE'] = (int)MakeTimeStamp($arProduct['DATE_CREATE']) - $intTimeOffset;
							else
								$arProduct['DATE_CREATE'] = $intStackTimestamp;
						}

						if (!empty($arProduct['TIMESTAMP_X']))
						{
							$intStackTimestamp = (int)$arProduct['TIMESTAMP_X'];
							if ($intStackTimestamp.'!' != $arProduct['TIMESTAMP_X'].'!')
								$arProduct['TIMESTAMP_X'] = (int)MakeTimeStamp($arProduct['TIMESTAMP_X']) - $intTimeOffset;
							else
								$arProduct['TIMESTAMP_X'] = $intStackTimestamp;
						}

						$arProduct['CREATED_BY'] = (int)$arProduct['CREATED_BY'];
						$arProduct['MODIFIED_BY'] = (int)$arProduct['MODIFIED_BY'];

						if (isset($arProduct['QUANTITY']))
						{
							$arProduct['CATALOG_QUANTITY'] = $arProduct['QUANTITY'];
							unset($arProduct['QUANTITY']);
						}
						if ('' != $arProduct['CATALOG_QUANTITY'])
							$arProduct['CATALOG_QUANTITY'] = doubleval($arProduct['CATALOG_QUANTITY']);

						if (isset($arProduct['WEIGHT']))
						{
							$arProduct['CATALOG_WEIGHT'] = $arProduct['WEIGHT'];
							unset($arProduct['WEIGHT']);
						}
						if ('' != $arProduct['CATALOG_WEIGHT'])
						$arProduct['CATALOG_WEIGHT'] = doubleval($arProduct['CATALOG_WEIGHT']);

						if (isset($arProduct['VAT_ID']))
						{
							$arProduct['CATALOG_VAT_ID'] = $arProduct['VAT_ID'];
							unset($arProduct['VAT_ID']);
						}
						if ('' != $arProduct['CATALOG_VAT_ID'])
							$arProduct['CATALOG_VAT_ID'] = (int)$arProduct['CATALOG_VAT_ID'];

						if (isset($arProduct['VAT_INCLUDED']))
						{
							$arProduct['CATALOG_VAT_INCLUDED'] = $arProduct['VAT_INCLUDED'];
							unset($arProduct['VAT_INCLUDED']);
						}

						$arPropParams = array();
						if (isset($arParams['TIME_ZONE']) && 'N' == $arParams['TIME_ZONE'])
							$arPropParams['TIME_ZONE'] = 'N';

						if (isset($arProduct['PROPERTIES']))
						{
							if (!empty($arProduct['PROPERTIES']) && is_array($arProduct['PROPERTIES']))
							{
								self::__ConvertProperties($arProduct, $arProduct['PROPERTIES'], $arPropParams);
							}
							unset($arProduct['PROPERTIES']);
						}
					}
					else
					{
						$arSelect = array('ID', 'IBLOCK_ID', 'CODE', 'XML_ID', 'NAME', 'ACTIVE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO',
							'SORT', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'DATE_CREATE', 'DATE_CREATE_UNIX', 'CREATED_BY', 'TIMESTAMP_X', 'TIMESTAMP_X_UNIX', 'MODIFIED_BY', 'TAGS', 'CATALOG_QUANTITY');
						CTimeZone::Disable();
						$rsProducts = CIBlockElement::GetList(array(), array('ID' => $arProduct['ID'], 'IBLOCK_ID' => $arProduct['IBLOCK_ID']), false, false, $arSelect);
						CTimeZone::Enable();
						if (!($obProduct = $rsProducts->GetNextElement(false,true)))
							return $boolResult;

						$arProduct = array();
						$arProductFields = $obProduct->GetFields();

						$arProduct['ID'] = (int)$arProductFields['ID'];
						$arProduct['IBLOCK_ID'] = (int)$arProductFields['IBLOCK_ID'];

						$arProduct['SECTION_ID'] = array();
						$arProductSections = self::__GetSectionList($arProduct['IBLOCK_ID'], $arProduct['ID']);
						if (false !== $arProductSections)
							$arProduct['SECTION_ID'] = $arProductSections;

						$arProduct['CODE'] = (string)$arProductFields['~CODE'];
						$arProduct['XML_ID'] = (string)$arProductFields['~XML_ID'];
						$arProduct['NAME'] = $arProductFields['~NAME'];

						$arProduct['ACTIVE'] = $arProductFields['ACTIVE'];

						$arProduct['DATE_ACTIVE_FROM'] = (string)$arProductFields['DATE_ACTIVE_FROM'];
						if (!empty($arProduct['DATE_ACTIVE_FROM']))
							$arProduct['DATE_ACTIVE_FROM'] = (int)MakeTimeStamp($arProduct['DATE_ACTIVE_FROM']);

						$arProduct['DATE_ACTIVE_TO'] = (string)$arProductFields['DATE_ACTIVE_TO'];
						if (!empty($arProduct['DATE_ACTIVE_TO']))
							$arProduct['DATE_ACTIVE_TO'] = (int)MakeTimeStamp($arProduct['DATE_ACTIVE_TO']);

						$arProduct['SORT'] = (int)$arProductFields['SORT'];

						$arProduct['PREVIEW_TEXT'] = (string)$arProductFields['~PREVIEW_TEXT'];
						$arProduct['DETAIL_TEXT'] = (string)$arProductFields['~DETAIL_TEXT'];
						$arProduct['TAGS'] = (string)$arProductFields['~TAGS'];

						if (isset($arProductFields['DATE_CREATE_UNIX']))
						{
							$arProduct['DATE_CREATE'] = (string)$arProductFields['DATE_CREATE_UNIX'];
							if ('' != $arProduct['DATE_CREATE'])
								$arProduct['DATE_CREATE'] = (int)$arProduct['DATE_CREATE'];
						}
						else
						{
							$arProduct['DATE_CREATE'] = (string)$arProductFields['DATE_CREATE'];
							if ('' != $arProduct['DATE_CREATE'])
								$arProduct['DATE_CREATE'] = (int)MakeTimeStamp($arProduct['DATE_CREATE']);
						}

						if (isset($arProductFields['TIMESTAMP_X_UNIX']))
						{
							$arProduct['TIMESTAMP_X'] = (string)$arProductFields['TIMESTAMP_X_UNIX'];
							if ('' != $arProduct['TIMESTAMP_X'])
								$arProduct['TIMESTAMP_X'] = (int)$arProduct['TIMESTAMP_X'];
						}
						else
						{
							$arProduct['TIMESTAMP_X'] = (string)$arProductFields['TIMESTAMP_X'];
							if ('' != $arProduct['TIMESTAMP_X'])
								$arProduct['TIMESTAMP_X'] = (int)MakeTimeStamp($arProduct['TIMESTAMP_X']);
						}

						$arProduct['CREATED_BY'] = (int)$arProductFields['CREATED_BY'];
						$arProduct['MODIFIED_BY'] = (int)$arProductFields['MODIFIED_BY'];

						$arProduct['CATALOG_QUANTITY'] = (string)$arProductFields['CATALOG_QUANTITY'];
						if ('' != $arProduct['CATALOG_QUANTITY'])
							$arProduct['CATALOG_QUANTITY'] = doubleval($arProduct['CATALOG_QUANTITY']);
						$arProduct['CATALOG_WEIGHT'] = (string)$arProductFields['CATALOG_WEIGHT'];
						if ('' != $arProduct['CATALOG_WEIGHT'])
							$arProduct['CATALOG_WEIGHT'] = doubleval($arProduct['CATALOG_WEIGHT']);

						$arProduct['CATALOG_VAT_ID'] = (string)$arProductFields['CATALOG_VAT_ID'];
						if ('' != $arProduct['CATALOG_VAT_ID'])
							$arProduct['CATALOG_VAT_ID'] = (int)$arProduct['CATALOG_VAT_ID'];

						$arProduct['CATALOG_VAT_INCLUDED'] = (string)$arProductFields['CATALOG_VAT_INCLUDED'];

						unset($arProductFields);
						if (!isset(self::$arCacheProductProperties[$arProduct['ID']]))
						{
							$arProps = $obProduct->GetProperties(array(), array('ACTIVE' => 'Y', 'EMPTY' => 'N'));
						}
						else
						{
							$arProps = self::$arCacheProductProperties[$arProduct['ID']];
						}
						self::__ConvertProperties($arProduct, $arProps, array('TIME_ZONE' => 'N'));
						if (isset(self::$arCacheProductProperties[$arProduct['ID']]))
							unset(self::$arCacheProductProperties[$arProduct['ID']]);
						if (isset(self::$arCacheProductSections[$arProduct['ID']]))
							unset(self::$arCacheProductSections[$arProduct['ID']]);
					}
				}
				else
				{
					$arProduct['ID'] = 0;
					if (!isset($arProduct['SECTION_ID']))
						$arProduct['SECTION_ID'] = array();
					if (!is_array($arProduct['SECTION_ID']))
						$arProduct['SECTION_ID'] = array($arProduct['SECTION_ID']);
					Main\Type\Collection::normalizeArrayValuesByInt($arProduct['SECTION_ID']);

					$arProduct['DATE_ACTIVE_FROM'] = '';
					$arProduct['DATE_ACTIVE_TO'] = '';
					$arProduct['SORT'] = 500;

					$arProduct['PREVIEW_TEXT'] = '';
					$arProduct['DETAIL_TEXT'] = '';
					$arProduct['TAGS'] = '';

					$arProduct['DATE_CREATE'] = '';
					$arProduct['TIMESTAMP_X'] = '';

					$arProduct['CREATED_BY'] = 0;
					$arProduct['MODIFIED_BY'] = 0;

					$arProduct['CATALOG_QUANTITY'] = '';
					$arProduct['CATALOG_WEIGHT'] = '';
					$arProduct['CATALOG_VAT_ID'] = '';
					$arProduct['CATALOG_VAT_INCLUDED'] = '';
				}
				$boolResult = true;
			}
		}
		return $boolResult;
	}

	protected static function __GetSectionList($intIBlockID, $intProductID)
	{
		$mxResult = false;
		$intIBlockID = (int)$intIBlockID;
		$intProductID = (int)$intProductID;
		if ($intIBlockID > 0 && $intProductID > 0)
		{
			$mxResult = array();
			$arProductSections = array();
			if (!isset(self::$arCacheProductSections[$intProductID]))
			{
				$rsSections = CIBlockElement::GetElementGroups($intProductID, true, array("ID", "IBLOCK_SECTION_ID", "IBLOCK_ELEMENT_ID"));
				while ($arSection = $rsSections->Fetch())
				{
					$arSection['ID'] = (int)$arSection['ID'];
					$arSection['IBLOCK_SECTION_ID'] = (int)$arSection['IBLOCK_SECTION_ID'];
					$arProductSections[] = $arSection;
				}
				if (isset($arSection))
					unset($arSection);
				self::$arCacheProductSections[$intProductID] = $arProductSections;
			}
			else
			{
				$arProductSections = self::$arCacheProductSections[$intProductID];
			}
			if (!empty($arProductSections))
			{
				foreach ($arProductSections as &$arSection)
				{
					$mxResult[$arSection['ID']] = true;
					if (0 < $arSection['IBLOCK_SECTION_ID'])
					{
						if (!isset(self::$arCacheProductSectionChain[$arSection['ID']]))
						{
							self::$arCacheProductSectionChain[$arSection['ID']] = array();
							$rsParents = CIBlockSection::GetNavChain($intIBlockID, $arSection['ID'], array('ID'));
							while ($arParent = $rsParents->Fetch())
							{
								$arParent['ID'] = (int)$arParent['ID'];
								$mxResult[$arParent['ID']] = true;
								self::$arCacheProductSectionChain[$arSection['ID']][] = $arParent["ID"];
							}
						}
						else
						{
							foreach (self::$arCacheProductSectionChain[$arSection['ID']] as $intOneID)
							{
								$mxResult[$intOneID] = true;
							}
							if (isset($intOneID))
								unset($intOneID);
						}
					}
				}
				if (isset($arSection))
					unset($arSection);
			}
			if (!empty($mxResult))
			{
				$mxResult = array_keys($mxResult);
				sort($mxResult);
			}
		}
		return $mxResult;
	}

	/**
	 * @param array $arProduct
	 * @param array $arProps
	 * @param bool|array $arParams
	 */
	protected static function __ConvertProperties(&$arProduct, &$arProps, $arParams = false)
	{
		static $iblockProperties = array();

		if (empty($arProps) || !is_array($arProps))
			return;

		if (!is_array($arParams))
			$arParams = array();
		static $intTimeOffset = false;
		if (false === $intTimeOffset)
			$intTimeOffset = CTimeZone::GetOffset();
		if (isset($arParams['TIME_ZONE']) && 'N' == $arParams['TIME_ZONE'])
			$intTimeOffset = 0;

		if (!isset($iblockProperties[$arProduct['IBLOCK_ID']]))
		{
			$iblockProperties[$arProduct['IBLOCK_ID']] = array();
			$propertyIterator = Iblock\PropertyTable::getList(array(
				'select' => array('ID', 'IBLOCK_ID', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'USER_TYPE', 'USER_TYPE_SETTINGS'),
				'filter' => array('=IBLOCK_ID' => $arProduct['IBLOCK_ID'], '=ACTIVE' => 'Y', '!=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_FILE),
				'order' => array('ID' => 'ASC')
			));
			while ($property = $propertyIterator->fetch())
			{
				$id = (int)$property['ID'];
				$property['USER_TYPE'] = (string)$property['USER_TYPE'];
				$property['USER_TYPE_SETTINGS'] = (string)$property['USER_TYPE_SETTINGS'];
				if ($property['USER_TYPE'] != '')
				{
					$property['USER_TYPE_SETTINGS'] = (
						CheckSerializedData($property['USER_TYPE_SETTINGS'])
						? unserialize($property['USER_TYPE_SETTINGS'])
						: array()
					);
				}
				switch ($property['PROPERTY_TYPE'])
				{
					case Iblock\PropertyTable::TYPE_LIST:
					case Iblock\PropertyTable::TYPE_ELEMENT:
					case Iblock\PropertyTable::TYPE_SECTION:
						$property['EMPTY_VALUE'] = array(-1);
						break;
					default:
						$property['EMPTY_VALUE'] = array('');
						break;
				}
				$iblockProperties[$arProduct['IBLOCK_ID']][$id] = $property;
			}
			unset($property, $propertyIterator);
		}

		$propertyList = $iblockProperties[$arProduct['IBLOCK_ID']];
		foreach ($arProps as &$arOneProp)
		{
			if ($arOneProp['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_FILE)
				continue;
			$propertyId = (int)$arOneProp['ID'];
			if (isset($propertyList[$propertyId]))
				unset($propertyList[$propertyId]);
			unset($propertyId);
			$boolCheck = false;
			if ('N' == $arOneProp['MULTIPLE'])
			{
				if (isset($arOneProp['USER_TYPE']) && !empty($arOneProp['USER_TYPE']))
				{
					switch($arOneProp['USER_TYPE'])
					{
						case 'DateTime':
						case 'Date':
							$arOneProp['VALUE'] = (string)$arOneProp['VALUE'];
							if ('' != $arOneProp['VALUE'])
							{
								$propertyFormat = false;
								if ($arOneProp['USER_TYPE'] == 'DateTime')
								{
									if (defined('FORMAT_DATETIME'))
										$propertyFormat = FORMAT_DATETIME;
								}
								else
								{
									if (defined('FORMAT_DATE'))
										$propertyFormat = FORMAT_DATE;
								}
								$intStackTimestamp = (int)$arOneProp['VALUE'];
								if ($intStackTimestamp.'!' != $arOneProp['VALUE'].'!')
									$arOneProp['VALUE'] = (int)MakeTimeStamp($arOneProp['VALUE'], $propertyFormat) - $intTimeOffset;
								else
									$arOneProp['VALUE'] = $intStackTimestamp;
							}
							$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arOneProp['VALUE'];
							$boolCheck = true;
							break;
					}
				}
				if (!$boolCheck)
				{
					if ('L' == $arOneProp['PROPERTY_TYPE'])
					{
						$arOneProp['VALUE_ENUM_ID'] = (int)$arOneProp['VALUE_ENUM_ID'];
						if (0 < $arOneProp['VALUE_ENUM_ID'])
							$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arOneProp['VALUE_ENUM_ID'];
						else
							$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = -1;
					}
					elseif ('E' == $arOneProp['PROPERTY_TYPE'] || 'G' == $arOneProp['PROPERTY_TYPE'])
					{
						$arOneProp['VALUE'] = (int)$arOneProp['VALUE'];
						if (0 < $arOneProp['VALUE'])
							$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arOneProp['VALUE'];
						else
							$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = -1;
					}
					else
					{
						$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arOneProp['VALUE'];
					}
				}
			}
			else
			{
				if (isset($arOneProp['USER_TYPE']) && !empty($arOneProp['USER_TYPE']))
				{
					switch($arOneProp['USER_TYPE'])
					{
						case 'DateTime':
						case 'Date':
							$arValues = array();
							if (is_array($arOneProp['VALUE']) && !empty($arOneProp['VALUE']))
							{
								$propertyFormat = false;
								if ($arOneProp['USER_TYPE'] == 'DateTime')
								{
									if (defined('FORMAT_DATETIME'))
										$propertyFormat = FORMAT_DATETIME;
								}
								else
								{
									if (defined('FORMAT_DATE'))
										$propertyFormat = FORMAT_DATE;
								}
								foreach ($arOneProp['VALUE'] as &$strOneValue)
								{
									$strOneValue = (string)$strOneValue;
									if ('' != $strOneValue)
									{
										$intStackTimestamp = (int)$strOneValue;
										if ($intStackTimestamp.'!' != $strOneValue.'!')
											$strOneValue = (int)MakeTimeStamp($strOneValue, $propertyFormat) - $intTimeOffset;
										else
											$strOneValue = $intStackTimestamp;
									}
									$arValues[] = $strOneValue;
								}
								if (isset($strOneValue))
									unset($strOneValue);
							}
							$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arValues;
							$boolCheck = true;
							break;
					}
				}
				if (!$boolCheck)
				{
					if ('L' == $arOneProp['PROPERTY_TYPE'])
					{
						$arValues = array();
						if (is_array($arOneProp['VALUE_ENUM_ID']) && !empty($arOneProp['VALUE_ENUM_ID']))
						{
							foreach ($arOneProp['VALUE_ENUM_ID'] as &$intOneValue)
							{
								$intOneValue = (int)$intOneValue;
								if (0 < $intOneValue)
									$arValues[] = $intOneValue;
							}
							if (isset($intOneValue))
								unset($intOneValue);
						}
						if (empty($arValues))
							$arValues = array(-1);
						$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arValues;
					}
					elseif ('E' == $arOneProp['PROPERTY_TYPE'] || 'G' == $arOneProp['PROPERTY_TYPE'])
					{
						$arValues = array();
						if (is_array($arOneProp['VALUE']) && !empty($arOneProp['VALUE']))
						{
							foreach ($arOneProp['VALUE'] as &$intOneValue)
							{
								$intOneValue = (int)$intOneValue;
								if (0 < $intOneValue)
									$arValues[] = $intOneValue;
							}
							if (isset($intOneValue))
								unset($intOneValue);
						}
						if (empty($arValues))
							$arValues = array(-1);
						$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arValues;
					}
					else
					{
						$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = $arOneProp['VALUE'];
					}
				}
			}
			if (!is_array($arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE']))
				$arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE'] = array($arProduct['PROPERTY_'.$arOneProp['ID'].'_VALUE']);
		}
		unset($arOneProp);

		if (!empty($propertyList))
		{
			foreach ($propertyList as &$property)
				$arProduct['PROPERTY_'.$property['ID'].'_VALUE'] = $property['EMPTY_VALUE'];
			unset($property);
		}
		unset($propertyList);
	}

	protected static function __GenerateParent(&$product, $sku)
	{
		$parentID = 0;
		if (isset($product['PARENT_ID']))
			$parentID = (int)$product['PARENT_ID'];
		elseif (isset($product['PROPERTY_'.$sku['SKU_PROPERTY_ID'].'_VALUE']))
			$parentID = (int)current($product['PROPERTY_'.$sku['SKU_PROPERTY_ID'].'_VALUE']);
		if ($parentID <= 0)
			return false;
		if (!isset(self::$arCacheProduct[$parentID]))
		{
			$parent = array('ID' => $parentID, 'IBLOCK_ID' => $sku['PRODUCT_IBLOCK_ID']);
			if (!self::__GenerateFields($parent))
				return false;
			self::$arCacheProduct[$parentID] = $parent;
		}
		else
		{
			$parent = self::$arCacheProduct[$parentID];
		}
		foreach ($parent as $key => $value)
		{
			if ($key == 'SECTION_ID')
			{
				$product[$key] = array_merge($product[$key], $value);
			}
			elseif (strncmp($key, 'PROPERTY_', 9) == 0)
			{
				$product[$key] = $value;
			}
			elseif (strncmp($key, 'CATALOG_', 8) != 0)
			{
				$product['PARENT_'.$key] = $value;
			}
		}
		unset($value, $key, $parent);
		return true;
	}

	protected static function __ParseArrays(&$arFields)
	{
		global $APPLICATION;

		$arMsg = array();
		$boolResult = true;

		$arResult = array(
		);

		if (!self::__CheckOneEntity($arFields, 'GROUP_IDS'))
		{
			$arMsg[] = array('id' => 'GROUP_IDS', "text" => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_PARSE_USER_GROUP'));
			$boolResult = false;
		}
		if (!self::__CheckOneEntity($arFields, 'CATALOG_GROUP_IDS'))
		{
			$arMsg[] = array('id' => 'CATALOG_GROUP_IDS', "text" => Loc::getMessage('BT_MOD_CATALOG_DISC_ERR_PARSE_PRICE_TYPE'));
			$boolResult = false;
		}

		if ($boolResult)
		{
			$arTempo = array(
				'USER_GROUP_ID' => $arFields['GROUP_IDS'],
				'PRICE_TYPE_ID' => $arFields['CATALOG_GROUP_IDS'],
			);

			$arOrder = array(
				'USER_GROUP_ID',
				'PRICE_TYPE_ID',
			);

			self::__ArrayMultiple($arOrder, $arResult, $arTempo);
			unset($arTempo);
		}

		if (!$boolResult)
		{
			$obError = new CAdminException($arMsg);
			$APPLICATION->ResetException();
			$APPLICATION->ThrowException($obError);
			return $boolResult;
		}
		else
		{
			return $arResult;
		}
	}

	protected function __CheckOneEntity(&$arFields, $strEntityID)
	{
		$boolResult = false;
		$strEntityID = trim(strval($strEntityID));
		if (!empty($strEntityID))
		{
			if (is_array($arFields) && !empty($arFields))
			{
				if (is_set($arFields, $strEntityID))
				{
					if (!is_array($arFields[$strEntityID]))
						$arFields[$strEntityID] = array($arFields[$strEntityID]);
					$arValid = array();
					foreach ($arFields[$strEntityID] as &$value)
					{
						$value = (int)$value;
						if ($value > 0)
							$arValid[] = $value;
					}
					if (isset($value))
						unset($value);
					if (!empty($arValid))
					{
						$arValid = array_unique($arValid);
					}
					$arFields[$strEntityID] = $arValid;

					if (empty($arFields[$strEntityID]))
					{
						$arFields[$strEntityID] = array(-1);
					}
				}
				else
				{
					$arFields[$strEntityID] = array(-1);
				}
			}
			else
			{
				$arFields[$strEntityID] = array(-1);
			}
			$boolResult = true;
		}
		return $boolResult;
	}

	protected function __ArrayMultiple($arOrder, &$arResult, $arTuple, $arTemp = array())
	{
		if (empty($arTuple))
		{
			$arResult[] = array(
				'EQUAL' => array_combine($arOrder, $arTemp),
			);
		}
		else
		{
			$head = array_shift($arTuple);
			$arTemp[] = false;
			if (is_array($head))
			{
				if (empty($head))
				{
					$arTemp[count($arTemp)-1] = -1;
					self::__ArrayMultiple($arOrder, $arResult, $arTuple, $arTemp);
				}
				else
				{
					foreach ($head as &$value)
					{
						$arTemp[count($arTemp)-1] = $value;
						self::__ArrayMultiple($arOrder, $arResult, $arTuple, $arTemp);
					}
					if (isset($value))
						unset($value);
				}
			}
			else
			{
				$arTemp[count($arTemp)-1] = $head;
				self::__ArrayMultiple($arOrder, $arResult, $arTuple, $arTemp);
			}
		}
	}

	protected static function __Unpack(/** @noinspection PhpUnusedParameterInspection */$arProduct, $strUnpack)
	{
		if (empty($strUnpack))
			return false;
		return eval('return '.$strUnpack.';');
	}

	protected function __ConvertOldConditions($strAction, &$arFields)
	{
		$strAction = ToUpper($strAction);
		if (!is_set($arFields, 'CONDITIONS'))
		{
			$arConditions = array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
					'True' => 'True',
				),
				'CHILDREN' => array(),
			);
			$intEntityCount = 0;

			$arIBlockList = self::__ConvertOldOneEntity($arFields, 'IBLOCK_IDS');
			if (!empty($arIBlockList))
			{
				$intEntityCount++;
			}

			$arSectionList = self::__ConvertOldOneEntity($arFields, 'SECTION_IDS');
			if (!empty($arSectionList))
			{
				$intEntityCount++;
			}

			$arElementList = self::__ConvertOldOneEntity($arFields, 'PRODUCT_IDS');
			if (!empty($arElementList))
			{
				$intEntityCount++;
			}

			if (0 < $intEntityCount)
			{
				self::__AddOldOneEntity($arConditions, 'CondIBIBlock', $arIBlockList, (1 == $intEntityCount));
				self::__AddOldOneEntity($arConditions, 'CondIBSection', $arSectionList, (1 == $intEntityCount));
				self::__AddOldOneEntity($arConditions, 'CondIBElement', $arElementList, (1 == $intEntityCount));
			}

			if ('ADD' == $strAction)
			{
				$arFields['CONDITIONS'] = $arConditions;
			}
			else
			{
				if (0 < $intEntityCount)
				{
					$arFields['CONDITIONS'] = $arConditions;
				}
			}
		}
	}

	protected function __ConvertOldOneEntity(&$arFields, $strEntityID)
	{
		$arResult = false;
		if (!empty($strEntityID))
		{
			$arResult = array();
			if (isset($arFields[$strEntityID]))
			{
				if (!is_array($arFields[$strEntityID]))
					$arFields[$strEntityID] = array($arFields[$strEntityID]);
				foreach ($arFields[$strEntityID] as &$value)
				{
					$value = (int)$value;
					if ($value > 0)
						$arResult[] = $value;
				}
				if (isset($value))
					unset($value);
				if (!empty($arResult))
				{
					$arResult = array_values(array_unique($arResult));
				}
			}
		}
		return $arResult;
	}

	protected function __AddOldOneEntity(&$arConditions, $strCondID, $arEntityValues, $boolOneEntity)
	{
		if (!empty($strCondID))
		{
			$boolOneEntity = (true == $boolOneEntity ? true : false);
			if (!empty($arEntityValues))
			{
				if (1 < count($arEntityValues))
				{
					$arList = array();
					foreach ($arEntityValues as &$intItemID)
					{
						$arList[] = array(
							'CLASS_ID' => $strCondID,
							'DATA' => array(
								'logic' => 'Equal',
								'value' => $intItemID
							),
						);
					}
					if (isset($intItemID))
						unset($intItemID);
					if ($boolOneEntity)
					{
						$arConditions = array(
							'CLASS_ID' => 'CondGroup',
							'DATA' => array(
								'All' => 'OR',
								'True' => 'True',
							),
							'CHILDREN' => $arList,
						);
					}
					else
					{
						$arConditions['CHILDREN'][] = array(
							'CLASS_ID' => 'CondGroup',
							'DATA' => array(
								'All' => 'OR',
								'True' => 'True',
							),
							'CHILDREN' => $arList,
						);
					}
				}
				else
				{
					$arConditions['CHILDREN'][] = array(
						'CLASS_ID' => $strCondID,
						'DATA' => array(
							'logic' => 'Equal',
							'value' => current($arEntityValues)
						),
					);
				}
			}
		}
	}

	protected function __GetConditionValues(&$arFields)
	{
		$arResult = false;
		if (isset($arFields['CONDITIONS']) && !empty($arFields['CONDITIONS']))
		{
			$arConditions = false;
			if (!is_array($arFields['CONDITIONS']))
			{
				if (CheckSerializedData($arFields['CONDITIONS']))
				{
					$arConditions = unserialize($arFields['CONDITIONS']);
				}
			}
			else
			{
				$arConditions = $arFields['CONDITIONS'];
			}

			if (is_array($arConditions) && !empty($arConditions))
			{
				$obCond = new CCatalogCondTree();
				$boolCond = $obCond->Init(BT_COND_MODE_SEARCH, BT_COND_BUILD_CATALOG, array());
				if ($boolCond)
				{
					$arResult = $obCond->GetConditionValues($arConditions);
				}
			}
		}
		return $arResult;
	}

	protected function __GetOldOneEntity(&$arFields, &$arCondList, $strEntityID, $strCondID)
	{
		if (is_array($arCondList) && !empty($arCondList))
		{
			$arFields[$strEntityID] = array();
			if (isset($arCondList[$strCondID]) && !empty($arCondList[$strCondID]) && is_array($arCondList[$strCondID]))
			{
				if (isset($arCondList[$strCondID]['VALUES']) && !empty($arCondList[$strCondID]['VALUES']) && is_array($arCondList[$strCondID]['VALUES']))
				{
					$arCheck = array();
					foreach ($arCondList[$strCondID]['VALUES'] as &$intValue)
					{
						$intValue = (int)$intValue;
						if (0 < $intValue)
							$arCheck[] = $intValue;
					}
					if (isset($intValue))
						unset($intValue);
					$arCheck = array_values(array_unique($arCheck));
					$arFields[$strEntityID] = $arCheck;
				}
			}
		}
	}

	protected function __UpdateOldOneEntity($intID, &$arFields, $arParams, $boolUpdate)
	{
		global $DB;

		$boolUpdate = (false === $boolUpdate ? false : true);
		$intID = (int)$intID;
		if ($intID <= 0)
			return;
		if (!empty($arParams) && is_array($arParams))
		{
			if (!empty($arParams['ENTITY_ID']) && !empty($arParams['TABLE_ID']) && !empty($arParams['FIELD_ID']))
			{
				if (isset($arFields[$arParams['ENTITY_ID']]))
				{
					if ($boolUpdate)
					{
						$DB->Query("DELETE FROM ".$arParams['TABLE_ID']." WHERE DISCOUNT_ID = ".$intID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
					}
					if (!empty($arFields[$arParams['ENTITY_ID']]))
					{
						foreach ($arFields[$arParams['ENTITY_ID']] as &$intValue)
						{
							$strSql = "INSERT INTO ".$arParams['TABLE_ID']."(DISCOUNT_ID, ".$arParams['FIELD_ID'].") VALUES(".$intID.", ".$intValue.")";
							$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
						}
						if (isset($intValue))
							unset($intValue);
					}
				}
			}
		}
	}

	public static function SetDiscountFilterCache($arDiscountIDs, $arCatalogGroups, $arUserGroups)
	{
		if (!is_array($arDiscountIDs))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arDiscountIDs);

		if (!is_array($arCatalogGroups))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arCatalogGroups);
		if (empty($arCatalogGroups))
			return false;

		if (!is_array($arUserGroups))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);
		if (empty($arUserGroups))
			return false;

		$strCacheKey = md5('C'.implode('_', $arCatalogGroups).'-'.'U'.implode('_', $arUserGroups));
		self::$arCacheDiscountFilter[$strCacheKey] = $arDiscountIDs;

		return true;
	}

	public static function SetAllDiscountFilterCache($arDiscountCache, $boolNeedClear = true)
	{
		if (empty($arDiscountCache) || !is_array($arDiscountCache))
			return false;
		$boolNeedClear = !!$boolNeedClear;
		foreach ($arDiscountCache as $strCacheKey => $arDiscountIDs)
		{
			if (!is_array($arDiscountIDs))
				continue;
			if ($boolNeedClear)
				Main\Type\Collection::normalizeArrayValuesByInt($arDiscountIDs);
			self::$arCacheDiscountFilter[$strCacheKey] = $arDiscountIDs;
		}
		return true;
	}

	public static function GetDiscountFilterCache($arCatalogGroups, $arUserGroups)
	{
		if (!is_array($arCatalogGroups))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arCatalogGroups);
		if (empty($arCatalogGroups))
			return false;

		if (!is_array($arUserGroups))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);
		if (empty($arUserGroups))
			return false;

		$strCacheKey = md5('C'.implode('_', $arCatalogGroups).'-'.'U'.implode('_', $arUserGroups));
		return (isset(self::$arCacheDiscountFilter[$strCacheKey]) ? self::$arCacheDiscountFilter[$strCacheKey] : false);
	}

	public static function IsExistsDiscountFilterCache($arCatalogGroups, $arUserGroups)
	{
		if (!is_array($arCatalogGroups))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arCatalogGroups);
		if (empty($arCatalogGroups))
			return false;

		if (!is_array($arUserGroups))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);
		if (empty($arUserGroups))
			return false;

		$strCacheKey = md5('C'.implode('_', $arCatalogGroups).'-'.'U'.implode('_', $arUserGroups));
		return isset(self::$arCacheDiscountFilter[$strCacheKey]);
	}

	public static function GetDiscountFilterCacheByKey($strCacheKey)
	{
		if (empty($strCacheKey))
			return false;
		$strCacheKey = md5($strCacheKey);
		return (isset(self::$arCacheDiscountFilter[$strCacheKey]) ? self::$arCacheDiscountFilter[$strCacheKey] : false);
	}

	public static function IsExistsDiscountFilterCacheByKey($strCacheKey)
	{
		if (empty($strCacheKey))
			return false;
		$strCacheKey = md5($strCacheKey);
		return isset(self::$arCacheDiscountFilter[$strCacheKey]);
	}

	public static function GetDiscountFilterCacheKey($arCatalogGroups, $arUserGroups, $boolNeedClear = true)
	{
		$boolNeedClear = !!$boolNeedClear;
		if ($boolNeedClear)
		{
			if (!is_array($arCatalogGroups))
				return false;
			Main\Type\Collection::normalizeArrayValuesByInt($arCatalogGroups);
			if (empty($arCatalogGroups))
				return false;

			if (!is_array($arUserGroups))
				return false;
			Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);
			if (empty($arUserGroups))
				return false;
		}

		return md5('C'.implode('_', $arCatalogGroups).'-'.'U'.implode('_', $arUserGroups));
	}

	public static function SetDiscountResultCache($arDiscountList, $arDiscountIDs, $strSiteID, $strRenewal)
	{
		if (!is_array($arDiscountList))
			return false;
		if (!is_array($arDiscountIDs))
			return false;
		Main\Type\Collection::normalizeArrayValuesByInt($arDiscountIDs);
		if (empty($arDiscountIDs))
			return false;
		if ('' == $strSiteID)
			return false;
		$strRenewal = ('Y' == $strRenewal ? 'Y' : 'N');
		$strCacheKey = md5('D'.implode('_', $arDiscountIDs).'-'.'S'.$strSiteID.'-R'.$strRenewal);
		self::$arCacheDiscountResult[$strCacheKey] = $arDiscountIDs;

		return true;
	}

	public static function SetAllDiscountResultCache($arDiscountResultCache)
	{
		if (empty($arDiscountResultCache) || !is_array($arDiscountResultCache))
			return false;
		foreach ($arDiscountResultCache as $strCacheKey => $arDiscountIDs)
		{
			self::$arCacheDiscountResult[$strCacheKey] = $arDiscountIDs;
		}
		return true;

	}

	public static function GetDiscountResultCacheKey($arDiscountIDs, $strSiteID, $strRenewal, $boolNeedClear = true)
	{
		$boolNeedClear = !!$boolNeedClear;
		if ($boolNeedClear)
		{
			if (!is_array($arDiscountIDs))
				return false;
			Main\Type\Collection::normalizeArrayValuesByInt($arDiscountIDs);
			if (empty($arDiscountIDs))
				return false;

			if ('' == $strSiteID)
				return false;
			$strRenewal = ('Y' == $strRenewal ? 'Y' : 'N');
		}
		return md5('D'.implode('_', $arDiscountIDs).'-'.'S'.$strSiteID.'-R'.$strRenewal);
	}

	public static function SetDiscountProductCache($arItem, $arParams = array())
	{
		if (empty($arItem) || !is_array($arItem))
			return;

		if(self::isUsedSaleDiscountOnly())
		{
			global $USER;
			Catalog\Discount\DiscountManager::preloadProductDataToExtendOrder($arItem, $USER->GetUserGroupArray());
			return;
		}

		if (!empty($arParams) && isset($arParams['GET_BY_ID']) && $arParams['GET_BY_ID'] == 'Y')
		{
			$filter = array('ID' => $arItem);
			if (isset($arParams['IBLOCK_ID']))
				$filter['IBLOCK_ID'] = $arParams['IBLOCK_ID'];

			$select = array('ID', 'IBLOCK_ID', 'CODE', 'XML_ID', 'NAME', 'ACTIVE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO',
				'SORT', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'DATE_CREATE', 'DATE_CREATE_UNIX', 'CREATED_BY', 'TIMESTAMP_X', 'TIMESTAMP_X_UNIX',
				'MODIFIED_BY', 'TAGS', 'CATALOG_QUANTITY'
			);
			CTimeZone::Disable();
			$rsProducts = CIBlockElement::GetList(array(), $filter, false, false, $select);
			CTimeZone::Enable();
			while ($arProductFields = $rsProducts->GetNext(false, true))
			{
				$arProduct = array();

				$arProduct['ID'] = (int)$arProductFields['ID'];
				$arProduct['IBLOCK_ID'] = (int)$arProductFields['IBLOCK_ID'];

				$arProduct['SECTION_ID'] = array();
				$arProductSections = self::__GetSectionList($arProduct['IBLOCK_ID'], $arProduct['ID']);
				if (false !== $arProductSections)
					$arProduct['SECTION_ID'] = $arProductSections;

				$arProduct['CODE'] = (string)$arProductFields['~CODE'];
				$arProduct['XML_ID'] = (string)$arProductFields['~XML_ID'];
				$arProduct['NAME'] = $arProductFields['~NAME'];

				$arProduct['ACTIVE'] = $arProductFields['ACTIVE'];

				$arProduct['DATE_ACTIVE_FROM'] = (string)$arProductFields['DATE_ACTIVE_FROM'];
				if (!empty($arProduct['DATE_ACTIVE_FROM']))
					$arProduct['DATE_ACTIVE_FROM'] = (int)MakeTimeStamp($arProduct['DATE_ACTIVE_FROM']);

				$arProduct['DATE_ACTIVE_TO'] = (string)$arProductFields['DATE_ACTIVE_TO'];
				if (!empty($arProduct['DATE_ACTIVE_TO']))
					$arProduct['DATE_ACTIVE_TO'] = (int)MakeTimeStamp($arProduct['DATE_ACTIVE_TO']);

				$arProduct['SORT'] = (int)$arProductFields['SORT'];

				$arProduct['PREVIEW_TEXT'] = (string)$arProductFields['~PREVIEW_TEXT'];
				$arProduct['DETAIL_TEXT'] = (string)$arProductFields['~DETAIL_TEXT'];
				$arProduct['TAGS'] = (string)$arProductFields['~TAGS'];

				if (isset($arProductFields['DATE_CREATE_UNIX']))
				{
					$arProduct['DATE_CREATE'] = (string)$arProductFields['DATE_CREATE_UNIX'];
					if ('' != $arProduct['DATE_CREATE'])
						$arProduct['DATE_CREATE'] = (int)$arProduct['DATE_CREATE'];
				}
				else
				{
					$arProduct['DATE_CREATE'] = (string)$arProductFields['DATE_CREATE'];
					if ('' != $arProduct['DATE_CREATE'])
						$arProduct['DATE_CREATE'] = (int)MakeTimeStamp($arProduct['DATE_CREATE']);
				}

				if (isset($arProductFields['TIMESTAMP_X_UNIX']))
				{
					$arProduct['TIMESTAMP_X'] = (string)$arProductFields['TIMESTAMP_X_UNIX'];
					if ('' != $arProduct['TIMESTAMP_X'])
						$arProduct['TIMESTAMP_X'] = (int)$arProduct['TIMESTAMP_X'];
				}
				else
				{
					$arProduct['TIMESTAMP_X'] = (string)$arProductFields['TIMESTAMP_X'];
					if ('' != $arProduct['TIMESTAMP_X'])
						$arProduct['TIMESTAMP_X'] = (int)MakeTimeStamp($arProduct['TIMESTAMP_X']);
				}

				$arProduct['CREATED_BY'] = (int)$arProductFields['CREATED_BY'];
				$arProduct['MODIFIED_BY'] = (int)$arProductFields['MODIFIED_BY'];

				$arProduct['CATALOG_QUANTITY'] = (string)$arProductFields['CATALOG_QUANTITY'];
				if ('' != $arProduct['CATALOG_QUANTITY'])
					$arProduct['CATALOG_QUANTITY'] = doubleval($arProduct['CATALOG_QUANTITY']);
				$arProduct['CATALOG_WEIGHT'] = (string)$arProductFields['CATALOG_WEIGHT'];
				if ('' != $arProduct['CATALOG_WEIGHT'])
					$arProduct['CATALOG_WEIGHT'] = doubleval($arProduct['CATALOG_WEIGHT']);

				$arProduct['CATALOG_VAT_ID'] = (string)$arProductFields['CATALOG_VAT_ID'];
				if ('' != $arProduct['CATALOG_VAT_ID'])
					$arProduct['CATALOG_VAT_ID'] = (int)$arProduct['CATALOG_VAT_ID'];

				$arProduct['CATALOG_VAT_INCLUDED'] = (string)$arProductFields['CATALOG_VAT_INCLUDED'];

				if (!isset(self::$arCacheProductProperties[$arProduct['ID']]))
				{
					$propsList = array(
						$arProduct['ID'] => array()
					);
					CIBlockElement::GetPropertyValuesArray(
						$propsList,
						$arProduct['IBLOCK_ID'],
						array('ID' => $arProduct['ID'], 'IBLOCK_ID' => $arProduct['IBLOCK_ID']),
						array(),
						array(
							'ID' => true,
							'PROPERTY_TYPE' => true,
							'MULTIPLE' => true,
							'USER_TYPE' => true,
						)
					);
					self::$arCacheProductProperties[$arProduct['ID']] = $propsList[$arProduct['ID']];
					unset($propsList);
				}
				$arProps = self::$arCacheProductProperties[$arProduct['ID']];

				self::__ConvertProperties($arProduct, $arProps, array('TIME_ZONE' => 'N'));
				if (isset(self::$arCacheProductProperties[$arProduct['ID']]))
					unset(self::$arCacheProductProperties[$arProduct['ID']]);
				if (isset(self::$arCacheProductSections[$arProduct['ID']]))
					unset(self::$arCacheProductSections[$arProduct['ID']]);

				$sku = CCatalogSku::GetInfoByOfferIBlock($arProduct['IBLOCK_ID']);
				if (!empty($sku))
					self::__GenerateParent($arProduct, $sku);
				self::$arCacheProduct[$arProduct['ID']] = $arProduct;
			}
		}
		else
		{
			if (!isset(self::$arCacheProduct[$arItem['ID']]))
			{
				$arParams = array(
					'PRODUCT' => 'Y'
				);
				if (!self::__GenerateFields($arItem, $arParams))
					return;

				$sku = CCatalogSku::GetInfoByOfferIBlock($arItem['IBLOCK_ID']);
				if (!empty($sku))
					self::__GenerateParent($arItem, $sku);
				self::$arCacheProduct[$arItem['ID']] = $arItem;
			}
		}
	}

	public static function getCachedProductData($productId)
	{
		if(!isset(self::$arCacheProduct[$productId]))
		{
			return null;
		}

		return self::$arCacheProduct[$productId];
	}

	public static function SetProductSectionsCache($arItemIDs)
	{
		if (empty($arItemIDs) || !is_array($arItemIDs))
			return;
		Main\Type\Collection::normalizeArrayValuesByInt($arItemIDs);
		if (empty($arItemIDs))
			return;

		if (empty(self::$arCacheProductSections))
		{
			self::$arCacheProductSections = array_fill_keys($arItemIDs, array());
		}
		else
		{
			foreach ($arItemIDs as $intOneID)
				self::$arCacheProductSections[$intOneID] = array();
			unset($intOneID);
		}

		foreach (array_chunk($arItemIDs, 500) as $pageIds)
		{
			$rsSections = CIBlockElement::GetElementGroups(
				$pageIds,
				true,
				array("ID", "IBLOCK_SECTION_ID", "IBLOCK_ELEMENT_ID")
			);
			while ($arSection = $rsSections->Fetch())
			{
				$arSection['ID'] = (int)$arSection['ID'];
				$arSection['IBLOCK_SECTION_ID'] = (int)$arSection['IBLOCK_SECTION_ID'];
				$arSection['IBLOCK_ELEMENT_ID'] = (int)$arSection['IBLOCK_ELEMENT_ID'];
				self::$arCacheProductSections[$arSection['IBLOCK_ELEMENT_ID']][] = $arSection;
			}
			unset($arSection, $rsSections);
		}
		unset($pageIds);
	}

	public static function SetProductPropertiesCache($intProductID, $arProps)
	{
		$intProductID = (int)$intProductID;
		if ($intProductID <= 0)
			return;
		if (!is_array($arProps))
			return;

		$whiteList = array(
			'ID' => true,
			'~ID' => true,
			'PROPERTY_TYPE' => true,
			'~PROPERTY_TYPE' => true,
			'MULTIPLE' => true,
			'~MULTIPLE' => true,
			'USER_TYPE' => true,
			'~USER_TYPE' => true,
			'VALUE' => true,
			'~VALUE' => true,
			'VALUE_ENUM_ID' => true,
			'~VALUE_ENUM_ID' => true
		);
		if (!empty($arProps))
		{
			foreach (array_keys($arProps) as $index)
			{
				$arProps[$index] = array_intersect_key($arProps[$index], $whiteList);
				if (empty($arProps[$index]))
					unset($arProps[$index]);
			}
			unset($index);
		}

		if (self::isUsedSaleDiscountOnly())
			Catalog\Discount\DiscountManager::setProductPropertiesCache($intProductID, $arProps);
		else
			self::$arCacheProductProperties[$intProductID] = $arProps;
	}

	public static function ClearDiscountCache($arTypes)
	{
		if (empty($arTypes) || !is_array($arTypes))
			return;

		if (self::isUsedSaleDiscountOnly())
		{
			Catalog\Discount\DiscountManager::clearProductsCache();
			Catalog\Discount\DiscountManager::clearProductPropertiesCache();
			Catalog\Discount\DiscountManager::clearProductPricesCache();
		}

		if (isset($arTypes['PRODUCT']))
			self::$arCacheProduct = array();
		if (isset($arTypes['SECTIONS']))
			self::$arCacheProductSections = array();
		if (isset($arTypes['SECTION_CHAINS']))
			self::$arCacheProductSectionChain = array();
		if (isset($arTypes['PROPERTIES']))
			self::$arCacheProductProperties = array();
	}

	public static function isUsedSaleDiscountOnly()
	{
		if (self::$useSaleDiscount === null)
			self::initDiscountSettings();

		return self::$useSaleDiscount;
	}

	protected static function primaryDiscountFilter($price, $currency, &$discountList, &$priceDiscountList, &$accumulativeDiscountList)
	{
		$price = (float)$price;
		$currency = CCurrency::checkCurrencyID($currency);
		if ($price <= 0 || $currency === false)
			return;

		$priceDiscountList = array();
		$accumulativeDiscountList = array();
		foreach ($discountList as $oneDiscount)
		{
			$oneDiscount['PRIORITY'] = (int)$oneDiscount['PRIORITY'];
			$oneDiscount['VALUE_TYPE'] = (string)$oneDiscount['VALUE_TYPE'];
			$oneDiscount['VALUE'] = (float)$oneDiscount['VALUE'];
			$oneDiscount['TYPE'] = (int)$oneDiscount['TYPE'];
			$changeData = ($oneDiscount['CURRENCY'] != $currency);
			switch ($oneDiscount['VALUE_TYPE'])
			{
				case self::TYPE_FIX:
					$discountValue = (
						!$changeData
						? $oneDiscount['VALUE']
						: Price\Calculation::roundPrecision(
							CCurrencyRates::ConvertCurrency($oneDiscount['VALUE'], $oneDiscount['CURRENCY'], $currency)
						)
					);
					$validDiscount = ($price >= $discountValue);
					if ($validDiscount)
					{
						$oneDiscount['DISCOUNT_CONVERT'] = $discountValue;
						if ($changeData)
							$oneDiscount['VALUE'] = $oneDiscount['DISCOUNT_CONVERT'];
					}
					break;
				case self::TYPE_SALE:
					$discountValue = (
						!$changeData
						? $oneDiscount['VALUE']
						: Price\Calculation::roundPrecision(
							CCurrencyRates::ConvertCurrency($oneDiscount['VALUE'], $oneDiscount['CURRENCY'], $currency)
						)
					);
					$validDiscount = ($price > $discountValue);
					if ($validDiscount)
					{
						$oneDiscount['DISCOUNT_CONVERT'] = $discountValue;
						if ($changeData)
							$oneDiscount['VALUE'] = $oneDiscount['DISCOUNT_CONVERT'];
					}
					break;
				case self::TYPE_PERCENT:
					$validDiscount = ($oneDiscount['VALUE'] <= 100);
					if ($validDiscount)
					{
						$oneDiscount['MAX_DISCOUNT'] = (float)$oneDiscount['MAX_DISCOUNT'];
						if ($oneDiscount['TYPE'] == self::ENTITY_ID && $oneDiscount['MAX_DISCOUNT'] > 0)
						{
							$oneDiscount['DISCOUNT_CONVERT'] = (
								!$changeData
								? $oneDiscount['MAX_DISCOUNT']
								: Price\Calculation::roundPrecision(
									CCurrencyRates::ConvertCurrency($oneDiscount['MAX_DISCOUNT'], $oneDiscount['CURRENCY'], $currency)
								)
							);
							if ($changeData)
								$oneDiscount['MAX_DISCOUNT'] = $oneDiscount['DISCOUNT_CONVERT'];
						}
					}
					break;
				default:
					$validDiscount = false;
			}
			if (!$validDiscount)
				continue;
			if ($changeData)
				$oneDiscount['CURRENCY'] = $currency;
			if ($oneDiscount['TYPE'] == CCatalogDiscountSave::ENTITY_ID)
			{
				$accumulativeDiscountList[] = $oneDiscount;
			}
			elseif ($oneDiscount['TYPE'] == self::ENTITY_ID)
			{
				if (!isset($priceDiscountList[$oneDiscount['PRIORITY']]))
					$priceDiscountList[$oneDiscount['PRIORITY']] = array();
				$priceDiscountList[$oneDiscount['PRIORITY']][] = $oneDiscount;
			}
		}
		unset($oneDiscount);

		if (!empty($priceDiscountList))
			krsort($priceDiscountList);
	}

	protected static function calculatePriorityLevel($basePrice, $price, $currency, &$discountList, &$resultDiscount)
	{
		$basePrice = (float)$basePrice;
		$price = (float)$price;
		$currency = CCurrency::checkCurrencyID($currency);
		if ($basePrice <= 0 || $price <= 0 || $currency === false)
			return false;

		if (!is_array($resultDiscount))
			$resultDiscount = array();

		$currentPrice = $price;
		do
		{
			$minPrice = false;
			$minIndex = -1;
			foreach ($discountList as $discountIndex => $oneDiscount)
			{
				$calculatePrice = self::calculatePriceByDiscount($basePrice, $currentPrice, $oneDiscount, $needErase);
				if ($needErase)
				{
					unset($discountList[$discountIndex]);
				}
				else
				{
					$apply = ($minPrice === false || $minPrice > $calculatePrice);
					if ($apply)
					{
						$minPrice = $calculatePrice;
						$minIndex = $discountIndex;
					}
					unset($apply);
				}
				unset($calculatePrice);
			}
			unset($oneDiscount, $discountIndex);

			if ($minPrice !== false)
			{
				$currentPrice = $minPrice;
				$resultDiscount[] = $discountList[$minIndex];
				if ($discountList[$minIndex]['LAST_DISCOUNT'] == 'Y')
				{
					$discountList = array();
				}
				else
				{
					unset($discountList[$minIndex]);
				}
			}
		}
		while (!empty($discountList));

		return $currentPrice;
	}

	protected static function calculateDiscSave($basePrice, $price, $currency, &$discsaveList, &$resultDiscount)
	{
		$basePrice = (float)$basePrice;
		$price = (float)$price;
		$currency = CCurrency::checkCurrencyID($currency);
		if ($basePrice <= 0 || $price <= 0 || $currency === false)
			return false;

		$currentPrice = $price;
		$minPrice = false;
		$minIndex = -1;
		foreach ($discsaveList as $discountIndex => $oneDiscount)
		{
			$calculatePrice = false;
			switch($oneDiscount['VALUE_TYPE'])
			{
				case CCatalogDiscountSave::TYPE_PERCENT:
					$discountValue = Price\Calculation::roundPrecision((
						self::$getPercentFromBasePrice
							? $basePrice
							: $currentPrice
						)*$oneDiscount['VALUE']/100
					);
					$needErase = ($currentPrice < $discountValue);
					if (!$needErase)
						$calculatePrice = $currentPrice - $discountValue;
					unset($discountValue);
					break;
				case CCatalogDiscountSave::TYPE_FIX:
					$needErase = ($oneDiscount['DISCOUNT_CONVERT'] > $currentPrice);
					if (!$needErase)
						$calculatePrice = $currentPrice - $oneDiscount['DISCOUNT_CONVERT'];
					break;
				default:
					$needErase = true;
					break;
			}
			if (!$needErase)
			{
				$apply = ($minPrice === false || $minPrice > $calculatePrice);
				if ($apply)
				{
					$minPrice = $calculatePrice;
					$minIndex = $discountIndex;
				}
				unset($apply);
			}
		}
		if ($minPrice !== false && isset($discsaveList[$minIndex]))
		{
			$currentPrice = $minPrice;
			$resultDiscount[] = $discsaveList[$minIndex];
		}

		return $currentPrice;
	}

	protected static function clearFields($value)
	{
		return ($value !== null);
	}

	protected static function initDiscountSettings()
	{
		$saleInstalled = ModuleManager::isModuleInstalled('sale');
		if (self::$useSaleDiscount === null)
			self::$useSaleDiscount = $saleInstalled && (string)Option::get('sale', 'use_sale_discount_only') == 'Y';
		if (self::$getPercentFromBasePrice === null)
		{
			$moduleID = ($saleInstalled ? 'sale' : 'catalog');
			self::$getPercentFromBasePrice = (string)Option::get($moduleID, 'get_discount_percent_from_base_price') == 'Y';
		}
		if (self::$existCouponsManager === null)
			self::$existCouponsManager = $saleInstalled;
	}
}