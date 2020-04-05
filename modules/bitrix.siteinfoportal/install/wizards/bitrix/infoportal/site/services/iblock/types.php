<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;

	
COption::SetOptionString('iblock', 'use_htmledit', 'Y');

$arTypes = Array(
	Array(
			"ID" => "photos",
			"SECTIONS" => "Y",
			"IN_RSS" => "N",
			"SORT" => 100,
			"LANG" => Array(),
		),
	Array(
		"ID" => "news",
		"SECTIONS" => "Y",
		"IN_RSS" => "Y",
		"SORT" => 200,
		"LANG" => Array(),
	),
	Array(
		"ID" => "services",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 300,
		"LANG" => Array(),
	),
	Array(
		"ID" => "photos",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 100,
		"LANG" => Array(),
	),
	Array(
		"ID" => "job",
		"SECTIONS" => "Y",
		"IN_RSS" => "N",
		"SORT" => 400,
		"LANG" => Array(),
	),
);

$arLanguages = Array();
$rsLanguage = CLanguage::GetList($by, $order, array());
while($arLanguage = $rsLanguage->Fetch())
	$arLanguages[] = $arLanguage["LID"];

$iblockType = new CIBlockType;
foreach($arTypes as $arType)
{
	$dbType = CIBlockType::GetList(Array(),Array("=ID" => $arType["ID"]));
	if($dbType->Fetch()){
		$iblockType->Update($arType["ID"], Array("SECTIONS" => "Y"));
	}
	else 
	{
		foreach($arLanguages as $languageID)
		{
			WizardServices::IncludeServiceLang("type.php", $languageID);
	
			$code = strtoupper($arType["ID"]);
			$arType["LANG"][$languageID]["NAME"] = GetMessage($code."_TYPE_NAME");
			$arType["LANG"][$languageID]["ELEMENT_NAME"] = GetMessage($code."_ELEMENT_NAME");
	
			if ($arType["SECTIONS"] == "Y")
				$arType["LANG"][$languageID]["SECTION_NAME"] = GetMessage($code."_SECTION_NAME");
		}
		$iblockType->Add($arType);
	}
}

?> 