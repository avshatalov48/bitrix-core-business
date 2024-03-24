<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage security
* @copyright 2001-2013 Bitrix
*/

use \Bitrix\Main\ORM\Query\Query;
use \Bitrix\Security\FilterMaskTable;

class CSecurityFilterMask
{
	public static function Update($arMasks)
	{
		global $CACHE_MANAGER;

		if(is_array($arMasks))
		{
			$res = FilterMaskTable::deleteList([]);
			if($res)
			{
				$arLikeSearch = array("?", "*", ".");
				$arLikeReplace = array("_",  "%", "\\.");
				$arPregSearch = array("\\", ".",  "?", "*",   "'");
				$arPregReplace = array("/",  "\.", ".", ".*?", "\'");

				$added = array();
				$i = 10;
				foreach($arMasks as $arMask)
				{
					$site_id = trim($arMask["SITE_ID"]);
					if($site_id == "NOT_REF")
						$site_id = "";

					$mask = trim($arMask["MASK"]);
					if($mask && !array_key_exists($mask, $added))
					{
						$arMask = array(
							"SORT" => $i,
							"FILTER_MASK" => $mask,
							"LIKE_MASK" => str_replace($arLikeSearch, $arLikeReplace, $mask),
							"PREG_MASK" => str_replace($arPregSearch, $arPregReplace, $mask),
						);
						if($site_id)
							$arMask["SITE_ID"] = $site_id;

						FilterMaskTable::add($arMask);
						$i += 10;
						$added[$mask] = true;
					}
				}

				if(CACHED_b_sec_filter_mask !== false)
					$CACHE_MANAGER->CleanDir("b_sec_filter_mask");

			}
		}

		return true;
	}

	public static function GetList()
	{
		$res = FilterMaskTable::getList(['select' => ['SITE_ID', 'FILTER_MASK'], 'order' => 'sort']);
		return $res;
	}

	public static function Check($siteId, $uri)
	{
		global $CACHE_MANAGER;
		$bFound = false;

		if(CACHED_b_sec_filter_mask !== false && is_object($CACHE_MANAGER))
		{
			$cache_id = "b_sec_filter_mask";
			if($CACHE_MANAGER->Read(CACHED_b_sec_filter_mask, $cache_id, "b_sec_filter_mask"))
			{
				$arMasks = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$arMasks = array();

				$rs = FilterMaskTable::getList(['order' => 'sort']);
				while($ar = $rs->Fetch())
				{
					$site_id = $ar["SITE_ID"]? $ar["SITE_ID"]: "-";
					$arMasks[$site_id][$ar["SORT"]] = $ar["PREG_MASK"];
				}

				$CACHE_MANAGER->Set($cache_id, $arMasks);
			}

			if(isset($arMasks["-"]) && is_array($arMasks["-"]))
			{
				foreach($arMasks["-"] as $mask)
				{
					if(preg_match("#^".$mask."$#", $uri))
					{
						$bFound = true;
						break;
					}
				}
			}

			if (
				!$bFound
				&& $siteId
				&& isset($arMasks[$siteId])
			)
			{
				foreach($arMasks[$siteId] as $mask)
				{
					if(preg_match("#^".$mask."$#", $uri))
					{
						$bFound = true;
						break;
					}
				}
			}

		}
		else
		{
			$sqlHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper();

			$filter = Query::filter()
				->whereNull('SITE_ID')
				->whereExpr("'".$sqlHelper->forSql($uri)."' LIKE %s", ['LIKE_MASK']);

			if ($siteId)
			{
				$filterOr = Query::filter()
					->where('SITE_ID', $siteId)
					->whereExpr("'".$sqlHelper->forSql($uri)."' LIKE %s", ['LIKE_MASK']);

				$filter = Query::filter()
					->logic('or')
						->where($filter)
						->where($filterOr);
			}

			$rs = FilterMaskTable::getList(['filter' => $filter]);

			if($rs->Fetch())
				$bFound = true;
		}

		return $bFound;
	}
}
