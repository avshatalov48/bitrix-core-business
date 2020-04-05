<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arResult['ITEMS_THEME'] = array();
if(!empty($arResult["DISPLAY_PROPERTIES"]["THEME"]["VALUE"]))
{
	$rsElementTheme = CIBlockElement::GetList(
		array(
			"active_from" => "DESC"
		),
		array(
			"PROPERTY_THEME" => $arResult["DISPLAY_PROPERTIES"]["THEME"]["VALUE"],
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"IBLOCK_ID" => $arResult["IBLOCK_ID"],
			"!ID" => $arResult["ID"],
			"ACTIVE_DATE" => "Y"
		),
		false,
		Array ("nTopCount" => 5),
		array("ID", "NAME", "DETAIL_PAGE_URL")
	);

	while($obElementTheme = $rsElementTheme->GetNextElement())
	{
		$arItemTheme = $obElementTheme->GetFields();
		$arResult['ITEMS_THEME'][] = $arItemTheme;
	}
}

foreach($arResult["FIELDS"] as $code=>$value)
{
	if ($code == 'PREVIEW_PICTURE')
	{
		if(is_array($value))
		{
			$arFileTmp = CFile::ResizeImageGet(
				$value,
				array("width" => $arParams["DISPLAY_IMG_DETAIL_WIDTH"], "height" => $arParams["DISPLAY_IMG_DETAIL_HEIGHT"]),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);

			$arResult["DETAIL_PICTURE"] = array(
				"SRC" => $arFileTmp["src"],
				"WIDTH" => $arFileTmp["width"],
				"HEIGHT" => $arFileTmp["height"],
			);
		}
	}
}
?>