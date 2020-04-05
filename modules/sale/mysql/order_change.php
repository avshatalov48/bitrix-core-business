<?

use Bitrix\Sale\Compatible;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/order_change.php");

class CSaleOrderChange extends CAllSaleOrderChange
{
	public function Add($arFields)
	{
		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			CSaleHelper::WriteToLog("CSaleOrderChange - Add", array("arFields" => $arFields), "SOCA1");
		}

		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleOrderChange::CheckFields("ADD", $arFields))
		{
			return false;
		}

		if (!array_key_exists("DATE_CREATE", $arFields))
		{
			$arFields["DATE_CREATE"] = new \Bitrix\Main\Type\DateTime();
		}

		if (!array_key_exists("DATE_MODIFY", $arFields))
		{
			$arFields["DATE_MODIFY"] = new \Bitrix\Main\Type\DateTime();
		}

		$result = \Bitrix\Sale\Internals\OrderChangeTable::add($arFields);
		return (int)$result->getId();
	}

	function Update($ID, $arFields)
	{
		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			CSaleHelper::WriteToLog("CSaleOrderChange - Update", array("ID" => $ID, "arFields" => $arFields), "SOCU2");
		}

		$ID = IntVal($ID);

		foreach ($arFields as $key => $value)
		{
			if (substr($key, 0, 1)=="=")
			{
				$arFields[substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleOrderChange::CheckFields("UPDATE", $arFields))
		{
			return false;
		}

		$arFields['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();

		\Bitrix\Sale\Internals\OrderChangeTable::update($ID, $arFields);

		return $ID;
	}

	function GetList($arOrder = array("ID"=>"DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		if (array_key_exists("DATE_CREATE_FROM", $arFilter))
		{
			$val = $arFilter["DATE_CREATE_FROM"];
			unset($arFilter["DATE_CREATE_FROM"]);
			$arFilter[">=DATE_CREATE"] = $val;
		}
		if (array_key_exists("DATE_CREATE_TO", $arFilter))
		{
			$val = $arFilter["DATE_CREATE_TO"];
			unset($arFilter["DATE_CREATE_TO"]);
			$arFilter["<=DATE_CREATE"] = $val;
		}
		if (array_key_exists("DATE_MODIFY_FROM", $arFilter))
		{
			$val = $arFilter["DATE_MODIFY_FROM"];
			unset($arFilter["DATE_MODIFY_FROM"]);
			$arFilter[">=DATE_MODIFY"] = $val;
		}
		if (array_key_exists("DATE_MODIFY_TO", $arFilter))
		{
			$val = $arFilter["DATE_MODIFY_TO"];
			unset($arFilter["DATE_MODIFY_TO"]);
			$arFilter["<=DATE_MODIFY"] = $val;
		}

		if (count($arSelectFields) <= 0
			|| in_array("*", $arSelectFields)
		)
		{
			$arSelectFields = array("ID", "ORDER_ID", "TYPE", "DATA", "DATE_CREATE", "DATE_MODIFY", "USER_ID", "ENTITY", "ENTITY_ID");
		}

		$query = new Compatible\OrderQuery(static::getEntity());
		$query->prepare($arOrder, $arFilter, $arGroupBy, $arSelectFields);

		if ($query->counted())
		{
			return $query->exec()->getSelectedRowsCount();
		}

		$result = new Compatible\CDBResult();
		return $query->compatibleExec($result, $arNavStartParams);
	}

	protected static function getEntity()
	{
		return \Bitrix\Sale\Internals\OrderChangeTable::getEntity();
	}
}