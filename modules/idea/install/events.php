<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$ET = new CEventType;
$oEventType = $ET->GetList(
	array("EVENT_NAME" => "ADD_IDEA_COMMENT")
);
if(!$oEventType->Fetch())
{
	$oLang = CLanguage::GetList();
	while ($arLang = $oLang->Fetch())
	{
		IncludeModuleLangFile(__FILE__, $arLang["LID"]);
		//Event Type
		$ET->Add(
			array(
				"LID" => $arLang["LID"],
				"EVENT_NAME" => "ADD_IDEA_COMMENT",
				"NAME" => GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT"),
				"DESCRIPTION" =>
					'#FULL_PATH# - '.GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT_PARAM_FULL_PATH")."\n".
					'#IDEA_TITLE# - '.GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT_PARAM_IDEA_TITLE")."\n".
					'#AUTHOR# - '.GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT_PARAM_AUTHOR")."\n".
					'#IDEA_COMMENT_TEXT# - '.GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT_PARAM_IDEA_COMMENT_TEXT")."\n".
					'#DATE_CREATE# - '.GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT_PARAM_DATE_CREATE")."\n".
					'#EMAIL_TO# - '.GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT_PARAM_EMAIL_TO")
			)
		);

		$arSites = array();
		$oSite = CLang::GetList("", "", Array("LANGUAGE_ID" => $arLang["LID"]));
		while ($arSite = $oSite->Fetch())
			$arSites[] = $arSite["LID"];

		//Template for Event Type
		if(!empty($arSites))
		{
			$EM = new CEventMessage;
			$EM->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "ADD_IDEA_COMMENT",
				"LID" => $arSites,
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#EMAIL_TO#",
				"SUBJECT" => "#SITE_NAME#: ".GetMessage("IDEA_EVENT_ADD_IDEA_COMMENT").": #IDEA_TITLE#",
				"MESSAGE" => GetMessage("ADD_IDEA_COMMENT_TEMPLATE"),
				"BODY_TYPE" => "text",
			));
		}
	}
}

$oEventType = $ET->GetList(
	array("EVENT_NAME" => "ADD_IDEA")
);
if(!$oEventType->Fetch())
{
	$oLang = CLanguage::GetList();
	while ($arLang = $oLang->Fetch())
	{
		IncludeModuleLangFile(__FILE__, $arLang["LID"]);
		//Event Type
		$ET->Add(array(
			"LID" => $arLang["LID"],
			"EVENT_NAME" => "ADD_IDEA",
			"NAME" => GetMessage("IDEA_EVENT_ADD_IDEA"),
			"DESCRIPTION" =>
				'#FULL_PATH# - '.GetMessage("IDEA_EVENT_ADD_IDEA_PARAM_FULL_PATH")."\n".
				'#TITLE# - '.GetMessage("IDEA_EVENT_ADD_IDEA_PARAM_TITLE")."\n".
				'#AUTHOR# - '.GetMessage("IDEA_EVENT_ADD_IDEA_PARAM_AUTHOR")."\n".
				'#IDEA_TEXT# - '.GetMessage("IDEA_EVENT_ADD_IDEA_PARAM_IDEA_TEXT")."\n".
				'#DATE_PUBLISH# - '.GetMessage("IDEA_EVENT_ADD_IDEA_PARAM_DATE_PUBLISH")."\n".
				'#EMAIL_TO# - '.GetMessage("IDEA_EVENT_ADD_IDEA_PARAM_EMAIL_TO")."\n".
				'#CATEGORY# - '.GetMessage("IDEA_EVENT_ADD_IDEA_PARAM_CATEGORY")
		));

		$arSites = array();
		$oSite = CLang::GetList("", "", Array("LANGUAGE_ID" => $arLang["LID"]));
		while ($arSite = $oSite->Fetch())
			$arSites[] = $arSite["LID"];

		//Template for Event Type
		if(!empty($arSites))
		{
			$EM = new CEventMessage;
			$EM->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "ADD_IDEA",
				"LID" => $arSites,
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#EMAIL_TO#",
				"SUBJECT" => "#SITE_NAME#: ".GetMessage("IDEA_EVENT_ADD_IDEA").": #IDEA_TITLE#",
				"MESSAGE" => GetMessage("ADD_IDEA_TEMPLATE"),
				"BODY_TYPE" => "text"
			));
		}
	}
}
?>