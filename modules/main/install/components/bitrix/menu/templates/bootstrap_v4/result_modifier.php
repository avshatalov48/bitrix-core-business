<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (empty($arResult))
	return;

if (isset($arParams["MENU_THEME"]) && !empty($arParams["MENU_THEME"]))
{
	$arAvailableThemes = array();


	if ($arParams["MENU_THEME"] == "site")
	{
		$solution = COption::GetOptionString("main", "wizard_solution", "", SITE_ID);
		if ($solution == "eshop")
		{
			$templateId = COption::GetOptionString("main", "wizard_template_id", "eshop_bootstrap", SITE_ID);
			$templateId = (preg_match("/^eshop_adapt/", $templateId)) ? "eshop_adapt" : $templateId;
			$theme = COption::GetOptionString("main", "wizard_".$templateId."_theme_id", "blue", SITE_ID);
			$arParams["MENU_THEME"] = $theme;
		}
	}
}


if(!function_exists("FillAllPicturesAndDescriptions"))
{
	function FillAllPicturesAndDescriptions(&$arAllItems, $arMenuItemsIDs, &$arImgDesc)
	{
		//find picture or description for the first level, if it hasn't
		foreach ($arMenuItemsIDs as $itemIdLevel_1=>$arLevels2)
		{
			if (!$arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] || !$arAllItems[$itemIdLevel_1]["PARAMS"]["description"])
			{
				foreach($arLevels2 as $itemIdLevel_2=>$arLevels3)
				{
					if (!$arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] && $arAllItems[$itemIdLevel_2]["PARAMS"]["picture_src"])
					{
						$arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] = $arAllItems[$itemIdLevel_2]["PARAMS"]["picture_src"];
					}
					if (!$arAllItems[$itemIdLevel_1]["PARAMS"]["description"] && $arAllItems[$itemIdLevel_2]["PARAMS"]["description"])
					{
						$arAllItems[$itemIdLevel_1]["PARAMS"]["description"] = $arAllItems[$itemIdLevel_2]["PARAMS"]["description"];
					}
					if ($arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] && $arAllItems[$itemIdLevel_1]["PARAMS"]["description"])
						break;
				}
				if (!$arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] || !$arAllItems[$itemIdLevel_1]["PARAMS"]["description"])
				{
					foreach($arLevels2 as $itemIdLevel_2=>$arLevels3)
					{
						foreach($arLevels3 as $itemIdLevel_3)
						{
							if (!$arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] && $arAllItems[$itemIdLevel_3]["PARAMS"]["picture_src"])
							{
								$arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] = $arAllItems[$itemIdLevel_3]["PARAMS"]["picture_src"];
							}
							if (!$arAllItems[$itemIdLevel_1]["PARAMS"]["description"] && $arAllItems[$itemIdLevel_3]["PARAMS"]["description"])
							{
								$arAllItems[$itemIdLevel_1]["PARAMS"]["description"] = $arAllItems[$itemIdLevel_3]["PARAMS"]["description"];
							}
							if ($arAllItems[$itemIdLevel_1]["PARAMS"]["picture_src"] && $arAllItems[$itemIdLevel_1]["PARAMS"]["description"])
							{
								break 2;
							}
						}
					}
				}
			}
		}

		foreach($arAllItems as $itemID=>$arItem)
		{
			if ($arItem["DEPTH_LEVEL"] == "1")
			{
				if ($arItem["IS_PARENT"])
				{
					$pictureLevel_1 = $arItem["PARAMS"]["picture_src"];
					$descriptionLevel_1 = $arItem["PARAMS"]["description"];
				}
				$arAllItems[$itemID] = $arItem;
			}
			elseif($arItem["DEPTH_LEVEL"] == "2")
			{
				if (!$arItem["PARAMS"]["picture_src"])
					$arItem["PARAMS"]["picture_src"] = $pictureLevel_1;
				if (!$arItem["PARAMS"]["description"])
					$arItem["PARAMS"]["description"] = $descriptionLevel_1;
				if ($arItem["IS_PARENT"])
				{
					$pictureLevel_2 = $arItem["PARAMS"]["picture_src"];
					$descriptionLevel_2 = $arItem["PARAMS"]["description"];
				}
				$arAllItems[$itemID] = $arItem;
			}
			elseif($arItem["DEPTH_LEVEL"] == "3")
			{
				if (!$arItem["PARAMS"]["picture_src"])
					$arItem["PARAMS"]["picture_src"] = $pictureLevel_2;
				if (!$arItem["PARAMS"]["description"])
					$arItem["PARAMS"]["description"] = $descriptionLevel_2;
				$arAllItems[$itemID] = $arItem;
			}

			$arImgDesc[$itemID]["PICTURE"] = $arItem["PARAMS"]["picture_src"];
			$arImgDesc[$itemID]["DESC"] = $arItem["PARAMS"]["description"];
		}
	}
}

$arSectionsInfo = array();
if (IsModuleInstalled("iblock"))
{
	$arFilter = array(
		"TYPE" => "catalog",
		"SITE_ID" => SITE_ID,
		"ACTIVE" => "Y"
	);

	$obCache = new CPHPCache();
	if ($obCache->InitCache(36000, serialize($arFilter), "/iblock/menu"))
	{
		$arSectionsInfo = $obCache->GetVars();
	}
	elseif ($obCache->StartDataCache())
	{
		if (CModule::IncludeModule("iblock"))
		{
			$dbIBlock = CIBlock::GetList(array('SORT' => 'ASC', 'ID' => 'ASC'), $arFilter);
			$dbIBlock = new CIBlockResult($dbIBlock);
			$curIblockID = 0;
			if ($arIBlock = $dbIBlock->GetNext())
			{
				$dbSections = CIBlockSection::GetList(array(), array("IBLOCK_ID" => $arIBlock["ID"]), false, array("ID", "SECTION_PAGE_URL", "PICTURE", "DESCRIPTION"));
				while($arSections = $dbSections->GetNext())
				{
					$pictureSrc = CFile::GetFileArray($arSections["PICTURE"]);

					if ($pictureSrc)
						$arResizePicture = CFile::ResizeImageGet(
							$arSections["PICTURE"],
							array("width" => 240, 'height'=>700),
							BX_RESIZE_IMAGE_PROPORTIONAL,
							true
						);

					$arSectionsInfo[crc32($arSections["SECTION_PAGE_URL"])]["PICTURE"] = $pictureSrc ? $arResizePicture["src"] : false;
					$arSectionsInfo[crc32($arSections["SECTION_PAGE_URL"])]["DESCRIPTION"] = $arSections["DESCRIPTION"];
				}
				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					global $CACHE_MANAGER;
					$CACHE_MANAGER->StartTagCache("/iblock/menu");
					$CACHE_MANAGER->RegisterTag("iblock_id_".$arIBlock["ID"]);
					$CACHE_MANAGER->EndTagCache();
				}
			}
		}
		$obCache->EndDataCache($arSectionsInfo);
	}
}

$arMenuItemsIDs = array();
$arAllItems = array();
$arImgDesc = array();
foreach($arResult as $key=>$arItem)
{
	if($arItem["DEPTH_LEVEL"] > $arParams["MAX_LEVEL"])
	{
		unset($arResult[$key]);
		continue;
	}

	$arItem["PARAMS"]["item_id"] = crc32($arItem["LINK"]);
	$arItem["PARAMS"]["picture_src"] = $arSectionsInfo[$arItem["PARAMS"]["item_id"]]["PICTURE"];
	$arItem["PARAMS"]["description"] = $arSectionsInfo[$arItem["PARAMS"]["item_id"]]["DESCRIPTION"];

	if ($arItem["DEPTH_LEVEL"] == "1")
	{
		$arMenuItemsIDs[$arItem["PARAMS"]["item_id"]] = array();
		if ($arItem["IS_PARENT"])
		{
			$curItemLevel_1 = $arItem["PARAMS"]["item_id"];
		}
		$arAllItems[$arItem["PARAMS"]["item_id"]] = $arItem;
	}
	elseif($arItem["DEPTH_LEVEL"] == "2")
	{
		$arMenuItemsIDs[$curItemLevel_1][$arItem["PARAMS"]["item_id"]] = array();
		if ($arItem["IS_PARENT"])
		{
			$curItemLevel_2 = $arItem["PARAMS"]["item_id"];
		}
		$arAllItems[$arItem["PARAMS"]["item_id"]] = $arItem;
	}
	elseif($arItem["DEPTH_LEVEL"] == "3")
	{
		$arMenuItemsIDs[$curItemLevel_1][$curItemLevel_2][] = $arItem["PARAMS"]["item_id"];
		$arAllItems[$arItem["PARAMS"]["item_id"]] = $arItem;
	}
}

FillAllPicturesAndDescriptions($arAllItems, $arMenuItemsIDs, $arImgDesc);

$arMenuStructure = array();
foreach ($arMenuItemsIDs as $itemIdLevel_1=>$arLevels2)
{
	$countItemsInRow = 18;
	$arMenuStructure[$itemIdLevel_1] = array();
	$countLevels2 = count($arLevels2);

	if ($countLevels2 > 0)
	{
		for ($i=1; $i<=3; $i++)
		{
			$sumElementsInRow = 0;
			foreach($arLevels2 as $itemIdLevel_2=>$arLevels3)
			{
				$sumElementsInRow+= count($arLevels3) + 1;
				$arMenuStructure[$itemIdLevel_1][$i][$itemIdLevel_2] = $arLevels3;
				if ($sumElementsInRow > $countItemsInRow)
					$countItemsInRow = $sumElementsInRow;

				unset($arLevels2[$itemIdLevel_2]);
				$tmpCount = 0;
				foreach($arLevels2 as $tmpItemIdLevel_2=>$arTmpLevels3)
				{
					$tmpCount+= 1 + count($arTmpLevels3);
				}

				if ($tmpCount <= $countItemsInRow*(3-$i) && $countItemsInRow<=$sumElementsInRow)
					break;
			}
		}
	}
}

$arResult = array();
$arResult["ALL_ITEMS"] = $arAllItems;
$arResult["ITEMS_IMG_DESC"] = $arImgDesc;
$arResult["MENU_STRUCTURE"] = $arMenuStructure;