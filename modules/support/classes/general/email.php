<?php

IncludeModuleLangFile(__FILE__);

class CSupportEMail
{
	public static function OnGetFilterList()
	{
		return Array(
			"ID"					=>	"support",
			"NAME"					=>	GetMessage("SUP_ADD_MESSAGE_TO_TECHSUPPORT"),
			"ACTION_INTERFACE"		=>	$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/mail/action.php",
			"PREPARE_RESULT_FUNC"	=>	Array("CSupportEMail", "PrepareVars"),
			"CONDITION_FUNC"		=>	Array("CSupportEMail", "EMailMessageCheck"),
			"ACTION_FUNC"			=>	Array("CSupportEMail", "EMailMessageAdd")
			);
	}

	public static function PrepareVars()
	{
		return
			'W_SUPPORT_CATEGORY='.urlencode($_REQUEST["W_SUPPORT_CATEGORY"]).
			'&W_SUPPORT_SITE_ID='.urlencode($_REQUEST["W_SUPPORT_SITE_ID"]).
			'&W_SUPPORT_CRITICALITY='.urlencode($_REQUEST["W_SUPPORT_CRITICALITY"]).
			'&W_SUPPORT_ADD_MESSAGE_AS_HIDDEN='.urlencode($_REQUEST["W_SUPPORT_ADD_MESSAGE_AS_HIDDEN"]).
			'&W_SUPPORT_SUBJECT='.urlencode($_REQUEST["W_SUPPORT_SUBJECT"]).
			'&W_SUPPORT_SEC='.urlencode($_REQUEST["W_SUPPORT_SEC"]).
			'&W_SUPPORT_USER_FIND='.urlencode($_REQUEST["W_SUPPORT_USER_FIND"]);
	}

	public static function EMailMessageCheck($arFields, $ACTION_VARS)
	{
		$arActionVars = explode("&", $ACTION_VARS);
		$countAr = count($arActionVars);
		for($i=0; $i<$countAr; $i++)
		{
			$v = $arActionVars[$i];
			if($pos = mb_strpos($v, "="))
				${mb_substr($v, 0, $pos)} = urldecode(mb_substr($v, $pos + 1));
		}
		return true;
	}

	public static function EMailMessageAdd($arMessageFields, $ACTION_VARS)
	{
		$arActionVars = explode("&", $ACTION_VARS);
		$countAr = count($arActionVars);
		for($i=0; $i<$countAr; $i++)
		{
			$v = $arActionVars[$i];
			if($pos = mb_strpos($v, "="))
				${mb_substr($v, 0, $pos)} = urldecode(mb_substr($v, $pos + 1));
		}

		if(!CModule::IncludeModule("support"))
			return false;

		if ($W_SUPPORT_SITE_ID <> '')
		{
			$rs = CSite::GetByID($W_SUPPORT_SITE_ID);
			if ($ar = $rs->Fetch()) $SITE_ID = $ar["LID"];
		}
		if ($SITE_ID == '')
		{
			$SITE_ID = $arMessageFields["LID"];
		}

		$sourceMail = COption::GetOptionString("support", "SOURCE_MAIL");
		$dbr = CTicketDictionary::GetBySID($sourceMail, "SR", $SITE_ID);
		if(!($ar = $dbr->Fetch()))
			return false;

		$TICKET_SOURCE_ID = $ar["ID"];
		$ID = $arMessageFields["ID"];
		$message_email = ($arMessageFields["FIELD_REPLY_TO"] <> '') ? $arMessageFields["FIELD_REPLY_TO"] : $arMessageFields["FIELD_FROM"];
		$message_email_addr = mb_strtolower(CMailUtil::ExtractMailAddress($message_email));

		$TID = 0;
		$arSubjects = explode("\n", trim($W_SUPPORT_SUBJECT));
		$countAr = count($arSubjects);
		for($i=0; $i<$countAr; $i++)
		{
			$arSubjects[$i] = Trim($arSubjects[$i]);
			if($arSubjects[$i] <> '')
			{
				if(preg_match("/".$arSubjects[$i]."/u", $arMessageFields["SUBJECT"], $regs))
				{
					$TID = intval($regs[1]);
					break;
				}
			}
		}

		if($TID>0)
		{
			$db_ticket = CTicket::GetByID($TID, $SITE_ID, "N", "N", "N");
			if($ar_ticket = $db_ticket->Fetch())
			{
				//check user email address limits
				if($W_SUPPORT_SEC == "domain" || $W_SUPPORT_SEC == "email")
				{
					$bEMailOK = false;
					if($TICKET_SOURCE_ID == $ar_ticket["SOURCE_ID"])
					{
						$ticket_email = mb_strtolower(CMailUtil::ExtractMailAddress($ar_ticket["OWNER_SID"]));
						if($W_SUPPORT_SEC == "domain")
							$ticket_email = mb_substr($ticket_email, mb_strpos($ticket_email, "@"));

						if(mb_strpos($message_email_addr, $ticket_email) !== false)
							$bEMailOK = true;
					}

					if(!$bEMailOK && $ar_ticket["OWNER_USER_ID"]>0)
					{
						$db_user = CUser::GetByID($ar_ticket["OWNER_USER_ID"]);
						if($arUser = $db_user->Fetch())
						{
							$ticket_email = mb_strtolower(CMailUtil::ExtractMailAddress($arUser["EMAIL"]));
							if($check_type == "domain")
								$ticket_email = mb_substr($ticket_email, mb_strpos($ticket_email, "@"));

							if(mb_strpos($message_email_addr, $ticket_email) !== false)
								$bEMailOK = true;
						}
					}
					if(!$bEMailOK) $TID = 0;
				}
			}
			else $TID=0;
		}

		//when message subject is empty - generate it from message body
		$title = trim($arMessageFields["SUBJECT"]);
		if($title == '')
		{
			$title = trim($arMessageFields["BODY"]);
			$title = preg_replace("/[\n\r\t ]+/su", " ", $title);
			$title = mb_substr($title, 0, 50);
		}

		$arFieldsTicket = array(
			"CLOSE"					=> "N",
			"TITLE"					=> $title,
			"MESSAGE"				=> $arMessageFields["BODY"],
			"MESSAGE_AUTHOR_SID"	=> $message_email,
			"MESSAGE_SOURCE_SID"	=> "email",
			"MODIFIED_MODULE_NAME"	=> "mail",
			"EXTERNAL_ID"			=> $ID,
			"EXTERNAL_FIELD_1"		=> $arMessageFields["HEADER"]
			);

		if($W_SUPPORT_USER_FIND=="Y")
		{
			$res = CUser::GetList("LAST_LOGIN", "DESC", Array("ACTIVE" => "Y", "=EMAIL"=>$message_email_addr));
			if(($arr = $res->Fetch()) && mb_strtolower(CMailUtil::ExtractMailAddress($arr["EMAIL"])) == $message_email_addr)
			{
				$AUTHOR_USER_ID = $arr["ID"];
			}
		}

		// process attach files
		$arFILES = array();
		$rsAttach = CMailAttachment::GetList(Array(), Array("MESSAGE_ID"=>$ID));
		while ($arAttach = $rsAttach->Fetch())
		{
			if ($arAttach['FILE_ID'])
				$arAttach['FILE_DATA'] = CMailAttachment::getContents($arAttach);
			// save from db to hdd
			$filename = CTempFile::GetFileName(md5(uniqid("")).".tmp");
			CheckDirPath($filename);
			if(file_put_contents($filename, $arAttach["FILE_DATA"]) !== false)
			{
				$arFILES[] = array(
					"name" => $arAttach["FILE_NAME"],
					"type" => $arAttach["CONTENT_TYPE"],
					"size" => filesize($filename),
					"tmp_name" => $filename,
					"MODULE_ID" => "support",
				);
			}
		}
		if (count($arFILES) > 0)
			$arFieldsTicket["FILES"] = $arFILES;

		$arFieldsTicket["CURRENT_USER_ID"] = null;
		if(intval($AUTHOR_USER_ID) > 0)
		{
			$resU = CUser::GetByID(intval($AUTHOR_USER_ID));
			if($arU = $resU->Fetch())
			{
				$arFieldsTicket["CURRENT_USER_ID"] = $arU["ID"];
			}
		}

		if($TID>0) // extend exist message
		{
			$arFieldsTicket["MESSAGE_AUTHOR_USER_ID"] = $AUTHOR_USER_ID;

			if ($W_SUPPORT_ADD_MESSAGE_AS_HIDDEN=="Y")	$arFieldsTicket["HIDDEN"] = "Y";
			if ($arMessageFields["SPAM"]=="Y")			$arFieldsTicket["IS_SPAM"] = "Y";

			$TID = CTicket::Set($arFieldsTicket, $MESSAGE_ID, $TID, "N");
		}
		else // new message
		{
			$arFieldsTicket["SITE_ID"] = $SITE_ID;
			$arFieldsTicket["OWNER_USER_ID"] = $AUTHOR_USER_ID;
			$arFieldsTicket["OWNER_SID"] = $message_email;
			$arFieldsTicket["CREATED_MODULE_NAME"] = "mail";
			$arFieldsTicket["SOURCE_SID"] = "email";

			if ($arMessageFields["SPAM"]=="Y")	$arFieldsTicket["IS_SPAM"] = "Y";
			if ($W_SUPPORT_CATEGORY>0)			$arFieldsTicket["CATEGORY_ID"] = $W_SUPPORT_CATEGORY;
			if ($W_SUPPORT_CRITICALITY>0)		$arFieldsTicket["CRITICALITY_ID"] = $W_SUPPORT_CRITICALITY;

			if (trim($arFieldsTicket["TITLE"]) == '')
			{
				$arFieldsTicket["TITLE"] = " ";
			}
			if (trim($arFieldsTicket["MESSAGE"]) == '')
			{
				$arFieldsTicket["MESSAGE"] = " ";
			}
			
			$TID = CTicket::Set($arFieldsTicket, $MESSAGE_ID, "", "N");
		}
	}
}
