<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if($arParams["SET_NAV_CHAIN_IDEA"]=="Y")
{
	$Category = $arResult["POST_PROPERTIES"]["DATA"]["UF_CATEGORY_CODE"]["VALUE"];
	$bParentCat = false;
	$arCategoryList = CIdeaManagment::getInstance()->Idea()->GetCategoryList();
	if(isset($arCategoryList[$Category]))
	{
		if($arCategoryList[$Category]["DEPTH_LEVEL"]>1)
		{
			foreach($arCategoryList as $Cat)
			{
				if($arCategoryList[$Category]["IBLOCK_SECTION_ID"] == $Cat["ID"])
				{
					$APPLICATION->AddChainItem($Cat["NAME"], str_replace("#category_1#", mb_strtolower($Cat["CODE"]), $arParams["EXT"][0]["PATH_TO_CATEGORY_1"]));
					$bParentCat = true;
					break;
				}
			}
		}
		if($bParentCat)
			$APPLICATION->AddChainItem($arCategoryList[$Category]["NAME"], str_replace(array("#category_1#","#category_2#"), array(mb_strtolower($Cat["CODE"]), mb_strtolower($Category)), $arParams["EXT"][0]["PATH_TO_CATEGORY_2"]));
		else
			$APPLICATION->AddChainItem($arCategoryList[$Category]["NAME"], str_replace(array("#category_1#"), array(mb_strtolower($Category)), $arParams["EXT"][0]["PATH_TO_CATEGORY_1"]));
		if(isset($arResult["Post"]["TITLE"]))
			$APPLICATION->AddChainItem($arResult["Post"]["TITLE"]);
	}
}
?>