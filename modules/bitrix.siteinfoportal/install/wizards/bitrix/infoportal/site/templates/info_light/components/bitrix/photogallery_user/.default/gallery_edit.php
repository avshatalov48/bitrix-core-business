<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="photo-page-gallery-edit">
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.gallery.edit",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"], 
		"ACTION" => $arResult["VARIABLES"]["ACTION"],
		
		"INDEX_URL"	=>	$arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"GALLERIES_URL"	=>	$arResult["URL_TEMPLATES"]["galleries"],
		"GALLERY_EDIT_URL"	=>	$arResult["URL_TEMPLATES"]["gallery_edit"],
		
		"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"],
		"GALLERY_AVATAR_THUMBS_SIZE"	=>	$arParams["GALLERY_AVATAR_THUMBS_SIZE"],
		
		"ONLY_ONE_GALLERY" => $arParams["ONLY_ONE_GALLERY"],
		"GALLERY_GROUPS" => $arParams["GALLERY_GROUPS"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);
?>
</div>