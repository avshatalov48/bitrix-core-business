<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{
	$lid = $lang["LID"];

	IncludeModuleLangFile(__FILE__, $lid);

	$arSites = [];
	$sites = CSite::GetList('sort', 'asc', ["LANGUAGE_ID" => $lid]);
	while ($site = $sites->Fetch())
	{
		$arSites[] = $site["LID"];
	}

	CEventType::Add(array(
		"LID" => $lid,
		"EVENT_NAME" => "CALENDAR_INVITATION",
		"NAME" => GetMessage("CALENDAR_INVITATION_NAME"),
		"DESCRIPTION" => GetMessage("CALENDAR_INVITATION_DESC"),
	));

	CEventType::Add([
		"LID" => $lid,
		"EVENT_NAME" => "SEND_ICAL_INVENT",
		"NAME" => GetMessage("CALENDAR_ICAL_INVENT_NAME"),
		"DESCRIPTION" => GetMessage("CALENDAR_ICAL_INVENT_DESC"),
		"SORT" => 1
	]);

	CEventType::Add([
		'LID' => $lid,
		'EVENT_NAME' => 'CALENDAR_SHARING',
		'NAME' => GetMessage('CALENDAR_SHARING_EVENT_TYPE_NAME'),
		'DESCRIPTION' => GetMessage('CALENDAR_SHARING_EVENT_TYPE_DESC'),
	]);

	if(count($arSites) > 0)
	{
		$emess = new CEventMessage;
		$emess->Add(array(
			"ACTIVE" => "Y",
			"EVENT_NAME" => "CALENDAR_INVITATION",
			"LID" => $arSites,
			"EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
			"EMAIL_TO" => "#EMAIL_TO#",
			"BCC" => "",
			"SUBJECT" => "#TITLE#",
			"MESSAGE" => "#MESSAGE#".GetMessage('CALENDAR_INVITATION_AUTO_GENERATED'),
			"BODY_TYPE" => "text",
		));

		$emess->Add([
			"ACTIVE" => "Y",
			'EVENT_NAME' => 'SEND_ICAL_INVENT',
			'LID' => $arSites,
			'EMAIL_FROM' => '#EMAIL_FROM#',
			'EMAIL_TO' => '#EMAIL_TO#',
			'SUBJECT' => '#MESSAGE_SUBJECT#',
			'MESSAGE' => '<?EventMessageThemeCompiler::includeComponent(
								"bitrix:calendar.ical.mail",
								"",
								Array(
									"PARAMS" => $arParams,
								)
							);?>',
			'BODY_TYPE' => 'html',
		]);

		$emess->Add([
			'ACTIVE' => 'Y',
			'EVENT_NAME' => 'CALENDAR_SHARING',
			'LID' => $arSites,
			'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
			'EMAIL_TO' => '#EMAIL_TO#',
			'SUBJECT' => '#SUBJECT#',
			'MESSAGE' => '<?php EventMessageThemeCompiler::includeComponent(
				"bitrix:calendar.sharing.mail",
				"",
				$arParams,
			);?>',
			'BODY_TYPE' => 'html',
		]);
	}
}
?>