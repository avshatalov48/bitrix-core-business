<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/general/catalog_import.php");

class CCatalogImport extends CAllCatalogImport
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CCatalogImport::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_catalog_export", $arFields);

		$strSql = "insert into b_catalog_export(".$arInsert[0].") values(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = (int)$DB->LastID();

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if (!CCatalogImport::CheckFields("UPDATE", $arFields))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_export", $arFields);

		if (!empty($strUpdate))
		{
			$strSql = "update b_catalog_export set ".$strUpdate." where ID = ".$ID." and IS_EXPORT = 'N'";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}
}