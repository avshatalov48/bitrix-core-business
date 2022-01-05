<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
	return;

if (WIZARD_INSTALL_DEMO_DATA)	
{
	$APPLICATION->SetGroupRight("socialnetwork", 1, "W");
	COption::SetOptionString("socialnetwork", "GROUP_DEFAULT_RIGHT", "D");
	COption::SetOptionString("socialnetwork", "allow_frields", "Y", false, WIZARD_SITE_ID);
	COption::SetOptionString("socialnetwork", "subject_path_template", WIZARD_SITE_DIR."groups/group/search/#subject_id#/", false, WIZARD_SITE_ID);
	COption::SetOptionString("socialnetwork", "group_path_template", WIZARD_SITE_DIR."groups/group/#group_id#/", false, WIZARD_SITE_ID);

	if (WIZARD_THEME_ID == "blue" || WIZARD_THEME_ID == "green")
		$sm_theme = WIZARD_THEME_ID;
	else
		$sm_theme = "grey";

	$arTooltipFieldsDefault = array(
		"PERSONAL_ICQ",
		"PERSONAL_BIRTHDAY",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
	);

	$arTooltipPropertiesDefault = array(
		"UF_SKYPE",
		"UF_TWITTER",
	);

	COption::SetOptionString("socialnetwork", "tooltip_fields", serialize($arTooltipFieldsDefault), "", WIZARD_SITE_ID); 
	COption::SetOptionString("socialnetwork", "tooltip_properties", serialize($arTooltipPropertiesDefault), "", WIZARD_SITE_ID); 

	COption::SetOptionString("main", "wizard_".WIZARD_TEMPLATE_ID."_sm_theme_id", $sm_theme, "", WIZARD_SITE_ID); 

	$cnt = CSocNetGroupSubject::GetList(array(), array("SITE_ID" => WIZARD_SITE_ID), array());
	if (intval($cnt) > 0)
		return;

	$arGroupSubjects = array();
	$arGroupSubjectsId = array();

	for ($i = 0; $i < 2; $i++)
	{
		$arGroupSubjects[$i] = array(
			"SITE_ID" => WIZARD_SITE_ID,
			"NAME" => GetMessage("SONET_GROUP_SUBJECT_".$i),
		);
		$arGroupSubjectsId[$i] = 0;
	}

	$errorMessage = "";

	foreach ($arGroupSubjects as $ind => $arGroupSubject)
	{
		$dbSubject = CSocNetGroupSubject::GetList(
			array(),
			$arGroupSubject
		);
		if (!$dbSubject->Fetch())
		{
			$idTmp = CSocNetGroupSubject::Add($arGroupSubject);
			if ($idTmp)
			{
				$arGroupSubjectsId[$ind] = intval($idTmp);
			}
			else
			{
				if ($e = $GLOBALS["APPLICATION"]->GetException())
					$errorMessage .= $e->GetString();
			}
		}
	}

	if ($errorMessage == '')
	{
		$pathToImages = WIZARD_SERVICE_ABSOLUTE_PATH."/images/";

		$arGroupsId = array(0 => 0, 1 => 0, 2 => 0);
		$arGroups = array(
			0 => array(
				"SITE_ID" => WIZARD_SITE_ID,
				"NAME" => GetMessage("SONET_GROUP_NAME_0"),
				"DESCRIPTION" => GetMessage("SONET_GROUP_DESCRIPTION_0"),
				"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"ACTIVE" => "Y",
				"VISIBLE" => "Y",
				"OPENED" => "Y",
				"SUBJECT_ID" => $arGroupSubjectsId[0],
				"OWNER_ID" => 1,
				"KEYWORDS" => GetMessage("SONET_GROUP_KEYWORDS_0"),
				"IMAGE_ID" => array(
					"name" => "0.gif",
					"type" => "image/gif",
					"tmp_name" => $pathToImages."/0.gif",
					"error" => "0",
					"size" => @filesize($pathToImages."/0.gif"),
					"MODULE_ID" => "socialnetwork"
				),
				"NUMBER_OF_MEMBERS" => 1,
				"INITIATE_PERMS" => "E",
				"SPAM_PERMS" => "N",
				"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
			),
			1 => array(
				"SITE_ID" => WIZARD_SITE_ID,
				"NAME" => GetMessage("SONET_GROUP_NAME_1"),
				"DESCRIPTION" => GetMessage("SONET_GROUP_DESCRIPTION_1"),
				"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"ACTIVE" => "Y",
				"VISIBLE" => "Y",
				"OPENED" => "Y",
				"SUBJECT_ID" => $arGroupSubjectsId[0],
				"OWNER_ID" => 1,
				"KEYWORDS" => GetMessage("SONET_GROUP_KEYWORDS_1"),
				"IMAGE_ID" => array(
					"name" => "1.jpg",
					"type" => "image/jpeg",
					"tmp_name" => $pathToImages."/1.jpg",
					"error" => "0",
					"size" => @filesize($pathToImages."/1.jpg"),
					"MODULE_ID" => "socialnetwork"
				),
				"NUMBER_OF_MEMBERS" => 1,
				"INITIATE_PERMS" => "E",
				"SPAM_PERMS" => "N",
				"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
			),
			2 => array(
				"SITE_ID" => WIZARD_SITE_ID,
				"NAME" => GetMessage("SONET_GROUP_NAME_2"),
				"DESCRIPTION" => GetMessage("SONET_GROUP_DESCRIPTION_2"),
				"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"ACTIVE" => "Y",
				"VISIBLE" => "N",
				"OPENED" => "N",
				"SUBJECT_ID" => $arGroupSubjectsId[1],
				"OWNER_ID" => 1,
				"KEYWORDS" => GetMessage("SONET_GROUP_KEYWORDS_2"),
				"NUMBER_OF_MEMBERS" => 1,
				"SPAM_PERMS" => "N",
				"INITIATE_PERMS" => "E",
				"=DATE_ACTIVITY" => $GLOBALS["DB"]->CurrentTimeFunction(),
			),
		);

		foreach ($arGroups as $ind => $arGroup)
		{
			$dbSubject = CSocNetGroup::GetList(
				array(),
				array(
					"NAME" => $arGroup["NAME"],
					"SITE_ID" => WIZARD_SITE_ID
				)
			);
			if (!$dbSubject->Fetch())
			{
				$idTmp = CSocNetGroup::Add($arGroup);
				if ($idTmp)
				{
					$arGroupsId[$ind] = intval($idTmp);
				}
				else
				{
					if ($e = $GLOBALS["APPLICATION"]->GetException())
						$errorMessage .= $e->GetString();
				}
			}
		}
	}

	if ($errorMessage == '')
	{
		foreach ($arGroupsId as $ind => $val)
		{
			CSocNetUserToGroup::Add(
				array(
					"USER_ID" => 1,
					"GROUP_ID" => $val,
					"ROLE" => "A",
					"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
					"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
					"INITIATED_BY_USER_ID" => 1,
					"MESSAGE" => false,
				)
			);
		}
	}

	socialnetwork::__SetLogFilter(WIZARD_SITE_ID);
}
?>