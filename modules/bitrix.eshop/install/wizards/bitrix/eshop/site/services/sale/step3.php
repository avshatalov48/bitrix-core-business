<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$dbSite = CSite::GetByID(WIZARD_SITE_ID);
if($arSite = $dbSite -> Fetch())
	$lid = $arSite["LANGUAGE_ID"];
if($lid == '')
	$lid = "ru";

$dbEvent = CEventMessage::GetList('id', 'asc', Array("EVENT_NAME" => "SALE_NEW_ORDER", "SITE_ID" => WIZARD_SITE_ID));
if(!($dbEvent->Fetch()))
{
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/install/events.php", $lid);

	$dbEvent = CEventType::GetList(Array("TYPE_ID" => "SALE_NEW_ORDER"));
	if(!($dbEvent->Fetch()))
	{
		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_NEW_ORDER",
			"NAME" => GetMessage("SALE_NEW_ORDER_NAME"),
			"DESCRIPTION" => GetMessage("SALE_NEW_ORDER_DESC"),
		));
		
		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_NEW_ORDER_RECURRING",
			"NAME" => GetMessage("SALE_NEW_ORDER_RECURRING_NAME"),
			"DESCRIPTION" => GetMessage("SALE_NEW_ORDER_RECURRING_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_ORDER_REMIND_PAYMENT",
			"NAME" => GetMessage("SALE_ORDER_REMIND_PAYMENT_NAME"),
			"DESCRIPTION" => GetMessage("SALE_ORDER_REMIND_PAYMENT_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_ORDER_CANCEL",
			"NAME" => GetMessage("SALE_ORDER_CANCEL_NAME"),
			"DESCRIPTION" => GetMessage("SALE_ORDER_CANCEL_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_ORDER_PAID",
			"NAME" => GetMessage("SALE_ORDER_PAID_NAME"),
			"DESCRIPTION" => GetMessage("SALE_ORDER_PAID_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_ORDER_DELIVERY",
			"NAME" => GetMessage("SALE_ORDER_DELIVERY_NAME"),
			"DESCRIPTION" => GetMessage("SALE_ORDER_DELIVERY_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_RECURRING_CANCEL",
			"NAME" => GetMessage("SALE_RECURRING_CANCEL_NAME"),
			"DESCRIPTION" => GetMessage("SALE_RECURRING_CANCEL_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_SUBSCRIBE_PRODUCT",
			"NAME" => GetMessage("UP_TYPE_SUBJECT"),
			"DESCRIPTION" => GetMessage("UP_TYPE_SUBJECT_DESC"),
		));
	}

	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_NEW_ORDER",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("SALE_NEW_ORDER_SUBJECT"),
		"MESSAGE" => GetMessage("SALE_NEW_ORDER_MESSAGE"),
		"BODY_TYPE" => "text",
	));
	
	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_NEW_ORDER_RECURRING",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("SALE_NEW_ORDER_RECURRING_SUBJECT"),
		"MESSAGE" => GetMessage("SALE_NEW_ORDER_RECURRING_MESSAGE"),
		"BODY_TYPE" => "text",
	));

	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_ORDER_CANCEL",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("SALE_ORDER_CANCEL_SUBJECT"),
		"MESSAGE" => GetMessage("SALE_ORDER_CANCEL_MESSAGE"),
		"BODY_TYPE" => "text",
	));

	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_ORDER_DELIVERY",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("SALE_ORDER_DELIVERY_SUBJECT"),
		"MESSAGE" => GetMessage("SALE_ORDER_DELIVERY_MESSAGE"),
		"BODY_TYPE" => "text",
	));

	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_ORDER_PAID",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("SALE_ORDER_PAID_SUBJECT"),
		"MESSAGE" => GetMessage("SALE_ORDER_PAID_MESSAGE"),
		"BODY_TYPE" => "text",
	));

	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_RECURRING_CANCEL",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("SALE_RECURRING_CANCEL_SUBJECT"),
		"MESSAGE" => GetMessage("SALE_RECURRING_CANCEL_MESSAGE"),
		"BODY_TYPE" => "text",
	));
	
	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_ORDER_REMIND_PAYMENT",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("SALE_ORDER_REMIND_PAYMENT_SUBJECT"),
		"MESSAGE" => GetMessage("SALE_ORDER_REMIND_PAYMENT_MESSAGE"),
		"BODY_TYPE" => "text",
	));
	$emess = new CEventMessage;
	$emess->Add(array(
		"ACTIVE" => "Y",
		"EVENT_NAME" => "SALE_SUBSCRIBE_PRODUCT",
		"LID" => WIZARD_SITE_ID,
		"EMAIL_FROM" => "#SALE_EMAIL#",
		"EMAIL_TO" => "#EMAIL#",
		"BCC" => "#BCC#",
		"SUBJECT" => GetMessage("UP_SUBJECT"),
		"MESSAGE" => GetMessage("UP_MESSAGE"),
		"BODY_TYPE" => "text",
	));

	if (CModule::IncludeModule("sale"))
	{
		$dbStatus = CSaleStatus::GetList(
				array($by => $order),
				array(),
				false,
				false,
				array("ID", "SORT", "LID", "NAME", "DESCRIPTION")
			);
		while($arStatus = $dbStatus->Fetch())
		{

			$ID = $arStatus["ID"];
			$eventType = new CEventType;
			$eventMessage = new CEventMessage;

		
			IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/status.php", $lid);
			$arStatusLang = CSaleStatus::GetLangByID($ID, $lid);

			$dbEventType = $eventType->GetList(
					array(
							"EVENT_NAME" => "SALE_STATUS_CHANGED_".$ID,
							"LID" => $lid
						)
				);
			if (!($arEventType = $dbEventType->Fetch()))
			{
				$str  = "";
				$str .= "#ORDER_ID# - ".GetMessage("SKGS_ORDER_ID")."\n";
				$str .= "#ORDER_DATE# - ".GetMessage("SKGS_ORDER_DATE")."\n";
				$str .= "#ORDER_STATUS# - ".GetMessage("SKGS_ORDER_STATUS")."\n";
				$str .= "#EMAIL# - ".GetMessage("SKGS_ORDER_EMAIL")."\n";
				$str .= "#ORDER_DESCRIPTION# - ".GetMessage("SKGS_STATUS_DESCR")."\n";
				$str .= "#TEXT# - ".GetMessage("SKGS_STATUS_TEXT")."\n";
				$str .= "#SALE_EMAIL# - ".GetMessage("SKGS_SALE_EMAIL")."\n";

				$eventTypeID = $eventType->Add(
						array(
								"LID" => $lid,
								"EVENT_NAME" => "SALE_STATUS_CHANGED_".$ID,
								"NAME" => GetMessage("SKGS_CHANGING_STATUS_TO")." \"".$arStatusLang["NAME"]."\"",
								"DESCRIPTION" => $str
							)
					);
			}

			$dbEventMessage = $eventMessage->GetList(
				'',
				'',
				array(
					"EVENT_NAME" => "SALE_STATUS_CHANGED_".$ID,
					"SITE_ID" => WIZARD_SITE_ID
				)
			);
			if (!($arEventMessage = $dbEventMessage->Fetch()))
			{
				$subject = GetMessage("SKGS_STATUS_MAIL_SUBJ");

				$message  = GetMessage("SKGS_STATUS_MAIL_BODY1");
				$message .= "------------------------------------------\n\n";
				$message .= GetMessage("SKGS_STATUS_MAIL_BODY2");
				$message .= GetMessage("SKGS_STATUS_MAIL_BODY3");
				$message .= "#ORDER_STATUS#\n";
				$message .= "#ORDER_DESCRIPTION#\n";
				$message .= "#TEXT#\n\n";
				$message .= GetMessage("SKGS_STATUS_MAIL_BODY4");
				$message .= "#SITE_NAME#\n";

				$arFields = Array(
						"ACTIVE" => "Y",
						"EVENT_NAME" => "SALE_STATUS_CHANGED_".$ID,
						"LID" => WIZARD_SITE_ID,
						"EMAIL_FROM" => "#SALE_EMAIL#",
						"EMAIL_TO" => "#EMAIL#",
						"SUBJECT" => $subject,
						"MESSAGE" => $message,
						"BODY_TYPE" => "text"
					);
				$eventMessageID = $eventMessage->Add($arFields);
			}
		}
	}
}
?>