<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\DB;
use Bitrix\Sale\Location;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Internals\DeliveryPaySystemTable;
use Bitrix\Sale\Location\Admin\LocationHelper as Helper;

/**
 * Class CAllSaleDelivery
 * @deprecated
 */
class CAllSaleDelivery
{
	const CONN_ENTITY_NAME = 'Bitrix\Sale\Delivery\DeliveryLocation';

	/**
	 * @param $val
	 * @param $key
	 * @param $operation
	 * @param $negative
	 * @param $field
	 * @param $arField
	 * @param $arFilter
	 * @return bool|string
	 * @deprecated
	 * @internal
	 */
	public static function PrepareLocation24Where($val, $key, $operation, $negative, $field, &$arField, &$arFilter)
	{
		try
		{
			$class = self::CONN_ENTITY_NAME.'Table';
			return $field." in (".$class::getConnectedEntitiesQuery(intval($val), 'id', array('select' => array('ID'))).")";
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	/**
	 * @param $arOrder
	 * @param $deliveryCode
	 * @param $arErrors
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 * @internal
	 * @deprecated
	 */
	static function DoProcessOrder(&$arOrder, $deliveryCode, &$arErrors)
	{
		if($deliveryCode == '' || $deliveryCode == '0')
			return false;

		if(CSaleDeliveryHandler::isSidNew($deliveryCode))
		{
			$service = \Bitrix\Sale\Delivery\Services\Manager::getObjectById(
				CSaleDeliveryHandler::getIdFromNewSid($deliveryCode)
			);
		}
		else
		{
			$service = \Bitrix\Sale\Delivery\Services\Manager::getObjectByCode($deliveryCode);
		}

		if ($service)
		{
			$isOrderConverted = \Bitrix\Main\Config\Option::get("main", "~sale_converted_15", 'Y');

			$arOrderTmpDel = array(
				"PRICE" => $arOrder["ORDER_PRICE"] + $arOrder["TAX_PRICE"] - $arOrder["DISCOUNT_PRICE"],
				"WEIGHT" => $arOrder["ORDER_WEIGHT"],
				"LOCATION_FROM" => COption::GetOptionString('sale', 'location', '2961', $arOrder["SITE_ID"]),
				"LOCATION_TO" => isset($arOrder["DELIVERY_LOCATION"]) ? $arOrder["DELIVERY_LOCATION"] : 0,
				"LOCATION_ZIP" => $arOrder["DELIVERY_LOCATION_ZIP"],
				"ITEMS" => $arOrder["BASKET_ITEMS"],
				"CURRENCY" => $arOrder["CURRENCY"]
			);

			if ($isOrderConverted != 'N'
				&& !empty($arOrder['ORDER_PROP']) && is_array($arOrder['ORDER_PROP']))
			{
				$arOrderTmpDel['PROPERTIES'] = $arOrder['ORDER_PROP'];
			}

			//$r = $propCollection->setValuesFromPost($fields, $_FILES);

			$arOrder["DELIVERY_ID"] = $deliveryCode;
			$shipment = self::convertOrderOldToNew($arOrderTmpDel);

			if(isset($arOrder["DELIVERY_EXTRA_SERVICES"]))
				$shipment->setExtraServices($arOrder["DELIVERY_EXTRA_SERVICES"]);

			$calculationResult = $service->calculate($shipment);

			if (!$calculationResult->isSuccess())
				$arErrors[] = array("CODE" => "CALCULATE", "TEXT" => implode("<br>\n", $calculationResult->getErrorMessages()));
			else
				$arOrder["DELIVERY_PRICE"] = roundEx($calculationResult->getPrice(), SALE_VALUE_PRECISION);
		}
		else
		{
			$arErrors[] = array("CODE" => "CALCULATE", "TEXT" => GetMessage('SKGD_DELIVERY_NOT_FOUND'));
		}
	}

	/**
	 * @deprecated Use \Bitrix\Sale\Delivery\Services\Manager
	 */
	public static function DoLoadDelivery($location, $locationZip, $weight, $price, $currency, $siteId = null, $arShoppingCart = array())
	{
		$location = intval($location);
		if ($location <= 0)
			return null;

		if ($siteId == null)
			$siteId = SITE_ID;

		$arResult = array();
		$arMaxDimensions = array();

		foreach ($arShoppingCart as $arBasketItem)
		{
			if (!is_array($arBasketItem["DIMENSIONS"]))
			{
				$arDim = unserialize($arBasketItem["~DIMENSIONS"]);
				$arBasketItem["DIMENSIONS"] = $arDim;
				unset($arBasketItem["~DIMENSIONS"]);
			}
			else
				$arDim = $arBasketItem["DIMENSIONS"];

			if (is_array($arDim))
			{
				$arMaxDimensions = CSaleDeliveryHelper::getMaxDimensions(
					array($arDim["WIDTH"], $arDim["HEIGHT"], $arDim["LENGTH"]),
					$arMaxDimensions
				);
			}
		}

		$arFilter = array(
			"COMPABILITY" => array(
				"WEIGHT" => $weight,
				"PRICE" => $price,
				"LOCATION_FROM" => COption::GetOptionString('sale', 'location', false, $siteId),
				"LOCATION_TO" => $location,
				"LOCATION_ZIP" => $locationZip,
				"MAX_DIMENSIONS" => $arMaxDimensions,
				"ITEMS" => $arShoppingCart
			),
			"SITE_ID" => $siteId,
		);
		$dbDeliveryServices = CSaleDeliveryHandler::GetList(array("SORT" => "ASC"), $arFilter);

		while ($arDeliveryService = $dbDeliveryServices->GetNext())
		{
			if (!is_array($arDeliveryService) || !is_array($arDeliveryService["PROFILES"]))
				continue;

			foreach ($arDeliveryService["PROFILES"] as $profileId => $arDeliveryProfile)
			{
				if ($arDeliveryProfile["ACTIVE"] != "Y")
					continue;

				if (!array_key_exists($arDeliveryService["SID"], $arResult))
				{
					$arResult[$arDeliveryService["SID"]] = array(
						"SID" => $arDeliveryService["SID"],
						"TITLE" => $arDeliveryService["NAME"],
						"DESCRIPTION" => $arDeliveryService["~DESCRIPTION"],
						"PROFILES" => array(),
					);
				}

				$arResult[$arDeliveryService["SID"]]["PROFILES"][$profileId] = array(
					"ID" => $arDeliveryService["SID"].":".$profileId,
					"SID" => $profileId,
					"TITLE" => $arDeliveryProfile["TITLE"],
					"DESCRIPTION" => $arDeliveryProfile["~DESCRIPTION"],
					"FIELD_NAME" => "DELIVERY_ID",
				);

				$arDeliveryPriceTmp = CSaleDeliveryHandler::CalculateFull(
					$arDeliveryService["SID"],
					$profileId,
					array(
						"PRICE" => $price,
						"WEIGHT" => $weight,
						"LOCATION_FROM" => COption::GetOptionString('sale', 'location', false, $siteId),
						"LOCATION_TO" => $location,
						"LOCATION_ZIP" => $locationZip,
						"ITEMS" => $arShoppingCart
					),
					$currency
				);

				if ($arDeliveryPriceTmp["RESULT"] != "ERROR")
				{
					$arResult[$arDeliveryService["SID"]]["PROFILES"][$profileId]["DELIVERY_PRICE"] = roundEx($arDeliveryPriceTmp["VALUE"], SALE_VALUE_PRECISION);
					$arResult[$arDeliveryService["SID"]]["PROFILES"][$profileId]["CURRENCY"] = $currency;
				}
			}
		}

		$dbDelivery = CSaleDelivery::GetList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			array(
				"LID" => $siteId,
				"+<=WEIGHT_FROM" => $weight,
				"+>=WEIGHT_TO" => $weight,
				"+<=ORDER_PRICE_FROM" => $price,
				"+>=ORDER_PRICE_TO" => $price,
				"ACTIVE" => "Y",
				"LOCATION" => $location,
			)
		);
		while ($arDelivery = $dbDelivery->GetNext())
		{
			$arDeliveryDescription = CSaleDelivery::GetByID($arDelivery["ID"]);
			$arDelivery["DESCRIPTION"] = $arDeliveryDescription["DESCRIPTION"];

			$arDelivery["FIELD_NAME"] = "DELIVERY_ID";
			if (intval($arDelivery["PERIOD_FROM"]) > 0 || intval($arDelivery["PERIOD_TO"]) > 0)
			{
				$arDelivery["PERIOD_TEXT"] = GetMessage("SALE_DELIV_PERIOD");
				if (intval($arDelivery["PERIOD_FROM"]) > 0)
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SOA_FROM")." ".intval($arDelivery["PERIOD_FROM"]);
				if (intval($arDelivery["PERIOD_TO"]) > 0)
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SOA_TO")." ".intval($arDelivery["PERIOD_TO"]);
				if ($arDelivery["PERIOD_TYPE"] == "H")
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SOA_HOUR")." ";
				elseif ($arDelivery["PERIOD_TYPE"] == "M")
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SOA_MONTH")." ";
				else
					$arDelivery["PERIOD_TEXT"] .= " ".GetMessage("SOA_DAY")." ";
			}
			$arResult[] = $arDelivery;
		}

		return $arResult;
	}

	/**
	 * @deprecated Use \Bitrix\Sale\Delivery\Services\Table::getById().
	 */
	public static function GetByID($ID)
	{
		$res = self::GetList(array(), array("ID" => $ID));
		return $res->Fetch();
	}


	/**
	 * @param array $arFilter
	 * @return bool|CDBResult
	 * @deprecated
	 */
	function GetLocationList($arFilter = Array())
	{
		if(!empty($arFilter['DELIVERY_ID']))
			$arFilter['DELIVERY_ID'] = self::getIdByCode($arFilter['DELIVERY_ID']);

		try
		{
			$locations = array();
			$res =  CSaleLocation::getDenormalizedLocationList(self::CONN_ENTITY_NAME, $arFilter);

			while($loc = $res->Fetch())
			{
				$oldDeliveryId = self::getCodeById($loc['DELIVERY_ID']);

				if($oldDeliveryId == '')
					continue;

				$loc['DELIVERY_ID'] = $oldDeliveryId;
				$locations[] = $loc;
			}
		}
		catch(Exception $e)
		{
			$locations = array();
		}

		$dbResult = new CDBResult();
		$dbResult->InitFromArray($locations);
		return $dbResult;
	}

	/**
	 * @deprecated
	 * @internal
	 */
	public static function CheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_DELIVERY"), "ERROR_NO_NAME");
			return false;
		}

		if ((is_set($arFields, "LID") || $ACTION=="ADD") && $arFields["LID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_SITE"), "ERROR_NO_SITE");
			return false;
		}

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";
		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && intval($arFields["SORT"]) <= 0)
			$arFields["SORT"] = 100;

		if (is_set($arFields, "PRICE"))
		{
			$arFields["PRICE"] = str_replace(",", ".", $arFields["PRICE"]);
			$arFields["PRICE"] = DoubleVal($arFields["PRICE"]);
		}
		if ((is_set($arFields, "PRICE") || $ACTION=="ADD") && DoubleVal($arFields["PRICE"]) < 0)
			return false;

		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && $arFields["CURRENCY"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_CURRENCY"), "ERROR_NO_CURRENCY");
			return false;
		}

		if (is_set($arFields, "ORDER_PRICE_FROM"))
		{
			$arFields["ORDER_PRICE_FROM"] = str_replace(",", ".", $arFields["ORDER_PRICE_FROM"]);
			$arFields["ORDER_PRICE_FROM"] = DoubleVal($arFields["ORDER_PRICE_FROM"]);
		}

		if (is_set($arFields, "ORDER_PRICE_TO"))
		{
			$arFields["ORDER_PRICE_TO"] = str_replace(",", ".", $arFields["ORDER_PRICE_TO"]);
			$arFields["ORDER_PRICE_TO"] = DoubleVal($arFields["ORDER_PRICE_TO"]);
		}

		if ((is_set($arFields, "LOCATIONS") || $ACTION=="ADD") && (!is_array($arFields["LOCATIONS"]) || count($arFields["LOCATIONS"]) <= 0))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGD_EMPTY_LOCATION"), "ERROR_NO_LOCATIONS");
			return false;
		}

		if (is_set($arFields, "LID"))
		{
			$dbSite = CSite::GetByID($arFields["LID"]);
			if (!$dbSite->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["LID"], GetMessage("SKGD_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if (is_set($arFields, "CURRENCY"))
		{
			if (!($arCurrency = CCurrency::GetByID($arFields["CURRENCY"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["CURRENCY"], GetMessage("SKGD_NO_CURRENCY")), "ERROR_NO_CURRENCY");
				return false;
			}
		}

		if (is_set($arFields, "LOCATIONS"))
		{
			$countField = count($arFields["LOCATIONS"]);
			for ($i = 0; $i < $countField; $i++)
			{
				if ($arFields["LOCATIONS"][$i]["LOCATION_TYPE"] != "G")
					$arFields["LOCATIONS"][$i]["LOCATION_TYPE"] = "L";
			}
		}

		return True;
	}

	/**
	 * @param $ID
	 * @param $locations
	 * @internal
	 * @deprecated
	 */
	public static function SetDeliveryLocationPro($ID, $locations)
	{
		$class = self::CONN_ENTITY_NAME.'Table';

		$links = Helper::prepareLinksForSaving($class, $locations);
		$class::resetMultipleForOwner($ID, $links);
	}

	/**
	 * @deprecated
	 */
	public static function Update($oldId, $arFields, $arOptions = array())
	{
		if($oldId == '')
			return false;

		$dbRes = Bitrix\Sale\Delivery\Services\Table::getList(array(
			'filter' => array(
				"CODE" => $oldId,
				"=CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Configurable'
			)
		));

		$oldData = $dbRes->fetch();

		if(!$oldData)
			return false;

		$newId = $oldData["ID"];

		$fields = array_intersect_key($arFields, Bitrix\Sale\Delivery\Services\Table::getMap());

		if(!empty($fields))
		{
			if(array_key_exists("LOGOTIP", $fields) && is_array($fields["LOGOTIP"]))
			{
				$fields["LOGOTIP"]["MODULE_ID"] = "sale";
				CFile::SaveForDB($fields, "LOGOTIP", "sale/delivery/logotip");
			}

			$fields["CONFIG"] = array(
				"MAIN" => array(
					"PRICE" => isset($arFields["PRICE"]) ? $arFields["PRICE"] : $oldData["CONFIG"]["MAIN"]["PRICE"],
					"PERIOD" => array(
						"FROM" => isset($arFields["PERIOD_FROM"]) ? $arFields["PERIOD_FROM"] : $oldData["CONFIG"]["MAIN"]["PERIOD"]["FROM"],
						"TO" => isset($arFields["PERIOD_TO"]) ? $arFields["PERIOD_TO"] : $oldData["CONFIG"]["MAIN"]["PERIOD"]["TO"],
						"TYPE" => isset($arFields["PERIOD_TYPE"]) ? $arFields["PERIOD_TYPE"] : $oldData["CONFIG"]["MAIN"]["PERIOD"]["TYPE"]
					)
				)
			);

			$res = \Bitrix\Sale\Delivery\Services\Manager::update($newId, $fields);

			if(!$res->isSuccess())
				return false;
		}

		if(is_set($arFields, "LOCATIONS"))
			Helper::resetLocationsForEntity($newId, $arFields['LOCATIONS'], self::CONN_ENTITY_NAME, !!$arOptions['EXPECT_LOCATION_CODES']);

		if (is_set($arFields, "PAY_SYSTEM"))
			CSaleDelivery::UpdateDeliveryPay($newId, $arFields["PAY_SYSTEM"]);

		if(isset($arFields["LID"]))
		{
			$rfields = array(
				"SERVICE_ID" => $newId,
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\BySite',
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"PARAMS" => array(
					"SITE_ID" => $arFields["LID"]
				)
			);

			$rstrRes = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
				'filter' =>array(
					"=SERVICE_ID" => $newId,
					"=SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
					"=CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\BySite'
				)
			));

			if($restrict = $rstrRes->fetch())
				$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::update($restrict["ID"], $rfields);
			else
				$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::add($rfields);
		}

		if(isset($arFields["LID"]) && $arFields["LID"] <> '')
		{
			$rfields = array(
				"SERVICE_ID" => $newId,
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\BySite',

				"PARAMS" => array(
					"SITE_ID" => $arFields["LID"]
				)
			);

			$rstrRes = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
				'filter' =>array(
					"=SERVICE_ID" => $newId,
					"=SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
					"=CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\BySite'
				)
			));

			if($restrict = $rstrRes->fetch())
				$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::update($restrict["ID"], $rfields);
			else
				$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::add($rfields);
		}

		if(isset($arFields["WEIGHT_FROM"]) || isset($arFields["WEIGHT_TO"]))
		{
			$rfields = array(
				"SERVICE_ID" => $newId,
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByWeight',
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"PARAMS" => array(
					"MIN_WEIGHT" => isset($arFields["WEIGHT_FROM"]) ? $arFields["WEIGHT_FROM"] : 0,
					"MAX_WEIGHT" => isset($arFields["WEIGHT_TO"]) ? $arFields["WEIGHT_TO"] : 0
				)
			);

			$rstrRes = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
				'filter' =>array(
					"=SERVICE_ID" => $newId,
					"=SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
					"=CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByWeight'
				)
			));

			if($restrict = $rstrRes->fetch())
			{
				if(floatval($arFields["WEIGHT_FROM"]) <= 0 && floatval($arFields["WEIGHT_TO"]) <= 0)
				{
					$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::delete($restrict["ID"]);
				}
				else
				{
					if(!isset($arFields["WEIGHT_FROM"]))
						$rfields["PARAMS"]["MIN_WEIGHT"] = $restrict["PARAMS"]["MIN_WEIGHT"];

					if(!isset($arFields["WEIGHT_TO"]))
						$rfields["PARAMS"]["MAX_WEIGHT"] = $restrict["PARAMS"]["MAX_WEIGHT"];

					$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::update($restrict["ID"], $rfields);
				}
			}
			else
			{
				$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::add($rfields);
			}

		}

		if(isset($arFields["ORDER_PRICE_FROM"]) || isset($arFields["ORDER_PRICE_TO"]) || isset($arFields["ORDER_CURRENCY"]))
		{
			$rfields = array(
				"SERVICE_ID" => $newId,
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByPrice',
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"PARAMS" => array(
					"MIN_PRICE" => isset($arFields["ORDER_PRICE_FROM"]) ? $arFields["ORDER_PRICE_FROM"] : 0,
					"MAX_PRICE" => isset($arFields["ORDER_PRICE_TO"]) ? $arFields["ORDER_PRICE_TO"] : 0,
					"CURRENCY" => isset($arFields["ORDER_CURRENCY"]) ? $arFields["ORDER_CURRENCY"] : ""
				)
			);

			$rstrRes = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
				'filter' =>array(
					"=SERVICE_ID" => $newId,
					"=SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
					"=CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByPrice'
				)
			));

			if($restrict = $rstrRes->fetch())
			{
				if(floatval($arFields["ORDER_PRICE_FROM"]) <= 0 && floatval($arFields["ORDER_PRICE_TO"]) <= 0 && $arFields["ORDER_CURRENCY"] == '')
				{
					$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::delete($restrict["ID"]);
				}
				else
				{
					if(!isset($arFields["ORDER_PRICE_FROM"]))
						$rfields["PARAMS"]["MIN_PRICE"] = $restrict["PARAMS"]["MIN_PRICE"];

					if(!isset($arFields["ORDER_PRICE_TO"]))
						$rfields["PARAMS"]["MAX_PRICE"] = $restrict["PARAMS"]["MAX_PRICE"];

					if(!isset($arFields["ORDER_CURRENCY"]))
						$rfields["PARAMS"]["CURRENCY"] = $restrict["PARAMS"]["CURRENCY"];

					$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::update($restrict["ID"], $rfields);
				}
			}
			else
			{
				$rres = \Bitrix\Sale\Internals\ServiceRestrictionTable::add($rfields);
			}
		}

		if(isset($arFields["STORE"]))
		{
			$stores = unserialize($arFields["STORE"]);

			if($stores)
				\Bitrix\Sale\Delivery\ExtraServices\Manager::saveStores($newId, $stores);
		}


		return $oldId;
	}

	/**
	 * @deprecated
	 */
	public static function Delete($ID)
	{
		$newId = \CSaleDelivery::getIdByCode($ID);

		try
		{
			$res = \Bitrix\Sale\Delivery\Services\Manager::delete($newId);
		}
		catch(\Bitrix\Main\SystemException $e)
		{
			$GLOBALS["APPLICATION"]->ThrowException($e->getMessage());
			return false;
		}

		return new CDBResult($res);
	}


	/**
	 * The function select delivery and paysystem
	 *
	 * @param array $arFilter - array to filter
	 * @return object $dbRes - object result
	 * @deprecated
	 */
	public static function GetDelivery2PaySystem($arFilter = array())
	{
		if(isset($arFilter["DELIVERY_ID"]))
			$arFilter["DELIVERY_ID"] = self::getIdByCode($arFilter["DELIVERY_ID"]);

		return CSaleDelivery2PaySystem::GetList(
			$arFilter,
			array("DELIVERY_ID", "PAYSYSTEM_ID"),
			array("DELIVERY_ID", "PAYSYSTEM_ID")
		);
	}

	/**
	 * The function updates delivery and paysystem
	 *
	 * @param int $ID - code delivery
	 * @param array $arFields - paysytem
	 * @return int $ID - code delivery
	 * @deprecated
	 */
	static function UpdateDeliveryPay($ID, $arFields)
	{
		$ID = trim($ID);

		if ($ID == '' || !is_array($arFields) || empty($arFields))
			return false;

		if ($arFields[0] == "")
			unset($arFields[0]);

		return CSaleDelivery2PaySystem::UpdateDelivery($ID, array("PAYSYSTEM_ID" => $arFields));
	}

	/**
	 * @param $fieldName
	 * @param $filter
	 * @return bool
	 */
	protected static function getFilterValue($fieldName, $filter)
	{
		$result = false;

		foreach($filter as $fName => $fValue)
			if(preg_replace('/[^A-Z_]/', '', $fName) == $fieldName)
				return $fValue;

		return $result;
	}

	/**
	 * @param $fieldName
	 * @param $filter
	 * @return bool
	 */
	protected static function isFieldInFilter($fieldName, $filter)
	{
		$res = array_key_exists(preg_replace('/[^A-Z_]/', '', $fieldName), $filter);
		return $res;
	}

	/**
	 * @param $fieldName
	 * @param $filter
	 * @return bool
	 */
	protected static function isFieldInFilter2($fieldName, $filter)
	{
		$result = false;

		foreach($filter as $key => $value)
			if(preg_replace('/[^A-Z_]/', '', $key) == $fieldName)
				return true;

		return $result;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	protected static function hasNewServiceField($name)
	{
		$serviceFields = \Bitrix\Sale\Delivery\Services\Table::getMap();
		return self::isFieldInFilter($name, $serviceFields);
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	protected static function convertFilterOldToNew(array $filter = array())
	{
		if(empty($filter))
			return array();

		$result = array();

		if(isset($filter["ID"]))
		{
			$filter["CODE"] = $filter["ID"];
			unset($filter["ID"]);
		}

		foreach($filter as $fieldName => $fieldValue)
			if(self::hasNewServiceField($fieldName))
				$result[$fieldName] = $fieldValue;

		return $result;
	}

	/**
	 * @param $groupBy
	 * @return array
	 */
	protected static function convertGroupOldToNew($groupBy)
	{
		if(!is_array($groupBy) || empty($groupBy))
			return array();

		$result = array();
		$serviceFields = Bitrix\Sale\Delivery\Services\Table::getMap();

		foreach($groupBy as $group)
			if(array_key_exists($group, $serviceFields))
				$result[] = $group;

		return $result;
	}

	/**
	 * @param array $selectFields
	 * @return array
	 */
	protected static function convertSelectOldToNew(array $selectFields = array())
	{
		if(empty($selectFields))
			return array();

		if(in_array('*', $selectFields))
			return array('*');

		$result = array();
		$serviceFields = Bitrix\Sale\Delivery\Services\Table::getMap();

		foreach($selectFields as $select)
			if(array_key_exists($select, $serviceFields))
				$result[] = $select;

		if(in_array('ID', $selectFields))
			$result[] = "CODE";

		return $result;
	}

	/**
	 * @param $fieldName
	 * @param array $select
	 * @return bool
	 */
	protected static function isFieldSelected($fieldName , array $select)
	{
		$result = empty($select) || in_array($fieldName, $select) || in_array("*", $select);
		return $result;
	}

	/**
	 * @param array $restriction
	 * @param array $filter
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected static function checkRestrictionFilter(array $restriction, array $filter)
	{
		$result = true;

		switch($restriction["CLASS_NAME"])
		{
			case '\Bitrix\Sale\Delivery\Restrictions\BySite':

				$fieldInFilter = self::isFieldInFilter2("LID", $filter);
				$value = self::getFilterValue("LID", $filter);

				if(!$fieldInFilter)
					break;

				if(is_array($restriction["PARAMS"]["SITE_ID"]))
					$result = in_array($value, $restriction["PARAMS"]["SITE_ID"]);
				else
					$result = ($value == $restriction["PARAMS"]["SITE_ID"]);

				break;

			case '\Bitrix\Sale\Delivery\Restrictions\ByWeight':
				$result = !(self::isFieldInFilter2("WEIGHT_FROM", $filter) && floatval(self::getFilterValue("WEIGHT_FROM", $filter)) < floatval($restriction["PARAMS"]["MIN_WEIGHT"]));
				$result = $result && !(self::isFieldInFilter2("WEIGHT_TO", $filter) && floatval(self::getFilterValue("WEIGHT_TO", $filter)) > floatval($restriction["PARAMS"]["MAX_WEIGHT"]));
				break;

			case '\Bitrix\Sale\Delivery\Restrictions\ByPrice':

				$fieldInFilter = self::isFieldInFilter2("ORDER_PRICE_FROM", $filter);
				$value = self::getFilterValue("ORDER_PRICE_FROM", $filter);
				$value = floatval($value);

				if($fieldInFilter && $value > 0 && floatval($restriction["PARAMS"]["MIN_PRICE"]) > 0)
				{
					$result = floatval($value) > floatval($restriction["PARAMS"]["MIN_PRICE"]);

					if(!$result)
						break;
				}

				$fieldInFilter = self::isFieldInFilter2("ORDER_PRICE_TO", $filter);
				$value = self::getFilterValue("ORDER_PRICE_TO", $filter);
				$value = floatval($value);

				if($fieldInFilter && $value > 0 && floatval($restriction["PARAMS"]["MAX_PRICE"]) > 0)
				{
					$result = floatval($value) < floatval($restriction["PARAMS"]["MAX_PRICE"]);

					if(!$result)
						break;
				}

				$fieldInFilter = self::isFieldInFilter2("ORDER_CURRENCY", $filter);
				$value = self::getFilterValue("ORDER_CURRENCY", $filter);

				if($fieldInFilter && $value <> '' && $restriction["PARAMS"]["CURRENCY"] <> '')
				{
					$result = ($value == $restriction["PARAMS"]["CURRENCY"]);

					if(!$result)
						break;
				}

				break;

			case '\Bitrix\Sale\Delivery\Restrictions\ByLocation':
				$fieldInFilter = self::isFieldInFilter2("LOCATION", $filter);
				$value = self::getFilterValue("LOCATION", $filter);

				if($fieldInFilter && $value <> '' && $restriction['SERVICE_ID'] > 0)
				{
					try
					{
						$result = \Bitrix\Sale\Delivery\DeliveryLocationTable::checkConnectionExists(
							intval($restriction['SERVICE_ID']),
							$value,
							array(
								'LOCATION_LINK_TYPE' => 'CODE'
							)
						);
					}
					catch(\Bitrix\Sale\Location\Tree\NodeNotFoundException $e)
					{
						$result = false;
					}

					if($result)
						return true;

					try
					{
						return \Bitrix\Sale\Delivery\DeliveryLocationTable::checkConnectionExists(
							intval($restriction['SERVICE_ID']),
							$value,
							array(
								'LOCATION_LINK_TYPE' => 'ID'
							)
						);
					}
					catch(\Bitrix\Sale\Location\Tree\NodeNotFoundException $e)
					{
						$result = false;
					}
				}

				break;

			default:
				break;
		}

		return $result;
	}

	/**
	 * @param array $service
	 * @param array $restriction
	 * @param array $selectedFields
	 * @return array
	 */
	protected static function getSelectedRestrictionField(array $service, array $restriction, array $selectedFields)
	{
		$fields = array();

		switch($restriction["CLASS_NAME"])
		{
			case '\Bitrix\Sale\Delivery\Restrictions\BySite':

				if(self::isFieldSelected("LID", $selectedFields))
				{
					$lids = $restriction["PARAMS"]["SITE_ID"];

					if(is_array($lids))
					{
						reset($lids);
						$fields["LID"] = current($lids);
					}
					else
					{
						$fields["LID"] = $lids;
					}
				}

				break;

			case '\Bitrix\Sale\Delivery\Restrictions\ByWeight':

				if(self::isFieldSelected("WEIGHT_FROM", $selectedFields))
					$fields["WEIGHT_FROM"] = $restriction["PARAMS"]["MIN_WEIGHT"];

				if(self::isFieldSelected("WEIGHT_TO", $selectedFields))
					$fields["WEIGHT_TO"] = $restriction["PARAMS"]["MAX_WEIGHT"];

				break;

			case '\Bitrix\Sale\Delivery\Restrictions\ByPrice':

				if(self::isFieldSelected("ORDER_PRICE_FROM", $selectedFields))
					$fields["ORDER_PRICE_FROM"] = $restriction["PARAMS"]["MIN_PRICE"];

				if(self::isFieldSelected("ORDER_PRICE_TO", $selectedFields))
					$fields["ORDER_PRICE_TO"] = $restriction["PARAMS"]["MAX_PRICE"];

				if(self::isFieldSelected("ORDER_CURRENCY", $selectedFields))
					$fields["ORDER_CURRENCY"] = $restriction["PARAMS"]["CURRENCY"];

				break;

			default:
				break;
		}

		if(!empty($fields))
			$service = array_merge($service, $fields);

		return $service;
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param bool $arGroupBy
	 * @param bool $arNavStartParams
	 * @param array $arSelectFields
	 * @return \CDBResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @deprecated
	 */
	public static function GetList($arOrder = array("SORT" => "ASC", "NAME" => "ASC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array('*'))
	{
		if(empty($arSelectFields))
			$arSelectFields = array('*');

		$params = array(
			'order' => self::convertFilterOldToNew($arOrder),
			'filter' => self::convertFilterOldToNew($arFilter),
			'group' => self::convertGroupOldToNew($arGroupBy),
			'select' => self::convertSelectOldToNew($arSelectFields)
		);

		$services = array();
		$params['filter']['=CLASS_NAME'] = '\Bitrix\Sale\Delivery\Services\Configurable';
		$dbRes = \Bitrix\Sale\Delivery\Services\Table::getList($params);

		if (isset($arFilter["WEIGHT"]) && DoubleVal($arFilter["WEIGHT"]) > 0)
		{
			if (!isset($arFilter["WEIGHT_FROM"]) || floatval($arFilter["WEIGHT"]) > floatval($arFilter["WEIGHT_FROM"]))
				$arFilter["+<=WEIGHT_FROM"] = $arFilter["WEIGHT"];
			if (!isset($arFilter["WEIGHT_TO"]) || floatval($arFilter["WEIGHT"]) < floatval($arFilter["WEIGHT_TO"]))
				$arFilter["+>=WEIGHT_TO"] = $arFilter["WEIGHT"];
		}

		if (isset($arFilter["ORDER_PRICE"]) && intval($arFilter["ORDER_PRICE"]) > 0)
		{
			if (!isset($arFilter["ORDER_PRICE_FROM"]) || floatval($arFilter["ORDER_PRICE"]) > floatval($arFilter["ORDER_PRICE_FROM"]))
				$arFilter["+<=ORDER_PRICE_FROM"] = $arFilter["ORDER_PRICE"];
			if (!isset($arFilter["ORDER_PRICE_TO"]) || floatval($arFilter["ORDER_PRICE"]) < floatval($arFilter["ORDER_PRICE_TO"]))
				$arFilter["+>=ORDER_PRICE_TO"] = $arFilter["ORDER_PRICE"];
		}

		while($service = $dbRes->fetch())
		{
			$dbRstrRes = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
				'filter' => array(
					"=SERVICE_ID" => $service["ID"],
					"=SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT
				)
			));

			while($restr = $dbRstrRes->fetch())
			{
				if(!self::checkRestrictionFilter($restr, $arFilter))
					continue(2);

				$service = self::getSelectedRestrictionField($service, $restr, $arSelectFields);
			}

			$selectAsterisk = in_array('*', $arSelectFields);
			$mofifiedFields = array("LID", "WEIGHT_FROM", "WEIGHT_TO","ORDER_PRICE_FROM", "ORDER_PRICE_TO", "ORDER_CURRENCY");

			foreach($mofifiedFields as $field)
				if(($selectAsterisk || in_array($field, $arSelectFields)) && !array_key_exists($field, $service))
					$service[$field] = "";

			if($selectAsterisk || in_array("PERIOD_FROM", $arSelectFields))
				$service["PERIOD_FROM"] = $service["CONFIG"]["MAIN"]["PERIOD"]["FROM"];

			if($selectAsterisk || in_array("PERIOD_TO", $arSelectFields))
				$service["PERIOD_TO"] = $service["CONFIG"]["MAIN"]["PERIOD"]["TO"];

			if($selectAsterisk || in_array("PERIOD_TYPE", $arSelectFields))
				$service["PERIOD_TYPE"] = $service["CONFIG"]["MAIN"]["PERIOD"]["TYPE"];

			if($selectAsterisk || in_array("PRICE", $arSelectFields))
			{
				$service["CLASS_NAME"] = '\Bitrix\Sale\Delivery\Services\Configurable';
				$tmpSrv = \Bitrix\Sale\Delivery\Services\Manager::getPooledObject($service);

				if($tmpSrv)
				{
					$res = $tmpSrv->calculate();
					$service["PRICE"] = $res->getPrice();
				}
				else
				{
					$service["PRICE"] = 0;
				}
			}

			if($selectAsterisk || in_array("STORE", $arSelectFields))
			{
				$stores = \Bitrix\Sale\Delivery\ExtraServices\Manager::getStoresList($service["ID"]);
				$service["STORE"] = count($stores) > 0 ? serialize($stores) : "";
			}

			if(intval($service["CODE"]) > 0)
				$service["ID"] = $service["CODE"];

			unset($service["CODE"], $service["CLASS_NAME"], $service["CONFIG"], $service["PARENT_ID"]);
			$services[] = $service;
		}

		if(!empty($arOrder))
		{
			foreach($arOrder as $k => $v)
			{
				if($v == 'ASC')
					$arOrder[$k] = SORT_ASC;
				elseif($v == 'DESC')
					$arOrder[$k] = SORT_DESC;
			}

			sortByColumn($services, $arOrder);
		}

		$result = new \CDBResult;
		$result->InitFromArray($services);

		return $result;
	}

	/**
	 * @param $arFields
	 * @param array $arOptions
	 * @return bool|int
	 * @throws Exception
	 * @deprecated
	 */
	static function Add($arFields, $arOptions = array())
	{
		$fields = array_intersect_key($arFields, Bitrix\Sale\Delivery\Services\Table::getMap());

		if (array_key_exists("LOGOTIP", $arFields) && is_array($arFields["LOGOTIP"]))
		{
			$arFields["LOGOTIP"]["MODULE_ID"] = "sale";
			CFile::SaveForDB($arFields, "LOGOTIP", "sale/delivery/logotip");
			$fields["LOGOTIP"] = $arFields["LOGOTIP"];
		}

		$fields["PARENT_ID"] = 0;
		$fields["CLASS_NAME"] = '\Bitrix\Sale\Delivery\Services\Configurable';
		$fields["CONFIG"] = array(
			"MAIN" => array(
				"PRICE" => $arFields["PRICE"],
				"PERIOD" => array(
					"FROM" => $arFields["PERIOD_FROM"],
					"TO" => $arFields["PERIOD_TO"],
					"TYPE" => $arFields["PERIOD_TYPE"],
				)
			)
		);

		$res = \Bitrix\Sale\Delivery\Services\Manager::add($fields);

		if(!$res->isSuccess())
			return false;

		$newId = $res->getId();

		if(empty($arFields["CODE"]))
		{
			\Bitrix\Sale\Delivery\Services\Manager::update($newId, array('CODE' => $newId));
		}

		$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::add(array(
			"SERVICE_ID" => $newId,
			"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
			"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\BySite',
			"PARAMS" => array(
				"SITE_ID" => array($arFields["LID"]),
			)
		));

		if(intval($arFields["WEIGHT_FROM"]) > 0 || intval($arFields["WEIGHT_TO"]) > 0)
		{
			$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::add(array(
				"SERVICE_ID" => $newId,
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByWeight',
				"PARAMS" => array(
					"MIN_WEIGHT" => $arFields["WEIGHT_FROM"],
					"MAX_WEIGHT" => $arFields["WEIGHT_TO"]
				)
			));
		}

		if(intval($arFields["ORDER_PRICE_FROM"]) > 0 || intval($arFields["ORDER_PRICE_TO"]) > 0)
		{
			$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::add(array(
				"SERVICE_ID" => $newId,
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByPrice',
				"PARAMS" => array(
					"MIN_PRICE" => $arFields["ORDER_PRICE_FROM"],
					"MAX_PRICE" => $arFields["ORDER_PRICE_TO"],
					"CURRENCY" => $arFields["ORDER_CURRENCY"]
				)
			));
		}

		if(isset($arFields["LOCATIONS"]) && is_array($arFields["LOCATIONS"]))
		{
			Helper::resetLocationsForEntity($newId, $arFields['LOCATIONS'], self::CONN_ENTITY_NAME, !!$arOptions['EXPECT_LOCATION_CODES']);

			\Bitrix\Sale\Internals\ServiceRestrictionTable::add(array(
				"SERVICE_ID" => $newId,
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByLocation',
				"SORT" => 100
			));
		}

		if (isset($arFields["PAY_SYSTEM"]))
			CSaleDelivery::UpdateDeliveryPay($newId, $arFields["PAY_SYSTEM"]);

		if(isset($arFields["STORE"]))
		{
			$stores = unserialize($arFields["STORE"]);

			if($stores)
				\Bitrix\Sale\Delivery\ExtraServices\Manager::saveStores($newId, $stores);
		}

		return $newId;
	}

	protected static function createD2LTable()
	{
		$con = \Bitrix\Main\Application::getConnection();
		$result = new \Bitrix\Sale\Result();
		$type = $con->getType();
		$query = array();

		if(!in_array($type, array('mssql', 'mysql', 'oracle')))
		{
			$result->addError(new \Bitrix\Main\Error('Wrong connection type!'));
			return $result;
		}

		switch($type)
		{
			case 'mssql':
				$query = array(
					"CREATE TABLE B_SALE_DELIVERY2LOCATION_TMP
					(
						DELIVERY_ID int NOT NULL,
						LOCATION_CODE varchar(100) NOT NULL,
						LOCATION_TYPE char(1) NOT NULL
					)",
					"ALTER TABLE B_SALE_DELIVERY2LOCATION_TMP ADD CONSTRAINT PK_B_SALE_DELIVERY2LOCATION_TMP PRIMARY KEY (DELIVERY_ID, LOCATION_CODE, LOCATION_TYPE)",
					"ALTER TABLE B_SALE_DELIVERY2LOCATION_TMP ADD CONSTRAINT DF_B_SALE_DELIVERY2LOCATION_TMP_LOCATION_TYPE DEFAULT 'L' FOR LOCATION_TYPE"
				);

				break;

			case 'mysql':
				$query = array(
					"create table if not exists b_sale_delivery2location_tmp
					(
						DELIVERY_ID int not null,
						LOCATION_CODE varchar(100) not null,
						LOCATION_TYPE char(1) not null default 'L',
						primary key (DELIVERY_ID, LOCATION_CODE, LOCATION_TYPE)
					)");

				break;

			case 'oracle':
				$query = array(
					"CREATE TABLE B_SALE_DELIVERY2LOCATION_TMP
					(
						DELIVERY_ID NUMBER(18) NOT NULL,
						LOCATION_CODE VARCHAR2(100 CHAR) NOT NULL,
						LOCATION_TYPE CHAR(1 CHAR) DEFAULT 'L' NOT NULL,
						PRIMARY KEY (DELIVERY_ID, LOCATION_CODE, LOCATION_TYPE)
					)");

				break;
		}

		foreach($query as $q)
			$con->queryExecute($q);

		return $result;
	}

	/**
	 * @param bool|false $renameTable
	 * @return \Bitrix\Sale\Result
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentException
	 * @internal
	 */
	public static function convertToNew($renameTable = false)
	{
		$result = new \Bitrix\Sale\Result();
		$con = \Bitrix\Main\Application::getConnection();

		if(!$con->isTableExists("b_sale_delivery"))
			return $result;

		if(!$con->isTableExists("b_sale_delivery2location_tmp"))
		{
			$res = self::createD2LTable();

			if(!$res->isSuccess())
			{
				$result->addErrors($res->getErrors());
				return $result;
			}

			$con->queryExecute('
				INSERT INTO
					b_sale_delivery2location_tmp(DELIVERY_ID, LOCATION_CODE, LOCATION_TYPE)
				SELECT
					DELIVERY_ID, LOCATION_CODE, LOCATION_TYPE FROM b_sale_delivery2location
			');

			$con->queryExecute('DELETE FROM b_sale_delivery2location');
		}

		$sqlHelper = $con->getSqlHelper();
		$deliveryRes = $con->query('SELECT * FROM b_sale_delivery WHERE CONVERTED != \'Y\'');

		while($delivery = $deliveryRes->fetch())
		{
			$delivery["CODE"] = $delivery["ID"];
			unset($delivery["ID"]);

			$newId = \CSaleDelivery::Add($delivery);

			if(intval($newId) <= 0)
			{
				$result->addError( new \Bitrix\Main\Entity\EntityError("Can't convert old delivery id: ".$delivery["CODE"]));
				continue;
			}

			if(!$res->isSuccess())
				$result->addErrors($res->getErrors());

			$con->queryExecute('UPDATE b_sale_delivery SET CONVERTED=\'Y\' WHERE ID='.$sqlHelper->forSql($delivery["CODE"]));
			$con->queryExecute("UPDATE b_sale_order SET DELIVERY_ID='".$sqlHelper->forSql($newId)."' WHERE DELIVERY_ID = '".$sqlHelper->forSql($delivery["CODE"])."'");
			$con->queryExecute("UPDATE b_sale_order_history SET DELIVERY_ID='".$sqlHelper->forSql($newId)."' WHERE DELIVERY_ID = '".$sqlHelper->forSql($delivery["CODE"])."'");
			$con->queryExecute("UPDATE b_sale_delivery2paysystem SET DELIVERY_ID='".$sqlHelper->forSql($newId)."', DELIVERY_PROFILE_ID='##CONVERTED##' WHERE DELIVERY_ID = '".$sqlHelper->forSql($delivery["CODE"])."'");

			$con->queryExecute('
				INSERT INTO
					b_sale_delivery2location(DELIVERY_ID, LOCATION_CODE, LOCATION_TYPE)
				SELECT
					'.$sqlHelper->forSql($newId).', LOCATION_CODE, LOCATION_TYPE FROM b_sale_delivery2location_tmp
				WHERE
					DELIVERY_ID = '.$sqlHelper->forSql($delivery["CODE"]).'
			');

			$con->queryExecute('DELETE FROM b_sale_delivery2location_tmp WHERE DELIVERY_ID = '.$sqlHelper->forSql($delivery["CODE"]));

			$d2pRes = \Bitrix\Sale\Internals\DeliveryPaySystemTable::getList(array(
				'filter' => array(
					'DELIVERY_ID' => $newId
				),
				'select' => array("DELIVERY_ID"),
				'group' => array("DELIVERY_ID")
			));

			if($d2p = $d2pRes->fetch())
			{
				$res = \Bitrix\Sale\Internals\ServiceRestrictionTable::add(array(
					"SERVICE_ID" => $d2p["DELIVERY_ID"],
					"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
					"CLASS_NAME" => '\Bitrix\Sale\Delivery\Restrictions\ByPaySystem',
					"SORT" => 100
				));

				if(!$res->isSuccess())
					$result->addErrors($res->getErrors());
			}
		}

		if($result->isSuccess())
		{
			$con->dropTable('b_sale_delivery2location_tmp');

			if($renameTable)
				$con->renameTable("b_sale_delivery", "b_sale_delivery_old");
		}

		return $result;
	}

	/**
	 * @param bool|false $renameTable
	 * @return string
	 * @internal
	 */
	public static function convertToNewAgent($renameTable = false)
	{
		self::convertToNew($renameTable);
		return "";
	}

	/**
	 * @return string
	 * @internal
	 */
	public static function convertPSRelationsAgent()
	{
		self::convertPSRelations();
		return "";
	}

	/**
	 * @return \Bitrix\Sale\Result
	 * @throws Exception
	 * @throws \Bitrix\Main\ArgumentException
	 * @internal
	 */
	public static function convertPSRelations()
	{
		$result = new \Bitrix\Sale\Result();
		$con = \Bitrix\Main\Application::getConnection();

		if(!$con->isTableExists("b_sale_delivery2paysystem"))
			return $result;

		$query = new \Bitrix\Main\Entity\Query(DeliveryPaySystemTable::getEntity());
		$query->setSelect(array('DELIVERY_ID'));
		$query->addFilter('LINK_DIRECTION', NULL);
		$query->setLimit(1);
		$res = $query->exec();

		if (!$res->fetch())
			return $result;

		$con->queryExecute('UPDATE b_sale_delivery2paysystem SET LINK_DIRECTION=\''.DeliveryPaySystemTable::LINK_DIRECTION_DELIVERY_PAYSYSTEM.'\'');
		$res = DeliveryPaySystemTable::getList(array());

		while($rec = $res->fetch())
		{
			unset($rec["ID"]);
			$rec["LINK_DIRECTION"] = DeliveryPaySystemTable::LINK_DIRECTION_PAYSYSTEM_DELIVERY;
			DeliveryPaySystemTable::Add($rec);
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @return array Old order.
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function convertOrderNewToOld(\Bitrix\Sale\Shipment $shipment)
	{
		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $shipment->getCollection();
		/** @var \Bitrix\Sale\Order $newOrder */
		$newOrder = $shipmentCollection->getOrder();
		$props = $newOrder->getPropertyCollection();
		$oldOrder = array();

		if(intval($newOrder->getId()) > 0)
			$oldOrder["ID"] = $newOrder->getId();

		/** @var \Bitrix\Sale\Basket  $basket */
		if($collection = $shipment->getShipmentItemCollection())
			$oldOrder["PRICE"] = $collection->getPrice();

		$oldOrder["LOCATION_FROM"] = \Bitrix\Main\Config\Option::get(
			'sale',
			'location',
			"",
			$newOrder->getSiteId());
		$oldOrder["SITE_ID"] = $newOrder->getSiteId();
		$oldOrder["PERSON_TYPE_ID"] = $newOrder->getPersonTypeId();
		$oldOrder["CURRENCY"] = $newOrder->getCurrency();

		$loc = $props->getDeliveryLocation();
		$oldOrder["LOCATION_TO"] = !!$loc ? $loc->getValue() : "";

		$loc = $props->getDeliveryLocationZip();
		$oldOrder["LOCATION_ZIP"] = !!$loc ? $loc->getValue() : "";

		$oldOrder["ITEMS"] = array();

		/** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
		foreach($shipment->getShipmentItemCollection() as $shipmentItem)
		{
			$basketItem = $shipmentItem->getBasketItem();

			if(!$basketItem)
				continue;

			if($basketItem->isBundleChild())
				continue;

			$itemFieldValues = $basketItem->getFieldValues();
			$itemFieldValues["QUANTITY"] = $shipmentItem->getField("QUANTITY");

			if(!empty($itemFieldValues["DIMENSIONS"]) && is_string($itemFieldValues["DIMENSIONS"]))
				$itemFieldValues["DIMENSIONS"] = unserialize($itemFieldValues["DIMENSIONS"]);

			unset($itemFieldValues['DATE_INSERT'], $itemFieldValues['DATE_UPDATE']);
			$oldOrder["ITEMS"][] = $itemFieldValues;
			$oldOrder["WEIGHT"] = $shipment->getWeight();
		}

		return $oldOrder;
	}

	/**
	 * @param array $oldOrder
	 * @return Shipment
	 * @internal
	 */
	public static function convertOrderOldToNew(array $oldOrder)
	{
		$siteId = isset($oldOrder["SITE_ID"]) ? $oldOrder["SITE_ID"] : SITE_ID;

		$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var \Bitrix\Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$newOrder = $orderClass::create($siteId, null, $oldOrder["CURRENCY"]);
		$isStartField = $newOrder->isStartField();

		if(!empty($oldOrder["PERSON_TYPE_ID"]) && intval($oldOrder["PERSON_TYPE_ID"]) > 0)
		{
			$personTypeId = $oldOrder["PERSON_TYPE_ID"];
		}
		else
		{
			$dbPersonType = \CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("ACTIVE" => "Y", "LID"=> $siteId));

			if($arPersonType = $dbPersonType->GetNext())
				$personTypeId = $arPersonType["ID"];
			else
				$personTypeId = 1;
		}

		$newOrder->setPersonTypeId($personTypeId);
		$newOrder->setFieldNoDemand("PRICE", $oldOrder["PRICE"]);

		/** @var \Bitrix\Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		$basket = $basketClass::create($siteId);
		$settableFields = array_flip(\Bitrix\Sale\BasketItemBase::getSettableFields());

		if (!empty($oldOrder["ITEMS"]) && is_array($oldOrder["ITEMS"]))
		{
			foreach($oldOrder["ITEMS"] as $oldBasketItem)
			{
				$basketId = null;
				if (!empty($oldBasketItem['ID']) && intval($oldBasketItem['ID']) > 0)
					$basketId = $oldBasketItem['ID'];

				$newBasketItem = \Bitrix\Sale\BasketItem::create($basket, $oldBasketItem['MODULE'], $oldBasketItem['PRODUCT_ID']);
				$oldBasketItem = array_intersect_key($oldBasketItem, $settableFields);

				$newBasketItem->setFieldsNoDemand($oldBasketItem);

				if ($basketId > 0)
					$newBasketItem->setFieldNoDemand('ID', $basketId);

				if ($newBasketItem->isBundleChild())
					continue;
				$basket->addItem($newBasketItem);
			}
		}

		$props = $newOrder->getPropertyCollection();

		if (!empty($oldOrder['PROPERTIES']) && is_array($oldOrder['PROPERTIES']))
		{
			$r = $props->setValuesFromPost($oldOrder, $_FILES);
		}

		$newOrder->setBasket($basket);


		if($loc = $props->getDeliveryLocation())
			$loc->setValue($oldOrder["LOCATION_TO"]);

		if($loc = $props->getDeliveryLocationZip())
			$loc->setValue($oldOrder["LOCATION_ZIP"]);

		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $newOrder->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		$shipment->setField("CURRENCY", $oldOrder["CURRENCY"]);
		$shipmentItemCollection = $shipment->getShipmentItemCollection();

		foreach($newOrder->getBasket() as $item)
		{
			$shipmentItem = $shipmentItemCollection->createItem($item);
			$shipmentItem->setQuantity($item->getQuantity());

			if($shipmentItem->getField("DIMENSIONS") <> '')
			{
				$shipmentItem->setField("DIMENSIONS", unserialize($shipmentItem->getField("DIMENSIONS")));
			}
		}

		if (isset($arOrder["DELIVERY_EXTRA_SERVICES"]))
			$shipment->setExtraServices($arOrder["DELIVERY_EXTRA_SERVICES"]);

		return $shipment;
	}

	/**
	 * @return string
	 * @throws Exception
	 * @internal
	 */
	public static function createNoDeliveryServiceAgent()
	{
		$id = \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId();

		if ($id <= 0)
		{
			Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/delivery/helper.php', 'ru');

			$fields = array();
			$fields["NAME"] = \Bitrix\Main\Localization\Loc::getMessage('SALE_DELIVERY_HELPER_NO_DELIVERY_SERVICE');
			$fields["CLASS_NAME"] = '\Bitrix\Sale\Delivery\Services\EmptyDeliveryService';
			$fields["CURRENCY"] = 'RUB';
			$fields["ACTIVE"] = "Y";
			$fields["CONFIG"] = array(
				'MAIN' => array(
					'CURRENCY' => 'RUB',
					'PRICE' => 0,
					'PERIOD' => array(
						'FROM' => 0,
						'TO' => 0,
						'TYPE' => 'D'
					)
				)
			);
			$res = \Bitrix\Sale\Delivery\Services\Manager::add($fields);
			$id = $res->getId();
			$fields = array(
				'SORT' => 100,
				'SERVICE_ID' => $id,
				'SERVICE_TYPE' => \Bitrix\Sale\Delivery\Restrictions\Manager::SERVICE_TYPE_SHIPMENT,
				'PARAMS' => array(
					'PUBLIC_SHOW' => 'N'
				)
			);
			$rstrPM = new \Bitrix\Sale\Delivery\Restrictions\ByPublicMode();
			$rstrPM->save($fields);
		}

		return "";
	}

	/**
	 * @param string $code Delivery service code.
	 * @return int Delivery id.
	 */
	public static function getIdByCode($code)
	{
		if(CSaleDeliveryHandler::isSidNew($code))
			$id = CSaleDeliveryHandler::getIdFromNewSid($code);
		else
			$id = \Bitrix\Sale\Delivery\Services\Manager::getIdByCode($code);

		return (int)$id;
	}

	/**
	 * @param int $id Delivery service id.
	 * @return string Delivery service code.
	 */
	public static function getCodeById($id)
	{
		if(intval($id) <= 0)
			return "";

		$code = \Bitrix\Sale\Delivery\Services\Manager::getCodeById($id);

		if($code == '')
			$code = 'new'.strval($id).':profile';

		return $code;
	}
}
?>