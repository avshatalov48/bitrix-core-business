<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule("idea"))
	return;
	
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/idea.xml";
$iblockCode = "idea_".WIZARD_SITE_ID; 
$iblockType = "services";

$rsIBlock = CIBlock::GetList(array(), array("CODE" => $iblockCode, "TYPE" => $iblockType));
$iblockID = false; 
if ($arIBlock = $rsIBlock->Fetch())
{
	$iblockID = $arIBlock["ID"];
}

if($iblockID == false)
{
	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile, 
		'idea', 
		$iblockType, 
		WIZARD_SITE_ID, 
		$permissions = Array(
			"1" => "X",
			"2" => "R",
			WIZARD_PORTAL_ADMINISTRATION_GROUP => "X",
			WIZARD_PERSONNEL_DEPARTMENT_GROUP => "W",
		)
	);
        
	if ($iblockID < 1)
		return;

        //Iblock Section form settings
	$aFormOptions = array('tabs' => "edit1--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_TAB_TITLE")."--,--ID--#--ID--,--ACTIVE--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_ACTIVE")."--,--NAME--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_NAME")."--,--CODE--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_CODE")."--,--IBLOCK_SECTION_ID--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_IBLOCK_SECTION_ID")."--,--SORT--#--".GetMessage("IDEA_CATEGORY_EDIT_FORM_P_SORT")."--;--");
	WizardServices::SetUserOption(
            "form", 
            "form_section_".$iblockID, 
            $aFormOptions,
            $common = true
        );
        
        WizardServices::SetIBlockFormSettings($iblockID, $aFormOptions);

	//IBlock fields settings
	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array(
                    "SECTION_CODE" => array(
                        "IS_REQUIRED" => "Y",
                        "DEFAULT_VALUE" => array
                        (
                            "UNIQUE" => "Y",
                            "TRANSLITERATION" => "Y",
                            "TRANS_LEN" => 50,
                            "TRANS_CASE" => "L",
                            "TRANS_SPACE" => "_",
                            "TRANS_OTHER" => "_",
                            "TRANS_EAT" => "Y",
                            "USE_GOOGLE" => "Y",
                        )
                    )
                ),
		"CODE" => $iblockCode, 
		"XML_ID" => $iblockCode, 
	);
        
	$iblock->Update($iblockID, $arFields);
}
else
{
	$arSites = array(); 
	$db_res = CIBlock::GetSite($iblockID);
	while ($res = $db_res->Fetch())
		$arSites[] = $res["LID"]; 
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID; 
		$iblock = new CIBlock;
		$iblock->Update($iblockID, array("LID" => $arSites));
	}
}

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/about/idea/index.php", array("IDEA_IBLOCK_CATEGORY" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/include/feedback.php", array("IDEA_IBLOCK_CATEGORY" => $iblockID));
?>