<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$defaultImg = "<img src=\"/bitrix/images/lists/nopic_list_150.png\" width=\"36\" height=\"30\" border=\"0\" alt=\"\" />";
if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$defaultImg = "<img src=\"/bitrix/images/lists/default.png\" width=\"36\" height=\"30\" border=\"0\" alt=\"\" />";
}
foreach($arResult["ITEMS"] as $key => $item)
{
	if($item["PICTURE"] > 0)
	{
		$imageFile = CFile::GetFileArray($item["PICTURE"]);
		if($imageFile !== false)
		{
			$imageFile = CFile::ResizeImageGet(
				$imageFile,
				["width" => 36, "height" => 30],
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);
			$imageScr = \Bitrix\Main\Web\Uri::urnEncode($imageFile['src']);
			$arResult["ITEMS"][$key]["IMAGE"] = "<img src=\"{$imageScr}\" border=\"0\">";
		}
	}
	if(!isset($arResult["ITEMS"][$key]["IMAGE"]))
	{
		$arResult["ITEMS"][$key]["IMAGE"] = $defaultImg;
	}

	if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
	{
		$arResult["ITEMS"][$key]["SHOW_LIVE_FEED"] = CLists::getLiveFeed($item['ID']);
	}
}