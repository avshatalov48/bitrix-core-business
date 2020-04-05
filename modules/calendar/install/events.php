<?
$langs = CLanguage::GetList(($b=""), ($o=""));
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "CALENDAR_INVITATION",
		"NAME" => GetMessage("CALENDAR_INVITATION_NAME"),
		"DESCRIPTION" => GetMessage("CALENDAR_INVITATION_DESC"),
	));

	if(count($arSites) > 0)
	{
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "CALENDAR_INVITATION",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"BCC" => "",
			"SUBJECT" => "#TITLE#",
			"MESSAGE" => "#MESSAGE#".GetMessage('CALENDAR_INVITATION_AUTO_GENERATED'),
			"BODY_TYPE" => "text",
		));
	}
}
?>