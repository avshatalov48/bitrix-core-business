<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach ($arResult["ITEMS"] as $key => $arItem) {
	$arResult["ITEMS"][$key]["COUNT_NEWS"] = 0;
	$rsElemet = CIBlockElement::GetList(array("show_counter"=>"desc"), array("PROPERTY_THEME" => $arItem["ID"], "IBLOCK_LID" => SITE_ID, "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "Y"), false, false, array("ID"));
	while ($arElemet = $rsElemet->Fetch())
	{
		$arResult["ITEMS"][$key]["COUNT_NEWS"]++;
	}
	
}
?>