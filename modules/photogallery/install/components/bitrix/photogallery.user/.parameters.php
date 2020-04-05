<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["~NAME"];
	}
}

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}
$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y"),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock),
		"USER_ALIAS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_USER_ALIAS"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["USER_ALIAS"]}'),
		
		"INDEX_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("P_INDEX_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"GALLERY_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("P_GALLERY_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "gallery.php&USER_ALIAS=#USER_ALIAS#"),
		"GALLERIES_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("P_GALLERIES_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "galleries.php&USER_ID=#USER_ID#"),
		"GALLERY_EDIT_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("P_GALLERY_EDIT_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "gallery_edit.php?USER_ALIAS=#USER_ALIAS#&ACTION=#ACTION#"),
		"UPLOAD_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("P_UPLOAD_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "upload.php&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#&ACTION=upload"),
		
		"ONLY_ONE_GALLERY" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_ONLY_ONE_GALLERY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"GALLERY_GROUPS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_GROUPS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arUGroupsEx),
		"GALLERY_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "50"),
		"GALLERY_AVATAR_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_AVATAR_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "50"), 
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600)
	),
);
?>