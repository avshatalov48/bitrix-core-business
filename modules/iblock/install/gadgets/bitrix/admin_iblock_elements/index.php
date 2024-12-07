<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<?
if(!CModule::IncludeModule("iblock"))
	return false;

if (!function_exists('__GD_AIE_ConvertDateTime'))
{
	function __GD_AIE_ConvertDateTime(&$item, $key)
	{
		$item = mb_strtolower(FormatDate("j F Y", MakeTimeStamp($item)));
	}
}

if (
	intval($arGadgetParams["ITEMS_COUNT"]) < 1
	|| intval($arGadgetParams["ITEMS_COUNT"]) > 20
)
	$arGadgetParams["ITEMS_COUNT"] = 10;

if (
	intval($arGadgetParams["THUMBNAIL_SIZE"]) < 10
	|| intval($arGadgetParams["THUMBNAIL_SIZE"]) > 500
)
	$arGadgetParams["THUMBNAIL_SIZE"] = 100;

if (
	intval($arGadgetParams["DESCRIPTION_CUT"]) < 50
	|| intval($arGadgetParams["DESCRIPTION_CUT"]) > 5000
)
	$arGadgetParams["DESCRIPTION_CUT"] = 500;

if ($arGadgetParams["SORT_BY"] == '')
	$arGadgetParams["SORT_BY"] = "ID";

if ($arGadgetParams["SORT_ORDER"] == '')
	$arGadgetParams["SORT_ORDER"] = "DESC";

if ($arGadgetParams["TITLE_FIELD"] == '')
	$arGadgetParams["TITLE_FIELD"] = "NAME";

if ($arGadgetParams["DATE_FIELD"] == '')
	$arGadgetParams["DATE_FIELD"] = "DATE_ACTIVE_FROM";

if ($arGadgetParams["PICTURE_FIELD"] == '')
	$arGadgetParams["PICTURE_FIELD"] = "PREVIEW_PICTURE";

if ($arGadgetParams["DESCRIPTION_FIELD"] == '')
	$arGadgetParams["DESCRIPTION_FIELD"] = "PREVIEW_TEXT";

if (!is_array($arGadgetParams["ADDITIONAL_FIELDS"]) || count($arGadgetParams["ADDITIONAL_FIELDS"]) <= 0)
	$arGadgetParams["ADDITIONAL_FIELDS"] = array();

if (
	$arGadgetParams["IBLOCK_TYPE"] <> ''
	&& intval($arGadgetParams["IBLOCK_ID"]) > 0
)
{
	$dbIBlock = CIBlock::GetList(
		Array(),
		Array(
			"TYPE" => $arGadgetParams["IBLOCK_TYPE"],
			"ID" => $arGadgetParams["IBLOCK_ID"],
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => (IsModuleInstalled("workflow")?"U":"W")
		)
	);
	if($arIBlock = $dbIBlock->GetNext())
	{
		$obParser = new CTextParser;

		if ($arGadgetParams["TITLE_STD"] == '')
			$arGadget["TITLE"] = $arIBlock["NAME"];

		$arSort = array($arGadgetParams["SORT_BY"] => $arGadgetParams["SORT_ORDER"]);
		$arFilter = array(
			"IBLOCK_ID" => $arGadgetParams["IBLOCK_ID"]
		);

		$arIBlockProperties = array();
		$arIBlockPropertiesDateTime = array();
		$dbIBlockProperties = CIBlockProperty::GetList(
			array("SORT" => "ASC"),
			array(
				"IBLOCK_ID" => $arGadgetParams["IBLOCK_ID"],
				"ACTIVE" => "Y"
			)
		);
		while($arIBlockProperty = $dbIBlockProperties->Fetch())
		{
			$arIBlockProperties[] = $arIBlockProperty["CODE"];
			if ($arIBlockProperty["USER_TYPE"] == "DateTime")
				$arIBlockPropertiesDateTime[] = $arIBlockProperty["CODE"];
		}

		$dbIBlockElement = CIBlockElement::GetList(
			$arSort,
			$arFilter,
			false,
			array(
				"nTopCount" => $arGadgetParams["ITEMS_COUNT"]
			)
		);

		while($obElement = $dbIBlockElement->GetNextElement())
		{
			$arIBlockElement = $obElement->GetFields();
			$arIBlockElement["PROPERTIES"] = $obElement->GetProperties();

			foreach($arIBlockProperties as $pid)
			{
				$prop = $arIBlockElement["PROPERTIES"][$pid];
				if(
					(is_array($prop["VALUE"]) && count($prop["VALUE"]) > 0)
					|| (!is_array($prop["VALUE"]) && $prop["VALUE"] <> '')
				)
					$arIBlockElement["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arIBlockElement, $prop);
			}

			?><div class="bx-gadgets-text" style="clear: both; padding: 0 0 10px 0;"><?

				if ($arGadgetParams["DATE_FIELD"] <> '')
				{
					$strDate = "";
					if (
						array_key_exists($arGadgetParams["DATE_FIELD"], $arIBlockElement)
						&& $arIBlockElement[$arGadgetParams["DATE_FIELD"]] <> ''
					)
					{
						if (in_array($arGadgetParams["DATE_FIELD"], array("DATE_CREATE", "TIMESTAMP_X", "DATE_ACTIVE_FROM", "DATE_ACTIVE_TO")))
							$strDate = mb_strtolower(FormatDate("j F Y", MakeTimeStamp($arIBlockElement[$arGadgetParams["DATE_FIELD"]])));
						else
							$strDate = $arIBlockElement[$arGadgetParams["DATE_FIELD"]];
					}
					elseif (
						mb_strpos($arGadgetParams["DATE_FIELD"], "PROPERTY_") === 0
						&& array_key_exists(mb_substr($arGadgetParams["DATE_FIELD"], 9), $arIBlockElement["DISPLAY_PROPERTIES"])
						&& $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["DATE_FIELD"], 9)]["DISPLAY_VALUE"] <> ''
					)
					{
						$val = $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["DATE_FIELD"], 9)]["DISPLAY_VALUE"];
						if (is_array($val))
						{
							if (in_array(mb_substr($arGadgetParams["DATE_FIELD"], 9), $arIBlockPropertiesDateTime))
								array_walk($val, '__GD_AIE_ConvertDateTime');
							$strDate = implode("&nbsp;/&nbsp;", $val);
						}
						elseif(in_array(mb_substr($arGadgetParams["DATE_FIELD"], 9), $arIBlockPropertiesDateTime))
								$strDate = mb_strtolower(FormatDate("j F Y", MakeTimeStamp($val)));
						else
							$strDate = $val;
					}

					if ($strDate <> '')
					{
						?><span class="bx-gadget-gray"><?=$strDate?></span><br><?
					}
				}

				if ($arGadgetParams["PICTURE_FIELD"] <> '')
				{
					$iPicture = 0;
					if (
						array_key_exists($arGadgetParams["PICTURE_FIELD"], $arIBlockElement)
						&& intval($arIBlockElement[$arGadgetParams["PICTURE_FIELD"]]) > 0
					)
						$iPicture = $arIBlockElement[$arGadgetParams["PICTURE_FIELD"]];
					elseif (
						mb_strpos($arGadgetParams["PICTURE_FIELD"], "PROPERTY_") === 0
						&& array_key_exists(mb_substr($arGadgetParams["PICTURE_FIELD"], 9), $arIBlockElement["DISPLAY_PROPERTIES"])
						&& intval($arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["PICTURE_FIELD"], 9)]["DISPLAY_VALUE"]) > 0
					)
						$iPicture = $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["PICTURE_FIELD"], 9)]["DISPLAY_VALUE"];

					if (!is_array($iPicture) && intval($iPicture) > 0)
					{
						$arImage = CFile::ResizeImageGet(
							$iPicture,
							array("width" => $arGadgetParams["THUMBNAIL_SIZE"], "height" => $arGadgetParams["THUMBNAIL_SIZE"]),
							BX_RESIZE_IMAGE_PROPORTIONAL,
							false
						);
						if ($arImage !== false)
						{
							?><div style="float: left;"><img  class="bx-gadgets-img" src="<?=$arImage["src"]?>" border="0"></div><?
						}
					}
				}

				if ($arGadgetParams["TITLE_FIELD"] <> '')
				{
					$strTitle = "";
					if (
						array_key_exists($arGadgetParams["TITLE_FIELD"], $arIBlockElement)
						&& $arIBlockElement[$arGadgetParams["TITLE_FIELD"]] <> ''
					)
						$strTitle = $arIBlockElement[$arGadgetParams["TITLE_FIELD"]];
					elseif (
						mb_strpos($arGadgetParams["TITLE_FIELD"], "PROPERTY_") === 0
						&& array_key_exists(mb_substr($arGadgetParams["TITLE_FIELD"], 9), $arIBlockElement["DISPLAY_PROPERTIES"])
						&& $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["TITLE_FIELD"], 9)]["DISPLAY_VALUE"] <> ''
					)
					{
						$val = $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["TITLE_FIELD"], 9)]["DISPLAY_VALUE"];
						if (is_array($val))
							$strTitle = implode("&nbsp;/&nbsp;", $val);
						else
							$strTitle = $val;
					}

					if ($strTitle <> '')
					{
						?><a href="/bitrix/admin/iblock_element_edit.php?ID=<?=$arIBlockElement["ID"]?>&type=<?=$arGadgetParams["IBLOCK_TYPE"]?>&IBLOCK_ID=<?=$arGadgetParams["IBLOCK_ID"]?>&lang=<?=LANGUAGE_ID?>"><?=$strTitle?></a><br><?
					}
				}

				if ($arGadgetParams["DESCRIPTION_FIELD"] <> '')
				{
					$strDescription = "";
					if (
						array_key_exists($arGadgetParams["DESCRIPTION_FIELD"], $arIBlockElement)
						&& $arIBlockElement[$arGadgetParams["DESCRIPTION_FIELD"]] <> ''
					)
						$strDescription = $arIBlockElement[$arGadgetParams["DESCRIPTION_FIELD"]];
					elseif (
						mb_strpos($arGadgetParams["DESCRIPTION_FIELD"], "PROPERTY_") === 0
						&& array_key_exists(mb_substr($arGadgetParams["DESCRIPTION_FIELD"], 9), $arIBlockElement["DISPLAY_PROPERTIES"])
						&& $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["DESCRIPTION_FIELD"], 9)]["DISPLAY_VALUE"] <> ''
					)
					{
						$val = $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($arGadgetParams["DESCRIPTION_FIELD"], 9)]["DISPLAY_VALUE"];
						if (is_array($val))
							$strDescription = implode("&nbsp;/&nbsp;", $val);
						else
							$strDescription = $val;
					}

					if ($strDescription <> '')
					{
						?><?=$obParser->html_cut($strDescription, $arGadgetParams["DESCRIPTION_CUT"]);?><br><?
					}
				}

				if (is_array($arGadgetParams["ADDITIONAL_FIELDS"]))
				{
					foreach($arGadgetParams["ADDITIONAL_FIELDS"] as $code)
					{
						if ($code <> '')
						{
							if (array_key_exists($code, $arIBlockElement))
							{
								?><?=GetMessage("GD_IBEL_NAME_".$code)?>: <?=$arIBlockElement[$code]?><br><?
							}

							if (
								mb_strpos($code, "PROPERTY_") === 0
								&& is_array($arIBlockElement["DISPLAY_PROPERTIES"])
								&& mb_substr($code, 9) <> ''
								&& array_key_exists(mb_substr($code, 9), $arIBlockElement["DISPLAY_PROPERTIES"])
								&& (
									(!is_array($arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($code, 9)]["DISPLAY_VALUE"]) && $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($code, 9)]["DISPLAY_VALUE"] <> '')
									|| (is_array($arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($code, 9)]["DISPLAY_VALUE"]) && count($arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($code, 9)]["DISPLAY_VALUE"]) > 0)
								)
							)
							{
								$val = $arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($code, 9)]["DISPLAY_VALUE"];
								?><?=$arIBlockElement["DISPLAY_PROPERTIES"][mb_substr($code, 9)]["NAME"]?>: <?=(is_array($val)?implode("&nbsp;/&nbsp;", $val):$val)?><br><?
							}
						}
					}
				}
			?></div><?
		}
		\CIBlockFormatProperties::clearCache();

		$urlElementAdminPage = CIBlock::GetAdminElementListLink($arIBlock["ID"], array());
		?><div><a href="<?=$urlElementAdminPage?>"><?=GetMessage("GD_IBEL_NAME_ALL_ELEMENTS")?></a></div><?
	}
}
