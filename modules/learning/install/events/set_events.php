<?
$langs = CLanguage::GetList(($b=""), ($o=""));
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/install/events.php", $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "NEW_LEARNING_TEXT_ANSWER",
		"NAME" => GetMessage("NEW_LEARNING_TEXT_ANSWER_NAME"),
		"DESCRIPTION" => GetMessage("NEW_LEARNING_TEXT_ANSWER_DESC"),
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
			"EVENT_NAME" => "NEW_LEARNING_TEXT_ANSWER",
			"LID" => $arSites,
			"EMAIL_FROM" => "#EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("NEW_LEARNING_TEXT_ANSWER_SUBJECT"),
			"MESSAGE" => GetMessage("NEW_LEARNING_TEXT_ANSWER_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	}
}
?>
