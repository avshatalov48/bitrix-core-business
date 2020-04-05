<?
$langs = CLanguage::GetList(($b=""), ($o=""));
while ($lang = $langs->Fetch())
{
	$lid = $lang["LID"];
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/im/install/events/set_events.php", $lid);

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "IM_NEW_NOTIFY",
		"NAME" => GetMessage("IM_NEW_NOTIFY_NAME"),
		"DESCRIPTION" => GetMessage("IM_NEW_NOTIFY_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "IM_NEW_NOTIFY_GROUP",
		"NAME" => GetMessage("IM_NEW_NOTIFY_GROUP_NAME"),
		"DESCRIPTION" => GetMessage("IM_NEW_NOTIFY_GROUP_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "IM_NEW_MESSAGE",
		"NAME" => GetMessage("IM_NEW_MESSAGE_NAME"),
		"DESCRIPTION" => GetMessage("IM_NEW_MESSAGE_DESC"),
	));

	$et = new CEventType;
	$et->Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "IM_NEW_MESSAGE_GROUP",
		"NAME" => GetMessage("IM_NEW_MESSAGE_GROUP_NAME"),
		"DESCRIPTION" => GetMessage("IM_NEW_MESSAGE_GROUP_DESC"),
	));

	
	$arSites = array();
	$sites = CSite::GetList(($b=""), ($o=""), Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{
		$message1 = GetMessage("IM_NEW_NOTIFY_MESSAGE");
		if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
		{
			$message1 = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $message1);
		}
		
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_NOTIFY",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_NOTIFY_SUBJECT"),
			"MESSAGE" => $message1,
			"BODY_TYPE" => "text",
		));

		$message2 = GetMessage("IM_NEW_NOTIFY_GROUP_MESSAGE");
		if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
		{
			$message2 = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $message2);
		}
		
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_NOTIFY_GROUP",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_NOTIFY_GROUP_SUBJECT"),
			"MESSAGE" => $message2,
			"BODY_TYPE" => "text",
		));

		$message3 = GetMessage("IM_NEW_MESSAGE_MESSAGE");
		if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
		{
			$message3 = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $message3);
		}
		
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_MESSAGE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_MESSAGE_SUBJECT"),
			"MESSAGE" => $message3,
			"BODY_TYPE" => "text",
		));

		$message4 = GetMessage("IM_NEW_MESSAGE_GROUP_MESSAGE");
		if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
		{
			$message4 = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $message4);
		}
		
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_MESSAGE_GROUP",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_MESSAGE_GROUP_SUBJECT"),
			"MESSAGE" => $message4,
			"BODY_TYPE" => "text",
		));
	}
}
?>