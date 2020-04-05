<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach ($arResult["ITEMS"] as $key => $arItem)
{
	if(is_array($arItem["PREVIEW_PICTURE"]))
	{
		if($arItem["PROPERTIES"]["PARTMAIN"]["VALUE"])
		{
			$prewiewImg = "PREVIEW_IMG_MEDIUM";
			$widthImg  = $arParams["DISPLAY_IMG_MEDIUM_WIDTH"];
			$heightImg = $arParams["DISPLAY_IMG_MEDIUM_HEIGHT"];
		}
		else
		{
			$prewiewImg = "PREVIEW_IMG_SMALL";
			$widthImg  = $arParams["DISPLAY_IMG_WIDTH"];
			$heightImg = $arParams["DISPLAY_IMG_HEIGHT"];
		}

		$arFileTmp = CFile::ResizeImageGet(
			$arItem["PREVIEW_PICTURE"],
			array("width" => $widthImg , "height" => $heightImg),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);


		$arResult["ITEMS"][$key][$prewiewImg] = array(
			"SRC" => $arFileTmp["src"],
			"WIDTH" => $arFileTmp["width"],
			"HEIGHT" => $arFileTmp["height"],
		);
	}
}
?>