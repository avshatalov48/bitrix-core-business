<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(CModule::IncludeModule("blog")) 
{
	$arTemplateParameters["PATH_TO_BLOG"] = array(
		"NAME" => GetMessage("SAF_TP_PATH_TO_BLOG"),
		"DEFAULT" => SITE_DIR."blogs/#blog#/",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
	);

	$arTemplateParameters["PATH_TO_NEW_BLOG"] = array(
		"NAME" => GetMessage("SAF_TP_PATH_TO_NEW_BLOG"),
		"DEFAULT" => SITE_DIR."blogs/new/blog_edit.php",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
	);

	$arGroupList = Array();
	$dbGroup = CBlogGroup::GetList(Array("SITE_ID" => "ASC", "NAME" => "ASC"));
	while($arGroup = $dbGroup->GetNext())
	{
		$arGroupList[$arGroup["ID"]] = "(".$arGroup["SITE_ID"].") [".$arGroup["ID"]."] ".$arGroup["NAME"];
	}

	$arTemplateParameters["BLOG_GROUP_ID"] = array(
		"NAME" => GetMessage("SAF_TP_BLOG_GROUP_ID"),
		"DEFAULT" => "",
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"ADDITIONAL_VALUES" => "Y",
		"VALUES" => $arGroupList,
	);
}

if(CModule::IncludeModule("socialnetwork")) 
{
	$arTemplateParameters["PATH_TO_SONET_MESSAGES"] = array(
		"NAME" => GetMessage("SAF_TP_PATH_TO_SONET_MESSAGES"),
		"DEFAULT" => SITE_DIR."club/messages/",
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"COLS" => 25,
	);
}
?>