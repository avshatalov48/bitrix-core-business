<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach($arResult["FIELDS"] as $code=>$value)
{
	if ($code == 'PREVIEW_PICTURE')
	{
		if(is_array($value))
		{
			$arFileTmp = CFile::ResizeImageGet(
				$value,
				//array("width" => $arParams["DISPLAY_IMG_WIDTH"], "height" => $arParams["DISPLAY_IMG_HEIGHT"]),
				array("width" => 298, "height" => 221),
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