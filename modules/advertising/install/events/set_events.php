<?
$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "ADV_BANNER_STATUS_CHANGE",
		"NAME" => GetMessage("ADV_BANNER_STATUS_CHANGE_NAME"),
		"DESCRIPTION" => GetMessage("ADV_BANNER_STATUS_CHANGE_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "ADV_CONTRACT_INFO",
		"NAME" => GetMessage("ADV_CONTRACT_INFO_NAME"),
		"DESCRIPTION" => GetMessage("ADV_CONTRACT_INFO_DESC"),
	));


	$arSites = array();
	$sites = CSite::GetList("", "", Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "ADV_BANNER_STATUS_CHANGE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"BCC" => "#BCC#",
			"SUBJECT" => GetMessage("ADV_BANNER_STATUS_CHANGE_SUBJECT"),
			"MESSAGE" => GetMessage("ADV_BANNER_STATUS_CHANGE_MESSAGE", array("#LANGUAGE_ID#" => $lid)),
			"BODY_TYPE" => "text",
		));

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "ADV_CONTRACT_INFO",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"BCC" => "#BCC#",
			"SUBJECT" => GetMessage("ADV_CONTRACT_INFO_SUBJECT"),
			"MESSAGE" => GetMessage("ADV_CONTRACT_INFO_MESSAGE", array("#LANGUAGE_ID#" => $lid)),
			"BODY_TYPE" => "text",
		));
	}
}
?>