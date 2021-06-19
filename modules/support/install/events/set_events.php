<?
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/install/events/del_events.php");

$arTemplates = array();

function UET($EVENT_NAME, $NAME, $LID, $DESCRIPTION)
{
	global $DB;
	$et = new CEventType;
	return $et->Add(
			Array(
			"LID"			=> $LID,
			"EVENT_NAME"	=> $EVENT_NAME,
			"NAME"			=> $NAME,
			"DESCRIPTION"	=> $DESCRIPTION
			)
		);
}

$langs = CLanguage::GetList();
while($lang = $langs->Fetch())
{

	$arSites = array();
	$sites = CSite::GetList('', '', Array("LANGUAGE_ID"=>$lang["LID"]));
	while ($site = $sites->Fetch())
		$arSites[] = $site["LID"];

	$lid = $lang["LID"];
	
	IncludeModuleLangFile(__FILE__, $lid);	
	
	$e = UET(
		'TICKET_NEW_FOR_AUTHOR',
		GetMessage('SUP_SE_TICKET_NEW_FOR_AUTHOR_TITLE'),
		$lid,
		GetMessage('SUP_SE_TICKET_NEW_FOR_AUTHOR_TEXT')
	);
	if (!$e) {$SE_ERROR = true; return ;}
	
	$e = UET(
		'TICKET_NEW_FOR_TECHSUPPORT',
		GetMessage('SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_TITLE'),
		$lid,
		GetMessage('SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_TEXT')
	);
	if (!$e) {$SE_ERROR = true; return ;}
	
	$e = UET(
		'TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR',
		GetMessage('SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_TITLE'),
		$lid,
		GetMessage('SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_TEXT')
	);
	if (!$e) {$SE_ERROR = true; return ;}
	
	$e = UET(
		'TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR',
		GetMessage('SUP_SE_TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR_TITLE'),
		$lid,
		GetMessage('SUP_SE_TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR_TEXT')
	);
	if (!$e) {$SE_ERROR = true; return ;}
	
	$e = UET(
		'TICKET_CHANGE_FOR_TECHSUPPORT',
		GetMessage('SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_TITLE'),
		$lid,
		GetMessage('SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_TEXT')
	);
	if (!$e) {$SE_ERROR = true; return ;}
	
	$e = UET(
		'TICKET_OVERDUE_REMINDER',
		GetMessage('SUP_SE_TICKET_OVERDUE_REMINDER_TITLE'),
		$lid,
		GetMessage('SUP_SE_TICKET_OVERDUE_REMINDER_TEXT')
	);
	if (!$e) {$SE_ERROR = true; return ;}
	
	$e = UET(
		'TICKET_GENERATE_SUPERCOUPON',
		GetMessage('SUP_SE_TICKET_GENERATE_SUPERCOUPON_TITLE'),
		$lid,
		GetMessage('SUP_SE_TICKET_GENERATE_SUPERCOUPON_TEXT')
	);
	if (!$e) {$SE_ERROR = true; return ;}
	
	if(is_array($arSites) && count($arSites)>0)
	{

		/************************************************************************************************
										"Новое обращение (для автора)"
		************************************************************************************************/

		$arr["EVENT_NAME"] = "TICKET_NEW_FOR_AUTHOR";
		$arr["SITE_ID"] = $arSites;
		$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
		$arr["EMAIL_TO"] = "#OWNER_EMAIL#";
		$arr["BCC"] = "";
		$arr["BODY_TYPE"] = "text";
		$arr["SUBJECT"] = GetMessage('SUP_SE_TICKET_NEW_FOR_AUTHOR_SUBJECT');
		$arr["MESSAGE"] = GetMessage('SUP_SE_TICKET_NEW_FOR_AUTHOR_MESSAGE');
		$arTemplates[] = $arr;
				
		/************************************************************************************************
									"Новое обращение (для техподдержки)"
		************************************************************************************************/
		
		$arr["EVENT_NAME"] = "TICKET_NEW_FOR_TECHSUPPORT";
		$arr["SITE_ID"] = $arSites;
		$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
		$arr["EMAIL_TO"] = "#SUPPORT_EMAIL#";
		$arr["BCC"] = "#SUPPORT_ADMIN_EMAIL#";
		$arr["BODY_TYPE"] = "text";
		$arr["SUBJECT"] = GetMessage('SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_SUBJECT');
		$arr["MESSAGE"] = GetMessage('SUP_SE_TICKET_NEW_FOR_TECHSUPPORT_MESSAGE');
		$arTemplates[] = $arr;
		
		/************************************************************************************************
								"Изменения в обращении (для автора)"
		************************************************************************************************/
		
		$arr["EVENT_NAME"] = "TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR";
		$arr["SITE_ID"] = $arSites;
		$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
		$arr["EMAIL_TO"] = "#OWNER_EMAIL#";
		$arr["BCC"] = "";
		$arr["SUBJECT"] = GetMessage('SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_SUBJECT');
		$arr["BODY_TYPE"] = "text";
		$arr["MESSAGE"] = GetMessage('SUP_SE_TICKET_CHANGE_BY_SUPPORT_FOR_AUTHOR_MESSAGE');
		$arTemplates[] = $arr;
		
		$arr["EVENT_NAME"] = "TICKET_CHANGE_BY_AUTHOR_FOR_AUTHOR";
		$arTemplates[] = $arr;
		
		/************************************************************************************************
									"Изменения в обращении (для техподдержки)"
		************************************************************************************************/
		
		$arr["EVENT_NAME"] = "TICKET_CHANGE_FOR_TECHSUPPORT";
		$arr["SITE_ID"] = $arSites;
		$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
		$arr["EMAIL_TO"] = "#SUPPORT_EMAIL#";
		$arr["BCC"] = "#SUPPORT_ADMIN_EMAIL#";
		$arr["SUBJECT"] = GetMessage('SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_SUBJECT');
		$arr["BODY_TYPE"] = "text";
		$arr["MESSAGE"] = GetMessage('SUP_SE_TICKET_CHANGE_FOR_TECHSUPPORT_MESSAGE');
		$arTemplates[] = $arr;
		
		
		/************************************************************************************************
							"Напоминание о необходимости ответа (для техподдержки)"
		************************************************************************************************/
		
		$arr["EVENT_NAME"] = "TICKET_OVERDUE_REMINDER";
		$arr["SITE_ID"] = $arSites;
		$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
		$arr["EMAIL_TO"] = "#SUPPORT_EMAIL#";
		$arr["BCC"] = "#SUPPORT_ADMIN_EMAIL#";
		$arr["SUBJECT"] = GetMessage('SUP_SE_TICKET_OVERDUE_REMINDER_SUBJECT');
		$arr["BODY_TYPE"] = "text";
		$arr["MESSAGE"] = GetMessage('SUP_SE_TICKET_OVERDUE_REMINDER_MESSAGE');
		$arTemplates[] = $arr;
	}
}

$emess = new CEventMessage;
foreach ($arTemplates as $Template)
{
	$arFields = Array(
		"ACTIVE"		=> "Y",
		"EVENT_NAME"	=> $Template["EVENT_NAME"],
		"LID"			=> $Template["SITE_ID"],
		"EMAIL_FROM"	=> $Template["EMAIL_FROM"],
		"EMAIL_TO"		=> $Template["EMAIL_TO"],
		"BCC"			=> $Template["BCC"],
		"SUBJECT"		=> $Template["SUBJECT"],
		"MESSAGE"		=> $Template["MESSAGE"],
		"BODY_TYPE"		=> $Template["BODY_TYPE"]
		);
	$e = $emess->Add($arFields);
	if (!$e) {$SE_ERROR = true; return ;}
}
?>