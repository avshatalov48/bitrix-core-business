<?
$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "VIRUS_DETECTED",
		"NAME" => GetMessage("VIRUS_DETECTED_NAME"),
		"DESCRIPTION" => GetMessage("VIRUS_DETECTED_DESC"),
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
			"EVENT_NAME" => "VIRUS_DETECTED",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"BCC" => "",
			"SUBJECT" => GetMessage("VIRUS_DETECTED_SUBJECT"),
			"MESSAGE" => GetMessage("VIRUS_DETECTED_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	}
}
?>