<?
use Bitrix\Catalog;

IncludeModuleLangFile(__FILE__);

class CCatalogStoreProductAll
{
	protected static function CheckFields($action, &$arFields)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		if ((($action == 'ADD') || isset($arFields["STORE_ID"])) && intval($arFields["STORE_ID"])<=0)
		{
			$APPLICATION->ThrowException(GetMessage("CP_EMPTY_STORE"));
			return false;
		}
		if ((($action == 'ADD') || isset($arFields["PRODUCT_ID"])) && intval($arFields["PRODUCT_ID"])<=0)
		{
			$APPLICATION->ThrowException(GetMessage("CP_EMPTY_PRODUCT"));
			return false;
		}
		if  (!is_numeric($arFields["AMOUNT"]))
		{
			$APPLICATION->ThrowException(GetMessage("CP_FALSE_AMOUNT"));
			return false;
		}

		return true;
	}

	/**
	 * @param array $arFields
	 * @return bool|int
	 */
	public static function UpdateFromForm($arFields)
	{
		$rsProps = CCatalogStoreProduct::GetList(array(),array("PRODUCT_ID"=>$arFields['PRODUCT_ID'], "STORE_ID"=>$arFields['STORE_ID']),false,false,array('ID'));
		if($arID = $rsProps->GetNext())
			return self::Update($arID["ID"],$arFields);
		else
			return CCatalogStoreProduct::Add($arFields);
	}

	public static function Update($id, $arFields)
	{
		$id = intval($id);

		foreach(GetModuleEvents("catalog", "OnBeforeStoreProductUpdate", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array($id, &$arFields)) === false)
				return false;

		if($id < 0 || !self::CheckFields('UPDATE', $arFields))
			return false;
		global $DB;

		$strUpdate = $DB->PrepareUpdate("b_catalog_store_product", $arFields);
		$strSql = "UPDATE b_catalog_store_product SET ".$strUpdate." WHERE ID = ".$id;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		foreach(GetModuleEvents("catalog", "OnStoreProductUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($id, $arFields));

		return true;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Product::delete
	 *
	 * @param $productId
	 * @return void
	 */
	public static function OnIBlockElementDelete($productId) {}

	public static function Delete($id)
	{
		global $DB;
		$id = intval($id);
		if($id > 0)
		{
			foreach(GetModuleEvents("catalog", "OnBeforeStoreProductDelete", true) as $arEvent)
				if(ExecuteModuleEventEx($arEvent, array($id)) === false)
					return false;

			$DB->Query("DELETE FROM b_catalog_store_product WHERE ID = ".$id." ", true);

			foreach(GetModuleEvents("catalog", "OnStoreProductDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($id));

			return true;
		}
		return false;
	}

	public static function addToBalanceOfStore($storeId, $productId, $amount)
	{
		$rsProps = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $productId, "STORE_ID" => $storeId), false, false, array('ID', 'AMOUNT'));
		if($arID = $rsProps->Fetch())
		{
			$amount = $arID["AMOUNT"] + $amount;
			return self::Update($arID["ID"], array("AMOUNT" => $amount, "PRODUCT_ID" => $productId, "STORE_ID" => $storeId,));
		}
		else
			return CCatalogStoreProduct::Add(array("PRODUCT_ID" => $productId, "STORE_ID" => $storeId, "AMOUNT" => $amount));
	}
}