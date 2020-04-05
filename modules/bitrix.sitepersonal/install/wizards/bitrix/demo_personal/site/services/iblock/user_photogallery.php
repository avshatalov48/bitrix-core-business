<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/user_photogallery.xml"; 
$iblockCode = "photogallery_personal_".WIZARD_SITE_ID; 
$iblockType = "photos"; 
$iblockID = false; 

$rsIBlock = CIBlock::GetList(array(), array("CODE" => $iblockCode, "TYPE" => $iblockType));
if ($rsIBlock && $arIBlock = $rsIBlock->Fetch())
{
	$iblockID = $arIBlock["ID"]; 
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		CIBlock::Delete($arIBlock["ID"]); 
		$iblockID = false; 
	}
}

if ($iblockID == false)
{
	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		"photogallery_personal",
		$iblockType,
		WIZARD_SITE_ID,
		$permissions = Array(
			"1" => "X",
			"2" => "R"
		)
	);

	if ($iblockID < 1)
		return;
	
	if ($iblockID > 0)
	{
		$arGalleries = unserialize(COption::GetOptionString("photogallery", "UF_GALLERY_SIZE"));
		$arGalleries = (is_array($arGalleries) ? $arGalleries : array());
		if (!$arGalleries[$iblockID])
		{
			$arGalleries[$iblockID] = Array(
			    "status" => "done",
			    "step" => 1,
			    "elements_cnt" => 13,
			    "element_number" => 13,
			    "element_id" => "",
			    "id" => "123456",
			    "date" => ""
			);
			COption::SetOptionString("photogallery", "UF_GALLERY_SIZE", serialize($arGalleries));
		}
	}

	$ibSection = new CIBlockSection;
	$dbSection = CIBlockSection::GetList(Array(), Array("ACTIVE" => "Y", "IBLOCK_ID" => $iblockID));
	while ($arSection = $dbSection->Fetch())
	{
		$arFields = Array("ACTIVE" => "Y", "CREATED_BY" => 1, "SOCNET_GROUP_ID" => false);
		if ($arSection["CODE"] == "user_1")
		{
			$rsUser = CUser::GetByID(1);
			if ($arUser = $rsUser->Fetch())
			{
				$userName = $arUser["NAME"].(strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0?"":" ").$arUser["LAST_NAME"];
				if (strlen(trim($userName)) > 0)
					$arFields["NAME"] = $userName;
			}
		}
		$ibSection->Update($arSection["ID"], $arFields);
	}
	
	$arProperties = Array("APPROVE_ELEMENT", "REAL_PICTURE", "PUBLIC_ELEMENT", "FORUM_TOPIC_ID", "FORUM_MESSAGE_CNT", "vote_count", "vote_sum", "rating");
	foreach ($arProperties as $propertyName)
	{
		${$propertyName."_PROPERTY_ID"} = 0;
		$properties = CIBlockProperty::GetList(Array(), Array("ACTIVE"=>"Y", "IBLOCK_ID" => $iblockID, "CODE" => $propertyName));
		if ($arProperty = $properties->Fetch())
			${$propertyName."_PROPERTY_ID"} = $arProperty["ID"];
	}
	
	WizardServices::SetIBlockFormSettings($iblockID, Array ( 'tabs' => GetMessage("W_IB_USER_PHOTOG_TAB1").$REAL_PICTURE_PROPERTY_ID.GetMessage("W_IB_USER_PHOTOG_TAB2").$rating_PROPERTY_ID.GetMessage("W_IB_USER_PHOTOG_TAB3").$vote_count_PROPERTY_ID.GetMessage("W_IB_USER_PHOTOG_TAB4").$vote_sum_PROPERTY_ID.GetMessage("W_IB_USER_PHOTOG_TAB5").$APPROVE_ELEMENT_PROPERTY_ID.GetMessage("W_IB_USER_PHOTOG_TAB6").$PUBLIC_ELEMENT_PROPERTY_ID.GetMessage("W_IB_USER_PHOTOG_TAB7"), ));
	
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array ( 'IBLOCK_SECTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'ACTIVE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y', ), 'ACTIVE_FROM' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'ACTIVE_TO' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SORT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'PREVIEW_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', ), ), 'PREVIEW_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'PREVIEW_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', ), ), 'DETAIL_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'DETAIL_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'CODE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'TAGS' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), ), 
		"CODE" => $iblockCode, 
		"XML_ID" => $iblockCode,
		"NAME" => "[".WIZARD_SITE_ID."] ".$iblock->GetArrayByID($iblockID, "NAME")
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

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/photo.php", array("PHOTO_IBLOCK_ID" => $iblockID, "PHOTO_SEF_FOLDER" => WIZARD_SITE_DIR."photo/"));
?>