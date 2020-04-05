<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(is_array($arResult["DETAIL_PICTURE"]))
{
	$arFilter = '';
	//if($arParams["SHARPEN"] != 0)
	//{
		$arFilter = array(array("name" => "sharpen", "precision" => 100/*$arParams["SHARPEN"]*/));
	//}
	$arFileTmp = CFile::ResizeImageGet(
		$arResult['DETAIL_PICTURE'],
		array("width" => 180, "height" => 180),
		BX_RESIZE_IMAGE_PROPORTIONAL,
		true, $arFilter
	);

	$arResult['DETAIL_PICTURE_SMALL'] = array(
		'SRC' => $arFileTmp["src"],
		'WIDTH' => $arFileTmp["width"],
		'HEIGHT' => $arFileTmp["height"],
	);
}
foreach ($arResult["DISPLAY_PROPERTIES"] as $code => $property)
{
	if ($property["PROPERTY_TYPE"] == "F")
		unset($arResult["DISPLAY_PROPERTIES"][$code]);
}

if (is_array($arResult['MORE_PHOTO']) && count($arResult['MORE_PHOTO']) > 0)
{
	$arPhotoGallery = array();
	if(count($arResult["MORE_PHOTO"])>0)
	{
		foreach($arResult["MORE_PHOTO"] as $photo)
		{
			$arPhotoGallery[] = $photo["SRC"];
		}
		$arResult["PHOTO_GALLERY"] = $arPhotoGallery;
	}

	foreach ($arResult['MORE_PHOTO'] as $key => $arFile)
	{
		$arFilter = '';
		$arFilter = array(array("name" => "sharpen", "precision" => 100));

		$arFileTmp = CFile::ResizeImageGet(
			$arFile,
			array("width" => 60, "height" => 60),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true, $arFilter
		);

		$arFile['PREVIEW_WIDTH'] = $arFileTmp["width"];
		$arFile['PREVIEW_HEIGHT'] = $arFileTmp["height"];

		$arFile['SRC'] = $arFileTmp['src'];
		$arResult['MORE_PHOTO'][$key] = $arFile;
	}
}
/*
if (CModule::IncludeModule('currency'))
{
	if (isset($arResult['DISPLAY_PROPERTIES']['MINIMUM_PRICE']))
		$arResult['DISPLAY_PROPERTIES']['MINIMUM_PRICE']['DISPLAY_VALUE'] = FormatCurrency($arResult['DISPLAY_PROPERTIES']['MINIMUM_PRICE']['VALUE'], CCurrency::GetBaseCurrency());
	if (isset($arResult['DISPLAY_PROPERTIES']['MAXIMUM_PRICE']))
		$arResult['DISPLAY_PROPERTIES']['MAXIMUM_PRICE']['DISPLAY_VALUE'] = FormatCurrency($arResult['DISPLAY_PROPERTIES']['MAXIMUM_PRICE']['VALUE'], CCurrency::GetBaseCurrency());
}

$this->__component->SetResultCacheKeys(array("DISPLAY_PROPERTIES"));
$this->__component->SetResultCacheKeys(array("DETAIL_TEXT"));
$this->__component->SetResultCacheKeys(array("CAN_BUY")); */
//$this->__component->SetResultCacheKeys(array("OFFERS_IDS"));

?>