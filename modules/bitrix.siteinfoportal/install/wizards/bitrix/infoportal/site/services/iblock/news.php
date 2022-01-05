<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;

$lang = (in_array(LANGUAGE_ID, array("ru", "en", "de"))) ? LANGUAGE_ID : \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".$lang."/news.xml";
$iblockCode = "infoportal_news_".WIZARD_SITE_ID; 
$iblockType = "news"; 

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
	$dbGroup = CGroup::GetList("", "", Array("STRING_ID" => "info_administrator"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	};
	$dbGroup = CGroup::GetList("", "", Array("STRING_ID" => "content_editor"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	};
	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		"infoportal_news",
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
		"FIELDS" => array ( 'IBLOCK_SECTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'ACTIVE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y', ), 'ACTIVE_FROM' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '=today', ), 'ACTIVE_TO' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SORT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'PREVIEW_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ), 'PREVIEW_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'PREVIEW_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ), 'DETAIL_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'html', ), 'DETAIL_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'CODE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), 'TAGS' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'SECTION_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ), 'SECTION_DESCRIPTION_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'SECTION_DESCRIPTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ), 'SECTION_XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_CODE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), ),
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
$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID" => $iblockID));
while($arProp = $dbProperty->Fetch())
	$arProperty[$arProp["CODE"]] = $arProp["ID"];
	
$dbSite = CSite::GetByID(WIZARD_SITE_ID);
if($arSite = $dbSite -> Fetch())
	$lang = $arSite["LANGUAGE_ID"];
if($lang == '')
	$lang = "ru";
	

$iblockCodeTheme = "infoportal_theme_".WIZARD_SITE_ID; 
$iblockTypeTheme = "news";
$rsIBlockTheme = CIBlock::GetList(array(), array("XML_ID" => $iblockCodeTheme, "TYPE" => $iblockTypeTheme));
if ($arIBlockTheme = $rsIBlockTheme->Fetch())
{
	$iblockIDTheme = $arIBlockTheme["ID"];
	$arFields = Array(
		"LINK_IBLOCK_TYPE_ID" => $iblockTypeTheme,
		"LINK_IBLOCK_ID" => $iblockIDTheme,
		"USER_TYPE" => "EAutocomplete",
		"USER_TYPE_SETTINGS" => array("MAX_WIDTH" => 250, "SHOW_ADD" => "Y", "IBLOCK_MESS" => "Y")
	);
  
	$ibprop = new CIBlockProperty;
	$ibprop->Update($arProperty["THEME"], $arFields);
	
	if($lang == 'ru')
	{
		$arThemeElement = array(
			"277" => array("254", "253"),
			"256" => array("259", "258", "255"),
		);
	}
	elseif($lang == 'en')
	{
		$arThemeElement = array(
			"161" => array("1", "8", "92"),
			"163" => array("169", "88", "89", "93"),
		);
	}
	elseif($lang == 'de')
	{
		$arThemeElement = array(
			"161" => array("35", "88", "77"),
			"163" => array("38", "83", "82", "166"),
		);
	}
	foreach ($arThemeElement as $ThemeID => $ThemeElements){
		$rsElemetTheme = CIBlockElement::GetList(array("show_counter"=>"desc"), array("XML_ID" => $ThemeID, "IBLOCK_ID" => $iblockIDTheme));
		if ($arElemetTheme = $rsElemetTheme->Fetch())
		{
			$elementIDTheme = $arElemetTheme["ID"];
			
			foreach ($ThemeElements as $ElementID){
				$rsElemet = CIBlockElement::GetList(array("show_counter"=>"desc"), array("XML_ID" => $ElementID, "IBLOCK_ID" => $iblockID), false, false, array("ID"));
				if ($arElemet = $rsElemet->Fetch())
				{
					CIBlockElement::SetPropertyValuesEx($arElemet["ID"], $iblockID, array("THEME" => $elementIDTheme));
				}	
			}
		}
	}
}

$arProperty = Array();
$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID" => $iblockID));
while($arProp = $dbProperty->Fetch())
	$arProperty[$arProp["CODE"]] = $arProp["ID"];


WizardServices::IncludeServiceLang("news.php", $lang);
CUserOptions::SetOption("form", "form_element_".$iblockID, array ( 
	'tabs' => 'edit1--#--'.GetMessage("WZD_OPTION_NEWS_1").'--,--ACTIVE_FROM--#--'.GetMessage("WZD_OPTION_NEWS_3").'--,--NAME--#--'.GetMessage("WZD_OPTION_NEWS_5").'--,--CODE--#--'.GetMessage("WZD_OPTION_NEWS_11").'--,--PREVIEW_PICTURE--#--'.GetMessage("WZD_OPTION_NEWS_4").'--,--PROPERTY_'.$arProperty['MAIN'].'--#--'.GetMessage("WZD_OPTION_NEWS_9").'--,--PROPERTY_'.$arProperty['PARTMAIN'].'--#--'.GetMessage("WZD_OPTION_NEWS_8").'--,--PROPERTY_'.$arProperty['LINK_SOURCE'].'--#--'.GetMessage("WZD_OPTION_NEWS_6").'--,--PROPERTY_'.$arProperty['THEME'].'--#--'.GetMessage("WZD_OPTION_NEWS_7").'--,--PREVIEW_TEXT--#--'.GetMessage("WZD_OPTION_NEWS_15").'--,--DETAIL_TEXT--#--'.GetMessage("WZD_OPTION_NEWS_10").'--,--SECTIONS--#--'.GetMessage("WZD_OPTION_NEWS_13").'--;--', )
);

CUserOptions::SetOption("list", "tbl_iblock_list_".md5($iblockType.".".$iblockID), array ( 'columns' => 'NAME,ACTIVE,DATE_ACTIVE_FROM', 'by' => 'timestamp_x', 'order' => 'desc', 'page_size' => '20', ));

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/news/index.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/themes/index.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.top.menu_ext.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/rss_mainnews.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/rss_news.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/rss_theme.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/board/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/about/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/agreement/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/advertising/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/information/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/login/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/nationalnews/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/vote/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/themes/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/news/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/personal/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/login/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/search/sect_rtop.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_ROOT_PATH."/bitrix/templates/".$templateID."_".$themeID."/components/bitrix/news.list/main_theme/template.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_ROOT_PATH."/bitrix/templates/".$templateID."_".$themeID."/footer.php", array("NEWS_IBLOCK_ID" => $iblockID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_ROOT_PATH."/bitrix/templates/".$templateID."_".$themeID."/components/bitrix/menu/horizontal_multilevel/component_epilog.php", array("NEWS_IBLOCK_ID" => $iblockID));

if(cmodule::includemodule('search')){
	$CustomRank = new CSearchCustomRank;
	$arFilter = Array(
		"SITE_ID"	=> WIZARD_SITE_ID,
		"MODULE_ID"	=> "iblock",
		"PARAM1"	=> $iblockType,
		"RANK"		=> 1000,
		"APPLIED" => "N"
	);
	
	$dbCustomRank = $CustomRank->GetList(array(), $arFilter);
	if($arCustomRank = $dbCustomRank->Fetch()){
		$IDCustomRank = $arCustomRank["ID"];
	}
	
	$arFields = Array(
		"SITE_ID"	=> WIZARD_SITE_ID,
		"MODULE_ID"	=> "iblock",
		"PARAM1"	=> $iblockType,
		"PARAM2"	=> $iblockID,
		"RANK"		=> 1000,
		"APPLIED" => "N"
	);
	
	if($IDCustomRank > 0)
	{
		if (WIZARD_INSTALL_DEMO_DATA)
		{
			$CustomRank->Update($IDCustomRank,$arFields);
			$CustomRank->StartUpdate();
			$CustomRank->NextUpdate();
		}
	}
	else
	{
		$CustomRank->Add($arFields);
		$CustomRank->StartUpdate();
		$CustomRank->NextUpdate();
	}
}
?>