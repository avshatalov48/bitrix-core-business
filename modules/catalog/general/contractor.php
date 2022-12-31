<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\v2\Contractor;

Loc::loadMessages(__FILE__);

class CAllCatalogContractor
{
	protected static function checkFields($action, &$arFields)
	{
		$personType = intval($arFields["PERSON_TYPE"]);

		if($personType == CONTRACTOR_JURIDICAL && is_set($arFields, "COMPANY") && $arFields["COMPANY"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(Loc::getMessage("CC_EMPTY_COMPANY"));
			return false;
		}
		if (((($action == 'ADD' || is_set($arFields, "PERSON_NAME")) && $arFields["PERSON_NAME"] == '') && $personType == CONTRACTOR_INDIVIDUAL))
		{
			$GLOBALS["APPLICATION"]->ThrowException(Loc::getMessage("CC_WRONG_PERSON_LASTNAME"));
			return false;
		}
		if (($action == 'UPDATE') && is_set($arFields, "ID"))
			unset($arFields["ID"]);

		return true;
	}

	public static function update($id, $arFields)
	{
		global $DB, $APPLICATION;

		if (Contractor\Provider\Manager::getActiveProvider())
		{
			$APPLICATION->throwException('This API has been deprecated and is no longer available');
			return false;
		}

		$id = intval($id);

		if(array_key_exists('DATE_CREATE', $arFields))
			unset($arFields['DATE_CREATE']);
		if(array_key_exists('DATE_MODIFY', $arFields))
			unset($arFields['DATE_MODIFY']);
		if(array_key_exists('CREATED_BY', $arFields))
			unset($arFields['CREATED_BY']);

		$arFields['~DATE_MODIFY'] = $DB->GetNowFunction();

		if($id <= 0 || !self::checkFields('UPDATE', $arFields))
			return false;
		$strUpdate = $DB->PrepareUpdate("b_catalog_contractor", $arFields);

		if(!empty($strUpdate))
		{
			$strSql = "UPDATE b_catalog_contractor SET ".$strUpdate." WHERE ID = ".$id." ";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return $id;
	}

	public static function delete($id)
	{
		global $DB, $APPLICATION;

		if (Contractor\Provider\Manager::getActiveProvider())
		{
			$APPLICATION->throwException('This API has been deprecated and is no longer available');
			return false;
		}

		$id = intval($id);
		if($id > 0)
		{
			$dbDocument = CCatalogDocs::getList(array(), array("CONTRACTOR_ID" => $id));
			if($arDocument = $dbDocument->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(Loc::getMessage("CC_CONTRACTOR_HAVE_DOCS_EXT"));
				return false;
			}

			return $DB->Query("DELETE FROM b_catalog_contractor WHERE ID = ".$id." ", true);
		}
		return false;
	}
}
