<?
IncludeModuleLangFile(__FILE__);

use \Bitrix\Main\ORM\Query\Query;
use \Bitrix\Security\FrameMaskTable;

class CSecurityFrame
{
	public static function SetHeader()
	{
		if((!defined("BX_SECURITY_SKIP_FRAMECHECK") || BX_SECURITY_SKIP_FRAMECHECK!==true) && !CSecurityFrameMask::Check(SITE_ID, $_SERVER["REQUEST_URI"]))
		{
			header("X-Frame-Options: SAMEORIGIN");
			header("Content-Security-Policy: frame-ancestors 'self';");
		}
	}

	public static function IsActive()
	{
		$bActive = false;
		foreach(GetModuleEvents("main", "OnPageStart", true) as $event)
		{
			if(
				isset($event["TO_MODULE_ID"]) && $event["TO_MODULE_ID"] === "security"
				&& isset($event["TO_CLASS"]) && $event["TO_CLASS"] === "CSecurityFrame"
			)
			{
				$bActive = true;
				break;
			}
		}
		return $bActive;
	}

	public static function SetActive($bActive = false)
	{
		if($bActive)
		{
			if(!CSecurityFrame::IsActive())
			{
				RegisterModuleDependences("main", "OnPageStart", "security", "CSecurityFrame", "SetHeader", "0");
			}
		}
		else
		{
			if(CSecurityFrame::IsActive())
			{
				UnRegisterModuleDependences("main", "OnPageStart", "security", "CSecurityFrame", "SetHeader");
			}
		}
	}
}

class CSecurityFrameMask
{
	public static function Update($arMasks)
	{
		global $CACHE_MANAGER;

		if(is_array($arMasks))
		{
			$res = FrameMaskTable::deleteList([]);
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
					$mask_site = $mask . "_" . $site_id;
					if($mask && !array_key_exists($mask_site, $added))
					{
						$arMask = array(
							"SORT" => $i,
							"FRAME_MASK" => $mask,
							"LIKE_MASK" => str_replace($arLikeSearch, $arLikeReplace, $mask),
							"PREG_MASK" => str_replace($arPregSearch, $arPregReplace, $mask),
						);
						if($site_id)
							$arMask["SITE_ID"] = $site_id;

						FrameMaskTable::add($arMask);
						$i += 10;
						$added[$mask_site] = true;
					}
				}

				if(CACHED_b_sec_frame_mask !== false)
					$CACHE_MANAGER->CleanDir("b_sec_frame_mask");

			}
		}

		return true;
	}

	public static function GetList()
	{
		$res = FrameMaskTable::getList(['select' => ['SITE_ID', 'FRAME_MASK'], 'order' => 'SORT']);
		return $res;
	}

	public static function Check($siteId, $uri)
	{
		global $DB, $CACHE_MANAGER;
		$bFound = false;

		if(CACHED_b_sec_frame_mask !== false)
		{
			$cache_id = "b_sec_frame_mask";
			if($CACHE_MANAGER->Read(CACHED_b_sec_frame_mask, $cache_id, "b_sec_frame_mask"))
			{
				$arMasks = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$arMasks = array();

				$rs = FrameMaskTable::getList(['order' => 'SORT']);
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

			if(
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

			$rs = FrameMaskTable::getList(['filter' => $filter]);
			if($rs->Fetch())
				$bFound = true;
		}

		return $bFound;
	}
}

?>