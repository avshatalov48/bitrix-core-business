<?
IncludeModuleLangFile(__FILE__);

class CIMShare
{
	const TYPE_POST = 'POST';
	const TYPE_TASK = 'TASK';

	function __construct($user_id = null)
	{
		if (is_null($user_id))
		{
			global $USER;
			$this->user_id = IntVal($USER->GetID());
		}
		else
		{
			$this->user_id = intval($user_id);
		}
	}

	public function Chat($messageId)
	{
		$CIMMessage = new CIMMessage($this->user_id);
		$message = $CIMMessage->GetMessage($messageId, true);
		if (!$message)
			return false;

		$parentChatId = $message['CHAT_ID'];

		$joinUsers = Array($this->user_id);
		if (!\Bitrix\Im\User::getInstance($message['AUTHOR_ID'])->isExtranet())
		{
			$joinUsers[] = (int)$message['AUTHOR_ID'];
		}

		$chat = new CIMChat($this->user_id);
		$chatId = $chat->Add(Array(
			'USERS' => $joinUsers,
			'SKIP_ADD_MESSAGE' => 'Y',
		));

		$parentMessageId = $this->SendMessage('', GetMessage('IM_SHARE_CHAT_CHAT'), $message, array(
			'CHAT_ID' => $chatId,
			'CHAT_MESSAGE' => 1,
			'CHAT_LAST_DATE' => new \Bitrix\Main\Type\DateTime(),
			'CHAT_USER' => array_unique($joinUsers),
		));

		$sendMessage = '';
		if ($message['MESSAGE'])
		{
			$sendMessage .= $message['MESSAGE']."\n";
		}

		$files = CIMMessageParam::Get($messageId, 'FILE_ID');
		if (!empty($files))
		{
			foreach ($files as $fileId)
			{
				$sendMessage .= " [DISK={$fileId}]";
			}
		}

		if ($message['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$dialogId = $this->user_id;
			$relations = \Bitrix\Im\Chat::getRelation($parentChatId);
			foreach ($relations as $relation)
			{
				if ($relation['USER_ID'] != $this->user_id)
				{
					$dialogId = $relation['USER_ID'];
				}
			}
			$chatLink = '[USER='.$dialogId.']'.Bitrix\Im\User::getInstance($dialogId)->getFullName(false)."[/USER]";
		}
		else
		{
			$chatLink = '[CHAT='.$parentChatId.']'.$message['CHAT_TITLE']."[/CHAT]";
		}

		$message['MESSAGE'] = trim($sendMessage);
		$message['MESSAGE_TYPE'] = IM_MESSAGE_CHAT;
		$message['CHAT_ID'] = $chatId;

		$noticeMessage = GetMessage('IM_SHARE_CHAT_CHAT_WELCOME', Array(
			'#CHAT#' => $chatLink
		));
		$pinMessageId = $this->SendMessage('', $noticeMessage, $message);

		\Bitrix\Im\Model\ChatTable::update($chatId, Array(
			'PARENT_ID' => $parentChatId,
			'PARENT_MID' => $parentMessageId,
			'PIN_MESSAGE_ID' => $pinMessageId,
		));

		return true;
	}

	public function Task($messageId, $date = '')
	{
		if (!CModule::IncludeModule("tasks"))
			return false;

		$CIMMessage = new CIMMessage($this->user_id);
		$message = $CIMMessage->GetMessage($messageId, true);

		if (!$message)
			return false;

		$task = new \Bitrix\Tasks\Item\Task(0, $this->user_id);

		$taskTitle = substr(trim(preg_replace(
			array("/\n+/is".BX_UTF_PCRE_MODIFIER, '/\s+/is'.BX_UTF_PCRE_MODIFIER),
			" ",
			CTextParser::clearAllTags($message['MESSAGE'])
		)), 0, 255);
		$task->title = $taskTitle? $taskTitle: CTextParser::clearAllTags(GetMessage('IM_SHARE_CHAT_TASK', Array('#LINK#' => '')));
		$task->description = $this->PrepareText($message)."\n";
		$task['RESPONSIBLE_ID'] = $this->user_id;
		if (
			$message['AUTHOR_ID'] > 0 && $message['AUTHOR_ID'] != $this->user_id
			&& !\Bitrix\Im\User::getInstance($message['AUTHOR_ID'])->isExtranet()
			&& !\Bitrix\Im\User::getInstance($message['AUTHOR_ID'])->isBot()
		)
		{
			$task['AUDITORS'] = Array($message['AUTHOR_ID']);
		}
		$task['CREATED_BY'] = $this->user_id;

		if (!empty($message['FILES']))
		{
			$diskUf = \Bitrix\Tasks\Integration\Disk\UserField::getMainSysUFCode();
			$task[$diskUf] = array_map(function($value){ return 'n'.$value;}, array_keys($message['FILES']));
		}

		$messageParams = Array();
		if ($message['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$messageParams = Array('LINK_ACTIVE' => Array((string)$this->user_id, (string)$message['AUTHOR_ID']));
		}
		else
		{
			$chat = \Bitrix\Im\Model\ChatTable::getById($message['CHAT_ID'])->fetch();
			if ($chat['ENTITY_TYPE'] == 'LINES' && CModule::IncludeModule('crm'))
			{
				$fieldData = explode("|", $chat['ENTITY_DATA_1']);
				if (isset($fieldData[0]) && $fieldData[0] == 'Y' && isset($fieldData[1]) && isset($fieldData[2]))
				{
					$crmType = \CCrmOwnerTypeAbbr::ResolveByTypeID(\CCrmOwnerType::ResolveID($fieldData[1]));
					$task['UF_CRM_TASK'] = array($crmType.'_'.$fieldData[2]);

				}
			}
			if ($chat['ENTITY_TYPE'] == 'SONET_GROUP')
			{
				$task['GROUP_ID'] = $chat['ENTITY_ID'];
			}
			else if ($chat['ENTITY_TYPE'] != 'SONET_GROUP')
			{
				$messageParams = Array('LINK_ACTIVE' => Array((string)$this->user_id, (string)$message['AUTHOR_ID']));
			}
		}

		$date = intval($date);
		if ($date > 0)
		{
			$task['DEADLINE'] = Bitrix\Main\Type\DateTime::createFromTimestamp($date);
		}
		else
		{
			$results = \Bitrix\Main\Text\DateConverter::decode(CTextParser::clearAllTags($message['MESSAGE']), 1000);
			if (!empty($results))
			{
				$task['DEADLINE'] = $results[0]->getDate();
				$userOffset = CTimeZone::GetOffset();
				if ($userOffset != 0)
				{
					$task['DEADLINE']->add(($userOffset*-1).' SECONDS');
				}
			}
		}

		$result = $task->save();

		if(!$result->isSuccess())
		{
			return false;
		}

		$link = CTaskNotifications::getNotificationPath(array('ID' => $this->user_id), $task->getId());

		$this->SendMessage('', GetMessage('IM_SHARE_CHAT_TASK', Array('#LINK#' => $link)), $message, $messageParams);

		return true;
	}

	public function Calendar($messageId, $date = '')
	{
		if (!CModule::IncludeModule("calendar"))
			return false;

		$CIMMessage = new CIMMessage($this->user_id);
		$message = $CIMMessage->GetMessage($messageId, true);

		if (!$message)
			return false;

		$dateFrom = new Bitrix\Main\Type\DateTime();
		$dateFrom->add('1 DAY');

		$date = intval($date);
		if ($date > 0)
		{
			$dateFrom = Bitrix\Main\Type\DateTime::createFromTimestamp($date);
		}
		else
		{
			$results = \Bitrix\Main\Text\DateConverter::decode(CTextParser::clearAllTags($message['MESSAGE']), 1000);
			if (!empty($results))
			{
				$dateFrom = $results[0]->getDate();
			}
		}

		$userOffset = CTimeZone::GetOffset();
		if ($userOffset != 0)
		{
			$dateFrom->add(($userOffset*-1).' SECONDS');
		}

		$dateTo = clone $dateFrom;
		$dateTo->add('30 MINUTES');

		$eventId = CCalendar::SaveEvent(array(
			'arFields' => array(
				'CAL_TYPE' => 'user',
				'OWNER_ID' => $this->user_id,
				'NAME' => CTextParser::clearAllTags($message['MESSAGE']),
				'DESCRIPTION' => $this->PrepareText($message),
				'SKIP_TIME' => false,
				'DATE_FROM' => $dateFrom,
				'DATE_TO' => $dateTo,
			),
			'userId' => $this->user_id,
			'autoDetectSection' => true,
			'autoCreateSection' => true
		));

		if (!$eventId)
		{
			return false;
		}

		$link = CHTTP::urlAddParams(CCalendar::GetPathForCalendarEx($this->user_id), array('EVENT_ID' => $eventId));

		$messageParams = Array('LINK_ACTIVE' => Array((string)$this->user_id));

		$this->SendMessage('', GetMessage('IM_SHARE_CHAT_CALEND', Array('#LINK#' => $link)), $message, $messageParams);

		return true;
	}

	public function Post($messageId)
	{
		global $DB;
		if (!CModule::IncludeModule("socialnetwork") || !CModule::IncludeModule("blog"))
			return false;

		$CIMMessage = new CIMMessage($this->user_id);
		$message = $CIMMessage->GetMessage($messageId, true);
		if (!$message)
			return false;

		$pathToPost = COption::GetOptionString("socialnetwork", "userblogpost_page", "/company/personal/user/#user_id#/blog/#post_id#/", SITE_ID);
		$pathToSmile = COption::GetOptionString("socialnetwork", "smile_page", false, SITE_ID);
		$blogGroupID = COption::GetOptionString("socialnetwork", "userbloggroup_id", false, SITE_ID);

		$blog = CBlog::GetByOwnerID($this->user_id);
		if (!$blog)
			$blog = $this->SonetPostCreateBlog($this->user_id, $blogGroupID, SITE_ID);

		$title = trim(preg_replace(
			array("/\n+/is".BX_UTF_PCRE_MODIFIER, '/\s+/is'.BX_UTF_PCRE_MODIFIER),
			" ",
			CTextParser::clearAllTags($message['MESSAGE'])
		));
		$title = $title? $title: CTextParser::clearAllTags(GetMessage('IM_SHARE_CHAT_POST', Array('#LINK#' => '')));

		$messagePost = $this->PrepareText($message)."\n".GetMessage('IM_SHARE_POST_WELCOME');

		$sonetRights = Array();
		if ($message['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
		{
			$chat = \Bitrix\Im\Model\ChatTable::getById($message['CHAT_ID'])->fetch();
			if ($chat['ENTITY_TYPE'] == 'SONET_GROUP')
			{
				$sonetRights = Array('SG'.$chat['ENTITY_ID']);
			}
		}
		if (empty($sonetRights))
		{
			$relations = CIMChat::GetRelationById($message['CHAT_ID']);
			$sonetRights = array_map(function($value){ return "U".$value['USER_ID']; }, $relations);
		}

		$postFields = array(
			'TITLE'            => $title,
			'DETAIL_TEXT'      => $messagePost,
			'DETAIL_TEXT_TYPE' => 'text',
			'=DATE_PUBLISH'    => $DB->CurrentTimeFunction(),
			'PUBLISH_STATUS'   => BLOG_PUBLISH_STATUS_PUBLISH,
			'CATEGORY_ID'      => '',
			'PATH'             => CComponentEngine::MakePathFromTemplate($pathToPost, array("post_id" => "#post_id#", "user_id" => $this->user_id)),
			'URL'              => $blog['URL'],
			'PERMS_POST'       => array(),
			'PERMS_COMMENT'    => array(),
			'MICRO'            => "Y",
			'SOCNET_RIGHTS'    => $sonetRights,
			'=DATE_CREATE'     => $DB->CurrentTimeFunction(),
			'AUTHOR_ID'        => $this->user_id,
			'BLOG_ID'          => $blog['ID'],
			"HAS_IMAGES"       => "N",
			"HAS_TAGS"         => "N",
			"HAS_PROPS"        => "Y",
			"HAS_SOCNET_ALL"   => "N",
			"SEARCH_GROUP_ID"  => $blogGroupID,
			"UF_BLOG_POST_FILE" => $message['FILES']? array_map(function($value){ return 'n'.$value;}, array_keys($message['FILES'])): Array()
		);

		$newId = CBlogPost::add($postFields);
		if (!$newId)
			return false;

		$postFields["ID"] = $newId;

		$arParamsNotify = Array(
			"bSoNet" => true,
			"UserID" => $this->user_id,
			"allowVideo" => COption::GetOptionString("blog","allow_video", "Y"),
			"PATH_TO_SMILE" => $pathToSmile,
			"PATH_TO_POST" => $pathToPost,
			"user_id" => $this->user_id,
			"NAME_TEMPLATE" => CSite::GetNameFormat(false),
			"SITE_ID" => SITE_ID
		);
		CBlogPost::Notify($postFields, $blog, $arParamsNotify);

		BXClearCache(true, "/".SITE_ID."/blog/last_messages_list/");

		$link = str_replace(array("#post_id#", "#user_id#"), Array($postFields["ID"], $this->user_id), $pathToPost);
		$processed = CSocNetLogTools::ProcessPath(array("BLOG" => $link), $this->user_id, SITE_ID);

		$this->SendMessage('', GetMessage('IM_SHARE_CHAT_POST', Array('#LINK#' => $processed["URLS"]["BLOG"])), $message);

		return true;
	}

	private function SonetPostCreateBlog($userId, $blogGroupId, $siteId)
	{
		global $DB;
		$arFields = array(
			"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
			"GROUP_ID" => $blogGroupId,
			"ACTIVE" => "Y",
			"ENABLE_COMMENTS" => "Y",
			"ENABLE_IMG_VERIF" => "Y",
			"EMAIL_NOTIFY" => "Y",
			"ENABLE_RSS" => "Y",
			"ALLOW_HTML" => "N",
			"ENABLE_TRACKBACK" => "N",
			"SEARCH_INDEX" => "Y",
			"USE_SOCNET" => "Y",
			"=DATE_CREATE" => $DB->CurrentTimeFunction(),
			"PERMS_POST" => Array(
				1 => "I",
				2 => "I" ),
			"PERMS_COMMENT" => Array(
				1 => "P",
				2 => "P" ),
		);

		$bRights = false;
		$rsUser = CUser::GetByID($userId);
		$arUser = $rsUser->Fetch();
		if(strlen($arUser["NAME"]."".$arUser["LAST_NAME"]) <= 0)
		{
			$arFields["NAME"] = GetMessage("SNBPA_BLOG_NAME")." ".$arUser["LOGIN"];
		}
		else
		{
			$arFields["NAME"] = GetMessage("SNBPA_BLOG_NAME")." ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
		}

		$arFields["URL"] = str_replace(" ", "_", $arUser["LOGIN"])."-blog-".$siteId;
		$arFields["OWNER_ID"] = $userId;

		$urlCheck = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arFields["URL"]);
		if ($urlCheck != $arFields["URL"])
		{
			$arFields["URL"] = "u".$userId."-blog-".$siteId;
		}

		if(CBlog::GetByUrl($arFields["URL"]))
		{
			$uind = 0;
			do
			{
				$uind++;
				$arFields["URL"] = $arFields["URL"].$uind;
			}
			while (CBlog::GetByUrl($arFields["URL"]));
		}

		$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $arFields["OWNER_ID"], "blog", "view_post");
		if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
		{
			$bRights = true;
		}

		$blogID = CBlog::Add($arFields);
		BXClearCache(true, "/blog/form/blog/");
		if ($bRights)
		{
			CBlog::AddSocnetRead($blogID);
		}

		return CBlog::GetByID($blogID);
	}

	private function PrepareText($quoteMessage)
	{
		$quoteMessage['MESSAGE'] = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $quoteMessage['MESSAGE']);
		$quoteMessage['MESSAGE'] = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $quoteMessage['MESSAGE']);
		$quoteMessage['MESSAGE'] = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $quoteMessage['MESSAGE']);
		//$quoteMessage['MESSAGE'] = preg_replace_callback('#<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>#', array($this, "PrepareImages"), $quoteMessage['MESSAGE']);

		$result = '[QUOTE]';
		if ($quoteMessage['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
		{
			$chat = \Bitrix\Im\Model\ChatTable::getById($quoteMessage['CHAT_ID'])->fetch();
			if ($chat)
			{
				$url = $chat['ENTITY_TYPE'] == 'LINES'? 'imol|'.$chat['ENTITY_ID']: 'chat'.$chat['ID'];
				$result .= "[B]".GetMessage('IM_SHARE_CHAT').":[/B] [URL=/online/?IM_DIALOG=".$url."]".$chat['TITLE']."[/URL]\n";
			}
		}

		$userName = \Bitrix\Im\User::getInstance($quoteMessage['AUTHOR_ID'])->getFullName(false);
		$result .= "[B]".$userName."[/B]\n";

		$result .= HTMLToTxt(nl2br($quoteMessage['MESSAGE']), '', array(), 0);
		if (!empty($quoteMessage['FILES']))
		{
			foreach ($quoteMessage['FILES'] as $file)
			{
				$result .= "[".GetMessage("IM_SHARE_FILE").": ".$file['name']."]\n";
			}
		}
		$result .= '[/QUOTE]';

		return $result;
	}

	private function SendMessage($startText, $endText, $quoteMessage, $messageParams = Array())
	{
		$userName = \Bitrix\Im\User::getInstance($quoteMessage['AUTHOR_ID'])->getFullName(false);
		$messageDate = FormatDate('X', $quoteMessage['DATE_CREATE'], time() + CTimeZone::GetOffset());


		$sendMessage = $startText;
		$sendMessage .= "------------------------------------------------------\n";
		$sendMessage .= $userName." [".$messageDate."]\n";
		if (trim($quoteMessage['MESSAGE']))
		{
			$sendMessage .= $quoteMessage['MESSAGE']."\n";
		}
		if (!empty($quoteMessage['FILES']))
		{
			foreach ($quoteMessage['FILES'] as $fileId =>  $file)
			{
				$sendMessage .= "[".GetMessage("IM_SHARE_FILE").": ".$file['name']."]\n";
			}
		}
		$sendMessage .= "------------------------------------------------------\n";
		$sendMessage .= $endText;

		$messageParams['CLASS'] = "bx-messenger-content-item-system";

		if ($quoteMessage['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			if ($quoteMessage['AUTHOR_ID'] == $this->user_id)
			{
				$relation = \Bitrix\Im\Model\RelationTable::getList(Array(
					'select' => Array('USER_ID'),
					'filter' => Array(
						'=CHAT_ID' => $quoteMessage['CHAT_ID'],
						'!=USER_ID' => $quoteMessage['AUTHOR_ID'],
					)
				))->fetch();
				if ($relation)
				{
					$quoteMessage['AUTHOR_ID'] = $relation['USER_ID'];
				}
			}

			$messageId = CIMMessage::Add(Array(
				'FROM_USER_ID' => $this->user_id,
				'TO_USER_ID' => $quoteMessage['AUTHOR_ID'],
				'MESSAGE' => $sendMessage,
				'PARAMS' => $messageParams,
				'SYSTEM' => 'Y',
				'URL_PREVIEW' => 'N',
				'SKIP_CONNECTOR' => 'Y',
				'SKIP_COMMAND' => 'Y',
				'SILENT_CONNECTOR' => 'Y',
			));
		}
		else
		{
			$messageId = CIMChat::AddMessage(Array(
				'TO_CHAT_ID' => $quoteMessage['CHAT_ID'],
				'MESSAGE' => $sendMessage,
				'PARAMS' => $messageParams,
				'SYSTEM' => 'Y',
				'URL_PREVIEW' => 'N',
				'SKIP_CONNECTOR' => 'Y',
				'SKIP_COMMAND' => 'Y',
				'SILENT_CONNECTOR' => 'Y',
			));
		}

		return $messageId;
	}
}
?>