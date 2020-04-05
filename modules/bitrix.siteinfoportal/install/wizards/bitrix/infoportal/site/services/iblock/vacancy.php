<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;

$lang = (in_array(LANGUAGE_ID, array("ru", "en", "de"))) ? LANGUAGE_ID : \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".$lang."/vacancy.xml";
$iblockCode = "infoportal_vacancy_".WIZARD_SITE_ID; 
$iblockType = "job"; 

$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => $iblockCode, "TYPE" => $iblockType));
$iblockID = false; 
if ($arIBlock = $rsIBlock->Fetch())
{
	$iblockID = $arIBlock["ID"]; 
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		CIBlock::Delete($arIBlock["ID"]); 
		$iblockID = false; 
	}
}

if($iblockID == false)
{
	$permissions = Array(
			"1" => "X",
			"2" => "R"
		);
	$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "info_administrator"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	};
	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		"infoportal_vacancy",
		$iblockType,
		WIZARD_SITE_ID,
		$permissions
	);

	if ($iblockID < 1)
		return;
	
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array ( 'IBLOCK_SECTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_CODE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), 'ACTIVE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y', ), 'ACTIVE_FROM' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '=today', ), 'ACTIVE_TO' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SORT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'PREVIEW_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ), ),
		"CODE" => $iblockCode, 
		"XML_ID" => $iblockCode,
		//"NAME" => "[".WIZARD_SITE_ID."] ".$iblock->GetArrayByID($iblockID, "NAME")
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

$arProperty = Array();
$codeProperty = '';
$i = 4;
$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID" => $iblockID));
while($arProp = $dbProperty->Fetch()){
	$arProperty[$arProp["CODE"]] = $arProp["ID"];
	$codeProperty .= $i++ . ' => "' . $arProp["ID"] . '",';
}
	
$codeRequired = '3 => "' . $arProperty['FIRM'] . '", 4 => "' . $arProperty['PHONE'] . '",  5 => "' . $arProperty['EMAIL'] . '", '; 

$dbSite = CSite::GetByID(WIZARD_SITE_ID);
if($arSite = $dbSite -> Fetch())
	$lang = $arSite["LANGUAGE_ID"];
if(strlen($lang) <= 0)
	$lang = "ru";
	
WizardServices::IncludeServiceLang("vacancy.php", $lang);
CUserOptions::SetOption("form", "form_element_".$iblockID, array ( 'tabs' => 'edit1--#--'.GetMessage("WZD_OPTION_VACANCY_0").'--,--ACTIVE--#--'.GetMessage("WZD_OPTION_VACANCY_1").'--,--ACTIVE_FROM--#--'.GetMessage("WZD_OPTION_VACANCY_2").'--,--ACTIVE_TO--#--'.GetMessage("WZD_OPTION_VACANCY_24").'--,--NAME--#--'.GetMessage("WZD_OPTION_VACANCY_3").'--,--PROPERTY_'.$arProperty['SEX'].'--#--'.GetMessage("WZD_OPTION_VACANCY_7").'--,--PROPERTY_'.$arProperty['AGE'].'--#--'.GetMessage("WZD_OPTION_VACANCY_8").'--,--PROPERTY_'.$arProperty['EDUCATION'].'--#--'.GetMessage("WZD_OPTION_VACANCY_9").'--,--PROPERTY_'.$arProperty['EXPERIENCE'].'--#--'.GetMessage("WZD_OPTION_VACANCY_10").'--,--PROPERTY_'.$arProperty['SCHEDULE'].'--#--'.GetMessage("WZD_OPTION_VACANCY_11").'--,--PROPERTY_'.$arProperty['REMUNERATION'].'--#--'.GetMessage("WZD_OPTION_VACANCY_12").'--,--PROPERTY_'.$arProperty['SKILLS'].'--#--'.GetMessage("WZD_OPTION_VACANCY_13").'--,--PROPERTY_'.$arProperty['FIRM'].'--#--'.GetMessage("WZD_OPTION_VACANCY_14").'--,--PROPERTY_'.$arProperty['PERSON'].'--#--'.GetMessage("WZD_OPTION_VACANCY_15").'--,--PROPERTY_'.$arProperty['PHONE'].'--#--'.GetMessage("WZD_OPTION_VACANCY_16").'--,--PROPERTY_'.$arProperty['EMAIL'].'--#--'.GetMessage("WZD_OPTION_VACANCY_17").'--,--PROPERTY_'.$arProperty['URL'].'--#--'.GetMessage("WZD_OPTION_VACANCY_18").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_VACANCY_4").'--;--', ));
CUserOptions::SetOption("form", "form_section_".$iblockID, array ( 'tabs' => 'edit1--#--'.GetMessage("WZD_OPTION_VACANCY_20").'--,--NAME--#--'.GetMessage("WZD_OPTION_VACANCY_21").'--,--CODE--#--'.GetMessage("WZD_OPTION_VACANCY_22").'--,--SORT--#--'.GetMessage("WZD_OPTION_VACANCY_23").'--;--', ));

CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockType.".".$iblockID), array ( 'columns' => 'NAME,ACTIVE,DATE_ACTIVE_FROM', 'by' => 'timestamp_x', 'order' => 'desc', 'page_size' => '20', ));


CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/index.php", array("VACANCY_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/vacancy/index.php", array("VACANCY_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/vacancy/my/index.php", array("VACANCY_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/vacancy/my/index.php", array("IDS_CODE_PROPERTY" => $codeProperty));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/vacancy/my/index.php", array("IDS_CODE_REQUIRED" => $codeRequired));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_rbottom.php", array("VACANCY_IBLOCK_ID" => $iblockID));
?>