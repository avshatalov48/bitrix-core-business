<?
if ($pathInMessage <> '')
{
	$pathInMessage = Str_Replace("\\", "/", $pathInMessage);
	$pathInMessage = "/".Trim(Trim($pathInMessage), "\\/")."/";
}
$strCorectPath = (($pathInMessage == '') ? "/club/" : $pathInMessage);

$langs = CLanguage::GetList();
while ($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/install/events.php", $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_NEW_MESSAGE",
		"NAME" => GetMessage("SONET_NEW_MESSAGE_NAME"),
		"DESCRIPTION" => GetMessage("SONET_NEW_MESSAGE_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_INVITE_FRIEND",
		"NAME" => GetMessage("SONET_INVITE_FRIEND_NAME"),
		"DESCRIPTION" => GetMessage("SONET_INVITE_FRIEND_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_INVITE_GROUP",
		"NAME" => GetMessage("SONET_INVITE_GROUP_NAME"),
		"DESCRIPTION" => GetMessage("SONET_INVITE_GROUP_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_AGREE_FRIEND",
		"NAME" => GetMessage("SONET_AGREE_FRIEND_NAME"),
		"DESCRIPTION" => GetMessage("SONET_AGREE_FRIEND_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_BAN_FRIEND",
		"NAME" => GetMessage("SONET_BAN_FRIEND_NAME"),
		"DESCRIPTION" => GetMessage("SONET_BAN_FRIEND_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_NEW_EVENT_GROUP",
		"NAME" => GetMessage("SONET_NEW_EVENT_GROUP_NAME"),
		"DESCRIPTION" => GetMessage("SONET_NEW_EVENT_GROUP_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_NEW_EVENT_USER",
		"NAME" => GetMessage("SONET_NEW_EVENT_USER_NAME"),
		"DESCRIPTION" => GetMessage("SONET_NEW_EVENT_USER_DESC"),
	));
	
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_NEW_EVENT",
		"NAME" => GetMessage("SONET_NEW_EVENT_NAME"),
		"DESCRIPTION" => GetMessage("SONET_NEW_EVENT_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_REQUEST_GROUP",
		"NAME" => GetMessage("SONET_REQUEST_GROUP_NAME"),
		"DESCRIPTION" => GetMessage("SONET_REQUEST_GROUP_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_LOG_NEW_ENTRY",
		"NAME" => GetMessage("SONET_LOG_NEW_ENTRY_NAME"),
		"DESCRIPTION" => GetMessage("SONET_LOG_NEW_ENTRY_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SONET_LOG_NEW_COMMENT",
		"NAME" => GetMessage("SONET_LOG_NEW_COMMENT_NAME"),
		"DESCRIPTION" => GetMessage("SONET_LOG_NEW_COMMENT_DESC"),
	));

	$arSites = array();
	$sites = CSite::GetList('', '', Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_NEW_MESSAGE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("SONET_NEW_MESSAGE_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_NEW_MESSAGE_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_INVITE_FRIEND",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#RECIPIENT_USER_EMAIL_TO#",
			"SUBJECT" => GetMessage("SONET_INVITE_FRIEND_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_INVITE_FRIEND_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_INVITE_GROUP",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#USER_EMAIL#",
			"SUBJECT" => GetMessage("SONET_INVITE_GROUP_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_INVITE_GROUP_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_AGREE_FRIEND",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#RECIPIENT_USER_EMAIL_TO#",
			"SUBJECT" => GetMessage("SONET_AGREE_FRIEND_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_AGREE_FRIEND_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_BAN_FRIEND",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#RECIPIENT_USER_EMAIL_TO#",
			"SUBJECT" => GetMessage("SONET_BAN_FRIEND_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_BAN_FRIEND_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_NEW_EVENT_GROUP",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#SUBSCRIBER_EMAIL#",
			"SUBJECT" => GetMessage("SONET_NEW_EVENT_GROUP_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_NEW_EVENT_GROUP_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_NEW_EVENT_USER",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#SUBSCRIBER_EMAIL#",
			"SUBJECT" => GetMessage("SONET_NEW_EVENT_USER_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_NEW_EVENT_USER_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_NEW_EVENT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("SONET_NEW_EVENT_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_NEW_EVENT_MESSAGE")),
			"BODY_TYPE" => "text",
		));		

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_REQUEST_GROUP",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("SONET_REQUEST_GROUP_SUBJECT"),
			"MESSAGE" => str_replace("/company/personal/", $strCorectPath, GetMessage("SONET_REQUEST_GROUP_MESSAGE")),
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_LOG_NEW_ENTRY",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => "#LOG_ENTRY_TITLE#",
			"MESSAGE" => "<?EventMessageThemeCompiler::includeComponent(\"bitrix:socialnetwork.log.entry.mail\",\"\",array(\"EMAIL_TO\" => \"{#EMAIL_TO#}\",\"RECIPIENT_ID\" => \"{#RECIPIENT_ID#}\",\"LOG_ENTRY_ID\" => \"{#LOG_ENTRY_ID#}\",\"URL\" => \"{#URL#}\"));?>",
			"BODY_TYPE" => "html",
			"SITE_TEMPLATE_ID" => "mail_user"
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "SONET_LOG_NEW_COMMENT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => "Re: #LOG_ENTRY_TITLE#",
			"MESSAGE" => "<?EventMessageThemeCompiler::includeComponent(\"bitrix:socialnetwork.log.entry.mail\",\"\",array(\"EMAIL_TO\" => \"{#EMAIL_TO#}\",\"RECIPIENT_ID\" => \"{#RECIPIENT_ID#}\",\"LOG_ENTRY_ID\" => \"{#LOG_ENTRY_ID#}\",\"COMMENT_ID\" => \"{#COMMENT_ID#}\",\"URL\" => \"{#URL#}\"));?>",
			"BODY_TYPE" => "html",
			"SITE_TEMPLATE_ID" => "mail_user"
		));
	}
}
?>