<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

function MenuSaveSettings($arParams, $POS)
{
	$arUserOptions = CUserOptions::GetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], false, 0);
	
	if(!is_array($arUserOptions))
		$arUserOptions = Array("FEATURES"=>Array(), "MAX_ITEMS" => 6);
		
	$arNewUserOptions = Array("FEATURES"=>Array(), "MAX_ITEMS" => $arUserOptions["MAX_ITEMS"]);
	
	foreach($POS as $col=>$itemId)
	{
		if(is_array($arUserOptions["FEATURES"][$itemId]))
			$arNewUserOptions["FEATURES"][$itemId] = $arUserOptions["FEATURES"][$itemId];
		else
			$arNewUserOptions["FEATURES"][$itemId] = Array();

		$arNewUserOptions["FEATURES"][$itemId]["INDEX"] = $col;
	}

	CUserOptions::SetOption("socialnetwork", "~menu_".$arParams["ENTITY_TYPE"]."_".$arParams["ENTITY_ID"], $arNewUserOptions, false, 0);
}

?>