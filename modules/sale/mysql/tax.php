<?php

require_once __DIR__."/../general/tax.php";

class CSaleTax extends CAllSaleTax
{
	public static function Add($arFields)
	{
		global $DB;

		if (!CSaleTax::CheckFields("ADD", $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_sale_tax", $arFields);
		$strSql =
			"INSERT INTO b_sale_tax(".$arInsert[0].", TIMESTAMP_X) ".
			"VALUES(".$arInsert[1].", ".$DB->GetNowFunction().")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = intval($DB->LastID());

		return $ID;
	}
}
