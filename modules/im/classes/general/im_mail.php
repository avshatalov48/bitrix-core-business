<?
IncludeModuleLangFile(__FILE__);

class CIMMail
{
	public static function MailNotifyAgent()
	{
		global $DB;

		$defSiteID = CSite::GetDefSite();

		$arMark = array();
		$arGroupNotify = array();
		$arGroupNotifyUser = array();
		$arUnsendNotify = CIMNotify::GetUnsendNotify();
		$maxId = 0;

		foreach($arUnsendNotify as $id => $arNotify)
		{
			$maxId = ((int)$id > $maxId) ? (int)$id : $maxId;
			if (!isset($arMark[$arNotify["CHAT_ID"]]) || $arMark[$arNotify["CHAT_ID"]] < $arNotify["ID"])
				$arMark[$arNotify["CHAT_ID"]] = $arNotify["ID"];

			if ($arNotify['TO_EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID || $arNotify['TO_EXTERNAL_AUTH_ID'] == "network")
			{
				unset($arUnsendNotify[$id]);
				continue;
			}

			if ($arNotify['TO_USER_ACTIVE'] != 'Y')
			{
				unset($arUnsendNotify[$id]);
				continue;
			}

			if (isset($arNotify["NOTIFY_MODULE"]) && isset($arNotify["NOTIFY_EVENT"])
			&& !CIMSettings::GetNotifyAccess($arNotify["TO_USER_ID"], $arNotify["NOTIFY_MODULE"], $arNotify["NOTIFY_EVENT"], CIMSettings::CLIENT_MAIL))
			{
				unset($arUnsendNotify[$id]);
				continue;
			}

			if ($arNotify["MESSAGE_OUT"] == IM_MAIL_SKIP)
			{
				unset($arUnsendNotify[$id]);
				continue;
			}

			if (!$arNotify["TO_USER_LID"] || $arNotify["TO_USER_LID"] == '')
			{
				$arNotify["TO_USER_LID"] = $defSiteID;
				if (!$arNotify["TO_USER_LID"] || $arNotify["TO_USER_LID"] == '')
				{
					unset($arUnsendNotify[$id]);
					continue;
				}
			}
			if ($arNotify["MESSAGE_OUT"] == '')
				$arNotify["MESSAGE_OUT"] = $arNotify["MESSAGE"];

			if (!(isset($arNotify["EMAIL_TEMPLATE"]) && $arNotify["EMAIL_TEMPLATE"] <> ''))
				$arNotify["EMAIL_TEMPLATE"] = "IM_NEW_NOTIFY";

			$arNotify["USER"] = \Bitrix\Im\User::formatFullNameFromDatabase(array(
				"NAME" => $arNotify["TO_USER_NAME"],
				"LAST_NAME" => $arNotify["TO_USER_LAST_NAME"],
				"SECOND_NAME" => $arNotify["TO_USER_SECOND_NAME"],
				"LOGIN"	=> $arNotify["TO_USER_LOGIN"],
				"EXTERNAL_AUTH_ID"	=> $arNotify["TO_EXTERNAL_AUTH_ID"]
			));

			if ($arNotify["FROM_USER_ID"] == 0)
			{
				$arNotify["FROM_USER"] = GetMessage('IM_MAIL_USER_SYSTEM');
			}
			else
			{
				$arNotify["FROM_USER"] = \Bitrix\Im\User::formatFullNameFromDatabase(array(
					"NAME" => $arNotify["FROM_USER_NAME"],
					"LAST_NAME" => $arNotify["FROM_USER_LAST_NAME"],
					"SECOND_NAME" => $arNotify["FROM_USER_SECOND_NAME"],
					"LOGIN" => $arNotify["FROM_USER_LOGIN"],
					"EXTERNAL_AUTH_ID" => $arNotify["FROM_EXTERNAL_AUTH_ID"]
				));
			}

			$arNotify['NOTIFY_TAG_MD5'] = md5($arNotify["TO_USER_ID"].'|'.$arNotify['NOTIFY_TAG']);
			$arUnsendNotify[$id] = $arNotify;
			if ($arNotify["EMAIL_TEMPLATE"] == "IM_NEW_NOTIFY" && $arNotify['NOTIFY_TAG'] != '')
			{
				if (isset($arGroupNotify[$arNotify['NOTIFY_TAG_MD5']]))
				{
					$arGroupNotifyUser[$arNotify['NOTIFY_TAG_MD5']][$arNotify["FROM_USER_ID"]] = $arNotify["FROM_USER"];
					unset($arUnsendNotify[$id]);
				}
				else
				{
					$arGroupNotifyUser[$arNotify['NOTIFY_TAG_MD5']][$arNotify["FROM_USER_ID"]] = $arNotify["FROM_USER"];
					$arGroupNotify[$arNotify['NOTIFY_TAG_MD5']] = true;
				}
			}
		}

		if ($maxId > 0)
		{
			\Bitrix\Main\Config\Option::set('im', 'last_send_mail_notification', $maxId);
		}

		$CTP = new CTextParser;
		foreach($arUnsendNotify as $id => $arNotify)
		{
			$message = $CTP->convert4mail(str_replace("#BR#", "\n", strip_tags($arNotify["MESSAGE_OUT"])));
			$messageShort = str_replace(array("<br>","<br/>","<br />", "#BR#"), Array(" ", " ", " ", " "), nl2br($CTP->convert4mail(strip_tags($arNotify["MESSAGE_OUT"]))));
			$message = \Bitrix\Im\Text::removeBbCodes($message);
			$messageShort = $CTP->html_cut(\Bitrix\Im\Text::removeBbCodes($messageShort), 50);
			$title = trim($arNotify["NOTIFY_TITLE"] ?? '');
			if ($title !== '')
			{
				$title = \Bitrix\Im\Text::removeBbCodes($title);
			}
			$arFields = array(
				"MESSAGE_ID" => $arNotify["ID"],
				"USER" => $arNotify["USER"],
				"USER_ID" => $arNotify["TO_USER_ID"],
				"USER_LOGIN" => $arNotify["TO_USER_LOGIN"],
				"USER_NAME" => $arNotify["TO_USER_NAME"],
				"USER_LAST_NAME" => $arNotify["TO_USER_LAST_NAME"],
				"USER_SECOND_NAME" => $arNotify["TO_USER_SECOND_NAME"],
				"DATE_CREATE" => FormatDate("FULL", $arNotify["DATE_CREATE"]),
				"FROM_USER_ID" => $arNotify["FROM_USER_ID"],
				"FROM_USER_LOGIN" => $arNotify["FROM_USER_LOGIN"],
				"FROM_USER" => $arNotify["FROM_USER"],
				"SENDER_ID" => $arNotify["FROM_USER_ID"], 				// legacy
				"SENDER_LOGIN" => $arNotify["FROM_USER_LOGIN"], 		// legacy
				"SENDER_NAME" => $arNotify["FROM_USER_NAME"], 			// legacy
				"SENDER_LAST_NAME" => $arNotify["FROM_USER_LAST_NAME"], // legacy
				"SENDER_SECOND_NAME" => $arNotify["FROM_USER_SECOND_NAME"], // legacy
				"EMAIL_TO" => $arNotify["TO_USER_EMAIL"],
				"TITLE" => $title,
				"MESSAGE" => $message,
				"MESSAGE_50" => $messageShort,
			);

			if ($arFields['TITLE'] <> '')
				$arFields["MESSAGE_50"] = $arFields['TITLE'];
			else
				$arFields["TITLE"] = $arFields['MESSAGE_50'];

			if (isset($arGroupNotifyUser[$arNotify['NOTIFY_TAG_MD5']]) && count($arGroupNotifyUser[$arNotify['NOTIFY_TAG_MD5']]) > 1)
			{
				$arNotify["EMAIL_TEMPLATE"] = "IM_NEW_NOTIFY_GROUP";
				$arFields['FROM_USERS'] = implode(', ', $arGroupNotifyUser[$arNotify['NOTIFY_TAG_MD5']]);
				unset($arFields['FROM_USER']);
			}

			$event = new CEvent;
			$event->Send($arNotify["EMAIL_TEMPLATE"], $arNotify["TO_USER_LID"], $arFields, "N");
		}

		return "CIMMail::MailNotifyAgent();";
	}

	public static function MailMessageAgent()
	{
		global $DB;

		$defSiteID = CSite::GetDefSite();

		$arMark = array();
		$arUnsendMessage = CIMMessage::GetUnsendMessage();

		$arToUser = Array();
		$arFromUser = Array();
		$arDialog = Array();
		$parser = new CTextParser();
		$maxId = 0;

		foreach($arUnsendMessage as $id => $arMessage)
		{
			$maxId = ((int)$id > $maxId) ? (int)$id : $maxId;
			if (!isset($arMark[$arMessage["TO_USER_ID"]][$arMessage["CHAT_ID"]]) || $arMark[$arMessage["TO_USER_ID"]][$arMessage["CHAT_ID"]] < $arMessage["ID"])
				$arMark[$arMessage["TO_USER_ID"]][$arMessage["CHAT_ID"]] = $arMessage["ID"];

			if ($arMessage['TO_EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID || $arMessage['TO_EXTERNAL_AUTH_ID'] == "network")
			{
				unset($arUnsendMessage[$id]);
				continue;
			}

			if ($arMessage['TO_USER_ACTIVE'] != 'Y')
			{
				unset($arUnsendMessage[$id]);
				continue;
			}

			if (!CIMSettings::GetNotifyAccess($arMessage["TO_USER_ID"], 'im', 'message', CIMSettings::CLIENT_MAIL))
			{
				unset($arUnsendMessage[$id]);
				continue;
			}

			if ($arMessage["MESSAGE_OUT"] == IM_MAIL_SKIP)
			{
				unset($arUnsendMessage[$id]);
				continue;
			}

			if ($arMessage["MESSAGE_OUT"] == '')
				$arMessage["MESSAGE_OUT"] = $arMessage["MESSAGE"];

			if (!isset($arToUser[$arMessage["TO_USER_ID"]]))
			{
				$siteID = $arMessage["TO_USER_LID"];
				if ($siteID == false || $siteID == '')
				{
					$siteID = $defSiteID;
					if ($siteID == false || $siteID == '')
						continue;
				}

				$arNotify["USER"] = \Bitrix\Im\User::formatFullNameFromDatabase(array(
					"NAME" => $arMessage["TO_USER_NAME"],
					"LAST_NAME" => $arMessage["TO_USER_LAST_NAME"],
					"SECOND_NAME" => $arMessage["TO_USER_SECOND_NAME"] ?? null,
					"LOGIN" => $arMessage["TO_USER_LOGIN"],
					"EXTERNAL_AUTH_ID" => $arMessage["TO_EXTERNAL_AUTH_ID"],
				));

				$arToUser[$arMessage["TO_USER_ID"]] = Array(
					"USER" => $arMessage['USER'] ?? null,
					"USER_ID" => $arMessage["TO_USER_ID"],
					"USER_LOGIN" => $arMessage["TO_USER_LOGIN"],
					"USER_NAME" => $arMessage["TO_USER_NAME"],
					"USER_LAST_NAME" => $arMessage["TO_USER_LAST_NAME"],
					"USER_SECOND_NAME" => $arMessage["TO_USER_SECOND_NAME"] ?? null,
					"TO_USER_LID" => $siteID,
					"EMAIL_TO" => $arMessage["TO_USER_EMAIL"],
				);
			}
			if (!isset($arFromUser[$arMessage["FROM_USER_ID"]]))
			{
				if ($arMessage["FROM_USER_ID"] == 0)
				{
					$arMessage["FROM_USER"] = GetMessage('IM_MAIL_USER_SYSTEM');
				}
				else
				{
					$arMessage["FROM_USER"] = \Bitrix\Im\User::formatFullNameFromDatabase(array(
						"NAME" => $arMessage["FROM_USER_NAME"],
						"LAST_NAME" => $arMessage["FROM_USER_LAST_NAME"],
						"SECOND_NAME" => $arMessage["FROM_USER_SECOND_NAME"] ?? null,
						"LOGIN" => $arMessage["FROM_USER_LOGIN"],
						"EXTERNAL_AUTH_ID" => $arMessage["FROM_EXTERNAL_AUTH_ID"],
					));
				}

				$arFromUser[$arMessage["FROM_USER_ID"]] = Array(
					"FROM_USER" => $arMessage["FROM_USER"],
					"FROM_USER_ID" => $arMessage["FROM_USER_ID"],
					"FROM_USER_LOGIN" => $arMessage["FROM_USER_LOGIN"],
					"FROM_USER_NAME" => $arMessage["FROM_USER_NAME"],
					"FROM_USER_LAST_NAME" => $arMessage["FROM_USER_LAST_NAME"],
					"FROM_USER_SECOND_NAME" => $arMessage["FROM_USER_SECOND_NAME"] ?? null,
				);
			}

			$message = $parser->convert4mail(str_replace("#BR#", "\n", strip_tags($arMessage["MESSAGE_OUT"])));
			$message = \Bitrix\Im\Text::removeBbCodes($message);
			$arDialog[$arMessage["TO_USER_ID"]][$arMessage["FROM_USER_ID"]][] = Array(
				'DATE_CREATE' => FormatDate("FULL", $arMessage["DATE_CREATE"]),
				'MESSAGE' => $message
			);
		}

		if ($maxId > 0)
		{
			\Bitrix\Main\Config\Option::set('im', 'last_send_mail_message', $maxId);
		}

		foreach ($arToUser as $toID=> $arToInfo)
		{
			$message = "";
			$messagesFromUsers = array();
			$bHeader = false;
			$arNames = Array();
			$arFromId = Array();
			$bFirstMessage = true;
			foreach ($arDialog[$toID] as $fromID => $arMessages)
			{
				$fromIdUserMessages = "";

				if ($bFirstMessage)
					$bFirstMessage = false;
				else
					$message .= "\n";

				if (count($arDialog[$toID])>1)
				{
					$message .= GetMessage('IM_MAIL_TEMPLATE_NEW_MESSAGE_HEADER', Array('#FROM_USER#' => $arFromUser[$fromID]['FROM_USER']))."\n";
					$bHeader = true;
				}
				$arNames[] = $arFromUser[$fromID]['FROM_USER'];
				$arFromId[] = $arFromUser[$fromID]['FROM_USER_ID'];
				foreach ($arMessages as $arMessage)
				{
					$message .= GetMessage('IM_MAIL_TEMPLATE_NEW_MESSAGE_TEXT', Array('#DATE_CREATE#' => $arMessage['DATE_CREATE'], '#MESSAGE#' => $arMessage['MESSAGE']))."\n";
					$fromIdUserMessages .= nl2br(GetMessage('IM_MAIL_TEMPLATE_NEW_MESSAGE_TEXT', Array('#DATE_CREATE#' => $arMessage['DATE_CREATE'], '#MESSAGE#' => $arMessage['MESSAGE']))."\n");
				}
				$messagesFromUsers[$fromID] = $fromIdUserMessages;
			}
			if ($bHeader)
				$message .= "\n".GetMessage('IM_MAIL_TEMPLATE_NEW_MESSAGE_FOOTER');

			$fromUserId = $arToInfo["FROM_USER_ID"] ?? null;

			$arFields = array(
				"USER" => $arToInfo["USER_ID"],
				"USER_ID" => count($arNames) > 1? $arToInfo["USER_ID"]: $fromUserId,
				"USER_LOGIN" => $arToInfo["USER_LOGIN"],
				"USER_NAME" => $arToInfo["USER_NAME"],
				"USER_LAST_NAME" => $arToInfo["USER_LAST_NAME"],
				"USER_SECOND_NAME" => $arToInfo["USER_SECOND_NAME"],
				"EMAIL_TO" => $arToInfo["EMAIL_TO"],
				"TITLE" => $arToInfo["TITLE"] ?? null,
				"MESSAGES" => $message,
				"MESSAGES_FROM_USERS" => serialize($messagesFromUsers)
			);
			$arFields['FROM_USER_ID'] = implode(', ', $arFromId);
			if (count($arNames) > 1)
			{
				$mailTemplate = "IM_NEW_MESSAGE_GROUP";
				$arFields['FROM_USERS'] = implode(', ', $arNames);
			}
			else
			{
				$mailTemplate = "IM_NEW_MESSAGE";
				$arFields['FROM_USER'] = implode(', ', $arNames);
			}

			$event = new CEvent;
			$event->Send($mailTemplate, $arToInfo['TO_USER_LID'], $arFields, "N");
		}

		return "CIMMail::MailMessageAgent();";
	}

	/**
	 * duplicate CIntranetUtils::IsExternalMailAvailable()
	 * for performance reasons
	 */
	public static function IsExternalMailAvailable()
	{
		global $USER;

		if (!is_object($USER) || !$USER->IsAuthorized())
			return false;

		if (!IsModuleInstalled('mail'))
			return false;

		if (COption::GetOptionString('intranet', 'allow_external_mail', 'Y') != 'Y')
			return false;

		if (COption::GetOptionString('extranet', 'extranet_site', '') == SITE_ID)
			return false;

		if (isset(\Bitrix\Main\Application::getInstance()->getKernelSession()['aExtranetUser_'.$USER->GetID()][SITE_ID]))
		{
			if (!\Bitrix\Main\Application::getInstance()->getKernelSession()['aExtranetUser_'.$USER->GetID()][SITE_ID])
				return false;
		}
		else if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
			return false;

		if (!IsModuleInstalled('dav'))
			return true;

		if (COption::GetOptionString('dav', 'exchange_server', '') == '')
			return true;

		if (COption::GetOptionString('dav', 'agent_mail', 'N') != 'Y')
			return true;

		if (COption::GetOptionString('dav', 'exchange_use_login', 'Y') == 'Y')
			return false;

		if (!CUserOptions::GetOption('global', 'davex_mailbox'))
		{
			$arUser = CUser::GetList(
				'ID', 'ASC',
				array('ID_EQUAL_EXACT' => $USER->GetID()),
				array('SELECT' => array('UF_BXDAVEX_MAILBOX'), 'FIELDS' => array('ID'))
			)->Fetch();

			CUserOptions::SetOption('global', 'davex_mailbox', empty($arUser['UF_BXDAVEX_MAILBOX']) ? 'N' : 'Y');
		}

		if (CUserOptions::GetOption('global', 'davex_mailbox') == 'Y')
			return false;

		return true;
	}

	public static function GetUserOffset($params)
	{
		$userOffset = 0;
		$localOffset = 0;

		if (!CTimeZone::Enabled())
			return 0;

		try //possible DateTimeZone incorrect timezone
		{
			$localTime = new DateTime();
			$localOffset = $localTime->getOffset();

			$autoTimeZone = trim($params["AUTO_TIME_ZONE"]);
			$userZone = $params["TIME_ZONE"];
			$factOffset = $params["TIME_ZONE_OFFSET"];

			if($autoTimeZone == "N")
			{
				$userTime = ($userZone <> ""? new DateTime(null, new DateTimeZone($userZone)) : $localTime);
				$userOffset = $userTime->getOffset();
			}
			else
			{
				if(CTimeZone::IsAutoTimeZone($autoTimeZone))
				{
					return intval($factOffset);
				}
				else
				{
					$serverZone = COption::GetOptionString("main", "default_time_zone", "");
					$serverTime = ($serverZone <> ""? new DateTime(null, new DateTimeZone($serverZone)) : $localTime);
					$userOffset = $serverTime->getOffset();
				}
			}
		}
		catch(Exception $e)
		{
			return 0;
		}
		return intval($userOffset) - intval($localOffset);
	}
}

?>