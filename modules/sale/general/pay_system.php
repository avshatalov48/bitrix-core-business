<?
use \Bitrix\Sale\Internals\PaySystemActionTable;

IncludeModuleLangFile(__FILE__);

/** @deprecated */
class CAllSalePaySystem
{
	static function DoProcessOrder(&$arOrder, $paySystemId, &$arErrors)
	{
		if (intval($paySystemId) > 0)
		{
			$arPaySystem = array();

			$dbPaySystem = CSalePaySystem::GetList(
				array("SORT" => "ASC", "PSA_NAME" => "ASC"),
				array(
					"ACTIVE" => "Y",
					"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
					"PSA_HAVE_PAYMENT" => "Y"
				)
			);

			while ($arPaySystem = $dbPaySystem->Fetch())
			{
				if ($arPaySystem["ID"] == $paySystemId)
				{
					$arOrder["PAY_SYSTEM_ID"] = $paySystemId;

					$arOrder["PAY_SYSTEM_PRICE"] = CSalePaySystemsHelper::getPSPrice(
						$arPaySystem,
						$arOrder["ORDER_PRICE"],
						$arOrder["PRICE_DELIVERY"],
						$arOrder["DELIVERY_LOCATION"]
					);
					break;
				}
			}

			if (empty($arPaySystem))
			{
				$arErrors[] = array("CODE" => "CALCULATE", "TEXT" => GetMessage('SKGPS_PS_NOT_FOUND'));
			}
		}
	}

	public static function DoLoadPaySystems($personType, $deliveryId = 0, $arDeliveryMap = null)
	{
		$arResult = array();

		$arFilter = array(
			"ACTIVE" => "Y",
			"PERSON_TYPE_ID" => $personType,
			"PSA_HAVE_PAYMENT" => "Y"
		);

		// $arDeliveryMap = array(array($deliveryId => 8), array($deliveryId => array(34, 22)), ...)
		if (is_array($arDeliveryMap) && (count($arDeliveryMap) > 0))
		{
			foreach ($arDeliveryMap as $val)
			{
				if (is_array($val[$deliveryId]))
				{
					foreach ($val[$deliveryId] as $v)
						$arFilter["ID"][] = $v;
				}
				elseif (intval($val[$deliveryId]) > 0)
					$arFilter["ID"][] = $val[$deliveryId];
			}
		}
		$dbPaySystem = CSalePaySystem::GetList(
			array("SORT" => "ASC", "PSA_NAME" => "ASC"),
			$arFilter
		);
		while ($arPaySystem = $dbPaySystem->GetNext())
			$arResult[$arPaySystem["ID"]] = $arPaySystem;

		return $arResult;
	}

	function GetByID($id, $personTypeId = 0)
	{
		$id = (int)$id;
		$personTypeId = (int)$personTypeId;

		if ($personTypeId > 0)
		{
			$select = array_merge(array('ID', 'NAME', 'DESCRIPTION', 'ACTIVE', 'SORT'), self::getAliases());

			$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getList(array(
				'select' => $select,
				'filter' => array('ID' => $id)
			));
		}
		else
		{
			$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getById($id);
		}

		if ($result = $dbRes->fetch())
		{
			$map = CSalePaySystemAction::getOldToNewHandlersMap();
			$key = array_search($result['ACTION_FILE'], $map);

			if ($key !== false)
				$result['ACTION_FILE'] = $key;

			return $result;
		}

		return false;
	}

	protected static function getAliases()
	{
		$aliases = array(
			"PSA_ID" => 'ID',
			"PSA_ACTION_FILE" => 'ACTION_FILE',
			"PSA_RESULT_FILE" => 'RESULT_FILE',
			"PSA_NEW_WINDOW" => 'NEW_WINDOW',
			"PSA_PERSON_TYPE_ID" => 'PERSON_TYPE_ID',
			"PSA_PARAMS" => 'PARAMS',
			"PSA_TARIF" => 'TARIF',
			"PSA_HAVE_PAYMENT" => 'HAVE_PAYMENT',
			"PSA_HAVE_ACTION" => 'HAVE_ACTION',
			"PSA_HAVE_RESULT" => 'HAVE_RESULT',
			"PSA_HAVE_PREPAY" => 'HAVE_PREPAY',
			"PSA_HAVE_RESULT_RECEIVE" => 'HAVE_RESULT_RECEIVE',
			"PSA_ENCODING" => 'ENCODING',
			"PSA_LOGOTIP" => 'LOGOTIP'
		);
		return $aliases;
	}

	function CheckFields($ACTION, &$arFields)
	{
		global $DB, $USER;

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGPS_EMPTY_NAME"), "ERROR_NO_NAME");
			return false;
		}

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"] = "N";
		if (is_set($arFields, "SORT") && intval($arFields["SORT"])<=0)
			$arFields["SORT"] = 100;

		return True;
	}

	function Update($id, $arFields)
	{
		if (isset($arFields['LID']))
			unset($arFields['LID']);

		if (isset($arFields['CURRENCY']))
			unset($arFields['CURRENCY']);

		$id = (int)$id;

		if (!CSalePaySystem::CheckFields("UPDATE", $arFields))
			return false;

		return CSalePaySystemAction::Update($id, $arFields);
	}

	function Delete($id)
	{
		$id = (int)$id;

		$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::getById($id);
		if (!$dbRes->fetch())
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGPS_ORDERS_TO_PAYSYSTEM"), "ERROR_ORDERS_TO_PAYSYSTEM");
			return false;
		}

		$dbRes = \Bitrix\Sale\Internals\PaySystemActionTable::delete($id);

		return $dbRes->isSuccess();
	}

	public static function getNewIdsFromOld($ids, $personTypeId = null)
	{
		$dbRes = PaySystemActionTable::getList(array(
			'select' => array('ID'),
			'filter' => array('PAY_SYSTEM_ID' => $ids)
		));

		$data = array();
		while ($ps = $dbRes->fetch())
		{
			if (!is_null($personTypeId))
			{
				$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
					'filter' => array(
						'SERVICE_ID' => $ps['ID'],
						'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
						'=CLASS_NAME' => '\\'.\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType::class
					)
				));

				while ($restriction = $dbRestriction->fetch())
				{
					if (!in_array($personTypeId, $restriction['PARAMS']['PERSON_TYPE_ID']))
						continue(2);
				}
			}

			$data[] = $ps['ID'];
		}

		return $data;
	}

	public static function getPaySystemPersonTypeIds($paySystemId)
	{
		$data = array();

		$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
			'filter' => array(
				'SERVICE_ID' => $paySystemId,
				'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
				'=CLASS_NAME' => '\\'.\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType::class
			)
		));
		while ($restriction = $dbRestriction->fetch())
			$data = array_merge($data, $restriction['PARAMS']['PERSON_TYPE_ID']);

		return $data;
	}

	public static function GetList($arOrder = array("SORT" => "ASC", "NAME" => "ASC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		if (array_key_exists("PSA_PERSON_TYPE_ID", $arFilter))
		{
			$arFilter['PERSON_TYPE_ID'] = $arFilter['PSA_PERSON_TYPE_ID'];
			unset($arFilter["PSA_PERSON_TYPE_ID"]);
		}

		$salePaySystemFields = array('ID', 'NAME', 'ACTIVE', 'SORT', 'DESCRIPTION');
		$ignoredFields = array('LID', 'CURRENCY', 'PERSON_TYPE_ID');

		if (!$arSelectFields)
		{
			$select = array('ID', 'NAME', 'ACTIVE', 'SORT', 'DESCRIPTION');
		}
		else
		{
			$select = array();
			foreach ($arSelectFields as $key => $field)
			{
				if (in_array($field, $ignoredFields))
					continue;

				$select[$key] = self::getAlias($field);
			}
		}

		$filter = array();
		foreach ($arFilter as $key => $value)
		{
			if (in_array($key, $ignoredFields))
				continue;

			$filter[self::getAlias($key)] = $value;
		}

		if (isset($arFilter['PERSON_TYPE_ID']))
			$select = array_merge($select, array('PSA_ID' => 'ID', 'PSA_NAME', 'ACTION_FILE', 'RESULT_FILE', 'NEW_WINDOW', 'PERSON_TYPE_ID', 'PARAMS', 'TARIF', 'HAVE_PAYMENT', 'HAVE_ACTION', 'HAVE_RESULT', 'HAVE_PREPAY', 'HAVE_RESULT_RECEIVE', 'ENCODING', 'LOGOTIP'));

		if (in_array('PARAMS', $select) && !array_key_exists('PSA_ID', $select))
			$select['PSA_ID'] = 'ID';

		if (in_array('PARAMS', $select) && !in_array('PERSON_TYPE_ID', $select))
			$select[] = 'PERSON_TYPE_ID';

		$order = array();
		foreach ($arOrder as $key => $value)
			$order[self::getAlias($key)] = $value;

		$groupBy = array();
		if ($arGroupBy !== false)
		{
			$arGroupBy = !is_array($arGroupBy) ? array($arGroupBy) : $arGroupBy;

			foreach ($arGroupBy as $key => $value)
				$groupBy[$key] = self::getAlias($value);
		}
		$dbRes = PaySystemActionTable::getList(
			array(
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'group' => $groupBy,
			)
		);

		$limit = null;
		if (is_array($arNavStartParams) && isset($arNavStartParams['nTopCount']))
		{
			if ($arNavStartParams['nTopCount'] > 0)
				$limit = $arNavStartParams['nTopCount'];
		}

		$result = array();

		while ($data = $dbRes->fetch())
		{
			if ($limit !== null && !$limit)
				break;

			$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
				'filter' => array(
					'SERVICE_ID' => $data['ID'],
					'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT
				)
			));

			while ($restriction = $dbRestriction->fetch())
			{
				if (!CSalePaySystemAction::checkRestriction($restriction, $arFilter))
					continue(2);
			}

			if (isset($data['ACTION_FILE']))
			{
				$oldHandler = array_search($data['ACTION_FILE'], CSalePaySystemAction::getOldToNewHandlersMap());
				if ($oldHandler !== false)
					$data['ACTION_FILE'] = $oldHandler;
			}

			if (array_key_exists('PARAMS', $data))
			{
				$params = CSalePaySystemAction::getParamsByConsumer('PAYSYSTEM_'.$data['PSA_ID'], $data['PERSON_TYPE_ID']);
				$params['BX_PAY_SYSTEM_ID'] = array('TYPE' => '', 'VALUE' => $data['PSA_ID']);
				$data['PARAMS'] = serialize($params);
			}

			foreach ($data as $key => $value)
			{
				if (!in_array($key, $salePaySystemFields))
				{
					$newKey = self::getAliasBack($key);
					if ($newKey != $key)
					{
						$data[$newKey] = $value;
						unset($data[$key]);
					}
				}
			}

			$result[] = $data;
			$limit--;
		}

		$dbRes = new \CDBResult();
		$dbRes->InitFromArray($result);

		return $dbRes;
	}

	private static function getAlias($key)
	{
		$prefix = '';
		$pos = mb_strpos($key, 'PSA_');
		if ($pos > 0)
		{
			$prefix = mb_substr($key, 0, $pos);
			$key = mb_substr($key, $pos);
		}

		$aliases = self::getAliases();

		if (isset($aliases[$key]))
			$key = $aliases[$key];

		return $prefix.$key;
	}

	private static function getAliasBack($value)
	{
		$aliases = self::getAliases();
		$result = array_search($value, $aliases);

		return $result !== false ?  $result : $value;
	}

	/**
	 * @param $arFields
	 * @return bool|int
	 * @throws Exception
	 */
	public static function Add($arFields)
	{
		if (isset($arFields['LID']))
			unset($arFields['LID']);

		if (isset($arFields['CURRENCY']))
			unset($arFields['CURRENCY']);

		if (!CSalePaySystem::CheckFields("ADD", $arFields))
			return false;

		return CSalePaySystemAction::add($arFields);
	}
}
?>