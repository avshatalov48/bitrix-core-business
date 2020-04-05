<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;

class CIMMessenger
{
	private $user_id = 0;
	private static $enableMessageCheck = 1;

	function __construct($user_id = false)
	{
		global $USER;
		$this->user_id = intval($user_id);
		if ($this->user_id == 0)
			$this->user_id = IntVal($USER->GetID());
	}

	/*
		$arParams keys:
		---------------
		MESSAGE_TYPE: P - private chat, G - group chat, S - notification
		TO_USER_ID
		FROM_USER_ID
		MESSAGE
		AUTHOR_ID
		EMAIL_TEMPLATE
		NOTIFY_TYPE: 1 - confirm, 2 - notify single from, 4 - notify single
		NOTIFY_MODULE: module id sender (ex: xmpp, main, etc)
		NOTIFY_EVENT: module event id for search (ex, IM_GROUP_INVITE)
		NOTIFY_TITLE: notify title to send email
		NOTIFY_BUTTONS: array of buttons - available with NOTIFY_TYPE = 1
			Array(
				Array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept', 'URL' => '/test.php?CONFIRM=Y'),
				Array('TITLE' => 'Cancel', 'VALUE' => 'N', 'TYPE' => 'cancel', 'URL' => '/test.php?CONFIRM=N'),
			)
		NOTIFY_TAG: field for group in JS notification and search in table
		NOTIFY_SUB_TAG: second TAG for search in table
	*/
	public static function Add($arFields)
	{
		global $DB;

		if (isset($arFields['DIALOG_ID']) && !empty($arFields['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arFields['DIALOG_ID']))
			{
				$arFields['TO_CHAT_ID'] = substr($arFields['DIALOG_ID'], 4);
			}
			else
			{
				$arFields['TO_USER_ID'] = intval($arFields['DIALOG_ID']);
			}
		}

		if (isset($arFields['TITLE']) && !isset($arFields['NOTIFY_TITLE']))
			$arFields['NOTIFY_TITLE'] = substr($arFields['TITLE'], 0, 255);

		if (isset($arFields['NOTIFY_MESSAGE']) && !isset($arFields['MESSAGE']))
			$arFields['MESSAGE'] = $arFields['NOTIFY_MESSAGE'];

		if (isset($arFields['NOTIFY_MESSAGE_OUT']) && !isset($arFields['MESSAGE_OUT']))
			$arFields['MESSAGE_OUT'] = $arFields['NOTIFY_MESSAGE_OUT'];

		if (isset($arFields['MESSAGE']))
		{
			$arFields['MESSAGE'] = trim(str_replace(Array('[BR]', '[br]'), "\n", $arFields['MESSAGE']));
		}

		$arFields['MESSAGE_OUT'] = isset($arFields['MESSAGE_OUT'])? trim($arFields['MESSAGE_OUT']): "";

		$arFields['URL_PREVIEW'] = isset($arFields['URL_PREVIEW']) && $arFields['URL_PREVIEW'] == 'N'? 'N': 'Y';

		$bConvert = false;
		if (isset($arFields['CONVERT']) && $arFields['CONVERT'] == 'Y')
			$bConvert = true;

		$incrementCounter = true;
		if (isset($arFields['INCREMENT_COUNTER']))
		{
			if ($arFields['INCREMENT_COUNTER'] == 'N')
			{
				$incrementCounter = Array();
			}
			else if (is_array($arFields['INCREMENT_COUNTER']))
			{
				$incrementCounter = array_keys($arFields['INCREMENT_COUNTER']);
			}
		}

		if (!isset($arFields['MESSAGE_TYPE']))
			$arFields['MESSAGE_TYPE'] = "";

		if (!isset($arFields['NOTIFY_MODULE']))
			$arFields['NOTIFY_MODULE'] = 'im';

		if (!isset($arFields['NOTIFY_EVENT']))
			$arFields['NOTIFY_EVENT'] = 'default';

		if (!isset($arFields['PARAMS']))
		{
			$arFields['PARAMS'] = Array();
		}
		if (!isset($arFields['EXTRA_PARAMS']))
		{
			$arFields['EXTRA_PARAMS'] = Array();
		}
		if (isset($arFields['ATTACH']) || isset($arFields['PARAMS']['ATTACH']))
		{
			$attach = isset($arFields['ATTACH'])? $arFields['ATTACH']: $arFields['PARAMS']['ATTACH'];
			if (is_object($attach))
			{
				$arFields['PARAMS']['ATTACH'] = Array($attach);
			}
			else if (is_array($attach))
			{
				$arFields['PARAMS']['ATTACH'] = $attach;
			}
			else
			{
				$arFields['PARAMS']['ATTACH'] = Array();
			}
		}
		if (isset($arFields['FILES']))
		{
			if (is_array($arFields['FILES']))
			{
				$arFields['PARAMS']['FILE_ID'] = $arFields['FILES'];
			}
			else
			{
				$arFields['PARAMS']['FILE_ID'] = Array();
			}
		}
		if (isset($arFields['KEYBOARD']) || isset($arFields['PARAMS']['KEYBOARD']))
		{
			$keyboard = isset($arFields['KEYBOARD'])? $arFields['KEYBOARD']: $arFields['PARAMS']['KEYBOARD'];
			if (is_object($keyboard))
			{
				$arFields['PARAMS']['KEYBOARD'] = $keyboard;
			}
			else
			{
				$arFields['PARAMS']['KEYBOARD'] = Array();
			}
		}
		if (isset($arFields['MENU']) || isset($arFields['PARAMS']['MENU']))
		{
			$menu = isset($arFields['MENU'])? $arFields['MENU']: $arFields['PARAMS']['MENU'];
			if (is_object($menu))
			{
				$arFields['PARAMS']['MENU'] = $menu;
			}
			else
			{
				$arFields['PARAMS']['MENU'] = Array();
			}
		}

		if (isset($arFields['FOR_USER_ID'])) // TODO create this feature in future
		{
			$arFields['PARAMS']['FOR_USER_ID'] = $arFields['FOR_USER_ID'];
		}

		$arFields['RECENT_ADD'] = isset($arFields['RECENT_ADD']) && $arFields['RECENT_ADD'] == 'N'? 'N': 'Y';
		$arFields['SKIP_COMMAND'] = isset($arFields['SKIP_COMMAND']) && $arFields['SKIP_COMMAND'] == 'Y'? 'Y': 'N';
		$arFields['SKIP_CONNECTOR'] = isset($arFields['SKIP_CONNECTOR']) && $arFields['SKIP_CONNECTOR'] == 'Y'? 'Y': 'N';
		$arFields['IMPORTANT_CONNECTOR'] = isset($arFields['IMPORTANT_CONNECTOR']) && $arFields['IMPORTANT_CONNECTOR'] == 'Y'? 'Y': 'N';
		$arFields['SILENT_CONNECTOR'] = isset($arFields['SILENT_CONNECTOR']) && $arFields['SILENT_CONNECTOR'] == 'Y'? 'Y': 'N';
		if ($arFields['SILENT_CONNECTOR'] == 'Y')
		{
			$arFields['PARAMS']['CLASS'] = "bx-messenger-content-item-system";
		}

		$arFields['URL_ATTACH'] = Array();
		if ($arFields['MESSAGE_TYPE'] == IM_MESSAGE_SYSTEM)
		{
			if (!isset($arFields['NOTIFY_TYPE']) && intval($arFields['FROM_USER_ID']) > 0)
				$arFields['NOTIFY_TYPE'] = IM_NOTIFY_FROM;
			else if (!isset($arFields['NOTIFY_TYPE']))
				$arFields['NOTIFY_TYPE'] = IM_NOTIFY_SYSTEM;

			if (isset($arFields['NOTIFY_ANSWER']) && $arFields['NOTIFY_ANSWER'] == 'Y')
				$arFields['PARAMS']['CAN_ANSWER'] = 'Y';

			/*
			$urlPrepare = self::PrepareUrl($arFields['MESSAGE']);
			if ($urlPrepare['RESULT'])
			{
				if (empty($arFields['MESSAGE_OUT']))
				{
					$arFields['MESSAGE_OUT'] = $arFields['MESSAGE'];
				}
				$arFields['MESSAGE'] = $urlPrepare['MESSAGE'];
				$arFields['PARAMS']['ATTACH'] = array_merge($arFields['PARAMS']['ATTACH'], $urlPrepare['ATTACH']);
			}
			*/
		}
		else if ($arFields['URL_PREVIEW'] == 'Y')
		{
			$link = new CIMMessageLink();
			$urlPrepare = $link->prepareInsert($arFields['MESSAGE']);
			if ($urlPrepare['RESULT'])
			{
				if (empty($arFields['MESSAGE_OUT']))
				{
					$arFields['MESSAGE_OUT'] = $arFields['MESSAGE'];
				}
				$arFields['MESSAGE'] = $urlPrepare['MESSAGE'];

				if (isset($arFields['PARAMS']['URL_ID']))
				{
					$arFields['PARAMS']['URL_ID'] = array_merge($arFields['PARAMS']['URL_ID'], $urlPrepare['URL_ID']);
				}
				else
				{
					$arFields['PARAMS']['URL_ID'] = $urlPrepare['URL_ID'];
				}
				$arFields['URL_ATTACH'] = $urlPrepare['ATTACH'];

				if ($urlPrepare['MESSAGE_IS_LINK'])
				{
					$arFields['PARAMS']['URL_ONLY'] = 'Y';
				}
			}
		}

		if (isset($arFields['NOTIFY_EMAIL_TEMPLATE']) && !isset($arFields['EMAIL_TEMPLATE']))
			$arFields['EMAIL_TEMPLATE'] = $arFields['NOTIFY_EMAIL_TEMPLATE'];

		if (!isset($arFields['AUTHOR_ID']))
			$arFields['AUTHOR_ID'] = intval($arFields['FROM_USER_ID']);

		foreach(GetModuleEvents("im", "OnBeforeMessageNotifyAdd", true) as $arEvent)
		{
			$result = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if($result===false || isset($result['result']) && $result['result'] === false)
			{
				$reason = self::GetReasonForMessageSendError($arFields['MESSAGE_TYPE'], $result['reason']);
				$GLOBALS["APPLICATION"]->ThrowException($reason, "ERROR_FROM_OTHER_MODULE");
				return false;
			}
		}
		if (!self::CheckFields($arFields))
		{
			return false;
		}

		if ($arFields['MESSAGE_TYPE'] != IM_MESSAGE_SYSTEM && $arFields['URL_PREVIEW'] == 'Y')
		{
			$results = \Bitrix\Im\Text::getDateConverterParams($arFields['MESSAGE']);
			foreach ($results as $result)
			{
				$arFields['PARAMS']['DATE_TEXT'][] = $result->getText();
				$arFields['PARAMS']['DATE_TS'][] = $result->getDate()->getTimestamp();
			}
		}

		if ($arFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$isSelfChat = false;
			if (isset($arFields['TO_CHAT_ID']))
			{
				$chatId = $arFields['TO_CHAT_ID'];
				$relations = CIMChat::GetRelationById($chatId);
				foreach ($relations as $rel)
				{
					if ($rel['USER_ID'] == $arFields['FROM_USER_ID'])
						continue;

					$arFields['TO_USER_ID'] = $rel['USER_ID'];
				}

				if (!IsModuleInstalled('intranet'))
				{
					if (CIMSettings::GetPrivacy(CIMSettings::PRIVACY_MESSAGE) == CIMSettings::PRIVACY_RESULT_CONTACT && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed() && !CSocNetUserRelations::IsFriends($arFields['FROM_USER_ID'], $arFields['TO_USER_ID']))
					{
						$GLOBALS["APPLICATION"]->ThrowException(GetMessage('IM_ERROR_MESSAGE_PRIVACY_SELF'), "ERROR_FROM_PRIVACY_SELF");
						return false;
					}
					else if (CIMSettings::GetPrivacy(CIMSettings::PRIVACY_MESSAGE, $arFields['TO_USER_ID']) == CIMSettings::PRIVACY_RESULT_CONTACT && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed() && !CSocNetUserRelations::IsFriends($arFields['FROM_USER_ID'], $arFields['TO_USER_ID']))
					{
						$GLOBALS["APPLICATION"]->ThrowException(GetMessage('IM_ERROR_MESSAGE_PRIVACY'), "ERROR_FROM_PRIVACY");
						return false;
					}
				}
			}
			else
			{
				$arFields['FROM_USER_ID'] = intval($arFields['FROM_USER_ID']);
				$arFields['TO_USER_ID'] = intval($arFields['TO_USER_ID']);

				if (!IsModuleInstalled('intranet'))
				{
					if (CIMSettings::GetPrivacy(CIMSettings::PRIVACY_MESSAGE) == CIMSettings::PRIVACY_RESULT_CONTACT && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed() && !CSocNetUserRelations::IsFriends($arFields['FROM_USER_ID'], $arFields['TO_USER_ID']))
					{
						$GLOBALS["APPLICATION"]->ThrowException(GetMessage('IM_ERROR_MESSAGE_PRIVACY_SELF'), "ERROR_FROM_PRIVACY_SELF");
						return false;
					}
					else if (CIMSettings::GetPrivacy(CIMSettings::PRIVACY_MESSAGE, $arFields['TO_USER_ID']) == CIMSettings::PRIVACY_RESULT_CONTACT && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed() && !CSocNetUserRelations::IsFriends($arFields['FROM_USER_ID'], $arFields['TO_USER_ID']))
					{
						$GLOBALS["APPLICATION"]->ThrowException(GetMessage('IM_ERROR_MESSAGE_PRIVACY'), "ERROR_FROM_PRIVACY");
						return false;
					}
				}
				$chatId = CIMMessage::GetChatId($arFields['FROM_USER_ID'], $arFields['TO_USER_ID']);
				if ($arFields['FROM_USER_ID'] == $arFields['TO_USER_ID'])
				{
					$isSelfChat = true;
				}
			}

			if ($chatId > 0)
			{
				$chatData = IM\Model\ChatTable::getById($chatId)->fetch();

				$arFields['CHAT_ID'] = $chatId;
				$arFields = self::UploadFileFromText($arFields);

				$arParams = Array();
				$arParams['CHAT_ID'] = $chatId;
				$arParams['AUTHOR_ID'] = intval($arFields['AUTHOR_ID']);
				$arParams['MESSAGE'] = $arFields['MESSAGE'];
				$arParams['MESSAGE_OUT'] = $arFields['MESSAGE_OUT'];
				$arParams['NOTIFY_MODULE'] = $arFields['NOTIFY_MODULE'];
				$arParams['NOTIFY_EVENT'] = $arFields['SYSTEM'] == 'Y'? 'private_system': 'private';

				if (isset($arFields['IMPORT_ID']))
					$arParams['IMPORT_ID'] = intval($arFields['IMPORT_ID']);

				if (isset($arFields['MESSAGE_DATE']))
				{
					$arParams['DATE_CREATE'] = new Bitrix\Main\Type\DateTime($arFields['MESSAGE_DATE']);
				}
				$arFiles = Array();
				$arFields['FILES'] = Array();
				if (isset($arFields['PARAMS']['FILE_ID']))
				{
					foreach ($arFields['PARAMS']['FILE_ID'] as $fileId)
					{
						$arFiles[$fileId] = $fileId;
					}
				}
				$arFields['FILES'] = CIMDisk::GetFiles($chatId, $arFiles, false);

				$messageFiles = self::GetFormatFilesMessageOut($arFields['FILES']);
				if (strlen($messageFiles) > 0)
				{
					$arParams['MESSAGE_OUT'] = strlen($arParams['MESSAGE_OUT'])>0? $arParams['MESSAGE_OUT']."\n".$messageFiles: $messageFiles;
					$arFields['MESSAGE_OUT'] = $arParams['MESSAGE_OUT'];
				}

				$arParams['MESSAGE'] = \Bitrix\Im\Text::prepareBeforeSave($arParams['MESSAGE']);

				$result = IM\Model\MessageTable::add($arParams);
				$messageID = IntVal($result->getId());
				if ($messageID <= 0)
					return false;

				IM\Model\ChatTable::update($chatId, Array(
					'MESSAGE_COUNT' => new \Bitrix\Main\DB\SqlExpression('?# + 1', 'MESSAGE_COUNT'),
					'LAST_MESSAGE_ID' => $messageID,
					'LAST_MESSAGE_STATUS' => IM_MESSAGE_STATUS_RECEIVED
				));

				if ($chatData['PARENT_MID'])
				{
					$chatData = IM\Model\ChatTable::getById($chatId)->fetch();
					CIMMessageParam::set($chatData['PARENT_MID'], Array(
						'CHAT_MESSAGE' => $chatData['MESSAGE_COUNT'],
						'CHAT_LAST_DATE' => new \Bitrix\Main\Type\DateTime()
					));
					CIMMessageParam::SendPull($chatData['PARENT_MID'], Array('CHAT_MESSAGE', 'CHAT_LAST_DATE'));
				}

				if (empty($arFields['PARAMS']))
				{
					CIMMessageParam::UpdateTimestamp($messageID, $arParams['CHAT_ID']);
				}
				else
				{
					CIMMessageParam::Set($messageID, $arFields['PARAMS']);
				}

				if (!empty($arFields['URL_ATTACH']))
				{
					if (isset($arFields['PARAMS']['ATTACH']))
					{
						$arFields['PARAMS']['ATTACH'] = array_merge($arFields['PARAMS']['ATTACH'], $arFields['URL_ATTACH']);
					}
					else
					{
						$arFields['PARAMS']['ATTACH'] = $arFields['URL_ATTACH'];
					}
				}


				$relations = CIMChat::GetRelationById($chatId);
				foreach ($relations as $relation)
				{
					if (
						IM\User::getInstance($relation['USER_ID'])->isBot()
						|| !IM\User::getInstance($relation['USER_ID'])->isActive()
					)
					{
						continue;
					}

					$addToRecent = true;
					if ($incrementCounter !== true && !in_array($relation['USER_ID'], $incrementCounter))
					{
						$addToRecent = \CIMContactList::InRecent($relation['USER_ID'], $arFields['MESSAGE_TYPE'], $relation['CHAT_ID']);
					}
					if ($addToRecent)
					{
						CIMContactList::SetRecent(Array(
							'ENTITY_ID' => $relation['USER_ID'] == $arFields['TO_USER_ID']? $arFields['FROM_USER_ID']: $arFields['TO_USER_ID'],
							'MESSAGE_ID' => $messageID,
							'CHAT_TYPE' => IM_MESSAGE_PRIVATE,
							'CHAT_ID' => $relation['CHAT_ID'],
							'RELATION_ID' => $relation['ID'],
							'USER_ID' => $relation['USER_ID']
						));
					}
				}

				CIMStatus::SetIdle($arFields['FROM_USER_ID'], false);

				if (!$bConvert)
				{
					$relations = \Bitrix\Im\Chat::getRelation($chatId, Array(
						'REAL_COUNTERS' => 'Y',
						'USER_DATA' => 'Y',
					));
					foreach ($relations as $id => $relation)
					{
						if (IM\User::getInstance($relation["USER_ID"])->isBot() || $relation['USER_DATA']['ACTIVE'] == 'N')
						{
							continue;
						}
						if (!$isSelfChat && $relation["USER_ID"] == $arFields["TO_USER_ID"])
						{
							$updateRelation = array(
								"STATUS" => IM_STATUS_UNREAD,
								"MESSAGE_STATUS" => IM_MESSAGE_STATUS_RECEIVED,
								"COUNTER" => $relation['COUNTER'],
							);
							if ($incrementCounter !== true && !in_array($relation['USER_ID'], $incrementCounter))
							{
								unset($updateRelation['COUNTER']);
							}

							IM\Model\RelationTable::update($relation["ID"], $updateRelation);
						}
						else
						{
							$relations[$id]['COUNTER'] = 0;
							IM\Model\RelationTable::update($relation["ID"], array(
								"STATUS" => IM_STATUS_READ,
								"MESSAGE_STATUS" => IM_MESSAGE_STATUS_RECEIVED,
								"COUNTER" => 0,
								"LAST_ID" => $messageID,
								"LAST_SEND_ID" => $messageID,
								"LAST_READ" => new Bitrix\Main\Type\DateTime(),
							));
						}
						\Bitrix\Im\Counter::clearCache($relation['USER_ID']);
					}

					if (CModule::IncludeModule("pull"))
					{
						$arParams['FROM_USER_ID'] = $arFields['FROM_USER_ID'];
						$arParams['TO_USER_ID'] = $arFields['TO_USER_ID'];

						$pullMessage = Array(
							'module_id' => 'im',
							'command' => 'message',
							'params' => CIMMessage::GetFormatMessage(Array(
								'ID' => $messageID,
								'CHAT_ID' => $chatId,
								'TO_USER_ID' => $arParams['TO_USER_ID'],
								'FROM_USER_ID' => $arParams['FROM_USER_ID'],
								'SYSTEM' => $arFields['SYSTEM'] == 'Y'? 'Y': 'N',
								'MESSAGE' => $arParams['MESSAGE'],
								'DATE_CREATE' => time(),
								'PARAMS' => self::PrepareParamsForPull($arFields['PARAMS']),
								'FILES' => $arFields['FILES'],
								'NOTIFY' => $incrementCounter
							)),
							'extra' => Array(
								'im_revision' => IM_REVISION,
								'im_revision_mobile' => IM_REVISION_MOBILE,
							),
						);

						$pullMessageTo = $pullMessage;
						$pullMessageFrom = $pullMessage;

						$pullMessageFrom['params']['counter'] = 0;
						\Bitrix\Pull\Event::add($arParams['FROM_USER_ID'], $pullMessageFrom);

						if ($arParams['FROM_USER_ID'] != $arParams['TO_USER_ID'])
						{
							$pullMessageTo['params']['counter'] = $relations[$arParams['TO_USER_ID']]['COUNTER'];
							\Bitrix\Pull\Event::add($arParams['TO_USER_ID'], $pullMessageTo);

							$pushParams = self::PreparePushForPrivate(Array(
								'FROM_USER_ID' => $arParams['FROM_USER_ID'],
								'MESSAGE' => $arParams['MESSAGE'],
								'MESSAGE_ID' => $messageID,
								'SYSTEM' => $arFields['SYSTEM'],
								'FILES' => $arFields['FILES'],
								'ATTACH' => isset($arFields['PARAMS']['ATTACH'])? true: false
							));

							if ($arFields['PUSH'] != 'N')
							{
								if (isset($arFields['MESSAGE_PUSH']))
								{
									$pushParams['push']['message'] = $arFields['MESSAGE_PUSH'];
									$pushParams['push']['advanced_params']['senderMessage'] = $arFields['MESSAGE_PUSH'];
								}
								$pushParams['push']['advanced_params']['counter'] = $pullMessageTo['params']['counter'];
								\Bitrix\Pull\Push::add($arParams['TO_USER_ID'], $pushParams);
							}
						}
						CPushManager::DeleteFromQueueBySubTag($arParams['FROM_USER_ID'], 'IM_MESS');
					}

					foreach(GetModuleEvents("im", "OnAfterMessagesAdd", true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array(intval($messageID), $arFields));

					$arFields['COMMAND_CONTEXT'] = 'TEXTAREA';
					$result = \Bitrix\Im\Command::onCommandAdd(intval($messageID), $arFields);
					if (!$result)
					{
						\Bitrix\Im\Bot::onMessageAdd(intval($messageID), $arFields);
					}
				}

				return $messageID;
			}
			else
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MESSAGE_CREATE"), "CHAT_ID");
				return false;
			}
		}
		else if ($arFields['MESSAGE_TYPE'] == IM_MESSAGE_CHAT || $arFields['MESSAGE_TYPE'] == IM_MESSAGE_OPEN || $arFields['MESSAGE_TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			$arFields['SKIP_USER_CHECK'] = isset($arFields['SKIP_USER_CHECK']) && $arFields['SKIP_USER_CHECK'] == 'Y'? 'Y': 'N';
			$arFields['FROM_USER_ID'] = intval($arFields['FROM_USER_ID']);
			$chatId = 0;
			$systemMessage = false;
			if (isset($arFields['SYSTEM']) && $arFields['SYSTEM'] == 'Y')
			{
				$strSql = "
					SELECT
						C.ID CHAT_ID,
						C.PARENT_ID CHAT_PARENT_ID,
						C.PARENT_MID CHAT_PARENT_MID,
						C.TITLE CHAT_TITLE,
						C.AUTHOR_ID CHAT_AUTHOR_ID,
						C.TYPE CHAT_TYPE,
						C.COLOR CHAT_COLOR,
						C.ENTITY_TYPE CHAT_ENTITY_TYPE,
						C.ENTITY_ID CHAT_ENTITY_ID,
						C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
						C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
						C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
						C.EXTRANET CHAT_EXTRANET,
						'1' RID
					FROM b_im_chat C
					WHERE C.ID = ".intval($arFields['TO_CHAT_ID'])."
				";
				$systemMessage = true;
			}
			else
			{
				$strSql = "
					SELECT
						C.ID CHAT_ID,
						C.PARENT_ID CHAT_PARENT_ID,
						C.PARENT_MID CHAT_PARENT_MID,
						C.TITLE CHAT_TITLE,
						C.AUTHOR_ID CHAT_AUTHOR_ID,
						C.TYPE CHAT_TYPE,
						C.AVATAR CHAT_AVATAR,
						C.COLOR CHAT_COLOR,
						C.ENTITY_TYPE CHAT_ENTITY_TYPE,
						C.ENTITY_ID CHAT_ENTITY_ID,
						C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
						C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
						C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
						C.EXTRANET CHAT_EXTRANET,
						R.USER_ID RID
					FROM b_im_chat C
					LEFT JOIN b_im_relation R ON R.CHAT_ID = C.ID AND R.USER_ID = ".$arFields['FROM_USER_ID']."
					WHERE C.ID = ".intval($arFields['TO_CHAT_ID'])."
				";
			}
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
			{
				$chatId = intval($arRes['CHAT_ID']);
				$chatTitle = htmlspecialcharsbx($arRes['CHAT_TITLE']);
				$chatAuthorId = intval($arRes['CHAT_AUTHOR_ID']);
				$chatParentId = intval($arRes['CHAT_PARENT_ID']);
				$chatParentMid = intval($arRes['CHAT_PARENT_MID']);
				$chatExtranet = $arRes['CHAT_EXTRANET'] == 'Y';
				$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
				$arFields['MESSAGE_TYPE'] = $arRes['CHAT_TYPE'];

				if ($arFields['SKIP_USER_CHECK'] == 'N')
				{
					if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
					{
						if (!CIMMessenger::CheckEnableOpenChat())
						{
							$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_GROUP_CANCELED"), "CANCELED");
							return false;
						}
						else if (intval($arRes['RID']) <= 0)
						{
							if (IM\User::getInstance($arFields['FROM_USER_ID'])->isExtranet())
							{
								$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_GROUP_CANCELED"), "CANCELED");
								return false;
							}
							else
							{
								$chat = new CIMChat(0);
								$chat->AddUser($chatId, $arFields['FROM_USER_ID']);
							}
						}
					}
					else if (intval($arRes['RID']) <= 0)
					{
						$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_GROUP_CANCELED"), "CANCELED");
						return false;
					}
				}
			}
			else
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_GROUP_CANCELED"), "CANCELED");
				return false;
			}

			if ($chatId > 0)
			{
				foreach(GetModuleEvents("im", "OnBeforeChatMessageAdd", true) as $arEvent)
				{
					$result = ExecuteModuleEventEx($arEvent, array($arFields, $arRes));
					if($result===false || isset($result['result']) && $result['result'] === false)
					{
						$reason = self::GetReasonForMessageSendError($arFields['MESSAGE_TYPE'], $result['reason']);
						$GLOBALS["APPLICATION"]->ThrowException($reason, "ERROR_FROM_OTHER_MODULE");
						return false;
					}
					if (isset($result['fields']))
					{
						$arFields = $result['fields'];
					}
				}

				$chatId = intval($arRes['CHAT_ID']);
				$arFields['CHAT_ID'] = $chatId;
				$arFields = self::UploadFileFromText($arFields);

				$arParams = Array();
				$arParams['CHAT_ID'] = $chatId;
				$arParams['AUTHOR_ID'] = $systemMessage? 0: intval($arFields['AUTHOR_ID']);
				$arParams['MESSAGE'] = $arFields['MESSAGE'];
				$arParams['MESSAGE_OUT'] = $arFields['MESSAGE_OUT'];
				$arParams['NOTIFY_MODULE'] = 'im';
				$arParams['NOTIFY_EVENT'] = 'group';

				if (isset($arFields['MESSAGE_DATE']))
				{
					$arParams['DATE_CREATE'] = new Bitrix\Main\Type\DateTime($arFields['MESSAGE_DATE']);
				}

				$arFiles = Array();
				$arFields['FILES'] = Array();

				if (isset($arFields['PARAMS']['FILE_ID']))
				{
					foreach ($arFields['PARAMS']['FILE_ID'] as $fileId)
					{
						$arFiles[$fileId] = $fileId;
					}
				}
				$arFields['FILES'] = CIMDisk::GetFiles($chatId, $arFiles, false);
				$messageFiles = self::GetFormatFilesMessageOut($arFields['FILES']);
				if (strlen($messageFiles) > 0)
				{
					$arParams['MESSAGE_OUT'] = strlen($arParams['MESSAGE_OUT'])>0? $arParams['MESSAGE_OUT']."\n".$messageFiles: $messageFiles;
					$arFields['MESSAGE_OUT'] = $arParams['MESSAGE_OUT'];
				}

				$arParams['MESSAGE'] = \Bitrix\Im\Text::prepareBeforeSave($arParams['MESSAGE']);

				$result = IM\Model\MessageTable::add($arParams);
				$messageID = IntVal($result->getId());
				if ($messageID <= 0)
					return false;

				IM\Model\ChatTable::update($chatId, Array(
					'MESSAGE_COUNT' => new \Bitrix\Main\DB\SqlExpression('?# + 1', 'MESSAGE_COUNT'),
					'LAST_MESSAGE_ID' => $messageID,
					'LAST_MESSAGE_STATUS' => IM_MESSAGE_STATUS_RECEIVED
				));

				if ($chatParentMid)
				{
					$chatData = IM\Model\ChatTable::getById($chatId)->fetch();
					CIMMessageParam::set($chatParentMid, Array(
						'CHAT_MESSAGE' => $chatData['MESSAGE_COUNT'],
						'CHAT_LAST_DATE' => new \Bitrix\Main\Type\DateTime()
					));
					CIMMessageParam::SendPull($chatParentMid, Array('CHAT_MESSAGE', 'CHAT_LAST_DATE'));
				}

				if (empty($arFields['PARAMS']))
				{
					CIMMessageParam::UpdateTimestamp($messageID, $arParams['CHAT_ID']);
				}
				else
				{
					CIMMessageParam::Set($messageID, $arFields['PARAMS']);
				}

				if (!empty($arFields['URL_ATTACH']))
				{
					if (isset($arFields['PARAMS']['ATTACH']))
					{
						$arFields['PARAMS']['ATTACH'] = array_merge($arFields['PARAMS']['ATTACH'], $arFields['URL_ATTACH']);
					}
					else
					{
						$arFields['PARAMS']['ATTACH'] = $arFields['URL_ATTACH'];
					}
				}

				$arParams['FROM_USER_ID'] = $arFields['FROM_USER_ID'];
				$arParams['TO_CHAT_ID'] = $arFields['TO_CHAT_ID'];

				$arBotInChat = Array();
				$relations = \Bitrix\Im\Chat::getRelation($chatId, Array(
					'REAL_COUNTERS' => 'Y',
					'USER_DATA' => 'Y',
				));

				$pullIncluded = CModule::IncludeModule("pull");
				$events = array();

				$skippedRelations = Array();

				$pushUserSkip = Array();
				$pushUserSend = Array();

				foreach ($relations as $id => $relation)
				{
					if ($arFields['RECENT_ADD'] != 'Y')
					{
						$skippedRelations[$id] = true;
						continue;
					}
					if ($relation['USER_DATA']["EXTERNAL_AUTH_ID"] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID)
					{
						$arBotInChat[$relation["USER_ID"]] = $relation["USER_ID"];
						$skippedRelations[$id] = true;
						continue;
					}
					if ($relation['USER_DATA']['ACTIVE'] == 'N')
					{
						$skippedRelations[$id] = true;
						continue;
					}

					$sessionId = 0;
					if ($arRes['CHAT_ENTITY_TYPE'] == "LINES")
					{
						if ($relation['USER_DATA']["EXTERNAL_AUTH_ID"] == 'imconnector')
						{
							$skippedRelations[$id] = true;
							continue;
						}
						if ($arRes['CHAT_ENTITY_DATA_1'])
						{
							$fieldData = explode("|", $arRes['CHAT_ENTITY_DATA_1']);
							$sessionId = intval($fieldData[5]);
						}
					}

					$addToRecent = true;
					if ($incrementCounter !== true && !in_array($relation['USER_ID'], $incrementCounter))
					{
						$addToRecent = \CIMContactList::InRecent($relation['USER_ID'], $arFields['MESSAGE_TYPE'], $relation['CHAT_ID']);
					}

					if ($addToRecent)
					{
						CIMContactList::SetRecent(Array(
							'ENTITY_ID' => $chatId,
							'MESSAGE_ID' => $messageID,
							'CHAT_TYPE' => $arFields['MESSAGE_TYPE'],
							'USER_ID' => $relation['USER_ID'],
							'CHAT_ID' => $relation['CHAT_ID'],
							'RELATION_ID' => $relation['ID'],
							'SESSION_ID' => $sessionId,
						));
					}

					if ($relation["USER_ID"] == $arFields["FROM_USER_ID"])
					{
						$relations[$id]['COUNTER'] = $relation['COUNTER'] = 0;
						IM\Model\RelationTable::update($relation["ID"], array(
							"STATUS" => IM_STATUS_READ,
							"MESSAGE_STATUS" => IM_MESSAGE_STATUS_RECEIVED,
							"COUNTER" => 0,
							"LAST_ID" => $messageID,
							"LAST_SEND_ID" => $messageID,
							"LAST_READ" => new Bitrix\Main\Type\DateTime(),
						));
					}
					else
					{
						$updateRelation = array(
							"STATUS" => IM_STATUS_UNREAD,
							"MESSAGE_STATUS" => IM_MESSAGE_STATUS_RECEIVED,
							"COUNTER" => $relation['COUNTER'],
						);
						if ($incrementCounter !== true && !in_array($relation['USER_ID'], $incrementCounter))
						{
							unset($updateRelation['COUNTER']);
						}
						IM\Model\RelationTable::update($relation["ID"], $updateRelation);
					}

					\Bitrix\Im\Counter::clearCache($relation['USER_ID']);

					if (!$pullIncluded)
					{
						continue;
					}

					$sendPush = true;
					if ($relation['USER_ID'] == $arParams['FROM_USER_ID'])
					{
						CPushManager::DeleteFromQueueBySubTag($arParams['FROM_USER_ID'], 'IM_MESS');
						$sendPush = false;
					}
					else if ($relation['NOTIFY_BLOCK'] == 'Y')
					{
						$pushUserSkip[] = $relation['USER_ID'];
						$pushUserSend[] = $relation['USER_ID'];
					}
					else if ($arFields['PUSH'] == 'N')
					{
						$sendPush = false;
					}
					else
					{
						$pushUserSend[] = $relation['USER_ID'];
					}
				}

				$pullMessage = Array(
					'module_id' => 'im',
					'command' => 'messageChat',
					'params' => CIMMessage::GetFormatMessage(Array(
						'ID' => $messageID,
						'CHAT_ID' => $chatId,
						'TO_CHAT_ID' => $arParams['TO_CHAT_ID'],
						'FROM_USER_ID' => $arParams['FROM_USER_ID'],
						'MESSAGE' => $arParams['MESSAGE'],
						'SYSTEM' => $arFields['SYSTEM'] == 'Y'? 'Y': 'N',
						'DATE_CREATE' => time(),
						'PARAMS' => self::PrepareParamsForPull($arFields['PARAMS']),
						'FILES' => $arFields['FILES'],
						'EXTRA_PARAMS' => $arFields['EXTRA_PARAMS'],
						'COUNTER' => -1,
						'NOTIFY' => $incrementCounter
					)),
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				);

				foreach ($relations as $id => $relation)
				{
					if ($skippedRelations[$id])
					{
						continue;
					}
					$events[$relation['USER_ID']] = $pullMessage;
					$events[$relation['USER_ID']]['params']['counter'] = $incrementCounter !== true && !in_array($relation['USER_ID'], $incrementCounter)? $relation['PREVIOUS_COUNTER']: $relation['COUNTER'];
					$events[$relation['USER_ID']]['groupId'] = 'im_chat_'.$chatId.'_'.$messageID.'_'.$events[$relation['USER_ID']]['params']['counter'];
				}

				if ($arFields['SYSTEM'] != 'Y')
				{
					self::SendMention(Array(
						'CHAT_ID' => $chatId,
						'CHAT_TITLE' => $chatTitle,
						'CHAT_RELATION' => $relations,
						'CHAT_TYPE' => $arFields['MESSAGE_TYPE'],
						'CHAT_ENTITY_TYPE' => $arRes['CHAT_ENTITY_TYPE'],
						'CHAT_COLOR' => $arRes['CHAT_COLOR'],
						'MESSAGE' => $arParams['MESSAGE'],
						'FILES' => $arFields['FILES'],
						'FROM_USER_ID' => $arParams['FROM_USER_ID'],
					));
				}

				if ($pullIncluded)
				{
					if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN || $arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN_LINE)
					{
						CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pullMessage);
					}

					$pushParams = self::PreparePushForChat(Array(
						'CHAT_ID' => $chatId,
						'CHAT_TITLE' => $chatTitle,
						'CHAT_TYPE' => $arRes['CHAT_TYPE'],
						'CHAT_AVATAR' => $arRes['CHAT_AVATAR'],
						'CHAT_COLOR' => $arRes['CHAT_COLOR'],
						'CHAT_ENTITY_ID' => $arRes['CHAT_ENTITY_ID'],
						'CHAT_ENTITY_TYPE' => $arRes['CHAT_ENTITY_TYPE'],
						'CHAT_ENTITY_DATA_1' => $arRes['CHAT_ENTITY_DATA_1'],
						'CHAT_EXTRANET' => $arRes['CHAT_EXTRANET'],
						'FROM_USER_ID' => $arParams['FROM_USER_ID'],
						'MESSAGE' => $arParams['MESSAGE'],
						'MESSAGE_ID' => $messageID,
						'SYSTEM' => $arFields['SYSTEM'],
						'FILES' => $arFields['FILES'],
						'LINES' => isset($pullMessage['params']['lines'][$chatId])? $pullMessage['params']['lines'][$chatId]: false,
						'ATTACH' => isset($arFields['PARAMS']['ATTACH'])? true: false
					));
					$pushParams['skip_users'] = $pushUserSkip;
					if (isset($arFields['MESSAGE_PUSH']))
					{
						$pushParams['push']['message'] = $arFields['MESSAGE_PUSH'];
						$pushParams['push']['advanced_params']['senderMessage'] = $arFields['MESSAGE_PUSH'];
					}

					$groups = self::GetEventByCounterGroup($events);
					foreach ($groups as $group)
					{
						\Bitrix\Pull\Event::add($group['users'], $group['event']);

						$userList = array_intersect($pushUserSend, $group['users']);
						if (!empty($userList))
						{
							$pushParams['push']['advanced_params']['counter'] = $group['event']['params']['counter'];
							\Bitrix\Pull\Push::add($userList, $pushParams);
						}
					}
				}

				CIMStatus::SetIdle($arFields['FROM_USER_ID'], false);

				$arFields['CHAT_AUTHOR_ID'] = $chatAuthorId;
				$arFields['CHAT_ENTITY_TYPE'] = $arRes['CHAT_ENTITY_TYPE'];
				$arFields['CHAT_ENTITY_ID'] = $arRes['CHAT_ENTITY_ID'];
				$arFields['CHAT_ENTITY_DATA_1'] = $arRes['CHAT_ENTITY_DATA_1'];
				$arFields['CHAT_ENTITY_DATA_2'] = $arRes['CHAT_ENTITY_DATA_2'];
				$arFields['CHAT_ENTITY_DATA_3'] = $arRes['CHAT_ENTITY_DATA_3'];
				$arFields['BOT_IN_CHAT'] = $arBotInChat;

				foreach(GetModuleEvents("im", "OnAfterMessagesAdd", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array(intval($messageID), $arFields));

				$arFields['COMMAND_CONTEXT'] = 'TEXTAREA';
				$result = \Bitrix\Im\Command::onCommandAdd(intval($messageID), $arFields);
				if (!$result)
				{
					\Bitrix\Im\Bot::onMessageAdd(intval($messageID), $arFields);
				}

				return $messageID;
			}
			else
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MESSAGE_CREATE"), "CHAT_ID");
				return false;
			}

		}
		else if ($arFields['MESSAGE_TYPE'] == IM_MESSAGE_SYSTEM)
		{
			$arFields['TO_USER_ID'] = intval($arFields['TO_USER_ID']);

			$orm = \Bitrix\Main\UserTable::getById($arFields['TO_USER_ID']);
			$userData = $orm->fetch();
			if (!$userData || $userData['ACTIVE'] == 'N' || $userData['EXTERNAL_AUTH_ID'] == 'email' || $userData['EXTERNAL_AUTH_ID'] == 'bot' || $userData['EXTERNAL_AUTH_ID'] == 'imconnector')
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MESSAGE_CREATE"), "TO_USER_ID");
				return false;
			}

			$strSql = "
				SELECT ID CHAT_ID
				FROM b_im_chat
				WHERE AUTHOR_ID = ".$arFields['TO_USER_ID']." AND TYPE = '".IM_MESSAGE_SYSTEM."'
				ORDER BY ID ASC
			";
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
			{
				$chatId = intval($arRes['CHAT_ID']);
			}
			else
			{
				$result = IM\Model\ChatTable::add(Array('TYPE' => IM_MESSAGE_SYSTEM, 'AUTHOR_ID' => $arFields['TO_USER_ID']));
				$chatId = $result->getId();
				if ($chatId <= 0)
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MESSAGE_CREATE"), "CHAT_ID");
					return false;
				}

				IM\Model\RelationTable::add(array(
					"CHAT_ID" => $chatId,
					"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
					"USER_ID" => intval($arFields['TO_USER_ID']),
					"STATUS" => ($bConvert? 2: 0),
				));
			}

			if ($chatId > 0)
			{
				$arParams = Array();
				$arParams['CHAT_ID'] = $chatId;
				$arParams['AUTHOR_ID'] = intval($arFields['AUTHOR_ID']);
				$arParams['MESSAGE'] = $arFields['MESSAGE'];
				$arParams['MESSAGE_OUT'] = $arFields['MESSAGE_OUT'];
				$arParams['NOTIFY_TYPE'] = intval($arFields['NOTIFY_TYPE']);
				$arParams['NOTIFY_MODULE'] = $arFields['NOTIFY_MODULE'];
				$arParams['NOTIFY_EVENT'] = $arFields['NOTIFY_EVENT'];

				//if (strlen($arParams['MESSAGE']) <= 0 && strlen($arParams['MESSAGE_OUT']) <= 0)
				//	return false;

				$sendToSite = true;
				if ($arParams['NOTIFY_TYPE'] != IM_NOTIFY_CONFIRM)
					$sendToSite = CIMSettings::GetNotifyAccess($arFields["TO_USER_ID"], $arFields["NOTIFY_MODULE"], $arFields["NOTIFY_EVENT"], CIMSettings::CLIENT_SITE);

				if (!$sendToSite)
					$arParams['NOTIFY_READ'] = 'Y';

				if (isset($arFields['IMPORT_ID']))
					$arParams['IMPORT_ID'] = intval($arFields['IMPORT_ID']);

				if (isset($arFields['MESSAGE_DATE']))
				{
					$arParams['DATE_CREATE'] = new Bitrix\Main\Type\DateTime($arFields['MESSAGE_DATE']);
				}

				if (isset($arFields['EMAIL_TEMPLATE']) && strlen(trim($arFields['EMAIL_TEMPLATE']))>0)
					$arParams['EMAIL_TEMPLATE'] = trim($arFields['EMAIL_TEMPLATE']);

				$arParams['NOTIFY_TAG'] = isset($arFields['NOTIFY_TAG'])? $arFields['NOTIFY_TAG']: '';
				$arParams['NOTIFY_SUB_TAG'] = isset($arFields['NOTIFY_SUB_TAG'])? $arFields['NOTIFY_SUB_TAG']: '';

				if (isset($arFields['NOTIFY_TITLE']) && strlen(trim($arFields['NOTIFY_TITLE']))>0)
					$arParams['NOTIFY_TITLE'] = trim($arFields['NOTIFY_TITLE']);

				if ($arParams['NOTIFY_TYPE'] == IM_NOTIFY_CONFIRM)
				{
					if (isset($arFields['NOTIFY_BUTTONS']))
					{
						foreach ($arFields['NOTIFY_BUTTONS'] as $key => $arButtons)
						{
							if (is_array($arButtons))
							{
								if (isset($arButtons['TITLE']) && strlen($arButtons['TITLE']) > 0
								&& isset($arButtons['VALUE']) && strlen($arButtons['VALUE']) > 0
								&& isset($arButtons['TYPE']) && strlen($arButtons['TYPE']) > 0)
								{
									$arButtons['TITLE'] = htmlspecialcharsbx($arButtons['TITLE']);
									$arButtons['VALUE'] = htmlspecialcharsbx($arButtons['VALUE']);
									$arButtons['TYPE'] = htmlspecialcharsbx($arButtons['TYPE']);
									$arFields['NOTIFY_BUTTONS'][$key] = $arButtons;
								}
								else
									unset($arFields['NOTIFY_BUTTONS'][$key]);
							}
							else
								unset($arFields['NOTIFY_BUTTONS'][$key]);
						}
					}
					else
					{
						$arFields['NOTIFY_BUTTONS'] = Array(
							Array('TITLE' => GetMessage('IM_ERROR_BUTTON_ACCEPT'), 'VALUE' => 'Y', 'TYPE' => 'accept'),
							Array('TITLE' => GetMessage('IM_ERROR_BUTTON_CANCEL'), 'VALUE' => 'N', 'TYPE' => 'cancel'),
						);
					}
					$arParams['NOTIFY_BUTTONS'] = serialize($arFields["NOTIFY_BUTTONS"]);

					if (isset($arParams['NOTIFY_TAG']) && strlen($arParams['NOTIFY_TAG'])>0)
						CIMNotify::DeleteByTag($arParams['NOTIFY_TAG']);
				}

				if ($sendToSite)
				{
					$result = IM\Model\MessageTable::add($arParams);
					$messageID = IntVal($result->getId());
					if ($messageID <= 0)
						return false;

					if (empty($arFields['PARAMS']))
					{
						CIMMessageParam::UpdateTimestamp($messageID, $arParams['CHAT_ID']);
					}
					else
					{
						CIMMessageParam::Set($messageID, $arFields['PARAMS']);
					}
				}
				else
				{
					$messageID = time();
				}

				$counter = \CIMNotify::GetCounter($chatId);

				$DB->Query("
					UPDATE b_im_relation 
					SET STATUS = '".IM_STATUS_UNREAD."', COUNTER = {$counter}
					WHERE USER_ID = ".intval($arFields['TO_USER_ID'])." AND MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' AND CHAT_ID = ".$chatId
				);

				\Bitrix\Im\Counter::clearCache($arFields['TO_USER_ID']);

				foreach(GetModuleEvents("im", "OnAfterNotifyAdd", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array(intval($messageID), $arFields));

				if (!empty($arFields['PARAMS']))
					CIMMessageParam::Set($messageID, $arFields['PARAMS']);

				IM\Model\ChatTable::update($chatId, Array(
					'LAST_MESSAGE_ID' => $messageID,
					'LAST_MESSAGE_STATUS' => IM_MESSAGE_STATUS_RECEIVED
				));

				CIMMessenger::SpeedFileDelete($arFields['TO_USER_ID'], IM_SPEED_NOTIFY);

				foreach(GetModuleEvents("im", "OnAfterNotifyAdd", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array(intval($messageID), $arFields));

				if (CModule::IncludeModule("pull"))
				{
					\Bitrix\Pull\Push::add($arFields['TO_USER_ID'], Array(
						'module_id' => $arParams['NOTIFY_MODULE'],
						'push' => Array(
							'type' => $arFields['NOTIFY_EVENT'],
							'message' => $arFields['PUSH_MESSAGE'],
							'params' => isset($arFields['PUSH_PARAMS'])? $arFields['PUSH_PARAMS']: '',
							'advanced_params' => isset($arFields['PUSH_PARAMS']) && isset($arFields['PUSH_PARAMS']['ADVANCED_PARAMS']) ? $arFields['PUSH_PARAMS']['ADVANCED_PARAMS']: array(),
							'tag' => $arParams['NOTIFY_TAG'],
							'sub_tag' => $arParams['NOTIFY_SUB_TAG'],
							'app_id' => isset($arParams['PUSH_APP_ID'])? $arParams['PUSH_APP_ID']: '',
						)
					));
					if ($sendToSite)
					{
						\Bitrix\Pull\Event::add($arFields['TO_USER_ID'], Array(
							'module_id' => 'im',
							'command' => 'notify',
							'params' => CIMNotify::GetFormatNotify(Array(
								'ID' => $messageID,
								'DATE_CREATE' => time(),
								'FROM_USER_ID' => intval($arFields['FROM_USER_ID']),
								'MESSAGE' => $arParams['MESSAGE'],
								'PARAMS' => self::PrepareParamsForPull($arFields['PARAMS']),
								'NOTIFY_MODULE' => $arParams['NOTIFY_MODULE'],
								'NOTIFY_EVENT' => $arParams['NOTIFY_EVENT'],
								'NOTIFY_TAG' => $arParams['NOTIFY_TAG'],
								'NOTIFY_TYPE' => $arParams['NOTIFY_TYPE'],
								'NOTIFY_BUTTONS' => isset($arParams['NOTIFY_BUTTONS'])? $arParams['NOTIFY_BUTTONS']: serialize(Array()),
								'NOTIFY_TITLE' => isset($arParams['NOTIFY_TITLE'])? $arParams['NOTIFY_TITLE']: '',
								'COUNTER' => $counter,
							)),
							'extra' => Array(
								'im_revision' => IM_REVISION,
								'im_revision_mobile' => IM_REVISION_MOBILE,
							),
						));
					}
				}


				return $messageID;
			}
			else
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MESSAGE_CREATE"), "CHAT_ID");
				return false;
			}
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MESSAGE_TYPE"), "MESSAGE_TYPE");
			return false;
		}
	}

	public static function CheckPossibilityUpdateMessage($type, $id, $userId = null)
	{
		$id = intval($id);
		if ($id <= 0)
			return false;

		$enableCheck = self::IsEnabledMessageCheck();
		if ($enableCheck)
		{
			global $USER;
			$userId = is_null($userId)? $USER->GetId(): intval($userId);
			if ($userId <= 0)
				return false;
		}

		$result = false;
		if (CModule::IncludeModule('pull') && CPullOptions::GetNginxStatus())
		{
			$message = self::GetById($id);
			$skipUserCheck = false;
			if ($message !== false)
			{
				if ($type == IM_CHECK_DELETE && $message['CHAT_ID'] == CIMChat::GetGeneralChatId() && self::IsAdmin())
				{
					$skipUserCheck = true;
				}
				else if (\Bitrix\Im\User::getInstance($userId)->isBot() && ($message['AUTHOR_ID'] == 0 || $message['PARAMS']['IS_DELETED'] == 'Y'))
				{}
				else if ($message['PARAMS']['IS_DELETED'] == 'Y' || $message['DATE_CREATE']+259200 < time())
				{
					return false;
				}
			}
			else
			{
				return false;
			}

			if ($enableCheck)
			{
				if ($message['CHAT_ENTITY_TYPE'] == 'LINES' && CModule::IncludeModule('imopenlines'))
				{
					list($connectorType) = explode("|", $message['CHAT_ENTITY_ID']);
					if (\Bitrix\Im\User::getInstance($userId)->isBot())
					{
						if ($message['AUTHOR_ID'] != $userId)
						{
							$relation = \CIMMessenger::GetRelationById($id);
							if (!isset($relation[$userId]))
							{
								return false;
							}
						}
					}
					else if ($message['AUTHOR_ID'] == $userId)
					{
						if (!($type == IM_CHECK_UPDATE && in_array($connectorType, \Bitrix\ImOpenlines\Connector::getListCanUpdateOwnMessage()))
							&& !($type == IM_CHECK_DELETE && in_array($connectorType, \Bitrix\ImOpenlines\Connector::getListCanDeleteOwnMessage())))
						{
							return false;
						}
					}
					else
					{
						if ($type == IM_CHECK_UPDATE || !in_array($connectorType, \Bitrix\ImOpenlines\Connector::getListCanDeleteMessage()))
						{
							return false;
						}
					}
				}
				else if (!$skipUserCheck && $message['AUTHOR_ID'] != $userId)
				{
					return false;
				}
			}

			$result = $message;
		}

		return $result;
	}

	public static function Update($id, $text, $urlPreview = true, $editFlag = true, $userId = null, $byEvent = false)
	{
		$updateFlags = Array(
			'ID' => $id,
			'TEXT' => $text,
			'URL_PREVIEW' => $urlPreview,
			'EDIT_FLAG' => $editFlag,
			'USER_ID' => $userId,
			'BY_EVENT' => $byEvent,
		);

		$text = trim(str_replace(Array('[BR]', '[br]'), "\n", $text));
		if (strlen($text) <= 0)
		{
			return self::Delete($id, $userId, false, $byEvent);
		}

		$message = self::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $id, $userId);
		if (!$message)
			return false;


		$dateText = Array();
		$dateTs = Array();

		if ($urlPreview)
		{
			$results = \Bitrix\Im\Text::getDateConverterParams($text);
			foreach ($results as $result)
			{
				$dateText[] = $result->getText();
				$dateTs[] = $result->getDate()->getTimestamp();
			}
		}

		$arUpdate = Array('MESSAGE' => $text, 'MESSAGE_OUT' => '');
		$urlId = Array();
		$urlOnly = false;
		if ($urlPreview)
		{
			$link = new CIMMessageLink();
			$urlPrepare = $link->prepareInsert($text);
			if ($urlPrepare['RESULT'])
			{
				$arUpdate['MESSAGE_OUT'] = $text;
				$arUpdate['MESSAGE'] = $urlPrepare['MESSAGE'];
				$urlId = $urlPrepare['URL_ID'];
				if ($urlPrepare['MESSAGE_IS_LINK'])
				{
					$urlOnly = true;
				}
			}
		}

		IM\Model\MessageTable::update($message['ID'], $arUpdate);

		CIMMessageParam::Set($message['ID'], Array('IS_EDITED' => $editFlag?'Y':'N', 'URL_ID' => $urlId, 'URL_ONLY' => $urlOnly?'Y':'N', 'DATE_TEXT' => $dateText, 'DATE_TS' => $dateTs));

		$arFields = $message;
		$arFields['MESSAGE'] = $text;
		$arFields['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();

		$pullMessage = \Bitrix\Im\Text::parse($arFields['MESSAGE']);

		$relations = CIMMessenger::GetRelationById($message['ID']);

		$arPullMessage = Array(
			'id' => (int)$arFields['ID'],
			'type' => $arFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE? 'private': 'chat',
			'text' => $pullMessage
		);
		$arBotInChat = Array();

		if ($message['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$arFields['FROM_USER_ID'] = $arFields['AUTHOR_ID'];
			foreach ($relations as $rel)
			{
				if ($rel['USER_ID'] != $arFields['AUTHOR_ID'])
					$arFields['TO_USER_ID'] = $rel['USER_ID'];
			}

			$arPullMessage['fromUserId'] = (int)$arFields['FROM_USER_ID'];
			$arPullMessage['toUserId'] = (int)$arFields['TO_USER_ID'];
		}
		else
		{
			$arPullMessage['chatId'] = (int)$arFields['CHAT_ID'];
			$arPullMessage['senderId'] = (int)$arFields['AUTHOR_ID'];

			foreach ($relations as $relation)
			{
				if ($message['CHAT_ENTITY_TYPE'] == 'LINES')
				{
					if ($relation["EXTERNAL_AUTH_ID"] == 'imconnector')
					{
						unset($relations[$relation["USER_ID"]]);
						continue;
					}
				}
				if ($relation["EXTERNAL_AUTH_ID"] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID)
				{
					$arBotInChat[$relation["USER_ID"]] = $relation["USER_ID"];
					unset($relations[$relation["USER_ID"]]);
					continue;
				}
			}
		}

		$arMessages[$message['ID']] = Array();

		$params = CIMMessageParam::Get(Array($message['ID']), false);
		$arMessages[$message['ID']]['params'] = $params[$message['ID']];

		$arDefault = CIMMessageParam::GetDefault();
		if (!isset($arMessages[$message['ID']]['params']['IS_EDITED']))
		{
			$arMessages[$message['ID']]['params']['IS_EDITED'] = $arDefault['IS_EDITED'];
		}
		if (!isset($arMessages[$message['ID']]['params']['URL_ID']))
		{
			$arMessages[$message['ID']]['params']['URL_ID'] = $arDefault['URL_ID'];
		}
		if (!isset($arMessages[$message['ID']]['params']['ATTACH']))
		{
			$arMessages[$message['ID']]['params']['ATTACH'] = $arDefault['ATTACH'];
		}
		if (!isset($arMessages[$message['ID']]['params']['DATE_TEXT']))
		{
			$arMessages[$message['ID']]['params']['DATE_TEXT'] = $arDefault['DATE_TEXT'];
		}
		if (!isset($arMessages[$message['ID']]['params']['DATE_TS']))
		{
			$arMessages[$message['ID']]['params']['DATE_TS'] = $arDefault['DATE_TS'];
		}

		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);
		$arPullMessage['params'] = CIMMessenger::PrepareParamsForPull($arMessages[$message['ID']]['params']);

		\Bitrix\Pull\Event::add(array_keys($relations), Array(
			'module_id' => 'im',
			'command' => 'messageUpdate',
			'params' => $arPullMessage,
			'extra' => Array(
				'im_revision' => IM_REVISION,
				'im_revision_mobile' => IM_REVISION_MOBILE,
			),
		));
		foreach ($relations as $rel)
		{
			$obCache = new CPHPCache();
			$obCache->CleanDir('/bx/imc/recent'.self::GetCachePath($rel['USER_ID']));
		}

		if ($message['MESSAGE_TYPE'] == IM_MESSAGE_OPEN || $message['MESSAGE_TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			CPullWatch::AddToStack('IM_PUBLIC_'.$message['CHAT_ID'], Array(
				'module_id' => 'im',
				'command' => 'messageUpdate',
				'params' => $arPullMessage,
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				)
			));
		}
		if ($message['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
		{
			$arFields['BOT_IN_CHAT'] = $arBotInChat;
		}

		foreach(GetModuleEvents("im", "OnAfterMessagesUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(intval($id), $arFields, $updateFlags));

		\Bitrix\Im\Bot::onMessageUpdate(intval($id), $arFields);

		return true;
	}

	public static function Share($id, $type, $date = '')
	{
		$chat = new CIMShare();

		if (\Bitrix\Im\User::getInstance($chat->user_id)->isExtranet())
		{
			return false;
		}

		if ($type == 'CHAT')
		{
			$chat->Chat($id);
		}
		else if ($type == 'TASK')
		{
			$chat->Task($id, $date);
		}
		else if ($type == 'POST')
		{
			$chat->Post($id);
		}
		else if ($type == 'CALEND')
		{
			$chat->Calendar($id, $date);
		}

		return true;
	}

	public static function Delete($id, $userId = null, $completeDelete = false, $byEvent = false)
	{
		$deleteFlags = Array(
			'ID' => $id,
			'USER_ID' => $userId,
			'COMPLETE_DELETE' => $completeDelete,
			'BY_EVENT' => $byEvent
		);

		$message = self::CheckPossibilityUpdateMessage(IM_CHECK_DELETE, $id, $userId);
		if (!$message)
			return false;

		$deleteFlags['COMPLETE_DELETE'] = $completeDelete = $message['CHAT_ID'] == CIMChat::GetGeneralChatId() && self::IsAdmin()? true: $completeDelete;

		$params = CIMMessageParam::Get($message['ID']);
		if (!empty($params['FILE_ID']))
		{
			foreach ($params['FILE_ID'] as $fileId)
			{
				CIMDisk::DeleteFile($message['CHAT_ID'], $fileId);
			}
		}

		$date = FormatDate("FULL", $message['DATE_CREATE']+CTimeZone::GetOffset());
		if (!$completeDelete)
		{
			IM\Model\MessageTable::update($message['ID'], array(
				"MESSAGE" => GetMessage('IM_MESSAGE_DELETED'),
				"MESSAGE_OUT" => GetMessage('IM_MESSAGE_DELETED_OUT', Array('#DATE#' => $date)),
			));
			CIMMessageParam::Set($message['ID'], Array('IS_DELETED' => 'Y', 'URL_ID' => Array(), 'FILE_ID' => Array(), 'KEYBOARD' => 'N', 'ATTACH' => Array()));
		}

		$arFields = $message;
		$arFields['MESSAGE'] = GetMessage('IM_MESSAGE_DELETED_OUT', Array('#DATE#' => $date));
		$arFields['DATE_MODIFY'] = time()+CTimeZone::GetOffset();

		$relations = CIMMessenger::GetRelationById($message['ID']);
		$arPullMessage = Array(
			'id' => (int)$arFields['ID'],
			'type' => $arFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE? 'private': 'chat',
			'date' => $arFields['DATE_MODIFY'],
			'text' => GetMessage('IM_MESSAGE_DELETED')
		);
		$arBotInChat = Array();
		if ($message['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$arFields['FROM_USER_ID'] = $arFields['AUTHOR_ID'];
			foreach ($relations as $rel)
			{
				if ($rel['USER_ID'] != $arFields['AUTHOR_ID'])
					$arFields['TO_USER_ID'] = $rel['USER_ID'];
			}

			$arPullMessage['fromUserId'] = (int)$arFields['FROM_USER_ID'];
			$arPullMessage['toUserId'] = (int)$arFields['TO_USER_ID'];
		}
		else
		{
			$arPullMessage['chatId'] = (int)$arFields['CHAT_ID'];
			$arPullMessage['senderId'] = (int)$arFields['AUTHOR_ID'];

			foreach ($relations as $relation)
			{
				if ($message['CHAT_ENTITY_TYPE'] == 'LINES')
				{
					if ($relation["EXTERNAL_AUTH_ID"] == 'imconnector')
					{
						unset($relations[$relation["USER_ID"]]);
						continue;
					}
				}
				if ($relation["EXTERNAL_AUTH_ID"] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID)
				{
					$arBotInChat[$relation["USER_ID"]] = $relation["USER_ID"];
					unset($relations[$relation["USER_ID"]]);
					continue;
				}
			}
		}

		if ($message['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
		{
			$arFields['BOT_IN_CHAT'] = $arBotInChat;
		}

		\Bitrix\Im\Bot::onMessageDelete(intval($id), $arFields);

		if ($completeDelete)
		{
			IM\Model\ChatTable::update($message['CHAT_ID'], Array(
				'MESSAGE_COUNT' => new \Bitrix\Main\DB\SqlExpression('?# - 1', 'MESSAGE_COUNT'),
			));

			if ($message['CHAT_PARENT_MID'])
			{
				$chatData = IM\Model\ChatTable::getById($message['CHAT_ID'])->fetch();
				CIMMessageParam::set($chatData['PARENT_MID'], Array(
					'CHAT_MESSAGE' => $chatData['MESSAGE_COUNT'],
					'CHAT_LAST_DATE' => new \Bitrix\Main\Type\DateTime()
				));
				CIMMessageParam::SendPull($chatData['PARENT_MID'], Array('CHAT_MESSAGE', 'CHAT_LAST_DATE'));
			}

			$completeDelete = true;
			CIMMessageParam::DeleteAll($message['ID']);
			\Bitrix\Im\Model\MessageTable::delete($message['ID']);

			$relationCounters = \Bitrix\Im\Chat::getRelation($message['CHAT_ID'], Array(
				'SELECT' => Array('ID', 'USER_ID'),
				'REAL_COUNTERS' => 'Y',
				'USER_DATA' => 'Y',
				'SKIP_RELATION_WITH_UNMODIFIED_COUNTERS' => 'Y'
			));
			foreach ($relationCounters as $relation)
			{
				if (
					$relation['USER_DATA']["EXTERNAL_AUTH_ID"] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID
					|| $relation['USER_DATA']['ACTIVE'] == 'N'
				)
				{
					continue;
				}
				\Bitrix\Im\Model\RelationTable::update($relation['ID'], Array('COUNTER' => $relation['COUNTER']));
				\Bitrix\Im\Counter::clearCache($relation['USER_ID']);
			}

			$result = \Bitrix\Im\Model\RecentTable::getList(Array('filter' => Array('=ITEM_MID' => $message['ID'])))->fetchAll();
			if (!empty($result))
			{
				$message = \Bitrix\Im\Model\MessageTable::getList(Array(
					'filter' => Array('=CHAT_ID' => $message['CHAT_ID']),
					'limit' => 1,
					'order' => Array('ID' => 'DESC')
				))->fetch();
				if ($message)
				{
					foreach ($result as $recent)
					{
						\Bitrix\Im\Model\RecentTable::update(Array(
							'USER_ID' => $recent['USER_ID'],
							'ITEM_TYPE' => $recent['ITEM_TYPE'],
							'ITEM_ID' => $recent['ITEM_ID'],
						), Array('ITEM_MID' => $message['ID']));

						$obCache = new CPHPCache();
						$obCache->CleanDir('/bx/imc/recent'.CIMMessenger::GetCachePath($recent['USER_ID']));

						if ($recent['ITEM_TYPE'] == 'P')
							CIMMessenger::SpeedFileDelete($recent['USER_ID'], IM_SPEED_GROUP);
						else
							CIMMessenger::SpeedFileDelete($recent['USER_ID'], IM_SPEED_MESSAGE);
					}
				}
			}
		}

		foreach ($relations as $rel)
		{
			$obCache = new CPHPCache();
			$obCache->CleanDir('/bx/imc/recent'.self::GetCachePath($rel['USER_ID']));
		}

		\Bitrix\Pull\Event::add(array_keys($relations), Array(
			'module_id' => 'im',
			'command' => ($completeDelete? 'messageDeleteComplete': 'messageDelete'),
			'params' => $arPullMessage,
			'push' => $completeDelete? Array('badge' => 'Y'): Array(),
			'extra' => Array(
				'im_revision' => IM_REVISION,
				'im_revision_mobile' => IM_REVISION_MOBILE,
			),
		));

		if ($message['MESSAGE_TYPE'] == IM_MESSAGE_OPEN || $message['MESSAGE_TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			CPullWatch::AddToStack('IM_PUBLIC_'.$message['CHAT_ID'], Array(
				'module_id' => 'im',
				'command' => ($completeDelete? 'messageDeleteComplete': 'messageDelete'),
				'params' => $arPullMessage,
				'push' => $completeDelete? Array('badge' => 'Y'): Array(),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				)
			));
		}

		foreach(GetModuleEvents("im", "OnAfterMessagesDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(intval($id), $arFields, $deleteFlags));

		return true;
	}

	public static function LinesSessionVote($dialogId, $messageId, $action, $userId = null)
	{
		global $USER;
		$userId = is_null($userId)? $USER->GetId(): intval($userId);
		if ($userId <= 0)
			return false;

		if (strlen($dialogId) <= 0)
			return false;

		if (!\Bitrix\Im\User::getInstance($userId)->isConnector() && !\Bitrix\Im\User::getInstance($dialogId)->isBot())
			return false;

		$messageId = intval($messageId);
		$action = $action == 'dislike'? 'dislike': 'like';

		$message = self::GetById($messageId);
		if (!$message || intval($message['PARAMS']['IMOL_VOTE']) <= 0)
			return false;
		if ($message['DATE_CREATE']+86400 < time())
			return false;
		$relations = CIMMessenger::GetRelationById($messageId);

		$result = IM\Model\ChatTable::getList(Array(
			'filter'=>Array(
				'=ID' => $message['CHAT_ID']
			)
		));
		$chat = $result->fetch();
		if (!isset($relations[$userId]))
			return false;

		CIMMessageParam::Set($messageId, Array('IMOL_VOTE' => $action));
		CIMMessageParam::SendPull($messageId, Array('IMOL_VOTE'));

		if ($chat['ENTITY_TYPE'] == 'LIVECHAT')
		{
			CIMMessageParam::Set($message['PARAMS']['CONNECTOR_MID'][0], Array('IMOL_VOTE' => $action));
			CIMMessageParam::SendPull($message['PARAMS']['CONNECTOR_MID'][0], Array('IMOL_VOTE'));

			if (CModule::IncludeModule('imopenlines'))
			{
				\Bitrix\ImOpenlines\Session::voteAsUser(intval($message['PARAMS']['IMOL_VOTE']), $action);
			}
		}
		else
		{
			$chat = Array();
			$relations = Array();
		}

		foreach(GetModuleEvents("im", "OnSessionVote", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE_ID' => $messageId,
				'SESSION_ID' => $message['PARAMS']['IMOL_VOTE'],
				'MESSAGE' => $message,
				'ACTION' => $action,
				'CHAT' => $chat,
				'RELATION' => $relations,
				'USER_ID' => $userId
			)));
		}

		return true;
	}

	public static function Like($id, $action = 'auto', $userId = null, $byEvent = false)
	{
		if (!CModule::IncludeModule('pull'))
			return false;

		global $USER;
		$userId = is_null($userId)? $USER->GetId(): intval($userId);
		if ($userId <= 0)
			return false;

		$action = in_array($action, Array('plus', 'minus'))? $action: 'auto';

		$message = self::GetById($id);
		if (!$message)
			return false;

		$relations = CIMMessenger::GetRelationById($id);

		$result = IM\Model\ChatTable::getList(Array(
			'filter'=>Array(
				'=ID' => $message['CHAT_ID']
			)
		));
		$chat = $result->fetch();
		if ($chat['ENTITY_TYPE'] != 'LIVECHAT')
		{
			if (!isset($relations[$userId]))
				return false;
		}

		if (!$byEvent && $chat['ENTITY_TYPE'] == 'LINES')
		{
			list($connectorType, $lineId, $chatId) = explode("|", $chat['ENTITY_ID']);
			if ($connectorType == "livechat")
			{
				foreach($message['PARAMS']['CONNECTOR_MID'] as $mid)
				{
					self::Like($mid, $action, $userId, true);
				}
			}
		}
		else if (!$byEvent && $chat['ENTITY_TYPE'] == 'LIVECHAT')
		{
			foreach($message['PARAMS']['CONNECTOR_MID'] as $mid)
			{
				self::Like($mid, $action, $userId, true);
			}
		}

		$isLike = false;
		if (isset($message['PARAMS']['LIKE']))
		{
			$isLike = in_array($userId, $message['PARAMS']['LIKE']);
		}

		if ($isLike && $action == 'plus')
		{
			return false;
		}
		else if (!$isLike && $action == 'minus')
		{
			return false;
		}

		$isLike = true;
		if (isset($message['PARAMS']['LIKE']))
		{
			$like = $message['PARAMS']['LIKE'];
			$selfLike = array_search($userId, $like);
			if ($selfLike !== false)
			{
				$isLike = false;
				unset($like[$selfLike]);
			}
			else
			{
				$like[] = $userId;
			}
		}
		else
		{
			$like = Array($userId);
		}

		sort($like);
		CIMMessageParam::Set($id, Array('LIKE' => $like));

		if ($message['AUTHOR_ID'] > 0 && $message['AUTHOR_ID'] != $userId && $isLike && $chat['ENTITY_TYPE'] != 'LIVECHAT')
		{
			$message['MESSAGE'] = self::PrepareMessageForPush($message);

			$isChat = $chat && strlen($chat['TITLE']) > 0;

			$dot = strlen($message['MESSAGE'])>=200? '...': '';
			$message['MESSAGE'] = substr($message['MESSAGE'], 0, 199).$dot;
			$message['MESSAGE'] = strlen($message['MESSAGE'])>0? $message['MESSAGE']: '-';

			$arMessageFields = array(
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"TO_USER_ID" => $message['AUTHOR_ID'],
				"FROM_USER_ID" => $userId,
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "im",
				"NOTIFY_EVENT" => "like",
				"NOTIFY_TAG" => "RATING|IM|".($isChat? 'G':'P')."|".($isChat? $chat['ID']: $userId)."|".$id,
				"NOTIFY_MESSAGE" => GetMessage($isChat? 'IM_MESSAGE_LIKE': 'IM_MESSAGE_LIKE_PRIVATE', Array(
					'#MESSAGE#' => $message['MESSAGE'],
					'#TITLE#' => $isChat? '[CHAT='.$chat['ID'].']'.$chat['TITLE'].'[/CHAT]': $chat['TITLE']
				)),
				"NOTIFY_MESSAGE_OUT" => GetMessage($isChat? 'IM_MESSAGE_LIKE': 'IM_MESSAGE_LIKE_PRIVATE', Array(
					'#MESSAGE#' => $message['MESSAGE'],
					'#TITLE#' => $chat['TITLE']
				)),
			);
			CIMNotify::Add($arMessageFields);
		}

		$pushUsers = $like;
		$pushUsers[] = $message['AUTHOR_ID'];
		$arPullMessage = Array(
			'id' => $id,
			'chatId' => $chat['ID'],
			'senderId' => $userId,
			'users' => $like
		);

		if ($chat['ENTITY_TYPE'] == 'LINES')
		{
			foreach ($relations as $rel)
			{
				if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
				{
					unset($relations[$rel["USER_ID"]]);
				}
			}
		}

		\Bitrix\Pull\Event::add(array_keys($relations), Array(
			'module_id' => 'im',
			'command' => 'messageLike',
			'params' => $arPullMessage,
			'extra' => Array(
				'im_revision' => IM_REVISION,
				'im_revision_mobile' => IM_REVISION_MOBILE,
			),
		));

		if ($chat['TYPE'] == IM_MESSAGE_OPEN || $chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], Array(
				'module_id' => 'im',
				'command' => 'messageLike',
				'params' => $arPullMessage,
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				)
			));
		}

		if ($chat['TYPE'] == IM_MESSAGE_PRIVATE)
		{
			$dialogId = $userId;
			foreach ($relations as $rel)
			{
				if ($rel['USER_ID'] != $userId)
				{
					$dialogId = $rel['USER_ID'];
				}
			}
		}
		else
		{
			$dialogId = 'chat'.$chat['ID'];
		}

		foreach(GetModuleEvents("im", "OnAfterMessagesLike", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(array(
				'DIALOG_ID' => $dialogId,
				'CHAT' => $chat,
				'MESSAGE' => $message,
				'ACTION' => $action,
				'USER_ID' => $userId,
				'BY_EVENT' => $byEvent
			)));
		}

		return $like;
	}

	public static function UrlAttachDelete($id, $attachId = false, $userId = null)
	{
		if (!CModule::IncludeModule('pull'))
			return false;

		global $USER;
		$userId = is_null($userId)? $USER->GetId(): intval($userId);
		if ($userId <= 0)
			return false;

		$relations = CIMMessenger::GetRelationById($id);
		if (!isset($relations[$userId]))
			return false;

		$newUrlId = Array();
		if ($attachId)
		{
			$urlId = CIMMessageParam::Get($id, 'URL_ID');
			foreach ($urlId as $value)
			{
				if ($value != $attachId)
				{
					$newUrlId[] = $value;
				}
			}
		}

		CIMMessageParam::Set($id, Array('URL_ID' => $newUrlId, 'URL_ONLY' => empty($newUrlId)? 'N': 'Y'));
		CIMMessageParam::SendPull($id, Array('URL_ID', 'ATTACH', 'URL_ONLY'));

		return true;
	}

	private static function CheckFields($arFields)
	{
		$aMsg = array();
		if(!is_set($arFields, "MESSAGE_TYPE") || !in_array($arFields["MESSAGE_TYPE"], Array(IM_MESSAGE_PRIVATE, IM_MESSAGE_CHAT, IM_MESSAGE_OPEN, IM_MESSAGE_SYSTEM, IM_MESSAGE_OPEN_LINE)))
		{
			$aMsg[] = array("id"=>"MESSAGE_TYPE", "text"=> GetMessage("IM_ERROR_MESSAGE_TYPE"));
		}
		else
		{
			if(in_array($arFields["MESSAGE_TYPE"], Array(IM_MESSAGE_CHAT, IM_MESSAGE_OPEN, IM_MESSAGE_OPEN_LINE)) && !isset($arFields['SYSTEM']) && (intval($arFields["TO_CHAT_ID"]) <= 0 && intval($arFields["FROM_USER_ID"]) <= 0))
				$aMsg[] = array("id"=>"TO_CHAT_ID", "text"=> GetMessage("IM_ERROR_MESSAGE_TO_FROM"));

			if($arFields["MESSAGE_TYPE"] == IM_MESSAGE_PRIVATE && !((intval($arFields["TO_USER_ID"]) > 0 || intval($arFields["TO_CHAT_ID"]) > 0) && intval($arFields["FROM_USER_ID"]) > 0))
				$aMsg[] = array("id"=>"FROM_USER_ID", "text"=> GetMessage("IM_ERROR_MESSAGE_TO_FROM"));

			if (is_set($arFields, "MESSAGE_DATE") && (!$GLOBALS['DB']->IsDate($arFields["MESSAGE_DATE"], false, LANG, "FULL")))
				$aMsg[] = array("id"=>"MESSAGE_DATE", "text"=> GetMessage("IM_ERROR_MESSAGE_DATE"));

			if(in_array($arFields["MESSAGE_TYPE"], Array(IM_MESSAGE_PRIVATE, IM_MESSAGE_SYSTEM)) && !(intval($arFields["TO_USER_ID"]) > 0 || intval($arFields["TO_CHAT_ID"]) > 0))
				$aMsg[] = array("id"=>"TO_USER_ID", "text"=> GetMessage("IM_ERROR_MESSAGE_TO"));

			if(is_set($arFields, "MESSAGE") && strlen(trim($arFields["MESSAGE"])) <= 0)
				$aMsg[] = array("id"=>"MESSAGE", "text"=> GetMessage("IM_ERROR_MESSAGE_TEXT"));
			else if(!is_set($arFields, "MESSAGE") && $arFields["MESSAGE_TYPE"] == IM_MESSAGE_SYSTEM && empty($arFields['PARAMS']['ATTACH']))
				$aMsg[] = array("id"=>"MESSAGE", "text"=> GetMessage("IM_ERROR_MESSAGE_TEXT"));
			else if(!is_set($arFields, "MESSAGE") && empty($arFields['PARAMS']['ATTACH']) && empty($arFields['PARAMS']['FILE_ID']))
				$aMsg[] = array("id"=>"MESSAGE", "text"=> GetMessage("IM_ERROR_MESSAGE_TEXT"));

			if($arFields["MESSAGE_TYPE"] == IM_MESSAGE_PRIVATE && is_set($arFields, "AUTHOR_ID") && intval($arFields["AUTHOR_ID"]) <= 0)
				$aMsg[] = array("id"=>"AUTHOR_ID", "text"=> GetMessage("IM_ERROR_MESSAGE_AUTHOR"));

			if(is_set($arFields, "IMPORT_ID") && intval($arFields["IMPORT_ID"]) <= 0)
				$aMsg[] = array("id"=>"IMPORT_ID", "text"=> GetMessage("IM_ERROR_IMPORT_ID"));

			if ($arFields["MESSAGE_TYPE"] == IM_MESSAGE_SYSTEM)
			{
				if(is_set($arFields, "NOTIFY_MODULE") && strlen(trim($arFields["NOTIFY_MODULE"])) <= 0)
					$aMsg[] = array("id"=>"NOTIFY_MODULE", "text"=> GetMessage("IM_ERROR_NOTIFY_MODULE"));

				if(is_set($arFields, "NOTIFY_EVENT") && strlen(trim($arFields["NOTIFY_EVENT"])) <= 0)
					$aMsg[] = array("id"=>"NOTIFY_EVENT", "text"=> GetMessage("IM_ERROR_NOTIFY_EVENT"));

				if(is_set($arFields, "NOTIFY_TYPE") && !in_array($arFields["NOTIFY_TYPE"], Array(IM_NOTIFY_CONFIRM, IM_NOTIFY_SYSTEM, IM_NOTIFY_FROM)))
					$aMsg[] = array("id"=>"NOTIFY_TYPE", "text"=> GetMessage("IM_ERROR_NOTIFY_TYPE"));

				if(is_set($arFields, "NOTIFY_TYPE") && $arFields["NOTIFY_TYPE"] == IM_NOTIFY_CONFIRM)
				{
					if(is_set($arFields, "NOTIFY_BUTTONS") && !is_array($arFields["NOTIFY_BUTTONS"]))
						$aMsg[] = array("id"=>"NOTIFY_BUTTONS", "text"=> GetMessage("IM_ERROR_NOTIFY_BUTTON"));
				}
				else if(is_set($arFields, "NOTIFY_TYPE") && $arFields["NOTIFY_TYPE"] == IM_NOTIFY_FROM)
				{
					if(!is_set($arFields, "FROM_USER_ID") || intval($arFields["FROM_USER_ID"]) <= 0)
						$aMsg[] = array("id"=>"FROM_USER_ID", "text"=> GetMessage("IM_ERROR_MESSAGE_FROM"));
				}
			}
		}

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	public static function GetById($ID, $params = Array())
	{
		global $DB;

		$ID = intval($ID);

		$strSql = "
			SELECT
				DISTINCT M.*,
				".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
				C.TYPE MESSAGE_TYPE,
				C.AUTHOR_ID CHAT_AUTHOR_ID,
				C.ENTITY_TYPE CHAT_ENTITY_TYPE,
				C.ENTITY_ID CHAT_ENTITY_ID,
				C.PARENT_ID CHAT_PARENT_ID,
				C.PARENT_MID CHAT_PARENT_MID,
				C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
				C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
				C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3
			FROM b_im_message M
			LEFT JOIN b_im_chat C ON M.CHAT_ID = C.ID
			WHERE M.ID = ".$ID;
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$param = CIMMessageParam::Get($arRes['ID']);
			$arRes['PARAMS'] = $param? $param: Array();
		}
		if ($arRes && $params['WITH_FILES'] == 'Y')
		{
			$arFiles = Array();
			if (isset($arRes['PARAMS']['FILE_ID']))
			{
				foreach ($arRes['PARAMS']['FILE_ID'] as $fileId)
				{
					$arFiles[$fileId] = $fileId;
				}
			}
			$arRes['FILES'] = CIMDisk::GetFiles($arRes['CHAT_ID'], $arFiles, false);
		}

		return $arRes;
	}

	public static function GetRelationById($ID)
	{
		global $DB;

		$ID = intval($ID);
		$arResult = Array();

		$strSql = "
			SELECT
				R.USER_ID, U.EXTERNAL_AUTH_ID
			FROM b_im_message M
			LEFT JOIN b_im_relation R ON M.CHAT_ID = R.CHAT_ID
			LEFT JOIN b_user U ON U.ID = R.USER_ID
			WHERE M.ID = ".$ID;
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($arRes = $dbRes->Fetch())
			$arResult[$arRes['USER_ID']] = $arRes;

		return $arResult;
	}

	public static function CheckXmppStatusOnline()
	{
		If (IsModuleInstalled('xmpp'))
		{
			$LastActivityDate = CUserOptions::GetOption('xmpp', 'LastActivityDate');
			if (intval($LastActivityDate)+60 > time())
				return true;
		}
		return false;
	}

	public static function CheckEnableOpenChat()
	{
		return COption::GetOptionString('im', 'open_chat_enable');
	}

	public static function CheckNetwork()
	{
		return COption::GetOptionString('bitrix24', 'network', 'N') == 'Y';
	}

	public static function CheckNetwork2()
	{
		if (!CModule::IncludeModule('socialservices'))
			return false;

		$network = new \Bitrix\Socialservices\Network();
		return $network->isEnabled();
	}

	public static function CheckInstallDesktop()
	{
		$LastActivityDate = CUserOptions::GetOption('im', 'DesktopLastActivityDate', -1);
		if (intval($LastActivityDate) >= 0)
			return true;
		else
			return false;
	}

	public static function EnableInVersion($version)
	{
		$version = intval($version);
		$currentVersion = intval(CUserOptions::GetOption('im', 'DesktopVersionApi', 0));

		return $currentVersion >= $version;
	}

	public static function SetDesktopVersion($version)
	{
		global $USER;

		$version = intval($version);
		$userId = intval($USER->GetId());
		if ($userId <= 0)
			return false;

		CUserOptions::SetOption('im', 'DesktopVersionApi', $version, false, $userId);

		return $version;
	}

	public static function GetDesktopVersion()
	{
		$version = CUserOptions::GetOption('im', 'DesktopVersionApi', 0);

		return $version;
	}

	public static function CheckPhoneStatus()
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant') || !\Bitrix\Main\Loader::includeModule('pull'))
			return false;

		return CPullOptions::GetNginxStatus() && (!IsModuleInstalled('extranet') || CModule::IncludeModule('extranet') && CExtranet::IsIntranetUser());
	}

	public static function CanUserCallCrmNumber()
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return false;

		$userPermissions = \Bitrix\Voximplant\Security\Permissions::createWithCurrentUser();
		return $userPermissions->canPerform(
			\Bitrix\Voximplant\Security\Permissions::ENTITY_CALL,
			\Bitrix\Voximplant\Security\Permissions::ACTION_PERFORM,
			\Bitrix\Voximplant\Security\Permissions::PERMISSION_CALL_CRM
		);
	}

	public static function CanUserCallUserNumber()
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return false;

		$userPermissions = \Bitrix\Voximplant\Security\Permissions::createWithCurrentUser();
		return $userPermissions->canPerform(
			\Bitrix\Voximplant\Security\Permissions::ENTITY_CALL,
			\Bitrix\Voximplant\Security\Permissions::ACTION_PERFORM,
			\Bitrix\Voximplant\Security\Permissions::PERMISSION_CALL_USERS
		);
	}

	public static function CanUserCallAnyNumber()
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return false;

		$userPermissions = \Bitrix\Voximplant\Security\Permissions::createWithCurrentUser();
		return $userPermissions->canPerform(
			\Bitrix\Voximplant\Security\Permissions::ENTITY_CALL,
			\Bitrix\Voximplant\Security\Permissions::ACTION_PERFORM,
			\Bitrix\Voximplant\Security\Permissions::PERMISSION_ANY
		);
	}

	public static function CanUserPerformCalls()
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return false;

		$userPermissions = \Bitrix\Voximplant\Security\Permissions::createWithCurrentUser();
		return $userPermissions->canPerform(\Bitrix\Voximplant\Security\Permissions::ENTITY_CALL, \Bitrix\Voximplant\Security\Permissions::ACTION_PERFORM);
	}

	public static function CanInterceptCall()
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return false;

		return \Bitrix\Voximplant\Limits::canInterceptCall();
	}


	public static function GetCallCardRestApps()
	{
		if(!\Bitrix\Main\Loader::includeModule('rest'))
			return array();

		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return array();

		$result = array();
		if(!defined('Bitrix\Voximplant\Rest\Helper::PLACEMENT_CALL_CARD'))
			return array();

		$placementId = Bitrix\Voximplant\Rest\Helper::PLACEMENT_CALL_CARD;
		$cursor = \Bitrix\Rest\PlacementTable::getHandlersList($placementId);
		foreach($cursor as $row)
		{
			$result[] = array(
				'id' => $row['ID'],
				'name' => $row['TITLE'] ?: $row['APP_NAME']
			);
		}
		return $result;
	}

	public static function GetTelephonyLines()
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return array();

		return CVoxImplantConfig::GetLines(true, true);
	}

	public static function GetTelephonyAvailableLines($userId = null)
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return array();

		if(is_null($userId))
			$userId = self::GetCurrentUserId();

		return \CVoxImplantUser::getAllowedLines($userId);
	}

	public static function GetDefaultTelephonyLine($userId = null)
	{
		if(!\Bitrix\Main\Loader::includeModule('voximplant'))
			return array();

		if(is_null($userId))
			$userId = self::GetCurrentUserId();

		return CVoxImplantUser::getUserOutgoingLine($userId);
	}

	public static function CheckDesktopStatusOnline($userId = null)
	{
		global $USER;

		if (is_null($userId))
			$userId = $USER->GetId();

		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		$maxDate = 120;
		if (CModule::IncludeModule('pull') && CPullOptions::GetNginxStatus())
			$maxDate = self::GetSessionLifeTime();

		$LastActivityDate = CUserOptions::GetOption('im', 'DesktopLastActivityDate', 0, $userId);
		if (intval($LastActivityDate)+$maxDate+60 > time())
			return true;
		else
			return false;
	}

	public static function GetDesktopStatusOnline($userId = null)
	{
		global $USER;

		if (is_null($userId))
			$userId = $USER->GetId();

		$userId = intval($userId);
		if ($userId <= 0)
			return 0;

		return CUserOptions::GetOption('im', 'DesktopLastActivityDate', 0, $userId);
	}

	public static function SetDesktopStatusOnline($userId = null, $cache = true)
	{
		global $USER;

		if (is_null($userId))
			$userId = $USER->GetId();

		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		if ($cache && $userId == $USER->GetId())
		{
			if (
				isset($_SESSION['SESS_AUTH']['SET_DESKTOP_ACTIVITY'])
				&& intval($_SESSION['SESS_AUTH']['SET_DESKTOP_ACTIVITY'])+300 > time()
			)
			{
				return false;
			}

			$_SESSION['SESS_AUTH']['SET_DESKTOP_ACTIVITY'] = time();
		}

		$time = time();
		CUserOptions::SetOption('im', 'DesktopLastActivityDate', $time, false, $userId);

		if ($cache && $userId == $USER->GetId())
		{
			if (
				isset($_SESSION['SESS_AUTH']['SET_DESKTOP_ACTIVITY_PULL'])
				&& intval($_SESSION['SESS_AUTH']['SET_DESKTOP_ACTIVITY_PULL'])+self::GetSessionLifeTime() > time()
			)
			{
				return false;
			}

			$_SESSION['SESS_AUTH']['SET_DESKTOP_ACTIVITY_PULL'] = time();

			if (CModule::IncludeModule("pull"))
			{
				\Bitrix\Pull\Event::add($userId, Array(
					'module_id' => 'im',
					'expiry' => 3600,
					'command' => 'desktopOnline',
					'params' => Array(),
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				));
			}
		}


		return $time;
	}

	public static function SetDesktopStatusOffline($userId = null)
	{
		global $USER;

		if (is_null($userId))
			$userId = $USER->GetId();

		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		CUserOptions::SetOption('im', 'DesktopLastActivityDate', 0, false, $userId);

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($userId, Array(
				'module_id' => 'im',
				'expiry' => 3600,
				'command' => 'desktopOffline',
				'params' => Array(),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}

	public static function GetSettings($userId = false)
	{
		$arSettings = CIMSettings::Get($userId);
		return $arSettings['settings'];
	}

	public static function GetFormatFilesMessageOut($files)
	{
		if (!is_array($files) || count($files) <= 0)
			return false;

		$messageFiles = '';
		$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name", ""));
		foreach ($files as $fileId => $fileData)
		{
			if ($fileData['status'] == 'done')
			{
				$fileElement = $fileData['name'].' ('.CFile::FormatSize($fileData['size']).")\n".
								GetMessage('IM_MESSAGE_FILE_DOWN').' '.$serverName.$fileData['urlDownload']['default']."\n";
				$messageFiles = strlen($messageFiles)>0? $messageFiles."\n".$fileElement: $fileElement;
			}
		}

		return $messageFiles;
	}

	public static function GetSessionLifeTime()
	{
		global $USER;

		$sessTimeout = CUser::GetSecondsForLimitOnline();
		if (is_object($USER))
		{
			$arPolicy = $USER->GetSecurityPolicy();
			if($arPolicy["SESSION_TIMEOUT"] > 0)
				$sessTimeout = min($arPolicy["SESSION_TIMEOUT"]*60, $sessTimeout);
		}
		$sessTimeout = intval($sessTimeout);
		if ($sessTimeout <= 120)
		{
			$sessTimeout = 100;
		}

		return intval($sessTimeout);
	}

	public static function GetUnreadCounter($userId)
	{
		$count = 0;
		$userId = intval($userId);
		if ($userId <= 0)
			return $count;

		global $DB;

		$strSql ="
			SELECT M.ID, M.NOTIFY_TYPE, M.NOTIFY_TAG
			FROM b_im_message M
			INNER JOIN b_im_relation R1 ON M.ID > R1.LAST_ID AND M.CHAT_ID = R1.CHAT_ID AND R1.USER_ID != M.AUTHOR_ID
			WHERE R1.USER_ID = ".$userId."  AND R1.STATUS < ".IM_STATUS_READ."
		";

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$arGroupNotify = Array();
		while ($arRes = $dbRes->Fetch())
		{
			if ($arRes['NOTIFY_TYPE'] == 2 && $arRes['NOTIFY_TAG'] != '')
			{
				if (!isset($arGroupNotify[$arRes['NOTIFY_TAG']]))
				{
					$arGroupNotify[$arRes['NOTIFY_TAG']] = true;
					$count++;
				}
			}
			else
				$count++;
		}

		return $count;
	}

	public static function GetMessageCounter($userId, $arMessages = Array())
	{
		$count = 0;
		if (isset($arMessages['message']))
		{
			foreach ($arMessages['message'] as $value)
				$count += isset($value['counter'])? $value['counter']: 1;
		}
		else
		{
			$count = CIMMessenger::SpeedFileGet($userId, IM_SPEED_MESSAGE);
		}

		return intval($count);
	}

	private static function GetReasonForMessageSendError($type = IM_MESSAGE_PRIVATE, $reason = '')
	{
		if (!empty($reason))
		{
			$CBXSanitizer = new CBXSanitizer;
			$CBXSanitizer->AddTags(array(
				'a' => array('href','style', 'target'),
				'b' => array(), 'u' => array(),
				'i' => array(), 'br' => array(),
				'span' => array('style'),
			));
			$reason = $CBXSanitizer->SanitizeHtml($reason);
		}
		else
		{
			if ($type == IM_MESSAGE_PRIVATE)
			{
				$reason = GetMessage("IM_ERROR_MESSAGE_CANCELED");
			}
			else if ($type == IM_MESSAGE_SYSTEM)
			{
				$reason = GetMessage("IM_ERROR_NOTIFY_CANCELED");
			}
			else
			{
				$reason = GetMessage("IM_ERROR_GROUP_CANCELED");
			}
		}

		return $reason;
	}

	public static function SpeedFileCreate($userID, $value, $type = IM_SPEED_MESSAGE)
	{
		global $CACHE_MANAGER;

		$userID = IntVal($userID);
		if ($userID <= 0 || !in_array($type, Array(IM_SPEED_NOTIFY, IM_SPEED_MESSAGE, IM_SPEED_GROUP)))
			return false;

		$CACHE_MANAGER->Clean("im_csf_v2_".$type.'_'.$userID);
		$CACHE_MANAGER->Read(86400*30, "im_csf_v2_".$type.'_'.$userID);
		$CACHE_MANAGER->Set("im_csf_v2_".$type.'_'.$userID, $value);
	}

	public static function SpeedFileDelete($userID, $type = IM_SPEED_MESSAGE)
	{
		global $CACHE_MANAGER;

		$userID = IntVal($userID);
		if ($userID <= 0 || !in_array($type, Array(IM_SPEED_NOTIFY, IM_SPEED_MESSAGE, IM_SPEED_GROUP)))
			return false;

		$CACHE_MANAGER->Clean("im_csf_v2_".$type.'_'.$userID);
	}

	public static function SpeedFileExists($userID, $type = IM_SPEED_MESSAGE)
	{
		global $CACHE_MANAGER;

		$userID = IntVal($userID);
		if ($userID <= 0 || !in_array($type, Array(IM_SPEED_NOTIFY, IM_SPEED_MESSAGE, IM_SPEED_GROUP)))
			return false;

		$result = $CACHE_MANAGER->Read(86400*30, "im_csf_v2_".$type.'_'.$userID);
		if ($result)
			$result = $CACHE_MANAGER->Get("im_csf_v2_".$type.'_'.$userID) === false? false: true;

		return $result;
	}

	public static function SpeedFileGet($userID, $type = IM_SPEED_MESSAGE)
	{
		global $CACHE_MANAGER;

		$userID = IntVal($userID);
		if ($userID <= 0 || !in_array($type, Array(IM_SPEED_NOTIFY, IM_SPEED_MESSAGE, IM_SPEED_GROUP)))
			return false;

		$CACHE_MANAGER->Read(86400*30, "im_csf_v2_".$type.'_'.$userID);
		return $CACHE_MANAGER->Get("im_csf_v2_".$type.'_'.$userID);
	}

	public static function GetTemplateJS($arParams, $arTemplate)
	{
		global $USER;

		$ppStatus = 'false';
		$ppServerStatus = 'false';
		$updateStateInterval = 'auto';
		if (CModule::IncludeModule("pull"))
		{
			$ppStatus = CPullOptions::ModuleEnable()? 'true': 'false';
			$ppServerStatus = CPullOptions::GetNginxStatus()? 'true': 'false';

			$updateStateInterval = CPullOptions::GetNginxStatus()? self::GetSessionLifeTime(): 80;
			if ($updateStateInterval > 100)
			{
				if ($updateStateInterval > 3600)
					$updateStateInterval = 3600;

				if (in_array($arTemplate["CONTEXT"], Array("POPUP-FULLSCREEN", "MESSENGER")))
					$updateStateInterval = $updateStateInterval-60;
				else
					$updateStateInterval = intval($updateStateInterval/2)-10;
			}
		}

		$diskStatus = CIMDisk::Enabled();
		$diskExternalLinkStatus = CIMDisk::EnabledExternalLink();

		$phoneCanPerformCalls = false;
		$phoneSipAvailable = false;
		$phoneDeviceActive = false;
		$phoneCanCallUserNumber = false;
		$phoneEnabled = false;
		$chatExtendShowHistory = \COption::GetOptionInt('im', 'chat_extend_show_history');
		$callServerEnabled = \COption::GetOptionString('im', 'call_server');
		$phoneCanInterceptCall = self::CanInterceptCall();

		if(!$phoneCanInterceptCall && \Bitrix\Main\Loader::includeModule('voximplant'))
		{
			\Bitrix\Voximplant\Ui\Helper::initLicensePopups();
		}

		if ($arTemplate['INIT'] == 'Y')
		{
			$phoneEnabled = self::CheckPhoneStatus();
			if ($phoneEnabled && CModule::IncludeModule('voximplant'))
			{
				$phoneCanPerformCalls = self::CanUserPerformCalls();
				$phoneSipAvailable = CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP);
				$phoneDeviceActive = CVoxImplantUser::GetPhoneActive($USER->GetId());
				$phoneCanCallUserNumber = self::CanUserCallUserNumber();
			}
		}

		$crmPath = Array();
		$olConfig = Array();
		$businessUsers = false;
		if (CModule::IncludeModule('imopenlines'))
		{
			$crmPath['LEAD'] = \Bitrix\ImOpenLines\Crm::getLink('LEAD');
			$crmPath['CONTACT'] = \Bitrix\ImOpenLines\Crm::getLink('CONTACT');
			$crmPath['COMPANY'] = \Bitrix\ImOpenLines\Crm::getLink('COMPANY');
			$crmPath['DEAL'] = \Bitrix\ImOpenLines\Crm::getLink('DEAL');

			$businessUsers = \Bitrix\Imopenlines\Limit::getLicenseUsersLimit();

			$olConfig['canDeleteMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanDeleteMessage());
			$olConfig['canDeleteOwnMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanDeleteOwnMessage());
			$olConfig['canUpdateOwnMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanUpdateOwnMessage());

			$olConfig['queue'] = Array();
			foreach (\Bitrix\ImOpenLines\Config::getQueueList($USER->GetID()) as $config)
			{
				$olConfig['queue'][] = array_change_key_case($config, CASE_LOWER);
			}
		}

		$pathToIm = isset($arTemplate['PATH_TO_IM']) ? $arTemplate['PATH_TO_IM'] : '';
		$pathToCall = isset($arTemplate['PATH_TO_CALL']) ? $arTemplate['PATH_TO_CALL'] : '';
		$pathToFile = isset($arTemplate['PATH_TO_FILE']) ? $arTemplate['PATH_TO_FILE'] : '';
		$pathToLf = isset($arTemplate['PATH_TO_LF']) ? $arTemplate['PATH_TO_LF'] : '/';

		$userColor = isset($arTemplate['CONTACT_LIST']['users'][$USER->GetID()]['color']) ? $arTemplate['CONTACT_LIST']['users'][$USER->GetID()]['color']: '';

		$sJS = "
			BX.ready(function() {
				BXIM = new BX.IM(BX('bx-notifier-panel'), {
					'init': ".($arTemplate['INIT'] == 'Y'? 'true': 'false').",

					'context': '".$arTemplate["CONTEXT"]."',
					'design': '".$arTemplate["DESIGN"]."',
					'colors': ".(IM\Color::isEnabled()? \Bitrix\Im\Common::objectEncode(IM\Color::getSafeColorNames()): 'false').",
					'colorsHex': ".\Bitrix\Im\Common::objectEncode(IM\Color::getSafeColors()).",
					'mailCount': ".$arTemplate["MAIL_COUNTER"].",
					'notifyCount': ".$arTemplate["NOTIFY_COUNTER"].",
					'messageCount': ".$arTemplate["MESSAGE_COUNTER"].",
					'counters': ".(empty($arTemplate['COUNTERS'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['COUNTERS'])).",
					'ppStatus': ".$ppStatus.",
					'ppServerStatus': ".$ppServerStatus.",
					'updateStateInterval': '".$updateStateInterval."',
					'openChatEnable': ".(CIMMessenger::CheckEnableOpenChat()? 'true': 'false').",
					'xmppStatus': ".(CIMMessenger::CheckXmppStatusOnline()? 'true': 'false').",
					'isAdmin': ".(self::IsAdmin()? 'true': 'false').",
					'bitrixNetwork': ".(CIMMessenger::CheckNetwork()? 'true': 'false').",
					'bitrixNetwork2': ".(CIMMessenger::CheckNetwork2()? 'true': 'false').",
					'bitrix24': ".(IsModuleInstalled('bitrix24')? 'true': 'false').",
					'bitrix24net': ".(IsModuleInstalled('b24network')? 'true': 'false').",
					'bitrixIntranet': ".(IsModuleInstalled('intranet')? 'true': 'false').",
					'bitrixXmpp': ".(IsModuleInstalled('xmpp')? 'true': 'false').",
					'bitrixMobile': ".(IsModuleInstalled('mobile')? 'true': 'false').",
					'bitrixOpenLines': ".(IsModuleInstalled('imopenlines')? 'true': 'false').",
					'desktop': ".$arTemplate["DESKTOP"].",
					'desktopStatus': ".(CIMMessenger::CheckDesktopStatusOnline()? 'true': 'false').",
					'desktopVersion': ".CIMMessenger::GetDesktopVersion().",
					'desktopLinkOpen': ".$arTemplate["DESKTOP_LINK_OPEN"].",
					'language': '".LANGUAGE_ID."',
					'tooltipShowed': ".\Bitrix\Im\Common::objectEncode(CUserOptions::GetOption('im', 'tooltipShowed', array())).",

					'bot': ".(empty($arTemplate['BOT'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["BOT"])).",
					'textareaIcon': ".(empty($arTemplate['TEXTAREA_ICON'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["TEXTAREA_ICON"])).",
					'command': ".(empty($arTemplate['COMMAND'])? '[]': \Bitrix\Im\Common::objectEncode($arTemplate["COMMAND"])).",

					'smile': ".\Bitrix\Im\Common::objectEncode($arTemplate["SMILE"]).",
					'smileSet': ".\Bitrix\Im\Common::objectEncode($arTemplate["SMILE_SET"]).",
					'settings': ".\Bitrix\Im\Common::objectEncode($arTemplate['SETTINGS']).",
					'settingsNotifyBlocked': ".(empty($arTemplate['SETTINGS_NOTIFY_BLOCKED'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['SETTINGS_NOTIFY_BLOCKED'])).",

					'notify': ".(empty($arTemplate['NOTIFY']['notify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['notify'])).",
					'unreadNotify' : ".(empty($arTemplate['NOTIFY']['unreadNotify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['unreadNotify'])).",
					'flashNotify' : ".(empty($arTemplate['NOTIFY']['flashNotify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['flashNotify'])).",
					'countNotify' : ".intval($arTemplate['NOTIFY']['countNotify']).",
					'loadNotify' : ".($arTemplate['NOTIFY']['loadNotify']? 'true': 'false').",

					'recent': ".(empty($arTemplate['RECENT']) && $arTemplate['RECENT'] !== false? '[]': \Bitrix\Im\Common::objectEncode($arTemplate['RECENT'])).",
					'users': ".(empty($arTemplate['CONTACT_LIST']['users'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['users'])).",
					'businessUsers': ".($businessUsers === false? false: empty($businessUsers)? '{}': \Bitrix\Im\Common::objectEncode($businessUsers)).",
					'groups': ".(empty($arTemplate['CONTACT_LIST']['groups'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['groups'])).",
					'userInGroup': ".(empty($arTemplate['CONTACT_LIST']['userInGroup'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['userInGroup'])).",
					'chat': ".(empty($arTemplate['CHAT']['chat'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['chat'])).",
					'userInChat': ".(empty($arTemplate['CHAT']['userInChat'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['userInChat'])).",
					'userChatBlockStatus': ".(empty($arTemplate['CHAT']['userChatBlockStatus'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['userChatBlockStatus'])).",
					'userChatOptions': ".\Bitrix\Im\Common::objectEncode(CIMChat::GetChatOptions()).",
					'message' : ".(empty($arTemplate['MESSAGE']['message'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['message'])).",
					'files' : ".(empty($arTemplate['MESSAGE']['files'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['files'])).",
					'showMessage' : ".(empty($arTemplate['MESSAGE']['usersMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['usersMessage'])).",
					'unreadMessage' : ".(empty($arTemplate['MESSAGE']['unreadMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['unreadMessage'])).",
					'flashMessage' : ".(empty($arTemplate['MESSAGE']['flashMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['flashMessage'])).",
					'history' : {},
					'openMessenger' : ".(isset($_REQUEST['IM_DIALOG'])? "'".CUtil::JSEscape(htmlspecialcharsbx($_REQUEST['IM_DIALOG']))."'": 'false').",
					'openHistory' : ".(isset($_REQUEST['IM_HISTORY'])? "'".CUtil::JSEscape(htmlspecialcharsbx($_REQUEST['IM_HISTORY']))."'": 'false').",
					'openNotify' : ".(isset($_GET['IM_NOTIFY']) && $_GET['IM_NOTIFY'] == 'Y'? 'true': 'false').",
					'openSettings' : ".(isset($_GET['IM_SETTINGS'])? $_GET['IM_SETTINGS'] == 'Y'? "'true'": "'".CUtil::JSEscape(htmlspecialcharsbx($_GET['IM_SETTINGS']))."'": 'false').",
					'externalRecentList' : '".(isset($arTemplate['EXTERNAL_RECENT_LIST'])?$arTemplate['EXTERNAL_RECENT_LIST']: '')."',

					'generalChatId': ".CIMChat::GetGeneralChatId().",
					'canSendMessageGeneralChat': ".(CIMChat::CanSendMessageToGeneralChat($USER->GetID())? 'true': 'false').",
					'userId': ".$USER->GetID().",
					'userEmail': '".CUtil::JSEscape($USER->GetEmail())."',
					'userColor': '".IM\Color::getCode($userColor)."',
					'userGender': '".IM\User::getInstance()->getGender()."',
					'userExtranet': ".(IM\User::getInstance()->isExtranet()? 'true': 'false').",
					'webrtc': {
						'turnServer' : '".CUtil::JSEscape($arTemplate['TURN_SERVER'])."', 
						'turnServerFirefox' : '".CUtil::JSEscape($arTemplate['TURN_SERVER_FIREFOX'])."', 
						'turnServerLogin' : '".CUtil::JSEscape($arTemplate['TURN_SERVER_LOGIN'])."', 
						'turnServerPassword' : '".CUtil::JSEscape($arTemplate['TURN_SERVER_PASSWORD'])."', 
						'mobileSupport': false, 
						'phoneEnabled': ".($phoneEnabled? 'true': 'false').", 
						'phoneSipAvailable': ".($phoneSipAvailable? 'true': 'false').", 
						'phoneDeviceActive': '".($phoneDeviceActive? 'Y': 'N')."', 
						'phoneCanPerformCalls': '".($phoneCanPerformCalls? 'Y': 'N')."', 
						'phoneCanCallUserNumber': '".($phoneCanCallUserNumber? 'Y': 'N')."', 
						'phoneCanInterceptCall': ".($phoneCanInterceptCall? 'true': 'false').", 
						'callServerEnabled': '".($callServerEnabled)."', 
						'phoneCallCardRestApps': ".\Bitrix\Im\Common::objectEncode(self::GetCallCardRestApps()).",
						'phoneLines': ".\Bitrix\Im\Common::objectEncode(self::GetTelephonyLines()).",
						'phoneDefaultLineId': '".self::GetDefaultTelephonyLine()."',
						'availableLines': ".\Bitrix\Im\Common::objectEncode(self::GetTelephonyAvailableLines())."
					},
					'openlines': ".\Bitrix\Im\Common::objectEncode($olConfig).",
					'options': {'chatExtendShowHistory' : ".($chatExtendShowHistory? 'true': 'false').", 'showRecent': ".($_REQUEST['IM_RECENT'] == 'N'? 'false': 'true').", 'showMenu': ".($_REQUEST['IM_MENU'] == 'N'? 'false': 'true')."},
					'disk': {'enable' : ".($diskStatus? 'true': 'false').", 'external' : ".($diskExternalLinkStatus? 'true': 'false')."},
					'path' : {'lf' : '".CUtil::JSEscape($pathToLf)."', 'profile' : '".CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE'])."', 'profileTemplate' : '".CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE_TEMPLATE'])."', 'mail' : '".CUtil::JSEscape($arTemplate['PATH_TO_USER_MAIL'])."', 'im': '".CUtil::JSEscape($pathToIm)."', 'call': '".CUtil::JSEscape($pathToCall)."', 'file': '".CUtil::JSEscape($pathToFile)."', 'crm' : ".\Bitrix\Im\Common::objectEncode($crmPath)."}
				});
			});
		";

		return $sJS;
	}

	public static function GetMobileTemplateJS($arParams, $arTemplate)
	{
		global $USER;

		$ppStatus = 'false';
		$ppServerStatus = 'false';
		$updateStateInterval = 'auto';
		if (CModule::IncludeModule("pull"))
		{
			$ppStatus = CPullOptions::ModuleEnable()? 'true': 'false';
			$ppServerStatus = CPullOptions::GetNginxStatus()? 'true': 'false';
			$updateStateInterval = CPullOptions::GetNginxStatus()? self::GetSessionLifeTime(): 80;
			if ($updateStateInterval > 100)
			{
				if ($updateStateInterval > 3600)
					$updateStateInterval = 3600;

				$updateStateInterval = $updateStateInterval-60;
			}
		}

		$diskStatus = CIMDisk::Enabled();
		$diskExternalLinkStatus = CIMDisk::EnabledExternalLink();

		$phoneSipAvailable = false;
		$phoneDeviceActive = false;

		$chatExtendShowHistory = \COption::GetOptionInt('im', 'chat_extend_show_history');

		$phoneEnabled = self::CheckPhoneStatus() && CModule::IncludeModule('mobileapp') && \Bitrix\MobileApp\Mobile::getInstance()->isWebRtcSupported();
		if ($phoneEnabled && CModule::IncludeModule('voximplant'))
		{
			$phoneSipAvailable = CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP);
			$phoneDeviceActive = CVoxImplantUser::GetPhoneActive($USER->GetId());
		}

		$olConfig = Array();
		$crmPath = Array();
		$businessUsers = false;
		if (CModule::IncludeModule('imopenlines'))
		{
			$crmPath['LEAD'] = \Bitrix\ImOpenLines\Crm::getLink('LEAD');
			$crmPath['CONTACT'] = \Bitrix\ImOpenLines\Crm::getLink('CONTACT');
			$crmPath['COMPANY'] = \Bitrix\ImOpenLines\Crm::getLink('COMPANY');
			$crmPath['DEAL'] = \Bitrix\ImOpenLines\Crm::getLink('DEAL');

			$businessUsers = \Bitrix\Imopenlines\Limit::getLicenseUsersLimit();

			$olConfig['canDeleteMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanDeleteMessage());
			$olConfig['canDeleteOwnMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanDeleteOwnMessage());
			$olConfig['canUpdateOwnMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanUpdateOwnMessage());
			$olConfig['queue'] = Array();
			foreach (\Bitrix\ImOpenLines\Config::getQueueList($USER->GetID()) as $config)
			{
				$olConfig['queue'][] = array_change_key_case($config, CASE_LOWER);
			}
		}

		$mobileAction = isset($arTemplate["ACTION"])? $arTemplate["ACTION"]: 'none';
		$mobileCallMethod = isset($arTemplate["CALL_METHOD"])? $arTemplate["CALL_METHOD"]: 'device';

		$userColor = isset($arTemplate['CONTACT_LIST']['users'][$USER->GetID()]['color']) ? $arTemplate['CONTACT_LIST']['users'][$USER->GetID()]['color']: '';

		$sJS = "
			BX.ready(function() {
				BXIM = new BX.ImMobile({
					'mobileAction': '".$mobileAction."',
					'mobileCallMethod': '".$mobileCallMethod."',

					'colors': ".(IM\Color::isEnabled()? \Bitrix\Im\Common::objectEncode(IM\Color::getSafeColorNames()): 'false').",
					'mailCount': ".intval($arTemplate["MAIL_COUNTER"]).",
					'notifyCount': ".intval($arTemplate["NOTIFY_COUNTER"]).",
					'messageCount': ".intval($arTemplate["MESSAGE_COUNTER"]).",
					'counters': ".(empty($arTemplate['COUNTERS'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['COUNTERS'])).",
					'ppStatus': ".$ppStatus.",
					'ppServerStatus': ".$ppServerStatus.",
					'updateStateInterval': '".$updateStateInterval."',
					'openChatEnable': ".(CIMMessenger::CheckEnableOpenChat()? 'true': 'false').",
					'xmppStatus': ".(CIMMessenger::CheckXmppStatusOnline()? 'true': 'false').",
					'isAdmin': ".(self::IsAdmin()? 'true': 'false').",
					'bitrixNetwork': ".(CIMMessenger::CheckNetwork()? 'true': 'false').",
					'bitrixNetwork2': ".(CIMMessenger::CheckNetwork2()? 'true': 'false').",
					'bitrix24': ".(IsModuleInstalled('bitrix24')? 'true': 'false').",
					'bitrix24net': ".(IsModuleInstalled('b24network')? 'true': 'false').",
					'bitrixIntranet': ".(IsModuleInstalled('intranet')? 'true': 'false').",
					'bitrixXmpp': ".(IsModuleInstalled('xmpp')? 'true': 'false').",
					'bitrixMobile': ".(IsModuleInstalled('mobile')? 'true': 'false').",
					'bitrixOpenLines': ".(IsModuleInstalled('imopenlines')? 'true': 'false').",
					'desktopStatus': ".(CIMMessenger::CheckDesktopStatusOnline()? 'true': 'false').",
					'desktopVersion': ".CIMMessenger::GetDesktopVersion().",
					'language': '".LANGUAGE_ID."',

					'bot': ".(empty($arTemplate['BOT'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["BOT"])).",
					'command': ".(empty($arTemplate['COMMAND'])? '[]': \Bitrix\Im\Common::objectEncode($arTemplate["COMMAND"])).",
					'textareaIcon': ".(empty($arTemplate['TEXTAREA_ICON'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["TEXTAREA_ICON"])).",
					
					'smile': ".(empty($arTemplate['SMILE'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["SMILE"])).",
					'smileSet': ".(empty($arTemplate['SMILE_SET'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["SMILE_SET"])).",
					'settings': ".(empty($arTemplate['SETTINGS'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['SETTINGS'])).",
					'settingsNotifyBlocked': ".(empty($arTemplate['SETTINGS_NOTIFY_BLOCKED'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['SETTINGS_NOTIFY_BLOCKED'])).",

					'notify': ".(empty($arTemplate['NOTIFY']['notify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['notify'])).",
					'unreadNotify' : ".(empty($arTemplate['NOTIFY']['unreadNotify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['unreadNotify'])).",
					'flashNotify' : ".(empty($arTemplate['NOTIFY']['flashNotify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['flashNotify'])).",
					'countNotify' : ".intval($arTemplate['NOTIFY']['countNotify']).",
					'loadNotify' : ".($arTemplate['NOTIFY']['loadNotify']? 'true': 'false').",

					'recent': ".(empty($arTemplate['RECENT']) && $arTemplate['RECENT'] !== false? '[]': \Bitrix\Im\Common::objectEncode($arTemplate['RECENT'])).",
					'users': ".(empty($arTemplate['CONTACT_LIST']['users'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['users'])).",
					'businessUsers': ".($businessUsers === false? false: empty($businessUsers)? '{}': \Bitrix\Im\Common::objectEncode($businessUsers)).",
					'groups': ".(empty($arTemplate['CONTACT_LIST']['groups'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['groups'])).",
					'userInGroup': ".(empty($arTemplate['CONTACT_LIST']['userInGroup'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['userInGroup'])).",
					'chat': ".(empty($arTemplate['CHAT']['chat'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['chat'])).",
					'userInChat': ".(empty($arTemplate['CHAT']['userInChat'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['userInChat'])).",
					'userChatBlockStatus': ".(empty($arTemplate['CHAT']['userChatBlockStatus'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['userChatBlockStatus'])).",
					'userChatOptions': ".\Bitrix\Im\Common::objectEncode(CIMChat::GetChatOptions()).",
					'message' : ".(empty($arTemplate['MESSAGE']['message'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['message'])).",
					'files' : ".(empty($arTemplate['MESSAGE']['files'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['files'])).",
					'showMessage' : ".(empty($arTemplate['MESSAGE']['usersMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['usersMessage'])).",
					'unreadMessage' : ".(empty($arTemplate['MESSAGE']['unreadMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['unreadMessage'])).",
					'flashMessage' : ".(empty($arTemplate['MESSAGE']['flashMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['flashMessage'])).",
					'history' : {},
					'openMessenger' : ".(isset($_GET['IM_DIALOG'])? "'".CUtil::JSEscape(htmlspecialcharsbx($_GET['IM_DIALOG']))."'": 'false').",
					'openHistory' : ".(isset($_GET['IM_HISTORY'])? "'".CUtil::JSEscape(htmlspecialcharsbx($_GET['IM_HISTORY']))."'": 'false').",
					'openNotify' : ".(isset($_GET['IM_NOTIFY']) && $_GET['IM_NOTIFY'] == 'Y'? 'true': 'false').",
					'openSettings' : ".(isset($_GET['IM_SETTINGS'])? $_GET['IM_SETTINGS'] == 'Y'? "'true'": "'".CUtil::JSEscape(htmlspecialcharsbx($_GET['IM_SETTINGS']))."'": 'false').",

					'generalChatId': ".CIMChat::GetGeneralChatId().",
					'canSendMessageGeneralChat': ".(CIMChat::CanSendMessageToGeneralChat($USER->GetID())? 'true': 'false').",
					'userId': ".$USER->GetID().",
					'userEmail': '".CUtil::JSEscape($USER->GetEmail())."',
					'userColor': '".IM\Color::getCode($userColor)."',
					'userGender': '".IM\User::getInstance()->getGender()."',
					'userExtranet': ".(IM\User::getInstance()->isExtranet()? 'true': 'false').",
					'webrtc': {'turnServer' : '".(empty($arTemplate['TURN_SERVER'])? '': CUtil::JSEscape($arTemplate['TURN_SERVER']))."', 'turnServerLogin' : '".(empty($arTemplate['TURN_SERVER_LOGIN'])? '': CUtil::JSEscape($arTemplate['TURN_SERVER_LOGIN']))."', 'turnServerPassword' : '".(empty($arTemplate['TURN_SERVER_PASSWORD'])? '': CUtil::JSEscape($arTemplate['TURN_SERVER_PASSWORD']))."', 'mobileSupport': ".($arTemplate['WEBRTC_MOBILE_SUPPORT']? 'true': 'false').", 'phoneEnabled': ".($phoneEnabled? 'true': 'false').", 'phoneSipAvailable': ".($phoneSipAvailable? 'true': 'false')."},
					'openlines': ".\Bitrix\Im\Common::objectEncode($olConfig).",
					'options': {'chatExtendShowHistory' : ".($chatExtendShowHistory? 'true': 'false')."},
					'disk': {'enable' : ".($diskStatus? 'true': 'false').", 'external' : ".($diskExternalLinkStatus? 'true': 'false')."},
					'path' : {'lf' : '".(empty($arTemplate['PATH_TO_LF'])? '/': CUtil::JSEscape($arTemplate['PATH_TO_LF']))."', 'profile' : '".(empty($arTemplate['PATH_TO_USER_PROFILE'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE']))."', 'profileTemplate' : '".(empty($arTemplate['PATH_TO_USER_PROFILE_TEMPLATE'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE_TEMPLATE']))."', 'mail' : '".(empty($arTemplate['PATH_TO_USER_MAIL'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_MAIL']))."', 'crm' : ".\Bitrix\Im\Common::objectEncode($crmPath)."}
				});
			});
		";

		return $sJS;
	}

	public static function GetMobileDialogTemplateJS($arParams, $arTemplate)
	{
		global $USER;

		$ppStatus = 'false';
		$ppServerStatus = 'false';
		$updateStateInterval = 'auto';
		if (CModule::IncludeModule("pull"))
		{
			$ppStatus = CPullOptions::ModuleEnable()? 'true': 'false';
			$ppServerStatus = CPullOptions::GetNginxStatus()? 'true': 'false';
			$updateStateInterval = CPullOptions::GetNginxStatus()? self::GetSessionLifeTime(): 80;
			if ($updateStateInterval > 100)
			{
				if ($updateStateInterval > 3600)
					$updateStateInterval = 3600;

				$updateStateInterval = $updateStateInterval-60;
			}
		}

		$diskStatus = CIMDisk::Enabled();
		$diskExternalLinkStatus = CIMDisk::EnabledExternalLink();

		$phoneSipAvailable = false;
		$phoneDeviceActive = false;

		$chatExtendShowHistory = \COption::GetOptionInt('im', 'chat_extend_show_history');

		$phoneEnabled = self::CheckPhoneStatus() && CModule::IncludeModule('mobileapp') && \Bitrix\MobileApp\Mobile::getInstance()->isWebRtcSupported();
		if ($phoneEnabled && CModule::IncludeModule('voximplant'))
		{
			$phoneSipAvailable = CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP);
			$phoneDeviceActive = CVoxImplantUser::GetPhoneActive($USER->GetId());
		}

		$olConfig = Array();
		$crmPath = Array();
		$businessUsers = false;
		if (CModule::IncludeModule('imopenlines'))
		{
			$crmPath['LEAD'] = \Bitrix\ImOpenLines\Crm::getLink('LEAD');
			$crmPath['CONTACT'] = \Bitrix\ImOpenLines\Crm::getLink('CONTACT');
			$crmPath['COMPANY'] = \Bitrix\ImOpenLines\Crm::getLink('COMPANY');
			$crmPath['DEAL'] = \Bitrix\ImOpenLines\Crm::getLink('DEAL');

			$businessUsers = \Bitrix\Imopenlines\Limit::getLicenseUsersLimit();

			$olConfig['canDeleteMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanDeleteMessage());
			$olConfig['canDeleteOwnMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanDeleteOwnMessage());
			$olConfig['canUpdateOwnMessage'] = str_replace('.', '_', \Bitrix\Imopenlines\Connector::getListCanUpdateOwnMessage());
			$olConfig['queue'] = Array();
			foreach (\Bitrix\ImOpenLines\Config::getQueueList($USER->GetID()) as $config)
			{
				$olConfig['queue'][] = array_change_key_case($config, CASE_LOWER);
			}
		}

		$mobileAction = isset($arTemplate["ACTION"])? $arTemplate["ACTION"]: 'none';
		$mobileCallMethod = isset($arTemplate["CALL_METHOD"])? $arTemplate["CALL_METHOD"]: 'device';

		$userColor = isset($arTemplate['CONTACT_LIST']['users'][$USER->GetID()]['color']) ? $arTemplate['CONTACT_LIST']['users'][$USER->GetID()]['color']: '';

		$staticJs = "
			BXIM = new BX.ImMobile({
				'path' : {'lf' : '".(empty($arTemplate['PATH_TO_LF'])? '/': CUtil::JSEscape($arTemplate['PATH_TO_LF']))."', 'profile' : '".(empty($arTemplate['PATH_TO_USER_PROFILE'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE']))."', 'profileTemplate' : '".(empty($arTemplate['PATH_TO_USER_PROFILE_TEMPLATE'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE_TEMPLATE']))."', 'mail' : '".(empty($arTemplate['PATH_TO_USER_MAIL'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_MAIL']))."', 'crm' : ".\Bitrix\Im\Common::objectEncode($crmPath)."}
			});
		";

		$dynamicJs = "
			BX.ready(function() {
				BXIM.initParams({
					'mobileAction': '".$mobileAction."',
					'mobileCallMethod': '".$mobileCallMethod."',

					'colors': ".(IM\Color::isEnabled()? \Bitrix\Im\Common::objectEncode(IM\Color::getSafeColorNames()): 'false').",
					'mailCount': ".intval($arTemplate["MAIL_COUNTER"]).",
					'notifyCount': ".intval($arTemplate["NOTIFY_COUNTER"]).",
					'messageCount': ".intval($arTemplate["MESSAGE_COUNTER"]).",
					'counters': ".(empty($arTemplate['COUNTERS'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['COUNTERS'])).",
					'ppStatus': ".$ppStatus.",
					'ppServerStatus': ".$ppServerStatus.",
					'updateStateInterval': '".$updateStateInterval."',
					'openChatEnable': ".(CIMMessenger::CheckEnableOpenChat()? 'true': 'false').",
					'xmppStatus': ".(CIMMessenger::CheckXmppStatusOnline()? 'true': 'false').",
					'isAdmin': ".(self::IsAdmin()? 'true': 'false').",
					'bitrixNetwork': ".(CIMMessenger::CheckNetwork()? 'true': 'false').",
					'bitrixNetwork2': ".(CIMMessenger::CheckNetwork2()? 'true': 'false').",
					'bitrix24': ".(IsModuleInstalled('bitrix24')? 'true': 'false').",
					'bitrix24net': ".(IsModuleInstalled('b24network')? 'true': 'false').",
					'bitrixIntranet': ".(IsModuleInstalled('intranet')? 'true': 'false').",
					'bitrixXmpp': ".(IsModuleInstalled('xmpp')? 'true': 'false').",
					'bitrixMobile': ".(IsModuleInstalled('mobile')? 'true': 'false').",
					'bitrixOpenLines': ".(IsModuleInstalled('imopenlines')? 'true': 'false').",
					'desktopStatus': ".(CIMMessenger::CheckDesktopStatusOnline()? 'true': 'false').",
					'desktopVersion': ".CIMMessenger::GetDesktopVersion().",
					'language': '".LANGUAGE_ID."',

					'bot': ".(empty($arTemplate['BOT'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["BOT"])).",
					'command': ".(empty($arTemplate['COMMAND'])? '[]': \Bitrix\Im\Common::objectEncode($arTemplate["COMMAND"])).",
					'textareaIcon': ".(empty($arTemplate['TEXTAREA_ICON'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["TEXTAREA_ICON"])).",
					
					'smile': ".(empty($arTemplate['SMILE'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["SMILE"])).",
					'smileSet': ".(empty($arTemplate['SMILE_SET'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate["SMILE_SET"])).",
					'settings': ".(empty($arTemplate['SETTINGS'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['SETTINGS'])).",
					'settingsNotifyBlocked': ".(empty($arTemplate['SETTINGS_NOTIFY_BLOCKED'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['SETTINGS_NOTIFY_BLOCKED'])).",

					'notify': ".(empty($arTemplate['NOTIFY']['notify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['notify'])).",
					'unreadNotify' : ".(empty($arTemplate['NOTIFY']['unreadNotify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['unreadNotify'])).",
					'flashNotify' : ".(empty($arTemplate['NOTIFY']['flashNotify'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['NOTIFY']['flashNotify'])).",
					'countNotify' : ".intval($arTemplate['NOTIFY']['countNotify']).",
					'loadNotify' : ".($arTemplate['NOTIFY']['loadNotify']? 'true': 'false').",

					'recent': ".(empty($arTemplate['RECENT']) && $arTemplate['RECENT'] !== false? '[]': \Bitrix\Im\Common::objectEncode($arTemplate['RECENT'])).",
					'users': ".(empty($arTemplate['CONTACT_LIST']['users'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['users'])).",
					'businessUsers': ".($businessUsers === false? false: empty($businessUsers)? '{}': \Bitrix\Im\Common::objectEncode($businessUsers)).",
					'groups': ".(empty($arTemplate['CONTACT_LIST']['groups'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['groups'])).",
					'userInGroup': ".(empty($arTemplate['CONTACT_LIST']['userInGroup'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CONTACT_LIST']['userInGroup'])).",
					'chat': ".(empty($arTemplate['CHAT']['chat'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['chat'])).",
					'userInChat': ".(empty($arTemplate['CHAT']['userInChat'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['userInChat'])).",
					'userChatBlockStatus': ".(empty($arTemplate['CHAT']['userChatBlockStatus'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['CHAT']['userChatBlockStatus'])).",
					'userChatOptions': ".\Bitrix\Im\Common::objectEncode(CIMChat::GetChatOptions()).",
					'message' : ".(empty($arTemplate['MESSAGE']['message'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['message'])).",
					'files' : ".(empty($arTemplate['MESSAGE']['files'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['files'])).",
					'showMessage' : ".(empty($arTemplate['MESSAGE']['usersMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['usersMessage'])).",
					'unreadMessage' : ".(empty($arTemplate['MESSAGE']['unreadMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['unreadMessage'])).",
					'flashMessage' : ".(empty($arTemplate['MESSAGE']['flashMessage'])? '{}': \Bitrix\Im\Common::objectEncode($arTemplate['MESSAGE']['flashMessage'])).",
					'history' : {},
					'openMessenger' : ".(isset($_GET['IM_DIALOG'])? "'".CUtil::JSEscape(htmlspecialcharsbx($_GET['IM_DIALOG']))."'": 'false').",
					'openHistory' : ".(isset($_GET['IM_HISTORY'])? "'".CUtil::JSEscape(htmlspecialcharsbx($_GET['IM_HISTORY']))."'": 'false').",
					'openNotify' : ".(isset($_GET['IM_NOTIFY']) && $_GET['IM_NOTIFY'] == 'Y'? 'true': 'false').",
					'openSettings' : ".(isset($_GET['IM_SETTINGS'])? $_GET['IM_SETTINGS'] == 'Y'? "'true'": "'".CUtil::JSEscape(htmlspecialcharsbx($_GET['IM_SETTINGS']))."'": 'false').",

					'generalChatId': ".CIMChat::GetGeneralChatId().",
					'canSendMessageGeneralChat': ".(CIMChat::CanSendMessageToGeneralChat($USER->GetID())? 'true': 'false').",
					'userId': ".$USER->GetID().",
					'userEmail': '".CUtil::JSEscape($USER->GetEmail())."',
					'userColor': '".IM\Color::getCode($userColor)."',
					'userGender': '".IM\User::getInstance()->getGender()."',
					'userExtranet': ".(IM\User::getInstance()->isExtranet()? 'true': 'false').",
					'webrtc': {'turnServer' : '".(empty($arTemplate['TURN_SERVER'])? '': CUtil::JSEscape($arTemplate['TURN_SERVER']))."', 'turnServerLogin' : '".(empty($arTemplate['TURN_SERVER_LOGIN'])? '': CUtil::JSEscape($arTemplate['TURN_SERVER_LOGIN']))."', 'turnServerPassword' : '".(empty($arTemplate['TURN_SERVER_PASSWORD'])? '': CUtil::JSEscape($arTemplate['TURN_SERVER_PASSWORD']))."', 'mobileSupport': ".($arTemplate['WEBRTC_MOBILE_SUPPORT']? 'true': 'false').", 'phoneEnabled': ".($phoneEnabled? 'true': 'false').", 'phoneSipAvailable': ".($phoneSipAvailable? 'true': 'false')."},
					'openlines': ".\Bitrix\Im\Common::objectEncode($olConfig).",
					'options': {'chatExtendShowHistory' : ".($chatExtendShowHistory? 'true': 'false')."},
					'disk': {'enable' : ".($diskStatus? 'true': 'false').", 'external' : ".($diskExternalLinkStatus? 'true': 'false')."},
					'path' : {'lf' : '".(empty($arTemplate['PATH_TO_LF'])? '/': CUtil::JSEscape($arTemplate['PATH_TO_LF']))."', 'profile' : '".(empty($arTemplate['PATH_TO_USER_PROFILE'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE']))."', 'profileTemplate' : '".(empty($arTemplate['PATH_TO_USER_PROFILE_TEMPLATE'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_PROFILE_TEMPLATE']))."', 'mail' : '".(empty($arTemplate['PATH_TO_USER_MAIL'])? '': CUtil::JSEscape($arTemplate['PATH_TO_USER_MAIL']))."', 'crm' : ".\Bitrix\Im\Common::objectEncode($crmPath)."}
				});
			});
		";

		return Array('STATIC' => $staticJs, 'DYNAMIC' => $dynamicJs);
	}

	public static function StartWriting($dialogId, $userId = false, $userName = "", $byEvent = false, $linesSilentMode = false)
	{
		$userId = intval($userId);
		if ($userId <= 0)
		{
			global $USER;
			$userId = intval($USER->GetID());
		}

		if (substr($dialogId, 0, 4) == 'chat')
		{
		}
		else if ($dialogId == $userId)
		{
			return false;
		}
		else
		{
			$dialogId = intval($dialogId);
		}
		if (!$userName)
		{
			$userName = \Bitrix\Im\User::getInstance($userId)->getFullName();
		}

		if ($userId > 0 && strlen($dialogId) > 0 && CModule::IncludeModule("pull"))
		{
			CPushManager::DeleteFromQueueBySubTag($userId, 'IM_MESS');

			$chat = Array();
			$relation = Array();
			if (substr($dialogId, 0, 4) == 'chat')
			{
				$orm = \Bitrix\Im\Model\ChatTable::getById(substr($dialogId, 4));
				$chat = $orm->fetch();

				$arRelation = CIMChat::GetRelationById(substr($dialogId, 4));
				$relation = $arRelation;

				if ($chat['ENTITY_TYPE'] != 'LIVECHAT' && !isset($arRelation[$userId]))
				{
					return false;
				}

				unset($arRelation[$userId]);

				$pullMessage = Array(
					'module_id' => 'im',
					'command' => 'startWriting',
					'expiry' => 60,
					'params' => Array(
						'dialogId' => $dialogId,
						'userId' => $userId,
						'userName' => $userName
					),
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				);

				$chatId = $chat['ID'];
				$entityType = $chat['ENTITY_TYPE'];
				$entityId = $chat['ENTITY_ID'];
				if ($chat['ENTITY_TYPE'] == 'LINES')
				{
					foreach ($arRelation as $rel)
					{
						if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
						{
							unset($arRelation[$rel["USER_ID"]]);
						}
					}
				}
				\Bitrix\Pull\Event::add(array_keys($arRelation), $pullMessage);

				if ($chat['TYPE'] == IM_MESSAGE_OPEN || $chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pullMessage);
				}
			}
			else if (intval($dialogId) > 0)
			{
				\Bitrix\Pull\Event::add($dialogId, Array(
					'module_id' => 'im',
					'command' => 'startWriting',
					'expiry' => 60,
					'params' => Array(
						'dialogId' => $userId,
						'userId' => $userId,
						'userName' => $userName
					),
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				));
			}

			foreach(GetModuleEvents("im", "OnStartWriting", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array(array(
					'DIALOG_ID' => $dialogId,
					'CHAT' => $chat,
					'RELATION' => $relation,
					'USER_ID' => $userId,
					'USER_NAME' => $userName,
					'BY_EVENT' => $byEvent,
					'LINES_SILENT_MODE' => $linesSilentMode
				)));
			}

			return true;
		}
		return false;
	}

	public static function PrepareSmiles()
	{
		return CSmileGallery::getSmilesWithSets();
	}

	private static function UploadFileFromText($params)
	{
		$params['CHAT_ID'] = intval($params['CHAT_ID']);
		if (empty($params['MESSAGE']) || $params['CHAT_ID'] <= 0)
		{
			return $params;
		}

		if (preg_match_all("/\[DISK=([0-9]+)\]/i", $params['MESSAGE'], $matches))
		{
			foreach ($matches[1] as $fileId)
			{
				$newFile = CIMDisk::SaveFromLocalDisk($params['CHAT_ID'], $fileId);
				if ($newFile)
				{
					$params['PARAMS']['FILE_ID'][] = $newFile->getId();
				}
			}
			$params['MESSAGE'] = preg_replace("/\[DISK\=([0-9]+)\]/i", "", $params['MESSAGE']);
		}

		return $params;
	}

	public static function SendMention($params)
	{
		$params['CHAT_ID'] = intval($params['CHAT_ID']);
		if (!isset($params['MESSAGE']) || $params['CHAT_ID'] <= 0)
		{
			return false;
		}

		$userName = \Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getFullName(false);
		$userGender = \Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getGender();

		if (!$userName)
		{
			return false;
		}

		if (!isset($params['CHAT_TITLE']) || !isset($params['CHAT_TYPE']) || !isset($params['CHAT_ENTITY_TYPE']))
		{
			$orm = \Bitrix\Im\Model\ChatTable::getById($params['CHAT_ID']);
			$chat = $orm->fetch();
			if (!$chat)
			{
				return false;
			}

			$params['CHAT_TITLE'] = $chat['TITLE'];
			$params['CHAT_TYPE'] = trim($chat['TYPE']);
			$params['CHAT_COLOR'] = trim($chat['COLOR']);
			$params['CHAT_ENTITY_TYPE'] = trim($chat['CHAT_ENTITY_TYPE']);
		}

		if (!in_array($params['CHAT_TYPE'], Array(IM_MESSAGE_OPEN, IM_MESSAGE_CHAT, IM_MESSAGE_OPEN_LINE)))
		{
			return false;
		}

		if (!isset($params['CHAT_RELATION']))
		{
			$params['CHAT_RELATION'] = CIMChat::GetRelationById($params['CHAT_ID']);
		}

		$forUsers = Array();
		if (preg_match_all("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", $params['MESSAGE'], $matches))
		{
			if ($params['CHAT_TYPE'] == IM_MESSAGE_OPEN)
			{
				foreach($matches[1] as $userId)
				{
					if (!CIMSettings::GetNotifyAccess($userId, 'im', 'mention', CIMSettings::CLIENT_SITE))
					{
						continue;
					}

					if (
						!isset($params['CHAT_RELATION'][$userId])
						|| $params['CHAT_RELATION'][$userId]['NOTIFY_BLOCK'] == 'Y'
					)
					{
						$forUsers[$userId] = $userId;
					}
				}
			}
			else
			{
				foreach($matches[1] as $userId)
				{
					if (!CIMSettings::GetNotifyAccess($userId, 'im', 'mention', CIMSettings::CLIENT_SITE))
					{
						continue;
					}

					if (
						isset($params['CHAT_RELATION'][$userId])
						&& $params['CHAT_RELATION'][$userId]['NOTIFY_BLOCK'] == 'Y'
					)
					{
						$forUsers[$userId] = $userId;
					}
				}
			}
		}

		$chatTitle = substr(htmlspecialcharsback($params['CHAT_TITLE']), 0, 32);
		$notifyMail = GetMessage('IM_MESSAGE_MENTION_'.($userGender=='F'?'F':'M'), Array('#TITLE#' => $chatTitle));
		$notifyText = GetMessage('IM_MESSAGE_MENTION_'.($userGender=='F'?'F':'M'), Array('#TITLE#' => '[CHAT='.$params['CHAT_ID'].']'.$chatTitle.'[/CHAT]'));
		$pushText1 = GetMessage('IM_MESSAGE_MENTION_PUSH_'.($userGender=='F'?'F':'M'), Array('#USER#' => $userName, '#TITLE#' => $chatTitle)).': '.self::PrepareMessageForPush(Array('MESSAGE' => $params['MESSAGE'], 'FILES' => $params['FILES']));
		$pushText2 = GetMessage('IM_MESSAGE_MENTION_PUSH_2_'.($userGender=='F'?'F':'M'), Array('#USER#' => $userName)).': '.self::PrepareMessageForPush(Array('MESSAGE' => $params['MESSAGE'], 'FILES' => $params['FILES']));

		if (strlen($pushText1) > 0)
		{
			foreach ($forUsers as $userId)
			{
				if ($params['FROM_USER_ID'] == $userId)
					continue;

				$arMessageFields = array(
					"TO_USER_ID" => $userId,
					"FROM_USER_ID" => $params['FROM_USER_ID'],
					"NOTIFY_TYPE" => IM_NOTIFY_FROM,
					"NOTIFY_MODULE" => "im",
					"NOTIFY_EVENT" => "mention",
					"NOTIFY_TAG" => 'IM|MENTION|'.$params['CHAT_ID'],
					"NOTIFY_SUB_TAG" => "IM_MESS",
					"NOTIFY_MESSAGE" => $notifyText,
					"NOTIFY_MESSAGE_OUT" => $notifyMail,
				);
				CIMNotify::Add($arMessageFields);

				$pushParams = self::PreparePushForChat(Array(
					'PUSH_TYPE' => "mention",
					'CHAT_ID' => $params['CHAT_ID'],
					'CHAT_TITLE' => $params['CHAT_TITLE'],
					'CHAT_TYPE' => $params['CHAT_TYPE'],
					'CHAT_COLOR' => $params['CHAT_COLOR'],
					'CHAT_ENTITY_TYPE' => $params['CHAT_ENTITY_TYPE'],
					'MESSAGE_ID' => $params['MESSAGE_ID'],
					'FROM_USER_ID' => $params['FROM_USER_ID'],
					'CUSTOM_MESSAGE' => Array(
						'DEFAULT' => $pushText1,
						'ADVANCED' => $pushText2,
					),
				));
				\Bitrix\Pull\Push::add($userId, $pushParams);
			}
		}

		return true;
	}

	public static function PrepareParamsForPull($params)
	{
		if (!is_array($params))
		{
			return $params;
		}

		foreach ($params as $key => $value)
		{
			if ($key == 'ATTACH')
			{
				if (is_object($value) && $value instanceof CIMMessageParamAttach)
				{
					$params[$key] = CIMMessageParamAttach::PrepareAttach($value->GetArray());
				}
				else
				{
					foreach ($value as $key2 => $value2)
					{
						if (is_object($value2) && $value2 instanceof CIMMessageParamAttach)
						{
							$params[$key][$key2] = CIMMessageParamAttach::PrepareAttach($value2->GetArray());
						}
					}
				}
			}
			elseif ($key == 'KEYBOARD')
			{
				if (is_object($value) && $value instanceof \Bitrix\Im\Bot\Keyboard)
				{
					$params[$key] = $value->getArray();
				}
			}
			elseif ($key == 'MENU')
			{
				if (is_object($value) && $value instanceof \Bitrix\Im\Bot\ContextMenu)
				{
					$params[$key] = $value->getArray();
				}
			}
			elseif ($key == 'AVATAR' && intval($value) > 0)
			{
				$arFileTmp = \CFile::ResizeImageGet(
					$value,
					array('width' => 100, 'height' => 100),
					BX_RESIZE_IMAGE_EXACT,
					false,
					false,
					true
				);
				$params[$key] = empty($arFileTmp['src'])? '': $arFileTmp['src'];
			}

		}

		return $params;
	}

	public static function PreparePushForChat($params)
	{
		$params['CHAT_ID'] = intval($params['CHAT_ID']);
		if ($params['CHAT_ID'] <= 0)
		{
			return false;
		}

		if (!isset($params['CHAT_TITLE']) || !isset($params['CHAT_TYPE']) || !isset($params['CHAT_ENTITY_TYPE']))
		{
			$orm = \Bitrix\Im\Model\ChatTable::getById($params['CHAT_ID']);
			$chat = $orm->fetch();
			if (!$chat)
			{
				return false;
			}
			$params['CHAT_TITLE'] = $chat['TITLE'];
			$params['CHAT_TYPE'] = trim($chat['TYPE']);
			$params['CHAT_ENTITY_ID'] = trim($chat['ENTITY_ID']);
			$params['CHAT_ENTITY_TYPE'] = trim($chat['ENTITY_TYPE']);
			$params['CHAT_ENTITY_DATA_1'] = trim($chat['ENTITY_DATA_1']);
		}

		$params['CHAT_TITLE'] = substr(htmlspecialcharsback($params['CHAT_TITLE']), 0, 32);

		if (isset($params['CUSTOM_MESSAGE']))
		{
			$userName = '';
			$pushText = $params['CUSTOM_MESSAGE']['DEFAULT'];
			$senderMessage = $pushText;
			$pushAdvancedText = $params['CUSTOM_MESSAGE']['ADVANCED']? $params['CUSTOM_MESSAGE']['ADVANCED']: $pushText;
		}
		else
		{
			$senderMessage = self::PrepareMessageForPush(Array(
				'MESSAGE' => $params['MESSAGE'],
				'FILES' => $params['FILES'],
				'ATTACH' => $params['ATTACH'],
			));

			if ($params['SYSTEM'] == 'Y')
			{
				$userName = '';
				$pushText = $params['CHAT_TITLE'].': '.$senderMessage;
				$pushAdvancedText = $senderMessage;
			}
			else
			{
				$userName = \Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getFullName(false);
				if (!$userName)
					return false;

				$pushText = GetMessage('IM_PUSH_GROUP_TITLE', Array('#USER#' => $userName, '#GROUP#' => $params['CHAT_TITLE'])).': '.$senderMessage;
				$pushAdvancedText = trim($userName).": ".$senderMessage;
			}
		}

		$chatType = CIMChat::getChatType($params);

		$avatarUser = \Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getAvatar();
		if ($avatarUser && strpos($avatarUser, 'http') !== 0)
		{
			$avatarUser = \Bitrix\Im\Common::getPublicDomain().$avatarUser;
		}
		$avatarChat = \CIMChat::GetAvatarImage($params['CHAT_AVATAR'], 100, false);
		if ($avatarChat && strpos($avatarChat, 'http') !== 0)
		{
			$avatarChat = \Bitrix\Im\Common::getPublicDomain().$avatarChat;
		}

		$chatColor = IM\Color::getColor($params['CHAT_COLOR']);
		if (!$chatColor)
		{
			$chatColor = IM\Color::getColorByNumber($params['CHAT_ID']);
		}

		$advancedParams = array(
			"type"=> 'chat',
			"group"=> $chatType == 'lines'? 'im_lines_message': 'im_message',
			"id"=> 'chat'.$params['CHAT_ID'],
			"avatarUrl"=> (string)$avatarUser,
			"chatUrl" => (string)$avatarChat,
			"chatColor" => (string)$chatColor,
			"chatType" => (string)$chatType,
			"chatName" => (string)$params['CHAT_TITLE'],
			"chatExtranet" => $params['CHAT_EXTRANET'] == 'Y',
			"chatEntityId" => (string)$params['CHAT_ENTITY_ID'],
			"chatEntityType" => (string)$params['CHAT_ENTITY_TYPE'],
			"chatEntityData1" => (string)$params['CHAT_ENTITY_DATA_1'],
			"userName" => (string)$userName,
			"userFirstName" => (string)\Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getName(false),
			"userLastName" => (string)\Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getLastName(false),
			"messageId" => (int)$params['MESSAGE_ID'],
			"messageText" => (string)$senderMessage,
			"messageDate" => date('c', time()),
			"messageAuthorId" => (int)$params['FROM_USER_ID'],
			"senderName" => (string)$params['CHAT_TITLE'],
			"senderMessage" => (string)$pushAdvancedText,
		);

		if ($chatType == 'lines')
		{
			$advancedParams['linesId'] = (int)$params['LINES']['id'];
			$advancedParams['linesStatus'] = (int)$params['LINES']['status'];
		}
		if ($params['PUSH_TYPE'] == 'mention')
		{
			unset($advancedParams['group']);
			unset($advancedParams['id']);
		}

		$pushText = self::PrepareMessageForPush(Array(
			'MESSAGE' => $pushText,
			'FILES' => $params['FILES'],
			'ATTACH' => $params['ATTACH'],
		));

		if (strlen($pushText) <= 0)
			return false;

		$result = Array();
		$result['module_id'] = 'im';
		$result['push']['params'] = Array(
			'TAG' => 'IM_CHAT_'.$params['CHAT_ID'],
			'CHAT_TYPE' => $params['CHAT_TYPE'],
			'CATEGORY' => 'ANSWER',
			'URL' => SITE_DIR.'mobile/ajax.php?mobile_action=im_answer',
			'PARAMS' => Array(
				'RECIPIENT_ID' => 'chat'.$params['CHAT_ID']
			)
		);
		$result['push']['type'] = $params['PUSH_TYPE']? $params['PUSH_TYPE']: ($params['CHAT_TYPE'] == IM_MESSAGE_OPEN? 'openChat': 'chat');
		$result['push']['tag'] = 'IM_CHAT_'.$params['CHAT_ID'];
		$result['push']['sub_tag'] = 'IM_MESS';
		$result['push']['app_id'] = 'Bitrix24';
		$result['push']['message'] = $pushText;
		$result['push']['advanced_params'] = $advancedParams;

		return $result;
	}

	public static function PreparePushForPrivate($params)
	{
		$advancedParams = array();

		$senderMessage = self::PrepareMessageForPush(Array(
			'MESSAGE' => $params['MESSAGE'],
			'FILES' => $params['FILES'],
			'ATTACH' => $params['ATTACH'],
		));

		if ($params['SYSTEM'] == 'Y')
		{
			$pushText = $senderMessage;
		}
		else
		{
			$userName = \Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getFullName(false);
			if (!$userName)
				return false;

			$pushText = $userName.': '.$senderMessage;

			$avatarUser = \Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getAvatar();
			if ($avatarUser && strpos($avatarUser, 'http') !== 0)
			{
				$avatarUser = \Bitrix\Im\Common::getPublicDomain().$avatarUser;
			}

			$advancedParams = array(
				"type"=> 'user',
				"group"=> 'im_message',
				"id"=> (int)$params['FROM_USER_ID'],
				"avatarUrl"=> (string)$avatarUser,
				"senderName" => (string)$userName,
				"senderMessage" => (string)$senderMessage,
				"userName" => (string)$userName,
				"userFirstName" => (string)\Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getName(false),
				"userLastName" => (string)\Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getLastName(false),
				"userColor" => (string)\Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->getColor(),
				"userExtranet" => \Bitrix\Im\User::getInstance($params['FROM_USER_ID'])->isExtranet(),
				"messageId" => (int)$params['MESSAGE_ID'],
				"messageDate" => date('c', time()),
				"messageAuthorId" => (int)$params['FROM_USER_ID'],
				"messageText" => (string)$senderMessage,
			);
		}

		$pushText = self::PrepareMessageForPush(Array(
			'MESSAGE' => $pushText,
			'FILES' => $params['FILES'],
			'ATTACH' => $params['ATTACH'],
		));

		if (!$pushText)
			return false;

		$result = Array();
		$result['push']['params'] = Array(
			'TAG' => 'IM_MESS_'.$params['FROM_USER_ID'],
			'CATEGORY' => 'ANSWER',
			'URL' => SITE_DIR.'mobile/ajax.php?mobile_action=im_answer',
			'PARAMS' => Array(
				'RECIPIENT_ID' => $params['FROM_USER_ID']
			),
		);
		$result['module_id'] = 'im';
		$result['push']['type'] = 'message';
		$result['push']['tag'] = 'IM_MESS_'.$params['FROM_USER_ID'];
		$result['push']['sub_tag'] = 'IM_MESS';
		$result['push']['app_id'] = 'Bitrix24';
		$result['push']['message'] = $pushText;
		$result['push']['advanced_params'] = $advancedParams;

		return $result;
	}

	public static function PrepareMessageForPush($params)
	{
		if (!isset($params['MESSAGE']))
		{
			$params['MESSAGE'] = '';
		}
		$params['MESSAGE'] = trim($params['MESSAGE']);

		$pushFiles = '';
		if (isset($params['FILES']) && count($params['FILES']) > 0)
		{
			foreach ($params['FILES'] as $file)
			{
				$pushFiles .= " [".GetMessage('IM_MESSAGE_FILE').": ".$file['name']."]";
			}
			$params['MESSAGE'] .= $pushFiles;
		}

		$hasAttach = strpos($params['MESSAGE'], '[ATTACH=') !== false;

		$params['MESSAGE'] = preg_replace("/\[s\].*?\[\/s\]/i", "-", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\\[url\\](.*?)\\[\\/url\\]/i".BX_UTF_PCRE_MODIFIER, "$1", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixs".BX_UTF_PCRE_MODIFIER, "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace("/\[ATTACH=([0-9]{1,})\]/i", " [".GetMessage('IM_MESSAGE_ATTACH')."] ", $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace_callback("/\[ICON\=([^\]]*)\]/i", Array("CIMMessenger", "PrepareMessageForPushIconCallBack"), $params['MESSAGE']);
		$params['MESSAGE'] = preg_replace('#\-{54}.+?\-{54}#s', " [".GetMessage('IM_QUOTE')."] ", str_replace(array("#BR#"), Array(" "), $params['MESSAGE']));

		if (!$pushFiles && !$hasAttach && $params['ATTACH'])
		{
			$params['MESSAGE'] .= " [".GetMessage('IM_MESSAGE_ATTACH')."]";
		}

		return $params['MESSAGE'];
	}

	public static function PrepareMessageForPushIconCallBack($params)
	{
		$text = $params[1];

		$title = GetMessage('IM_MESSAGE_ICON');

		preg_match('/title\=(.*[^\s\]])/i', $text, $match);
		if ($match)
		{
			$title = $match[1];
			if (strpos($title, 'width=') !== false)
			{
				$title = substr($title, 0, strpos($title, 'width='));
			}
			if (strpos($title, 'height=') !== false)
			{
				$title = substr($title, 0, strpos($title, 'height='));
			}
			if (strpos($title, 'size=') !== false)
			{
				$title = substr($title, 0, strpos($title, 'size='));
			}
			$title = trim($title);
		}

		return '('.$title.')';
	}

	/* TMP FUNCTION */
	public static function GetCachePath($id)
	{
		return \Bitrix\Im\Common::getCacheUserPostfix($id);
	}

	function GetSonetCode($user_id, $site_id = SITE_ID)
	{
		global $DB;
		$result = array();
		$user_id = intval($user_id);

		if($user_id > 0 && IsModuleInstalled('socialnetwork'))
		{
			$strSQL = "
				SELECT CODE, SUM(CNT) CNT
				FROM b_sonet_log_counter
				WHERE USER_ID = ".$user_id."
				AND (SITE_ID = '".$site_id."' OR SITE_ID = '**')
				GROUP BY CODE
			";
			$dbRes = $DB->Query($strSQL, true, "File: ".__FILE__."<br>Line: ".__LINE__);
			while ($arRes = $dbRes->Fetch())
				$result[$arRes["CODE"]] = $arRes["CNT"];
		}

		return $result;
	}

	public static function EnableMessageCheck()
	{
		self::$enableMessageCheck++;
		return true;
	}
	public static function DisableMessageCheck()
	{
		self::$enableMessageCheck--;
		return true;
	}

	public static function IsEnabledMessageCheck()
	{
		return self::$enableMessageCheck > 0;
	}

	public static function IsMysqlDb()
	{
		global $DB;
		return strtolower($DB->type) == 'mysql';
	}

	public static function IsAdmin()
	{
		global $USER;
		return $USER->IsAdmin() || CModule::IncludeModule('bitrix24') && CBitrix24::IsPortalAdmin($USER->GetId());
	}

	public static function GetCurrentUserId()
	{
		global $USER;
		return $USER->GetID();
	}

	private static function GetEventByCounterGroup($events, $maxUserInGroup = 100)
	{
		$groups = Array();
		foreach ($events as $userId => $event)
		{
			$eventCode = $event['groupId'];
			if (!isset($groups[$eventCode]))
			{
				$groups[$eventCode]['event'] = $event;
			}
			$groups[$eventCode]['users'][] = $userId;
			$groups[$eventCode]['count'] = count($groups[$eventCode]['users']);
		}

		\Bitrix\Main\Type\Collection::sortByColumn($groups, Array('count' => SORT_DESC));

		$count = 0;
		$finalGroup = Array();
		foreach ($groups as $eventCode => $event)
		{
			if ($count >= $maxUserInGroup)
			{
				if (isset($finalGroup['other']))
				{
					$finalGroup['other']['users'] = array_unique(array_merge($finalGroup['other']['users'], $event['users']));
				}
				else
				{
					$finalGroup['other'] = $event;
					$finalGroup['other']['event']['params']['counter'] = 100;
				}
			}
			else
			{
				$finalGroup[$eventCode] = $event;
			}
			$count++;
		}

		\Bitrix\Main\Type\Collection::sortByColumn($finalGroup, Array('count' => SORT_ASC));

		return $finalGroup;
	}
}
