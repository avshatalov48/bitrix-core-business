<?
$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{
	$db_events = CEventType::GetList(array("EVENT_NAME" => "VOTE_FOR", "LID" => $lang["LID"]));
	if (!$db_events->Fetch())
	{
		IncludeModuleLangFile(__FILE__, $lang["LID"]);
		$et = new CEventType;
		$et->Add(array(
			"LID" => $lang["LID"],
			"EVENT_NAME" => "VOTE_FOR",
			"NAME" => GetMessage("VOTE_FOR_NAME"),
			"DESCRIPTION" => GetMessage("VOTE_FOR_DESC")));

		$arSites = array();
		$sites = CSite::GetList('', '', Array("LANGUAGE_ID"=>$lang["LID"]));
		while ($site = $sites->Fetch())
			$arSites[] = $site["LID"];

		if(count($arSites) > 0)
		{
			$emess = new CEventMessage;
			$emess->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "VOTE_FOR",
				"LID" => $arSites,
				"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
				"EMAIL_TO" => "#EMAIL_TO#",
				"SUBJECT" => GetMessage("VOTE_FOR_SUBJECT"),
				"MESSAGE" => GetMessage("VOTE_FOR_MESSAGE"),
				"BODY_TYPE" => "text",
			));
		}
	}
}
?>