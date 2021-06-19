<?
function UET($EVENT_NAME, $NAME, $LID, $DESCRIPTION)
{
	$et = new CEventType;
	$et->Add(Array(
		"LID" => $LID,
		"EVENT_NAME" => $EVENT_NAME,
		"NAME" => $NAME,
		"DESCRIPTION" => $DESCRIPTION
		)
	);
}

$em = new CEventMessage;
$langs = CLanguage::GetList();
while ($lang = $langs->Fetch())
{
	IncludeModuleLangFile(__FILE__, $lang["LID"]);
	$arSites = array();
	$sites = CLang::GetList('', '', Array("LANGUAGE_ID"=>$lang["LID"]));
	while ($site = $sites->Fetch())
	{
		$arSites[] = $site["LID"];
	}

	///////////////////// NEW_FORUM_MESSAGE /////////////////////
	$fres = CEventType::GetList(array("EVENT_NAME" => "NEW_FORUM_MESSAGE", "LID" => $lang["LID"]));
	if (!$fres->Fetch())
	{
		UET("NEW_FORUM_MESSAGE", GetMessage("F_NEW_MESSAGE_ON_FORUM"), $lang["LID"],
			"
			#FORUM_ID# - ".GetMessage("F_FORUM_ID")."
			#FORUM_NAME# - ".GetMessage("F_FORUM_NAME")."
			#TOPIC_ID# - ".GetMessage("F_TOPIC_ID")."
			#MESSAGE_ID# - ".GetMessage("F_MESSAGE_ID")."
			#TOPIC_TITLE# - ".GetMessage("F_TOPIC_TITLE")."
			#MESSAGE_TEXT# - ".GetMessage("F_MESSAGE_TEXT")."
			#MESSAGE_DATE# - ".GetMessage("F_MESSAGE_DATE")."
			#AUTHOR# - ".GetMessage("F_MESSAGE_AUTHOR")."
			#RECIPIENT# - ".GetMessage("F_MAIL_RECIPIENT")."
			#TAPPROVED# - ".GetMessage("F_MAIL_TAPPROVED")."
			#MAPPROVED# - ".GetMessage("F_MAIL_MAPPROVED")."
			#PATH2FORUM# - ".GetMessage("F_MAIL_PATH2FORUM")."
			#FROM_EMAIL# - ".GetMessage("F_MAIL_FROM_EMAIL"));
		
		if (is_array($arSites) && count($arSites)>0)
		{
			//****************************************************************
			$em->Add(
				Array(
					"ACTIVE" => "Y",
					"EVENT_NAME" => "NEW_FORUM_MESSAGE",
					"LID" => $arSites,
					"EMAIL_FROM" => "#FROM_EMAIL#",
					"EMAIL_TO" => "#RECIPIENT#",
					"SUBJECT" => "#SITE_NAME#: [F] #TOPIC_TITLE# : #FORUM_NAME#",
					"MESSAGE" => GetMessage("F_MAIL_TEXT"),
					"BODY_TYPE"=>"text"));
			//****************************************************************
		}
	}

	$fres = CEventType::GetList(array("EVENT_NAME" => "NEW_FORUM_PRIV", "LID" => $lang["LID"]));
	if (!$fres->Fetch())
	{
		UET("NEW_FORUM_PRIV", GetMessage("F_PRIV"), $lang["LID"],
			"
			#FROM_NAME# - ".Getmessage("F_PRIV_AUTHOR")."
			#FROM_EMAIL# - ".GetMessage("F_PRIV_AUTHOR_EMAIL")."
			#TO_NAME# - ".GetMessage("F_PRIV_RECIPIENT_NAME")."
			#TO_EMAIL# - ".GetMessage("F_PRIV_RECIPIENT_EMAIL")."
			#SUBJECT# - ".GetMessage("F_PRIV_TITLE")."
			#MESSAGE# - ".GetMessage("F_PRIV_TEXT")."
			#MESSAGE_DATE# - ".GetMessage("F_PRIV_DATE"));
		if (is_array($arSites) && count($arSites)>0)
		{
			//****************************************************************
			$em->Add(Array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "NEW_FORUM_PRIV",
				"LID" => $arSites,
				"EMAIL_FROM" => "#FROM_EMAIL#",
				"EMAIL_TO" => "#TO_EMAIL#",
				"SUBJECT" => "#SITE_NAME#: [private] #SUBJECT#",
				"MESSAGE" => GetMessage("F_PRIV_MAIL"),
				"BODY_TYPE"=>"text"));
		//****************************************************************
		}
	}

	///////////////////// NEW_FORUM_PRIVATE_MESSAGE /////////////////////
	$fres = CEventType::GetList(array("EVENT_NAME" => "NEW_FORUM_PRIVATE_MESSAGE", "LID" => $lang["LID"]));
	if (!$fres->Fetch())
	{
		UET("NEW_FORUM_PRIVATE_MESSAGE", GetMessage("F_PRIVATE"), $lang["LID"],
			"
			#FROM_NAME# - ".GetMessage("F_PRIVATE_AUTHOR")."
			#FROM_USER_ID# - ".GetMessage("F_PRIVATE_AUTHOR_ID")."
			#FROM_EMAIL# - ".GetMessage("F_PRIVATE_AUTHOR_EMAIL")."
			#TO_NAME# - ".GetMessage("F_PRIVATE_RECIPIENT_NAME")."
			#TO_USER_ID# - ".GetMessage("F_PRIVATE_RECIPIENT_ID")."
			#TO_EMAIL# - ".GetMessage("F_PRIVATE_RECIPIENT_EMAIL")."
			#SUBJECT# - ".GetMessage("F_PRIVATE_SUBJECT")."
			#MESSAGE# - ".GetMessage("F_PRIVATE_MESSAGE")."
			#MESSAGE_DATE# - ".GetMessage("F_PRIVATE_MESSAGE_DATE")."
			#MESSAGE_LINK# - ".GetMessage("F_PRIVATE_MESSAGE_LINK"));
		if (is_array($arSites) && count($arSites)>0)
		{
		//****************************************************************
			$em->Add(Array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "NEW_FORUM_PRIVATE_MESSAGE",
				"LID" => $arSites,
				"EMAIL_FROM" => "#FROM_EMAIL#",
				"EMAIL_TO" => "#TO_EMAIL#",
				"SUBJECT" => "#SITE_NAME#: [private] #SUBJECT#",
				"MESSAGE" => GetMessage("F_PRIVATE_TEXT"),
				"BODY_TYPE"=>"text"
				));
			//****************************************************************
		}
	}

	///////////////////// EDIT_FORUM_MESSAGE /////////////////////
	$fres = CEventType::GetList(array("EVENT_NAME" => "EDIT_FORUM_MESSAGE", "LID" => $lang["LID"]));
	if (!$fres->Fetch())
	{
		UET(
			"EDIT_FORUM_MESSAGE", GetMessage("F_EDITM"), $lang["LID"],
			"
			#FORUM_ID# - ".GetMessage("F_FORUM_ID")."
			#FORUM_NAME# - ".GetMessage("F_FORUM_NAME")."
			#TOPIC_ID# - ".GetMessage("F_TOPIC_ID")."
			#MESSAGE_ID# - ".GetMessage("F_MESSAGE_ID")."
			#TOPIC_TITLE# - ".GetMessage("F_TOPIC_TITLE")."
			#MESSAGE_TEXT# - ".GetMessage("F_MESSAGE_TEXT")."
			#MESSAGE_DATE# - ".GetMessage("F_MESSAGE_DATE")."
			#AUTHOR# - ".GetMessage("F_MESSAGE_AUTHOR")."
			#RECIPIENT# - ".GetMessage("F_MAIL_RECIPIENT")."
			#TAPPROVED# - ".GetMessage("F_MAIL_TAPPROVED")."
			#MAPPROVED# - ".GetMessage("F_MAIL_MAPPROVED")."
			#PATH2FORUM# - ".GetMessage("F_MAIL_PATH2FORUM")."
			#FROM_EMAIL# - ".GetMessage("F_MAIL_FROM_EMAIL"));
		if (is_array($arSites) && count($arSites)>0)
		{
			//****************************************************************
			$em->Add(Array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "EDIT_FORUM_MESSAGE",
				"LID" => $arSites,
				"EMAIL_FROM" => "#FROM_EMAIL#",
				"EMAIL_TO" => "#RECIPIENT#",
				"SUBJECT" => "#SITE_NAME#: [F] #TOPIC_TITLE# : #FORUM_NAME#",
				"MESSAGE" => GetMessage("F_EDITM_TEXT"),
				"BODY_TYPE"=>"text"));
		}
	}
}


$arEventsType = array("FORUM_NEW_MESSAGE_MAIL");
$OLD_MESS = $MESS;
$langs = CLanguage::GetList();
while ($lang = $langs->Fetch())
{
	$arSites = array();
	$sites = CLang::GetList('', '', Array("LANGUAGE_ID"=>$lang["LID"]));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	$langFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/lang/".$lang["LID"]."/install/events.php";
	if (!file_exists($langFile))
		continue;

	$MESS = Array();
	include($langFile);

	foreach($arEventsType as $event)
	{
		$fres = CEventType::GetList(array("EVENT_NAME" => $event, "LID" => $lang["LID"]));
		if (!($fres->Fetch()))
		{
			$et = new CEventType;
			$et->Add(array(
				"LID" => $lang["LID"],
				"EVENT_NAME" => $event,
				"NAME" => $MESS[$event."_NAME"],
				"DESCRIPTION" => $MESS[$event."_DESC"],
			));
			
			if (is_array($arSites) && count($arSites)>0)
			{
				$em = new CEventMessage();
				$em->Add(Array(
					"ACTIVE" => "Y",
					"EVENT_NAME" => $event,
					"LID" => $arSites,
					"EMAIL_FROM" => "#FROM_EMAIL#",
					"EMAIL_TO" => "#RECIPIENT#",
					"SUBJECT" => "#TOPIC_TITLE#",
					"MESSAGE" => $MESS[$event."_MESSAGE"],
					"BODY_TYPE"=>"text"
					));
			}
		}
	}
}
$MESS = $OLD_MESS;
?>