<?
$langs = CLanguage::GetList(($b=""), ($o=""));
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "SUBSCRIBE_CONFIRM",
		"NAME" => GetMessage("SUBSCRIBE_CONFIRM_NAME"),
		"DESCRIPTION" => GetMessage("SUBSCRIBE_CONFIRM_DESC"),
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
			"EVENT_NAME" => "SUBSCRIBE_CONFIRM",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL#",
			"BCC" => "",
			"SUBJECT" => GetMessage("SUBSCRIBE_CONFIRM_SUBJECT"),
			"MESSAGE" => GetMessage("SUBSCRIBE_CONFIRM_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	}
}
?>