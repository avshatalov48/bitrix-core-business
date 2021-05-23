<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (
	isset($arResult["User"])
	&& is_array($arResult["User"])
	&& isset($arResult["User"]["PersonalPhotoImgThumbnail"])
	&& empty($arResult["User"]["PersonalPhotoImgThumbnail"]["Image"])
)
{
	$arResult["User"]["PersonalPhotoImgThumbnail"]["Image"] = '<img src="'.$this->GetFolder().'/images/nopic_30x30.gif" width="'.$arParams["THUMBNAIL_LIST_SIZE"].'" height="'.$arParams["THUMBNAIL_LIST_SIZE"].'" border="0">';
}
?>