<?php

class CIBlockFindTools
{
	public static function GetElementID($element_id, $element_code, $section_id, $section_code, $arFilter)
	{
		$element_id = (int)$element_id;
		$element_code = (string)$element_code;
		if ($element_id > 0)
		{
			return $element_id;
		}
		elseif ($element_code != '')
		{
			if (!is_array($arFilter))
				$arFilter = array();
			$arFilter['=CODE'] = $element_code;

			$section_id = (int)$section_id;
			$section_code = (string)$section_code;
			if ($section_id > 0)
				$arFilter['SECTION_ID'] = $section_id;
			elseif ($section_code != '')
				$arFilter["SECTION_CODE"] = $section_code;

			$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
			if ($arElement = $rsElement->Fetch())
				return (int)$arElement["ID"];
		}
		return 0;
	}

	public static function GetSectionID($section_id, $section_code, $arFilter)
	{
		$section_id = (int)$section_id;
		$section_code = (string)$section_code;
		if ($section_id > 0)
		{
			return $section_id;
		}
		elseif ($section_code != '')
		{
			if (!is_array($arFilter))
				$arFilter = array();
			$arFilter['=CODE'] = $section_code;

			$rsSection = CIBlockSection::GetList(array(), $arFilter, false, array("ID"));
			if ($arSection = $rsSection->Fetch())
				return (int)$arSection["ID"];
		}
		return 0;
	}

	public static function GetSectionIDByCodePath($iblock_id, $section_code_path)
	{
		if ($section_code_path == '')
		{
			return 0;
		}
		$arVariables = array(
			"SECTION_CODE_PATH" => $section_code_path,
		);
		return (self::checkSection($iblock_id, $arVariables) ? $arVariables["SECTION_ID"] : 0);
	}

	public static function resolveComponentEngine(CComponentEngine $engine, $pageCandidates, &$arVariables)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION, $CACHE_MANAGER;
		static $aSearch = array("&lt;", "&gt;", "&quot;", "&#039;");
		static $aReplace = array("<", ">", "\"", "'");

		$component = $engine->getComponent();
		if ($component)
		{
			$iblock_id = (int)$component->arParams["IBLOCK_ID"];
			$strict_check = ($component->arParams["DETAIL_STRICT_SECTION_CHECK"] ?? '') === "Y";
		}
		else
		{
			$iblock_id = 0;
			$strict_check = false;
		}

		//To fix GetPagePath security hack for SMART_FILTER_PATH
		foreach ($pageCandidates as $pageID => $arVariablesTmp)
		{
			foreach ($arVariablesTmp as $variableName => $variableValue)
			{
				if ($variableName === "SMART_FILTER_PATH")
					$pageCandidates[$pageID][$variableName] = str_replace($aSearch, $aReplace, $variableValue);
			}
		}

		$requestURL = $APPLICATION->GetCurPage(true);

		$cacheId = $requestURL.implode("|", array_keys($pageCandidates))."|".SITE_ID."|".$iblock_id.$engine->cacheSalt;
		$cache = new CPHPCache;
		if ($cache->StartDataCache(3600, $cacheId, "iblock_find"))
		{
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache("iblock_find");
				CIBlock::registerWithTagCache($iblock_id);
			}

			foreach ($pageCandidates as $pageID => $arVariablesTmp)
			{
				if (
					(isset($arVariablesTmp["SECTION_CODE_PATH"]) && $arVariablesTmp["SECTION_CODE_PATH"] !== "")
					&& (isset($arVariablesTmp["ELEMENT_ID"]) || isset($arVariablesTmp["ELEMENT_CODE"]))
				)
				{
					if (CIBlockFindTools::checkElement($iblock_id, $arVariablesTmp, $strict_check))
					{
						$arVariables = $arVariablesTmp;
						if (defined("BX_COMP_MANAGED_CACHE"))
							$CACHE_MANAGER->EndTagCache();
						$cache->EndDataCache(array($pageID, $arVariablesTmp));
						return $pageID;
					}
				}
			}

			foreach ($pageCandidates as $pageID => $arVariablesTmp)
			{
				if (
					(isset($arVariablesTmp["SECTION_CODE_PATH"]) && $arVariablesTmp["SECTION_CODE_PATH"] !== "")
					&& (!isset($arVariablesTmp["ELEMENT_ID"]) && !isset($arVariablesTmp["ELEMENT_CODE"]))
				)
				{
					if (CIBlockFindTools::checkSection($iblock_id, $arVariablesTmp))
					{
						$arVariables = $arVariablesTmp;
						if (defined("BX_COMP_MANAGED_CACHE"))
							$CACHE_MANAGER->EndTagCache();
						$cache->EndDataCache(array($pageID, $arVariablesTmp));
						return $pageID;
					}
				}
			}

			if (defined("BX_COMP_MANAGED_CACHE"))
				$CACHE_MANAGER->AbortTagCache();
			$cache->AbortDataCache();
		}
		else
		{
			$vars = $cache->GetVars();
			$pageID = $vars[0];
			$arVariables = $vars[1];

			return $pageID;
		}

		reset($pageCandidates);
		$pageID = key($pageCandidates);
		$arVariables = $pageCandidates[$pageID];

		return $pageID;
	}

	public static function checkElement($iblock_id, &$arVariables, $strict_check = false)
	{
		global $DB;

		$select = "BE.ID";

		$strFrom = "
			b_iblock_element BE
		";

		$elementId = $arVariables["ELEMENT_ID"] ?? '';
		$elementCode = $arVariables["ELEMENT_CODE"] ?? '';
		$strWhere = "
			" . ($elementId != "" ? "AND BE.ID = " . (int)$elementId : "") . "
			" . ($elementCode != "" ? "AND BE.CODE = '" . $DB->ForSql($elementCode) . "'" : "") . "
		";

		if (
			isset($arVariables["SECTION_CODE_PATH"])
			&& is_string($arVariables["SECTION_CODE_PATH"])
			&& $arVariables["SECTION_CODE_PATH"] != ""
		)
		{
			$select .= ", BS.ID as SECTION_ID, BS.CODE";
			//The path may be incomplete so we join part of the section tree BS and BSP
			$strFrom .= "
				INNER JOIN b_iblock_section_element BSE ON BSE.IBLOCK_ELEMENT_ID = BE.ID AND BSE.ADDITIONAL_PROPERTY_ID IS NULL
				INNER JOIN b_iblock_section BS ON BS.ID = BSE.IBLOCK_SECTION_ID
				INNER JOIN b_iblock_section BSP ON BS.IBLOCK_ID = BSP.IBLOCK_ID AND BS.LEFT_MARGIN >= BSP.LEFT_MARGIN AND BS.RIGHT_MARGIN <= BSP.RIGHT_MARGIN
			";
			$joinField = "BSP.ID";

			$sectionPath = explode("/", $arVariables["SECTION_CODE_PATH"]);
			// B24 fix
			if (count($sectionPath) > 58) // $strFrom already contains three join (max - 61)
			{
				return false;
			}

			$i = 0;
			foreach (array_reverse($sectionPath) as $i => $SECTION_CODE)
			{
				$strFrom .= "
					INNER JOIN b_iblock_section BS".$i." ON BS".$i.".ID = ".$joinField."
				";
				$joinField = "BS".$i.".IBLOCK_SECTION_ID";
				$strWhere .= "
					AND BS".$i.".CODE = '".$DB->ForSql($SECTION_CODE)."'
				";
			}

			if ($strict_check)
			{
				$strWhere .= "
					AND BS".$i.".IBLOCK_SECTION_ID is null
				";
			}
		}

		$strSql = "
			select ".$select."
			from ".$strFrom."
			WHERE BE.IBLOCK_ID = ".$iblock_id."
			".$strWhere."
		";
		$rs = $DB->Query($strSql);
		$r = $rs->Fetch();
		unset($rs);
		if ($r)
		{
			if (isset($sectionPath) && is_array($sectionPath))
			{
				$arVariables["SECTION_CODE"] = $sectionPath[count($sectionPath) - 1];
				if (isset($r['SECTION_ID']) && isset($r['SECTION_CODE']))
				{
					if ($arVariables["SECTION_CODE"] === $r['SECTION_CODE'])
					{
						$arVariables["SECTION_ID"] = $r['SECTION_ID'];
						$arVariables["ELEMENT_ID"] = $r['ID'];
					}
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	public static function checkSection($iblock_id, &$arVariables)
	{
		global $DB;

		if (
			!isset($arVariables["SECTION_CODE_PATH"])
			|| !is_string($arVariables["SECTION_CODE_PATH"])
		)
		{
			return false;
		}

		$sectionPath = explode("/", $arVariables["SECTION_CODE_PATH"]);

		// B24 fix
		if (count($sectionPath) > 61)
		{
			return false;
		}

		$strFrom = "";
		$joinField = "";
		$strWhere = "";
		$strRoot = "";
		foreach (array_reverse($sectionPath) as $i => $SECTION_CODE)
		{
			if ($i == 0)
			{
				$strFrom .= "
					b_iblock_section BS
				";
				$joinField .= "BS.IBLOCK_SECTION_ID";
				$strWhere .= "
					AND BS.CODE = '".$DB->ForSql($SECTION_CODE)."'
				";
				$strRoot = "AND BS.IBLOCK_SECTION_ID IS NULL";
			}
			else
			{
				$strFrom .= "
					INNER JOIN b_iblock_section BS".$i." ON BS".$i.".ID = ".$joinField."
				";
				$joinField = "BS".$i.".IBLOCK_SECTION_ID";
				$strWhere .= "
					AND BS".$i.".CODE = '".$DB->ForSql($SECTION_CODE)."'
				";
				$strRoot = "AND BS".$i.".IBLOCK_SECTION_ID IS NULL";
			}
		}

		$strSql = "
			select BS.ID
			from ".$strFrom."
			WHERE BS.IBLOCK_ID = ".$iblock_id."
			".$strWhere."
			".$strRoot."
		";
		$rs = $DB->Query($strSql);
		$ar = $rs->Fetch();
		unset($rs);
		if ($ar)
		{
			$arVariables["SECTION_ID"] = $ar["ID"];
			$arVariables["SECTION_CODE"] = $sectionPath[count($sectionPath)-1];

			return true;
		}
		else
		{
			return false;
		}
	}
}
