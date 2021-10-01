<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile(__FILE__, $lid);

	$et = new CEventType;
	if($senderSubscribeEvent)
	{
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SENDER_SUBSCRIBE_CONFIRM",
			"NAME" => GetMessage("SUBSCRIBE_CONFIRM_NAME"),
			"DESCRIPTION" => GetMessage("SUBSCRIBE_CONFIRM_DESC"),
		));
	}
	$arSites = array();
	$sites = CSite::GetList('', '', Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{

		$emess = new CEventMessage;
		if($senderSubscribeEvent)
		{
			$emess->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "SENDER_SUBSCRIBE_CONFIRM",
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
}
?>