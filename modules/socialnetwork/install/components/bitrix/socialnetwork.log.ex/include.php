<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$GLOBALS["CurUserCanAddComments"] = array();

if (!function_exists('__SLLogUpDateTSSort'))
{
	function __SLLogUpDateTSSort($a, $b)
	{
		if ($a["LOG_UPDATE_TS"] == $b["LOG_UPDATE_TS"])
		{
			if (array_key_exists("EVENT", $a))
				return ($a["EVENT"]["ID"] > $b["EVENT"]["ID"]) ? -1 : 1;
			else
				return 0;
		}

		return ($a["LOG_UPDATE_TS"] > $b["LOG_UPDATE_TS"]) ? -1 : 1;
	}
}

if (!function_exists('__SLLogGetIds'))
{
	function __SLLogGetIds(
		$arOrder, $arFilter, $arNavStartParams, $arSelectFields, $arListParams, $bFirstPage,
		&$arResult, &$arActivity2Log, &$arDiskUFEntity, &$arTmpEventsNew
	)
	{
		$dbEventsID = CSocNetLog::GetList(
			$arOrder,
			$arFilter,
			false,
			$arNavStartParams,
			$arSelectFields,
			$arListParams
		);

		if ($bFirstPage)
		{
			$arResult["NAV_STRING"] = "";
			$arResult["PAGE_NAVNUM"] = $GLOBALS["NavNum"]+1;
			$arResult["PAGE_NAVCOUNT"] = 1000000;
		}
		else
		{
			$arResult["NAV_STRING"] = $dbEventsID->GetPageNavStringEx($navComponentObject, GetMessage("SONET_C73_NAV"), "", false);
			$arResult["PAGE_NUMBER"] = $dbEventsID->NavPageNomer;
			$arResult["PAGE_NAVNUM"] = $dbEventsID->NavNum;
			$arResult["PAGE_NAVCOUNT"] = $dbEventsID->NavPageCount;
		}

		$cnt = 0;
		while ($arEventsID = $dbEventsID->GetNext())
		{
			if ($arEventsID["MODULE_ID"] == "crm_shared")
			{
				$arEventsID["MODULE_ID"] = "crm";
			}

			if (
				(
					!empty($arEventsID["MODULE_ID"])
					&& !IsModuleInstalled($arEventsID["MODULE_ID"])
				)
				||
				(
					in_array($arEventsID["EVENT_ID"], array("timeman_entry", "report"))
					&& !IsModuleInstalled("timeman")
				)
				|| (
					in_array($arEventsID["EVENT_ID"], array("tasks"))
					&& !IsModuleInstalled("tasks")
				)
				|| (
					in_array($arEventsID["EVENT_ID"], array("lists_new_element"))
					&& !IsModuleInstalled("lists")
				)
			)
			{
				continue;
			}

			if (in_array($arEventsID["EVENT_ID"], array("crm_activity_add")))
			{
				$arActivity2Log[$arEventsID["ENTITY_ID"]] = $arEventsID["ID"];
			}

			$cnt++;
			if ($cnt == 1)
			{
				$arResult["CURRENT_PAGE_DATE"] = (
					$dbEventsID->NavPageNomer > 1
						? $arEventsID["LOG_UPDATE"]
						: ConvertTimeStamp(time() + $arResult["TZ_OFFSET"], "FULL")
				);
			}
			$arResult["arLogTmpID"][] = $arEventsID["ID"];
			$arTmpEventsNew[] = $arEventsID;

			$livefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

			if (
				in_array($arEventsID["EVENT_ID"], array_merge($livefeedProvider->getEventId(), array("idea")))
				&& intval($arEventsID["SOURCE_ID"]) > 0
			)
			{
				$arDiskUFEntity["BLOG_POST"][] = $arEventsID["SOURCE_ID"];
			}
			elseif (!in_array($arEventsID["EVENT_ID"], array("data", "photo", "photo_photo", "bitrix24_new_user", "intranet_new_user", "news")))
			{
				$arDiskUFEntity["SONET_LOG"][] = $arEventsID["ID"];
			}
		}

		return $dbEventsID;
	}
}
?>