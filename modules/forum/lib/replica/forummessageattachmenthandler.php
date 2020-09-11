<?php
namespace Bitrix\Forum\Replica;

use Bitrix\Main\Loader;

if (Loader::includeModule('replica'))
{
	class ForumMessageAttachmentHandler extends \Bitrix\Replica\Client\AttachmentHandler
	{
		protected $moduleId = "forum";
		protected $relation = "b_forum_message.ATTACH_ID";

		protected $executeEventEntity = "ForumMessage";
		protected $parentRelation = "b_forum_message.ID";
		protected $diskConnectorString = "forum_message";

		protected $dataFields = array("POST_MESSAGE", "POST_MESSAGE_HTML");

		/**
		 * Adds attachment to user field value for given entity.
		 *
		 * @param integer $messageId Message identifier.
		 * @param integer $diskAttachId Disk attachment identifier.
		 *
		 * @return void
		 */
		public static function updateUserField($messageId, $diskAttachId)
		{
			global $USER_FIELD_MANAGER;
			$ufValue = $USER_FIELD_MANAGER->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", $messageId);
			if (!$ufValue)
			{
				$ufValue = array($diskAttachId);
			}
			elseif (is_array($ufValue))
			{
				$ufValue[] = $diskAttachId;
			}
			else
			{
				$ufValue = $diskAttachId;
			}
			$USER_FIELD_MANAGER->Update("FORUM_MESSAGE", $messageId, array("UF_FORUM_MESSAGE_DOC" => $ufValue));
		}

		/**
		 * Returns array of attachments for given entity.
		 *
		 * @param integer $messageId Message identifier.
		 *
		 * @return array[]\Bitrix\Disk\AttachedObject
		 */
		public static function getUserField($messageId)
		{
			$result = array();

			$messageList = \CForumMessage::getList(
				array(),
				array(
					"ID" => $messageId,
				),
				false, 0,
				array("SELECT" => array("UF_FORUM_MESSAGE_DOC"))
			);
			$messageInfo = $messageList->fetch();

			if (
				$messageInfo
				&& $messageInfo["UF_FORUM_MESSAGE_DOC"]
				&& $messageInfo["UF_FORUM_MESSAGE_DOC"]["VALUE"]
				&& \Bitrix\Main\Loader::includeModule('disk')
			)
			{
				foreach ($messageInfo["UF_FORUM_MESSAGE_DOC"]["VALUE"] as $attachId)
				{
					$attachedObject = \Bitrix\Disk\AttachedObject::getById($attachId, array('OBJECT'));
					if ($attachedObject && $attachedObject->getFile())
					{
						$result[$attachId] = $attachedObject;
					}
				}
			}

			return $result;
		}

		/**
		 * Remote event handler.
		 *
		 * @param \Bitrix\Main\Event $event Contains two parameters: 0 - id, 1 - data.
		 *
		 * @return void
		 * @see \Bitrix\Replica\Client\AttachmentHandler::onAfterAdd
		 * @see \Bitrix\Replica\Client\AttachmentHandler::onAfterUpdate
		 */
		public function onExecuteDescriptionFix(\Bitrix\Main\Event $event)
		{
			$parameters = $event->getParameters();
			$messId = $parameters[0];
			$fields = $parameters[1];
			$connection = \Bitrix\Main\Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();

			if ($this->replaceGuidsWithFiles($fields))
			{
				$update = $sqlHelper->prepareUpdate("b_forum_message", $fields);
				if ($update[0] <> '')
				{
					$sql = "UPDATE ".$sqlHelper->quote("b_forum_message")." SET ".$update[0]." WHERE ID = ".$messId;
					$connection->query($sql);

					if (\Bitrix\Main\Loader::includeModule('socialnetwork'))
					{
						$dbLogComment = \CSocNetLogComments::GetList(
							array("ID" => "DESC"),
							array(
								"EVENT_ID" => 'tasks_comment',
								"SOURCE_ID" => $messId,
							),
							false,
							false,
							array("ID", "LOG_ID")
						);
						$arLogComment = $dbLogComment->fetch();
						if ($arLogComment)
						{
							$parser = new \CTextParser();
							$parser->allow = array(
								"HTML" => 'Y',
								"ANCHOR" => 'Y',
								"BIU" => 'Y',
								"IMG" => "Y",
								"VIDEO" => "Y",
								"LIST" => 'N',
								"QUOTE" => 'Y',
								"CODE" => 'Y',
								"FONT" => 'Y',
								"SMILES" => "N",
								"UPLOAD" => 'N',
								"NL2BR" => 'N',
								"TABLE" => "Y",
							);
							$arFieldsForSocnet = array(
								"LOG_ID" => intval($arLogComment["LOG_ID"]),
								"MESSAGE" => $fields["POST_MESSAGE"],
								"TEXT_MESSAGE" => $parser->convert4mail($fields["POST_MESSAGE"]),
							);

							$ufFileID = array();
							$dbAddedMessageFiles = \CForumFiles::GetList(array("ID" => "ASC"), array("MESSAGE_ID" => $messId));
							while ($arAddedMessageFiles = $dbAddedMessageFiles->Fetch())
							{
								$ufFileID[] = $arAddedMessageFiles["FILE_ID"];
							}

							if (count($ufFileID) > 0)
							{
								$arFieldsForSocnet["UF_SONET_COM_FILE"] = $ufFileID;
							}

							$ufDocID = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFieldValue("FORUM_MESSAGE", "UF_FORUM_MESSAGE_DOC", intval($messId), LANGUAGE_ID);
							if ($ufDocID)
							{
								$arFieldsForSocnet["UF_SONET_COM_DOC"] = $ufDocID;
							}

							\CSocNetLogComments::Update($arLogComment["ID"], $arFieldsForSocnet);
						}
					}
				}
			}
		}
	}
}
