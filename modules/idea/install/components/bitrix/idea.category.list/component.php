<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('blog'))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule('idea'))
{
	ShowError(GetMessage("IDEA_MODULE_NOT_INSTALL"));
	return;
}

$arResult["ITEMS"] = array();
$previousDepthLevel = 1;
$index = 0;
$arParams["IBLOCK_CATEGORIES"] = (array_key_exists("IBLOCK_CATEGORIES", $arParams) ? $arParams["IBLOCK_CATEGORIES"] : $arParams["IBLOCK_CATOGORIES"]);
$arResult["CATEGORY"] = CIdeaManagment::getInstance()->Idea()->GetCategoryList($arParams["IBLOCK_CATEGORIES"]);

foreach($arResult["CATEGORY"] as $arCategory)
{
	if ($index > 0)
		$arResult["ITEMS"][$index - 1]["IS_PARENT"] = $arCategory["DEPTH_LEVEL"] > $previousDepthLevel;
	$previousDepthLevel = $arCategory["DEPTH_LEVEL"];

	//Make only 2d level %TODO%
	//if($arCategory["DEPTH_LEVEL"]>2)
	//	$arCategory["DEPTH_LEVEL"] = 2;

	$ItemLink = $arCategory["DEPTH_LEVEL"] == 1
		?$arParams["PATH_TO_CATEGORY_1"]
		:$arParams["PATH_TO_CATEGORY_2"];

	$arSequence = CIdeaManagment::getInstance()->Idea()->GetCategorySequence($arCategory["CODE"]);
	if(!$arSequence)
		$arSequence = "";

	$ItemLink = str_replace(array("#category_1#", "#category_2#"), $arSequence, $ItemLink);
	$ItemLink = str_replace("//","/", $ItemLink);

	$arButtons = CIBlock::GetPanelButtons(
			$arCategory["IBLOCK_ID"],
			0,
			$arCategory["ID"],
			array(
				//"SECTION_BUTTONS"=>false,
				"SESSID" => false
			)
	);

	$arResult["ITEMS"][$index] = array(
		"ID" => $arCategory["ID"],
		"DEPTH_LEVEL" => $arCategory["DEPTH_LEVEL"],
		"IS_PARENT" => false,
		"TEXT" => trim($arCategory["NAME"]),
		"LINK" => $ItemLink,
		"SELECTED" => "",
		"EDIT_LINK" => $arButtons["edit"]["edit_section"],
		"DELETE_LINK" => $arButtons["edit"]["delete_section"],
	);

	$index++;
}

if(array_key_exists("SELECTED_CATEGORY", $arParams) && strlen($arParams["SELECTED_CATEGORY"])>0)
{
	$arSelected = $arResult["CATEGORY"][$arParams["SELECTED_CATEGORY"]];
	if($arSelected)
	{
		foreach($arResult["ITEMS"] as $key=>$Item)
		{
			if($arSelected["ID"] == $Item["ID"])
			{
				$arResult["ITEMS"][$key]["SELECTED"] = true;
				break;
			}
		}
	}
}

$this->IncludeComponentTemplate();
?>