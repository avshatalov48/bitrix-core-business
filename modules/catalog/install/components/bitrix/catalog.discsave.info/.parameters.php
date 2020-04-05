<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("catalog")):
	ShowError(GetMessage('BX_CMP_CDI_ERR_MODULE_CATALOG_ABSENT'));
	return;
endif;

$arSiteList = array();
$strDefSite = '';
$rsSites = CSite::GetList($by="sort", $order="desc");
while ($arSite = $rsSites->GetNext())
{
	if ('Y' == $arSite['DEF'])
		$strDefSite = $arSite["ID"];
	$arSiteList[$arSite["ID"]] = "[".$arSite["ID"]."] ".$arSite["NAME"];
}

$arComponentParameters = array(
	'GROUPS' => array(
	),
	'PARAMETERS' => array(
		"SITE_ID" => array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("BX_CMP_CDI_PARAM_TITLE_SITE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arSiteList,
			"ADDITIONAL_VALUES" => "N",
			"REFRESH" => "N",
			"MULTIPLE" => "N",
			"DEFAULT" => $strDefSite,
		),
		"USER_ID" => array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("BX_CMP_CDI_PARAM_TITLE_USER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"SHOW_NEXT_LEVEL" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage('BX_CMP_CDI_PARAM_TITLE_SHOW_NEXT_LEVEL'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
	),
);
?>