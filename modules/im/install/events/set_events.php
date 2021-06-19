<?
$isIntranet = file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/");
$langs = CLanguage::GetList();
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
	$sites = CSite::GetList('', '', Array("LANGUAGE_ID"=>$lid));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	if(count($arSites) > 0)
	{
		if ($isIntranet)
		{
			$notifyMessage = "<?EventMessageThemeCompiler::includeComponent(\"bitrix:intranet.template.mail\",\"\",array(\"MESSAGE\" => \$arParams['MESSAGE'],\"FROM_USER\" => \"{#FROM_USER#}\",\"USER_NAME\" => \"{#USER_NAME#}\",\"SERVER_NAME\" => \"{#SERVER_NAME#}\",\"DATE_CREATE\" => \"{#DATE_CREATE#}\",\"FROM_USER_ID\" => \"{#FROM_USER_ID# }\",\"TEMPLATE_TYPE\" => \"IM_NEW_NOTIFY\"));?>";
		}
		else
		{
			$notifyMessage = GetMessage("IM_NEW_NOTIFY_MESSAGE");
			if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
			{
				$notifyMessage = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $notifyMessage);
			}
		}

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_NOTIFY",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_NOTIFY_SUBJECT"),
			"MESSAGE" => $notifyMessage,
			"BODY_TYPE" => $isIntranet ? "html" : "text",
		));

		$notifyGroupMessage = GetMessage("IM_NEW_NOTIFY_GROUP_MESSAGE");
		if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
		{
			$notifyGroupMessage = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $notifyGroupMessage);
		}

		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_NOTIFY_GROUP",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_NOTIFY_GROUP_SUBJECT"),
			"MESSAGE" => $notifyGroupMessage,
			"BODY_TYPE" => "text",
		));

		if ($isIntranet)
		{
			$newMessage = "<?EventMessageThemeCompiler::includeComponent(\"bitrix:intranet.template.mail\",\"\",array(\"MESSAGE\" => \"{#MESSAGES#}\",\"FROM_USER\" => \"{#FROM_USER#}\",\"USER_NAME\" => \"{#USER_NAME#}\",\"SERVER_NAME\" => \"{#SERVER_NAME#}\",\"DATE_CREATE\" => \"{#DATE_CREATE#}\",\"FROM_USER_ID\" => \"{#FROM_USER_ID# }\",\"TEMPLATE_TYPE\" => \"IM_NEW_MESSAGE\"));?>";
		}
		else
		{
			$newMessage = GetMessage("IM_NEW_MESSAGE_MESSAGE");
			if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
			{
				$newMessage = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $newMessage);
			}
		}
		
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_MESSAGE",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_MESSAGE_SUBJECT"),
			"MESSAGE" => $newMessage,
			"BODY_TYPE" => $isIntranet ? "html" : "text",
		));

		if ($isIntranet)
		{
			$newGroupMessage = "<?EventMessageThemeCompiler::includeComponent(\"bitrix:intranet.template.mail\",\"\",array(\"MESSAGE\" => \"{#MESSAGES#}\",\"MESSAGES_FROM_USERS\" => \"{#MESSAGES_FROM_USERS#}\",\"FROM_USER\" => \"{#FROM_USERS#}\",\"USER_NAME\" => \"{#USER_NAME#}\",\"SERVER_NAME\" => \"{#SERVER_NAME#}\",\"DATE_CREATE\" => \"{#DATE_CREATE#}\",\"FROM_USER_ID\" => \"{#FROM_USER_ID# }\",\"TEMPLATE_TYPE\" => \"IM_NEW_MESSAGE_GROUP\"));?>";
		}
		else
		{
			$newGroupMessage = GetMessage("IM_NEW_MESSAGE_GROUP_MESSAGE");
			if (defined('BX24_HOST_NAME') || \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps())
			{
				$newGroupMessage = str_replace('http://#SERVER_NAME#/', 'https://#SERVER_NAME#/', $newGroupMessage);
			}
		}
		
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "IM_NEW_MESSAGE_GROUP",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"SUBJECT" => GetMessage("IM_NEW_MESSAGE_GROUP_SUBJECT"),
			"MESSAGE" => $newGroupMessage,
			"BODY_TYPE" => $isIntranet ? "html" : "text",
		));
	}
}
?>