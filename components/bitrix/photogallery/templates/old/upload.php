<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.upload",
	"",
	Array(
 		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
 		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],

		"UPLOAD_MAX_FILE"	=>	$arParams["UPLOAD_MAX_FILE"],
		"UPLOAD_MAX_FILE_SIZE"	=>	$arParams["UPLOAD_MAX_FILE_SIZE"],
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"MODERATION" => "N",
		
		"WATERMARK_RULES" => $arParams["WATERMARK_RULES"],
		"WATERMARK_TYPE" => $arParams["WATERMARK_TYPE"], 
		"WATERMARK_TEXT" => $arParams["WATERMARK_TEXT"], 
		"WATERMARK_COLOR" => $arParams["WATERMARK_COLOR"], 
		"WATERMARK_SIZE" => $arParams["WATERMARK_SIZE"], 
		"WATERMARK_FILE" => $arParams["WATERMARK_FILE"], 
		"WATERMARK_FILE_ORDER" => $arParams["WATERMARK_FILE_ORDER"], 
		"WATERMARK_POSITION" => $arParams["WATERMARK_POSITION"], 
		"WATERMARK_TRANSPARENCY" => $arParams["WATERMARK_TRANSPARENCY"], 
		"PATH_TO_FONT" => $arParams["PATH_TO_FONT"], 
		"WATERMARK_MIN_PICTURE_SIZE"	=>	$arParams["WATERMARK_MIN_PICTURE_SIZE"],
		
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		
 		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"JPEG_QUALITY1"	=>	$arParams["JPEG_QUALITY1"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"], 
		"JPEG_QUALITY2"	=>	$arParams["JPEG_QUALITY2"],
		"ORIGINAL_SIZE" =>	$arParams["ORIGINAL_SIZE"], 
		"JPEG_QUALITY"	=>	$arParams["JPEG_QUALITY"],
		
 		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
 		"SET_TITLE" => $arParams["SET_TITLE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"WATERMARK" => $arParams["WATERMARK"],
		"SHOW_WATERMARK" => $arParams["WATERMARK"],
 		"WATERMARK_COLORS" => $arParams["WATERMARK_COLORS"], 
 		"SHOW_TAGS" => $arParams["SHOW_TAGS"]
 	),
	$component
);?>