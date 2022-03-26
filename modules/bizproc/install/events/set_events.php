<?
$dbLangs = CLanguage::GetList();
while ($arLang = $dbLangs->Fetch())
{
	$lid = $arLang["LID"];
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/install/events.php", $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BIZPROC_MAIL_TEMPLATE",
		"NAME" => GetMessage("BIZPROC_MAIL_TEMPLATE_NAME_1"),
		"DESCRIPTION" => GetMessage("BIZPROC_MAIL_TEMPLATE_DESC"),
	));

	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "BIZPROC_HTML_MAIL_TEMPLATE",
		"NAME" => GetMessage("BIZPROC_HTML_MAIL_TEMPLATE_NAME_1"),
		"DESCRIPTION" => GetMessage("BIZPROC_MAIL_TEMPLATE_DESC"),
	));

	$arSites = array();
	$dbSites = CSite::GetList("", "", Array("LANGUAGE_ID" => $lid));
	while ($site = $dbSites->Fetch())
		$arSites[] = $site["LID"];

	if (count($arSites) > 0)
	{
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BIZPROC_MAIL_TEMPLATE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#SENDER#",
			"EMAIL_TO" => "#RECEIVER#",
			"SUBJECT" => "#TITLE#",
			"MESSAGE" => "#MESSAGE#",
			"REPLY_TO" => "#REPLY_TO#",
			"BODY_TYPE" => "text",
		));

		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "BIZPROC_HTML_MAIL_TEMPLATE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#SENDER#",
			"EMAIL_TO" => "#RECEIVER#",
			"SUBJECT" => "#TITLE#",
			"MESSAGE" => "#MESSAGE#",
			"REPLY_TO" => "#REPLY_TO#",
			"BODY_TYPE" => "html",
		));
	}
}