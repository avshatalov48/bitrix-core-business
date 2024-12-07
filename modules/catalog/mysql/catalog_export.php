<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/catalog_export.php");

class CCatalogExport extends CAllCatalogExport
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CCatalogExport::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_catalog_export", $arFields);

		$strSql = "insert into b_catalog_export(".$arInsert[0].") values(".$arInsert[1].")";
		$DB->Query($strSql);

		$ID = (int)$DB->LastID();

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if (!CCatalogExport::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_export", $arFields);

		if (!empty($strUpdate))
		{
			$strSql = "update b_catalog_export set ".$strUpdate." where ID = ".$ID." and IS_EXPORT = 'Y'";
			$DB->Query($strSql);
		}

		return $ID;
	}
}
