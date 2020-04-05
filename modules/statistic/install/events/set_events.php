<?
$langs = CLanguage::GetList(($b=""), ($o=""));
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "STATISTIC_ACTIVITY_EXCEEDING",
		"NAME" => GetMessage("STATISTIC_ACTIVITY_EXCEEDING_NAME"),
		"DESCRIPTION" => GetMessage("STATISTIC_ACTIVITY_EXCEEDING_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "STATISTIC_DAILY_REPORT",
		"NAME" => GetMessage("STATISTIC_DAILY_REPORT_NAME"),
		"DESCRIPTION" => GetMessage("STATISTIC_DAILY_REPORT_DESC"),
	));


	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "STATISTIC_DAILY_REPORT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"BCC" => "",
			"SUBJECT" => GetMessage("STATISTIC_DAILY_REPORT_SUBJECT"),
			"MESSAGE" => GetMessage("STATISTIC_DAILY_REPORT_MESSAGE", array("#LANGUAGE_ID#" => $lid)),
			"BODY_TYPE" => "html",
		));

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "STATISTIC_ACTIVITY_EXCEEDING",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"BCC" => "",
			"SUBJECT" => GetMessage("STATISTIC_ACTIVITY_EXCEEDING_SUBJECT"),
			"MESSAGE" => GetMessage("STATISTIC_ACTIVITY_EXCEEDING_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	}
}
?>