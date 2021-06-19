<?
$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "WF_STATUS_CHANGE",
		"NAME" => GetMessage("WF_STATUS_CHANGE_NAME"),
		"DESCRIPTION" => GetMessage("WF_STATUS_CHANGE_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "WF_NEW_DOCUMENT",
		"NAME" => GetMessage("WF_NEW_DOCUMENT_NAME"),
		"DESCRIPTION" => GetMessage("WF_NEW_DOCUMENT_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "WF_IBLOCK_STATUS_CHANGE",
		"NAME" => GetMessage("WF_IBLOCK_STATUS_CHANGE_NAME"),
		"DESCRIPTION" => GetMessage("WF_IBLOCK_STATUS_CHANGE_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "WF_NEW_IBLOCK_ELEMENT",
		"NAME" => GetMessage("WF_NEW_IBLOCK_ELEMENT_NAME"),
		"DESCRIPTION" => GetMessage("WF_NEW_IBLOCK_ELEMENT_DESC"),
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
			"EVENT_NAME" => "WF_STATUS_CHANGE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#ENTERED_BY_EMAIL#, #ADMIN_EMAIL#",
			"BCC" => "#BCC#",
			"SUBJECT" => GetMessage("WF_STATUS_CHANGE_SUBJECT"),
			"MESSAGE" => GetMessage("WF_STATUS_CHANGE_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "WF_NEW_DOCUMENT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#ENTERED_BY_EMAIL#, #ADMIN_EMAIL#",
			"BCC" => "#BCC#",
			"SUBJECT" => GetMessage("WF_NEW_DOCUMENT_SUBJECT"),
			"MESSAGE" => GetMessage("WF_NEW_DOCUMENT_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "WF_IBLOCK_STATUS_CHANGE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#CREATED_BY_EMAIL#, #ADMIN_EMAIL#",
			"BCC" => "#BCC#",
			"SUBJECT" => GetMessage("WF_IBLOCK_STATUS_CHANGE_SUBJECT"),
			"MESSAGE" => GetMessage("WF_IBLOCK_STATUS_CHANGE_MESSAGE"),
			"BODY_TYPE" => "text",
		));

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "WF_NEW_IBLOCK_ELEMENT",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#CREATED_BY_EMAIL#, #ADMIN_EMAIL#",
			"BCC" => "#BCC#",
			"SUBJECT" => GetMessage("WF_NEW_IBLOCK_ELEMENT_SUBJECT"),
			"MESSAGE" => GetMessage("WF_NEW_IBLOCK_ELEMENT_MESSAGE"),
			"BODY_TYPE" => "text",
		));
	}
}
?>