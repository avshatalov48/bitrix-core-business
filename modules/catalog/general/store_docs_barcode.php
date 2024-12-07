<?php

use Bitrix\Catalog;

IncludeModuleLangFile(__FILE__);

class CCatalogStoreDocsBarcodeAll
{
	protected static function checkFields($action, &$arFields)
	{
		if ((($action == 'ADD') || is_set($arFields, "BARCODE")) && ($arFields["BARCODE"] == ''))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("CP_EMPTY_BARCODE"));
			return false;
		}

		return true;
	}

	public static function update($id, $arFields)
	{
		$id=intval($id);

		foreach(GetModuleEvents("catalog", "OnBeforeCatalogStoreDocsBarcodeUpdate", true) as $arEvent)
			if(ExecuteModuleEventEx($arEvent, array($id, &$arFields)) === false)
				return false;

		if($id < 0 || !self::checkFields('UPDATE', $arFields))
			return false;
		global $DB;
		$strUpdate = $DB->PrepareUpdate("b_catalog_docs_barcode", $arFields);
		$strSql = "UPDATE b_catalog_docs_barcode SET ".$strUpdate." WHERE ID = ".$id;
		if(!$DB->Query($strSql, true))
			return false;

		foreach(GetModuleEvents("catalog", "OnStoreDocsBarcodeUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($id, $arFields));
		return true;
	}

	public static function delete($id)
	{
		global $DB;
		$id = intval($id);
		if ($id > 0)
		{
			foreach(GetModuleEvents("catalog", "OnBeforeCatalogStoreDocsBarcodeDelete", true) as $arEvent)
				if(ExecuteModuleEventEx($arEvent, array($id)) === false)
					return false;

			$DB->Query("DELETE FROM b_catalog_docs_barcode WHERE ID = ".$id." ", true);

			foreach (GetModuleEvents("catalog", "OnCatalogStoreDocsBarcodeDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($id));
			return true;
		}
		return false;
	}

	/**
	 * @deprecated
	 * @see Catalog\StoreDocumentBarcodeTable::deleteByDocument
	 *
	 * @param $id
	 * @return bool
	 */
	public static function OnBeforeDocumentDelete($id): bool
	{
		$id = (int)$id;
		Catalog\StoreDocumentBarcodeTable::deleteByDocument($id);

		foreach(GetModuleEvents('catalog', 'OnDocumentBarcodeDelete', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$id]);
		}

		return true;
	}
}
