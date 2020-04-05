<?

use	Bitrix\Sale\Compatible,
	Bitrix\Sale\Internals,
	Bitrix\Main\Entity,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/** @deprecated */
class CSaleOrderPropsValue
{
	function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

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

			$arSelectFields = array("ID", "ORDER_ID", "ORDER_PROPS_ID", "NAME", "VALUE", "VALUE_ORIG", "CODE");
		}

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "ORDER_ID", "ORDER_PROPS_ID", "NAME", "VALUE", "VALUE_ORIG", "CODE");

		// add aliases

		$query = new Compatible\OrderPropertyValuesQuery(Internals\OrderPropsValueTable::getEntity());
		$query->addAliases(array(
			// for GetList
			'VALUE_ORIG'           => 'VALUE',
			'PROP_ID'              => 'PROPERTY.ID',
			'PROP_PERSON_TYPE_ID'  => 'PROPERTY.PERSON_TYPE_ID',
			'PROP_NAME'            => 'PROPERTY.NAME',
			'PROP_TYPE'            => 'PROPERTY.TYPE',
			'PROP_REQUIED'         => 'PROPERTY.REQUIRED',
			'PROP_DEFAULT_VALUE'   => 'PROPERTY.DEFAULT_VALUE',
			'PROP_SORT'            => 'PROPERTY.SORT',
			'PROP_USER_PROPS'      => 'PROPERTY.USER_PROPS',
			'PROP_IS_LOCATION'     => 'PROPERTY.IS_LOCATION',
			'PROP_PROPS_GROUP_ID'  => 'PROPERTY.PROPS_GROUP_ID',
			'PROP_DESCRIPTION'     => 'PROPERTY.DESCRIPTION',
			'PROP_IS_EMAIL'        => 'PROPERTY.IS_EMAIL',
			'PROP_IS_PROFILE_NAME' => 'PROPERTY.IS_PROFILE_NAME',
			'PROP_IS_PAYER'        => 'PROPERTY.IS_PAYER',
			'PROP_IS_LOCATION4TAX' => 'PROPERTY.IS_LOCATION4TAX',
			'PROP_IS_ZIP'          => 'PROPERTY.IS_ZIP',
			'PROP_CODE'            => 'PROPERTY.CODE',
			'PROP_ACTIVE'          => 'PROPERTY.ACTIVE',
			'PROP_UTIL'            => 'PROPERTY.UTIL',
			// for converter
			'TYPE'     => 'PROPERTY.TYPE',
			'SETTINGS' => 'PROPERTY.SETTINGS',
			'MULTIPLE' => 'PROPERTY.MULTIPLE',
			// for GetOrderProps
			'PROPERTY_NAME'        => 'PROPERTY.NAME',
			'PROPS_GROUP_ID'       => 'PROPERTY.PROPS_GROUP_ID',
			'INPUT_FIELD_LOCATION' => 'PROPERTY.INPUT_FIELD_LOCATION',
			'IS_LOCATION'          => 'PROPERTY.IS_LOCATION',
			'IS_EMAIL'             => 'PROPERTY.IS_EMAIL',
			'IS_PROFILE_NAME'      => 'PROPERTY.IS_PROFILE_NAME',
			'IS_PAYER'             => 'PROPERTY.IS_PAYER',
			'IS_ZIP'               => 'PROPERTY.IS_ZIP',
			'ACTIVE'               => 'PROPERTY.ACTIVE',
			'UTIL'                 => 'PROPERTY.UTIL',
			'GROUP_SORT'           => 'PROPERTY.GROUP.SORT',
			'GROUP_NAME'           => 'PROPERTY.GROUP.NAME',
		));

		// relations for GetOrderRelatedProps

		$relationFilter = array();

		if ($arFilter['PAYSYSTEM_ID'])
		{
			$relationFilter []= array(
				'=PROPERTY.Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_TYPE' => 'P',
				'=PROPERTY.Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID' => $arFilter['PAYSYSTEM_ID'],
			);
		}

		if ($arFilter['DELIVERY_ID'])
		{
			$relationFilter['LOGIC'] = 'OR';
			$relationFilter []= array(
				'=PROPERTY.Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_TYPE' => 'D',
				'=PROPERTY.Bitrix\Sale\Internals\OrderPropsRelationTable:lPROPERTY.ENTITY_ID' => $arFilter['DELIVERY_ID'],
			);
		}

		if ($relationFilter)
			$query->addFilter(null, $relationFilter);

		// execute

		$query->prepare($arOrder, $arFilter, $arGroupBy, $arSelectFields);

		if ($query->counted())
		{
			return $query->exec()->getSelectedRowsCount();
		}
		else
		{
			$result = new Compatible\CDBResult;
			$adapter = new CSaleOrderPropsValueAdapter($query->getSelectNamesAssoc() + array_flip($arSelectFields));
			$adapter->addFieldProxy('VALUE');
			$result->addFetchAdapter($adapter);

			if (! $query->aggregated())
			{
				$query->addAliasSelect('TYPE');
				$query->addAliasSelect('SETTINGS');
				$query->addAliasSelect('MULTIPLE');

				if ($relationFilter)
				{
					$query->registerRuntimeField('PROPERTY_ID', new Entity\ExpressionField('PROPERTY_ID', 'DISTINCT(%s)', 'ID'));
					$sel = $query->getSelect();
					array_unshift($sel, 'PROPERTY_ID');
					$query->setSelect($sel);
				}
			}

			return $query->compatibleExec($result, $arNavStartParams);
		}
	}

	function GetByID($ID)
	{
		return $ID
			? self::GetList(array(), array('ID' => $ID))->Fetch()
			: false;
	}

	function GetOrderProps($ORDER_ID)
	{
		return self::GetList(
			array('GROUP_SORT' => 'ASC', 'GROUP_NAME' => 'ASC', 'PROP_SORT' => 'ASC', 'PROPERTY_NAME' => 'ASC', 'PROP_ID' => 'ASC'),
			array('ORDER_ID' => $ORDER_ID),
			false, false,
			array(
				'ID', 'ORDER_ID', 'ORDER_PROPS_ID', 'NAME', 'VALUE', 'CODE',
				'PROPERTY_NAME', 'TYPE', 'PROPS_GROUP_ID', 'INPUT_FIELD_LOCATION', 'IS_LOCATION', 'IS_EMAIL', 'IS_PROFILE_NAME', 'IS_PAYER', 'IS_ZIP', 'ACTIVE', 'UTIL',
				'GROUP_NAME', 'GROUP_SORT',
				'PROP_SORT', 'PROP_ID'
			)
		);
	}

	function GetOrderRelatedProps($ORDER_ID, $arFilter = array())
	{
		if (! is_array($arFilter))
			$arFilter = array();

		return self::GetList(
			array('GROUP_SORT' => 'ASC', 'GROUP_NAME' => 'ASC', 'PROP_SORT' => 'ASC', 'PROPERTY_NAME' => 'ASC', 'PROP_ID' => 'ASC'),
			array('ORDER_ID' => $ORDER_ID, 'PAYSYSTEM_ID' => $arFilter['PAYSYSTEM_ID'], 'DELIVERY_ID' => $arFilter['DELIVERY_ID']),
			false, false,
			array(
				'ID', 'ORDER_ID', 'ORDER_PROPS_ID', 'NAME', 'VALUE', 'CODE',
				'PROPERTY_NAME', 'TYPE', 'PROPS_GROUP_ID', 'INPUT_FIELD_LOCATION', 'IS_LOCATION', 'IS_EMAIL', 'IS_PROFILE_NAME', 'IS_PAYER', 'IS_ZIP', 'ACTIVE', 'UTIL',
				'GROUP_NAME', 'GROUP_SORT',
				'PROP_SORT', 'PROP_ID'
			)
		);
	}

	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "ORDER_ID") || $ACTION=="ADD") && IntVal($arFields["ORDER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPV_EMPTY_ORDER_ID"), "EMPTY_ORDER_ID");
			return false;
		}
		
		if ((is_set($arFields, "ORDER_PROPS_ID") || $ACTION=="ADD") && IntVal($arFields["ORDER_PROPS_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPV_EMPTY_PROP_ID"), "EMPTY_ORDER_PROPS_ID");
			return false;
		}

		if (is_set($arFields, "ORDER_ID"))
		{
			if (!($arOrder = CSaleOrder::GetByID($arFields["ORDER_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["ORDER_ID"], GetMessage("SKGOPV_NO_ORDER_ID")), "ERROR_NO_ORDER");
				return false;
			}
		}

		if (is_set($arFields, "ORDER_PROPS_ID"))
		{
			if (!($arOrder = CSaleOrderProps::GetByID($arFields["ORDER_PROPS_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["ORDER_PROPS_ID"], GetMessage("SKGOPV_NO_PROP_ID")), "ERROR_NO_PROPERY");
				return false;
			}

			if (is_set($arFields, "ORDER_ID"))
			{
				$arFilter = Array(
						"ORDER_ID" => $arFields["ORDER_ID"],
						"ORDER_PROPS_ID" => $arFields["ORDER_PROPS_ID"],
					);
				if(IntVal($ID) > 0)
					$arFilter["!ID"] = $ID;
				$dbP = CSaleOrderPropsValue::GetList(Array(), $arFilter);
				if($arP = $dbP->Fetch())
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPV_DUPLICATE_PROP_ID", Array("#ID#" => $arFields["ORDER_PROPS_ID"], "#ORDER_ID#" => $arFields["ORDER_ID"])), "ERROR_DUPLICATE_PROP_ID");
					return false;
				}
			}
		}

		return true;
	}

	function Add($arFields)
	{
		if (! self::CheckFields('ADD', $arFields, 0))
			return false;

//		if ($arFields['VALUE'] && ($oldProperty = CSaleOrderProps::GetById($arFields['ORDER_PROPS_ID'])))
//		{
//			$oldProperty['VALUE'] = $arFields['VALUE'];
//			$arFields['VALUE'] = CSaleOrderPropsAdapter::convertOldToNew($oldProperty, 'VALUE', true);
//		}

		// location ID to CODE, VALUE is always present
		if((string) $arFields['VALUE'] != '')
			$arFields['VALUE'] = self::translateLocationIDToCode($arFields['VALUE'], $arFields['ORDER_PROPS_ID']);

		if (!empty($arFields['ORDER_PROPS_ID']) && intval($arFields['ORDER_PROPS_ID']) > 0 && !empty($arFields['VALUE']))
		{
			if ($value = self::correctValueToMultiple($arFields['ORDER_PROPS_ID'], $arFields['VALUE']))
			{
				$arFields['VALUE'] = $value;
			}
		}

		return Internals\OrderPropsValueTable::add(array_intersect_key($arFields, CSaleOrderPropsValueAdapter::$allFields))->getId();
	}

	function Update($ID, $arFields)
	{
		if (! self::CheckFields('UPDATE', $arFields, $ID))
			return false;

//		if ($arFields['VALUE'])
//		{
//			if (!  ($propertyId = $arFields['ORDER_PROPS_ID'])
//				&& ($propertyValue = Internals\OrderPropsValueTable::getById($ID)->fetch()))
//			{
//				$propertyId = $propertyValue['ORDER_PROPS_ID'];
//			}
//
//			if ($propertyId && ($oldProperty = CSaleOrderProps::GetById($propertyId)))
//			{
//				$oldProperty['VALUE'] = $arFields['VALUE'];
//				$arFields['VALUE'] = CSaleOrderPropsAdapter::convertOldToNew($oldProperty, 'VALUE', true);
//			}
//		}

		// location ID to CODE
		if((string) $arFields['VALUE'] != '')
		{
			if((string) $arFields['ORDER_PROPS_ID'] != '')
				$propId = intval($arFields['ORDER_PROPS_ID']);
			else
			{
				$propValue = self::GetByID($ID);
				$propId = $propValue['ORDER_PROPS_ID'];
			}

			$arFields['VALUE'] = self::translateLocationIDToCode($arFields['VALUE'], $propId);
		}

		if (!empty($arFields['ORDER_PROPS_ID']) && intval($arFields['ORDER_PROPS_ID']) > 0 && !empty($arFields['VALUE']))
		{
			if ($value = self::correctValueToMultiple($arFields['ORDER_PROPS_ID'], $arFields['VALUE']))
			{
				$arFields['VALUE'] = $value;
			}
		}

		return Internals\OrderPropsValueTable::update($ID, array_intersect_key($arFields, CSaleOrderPropsValueAdapter::$allFields))->getId();
	}

	/**
	 * @param $id
	 * @param $value
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function correctValueToMultiple($id, $value)
	{
		$output = null;

		$res = Internals\OrderPropsTable::getList(array(
													  'select' => array('TYPE', 'MULTIPLE'),
													  'filter' => array(
														  'ID' => $id
													  ),
													  'limit' => 1));
		if($propertyData = $res->fetch())
		{
			if (($propertyData["MULTIPLE"] == 'Y' || $propertyData["TYPE"] == 'MULTISELECT') && !is_array($value))
			{
				$values = explode(',', $value);
				if (!empty($values) && is_array($values))
				{
					$output = array();
					foreach ($values as $value)
					{
						$output[] = trim($value);
					}
				}
			}
		}
		return $output;
	}

	public static function translateLocationIDToCode($id, $orderPropId)
	{
		$prop = CSaleOrderProps::GetByID($orderPropId);
		if(isset($prop['TYPE']) && $prop['TYPE'] == 'LOCATION')
			return CSaleLocation::tryTranslateIDToCode($id);

		return $id;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = IntVal($ID);

		$strSql = "DELETE FROM b_sale_order_props_value WHERE ID = ".$ID." ";
		return $DB->Query($strSql, True);
	}

	function DeleteByOrder($orderID)
	{
		global $DB;
		$orderID = IntVal($orderID);

		$strSql = "DELETE FROM b_sale_order_props_value WHERE ORDER_ID = ".$orderID." ";
		return $DB->Query($strSql, True);
	}
}

/** @deprecated */
final class CSaleOrderPropsValueAdapter implements Compatible\FetchAdapter
{
	private $fieldProxy = array();

	function __construct(array $select)
	{
		$this->select = $select;
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
		if (! isset($newProperty['TYPE']))
			return $newProperty;

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

		$oldProperty = CSaleOrderPropsAdapter::convertNewToOld($newProperty);

		if (array_key_exists('VALUE', $newProperty))
		{
			$oldProperty['VALUE'] = CSaleOrderPropsAdapter::getOldValue($newProperty['VALUE'], $newProperty['TYPE']);
		}

		if (array_key_exists('TYPE', $oldProperty))
		{
			$oldProperty['PROP_TYPE' ] = $oldProperty['TYPE' ];
		}

		if (array_key_exists('SIZE1', $oldProperty))
		{
			$oldProperty['PROP_SIZE1'] = $oldProperty['SIZE1'];
		}

		if (array_key_exists('SIZE2', $oldProperty))
		{
			$oldProperty['PROP_SIZE2'] = $oldProperty['SIZE2'];
		}
		return array_intersect_key($oldProperty, $this->select);
	}

	public static $allFields = array('ORDER_ID'=>1, 'ORDER_PROPS_ID'=>1, 'NAME'=>1, 'VALUE'=>1, 'CODE'=>1);
}
