<?php
namespace Bitrix\Im\Replica;

use Bitrix\Main\Loader;

if (Loader::includeModule('replica'))
{
	class MessageParamHandler extends \Bitrix\Replica\Client\BaseHandler
	{
		protected $tableName = "b_im_message_param";
		protected $moduleId = "im";
		protected $className = "\\Bitrix\\Im\\Model\\MessageParamTable";
		protected $primary = array(
			"ID" => "auto_increment",
		);
		protected $predicates = array(
			"MESSAGE_ID" => "b_im_message.ID",
		);

		public function __construct()
		{
			$this->translation = array(
				"MESSAGE_ID" => "b_im_message.ID",
				"PARAM_VALUE" => array($this, "paramValueTranslation"),
			);
		}

		/**
		 * Called before log write. You may return false and not log write will take place.
		 *
		 * @param array $record Database record.
		 *
		 * @return boolean
		 */
		public function beforeLogInsert(array $record)
		{
			return $record["PARAM_NAME"] === "KEYBOARD"? false: true;
		}

		/**
		 * Method will be invoked before new database record inserted.
		 * When an array returned the insert will be cancelled and map for
		 * returned record will be added.
		 *
		 * @param array &$newRecord All fields of inserted record.
		 *
		 * @return null|array
		 */
		public function beforeInsertTrigger(array &$newRecord)
		{
			if ($newRecord["MESSAGE_ID"] <= 0)
			{
				return array("ID" => 0);
			}

			return null;
		}

		/**
		 * Returns relation depending on record values.
		 *
		 * @param array $record Database record.
		 * @return string|false
		 */
		public static function paramValueTranslation($record)
		{
			if ($record["PARAM_NAME"] === "LIKE" && $record["PARAM_VALUE"])
			{
				return "b_user.ID";
			}
			elseif ($record["PARAM_NAME"] === "URL_ID" && $record["PARAM_VALUE"])
			{
				return "b_urlpreview_metadata.ID";
			}
			return false;
		}

		/**
		 * Called before record transformed for log writing.
		 *
		 * @param array &$record Database record.
		 *
		 * @return void
		 */
		public function beforeLogFormat(array &$record)
		{
			global $USER;
			if ($record["PARAM_NAME"] !== "FILE_ID" || $record["PARAM_VALUE"] <= 0)
			{
				return;
			}

			if (!\Bitrix\Main\Loader::includeModule('disk'))
			{
				AddMessage2Log('MessageParamHandler::beforeLogFormat: failed to load disk module.');
				return;
			}

			if (!is_object($USER) || $USER->GetID() < 0)
			{
				AddMessage2Log('MessageParamHandler::beforeLogFormat: no user provided.');
				return;
			}

			/** @var \Bitrix\Disk\File $file */
			$fileId = $record["PARAM_VALUE"];
			$userId = $USER->GetID();
			$file = \Bitrix\Disk\File::loadById($fileId);
			if (!$file)
			{
				AddMessage2Log('MessageParamHandler::beforeLogFormat: file ('.$fileId.') not found for user ('.$userId.').');
				return;
			}

			$url = \CIMDisk::GetFileLink($file);
			if (!$url)
			{
				AddMessage2Log('MessageParamHandler::beforeLogFormat: failed to get external link for file ('.$fileId.').');
				AddMessage2Log($file->getErrors());
				return;
			}

			$fileName =  $file->getName();
			$fileSize = $file->getSize();

			$attach = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);
			if (\Bitrix\Disk\TypeFile::isImage($file))
			{
				$source = $file->getFile();
				if ($source)
				{
					$attach->AddImages([[
						"NAME" => $fileName,
						"LINK" => $url,
						"WIDTH" => (int)$source["WIDTH"],
						"HEIGHT" => (int)$source["HEIGHT"],
					]]);
				}
			}

			if ($attach->IsEmpty())
			{
				$attach->AddFiles([[
					"NAME" => $fileName,
					"LINK" => $url,
					"SIZE" => $fileSize,
				]]);
			}

			$record["PARAM_NAME"] = 'ATTACH';
			$record["PARAM_VALUE"] = 1;
			$record["PARAM_JSON"] = $attach->GetJSON();
		}

		/**
		 * Method will be invoked after new database record inserted.
		 *
		 * @param array $newRecord All fields of inserted record.
		 *
		 * @return void
		 */
		public function afterInsertTrigger(array $newRecord)
		{
			$id = intval($newRecord['MESSAGE_ID']);

			if (!\Bitrix\Main\Loader::includeModule('pull'))
				return;

			$message = \CIMMessenger::GetById($id, Array('WITH_FILES' => 'Y'));
			if (!$message)
				return;

			if ($newRecord['PARAM_NAME'] === 'LIKE' && $newRecord["PARAM_VALUE"])
			{
				$like = $message['PARAMS']['LIKE'];

				$result = \Bitrix\Im\Model\ChatTable::getList(Array(
					'filter'=>Array(
						'=ID' => $message['CHAT_ID']
					)
				));
				$chat = $result->fetch();

				$relations = \CIMMessenger::GetRelationById($id);
				if (!isset($relations[$newRecord["PARAM_VALUE"]]))
					return;

				if ($message['AUTHOR_ID'] > 0 && $message['AUTHOR_ID'] != $newRecord["PARAM_VALUE"])
				{
					$message['MESSAGE'] = str_replace('<br />', ' ', \Bitrix\Im\Text::parse($message['MESSAGE']));
					$message['MESSAGE'] = preg_replace("/\[s\].*?\[\/s\]/i", "", $message['MESSAGE']);
					$message['MESSAGE'] = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $message['MESSAGE']);
					$message['MESSAGE'] = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $message['MESSAGE']);
					$message['MESSAGE'] = preg_replace("/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/i", "$2", $message['MESSAGE']);
					$message['MESSAGE'] = preg_replace("/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/i", "$2", $message['MESSAGE']);
					$message['MESSAGE'] = preg_replace("/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/i", "$2", $message['MESSAGE']);
					$message['MESSAGE'] = preg_replace("/------------------------------------------------------(.*)------------------------------------------------------/mi", " [".GetMessage('IM_QUOTE')."] ", str_replace(array("#BR#"), Array(" "), $message['MESSAGE']));

					if (count($message['FILES']) > 0 && mb_strlen($message['MESSAGE']) < 200)
					{
						foreach ($message['FILES'] as $file)
						{
							$file = " [".GetMessage('IM_MESSAGE_FILE').": ".$file['name']."]";
							if (mb_strlen($message['MESSAGE'].$file) > 200)
								break;

							$message['MESSAGE'] .= $file;
						}
						$message['MESSAGE'] = trim($message['MESSAGE']);
					}

					$isChat = $chat && $chat['TITLE'] <> '';

					$dot = mb_strlen($message['MESSAGE']) >= 200? '...': '';
					$message['MESSAGE'] = mb_substr($message['MESSAGE'], 0, 199).$dot;
					$message['MESSAGE'] = $message['MESSAGE'] <> ''? $message['MESSAGE']: '-';

					$arMessageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $message['AUTHOR_ID'],
						"FROM_USER_ID" => $newRecord["PARAM_VALUE"],
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "im",
						"NOTIFY_EVENT" => "like",
						"NOTIFY_TAG" => "RATING|IM|".($isChat? 'G':'P')."|".($isChat? $chat['ID']: $newRecord["PARAM_VALUE"])."|".$id,
						"NOTIFY_MESSAGE" => GetMessage($isChat? 'IM_MESSAGE_LIKE': 'IM_MESSAGE_LIKE_PRIVATE', Array(
							'#MESSAGE#' => $message['MESSAGE'],
							'#TITLE#' => $chat['TITLE']
						))
					);
					\CIMNotify::Add($arMessageFields);
				}

				$arPullMessage = Array(
					'id' => $id,
					'chatId' => $relations[$newRecord["PARAM_VALUE"]]['CHAT_ID'],
					'senderId' => $newRecord["PARAM_VALUE"],
					'users' => $like
				);

				foreach ($relations as $rel)
				{
					\Bitrix\Pull\Event::add($rel['USER_ID'], Array(
						'module_id' => 'im',
						'command' => 'messageLike',
						'params' => $arPullMessage,
						'extra' => \Bitrix\Im\Common::getPullExtra()
					));
				}
			}
			else if (in_array($newRecord['PARAM_NAME'], Array('ATTACH', 'URL_ID', 'IS_DELETED', 'IS_EDITED')))
			{
				\CIMMessageParam::SendPull($id, Array($newRecord['PARAM_NAME']));
			}
		}

		public function afterDeleteTrigger(array $oldRecord)
		{
			\CIMMessageParam::SendPull($oldRecord['MESSAGE_ID'], Array($oldRecord['PARAM_NAME']));
		}
	}
}
