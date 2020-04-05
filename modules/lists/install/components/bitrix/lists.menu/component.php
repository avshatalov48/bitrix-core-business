<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);
/** @var CCacheManager $CACHE_MANAGER */
global $CACHE_MANAGER;

$arParams["LIST_ID"] = intval($arParams["LIST_ID"]);
$arResult["LISTS"] = array();

$arUserGroups = $USER->GetUserGroupArray();

if($this->StartResultCache(false, $arUserGroups))
{
	if(CModule::IncludeModule('lists'))
	{
		//Find out if there is some groups to edit lists (so it's lists)
		$arListsPerm = CLists::GetPermission($arParams["~IBLOCK_TYPE_ID"]);
		if(count($arListsPerm) > 0)
		{
			$CACHE_MANAGER->StartTagCache($this->GetCachePath());
			$CACHE_MANAGER->RegisterTag("lists_list_any");

			$CAN_EDIT = count(array_intersect($arListsPerm, $arUserGroups)) > 0;

			$arOrder = array(
				"SORT" => "ASC",
				"NAME" => "ASC",
			);
			$arFilter = array(
				"ACTIVE" => "Y",
				"SITE_ID" => SITE_ID,
				"=TYPE" => $arParams["~IBLOCK_TYPE_ID"],
				"CHECK_PERMISSIONS" => ($CAN_EDIT? "N": "Y"), //This cancels iblock permissions for trusted users
			);

			$rsLists = CIBlock::GetList($arOrder, $arFilter);
			while($arList = $rsLists->GetNext())
			{
				$ar = array();

				$ar["ID"] = $arList["ID"];
				$ar["DEPTH_LEVEL"] = 1;
				$ar["~NAME"] = $arList["~NAME"];
				$ar["LINKS"] = array();

				if($arParams["IS_SEF"] == "Y")
				{
					$ar["LIST_URL"] = CHTTP::urlAddParams(str_replace(
						array("#list_id#", "#section_id#"),
						array($arList["ID"], "0"),
						$arParams["~SEF_BASE_URL"].$arParams["~SEF_LIST_URL"]
					), array("list_section_id" => ""));

					$ar["LINKS"][] = str_replace(
						array("#list_id#", "#section_id#"),
						array($arList["ID"], "0"),
						$arParams["~SEF_BASE_URL"].$arParams["~SEF_LIST_BASE_URL"]
					);
				}
				else
				{
					$ar["LIST_URL"] = CHTTP::urlAddParams(str_replace(
						array("#list_id#", "#section_id#"),
						array($arList["ID"], "0"),
						$arParams["~LIST_URL"]
					), array("list_section_id" => ""));
				}

				$arResult["LISTS"][$arList["ID"]] = $ar;
			}

			$CACHE_MANAGER->EndTagCache();
			$this->EndResultCache();
		}
		else
		{
			$this->AbortResultCache();
		}
	}
	else
	{
		$this->AbortResultCache();
	}
}

$aMenuLinksNew = array();
foreach($arResult["LISTS"] as $i => $arList)
{
	$aMenuLinksNew[] = array(
		$arList["~NAME"],
		$arList["LIST_URL"],
		$arList["LINKS"],
	);
}

return $aMenuLinksNew;
?>
