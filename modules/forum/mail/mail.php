<?php

/*
При добавлении сообщения в форум мы должны отправить всем подписанным пользователям рабочей группы сообщение следующего шаблона:
Reply-To: email@рабочей.группы
From: "Автор сообщения" <его@адрес> или "Автор сообщения" <адрес@форума> - в зависимости от настроек
To: "Участник группы" <его@адрес>
In-Reply-To: <ИдСообщенияТопика>
?References: <ИдСообщенияТопика>
Subject: [RE:] Топик сообщения

Текст сообщения

--
Ссылка на сообщение на КП

При получении сообщения в SMTP или забрали по POP3 запускаем правило, которое ищет по рабочим группам в какую пришло сообщение по адресу:
Проверяем и находим от кого
(?)Смотрим на авторизацию?
Находим тему по In-Reply-To, по Subject
Тема новая:
	- Добавляем топик с названием из темы, автором из from, текстом из тела
	- Храним: идентификатор сообщения в XML_ID, ?ссылку на оригинал сообщение,
Тема найдена:
	- Добавляем сообщение в тему, автори из from, дата пропарсенный текст из тела
	- Храним: идентификатор в XML_ID, (?)ссылку на оригинал сообщения, (?)на какие сообщения ответ
*/

IncludeModuleLangFile(__FILE__);

class CForumEMail
{
	public static function GetForumFilters($FID, $SOCNET_GROUP_ID = false)
	{
		global $DB;
		$strSql = 'SELECT *
			FROM b_forum_email
			WHERE FORUM_ID = '.intval($FID).($SOCNET_GROUP_ID>0?' AND SOCNET_GROUP_ID = '.intval($SOCNET_GROUP_ID):'').'
			ORDER BY EMAIL_GROUP '.($DB->type == 'ORACLE'?' NULLS LAST':'');

		$dbr = $DB->Query($strSql);
		return $dbr->Fetch();
	}

	public static function GetMailFilters($MAIL_FILTER_ID)
	{
		global $DB;
		$strSql = 'SELECT fe.*, f.MODERATION
			FROM b_forum_email fe INNER JOIN b_forum f ON fe.FORUM_ID=f.ID
			WHERE fe.MAIL_FILTER_ID = '.intval($MAIL_FILTER_ID);
		$dbr = $DB->Query($strSql);
		return $dbr;
	}

	public static function Set($arFields)
	{
		global $DB;

		if(is_set($arFields, "USE_EMAIL") && $arFields["USE_EMAIL"]!="Y")
			$arFields["USE_EMAIL"] = "N";

		if(is_set($arFields, "USE_SUBJECT") && $arFields["USE_SUBJECT"]!="Y")
			$arFields["USE_SUBJECT"] = "N";

		if(is_set($arFields, "NOT_MEMBER_POST") && $arFields["NOT_MEMBER_POST"]!="Y")
			$arFields["NOT_MEMBER_POST"] = "N";

		$filter = CForumEMail::GetForumFilters($arFields["FORUM_ID"], $arFields["SOCNET_GROUP_ID"]);
		if($filter)
		{
			$ID = $filter["ID"];
			if(is_set($arFields["EMAIL_FORUM_ACTIVE"])  && $arFields["EMAIL_FORUM_ACTIVE"]!="Y")
			{
				$strSql = "DELETE FROM b_forum_email WHERE ID=".$ID;
				$DB->Query($strSql);
			}

			if(is_set($arFields["MAIL_FILTER_ID"]) && intval($arFields["MAIL_FILTER_ID"])<=0)
			{
				$GLOBALS["APPLICATION"]->ThrowException("Empty field MAIL_FILTER_ID", "ERROR");
				return false;
			}

			if(is_set($arFields["EMAIL"]) && $arFields["EMAIL"]=='')
			{
				$GLOBALS["APPLICATION"]->ThrowException("Empty field EMAIL", "ERROR");
				return false;
			}

			$strUpdate = $DB->PrepareUpdate("b_forum_email", $arFields);

			$strSql =
				"UPDATE b_forum_email SET ".
					$strUpdate." ".
				"WHERE ID=".$ID;

			$DB->Query($strSql);
		}
		elseif(!is_set($arFields["EMAIL_FORUM_ACTIVE"]) || $arFields["EMAIL_FORUM_ACTIVE"]=="Y")
		{
			if(intval($arFields["MAIL_FILTER_ID"])<=0)
			{
				$GLOBALS["APPLICATION"]->ThrowException("Empty field MAIL_FILTER_ID", "ERROR");
				return false;
			}

			if($arFields["EMAIL"]=='')
			{
				$GLOBALS["APPLICATION"]->ThrowException("Empty field EMAIL", "ERROR");
				return false;
			}

			$ID = $DB->Add("b_forum_email", $arFields);
		}

		return $ID;
	}

	public static function OnGetSocNetFilterList()
	{
		return Array(
			"ID"					=>	"forumsocnet",
			"NAME"					=>	GetMessage("FORUM_MAIL_NAME"),
			"ACTION_INTERFACE"		=>	$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/mail/action.php",
//			"PREPARE_RESULT_FUNC"	=>	Array("CForumEMail", "SocnetPrepareVars"),
			"CONDITION_FUNC"		=>	Array("CForumEMail", "SocnetEMailMessageCheck"),
			"ACTION_FUNC"			=>	Array("CForumEMail", "SocnetEMailMessageAdd")
			);
	}

	public static function SocnetPrepareVars()
	{
		return '';
	}

	public static function SocnetLogMessageAdd($arParams, $arMessageFields, $action = "")
	{
		static $parser = null;
		if ($parser == null)
			$parser = new forumTextParser();
		$arAllow = array(
			"HTML" => "N",
			"ANCHOR" => "N",
			"BIU" => "N",
			"IMG" => "N",
			"LIST" => "N",
			"QUOTE" => "N",
			"CODE" => "N",
			"FONT" => "N",
			"SMILES" => "N",
			"UPLOAD" => "Y",
			"NL2BR" => "N",
			"TABLE" => "N",
			"ALIGN" => "N"
		);
//		$arParams["LOG_ID"], $arParams["LANG"] ....

		$arFieldsForSocnet = array(
			"ENTITY_TYPE" => SONET_ENTITY_GROUP,
			"ENTITY_ID" => $arParams["ENTITY_ID"],
			"EVENT_ID" => "forum",
			"=LOG_DATE" => (!!$arMessageFields["DATE_CREATE"] ? $arMessageFields["DATE_CREATE"] : $GLOBALS["DB"]->CurrentTimeFunction()),
			"LOG_UPDATE" => (!!$arMessageFields["POST_DATE"] ? $arMessageFields["POST_DATE"] : null),
			"TITLE_TEMPLATE" => str_replace(
				"#AUTHOR_NAME#",
				$arMessageFields["AUTHOR_NAME"],
				CForumEmail::GetLangMessage("FORUM_MAIL_SOCNET_TITLE_TOPIC", $arParams["LANG"])),
			"TITLE" => $arMessageFields["TITLE"],
			"MESSAGE" => $parser->convert($arMessageFields["POST_MESSAGE"], $arAllow),
			"TEXT_MESSAGE" => $parser->convert4mail($arMessageFields["POST_MESSAGE"]),
			"URL" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"],
					array(
						"UID" => $arMessageFields["AUTHOR_ID"],
						"FID" => $arMessageFields["FORUM_ID"],
						"TID" => $arMessageFields["TOPIC_ID"],
						"TITLE_SEO" => $arMessageFields["TOPIC_ID"],
						"MID" => $arMessageFields["ID"]
					)
				),
			"PARAMS" => serialize(
				array(
					"PATH_TO_MESSAGE" => CComponentEngine::MakePathFromTemplate(
							$arParams["URL_TEMPLATES_MESSAGE"], array("TID" => $arMessageFields["TOPIC_ID"])))),
			"MODULE_ID" => false,
			"CALLBACK_FUNC" => false,
			"SOURCE_ID" => $arMessageFields["ID"],
			"RATING_TYPE_ID" => "FORUM_TOPIC",
			"RATING_ENTITY_ID" => $arMessageFields["TOPIC_ID"]
		);

		if ($arMessageFields["AUTHOR_ID"] > 0)
			$arFieldsForSocnet["USER_ID"] = $arMessageFields["AUTHOR_ID"];

		$db_res = CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => $arMessageFields["ID"]));
		$ufFileID = array();
		while ($res = $db_res->Fetch())
			$ufFileID[] = $res["FILE_ID"];
		$ufDocID = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", $arMessageFields["ID"], LANGUAGE_ID);

		$logID = $arParams["LOG_ID"];
		if ($logID <= 0)
		{
			if (!empty($ufFileID))
				$arFieldsForSocnet["UF_SONET_LOG_FILE"] = $ufFileID;
			if ($ufDocID)
				$arFieldsForSocnet["UF_SONET_LOG_DOC"] = $ufDocID;

			$logID = CSocNetLog::Add($arFieldsForSocnet, false);

			if (intval($logID) > 0)
			{
				CSocNetLog::Update($logID, array("TMP_ID" => $logID));
				CSocNetLogRights::SetForSonet($logID, $arFieldsForSocnet["ENTITY_TYPE"], $arFieldsForSocnet["ENTITY_ID"], "forum", "view", true);
				if ($action == "SEND_EVENT")
					CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);
			}
		}
		if ($logID > 0 && $action == "ADD_COMMENT")
		{
			if (!empty($ufFileID))
				$arFieldsForSocnet["UF_SONET_COM_FILE"] = $ufFileID;
			if ($ufDocID)
				$arFieldsForSocnet["UF_SONET_COM_DOC"] = $ufDocID;

			$arFieldsForSocnet["LOG_ID"] = $logID;
			$arFieldsForSocnet["RATING_TYPE_ID"] = "FORUM_POST";
			$arFieldsForSocnet["RATING_ENTITY_ID"] = $arMessageFields["ID"];

			$commentID = CSocNetLogComments::Add($arFieldsForSocnet);
			CSocNetLog::CounterIncrement($commentID, false, false, "LC");
			return $commentID;
		}
		return $logID;
	}

	public static function SocnetEMailMessageCheck(&$arMessageFields, $ACTION_VARS)
	{
		$arEmails = CMailUtil::ExtractAllMailAddresses($arMessageFields["FIELD_TO"].",".$arMessageFields["FIELD_CC"].",".$arMessageFields["FIELD_BCC"]);
		$dbMbx = CMailBox::GetById($arMessageFields["MAIL_FILTER"]["MAILBOX_ID"]);
		$arMbx = $dbMbx->Fetch();
		$dbRes = CForumEMail::GetMailFilters($arMessageFields["MAIL_FILTER"]["ID"]);
		while($arRes = $dbRes->Fetch())
		{
			if($arRes["EMAIL_FORUM_ACTIVE"]=="Y")
			{
				if($arMbx["SERVER_TYPE"]=="smtp" && !in_array(CMailUtil::ExtractMailAddress($arRes["EMAIL"]), $arEmails))
					continue;

				if($arRes["EMAIL_GROUP"]!='' && !in_array(CMailUtil::ExtractMailAddress($arRes["EMAIL_GROUP"]), $arEmails))
					continue;

				if($arRes["SUBJECT_SUF"]!='' && mb_strpos($arMessageFields["SUBJECT"], $arRes["SUBJECT_SUF"]) === false)
					continue;

				$arMessageFields["FORUM_EMAIL_FILTER"] = $arRes;
				return true;
			}
		}

		return false;
	}

	public static function SocnetEMailMessageAdd($arMessageFields, $ACTION_VARS)
	{
		if(!is_array($arMessageFields["FORUM_EMAIL_FILTER"]))
			return false;

		if(!CModule::IncludeModule("socialnetwork"))
			return false;

		$arParams = $arMessageFields["FORUM_EMAIL_FILTER"];

		if(!CSocNetGroup::GetByID($arParams["SOCNET_GROUP_ID"]))
			return false;

		if(!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum"))
			return false;

		// Найдем кто отправитель
		$message_email = ($arMessageFields["FIELD_REPLY_TO"] <> '') ? $arMessageFields["FIELD_REPLY_TO"] : $arMessageFields["FIELD_FROM"];
		$message_email_addr = mb_strtolower(CMailUtil::ExtractMailAddress($message_email));

		$res = CUser::GetList("LAST_LOGIN", "DESC", Array("ACTIVE" => "Y", "EMAIL"=>$message_email_addr));
		if(($arUser = $res->Fetch()) && mb_strtolower(CMailUtil::ExtractMailAddress($arUser["EMAIL"])) == $message_email_addr)
			$AUTHOR_USER_ID = $arUser["ID"];
		elseif($arParams["NOT_MEMBER_POST"]=="Y")
		{
			$AUTHOR_USER_ID = false;
		}
		else
		{
			CMailLog::AddMessage(
				Array(
					"MAILBOX_ID"=>$arMessageFields["MAILBOX_ID"],
					"MESSAGE_ID"=>$arMessageFields["ID"],
					"FILTER_ID"=>$arParams["MAIL_FILTER_ID"],
					"LOG_TYPE"=>"FILTER_ERROR",
					"MESSAGE"=>GetMessage("FORUM_MAIL_ERROR1").": ".$message_email_addr
					)
				);

			return false;
		}

		if($arParams["NOT_MEMBER_POST"]!="Y")
		{
			// Проверим права доступа
			if(CSocNetFeaturesPerms::CanPerformOperation($AUTHOR_USER_ID, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "full"))
				$PERMISSION = "Y";
			elseif(CSocNetFeaturesPerms::CanPerformOperation($AUTHOR_USER_ID, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "newtopic"))
				$PERMISSION = "M";
			elseif(CSocNetFeaturesPerms::CanPerformOperation($AUTHOR_USER_ID, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "forum", "answer"))
				$PERMISSION = "I";
			else
			{
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$arMessageFields["MAILBOX_ID"],
						"MESSAGE_ID"=>$arMessageFields["ID"],
						"FILTER_ID"=>$arParams["MAIL_FILTER_ID"],
						"LOG_TYPE"=>"FILTER_ERROR",
						"MESSAGE"=>GetMessage("FORUM_MAIL_ERROR2")." ".$arUser["LOGIN"]." [".$AUTHOR_USER_ID."] (".$message_email_addr.")"
						)
					);

				return false;
			}
		}

		$body = $arMessageFields["BODY"];
		//$body = preg_replace("/(\r\n)+/", "\r\n", $body);
		$p = mb_strpos($body, "\r\nFrom:");
		if($p>0)
		{
			$body = mb_substr($body, 0, $p)."\r\n[CUT]".mb_substr($body, $p)."[/CUT]";
		}


		$subject = $arMessageFields["SUBJECT"];
		if (!isset($subject))
		{
			$subject = '';
		}
		// обрежем все RE и FW
		$subject = trim(preg_replace('#^\s*((RE[0-9\[\]]*:\s*)|(FW:\s*))+(.*)$#i', '\4', $subject));
		if($subject=='')
			$subject = GetMessage("FORUM_MAIL_EMPTY_TOPIC_TITLE")." ".rand();

		// Найдем какая тема
		$arFields = Array();
		$FORUM_ID = intval($arParams["FORUM_ID"]);
		$SOCNET_GROUP_ID = intval($arParams["SOCNET_GROUP_ID"]);
		$TOPIC_ID = 0;
		global $DB;
		if($arMessageFields["IN_REPLY_TO"]!='')
		{
			$dbTopic = $DB->Query("SELECT FT.ID FROM b_forum_topic FT INNER JOIN b_forum_message FM ON FM.TOPIC_ID=FT.ID WHERE FM.XML_ID='".$DB->ForSQL($arMessageFields["IN_REPLY_TO"], 255)."' AND FT.FORUM_ID=".$FORUM_ID." AND FT.SOCNET_GROUP_ID=".$SOCNET_GROUP_ID);
			if($arTopic = $dbTopic->Fetch())
				$TOPIC_ID = $arTopic["ID"];
		}

		if($arParams["USE_SUBJECT"] == "Y" && $TOPIC_ID<=0)
		{
			$dbTopic = $DB->Query("SELECT ID FROM b_forum_topic WHERE TITLE='".$DB->ForSQL($subject, 255)."' AND FORUM_ID=".$FORUM_ID." AND SOCNET_GROUP_ID=".$SOCNET_GROUP_ID);// ограничить по старости?
			if($arTopic = $dbTopic->Fetch())
				$TOPIC_ID = $arTopic["ID"];
		}

		if($AUTHOR_USER_ID>0)
		{
			if($TOPIC_ID<0 && $PERMISSION <= "I")
			{
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$arMessageFields["MAILBOX_ID"],
						"MESSAGE_ID"=>$arMessageFields["ID"],
						"FILTER_ID"=>$arParams["MAIL_FILTER_ID"],
						"LOG_TYPE"=>"FILTER_ERROR",
						"MESSAGE"=>GetMessage("FORUM_MAIL_ERROR3")." ".$arUser["LOGIN"]." [".$AUTHOR_USER_ID."] (".$message_email_addr.")"
						)
					);
				return false;
			}

			$bSHOW_NAME = true;
			$res = CForumUser::GetByUSER_ID($AUTHOR_USER_ID);
			if ($res)
				$bSHOW_NAME = ($res["SHOW_NAME"]=="Y");

			if ($bSHOW_NAME)
				$AUTHOR_NAME = $arUser["NAME"].($arUser["NAME"] == '' || $arUser["LAST_NAME"] == ''?"":" ").$arUser["LAST_NAME"];

			if (Trim($AUTHOR_NAME) == '')
				$AUTHOR_NAME = $arUser["LOGIN"];
		}
		else
		{
			$AUTHOR_NAME = $arMessageFields["FIELD_FROM"];
			$arFields["AUTHOR_EMAIL"] = $arMessageFields["FIELD_FROM"];
		}

		$arFields["NEW_TOPIC"] = "N";

		if($PERMISSION>="Q")
			$arFields["APPROVED"] = "Y";
		else
			$arFields["APPROVED"] = ($arParams["MODERATION"]=="Y") ? "N" : "Y";

		// Добавим новую тему
		if($TOPIC_ID<=0)
		{
			$arTopicFields = Array(
				"TITLE"			=> $subject,
				"FORUM_ID"		=> $FORUM_ID,
				"USER_START_ID" => $AUTHOR_USER_ID,
				"OWNER_ID" 		=> $AUTHOR_USER_ID,
				"SOCNET_GROUP_ID" => $SOCNET_GROUP_ID,
				);

			$arTopicFields["XML_ID"] = $arMessageFields["MSG_ID"];
			$arTopicFields["APPROVED"] = $arFields['APPROVED'];

			$arTopicFields["USER_START_NAME"] = $AUTHOR_NAME;
			$arTopicFields["LAST_POSTER_NAME"] = $AUTHOR_NAME;

			$TOPIC_ID = CForumTopic::Add($arTopicFields);
			if(intval($TOPIC_ID)<=0)
			{
				CMailLog::AddMessage(
					Array(
						"MAILBOX_ID"=>$arMessageFields["MAILBOX_ID"],
						"MESSAGE_ID"=>$arMessageFields["ID"],
						"FILTER_ID"=>$arParams["MAIL_FILTER_ID"],
						"LOG_TYPE"=>"FILTER_ERROR",
						"MESSAGE"=>GetMessage("FORUM_MAIL_ERROR4")
						)
					);
				return false;
			}

			$arFields["NEW_TOPIC"] = "Y";
		}

		// Добавим сообщение
		$arFields["POST_MESSAGE"] = $body;

		// Аттаченные файлы
		$arFILES = array();
		$rsAttach = CMailAttachment::GetList(Array(), Array("MESSAGE_ID"=>$arMessageFields["ID"]));
		while ($arAttach = $rsAttach->Fetch())
		{
			if ($arAttach['FILE_ID'])
				$arAttach['FILE_DATA'] = CMailAttachment::getContents($arAttach);
			$filename = CTempFile::GetFileName(md5(uniqid("")).".tmp");
			CheckDirPath($filename);
			if(file_put_contents($filename, $arAttach["FILE_DATA"]) !== false)
			{
				$arFile = array(
					"name" => $arAttach["FILE_NAME"],
					"type" => $arAttach["CONTENT_TYPE"],
					"size" => @filesize($filename),
					"tmp_name" => $filename,
					"MODULE_ID" => "forum",
				);
				$arFilter = array("FORUM_ID" => $FORUM_ID);
				$arFiles = array($arFile);

				if(CForumFiles::CheckFields($arFiles, $arFilter))
				{
					$arFILES[] = $arFiles[0];
				}
				else
				{
					$oError = $GLOBALS["APPLICATION"]->GetException();
					CMailLog::AddMessage(array(
						"MAILBOX_ID" => $arMessageFields["MAILBOX_ID"],
						"MESSAGE_ID" => $arMessageFields["ID"],
						"FILTER_ID" => $arParams["MAIL_FILTER_ID"],
						"LOG_TYPE" => "FILTER_ERROR",
						"MESSAGE" => GetMessage("FORUM_MAIL_ERROR6")." (".$arAttach["FILE_NAME"]."): ".($oError && $oError->GetString() ? $oError->GetString() : ""),
					));
				}
			}
		}

		if(count($arFILES)>0)
			$arFields["FILES"] = $arFILES;

		$arFields["AUTHOR_NAME"] = $AUTHOR_NAME;
		$arFields["AUTHOR_ID"] = $AUTHOR_USER_ID;
		$arFields["FORUM_ID"] = $FORUM_ID;
		$arFields["TOPIC_ID"] = $TOPIC_ID;
		$arFields["XML_ID"] = $arMessageFields["MSG_ID"];
		$arFields["SOURCE_ID"] = "EMAIL";
		$arRes = array();
		if (!empty($arMessageFields["FIELD_FROM"]))
			$arRes[] = "From: ".$arMessageFields["FIELD_FROM"];
		if (!empty($arMessageFields["FIELD_TO"]))
			$arRes[] = "To: ".$arMessageFields["FIELD_TO"];
		if (!empty($arMessageFields["FIELD_CC"]))
			$arRes[] = "Cc: ".$arMessageFields["FIELD_CC"];
		if (!empty($arMessageFields["FIELD_BCC"]))
			$arRes[] = "Bcc: ".$arMessageFields["FIELD_BCC"];
		$arRes[] = "Subject: ".$arMessageFields["SUBJECT"];
		$arRes[] = "Date: ".$arMessageFields["FIELD_DATE"];

		$arFields["MAIL_HEADER"] = implode("\r\n", $arRes);

		preg_match_all('#Received:\s+from\s+(.*)by.*#i', $arMessageFields["HEADER"], $regs);
		if(is_array($regs) && is_array($regs[1]))
			$arFields["AUTHOR_IP"] = $arFields["AUTHOR_REAL_IP"] = '<email: '.$regs[1][count($regs[1])-1].'>';
		else
			$arFields["AUTHOR_IP"] = $arFields["AUTHOR_REAL_IP"] = '<email: no address>';
		/*

		$AUTHOR_IP = ForumGetRealIP();
		$AUTHOR_IP_tmp = $AUTHOR_IP;
		$AUTHOR_REAL_IP = $_SERVER['REMOTE_ADDR'];
		if (COption::GetOptionString("forum", "FORUM_GETHOSTBYADDR", "N") == "Y")
		{
			$AUTHOR_IP = @gethostbyaddr($AUTHOR_IP);

			if ($AUTHOR_IP_tmp==$AUTHOR_REAL_IP)
				$AUTHOR_REAL_IP = $AUTHOR_IP;
			else
				$AUTHOR_REAL_IP = @gethostbyaddr($AUTHOR_REAL_IP);
		}

		$arFields["AUTHOR_IP"] = ($AUTHOR_IP!==False) ? $AUTHOR_IP : "<no address>";
		$arFields["AUTHOR_REAL_IP"] = ($AUTHOR_REAL_IP!==False) ? $AUTHOR_REAL_IP : "<no address>";
		*/

		$strErrorMessage = '';
		$MESSAGE_ID = CForumMessage::Add($arFields, false);
		if (intval($MESSAGE_ID)<=0)
		{
			$str = $GLOBALS['APPLICATION']->GetException();
			if ($str && $str->GetString())
				$strErrorMessage .= "[".$str->GetString()."]";

			if($arFields["NEW_TOPIC"] == 'Y')
				CForumTopic::Delete($TOPIC_ID);

			CMailLog::AddMessage(
				Array(
					"MAILBOX_ID"=>$arMessageFields["MAILBOX_ID"],
					"MESSAGE_ID"=>$arMessageFields["ID"],
					"FILTER_ID"=>$arParams["MAIL_FILTER_ID"],
					"LOG_TYPE"=>"FILTER_ERROR",
					"MESSAGE"=>GetMessage("FORUM_MAIL_ERROR5")." ".$strErrorMessage
					)
				);
		}

		if($MESSAGE_ID>0)
		{
			CMailLog::AddMessage(
				Array(
					"MAILBOX_ID"=>$arMessageFields["MAILBOX_ID"],
					"MESSAGE_ID"=>$arMessageFields["ID"],
					"FILTER_ID"=>$arParams["MAIL_FILTER_ID"],
					"LOG_TYPE"=>"FILTER_COMPLETE",
					"MESSAGE"=>GetMessage("FORUM_MAIL_OK")." ".$MESSAGE_ID." (TID#".$TOPIC_ID.")"
					)
				);

			CForumMessage::SendMailMessage($MESSAGE_ID, array(), false, "NEW_FORUM_MESSAGE");

			$dbSite = CSite::GetById($arMessageFields["LID"]);
			if($arSite = $dbSite->Fetch())
				$lang = $arSite['LANGUAGE_ID'];
			else
				$lang = $LANGUAGE_ID;
			$params = array(
				"LOG_ID" => 0,
				"ENTITY_ID" => $SOCNET_GROUP_ID,
				"URL_TEMPLATES_MESSAGE" => $arParams["URL_TEMPLATES_MESSAGE"],
				"LANG" => $lang
			);

			$arMessageFields = array(
				"FORUM_ID" => $FORUM_ID,
				"TOPIC_ID" => $TOPIC_ID,
				"ID" => $MESSAGE_ID,
				"AUTHOR_NAME" => $AUTHOR_NAME,
				"AUTHOR_ID" => $AUTHOR_USER_ID,
				"TITLE" => $subject,
				"POST_MESSAGE" => $body,
			);
			if ($arFields["NEW_TOPIC"] == "Y")
			{
				$log_id = self::SocnetLogMessageAdd(
					$params,
					$arMessageFields,
					"SEND_EVENT"
				);
			}
			else
			{
				$dbForumMessage = CForumMessage::GetList(
					array("ID" => "ASC"),
					array("TOPIC_ID" => $TOPIC_ID)
				);
				if ($arForumMessage = $dbForumMessage->Fetch())
				{
					$dbRes = CSocNetLog::GetList(
						array("ID" => "DESC"),
						array(
							"EVENT_ID" => "forum",
							"SOURCE_ID" => $arForumMessage["ID"]
						),
						false,
						false,
						array("ID", "TMP_ID")
					);
					if ($arRes = $dbRes->Fetch())
						$log_id = $arRes["TMP_ID"];
					else
					{
						$dbFirstMessage = CForumMessage::GetList(
							array("ID" => "ASC"),
							array("TOPIC_ID" => $arForumMessage["TOPIC_ID"]),
							false,
							1
						);
						if ($arFirstMessage = $dbFirstMessage->Fetch())
						{
							$arTopic = CForumTopic::GetByID($arFirstMessage["TOPIC_ID"]);
							$arFirstMessage["POST_MESSAGE"] = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $arFirstMessage["POST_MESSAGE_FILTER"] : $arFirstMessage["POST_MESSAGE"]);
							$arFirstMessage["TITLE"] = $arTopic["TITLE"];
							$log_id = self::SocnetLogMessageAdd(
								$params,
								$arFirstMessage
							);
						}
					}
					if (intval($log_id) > 0)
					{
						$params["LOG_ID"] = $log_id;
						self::SocnetLogMessageAdd(
							$params,
							$arMessageFields,
							"ADD_COMMENT"
						);
					}
				}
			}
		}
	}

	public static function GetLangMessage($ID, $lang)
	{
		$MESS = Array();
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/forum/lang/'.$lang.'/mail/mail.php'))
			include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/forum/lang/'.$lang.'/mail/mail.php');
		if($MESS[$ID])
			return $MESS[$ID];
		include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/forum/lang/en/mail/mail.php');
		return $MESS[$ID];
	}
}
