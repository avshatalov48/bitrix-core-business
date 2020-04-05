<?

use
	Bitrix\Sale\Internals\OrderPropsTable,
	Bitrix\Sale\Compatible\OrderQuery,
	Bitrix\Sale\Compatible\FetchAdapter,
	Bitrix\Main\Entity,
	Bitrix\Main\Application,
	Bitrix\Main\SystemException,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/** @deprecated */
class CSaleOrderProps
{
	/*
	 * Checks order properties' values on the basis of order properties' restrictions
	 *
	 * @param array $arOrder - order data
	 * @param array $arOrderPropsValues - array of order properties values to be checked
	 * @param array $arErrors
	 * @param array $arWarnings
	 * @param int $paysystemId - id of the paysystem, will be used to get order properties related to this paysystem
	 * @param int $deliveryId - id of the delivery sysetm, will be used to get order properties related to this delivery system
	 */
	static function DoProcessOrder(&$arOrder, $arOrderPropsValues, &$arErrors, &$arWarnings, $paysystemId = 0, $deliveryId = "", $arOptions = array())
	{
		if (!is_array($arOrderPropsValues))
			$arOrderPropsValues = array();

		$arUser = null;

		$arFilter = [
			"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
			"ACTIVE" => "Y"
		];

		$relationFilter = [];
		if ($paysystemId > 0)
		{
			$relationFilter[] = [
				'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_TYPE' => 'P',
				'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID' => $paysystemId,
			];
		}

		if (strlen($deliveryId) > 0)
		{
			if ($paysystemId > 0)
			{
				$relationFilter['LOGIC'] = 'OR';
			}

			$relationFilter[] = [
				'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_TYPE' => 'D',
				'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID' => \CSaleDelivery::getIdByCode($deliveryId),
			];
		}

		$arFilter[] = [
			'LOGIC' => 'OR',
			$relationFilter,
			[
				'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.PROPERTY_ID' => null
			],
		];

		if (isset($arOptions['ORDER'])
			&& $arOptions['ORDER'] instanceof \Bitrix\Sale\Order
		)
		{
			$registry = \Bitrix\Sale\Registry::getInstance($arOptions['ORDER']::getRegistryType());
			$property = $registry->getPropertyClassName();
		}
		else
		{
			$property = \Bitrix\Sale\Property::class;
		}

		/** @var Bitrix\Main\DB\Result $dbRes */
		$dbRes = $property::getlist([
			'select' => [
				'ID', 'NAME', 'TYPE', 'IS_LOCATION', 'IS_LOCATION4TAX', 'IS_PROFILE_NAME', 'IS_PAYER', 'IS_EMAIL',
				'REQUIRED', 'SORT', 'IS_ZIP', 'CODE', 'DEFAULT_VALUE'
			],
			'filter' => $arFilter,
			'order' => ['SORT' => 'ASC']
		]);

		while ($arOrderProp = $dbRes->fetch())
		{
			$arOrderProp = CSaleOrderPropsAdapter::convertNewToOld($arOrderProp);
			if (!array_key_exists($arOrderProp["ID"], $arOrderPropsValues))
			{
				$curVal = $arOrderProp["DEFAULT_VALUE"];

				if (!is_array($curVal) && strlen($curVal) <= 0)
				{
					if ($arOrderProp["IS_EMAIL"] == "Y" || $arOrderProp["IS_PAYER"] == "Y")
					{
						if ($arUser == null)
						{
							$dbUser = CUser::GetList($by = "ID", $order = "desc", array("ID_EQUAL_EXACT" => $arOrder["USER_ID"]));
							$arUser = $dbUser->Fetch();
						}
						if ($arOrderProp["IS_EMAIL"] == "Y")
							$curVal = is_array($arUser) ? $arUser["EMAIL"] : "";
						elseif ($arOrderProp["IS_PAYER"] == "Y")
							$curVal = is_array($arUser) ? $arUser["NAME"].(strlen($arUser["NAME"]) <= 0 || strlen($arUser["LAST_NAME"]) <= 0 ? "" : " ").$arUser["LAST_NAME"] : "";
					}
				}
			}
			else
			{
				$curVal = $arOrderPropsValues[$arOrderProp["ID"]];
			}

			if ((!is_array($curVal) && strlen($curVal) > 0) || (is_array($curVal) && count($curVal) > 0))
			{
				//if ($arOrderProp["TYPE"] == "SELECT" || $arOrderProp["TYPE"] == "MULTISELECT" || $arOrderProp["TYPE"] == "RADIO")
				if ($arOrderProp["TYPE"] == "SELECT" || $arOrderProp["TYPE"] == "RADIO")
				{
					$arVariants = array();
					$dbVariants = CSaleOrderPropsVariant::GetList(
						array("SORT" => "ASC", "NAME" => "ASC"),
						array("ORDER_PROPS_ID" => $arOrderProp["ID"]),
						false,
						false,
						array("*")
					);
					while ($arVariant = $dbVariants->Fetch())
						$arVariants[] = $arVariant["VALUE"];

					if (!is_array($curVal))
						$curVal = array($curVal);

					$arKeys = array_keys($curVal);
					foreach ($arKeys as $k)
					{
						if (!in_array($curVal[$k], $arVariants))
							unset($curVal[$k]);
					}

					if ($arOrderProp["TYPE"] == "SELECT" || $arOrderProp["TYPE"] == "RADIO")
						$curVal = array_shift($curVal);
				}
				elseif ($arOrderProp["TYPE"] == "LOCATION")
				{
					if (is_array($curVal))
						$curVal = array_shift($curVal);

					if(CSaleLocation::isLocationProMigrated())
					{
						// if we came from places like CRM, we got location in CODEs, because CRM knows nothing about location IDs.
						// so, CRM sends LOCATION_IN_CODES in options array. In the other case, we assume we got locations as IDs
						$res = CSaleLocation::GetById($curVal);
						if(intval($res['ID']))
						{
							$curVal = $res['ID'];
							$locId = $res['ID'];
						}
						else
						{
							$curVal = null;
							$locId = false;
						}
					}
					else // dead branch in 15.5.x
					{
						$dbVariants = CSaleLocation::GetList(
							array(),
							array("ID" => $curVal),
							false,
							false,
							array("ID")
						);
						if ($arVariant = $dbVariants->Fetch())
							$curVal = intval($arVariant["ID"]);
						else
							$curVal = null;
					}
				}
			}

			if ($arOrderProp["TYPE"] == "LOCATION" && ($arOrderProp["IS_LOCATION"] == "Y" || $arOrderProp["IS_LOCATION4TAX"] == "Y"))
			{
				if ($arOrderProp["IS_LOCATION"] == "Y")
					$arOrder["DELIVERY_LOCATION"] = $locId;
				if ($arOrderProp["IS_LOCATION4TAX"] == "Y")
					$arOrder["TAX_LOCATION"] = $locId;

				if (!$locId)
					$bErrorField = true;
			}
			elseif ($arOrderProp["IS_PROFILE_NAME"] == "Y" || $arOrderProp["IS_PAYER"] == "Y" || $arOrderProp["IS_EMAIL"] == "Y" || $arOrderProp["IS_ZIP"] == "Y")
			{
				$curVal = trim($curVal);
				if ($arOrderProp["IS_PROFILE_NAME"] == "Y")
					$arOrder["PROFILE_NAME"] = $curVal;
				if ($arOrderProp["IS_PAYER"] == "Y")
					$arOrder["PAYER_NAME"] = $curVal;
				if ($arOrderProp["IS_ZIP"] == "Y")
					$arOrder["DELIVERY_LOCATION_ZIP"] = $curVal;
				if ($arOrderProp["IS_EMAIL"] == "Y")
				{
					$arOrder["USER_EMAIL"] = $curVal;
					if (!check_email($curVal))
						$arWarnings[] = array("CODE" => "PARAM", "TEXT" => str_replace(array("#EMAIL#", "#NAME#"), array(htmlspecialcharsbx($curVal), htmlspecialcharsbx($arOrderProp["NAME"])), GetMessage("SALE_GOPE_WRONG_EMAIL")));
				}

				if (strlen($curVal) <= 0)
					$bErrorField = true;
			}
			elseif ($arOrderProp["REQUIED"] == "Y")
			{
				if ($arOrderProp["TYPE"] == "TEXT" || $arOrderProp["TYPE"] == "TEXTAREA" || $arOrderProp["TYPE"] == "RADIO" || $arOrderProp["TYPE"] == "SELECT" || $arOrderProp["TYPE"] == "CHECKBOX")
				{
					if (strlen($curVal) <= 0)
						$bErrorField = true;
				}
				elseif ($arOrderProp["TYPE"] == "LOCATION")
				{
					if (intval($curVal) <= 0)
						$bErrorField = true;
				}
				elseif ($arOrderProp["TYPE"] == "MULTISELECT")
				{
					//if (!is_array($curVal) || count($curVal) <= 0)
					if (strlen($curVal) <= 0)
						$bErrorField = true;
				}
				elseif ($arOrderProp["TYPE"] == "FILE")
				{
					if (is_array($curVal))
					{
						foreach ($curVal as $index => $arFileData)
						{
							if (!array_key_exists("name", $arFileData) && !array_key_exists("file_id", $arFileData))
								$bErrorField = true;
						}
					}
					else
					{
						$bErrorField = true;
					}
				}
			}

			if ($bErrorField)
			{
				$arWarnings[] = array("CODE" => "PARAM", "TEXT" => str_replace("#NAME#", htmlspecialcharsbx($arOrderProp["NAME"]), GetMessage("SALE_GOPE_FIELD_EMPTY")));
				$bErrorField = false;
			}

			$arOrder["ORDER_PROP"][$arOrderProp["ID"]] = $curVal;
		}
	}

	/*
	 * Updates/adds order properties' values
	 *
	 * @param array $orderId
	 * @param array $personTypeId
	 * @param array $arOrderProps - array of order properties values
	 * @param array $arErrors
	 */
	static function DoSaveOrderProps($orderId, $personTypeId, $arOrderProps, &$arErrors, $paysystemId = 0, $deliveryId = "")
	{
		$arIDs = array();
		$dbResult = CSaleOrderPropsValue::GetList(
			array(),
			//array("ORDER_ID" => $orderId, "PROP_UTIL" => "N"),
			array("ORDER_ID" => $orderId),
			false,
			false,
			array("ID", "ORDER_PROPS_ID")
		);
		while ($arResult = $dbResult->Fetch())
			$arIDs[$arResult["ORDER_PROPS_ID"]] = $arResult["ID"];

		$arFilter = array(
			"PERSON_TYPE_ID" => $personTypeId,
			"ACTIVE" => "Y"
		);

		if ($paysystemId != 0)
		{
			$arFilter["RELATED"]["PAYSYSTEM_ID"] = $paysystemId;
			$arFilter["RELATED"]["TYPE"] = "WITH_NOT_RELATED";
		}

		if (strlen($deliveryId) > 0)
		{
			$arFilter["RELATED"]["DELIVERY_ID"] = $deliveryId;
			$arFilter["RELATED"]["TYPE"] = "WITH_NOT_RELATED";
		}

		$dbOrderProperties = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			$arFilter,
			false,
			false,
			array("ID", "TYPE", "NAME", "CODE", "USER_PROPS", "SORT")
		);
		while ($arOrderProperty = $dbOrderProperties->Fetch())
		{
			$curVal = $arOrderProps[$arOrderProperty["ID"]];

			if (($arOrderProperty["TYPE"] == "MULTISELECT") && is_array($curVal))
				$curVal = implode(",", $curVal);

			if ($arOrderProperty["TYPE"] == "FILE" && is_array($curVal))
			{
				$tmpVal = "";
				foreach ($curVal as $index => $fileData)
				{
					$bModify = true;
					if (isset($fileData["file_id"])) // existing file
					{
						if (isset($fileData["del"]))
						{
							$arFile = CFile::MakeFileArray($fileData["file_id"]);
							$arFile["del"] = $fileData["del"];
							$arFile["old_file"] = $fileData["file_id"];
						}
						else
						{
							$bModify = false;
							if (strlen($tmpVal) > 0)
								$tmpVal .= ", ".$fileData["file_id"];
							else
								$tmpVal = $fileData["file_id"];
						}
					}
					else // new file array
						$arFile = $fileData;

					if (isset($arFile["name"]) && strlen($arFile["name"]) > 0 && $bModify)
					{
						$arFile["MODULE_ID"] = "sale";
						$fid = CFile::SaveFile($arFile, "sale");
						if (intval($fid) > 0)
						{
							if (strlen($tmpVal) > 0)
								$tmpVal .= ", ".$fid;
							else
								$tmpVal = $fid;
						}
					}
				}

				$curVal = $tmpVal;
			}

			if (strlen($curVal) > 0)
			{
				$arFields = array(
					"ORDER_ID" => $orderId,
					"ORDER_PROPS_ID" => $arOrderProperty["ID"],
					"NAME" => $arOrderProperty["NAME"],
					"CODE" => $arOrderProperty["CODE"],
					"VALUE" => $curVal
				);

				if (array_key_exists($arOrderProperty["ID"], $arIDs))
				{
					CSaleOrderPropsValue::Update($arIDs[$arOrderProperty["ID"]], $arFields);
					unset($arIDs[$arOrderProperty["ID"]]);
				}
				else
				{
					CSaleOrderPropsValue::Add($arFields);
				}
			}
		}

		foreach ($arIDs as $id)
			CSaleOrderPropsValue::Delete($id);
	}

	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		if (!is_array($arOrder) && !is_array($arFilter))
		{
			$arOrder = strval($arOrder);
			$arFilter = strval($arFilter);
			if (strlen($arOrder) > 0 && strlen($arFilter) > 0)
				$arOrder = array($arOrder => $arFilter);
			else
				$arOrder = array();
			if (is_array($arGroupBy))
				$arFilter = $arGroupBy;
			else
				$arFilter = array();
			$arGroupBy = false;

			$arSelectFields = array();
		}

		if (is_array($arFilter))
		{
			$arFilter['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;
		}

		$defaultSelectFields = array(
			"ID",
			"PERSON_TYPE_ID",
			"NAME",
			"TYPE",
			"REQUIED",
			"DEFAULT_VALUE",
			"DEFAULT_VALUE_ORIG",
			"SORT",
			"USER_PROPS",
			"IS_LOCATION",
			"PROPS_GROUP_ID",
			"SIZE1",
			"SIZE2",
			"DESCRIPTION",
			"IS_EMAIL",
			"IS_PROFILE_NAME",
			"IS_PAYER",
			"IS_LOCATION4TAX",
			"IS_ZIP",
			"CODE",
			"IS_FILTERED",
			"ACTIVE",
			"UTIL",
			"INPUT_FIELD_LOCATION",
			"MULTIPLE",
			"PAYSYSTEM_ID",
			"DELIVERY_ID"
		);

		if (! $arSelectFields)
		{
			$arSelectFields = $defaultSelectFields;
		}

		if (is_array($arSelectFields) && in_array("*", $arSelectFields))
		{
			$key = array_search('*', $arSelectFields);
			unset($arSelectFields[$key]);

			$arSelectFields = array_merge($arSelectFields, $defaultSelectFields);

			$arSelectFields = array_unique($arSelectFields);
		}

		// add aliases

		$query = new \Bitrix\Sale\Compatible\OrderQueryLocation(OrderPropsTable::getEntity());
		$query->addLocationRuntimeField('DEFAULT_VALUE');
		$query->addAliases(array(
			'REQUIED'              => 'REQUIRED',
			'GROUP_ID'             => 'GROUP.ID',
			'GROUP_PERSON_TYPE_ID' => 'GROUP.PERSON_TYPE_ID',
			'GROUP_NAME'           => 'GROUP.NAME',
			'GROUP_SORT'           => 'GROUP.SORT',
			'PERSON_TYPE_LID'      => 'PERSON_TYPE.LID',
			'PERSON_TYPE_NAME'     => 'PERSON_TYPE.NAME',
			'PERSON_TYPE_SORT'     => 'PERSON_TYPE.SORT',
			'PERSON_TYPE_ACTIVE'   => 'PERSON_TYPE.ACTIVE',
			'PAYSYSTEM_ID'         => 'Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID',
			'DELIVERY_ID'          => 'Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID',
		));

		// relations

		if (isset($arFilter['RELATED']))
		{
			// 1. filter related to something
			if (is_array($arFilter['RELATED']))
			{
				$relationFilter = array();

				if ($arFilter['RELATED']['PAYSYSTEM_ID'])
					$relationFilter []= array(
						'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_TYPE' => 'P',
						'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID' => $arFilter['RELATED']['PAYSYSTEM_ID'],
					);

				if ($arFilter['RELATED']['DELIVERY_ID'])
				{
					if ($relationFilter)
						$relationFilter['LOGIC'] = $arFilter['RELATED']['LOGIC'] == 'AND' ? 'AND' : 'OR';

					$relationFilter []= array(
						'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_TYPE' => 'D',
						'=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID' => \CSaleDelivery::getIdByCode($arFilter['RELATED']['DELIVERY_ID']),
					);
				}

				// all other
				if ($arFilter['RELATED']['TYPE'] == 'WITH_NOT_RELATED' && $relationFilter)
				{
					$relationFilter = array(
						'LOGIC' => 'OR',
						$relationFilter,
						array('=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.PROPERTY_ID' => null),
					);
				}

				if ($relationFilter)
					$query->addFilter(null, $relationFilter);
			}
			// 2. filter all not related to anything
			else
			{
				$query->addFilter('=Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.PROPERTY_ID', null);

				if (($key = array_search('PAYSYSTEM_ID', $arSelectFields)) !== false)
					unset($arSelectFields[$key]);

				if (($key = array_search('DELIVERY_ID', $arSelectFields)) !== false)
					unset($arSelectFields[$key]);
			}

			unset($arFilter['RELATED']);
		}

		if (isset($arFilter['PERSON_TYPE_ID']) && is_array($arFilter['PERSON_TYPE_ID']))
		{
			foreach ($arFilter['PERSON_TYPE_ID'] as $personTypeKey => $personTypeValue)
			{
				if (!is_array($personTypeValue) && !empty($personTypeValue) && intval($personTypeValue) > 0)
				{
					unset($arFilter['PERSON_TYPE_ID'][$personTypeKey]);
					$arFilter['PERSON_TYPE_ID'][] = $personTypeValue;
				}
			}
		}

		// execute

		$query->prepare($arOrder, $arFilter, $arGroupBy, $arSelectFields);

		if ($query->counted())
		{
			return $query->exec()->getSelectedRowsCount();
		}
		else
		{
			$result = new \Bitrix\Sale\Compatible\CDBResult;
			$adapter = new CSaleOrderPropsAdapter($query, $arSelectFields);
			$adapter->addFieldProxy('DEFAULT_VALUE');
			$result->addFetchAdapter($adapter);
			return $query->compatibleExec($result, $arNavStartParams);
		}
	}

	function GetByID($ID)
	{
		$id = (int) $ID;
		return $id > 0 && $id == $ID
			? self::GetList(array(), array('ID' => $ID))->Fetch()
			: false;
	}

	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION;

		if (is_set($arFields, "PERSON_TYPE_ID") && $ACTION != "ADD")
			UnSet($arFields["PERSON_TYPE_ID"]);

		if ((is_set($arFields, "PERSON_TYPE_ID") || $ACTION=="ADD") && IntVal($arFields["PERSON_TYPE_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGOP_EMPTY_PERS_TYPE"), "ERROR_NO_PERSON_TYPE");
			return false;
		}
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && strlen($arFields["NAME"]) <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGOP_EMPTY_PROP_NAME"), "ERROR_NO_NAME");
			return false;
		}
		if ((is_set($arFields, "TYPE") || $ACTION=="ADD") && strlen($arFields["TYPE"]) <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGOP_EMPTY_PROP_TYPE"), "ERROR_NO_TYPE");
			return false;
		}

		if (is_set($arFields, "REQUIED") && $arFields["REQUIED"]!="Y")
			$arFields["REQUIED"]="N";
		if (is_set($arFields, "USER_PROPS") && $arFields["USER_PROPS"]!="Y")
			$arFields["USER_PROPS"]="N";
		if (is_set($arFields, "IS_LOCATION") && $arFields["IS_LOCATION"]!="Y")
			$arFields["IS_LOCATION"]="N";
		if (is_set($arFields, "IS_LOCATION4TAX") && $arFields["IS_LOCATION4TAX"]!="Y")
			$arFields["IS_LOCATION4TAX"]="N";
		if (is_set($arFields, "IS_EMAIL") && $arFields["IS_EMAIL"]!="Y")
			$arFields["IS_EMAIL"]="N";
		if (is_set($arFields, "IS_PROFILE_NAME") && $arFields["IS_PROFILE_NAME"]!="Y")
			$arFields["IS_PROFILE_NAME"]="N";
		if (is_set($arFields, "IS_PAYER") && $arFields["IS_PAYER"]!="Y")
			$arFields["IS_PAYER"]="N";
		if (is_set($arFields, "IS_FILTERED") && $arFields["IS_FILTERED"]!="Y")
			$arFields["IS_FILTERED"]="N";
		if (is_set($arFields, "IS_ZIP") && $arFields["IS_ZIP"]!="Y")
			$arFields["IS_ZIP"]="N";

		if (is_set($arFields, "IS_LOCATION") && is_set($arFields, "TYPE") && $arFields["IS_LOCATION"]=="Y" && $arFields["TYPE"]!="LOCATION")
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGOP_WRONG_PROP_TYPE"), "ERROR_WRONG_TYPE1");
			return false;
		}
		if (is_set($arFields, "IS_LOCATION4TAX") && is_set($arFields, "TYPE") && $arFields["IS_LOCATION4TAX"]=="Y" && $arFields["TYPE"]!="LOCATION")
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGOP_WRONG_PROP_TYPE"), "ERROR_WRONG_TYPE2");
			return false;
		}

		if ((is_set($arFields, "PROPS_GROUP_ID") || $ACTION=="ADD") && IntVal($arFields["PROPS_GROUP_ID"])<=0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGOP_EMPTY_PROP_GROUP"), "ERROR_NO_GROUP");
			return false;
		}

		if (is_set($arFields, "PERSON_TYPE_ID"))
		{
			if (!($arPersonType = CSalePersonType::GetByID($arFields["PERSON_TYPE_ID"])))
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["PERSON_TYPE_ID"], Loc::getMessage("SKGOP_NO_PERS_TYPE")), "ERROR_NO_PERSON_TYPE");
				return false;
			}
		}

		return true;
	}

	function Add($arFields)
	{
		foreach (GetModuleEvents('sale', 'OnBeforeOrderPropsAdd', true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				return false;

		if (! self::CheckFields('ADD', $arFields))
			return false;

		$newProperty = CSaleOrderPropsAdapter::convertOldToNew($arFields);
		$fields = array_intersect_key($newProperty, CSaleOrderPropsAdapter::$allFields);
		$fields['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;

		$ID = OrderPropsTable::add($fields)->getId();

		foreach(GetModuleEvents('sale', 'OnOrderPropsAdd', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	function Update($ID, $arFields)
	{
		if (! $ID)
			return false;

		foreach (GetModuleEvents('sale', 'OnBeforeOrderPropsUpdate', true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields)) === false)
				return false;

		if (! self::CheckFields('UPDATE', $arFields, $ID))
			return false;

		$oldFields = self::GetList(array(), array('ID' => $ID), false, false, array('SETTINGS', '*' ))->Fetch();
		$propertyFields = $arFields + $oldFields;

		$newProperty = CSaleOrderPropsAdapter::convertOldToNew($propertyFields);
		OrderPropsTable::update($ID, array_intersect_key($newProperty, CSaleOrderPropsAdapter::$allFields));

		foreach(GetModuleEvents('sale', 'OnOrderPropsUpdate', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}

	function Delete($ID)
	{
		if (! $ID)
			return false;

		foreach (GetModuleEvents('sale', 'OnBeforeOrderPropsDelete', true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID)) === false)
				return false;

		global $DB;

		$DB->Query("DELETE FROM b_sale_order_props_variant WHERE ORDER_PROPS_ID = ".$ID, true);
		$DB->Query("UPDATE b_sale_order_props_value SET ORDER_PROPS_ID = NULL WHERE ORDER_PROPS_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_sale_user_props_value WHERE ORDER_PROPS_ID = ".$ID, true);
		$DB->Query("DELETE FROM b_sale_order_props_relation WHERE PROPERTY_ID = ".$ID, true);
		CSaleOrderUserProps::ClearEmpty();

		foreach(GetModuleEvents('sale', 'OnOrderPropsDelete', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		return $DB->Query("DELETE FROM b_sale_order_props WHERE ID = ".$ID, true);
	}

	function GetRealValue($propertyID, $propertyCode, $propertyType, $value, $lang = false)
	{
		$propertyID = IntVal($propertyID);
		$propertyCode = Trim($propertyCode);
		$propertyType = Trim($propertyType);

		if ($lang === false)
			$lang = LANGUAGE_ID;

		$arResult = array();

		$curKey = ((strlen($propertyCode) > 0) ? $propertyCode : $propertyID);

		if ($propertyType == "SELECT" || $propertyType == "RADIO")
		{
			$arValue = CSaleOrderPropsVariant::GetByValue($propertyID, $value);
			$arResult[$curKey] = $arValue["NAME"];
		}
		elseif ($propertyType == "MULTISELECT")
		{
			$curValue = "";

			if (!is_array($value))
				$value = explode(",", $value);

			for ($i = 0, $max = count($value); $i < $max; $i++)
			{
				if ($arValue1 = CSaleOrderPropsVariant::GetByValue($propertyID, $value[$i]))
				{
					if ($i > 0)
						$curValue .= ",";
					$curValue .= $arValue1["NAME"];
				}
			}

			$arResult[$curKey] = $curValue;
		}
		elseif ($propertyType == "LOCATION")
		{
			if(CSaleLocation::isLocationProMigrated())
			{
				$curValue = '';
				if(strlen($value))
				{
					$arValue = array();

					if(intval($value))
					{
						try
						{
							$locationStreetPropertyValue = '';
							$res = \Bitrix\Sale\Location\LocationTable::getPathToNode($value, array('select' => array('LNAME' => 'NAME.NAME', 'TYPE_ID'), 'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID)));
							$types = \Bitrix\Sale\Location\Admin\TypeHelper::getTypeCodeIdMapCached();
							$path = array();
							while($item = $res->fetch())
							{
								// copy street to STREET property
								if($types['ID2CODE'][$item['TYPE_ID']] == 'STREET')
									$arResult[$curKey."_STREET"] = $item['LNAME'];

								if($types['ID2CODE'][$item['TYPE_ID']] == 'COUNTRY')
									$arValue["COUNTRY_NAME"] = $item['LNAME'];

								if($types['ID2CODE'][$item['TYPE_ID']] == 'REGION')
									$arValue["REGION_NAME"] = $item['LNAME'];

								if($types['ID2CODE'][$item['TYPE_ID']] == 'CITY')
									$arValue["CITY_NAME"] = $item['LNAME'];

								if($types['ID2CODE'][$item['TYPE_ID']] == 'VILLAGE')
									$arResult[$curKey."_VILLAGE"] = $item['LNAME'];

								$path[] = $item['LNAME'];
							}

							$curValue = implode(' - ', $path);
						}
						catch(\Bitrix\Main\SystemException $e)
						{
						}
					}
				}
			}
			else
			{
				$arValue = CSaleLocation::GetByID($value, $lang);
				$curValue = $arValue["COUNTRY_NAME"].((strlen($arValue["COUNTRY_NAME"])<=0 || strlen($arValue["REGION_NAME"])<=0) ? "" : " - ").$arValue["REGION_NAME"].((strlen($arValue["COUNTRY_NAME"])<=0 || strlen($arValue["CITY_NAME"])<=0) ? "" : " - ").$arValue["CITY_NAME"];
			}

			$arResult[$curKey] = $curValue;
			$arResult[$curKey."_COUNTRY"] = $arValue["COUNTRY_NAME"];
			$arResult[$curKey."_REGION"] = $arValue["REGION_NAME"];
			$arResult[$curKey."_CITY"] = $arValue["CITY_NAME"];
		}
		else
		{
			$arResult[$curKey] = $value;
		}

		return $arResult;
	}

	/*
	 * Get order property relations
	 *
	 * @param array $arFilter with keys: PROPERTY_ID, ENTITY_ID, ENTITY_TYPE
	 * @return dbResult
	 */
	function GetOrderPropsRelations($arFilter = array())
	{
		global $DB;

		$strSqlSearch = "";

		foreach ($arFilter as $key => $val)
		{
			$val = $DB->ForSql($val);

			switch(ToUpper($key))
			{
				case "PROPERTY_ID":
					$strSqlSearch .= " AND PROPERTY_ID = '".trim($val)."' ";
					break;
				case "ENTITY_ID":
					$strSqlSearch .= " AND ENTITY_ID = '".trim($val)."' ";
					break;
				case "ENTITY_TYPE":
					$strSqlSearch .= " AND ENTITY_TYPE = '".trim($val)."' ";
					break;
			}
		}

		$strSql =
			"SELECT * ".
			"FROM b_sale_order_props_relation ".
			"WHERE 1 = 1";

		if (strlen($strSqlSearch) > 0)
			$strSql .= " ".$strSqlSearch;

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $dbRes;
	}

	/*
	 * Update order property relations
	 *
	 * @param int $ID - property id
	 * @param array $arEntityIDs - array of IDs entities (payment or delivery systems)
	 * @param string $entityType - P/D (payment or delivery systems)
	 * @return dbResult
	 */
	function UpdateOrderPropsRelations($ID, $arEntityIDs, $entityType)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		$strUpdate = "";
		$arFields = array();

		foreach ($arEntityIDs as &$id)
		{
			$id = $DB->ForSql($id);
		}
		unset($id);

		$entityType = $DB->ForSql($entityType, 1);

		$DB->Query("DELETE FROM b_sale_order_props_relation WHERE PROPERTY_ID = '".$DB->ForSql($ID)."' AND ENTITY_TYPE = '".$entityType."'");

		foreach ($arEntityIDs as $val)
		{
			if (strval(trim($val)) == '')
				continue;

			$arTmp = array("ENTITY_ID" => $val, "ENTITY_TYPE" => $entityType);
			$arInsert = $DB->PrepareInsert("b_sale_order_props_relation", $arTmp);

			$strSql =
				"INSERT INTO b_sale_order_props_relation (PROPERTY_ID, ".$arInsert[0].") ".
				"VALUES('".$ID."', ".$arInsert[1].")";

			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return true;
	}

	public static function PrepareRelation4Where($val, $key, $operation, $negative, $field, &$arField, &$arFilter)
	{
		return false;
	}
}

/** @deprecated */
final class CSaleOrderPropsAdapter implements FetchAdapter
{
	private $select;

	private $fieldProxy = array();

	function __construct(OrderQuery $query, array $select)
	{
		$this->select = $query->getSelectNamesAssoc() + array_flip($select);

		if (! $query->aggregated())
		{
			$query->addAliasSelect('TYPE');
			$query->addAliasSelect('SETTINGS');
			$query->addAliasSelect('MULTIPLE');
			$query->registerRuntimeField('PROPERTY_ID', new Entity\ExpressionField('PROPERTY_ID', 'DISTINCT(%s)', 'ID'));
			$sel = $query->getSelect();
			array_unshift($sel, 'PROPERTY_ID');
			$query->setSelect($sel);
		}
	}

	public function addFieldProxy($field)
	{
		if((string) $field == '')
			return false;

		$this->fieldProxy['PROXY_'.$field] = $field;

		return true;
	}

	public function adapt(array $newProperty)
	{
		if(is_array($newProperty))
		{
			foreach($newProperty as $k => $v)
			{
				if(isset($this->fieldProxy[$k]))
				{
					unset($newProperty[$k]);
					$newProperty[$this->fieldProxy[$k]] = $v;
				}
			}
		}

		$oldProperty = self::convertNewToOld($newProperty);
		if (array_key_exists('VALUE', $newProperty))
		{
			$oldProperty['VALUE'] = self::getOldValue($newProperty['VALUE'], $newProperty['TYPE']);
		}

		return array_intersect_key($oldProperty, $this->select);
	}

	static function getOldValue($value, $type)
	{
		if (is_array($value))
		{
			switch ($type)
			{
				case 'ENUM': $value = implode(',', $value); break;
				case 'FILE': $value = implode(', ', $value); break;
				default    : $value = reset($value);
			}
		}

		return $value;
	}

	static public function convertNewToOld(array $property)
	{
		if (isset($property['REQUIRED']) && !empty($property['REQUIRED']))
			$property['REQUIED'] = $property['REQUIRED'];

		$settings = $property['SETTINGS'];

		switch ($property['TYPE'])
		{
			case 'STRING':

				if ($settings['MULTILINE'] == 'Y')
				{
					$property['TYPE'] = 'TEXTAREA';
					$property['SIZE1'] = $settings['COLS'];
					$property['SIZE2'] = $settings['ROWS'];
				}
				else
				{
					$property['TYPE'] = 'TEXT';
					$property['SIZE1'] = $settings['SIZE'];
				}

				break;

			case 'Y/N':

				$property['TYPE'] = 'CHECKBOX';

				break;

			case 'DATE':

				$property['TYPE'] = 'DATE';

				break;

			case 'FILE':

				$property['TYPE'] = 'FILE';

				break;

			case 'ENUM':

				if ($property['MULTIPLE'] == 'Y')
				{
					$property['TYPE'] = 'MULTISELECT';
					$property['SIZE1'] = $settings['SIZE'];
				}
				elseif ($settings['MULTIELEMENT'] == 'Y')
				{
					$property['TYPE'] = 'RADIO';
				}
				else
				{
					$property['TYPE'] = 'SELECT';
					$property['SIZE1'] = $settings['SIZE'];
				}

				break;

			case 'LOCATION':

				$property['SIZE1'] = $settings['SIZE'];

				break;

			default: $property['TYPE'] = 'TEXT';
		}

		return $property;
	}

	// M I G R A T I O N

	static function convertOldToNew(array $property)
	{
		if (isset($property['REQUIED']) && !empty($property['REQUIED']))
			$property['REQUIRED'] = $property['REQUIED'];

		$size1 = intval($property['SIZE1']);
		$size2 = intval($property['SIZE2']);

		$settings = array();

		// TODO remove sale/include.php - $GLOBALS["SALE_FIELD_TYPES"]
		switch ($property['TYPE'])
		{
			case 'TEXT':

				$property['TYPE'] = 'STRING';

				if ($size1 > 0)
					$settings['SIZE'] = $size1;

				break;

			case 'TEXTAREA':

				$property['TYPE'] = 'STRING';

				$settings['MULTILINE'] = 'Y';

				if ($size1 > 0)
					$settings['COLS'] = $size1;

				if ($size2 > 0)
					$settings['ROWS'] = $size2;

				break;

			case 'CHECKBOX':

				$property['TYPE'] = 'Y/N';

				break;

			case 'RADIO':

				$property['TYPE'] = 'ENUM';

				$settings['MULTIELEMENT'] = 'Y';

				break;

			case 'SELECT':

				$property['TYPE'] = 'ENUM';

				if ($size1 > 0)
					$settings['SIZE'] = $size1;

				break;

			case 'MULTISELECT':

				$property['TYPE'] = 'ENUM';

				$property['MULTIPLE'] = 'Y';

				if ($size1 > 0)
					$settings['SIZE'] = $size1;

				break;

			case 'LOCATION':

				// ID came, should store CODE
				if (intval($property['DEFAULT_VALUE']))
				{
					$res = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=ID' => intval($property['DEFAULT_VALUE'])), 'select' => array('CODE')))->fetch();
					if(is_array($res) && (string) $res['CODE'] != '')
					{
						$property['DEFAULT_VALUE'] = $res['CODE'];
					}
				}

				if ($size1 > 0)
					$settings['SIZE'] = $size1;

				break;
		}

		$propertySettings = array();
		if (isset($property['SETTINGS']) && is_array($property['SETTINGS']))
		{
			$propertySettings = $property['SETTINGS'];
		}

		$property['SETTINGS'] = $propertySettings + $settings;

		return $property;
	}

	static $allFields = array(
		'PERSON_TYPE_ID'=>1, 'NAME'=>1, 'TYPE'=>1, 'REQUIRED'=>1, 'DEFAULT_VALUE'=>1, 'SORT'=>1, 'USER_PROPS'=>1,
		'IS_LOCATION'=>1, 'PROPS_GROUP_ID'=>1, 'DESCRIPTION'=>1, 'IS_EMAIL'=>1, 'IS_PROFILE_NAME'=>1, 'IS_PAYER'=>1,
		'IS_LOCATION4TAX'=>1, 'IS_FILTERED'=>1, 'CODE'=>1, 'IS_ZIP'=>1, 'IS_PHONE'=>1, 'ACTIVE'=>1, 'UTIL'=>1,
		'INPUT_FIELD_LOCATION'=>1, 'MULTIPLE'=>1, 'IS_ADDRESS'=>1, 'SETTINGS'=>1,
	);

	static function migrate()
	{
		$correctFields = array(
			'REQUIRED',
			'USER_PROPS',
			'ACTIVE',
			'UTIL',
			'MULTIPLE',
		);

		$errors = '';
		$result = Application::getConnection()->query('SELECT * FROM b_sale_order_props ORDER BY ID ASC');

		while ($oldProperty = $result->fetch())
		{
			$newProperty = self::convertOldToNew($oldProperty);
			$newProperty['IS_ADDRESS'] = 'N'; // fix oracle's mb default

			foreach ($newProperty as $key => $value)
			{
				if (strpos($key, 'IS_') === 0)
				{
					$newProperty[$key] = ToUpper($value);
				}
				elseif(in_array($key, $correctFields))
				{
					$newProperty[$key] = ToUpper($value);
				}
			}

			$update = OrderPropsTable::update($newProperty['ID'], array_intersect_key($newProperty, self::$allFields));

			if ($update->isSuccess())
			{
				//////CSaleOrderPropsValueAdapter::migrate($oldProperty);
			}
			else
			{
				$errors .= 'cannot update property: '.$oldProperty['ID']."\n".implode("\n", $update->getErrorMessages())."\n\n";
			}
		}

		if ($errors)
			throw new SystemException($errors, 0, __FILE__, __LINE__);
	}
}
