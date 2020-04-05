<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();



			$templateGeneral = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=#SITE_CHARSET#"/>
	<style>
		body
		{
			font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
			font-size: 14px;
			color: #000;
		}
	</style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="850" style="background-color: #d1d1d1; border-radius: 2px; border:1px solid #d1d1d1; margin: 0 auto;" border="1" bordercolor="#d1d1d1">
	<tr>
		<td height="83" width="850" bgcolor="#eaf3f5" style="border: none; padding-top: 23px; padding-right: 17px; padding-bottom: 24px; padding-left: 17px;">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td bgcolor="#ffffff" height="75" style="font-weight: bold; text-align: center; font-size: 26px; color: #0b3961;">#TITLE#</td>
				</tr>
				<tr>
					<td bgcolor="#bad3df" height="11"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="850" bgcolor="#f7f7f7" valign="top" style="border: none; padding-top: 0; padding-right: 44px; padding-bottom: 16px; padding-left: 44px;">
			<p style="margin-top:30px; margin-bottom: 28px; font-weight: bold; font-size: 19px;">#SUB_TITLE#</p>
			<p style="margin-top: 0; margin-bottom: 20px; line-height: 20px;">#TEXT#</p>
		</td>
	</tr>
	<tr>
		<td height="40px" width="850" bgcolor="#f7f7f7" valign="top" style="border: none; padding-top: 0; padding-right: 44px; padding-bottom: 30px; padding-left: 44px;">
			<p style="border-top: 1px solid #d1d1d1; margin-bottom: 5px; margin-top: 0; padding-top: 20px; line-height:21px;">#FOOTER_BR# <a href="http://#SERVER_NAME#" style="color:#2e6eb6;">#FOOTER_SHOP#</a><br />
				E-mail: <a href="mailto:#SALE_EMAIL#" style="color:#2e6eb6;">#SALE_EMAIL#</a>
			</p>
		</td>
	</tr>
</table>
</body>
</html>';

$dbEvent = CEventMessage::GetList($b="ID", $order="ASC", Array("EVENT_NAME" => SALE_NEW_ORDER));
if(!($dbEvent->Fetch()))
{
	$langs = CLanguage::GetList(($b=""), ($o=""));
	while($lang = $langs->Fetch())
	{
		$lid = $lang["LID"];
		IncludeModuleLangFile(__FILE__, $lid);

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

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_ORDER_TRACKING_NUMBER",
			"NAME" => GetMessage("SALE_ORDER_TRACKING_NUMBER_TYPE_NAME"),
			"DESCRIPTION" => GetMessage("SALE_ORDER_TRACKING_NUMBER_TYPE_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_CHECK_PRINT",
			"NAME" => GetMessage("SALE_CHECK_PRINT_TYPE_NAME"),
			"DESCRIPTION" => GetMessage("SALE_CHECK_PRINT_TYPE_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID" => $lid,
			"EVENT_NAME" => "SALE_CHECK_PRINT_ERROR",
			"NAME" => GetMessage("SALE_CHECK_PRINT_ERROR_TYPE_NAME"),
			"DESCRIPTION" => GetMessage("SALE_CHECK_PRINT_ERROR_TYPE_DESC"),
		));

		$et = new CEventType;
		$et->Add(array(
			"LID"       => $lid,
			"EVENT_NAME"    => "SALE_ORDER_SHIPMENT_STATUS_CHANGED",
			"NAME"          => GetMessage("SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_NAME"),
			"DESCRIPTION"   => GetMessage("SALE_ORDER_SHIPMENT_STATUS_CHANGED_TYPE_DESC")
		));

		$arSites = array();
		$sites = CSite::GetList(($b=""), ($o=""), Array("LANGUAGE_ID"=>$lid));
		while ($site = $sites->Fetch())
			$arSites[] = $site["LID"];

		if(count($arSites) > 0)
		{
			$template = str_replace("#SITE_CHARSET#", $lang["CHARSET"], $templateGeneral);

			$arHTMLEvents = array(
				"SALE_NEW_ORDER", "SALE_ORDER_CANCEL", "SALE_ORDER_DELIVERY", "SALE_ORDER_PAID",
				"SALE_ORDER_REMIND_PAYMENT", "SALE_SUBSCRIBE_PRODUCT", "SALE_ORDER_TRACKING_NUMBER", "SALE_CHECK_PRINT",
				"SALE_CHECK_PRINT_ERROR", "SALE_ORDER_SHIPMENT_STATUS_CHANGED",
			);

			foreach($arHTMLEvents as $eventName)
			{
				$emess = new CEventMessage;

				$message = str_replace(
						array(
								"#TITLE#",
								"#SUB_TITLE#",
								"#TEXT#",
								"#FOOTER_BR#",
								"#FOOTER_SHOP#",
							),
						array(
								GetMessage($eventName."_HTML_TITLE"),
								GetMessage($eventName."_HTML_SUB_TITLE"),
								str_replace("\n", "<br />\n", GetMessage($eventName."_HTML_TEXT")),
								GetMessage("SMAIL_FOOTER_BR"),
								GetMessage("SMAIL_FOOTER_SHOP"),
							),
						$template);

				$emess->Add(array(
					"ACTIVE" => "Y",
					"EVENT_NAME" => $eventName,
					"LID" => $arSites,
					"EMAIL_FROM" => "#SALE_EMAIL#",
					"EMAIL_TO" => "#EMAIL#",
					"BCC" => "#BCC#",
					"SUBJECT" => GetMessage($eventName."_SUBJECT"),
					"MESSAGE" => $message,
					"BODY_TYPE" => "html",
				));
			}

			$emess = new CEventMessage;
			$emess->Add(array(
				"ACTIVE" => "Y",
				"EVENT_NAME" => "SALE_NEW_ORDER_RECURRING",
				"LID" => $arSites,
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
				"EVENT_NAME" => "SALE_RECURRING_CANCEL",
				"LID" => $arSites,
				"EMAIL_FROM" => "#SALE_EMAIL#",
				"EMAIL_TO" => "#EMAIL#",
				"BCC" => "#BCC#",
				"SUBJECT" => GetMessage("SALE_RECURRING_CANCEL_SUBJECT"),
				"MESSAGE" => GetMessage("SALE_RECURRING_CANCEL_MESSAGE"),
				"BODY_TYPE" => "text",
			));
		}

		$dbStatus = CSaleStatus::GetList(
				array($by => $order),
				array(),
				false,
				false,
				array("ID")
			);
		while($arStatus = $dbStatus->Fetch())
		{
			$ID = $arStatus["ID"];
			$eventType = new CEventType;
			$eventMessage = new CEventMessage;

			IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/status.php", $lid);
			$arStatusLang = CSaleStatus::GetLangByID($ID, $lid);

			$template = str_replace("#SITE_CHARSET#", $lang["CHARSET"], $templateGeneral);

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
				$str .= "#ORDER_PUBLIC_URL# - ".GetMessage("SKGS_ORDER_PUBLIC_LINK")."\n";

				$eventTypeID = $eventType->Add(
						array(
								"LID" => $lid,
								"EVENT_NAME" => "SALE_STATUS_CHANGED_".$ID,
								"NAME" => GetMessage("SKGS_CHANGING_STATUS_TO")." \"".$arStatusLang["NAME"]."\"",
								"DESCRIPTION" => $str
							)
					);
			}

			if(count($arSites) > 0)
			{
				$dbEventMessage = $eventMessage->GetList(
						($b = ""),
						($o = ""),
						array(
								"EVENT_NAME" => "SALE_STATUS_CHANGED_".$ID,
								"SITE_ID" => $arSites
							)
					);
				if (!($arEventMessage = $dbEventMessage->Fetch()))
				{
					$message  = GetMessage("SKGS_STATUS_MAIL_BODY1");
					$message .= "------------------------------------------\n\n";
					$message .= GetMessage("SKGS_STATUS_MAIL_BODY2");
					$message .= GetMessage("SKGS_STATUS_MAIL_BODY3");
					$message .= "#ORDER_STATUS#\n";
					$message .= "#ORDER_DESCRIPTION#\n";
					$message .= "#TEXT#\n\n";
					$message .= GetMessage("SKGS_STATUS_MAIL_BODY4");
					$message .= "#SITE_NAME#\n";

					$message = str_replace(
								array(
										"#TITLE#",
										"#SUB_TITLE#",
										"#TEXT#",
										"#FOOTER_BR#",
										"#FOOTER_SHOP#",
									),
								array(
										GetMessage("SKGS_STATUS_MAIL_HTML_TITLE"),
										GetMessage("SKGS_STATUS_MAIL_HTML_SUB_TITLE"),
										str_replace("\n", "<br />\n", $message),
										GetMessage("SMAIL_FOOTER_BR"),
										GetMessage("SMAIL_FOOTER_SHOP"),
									),
								$template);

					$arFields = Array(
							"ACTIVE" => "Y",
							"EVENT_NAME" => "SALE_STATUS_CHANGED_".$ID,
							"LID" => $arSites,
							"EMAIL_FROM" => "#SALE_EMAIL#",
							"EMAIL_TO" => "#EMAIL#",
							"SUBJECT" => GetMessage("SKGS_STATUS_MAIL_SUBJ"),
							"MESSAGE" => $message,
							"BODY_TYPE" => "html"
						);
					$eventMessageID = $eventMessage->Add($arFields);
				}
			}
		}
	}
}
?>