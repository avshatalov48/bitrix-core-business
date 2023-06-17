<?php

use Bitrix\Disk;
use Bitrix\Disk\Document\OnlyOffice\Templates\CreateDocumentByCallTemplateScenario;
use \Bitrix\Im as IM;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CIMDisk
{
	const MODULE_ID = 'im';

	const PATH_TYPE_SHOW = 'show';
	const PATH_TYPE_PREVIEW = 'preview';
	const PATH_TYPE_DOWNLOAD = 'download';

	/**
	 * Returns IM's specialized storage.
	 *
	 * @return \Bitrix\Disk\Storage|false
	 */
	public static function GetStorage()
	{
		if (!self::Enabled())
		{
			return false;
		}

		$storageModel = false;
		if ($storageId = self::GetStorageId())
		{
			$storageModel = \Bitrix\Disk\Storage::loadById($storageId);
			if (!$storageModel || $storageModel->getModuleId() != self::MODULE_ID)
			{
				$storageModel = false;
			}
		}

		if (!$storageModel)
		{
			$data = [
				'NAME' => Loc::getMessage('IM_DISK_STORAGE_TITLE'),
				'USE_INTERNAL_RIGHTS' => 1,
				'MODULE_ID' => self::MODULE_ID,
				'ENTITY_TYPE' => IM\Disk\ProxyType\Im::className(),
				'ENTITY_ID' => self::MODULE_ID,
			];

			$driver = \Bitrix\Disk\Driver::getInstance();

			// allow access for all on the top folder
			$storageModel = $driver->addStorageIfNotExist($data);
			if ($storageModel)
			{
				self::SetStorageId($storageModel->getId());
			}
			else
			{
				$storageModel = false;
			}
		}

		return $storageModel;
	}

	/**
	 * @param int $chatId
	 * @param array $files
	 * @param string $text
	 * @param false $linesSilentMode
	 * @return array|false
	 */
	public static function UploadFileRegister($chatId, $files, $text = '', $linesSilentMode = false)
	{
		if ((int)$chatId <= 0 || empty($files))
		{
			return false;
		}

		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);
		if (!$chatRelation[self::GetUserId()])
		{
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return false;
		}

		$result = [];
		$result['FILE_ID'] = [];
		$messageFileId = [];
		$filesModels = [];
		foreach ($files as $fileId => $fileData)
		{
			if (!$fileData['mimeType'])
			{
				$fileData['mimeType'] = "binary";
			}
			if (!$fileData['name'])
			{
				continue;
			}
			$newFile = $folderModel->addBlankFile(Array(
				'NAME' => $fileData['name'],
				'SIZE' => $fileData['size'],
				'CREATED_BY' => self::GetUserId(),
				'MIME_TYPE' => $fileData['mimeType'],
			), Array(), true);
			if ($newFile)
			{
				$result['FILE_ID'][$fileId]['TMP_ID'] = $fileId;
				$result['FILE_ID'][$fileId]['FILE_ID'] = $newFile->getId();
				$result['FILE_ID'][$fileId]['FILE_NAME'] = $newFile->getName();
				$filesModels[] = $newFile;

				$messageFileId[] = $newFile->getId();
			}
			else
			{
				$result['FILE_ID'][$fileId]['TMP_ID'] = $fileId;
				$result['FILE_ID'][$fileId]['FILE_ID'] = 0;
			}
		}
		if (empty($messageFileId))
		{
			return false;
		}

		$result['MESSAGE_ID'] = 0;
		$arChat = \CIMChat::GetChatData(Array('ID' => $chatId));
		$ar = Array(
			"TO_CHAT_ID" => $chatId,
			"FROM_USER_ID" => self::GetUserId(),
			"MESSAGE_TYPE" => $arChat['chat'][$chatId]['message_type'],
			"SILENT_CONNECTOR" => $linesSilentMode?'Y':'N',
			"PARAMS" => Array(
				'FILE_ID' => $messageFileId
			)
		);

		$text = trim($text);
		if ($text)
		{
			$ar['MESSAGE'] = $text;
		}
		$messageId = \CIMMessage::Add($ar);
		if ($messageId)
		{
			$message = new Bitrix\Im\V2\Message([
				'ID' => $messageId,
				'CHAT_ID' => $chatId,
				'AUTHOR_ID' => self::GetUserId()
			]);
			(new IM\V2\Link\File\FileService())->saveFilesFromMessage($filesModels, $message);
			$result['MESSAGE_ID'] = $messageId;
		}
		else
		{
			if ($e = $GLOBALS["APPLICATION"]->GetException())
			{
				$result['MESSAGE_ERROR'] = $e->GetString();
			}
		}

		return $result;
	}

	/**
	 * @param string $hash
	 * @param array $file
	 * @param \Bitrix\Main\UI\Uploader\Log $package
	 * @param \Bitrix\Main\UI\Uploader\Log $upload
	 * @param string $error
	 * @return bool
	 */
	public static function UploadFile($hash, &$file, &$package, &$upload, &$error)
	{
		$post = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->toArray();
		$post['PARAMS'] = \CUtil::JsObjectToPhp($post['REG_PARAMS']);
		$post['PARAMS'] = \Bitrix\Main\Text\Encoding::convertEncoding($post['PARAMS'], 'UTF-8', LANG_CHARSET);
		$post['MESSAGE_HIDDEN'] = $post['REG_MESSAGE_HIDDEN'] == 'Y'? 'Y': 'N';
		$post['PARAMS']['TEXT'] = $post['PARAMS']['TEXT']? trim($post['PARAMS']['TEXT']): '';

		$chatId = (int)$post['CHAT_ID'];
		if ($chatId <= 0)
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E100)';
			return false;
		}

		$chat = \Bitrix\Im\Chat::getById($chatId, ['CHECK_ACCESS' => 'Y']);
		if (!$chat)
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E101)';
			return false;
		}

		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);
		if (!$chatRelation[self::GetUserId()])
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E102)';
			return false;
		}

		if ($chat['ENTITY_TYPE'] === 'ANNOUNCEMENT' && $chatRelation[self::GetUserId()]['MANAGER'] !== 'Y')
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E103)';
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E104)';
			return false;
		}

		if (!$file["files"]["default"])
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E106)';
			return false;
		}

		$fileModel = $folderModel->uploadFile(
			$file['files']['default'],
			[
				'NAME' => $file['name'],
				'CREATED_BY' => self::GetUserId()
			],
			[],
			true
		);

		if (!$fileModel || !$fileModel->getId())
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E107)';
			return false;
		}

		$fileTmpId = $file["id"];
		$messageTmpId = $file["regTmpMessageId"];
		$isMessageHidden = $file["regHiddenMessageId"] === 'Y';

		if (!$fileTmpId || !$messageTmpId)
		{
			$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E108)';
			return false;
		}
		$uploadRealResult = self::UploadFileFromDisk(
			$chatId,
			['upload'.$fileModel->getId()],
			$post['PARAMS']['TEXT'],
			[
				'LINES_SILENT_MODE' => $isMessageHidden,
				'TEMPLATE_ID' => $messageTmpId,
				'FILE_TEMPLATE_ID' => $fileTmpId
			]
		);

		if (!$uploadRealResult)
		{
			$error = '';
			if ($e = $GLOBALS["APPLICATION"]->GetException())
			{
				$error = $e->GetString();
			}
			if ($error == '')
			{
				$error = Loc::getMessage('IM_DISK_ERR_UPLOAD').' (E109)';
			}

			return false;
		}

		$fileModel = $folderModel->getChild(['ID' => $fileModel->getId()]);

		$file['fileParams'] = self::GetFileParams($chatId, $fileModel);
		$file['fileParams']['date'] = date('c', $file['fileParams']['date']->getTimestamp());

		foreach(\GetModuleEvents("im", "OnAfterFileUpload", true) as $arEvent)
		{
			\ExecuteModuleEventEx($arEvent, [[
				'CHAT_ID' => $chatId,
				'FILE_ID' => $fileModel->getId(),
				'MESSAGE_ID' => $uploadRealResult['MESSAGE_ID'],
				'MESSAGE_OUT' => $post['PARAMS']['TEXT'],
				'MESSAGE_HIDDEN' => $isMessageHidden,
				'FILE' => $file['fileParams'],
			]]);
		}

		return true;
	}

	/**
	 * @param int $chatId
	 * @param array $files
	 * @param array $messages
	 * @return array|false
	 */
	public static function UploadFileUnRegister($chatId, $files, $messages)
	{
		if ((int)$chatId <= 0 || empty($files))
		{
			return false;
		}

		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);
		if (!$chatRelation[self::GetUserId()])
		{
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return false;
		}

		$result = [];
		$result['CHAT_ID'] = $chatId;
		$result['FILE_ID'] = [];
		$result['MESSAGE_ID'] = [];
		foreach ($files as $fileTmpId => $fileId)
		{
			$fileModel = \Bitrix\Disk\File::getById($fileId);
			if (
				!$fileModel
				|| $fileModel->getParentId() != $folderModel->getId()
				|| $fileModel->getCreatedBy() != self::GetUserId()
			)
			{
				continue;
			}
			$fileModel->delete(self::GetUserId());
			$result['FILE_ID'][$fileTmpId] = $fileId;
		}
		foreach ($messages as $fileTmpId => $messageId)
		{
			if (!isset($result['FILE_ID'][$fileTmpId]))
			{
				continue;
			}

			$CIMMessage = new \CIMMessage();
			$arMessage = $CIMMessage->GetMessage($messageId);
			if ($arMessage['AUTHOR_ID'] != self::GetUserId())
			{
				continue;
			}
			\CIMMessage::Delete($messageId);
			$result['MESSAGE_ID'][$fileTmpId] = $messageId;
		}
		if (empty($result['FILE_ID']) && empty($result['MESSAGE_ID']))
		{
			return false;
		}

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			$pullMessage = Array(
				'module_id' => 'im',
				'command' => 'fileUnRegister',
				'params' => Array(
					'chatId' => $result['CHAT_ID'],
					'files' => $result['FILE_ID'],
					'messages' => $result['MESSAGE_ID'],
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			);
			\Bitrix\Pull\Event::add(array_keys($chatRelation), $pullMessage);

			$orm = \Bitrix\Im\Model\ChatTable::getById($result['CHAT_ID']);
			$chat = $orm->fetch();
			if ($chat['TYPE'] == \IM_MESSAGE_OPEN || $chat['TYPE'] == \IM_MESSAGE_OPEN_LINE)
			{
				\CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
			}
		}

		return $result;
	}

	/**
	 * @param int $chatId
	 * @param int $fileId
	 * @return bool
	 */
	public static function DeleteFile($chatId, $fileId)
	{
		if ((int)$chatId <= 0 || (int)$fileId <= 0)
		{
			return false;
		}

		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);
		if (!$chatRelation[self::GetUserId()])
		{
			return false;
		}

		$folderModel = self::getFolderModel($chatId, false);
		if (!$folderModel)
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId);
		if (!$fileModel || $fileModel->getParentId() != $folderModel->getId())
		{
			return false;
		}

		/** global \CUser $USER */
		global $USER;
		if (
			$fileModel->getCreatedBy() == self::GetUserId()
			|| $USER->IsAdmin()
		)
		{
			// allow deleting only owned files
			$fileModel->delete(self::GetUserId());
			$notifyUsers = array_keys($chatRelation);
		}
		else
		{
			$driver = \Bitrix\Disk\Driver::getInstance();
			$rightsManager = $driver->getRightsManager();

			// hide file from user by access disabling
			$accessCodes = [
				// keep previous scheme with Uxx access code
				[
					'ACCESS_CODE' => 'U'.self::GetUserId(),
					'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_FULL),
					'NEGATIVE' => 1,
				]
			];
			$rightsManager->append($fileModel, $accessCodes);

			$notifyUsers = [self::GetUserId()];
		}

		$fileService = new IM\V2\Link\File\FileService();
		if (\Bitrix\Main\Loader::includeModule('pull') && !$fileService->isMigrationFinished())
		{
			$pullMessage = Array(
				'module_id' => 'im',
				'command' => 'fileDelete',
				'params' => Array(
					'chatId' => $chatId,
					'fileId' => $fileId
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			);
			\Bitrix\Pull\Event::add($notifyUsers, $pullMessage);

			$orm = \Bitrix\Im\Model\ChatTable::getById($chatId);
			$chat = $orm->fetch();
			if ($chat['TYPE'] == \IM_MESSAGE_OPEN || $chat['TYPE'] == \IM_MESSAGE_OPEN_LINE)
			{
				\CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
			}
		}

		return true;
	}

	/**
	 * @param int $chatId
	 * @param array $files
	 * @param string $text
	 * @param array $options
	 * @param bool $robot
	 * @return array|bool
	 */
	public static function UploadFileFromDisk($chatId, $files, $text = '', $options = [], $robot = false)
	{
		if ((int)$chatId <= 0 || empty($files))
		{
			return false;
		}

		$orm = \Bitrix\Im\Model\ChatTable::getList([
			'filter'=>[
				'=ID' => $chatId
			]
		]);
		$chat = $orm->fetch();
		if (!$chat)
		{
			return false;
		}

		if (isset($options['USER_ID']))
		{
			$userId = (int)$options['USER_ID'];
		}
		else
		{
			$userId = self::GetUserId();
			if (!$userId)
			{
				return false;
			}
		}

		$skipUserCheck = isset($options['SKIP_USER_CHECK']) && $options['SKIP_USER_CHECK'] === true;
		$linesSilentMode = isset($options['LINES_SILENT_MODE']) && $options['LINES_SILENT_MODE'] === true;
		$makeSymlink = isset($options['SYMLINK']) && $options['SYMLINK'] === true;
		$templateId = isset($options['TEMPLATE_ID']) && $options['TEMPLATE_ID'] <> '' ? $options['TEMPLATE_ID'] : '';
		$fileTemplateId = isset($options['FILE_TEMPLATE_ID']) && $options['FILE_TEMPLATE_ID'] <> '' ? $options['FILE_TEMPLATE_ID'] : '';
		$attach = $options['ATTACH'] ?? null;
		$params = isset($options['PARAMS']) && is_array($options['PARAMS']) ? $options['PARAMS'] : null;

		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);

		if ($chat['ENTITY_TYPE'] !== 'LIVECHAT' && $userId > 0 && !$skipUserCheck)
		{
			if (!$chatRelation[$userId])
			{
				return false;
			}

			if (
				$chat['ENTITY_TYPE'] === 'ANNOUNCEMENT'
				&& $chatRelation[$userId]['MANAGER'] !== 'Y'
			)
			{
				return false;
			}
		}

		$result = [];
		$result['FILES'] = [];
		$result['DISK_ID'] = [];
		$result['FILE_MODELS'] = [];
		foreach ($files as $fileId)
		{
			if (mb_substr($fileId, 0, 6) == 'upload')
			{
				$newFile = self::IncreaseFileVersionDisk($chatId, mb_substr($fileId, 6), $skipUserCheck? 0: $userId);
			}
			else
			{
				$newFile = self::SaveFromLocalDisk($chatId, mb_substr($fileId, 4), $makeSymlink, $skipUserCheck? 0: $userId);
			}

			if ($newFile)
			{
				$result['FILES'][$fileId] = self::GetFileParams($chatId, $newFile);
				$result['DISK_ID'][] = $newFile->getId();
				$result['FILE_MODELS'][$fileId] = $newFile;

				if ($robot)
				{
					if ($userId)
					{
						// temporary - waiting for fix in Disk
						$recentItem = \Bitrix\Disk\Internals\RecentlyUsedTable::getList([
							'select' => ['ID'],
							'filter' => [
								'=USER_ID' => $userId,
								'=OBJECT_ID' => $newFile->getId()
							]
						])->fetch();

						if ($recentItem)
						{
							\Bitrix\Disk\Internals\RecentlyUsedTable::delete($recentItem['ID']);
						}
					}
				}
				else if (!$skipUserCheck)
				{
					if ($chat['ENTITY_TYPE'] == 'LINES')
					{
						if ($userId)
						{
							\Bitrix\Disk\Driver::getInstance()->getRecentlyUsedManager()->push($userId, $newFile);
						}
					}
					else if ($chat['ENTITY_TYPE'] != 'LIVECHAT')
					{
						foreach ($chatRelation as $relation)
						{
							if ($relation['MESSAGE_TYPE'] != \IM_MESSAGE_PRIVATE)
							{
								break;
							}

							if ($userId == $relation['USER_ID'])
							{
								continue;
							}

							\Bitrix\Disk\Driver::getInstance()->getRecentlyUsedManager()->push($relation['USER_ID'], $newFile);
						}
					}
				}
			}
			else
			{
				$result['FILES'][$fileId]['id'] = 0;
			}
		}
		if (empty($result['DISK_ID']))
		{
			return false;
		}

		$result['MESSAGE_ID'] = 0;

		$ar = [
			"TO_CHAT_ID" => $chatId,
			"FROM_USER_ID" => $userId,
			"MESSAGE_TYPE" => $chat['TYPE'],
			"SILENT_CONNECTOR" => $linesSilentMode?'Y':'N',
			"SKIP_USER_CHECK" => ($skipUserCheck || !$userId || $chat['ENTITY_TYPE'] == 'LIVECHAT'),
			"TEMPLATE_ID" => $templateId,
			"FILE_TEMPLATE_ID" => $fileTemplateId,
		];

		if ($chat['ENTITY_TYPE'] == 'LIVECHAT')
		{
			[$lineId] = explode("|", $chat['ENTITY_ID']);
			$ar["EXTRA_PARAMS"] = [
				"CONTEXT" => "LIVECHAT",
				"LINE_ID" => $lineId
			];
			//TODO: fix 0135872
			//$ar['SKIP_CONNECTOR'] = 'Y';
		}

		$ar['FILES'] = $result['DISK_ID'];

		if ($params)
		{
			$ar['PARAMS'] = $params;
		}

		if ($attach)
		{
			$ar['ATTACH'] = $attach;
		}

		$text = trim($text);
		if ($text)
		{
			$ar["MESSAGE"] = $text;
		}

		$messageId = \CIMMessage::Add($ar);
		if (!$messageId)
		{
			foreach ($result['FILE_MODELS'] as $file)
			{
				$file->delete($userId);
			}
			return false;
		}

		$result['MESSAGE_ID'] = $messageId;

		$message = new IM\V2\Message([
			'ID' => $messageId,
			'CHAT_ID' => $chatId,
			'AUTHOR_ID' => $userId,
		]);
		(new IM\V2\Link\File\FileService())->saveFilesFromMessage($result['FILE_MODELS'], $message);

		if (
			!$robot
			&& !$linesSilentMode
			&& ($chat['ENTITY_TYPE'] == 'LINES' || $chat['ENTITY_TYPE'] == 'LIVECHAT')
		)
		{
			$fileIds = array_map(function($item){
				return 'disk' . $item;
			}, $result['DISK_ID']);

			$uploadResult = false;

			if ($chat['ENTITY_TYPE'] == 'LIVECHAT' && \Bitrix\Main\Loader::includeModule('imopenlines'))
			{
				[$lineId, $clientUserId] = explode("|", $chat['ENTITY_ID']);

				$session = new \Bitrix\Imopenlines\Session();
				if ($session->load([
					'USER_CODE' => 'livechat|'.$lineId.'|'.$chat['ID'].'|'.$clientUserId,
					'DEFERRED_JOIN' => 'Y',
				]))
				{
					if ($session->isNowCreated())
					{
						\Bitrix\ImOpenLines\Connector::saveCustomData($session->getData('CHAT_ID'), $_SESSION['LIVECHAT']['CUSTOM_DATA']);

						$session->joinUser();

						$messageParams = [
							'IMOL_SID' => $session->getData('ID'),
							"IMOL_FORM" => "welcome",
							"TYPE" => "lines",
							"COMPONENT_ID" => "bx-imopenlines-message",
						];
						\CIMMessageParam::Set($messageId, $messageParams);
						\CIMMessageParam::SendPull($messageId, array_keys($messageParams));
					}
					$session->getData('CHAT_ID');
					//TODO: fix 0135872
					/*$uploadResult = self::UploadFileFromDisk(
						$session->getData('CHAT_ID'),
						$fileIds,
						$text,
						['USER_ID' => $userId],
						true
					);*/
				}
			}

			if (
				!empty($uploadResult) &&
				$uploadResult['MESSAGE_ID'] &&
				$result['MESSAGE_ID']
			)
			{
				\Bitrix\Im\Model\MessageParamTable::add([
					"MESSAGE_ID" => $result['MESSAGE_ID'],
					"PARAM_NAME" => 'CONNECTOR_MID',
					"PARAM_VALUE" => $uploadResult['MESSAGE_ID']
				]);
				\Bitrix\Im\Model\MessageParamTable::add([
					"MESSAGE_ID" => $uploadResult['MESSAGE_ID'],
					"PARAM_NAME" => 'CONNECTOR_MID',
					"PARAM_VALUE" => $result['MESSAGE_ID']
				]);

				\CIMMessageParam::SendPull($result['MESSAGE_ID'], ['CONNECTOR_MID']);
				\CIMMessageParam::SendPull($uploadResult['MESSAGE_ID'], ['CONNECTOR_MID']);

				$event = new \Bitrix\Main\Event("imopenlines", "OnLivechatUploadFile", ['FILES' => $uploadResult['DISK_ID']]);
				$event->send();
			}
		}

		return $result;
	}

	/**
	 * @param int $chatId
	 * @param int[] $files
	 * @return int[]|false
	 */
	public static function UploadFileFromMain($chatId, $files)
	{
		if ((int)$chatId <= 0 || empty($files))
		{
			return false;
		}

		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);
		if (!$chatRelation)
		{
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return false;
		}

		$messageFileId = [];
		foreach ($files as $fileId)
		{
			$res = \CFile::GetByID($fileId);
			$file = $res->Fetch();
			if (!$file)
			{
				continue;
			}

			if (empty($file['ORIGINAL_NAME']))
			{
				$fileName = $file['FILE_NAME'];
			}
			else
			{
				$fileName = $file['ORIGINAL_NAME'];
			}

			$fileName = \Bitrix\Disk\Ui\Text::correctFilename($fileName);
			$newFile = $folderModel->addFile(array(
				'NAME' => $fileName,
				'FILE_ID' => $fileId,
				'SIZE' => $file['FILE_SIZE'],
				'CREATED_BY' => \Bitrix\Disk\SystemUser::SYSTEM_USER_ID,
			), Array(), true);
			if ($newFile)
			{
				$newFile->increaseGlobalContentVersion();
				$messageFileId[] = $newFile->getId();
			}
		}

		return !empty($messageFileId) ? $messageFileId : false;
	}

	/**
	 * @param int $fileId
	 * @return array{FILE: \Bitrix\Disk\File, FOLDER: \Bitrix\Disk\Folder}|false
	 */
	public static function SaveToLocalDisk($fileId)
	{
		if (!self::Enabled())
		{
			return false;
		}

		if ((int)$fileId <= 0)
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId, array('STORAGE'));
		if (!$fileModel)
		{
			return false;
		}
		if ($fileModel instanceof \Bitrix\Disk\FileLink)
		{
			$fileModel = $fileModel->getRealObject();
			if (!$fileModel)
			{
				return false;
			}
		}

		$storageModel = $fileModel->getStorage();
		if (!$storageModel)
		{
			return false;
		}

		if (!$fileModel->canRead($storageModel->getCurrentUserSecurityContext()))
		{
			return false;
		}

		$folderModel = self::GetLocalDiskSavedModel();
		if (!$folderModel)
		{
			return false;
		}

		$newFileModel = $fileModel->copyTo($folderModel, self::GetUserId(), true);
		if (!$newFileModel)
		{
			return false;
		}

		return [
			'FILE' => $newFileModel,
			'FOLDER' => $folderModel,
		];
	}

	/**
	 * @param int $chatId
	 * @param int $fileId
	 * @param int|null $userId
	 * @return \Bitrix\Disk\File|false
	 */
	public static function IncreaseFileVersionDisk($chatId, $fileId, ?int $userId = null)
	{
		if (!self::Enabled())
		{
			return false;
		}

		if ((int)$fileId <= 0 || (int)$chatId <= 0)
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId, array('STORAGE'));
		if (!$fileModel)
		{
			return false;
		}

		$storageModel = $fileModel->getStorage();
		if (!$storageModel)
		{
			return false;
		}

		$securityContext = null;
		if (is_null($userId))
		{
			$securityContext = $storageModel->getCurrentUserSecurityContext();
		}
		else if ($userId > 0)
		{
			$securityContext = $storageModel->getSecurityContext($userId);
		}

		if ($securityContext && !$fileModel->canRead($securityContext))
		{
			return false;
		}

		$fileModel->increaseGlobalContentVersion();

		return $fileModel;
	}

	/**
	 * @param int $chatId
	 * @param int $fileId
	 * @param bool $symlink
	 * @param int|null $userId
	 * @return \Bitrix\Disk\File|false
	 */
	public static function SaveFromLocalDisk($chatId, $fileId, bool $symlink = false, ?int $userId = null)
	{
		if (!self::Enabled())
		{
			return false;
		}

		if ((int)$fileId <= 0 || (int)$chatId <= 0)
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId, array('STORAGE'));
		if (!$fileModel)
		{
			return false;
		}
		if ($fileModel instanceof \Bitrix\Disk\FileLink)
		{
			$fileModel = $fileModel->getRealObject();
			if (!$fileModel)
			{
				return false;
			}
		}

		$storageModel = $fileModel->getStorage();
		if (!$storageModel)
		{
			return false;
		}

		$securityContext = null;
		if (is_null($userId))
		{
			$securityContext = $storageModel->getCurrentUserSecurityContext();
		}
		else if ($userId > 0)
		{
			$securityContext = $storageModel->getSecurityContext($userId);
		}

		if ($securityContext && !$fileModel->canRead($securityContext))
		{
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return false;
		}

		if ($symlink)
		{
			$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;
			$rightsManager = \Bitrix\Disk\Driver::getInstance()->getRightsManager();

			$rightsManager->append(
				$fileModel,
				[[
					// allow reading for access code `CHATxxx`
					'ACCESS_CODE' => $accessProvider->generateAccessCode($chatId),
					'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_READ)
				]]
			);

			$newFileModel = $folderModel->addFileLink(
				$fileModel,
				[
					'CREATED_BY' => (int)$userId,
					'GLOBAL_CONTENT_VERSION' => 1
				],
				[],// link inherits access rights from parent folder
				true
			);
		}
		else
		{
			$newFileModel = $fileModel->copyTo($folderModel, (int)$userId, true);
		}

		if (!$newFileModel)
		{
			return false;
		}

		$newFileModel->increaseGlobalContentVersion();

		return $newFileModel;
	}

	/**
	 * @param int $chatId
	 * @param int $fileId
	 * @param int|null $userId
	 * @return bool
	 */
	public static function RecordShare(int $chatId, int $fileId, ?int $userId = null): bool
	{
		if (!self::Enabled())
		{
			return false;
		}

		if ($chatId <= 0 || $fileId <= 0)
		{
			return false;
		}

		$chat = \Bitrix\Im\Model\ChatTable::getByPrimary($chatId, [
			'select' => ['TITLE', 'ENTITY_TYPE', 'ENTITY_ID']
		])->fetch();
		if (!$chat)
		{
			return false;
		}

		if (!\CIMChat::GetRelationById($chatId, $userId, true, false))
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId, array('STORAGE'));
		if (!$fileModel)
		{
			return false;
		}

		$storageModel = $fileModel->getStorage();
		if (!$storageModel)
		{
			return false;
		}

		$securityContext = null;
		if (is_null($userId))
		{
			$securityContext = $storageModel->getCurrentUserSecurityContext();
		}
		else if ($userId > 0)
		{
			$securityContext = $storageModel->getSecurityContext($userId);
		}

		if ($securityContext && !$fileModel->canRead($securityContext))
		{
			return false;
		}

		self::UploadFileFromDisk($chatId, ['disk'.$fileId], '', ['SYMLINK' => true]);

		if (!empty($chat['ENTITY_TYPE']) && !empty($chat['ENTITY_ID']))
		{
			$event = new \Bitrix\Main\Event('im', 'onDiskRecordShare', [
				'DISK_ID' => $fileId,
				'CHAT' => [
					'ID' => $chatId,
					'TITLE' => $chat['TITLE'],
					'ENTITY_TYPE' => $chat['ENTITY_TYPE'],
					'ENTITY_ID' => $chat['ENTITY_ID']
				],
				'USER_ID' => $userId,
			]);
			$event->send();
		}

		return true;
	}

	/**
	 * @param string $hash
	 * @param array $file
	 * @param \Bitrix\Main\UI\Uploader\Log $package
	 * @param \Bitrix\Main\UI\Uploader\Log $upload
	 * @param string $error
	 * @return bool
	 */
	public static function UploadAvatar($hash, &$file, &$package, &$upload, &$error)
	{
		$post = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->toArray();

		$chatId = (int)$post['CHAT_ID'];
		if ($chatId <= 0)
		{
			return false;
		}

		$chat = IM\Model\ChatTable::getById($chatId)->fetch();
		if (!$chat)
		{
			return false;
		}

		$relationError = true;
		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);
		foreach ($chatRelation as $relation)
		{
			if ($relation["EXTERNAL_AUTH_ID"] == 'imconnector')
			{
				unset($chatRelation[$relation["USER_ID"]]);
				continue;
			}
			if ($relation['USER_ID'] == self::GetUserId())
			{
				$relationError = false;
			}
		}
		if ($relationError)
		{
			$error = Loc::getMessage('IM_DISK_ERR_AVATAR_1');
			return false;
		}

		if ($chat['ENTITY_TYPE'] === 'ANNOUNCEMENT' && $chatRelation[self::GetUserId()]['MANAGER'] !== 'Y')
		{
			return false;
		}

		$file["files"]["default"]["MODULE_ID"] = "im";

		$checkResponse = \CFile::CheckImageFile($file["files"]["default"], (10*1024*1024), 5000, 5000);
		if ($checkResponse !== null)
		{
			return false;
		}

		$fileId = \CFile::saveFile($file["files"]["default"], self::MODULE_ID);
		if ($fileId > 0)
		{
			if ($chat['AVATAR'] > 0)
			{
				\CFile::DeLete($chat['AVATAR']);
			}
			IM\Model\ChatTable::update($chatId, Array('AVATAR' => $fileId));

			$file['chatId'] = $chatId;
			$file['chatAvatar'] = \CIMChat::GetAvatarImage($fileId);

			if ($chat["ENTITY_TYPE"] != 'CALL')
			{
				\CIMChat::AddSystemMessage(Array(
					'CHAT_ID' => $chatId,
					'USER_ID' => self::GetUserId(),
					'MESSAGE_CODE' => 'IM_DISK_AVATAR_CHANGE_'
				));
			}

			if (\Bitrix\Main\Loader::includeModule('pull'))
			{
				$pullMessage = Array(
					'module_id' => 'im',
					'command' => 'chatAvatar',
					'params' => Array(
						'chatId' => $chatId,
						'avatar' => $file['chatAvatar'],
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				);
				\Bitrix\Pull\Event::add(array_keys($chatRelation), $pullMessage);
				if ($chat['TYPE'] == \IM_MESSAGE_OPEN  || $chat['TYPE'] == \IM_MESSAGE_OPEN_LINE)
				{
					\CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
				}
			}
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * @param int $chatId
	 * @param int $fileId
	 * @param int|null $userId
	 * @return bool
	 */
	public static function UpdateAvatarId($chatId, $fileId, $userId = null)
	{
		$chatId = (int)$chatId;
		$fileId = (int)$fileId;
		if ($chatId <= 0 || $fileId <= 0)
		{
			return false;
		}

		$chat = IM\Model\ChatTable::getById($chatId)->fetch();
		if (!$chat || in_array($chat['TYPE'], Array(\IM_MESSAGE_PRIVATE, \IM_MESSAGE_SYSTEM)))
		{
			return false;
		}

		$relationError = true;
		$chatRelation = \CIMChat::GetRelationById($chatId, false, true, false);
		foreach ($chatRelation as $relation)
		{
			if ($relation["EXTERNAL_AUTH_ID"] == 'imconnector')
			{
				unset($chatRelation[$relation["USER_ID"]]);
				continue;
			}
			if ($relation['USER_ID'] == \Bitrix\Im\Common::getUserId($userId))
			{
				$relationError = false;
			}
		}
		if ($relationError)
		{
			return false;
		}

		if ($chat['AVATAR'] > 0)
		{
			\CFile::DeLete($chat['AVATAR']);
		}
		IM\Model\ChatTable::update($chatId, Array('AVATAR' => $fileId));

		$file['chatId'] = $chatId;
		$file['chatAvatar'] = \CIMChat::GetAvatarImage($fileId);

		if ($chat["ENTITY_TYPE"] != 'CALL')
		{
			\CIMChat::AddSystemMessage(Array(
				'CHAT_ID' => $chatId,
				'USER_ID' => \Bitrix\Im\Common::getUserId($userId),
				'MESSAGE_CODE' => 'IM_DISK_AVATAR_CHANGE_'
			));
		}

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			$pullMessage = Array(
				'module_id' => 'im',
				'command' => 'chatAvatar',
				'params' => Array(
					'chatId' => $chatId,
					'avatar' => $file['chatAvatar'],
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			);
			\Bitrix\Pull\Event::add(array_keys($chatRelation), $pullMessage);

			if ($chat['TYPE'] == \IM_MESSAGE_OPEN || $chat['TYPE'] == \IM_MESSAGE_OPEN_LINE)
			{
				\CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
			}
		}

		return true;
	}

	/**
	 * @param int $chatId
	 * @param int $historyPage
	 * @return array
	 */
	public static function GetHistoryFiles($chatId, $historyPage = 1)
	{
		$fileArray = [];
		if (!self::Enabled())
		{
			return $fileArray;
		}

		if ((int)$chatId <= 0)
		{
			return $fileArray;
		}

		$offset = (int)$historyPage - 1;
		if ($offset < 0)
		{
			return $fileArray;
		}

		$folderModel = self::getFolderModel($chatId, false);
		if (!$folderModel)
		{
			return $fileArray;
		}

		$filter = Array(
			'PARENT_ID' => $folderModel->getId(),
			'STORAGE_ID' => $folderModel->getStorageId()
		);

		$relation = \CIMChat::GetRelationById($chatId, self::GetUserId(), true, false);
		if (!$relation)
		{
			return $fileArray;
		}

		if ($relation['LAST_FILE_ID'] > 0)
		{
			$filter['>ID'] = $relation['LAST_FILE_ID'];
		}

		/**
		 * @see \Bitrix\Im\Disk\ProxyType\Im::getSecurityContextByUser
		 */
		$securityContext = new \Bitrix\Disk\Security\DiskSecurityContext(self::GetUserId());

		$parameters = Array(
			'filter' => $filter,
			'with' => Array('CREATE_USER'),
			'limit' => 15,
			'offset' => $offset*15,
			'order' => Array('UPDATE_TIME' => 'DESC')
		);
		$parameters = \Bitrix\Disk\Driver::getInstance()->getRightsManager()->addRightsCheck($securityContext, $parameters, array('ID', 'CREATED_BY'));

		$fileCollection = \Bitrix\Disk\File::getModelList($parameters);

		foreach ($fileCollection as $fileModel)
		{
			$fileArray[$fileModel->getId()] = self::GetFileParams($chatId, $fileModel);
		}

		return $fileArray;
	}

	/**
	 * @param int $chatId
	 * @param string $name
	 * @return array
	 */
	public static function GetHistoryFilesByName($chatId, $name)
	{
		$fileArray = [];
		if (!self::Enabled())
		{
			return $fileArray;
		}

		if ((int)$chatId <= 0)
		{
			return $fileArray;
		}

		$name = trim($name);
		if ($name == '')
		{
			return $fileArray;
		}

		$folderModel = self::getFolderModel($chatId, false);
		if (!$folderModel)
		{
			return $fileArray;
		}

		$filter = Array(
			'PARENT_ID' => $folderModel->getId(),
			'STORAGE_ID' => $folderModel->getStorageId(),
			'%=NAME' => str_replace("%", '', $name)."%",
		);

		$relation = \CIMChat::GetRelationById($chatId, self::GetUserId(), true, false);
		if (!$relation)
		{
			return $fileArray;
		}

		if ($relation['LAST_FILE_ID'] > 0)
		{
			$filter['>ID'] = $relation['LAST_FILE_ID'];
		}

		/**
		 * @see \Bitrix\Im\Disk\ProxyType\Im::getSecurityContextByUser
		 */
		$securityContext = new \Bitrix\Disk\Security\DiskSecurityContext(self::GetUserId());

		$parameters = Array(
			'filter' => $filter,
			'with' => Array('CREATE_USER'),
			'limit' => 100,
			'order' => Array('UPDATE_TIME' => 'DESC')
		);
		$parameters = \Bitrix\Disk\Driver::getInstance()->getRightsManager()->addRightsCheck($securityContext, $parameters, array('ID', 'CREATED_BY'));

		$fileCollection = \Bitrix\Disk\File::getModelList($parameters);

		foreach ($fileCollection as $fileModel)
		{
			$fileArray[$fileModel->getId()] = self::GetFileParams($chatId, $fileModel);
		}

		return $fileArray;
	}

	/**
	 * @param int $chatId
	 * @return int
	 */
	public static function GetMaxFileId($chatId)
	{
		$maxId = 0;
		if (!self::Enabled())
		{
			return $maxId;
		}

		if ((int)$chatId <= 0)
		{
			return $maxId;
		}

		$folderModel = self::getFolderModel($chatId, false);
		if (!$folderModel)
		{
			return $maxId;
		}

		$result = \Bitrix\Disk\Internals\ObjectTable::getList(array(
			'select' => array('MAX_ID'),
			'filter' => array(
				'=PARENT_ID' => $folderModel->getId(),
				'=TYPE' => \Bitrix\Disk\Internals\ObjectTable::TYPE_FILE
			),
			'runtime' => array(
				'MAX_ID' => array(
					'data_type' => 'integer',
					'expression' => array('MAX(ID)')
				)
			)
		));
		if ($data = $result->fetch())
		{
			$maxId = $data['MAX_ID'];
		}

		return (int)$maxId;
	}

	/**
	 * @param int $chatId
	 * @param int|int[]|false $fileId
	 * @param bool $checkPermission
	 * @return array
	 */
	public static function GetFiles($chatId, $fileId = false, $checkPermission = true)
	{
		$fileArray = Array();
		if (!self::Enabled())
		{
			return $fileArray;
		}

		if ((int)$chatId <= 0)
		{
			return $fileArray;
		}

		if ($fileId === false || $fileId === null)
		{
			if (!is_array($fileId))
			{
				$fileId = Array($fileId);
			}
			foreach ($fileId as $key => $value)
			{
				$fileId[$key] = (int)$value;
			}
		}
		if (empty($fileId))
		{
			return $fileArray;
		}
		$folderModel = self::getFolderModel($chatId, false);
		if (!$folderModel)
		{
			return $fileArray;
		}
		$filter = Array(
			'PARENT_ID' => $folderModel->getId(),
			'STORAGE_ID' => $folderModel->getStorageId()
		);
		if ($fileId)
		{
			$filter['ID'] = array_values($fileId);
		}

		if ($checkPermission)
		{
			$securityContext = new \Bitrix\Disk\Security\DiskSecurityContext(self::GetUserId());
		}
		else
		{
			$securityContext = \Bitrix\Disk\Driver::getInstance()->getFakeSecurityContext();
		}

		$parameters = Array(
			'filter' => $filter,
			'with' => Array('CREATE_USER')
		);
		$parameters = \Bitrix\Disk\Driver::getInstance()->getRightsManager()->addRightsCheck($securityContext, $parameters, array('ID', 'CREATED_BY'));

		$fileCollection = \Bitrix\Disk\File::getModelList($parameters);
		foreach ($fileCollection as $fileModel)
		{
			$fileArray[$fileModel->getId()] = self::GetFileParams($chatId, $fileModel);
		}

		return $fileArray;
	}

	/**
	 * @param int $chatId
	 * @param \Bitrix\Disk\File|int $fileModel
	 * @return array|false
	 */
	public static function GetFileParams($chatId, $fileModel, $options = [])
	{
		if (!self::Enabled())
		{
			return false;
		}

		$skipViewer = isset($options['SKIP_VIEWER']) && ($options['SKIP_VIEWER'] === true);

		if ($fileModel instanceof \Bitrix\Disk\File)
		{
		}
		elseif ((int)$fileModel > 0)
		{
			$fileModel = \Bitrix\Disk\File::getById($fileModel);
		}
		else
		{
			return false;
		}

		if (!$fileModel || $fileModel->getId() <= 0)
		{
			return false;
		}

		/** @var \Bitrix\Disk\File $fileModel */
		$contentType = 'file';
		$imageParams = false;
		if (\Bitrix\Disk\TypeFile::isImage($fileModel->getName()))
		{
			$contentType = 'image';
			$params = $fileModel->getFile();
			$width = isset($params['WIDTH']) ? (int)$params['WIDTH'] : 0;
			$height = isset($params['HEIGHT']) ? (int)$params['HEIGHT'] : 0;
			$imageParams = Array(
				'width' => $width,
				'height' => $height,
			);
		}
		else if (\Bitrix\Disk\TypeFile::isVideo($fileModel->getName()))
		{
			$contentType = 'video';
			$params = $fileModel->getView()->getPreviewData();
			$width = isset($params['WIDTH']) ? (int)$params['WIDTH'] : 0;
			$height = isset($params['HEIGHT']) ? (int)$params['HEIGHT'] : 0;
			$imageParams = Array(
				'width' => $width,
				'height' => $height,
			);
		}
		else if (\Bitrix\Disk\TypeFile::isAudio($fileModel->getName()))
		{
			$contentType = 'audio';
		}

		$fileData = Array(
			'id' => (int)$fileModel->getId(),
			'chatId' => (int)$chatId,
			'date' => $fileModel->getCreateTime(),
			'type' => $contentType,
			'name' => $fileModel->getName(),
			'extension' => mb_strtolower($fileModel->getExtension()),
			'size' => (int)$fileModel->getSize(),
			'image' => $imageParams,
			'status' => $fileModel->getGlobalContentVersion() > 1? 'done': 'upload',
			'progress' => $fileModel->getGlobalContentVersion() > 1? 100: -1,
			'authorId' => (int)$fileModel->getCreatedBy(),
			'authorName' => \Bitrix\Im\User::formatFullNameFromDatabase($fileModel->getCreateUser()),
			'urlPreview' => self::GetPublicPath(self::PATH_TYPE_PREVIEW, $fileModel),
			'urlShow' => self::GetPublicPath(self::PATH_TYPE_SHOW, $fileModel),
			'urlDownload' => self::GetPublicPath(self::PATH_TYPE_DOWNLOAD, $fileModel),
		);

		if ($skipViewer)
		{
			$fileData['viewerAttrs'] = null;
		}
		else
		{
			try
			{
				$viewerType = Disk\Ui\FileAttributes::buildByFileId($fileModel->getFileId(), $fileData['urlDownload'])
					->setObjectId($fileModel->getId())
					->setGroupBy($chatId)
					->setAttribute('data-im-chat-id', $chatId)
					->setTitle($fileModel->getName())
					->addAction([
						'type' => 'download',
					])
					->addAction([
						'type' => 'copyToMe',
						'text' => Loc::getMessage('IM_DISK_ACTION_SAVE_TO_OWN_FILES'),
						'action' => 'BXIM.disk.saveToDiskAction',
						'params' => [
							'fileId' => $fileModel->getId(),
						],
						'extension' => 'disk.viewer.actions',
						'buttonIconClass' => 'ui-btn-icon-cloud',
					])
				;

				if ($viewerType->getTypeClass() === Disk\Ui\FileAttributes::JS_TYPE_CLASS_ONLYOFFICE)
				{
					$viewerType->setTypeClass('BX.Messenger.Integration.Viewer.OnlyOfficeChatItem');
					if (
						$fileModel->getCode() === CreateDocumentByCallTemplateScenario::CODE_RESUME
						|| $fileModel->getRealObject()->getCode() === CreateDocumentByCallTemplateScenario::CODE_RESUME
					)
					{
						$viewerType->setTypeClass('BX.Messenger.Integration.Viewer.OnlyOfficeResumeItem');
					}

					$viewerType->setExtension('im.integration.viewer');
				}
				if ($viewerType->getViewerType() !== \Bitrix\Main\UI\Viewer\Renderer\Renderer::JS_TYPE_UNKNOWN)
				{
					$fileData['viewerAttrs'] = $viewerType->toDataSet();
				}
				else
				{
					$fileData['viewerAttrs'] = null;
				}
			}
			catch (\Bitrix\Main\ArgumentException $exception)
			{
				$fileData['viewerAttrs'] = null;
			}
		}


		return $fileData;
	}

	/**
	 * Is full functionality enabled.
	 * @return bool
	 */
	public static function Enabled()
	{
		static $isEnable;
		if ($isEnable === null)
		{
			$isEnable =
				\Bitrix\Main\Loader::includeModule('pull')
				&& \CPullOptions::GetNginxStatus()
				&& \Bitrix\Main\Loader::includeModule('disk')
				&& \Bitrix\Disk\Driver::isSuccessfullyConverted()
			;
		}

		return $isEnable;
	}

	public static function updateFolderRights(int $chatId, bool $fullRights = false): bool
	{
		if (!self::Enabled() || $chatId <= 0)
		{
			return false;
		}

		$result = IM\Model\ChatTable::getById($chatId);
		if (!$chat = $result->fetch())
		{
			return false;
		}

		if ($chat['TYPE'] !== IM\V2\Chat::IM_TYPE_CHAT)
		{
			return false;
		}

		$folderId = (int)$chat['DISK_FOLDER_ID'];
		if ($folderId <= 0)
		{
			return false;
		}

		$folderModel = \Bitrix\Disk\Folder::getById($folderId);
		if (
			!$folderModel
			|| !($folderModel instanceof \Bitrix\Disk\Folder)
			|| ($folderModel->getStorageId() != self::GetStorageId())
		)
		{
			return false;
		}

		$driver = \Bitrix\Disk\Driver::getInstance();
		$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;
		$rightsManager = $driver->getRightsManager();

		if ($fullRights)
		{
			$accessCode = self::GetTopDepartmentCode();
		}
		else
		{
			$accessCode = $accessProvider->generateAccessCode($chatId);
		}

		$rightsManager->delete($folderModel);
		return $rightsManager->append(
			$folderModel,
			[[
				'ACCESS_CODE' => $accessCode,
				'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_READ)
			]]
		);
	}

	/**
	 * @param int $chatId Chat Id.
	 * @param bool $createFolder Create disk folder if not exists.
	 * @return \Bitrix\Disk\Folder|false|null
	 */
	public static function GetFolderModel($chatId, $createFolder = true)
	{
		if (!self::Enabled())
		{
			return false;
		}

		if ((int)$chatId <= 0)
		{
			return false;
		}

		$folderModel = false;

		$result = IM\Model\ChatTable::getById($chatId);
		if (!$chat = $result->fetch())
		{
			return false;
		}

		if (in_array($chat['TYPE'], [IM\V2\Chat::IM_TYPE_COMMENT], true))
		{
			return false;
		}

		$folderId = (int)$chat['DISK_FOLDER_ID'];
		if ($folderId > 0)
		{
			$folderModel = \Bitrix\Disk\Folder::getById($folderId);
			if (
				!$folderModel
				|| !($folderModel instanceof \Bitrix\Disk\Folder)
				|| ($folderModel->getStorageId() != self::GetStorageId())
			)
			{
				$folderId = 0;
			}
		}

		if (!$folderId && $createFolder === true)
		{
			$chatType = $chat['TYPE'];
			$driver = \Bitrix\Disk\Driver::getInstance();
			$storageModel = self::GetStorage();
			if (!$storageModel)
			{
				return false;
			}

			$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;
			$rightsManager = $driver->getRightsManager();

			$accessCodes = [];
			// allow for access code `CHATxxx`
			$accessCodes[] = [
				'ACCESS_CODE' => $accessProvider->generateAccessCode($chatId),
				'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_EDIT)
			];
			if ($chatType === IM\V2\Chat::IM_TYPE_OPEN)
			{
				// allow reading for top department, access code `DRxxx`
				$departmentCode = self::GetTopDepartmentCode();
				if ($departmentCode)
				{
					$accessCodes[] = Array(
						'ACCESS_CODE' => $departmentCode,
						'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_READ)
					);
				}
			}

			$folderModel = $storageModel->addFolder(
				[
					'NAME' => 'chat'.$chatId,
					'CREATED_BY' => self::GetUserId()
				],
				$accessCodes,
				true
			);
			if ($folderModel)
			{
				IM\Model\ChatTable::update($chatId, ['DISK_FOLDER_ID' => $folderModel->getId()]);

				$accessProvider->updateChatCodesByRelations($chatId);
			}
		}

		return $folderModel;
	}

	/**
	 * @param int $chatId
	 * @param int|int[] $userId
	 * @param bool $append
	 * @return bool
	 */
	public static function ChangeFolderMembers($chatId, $userId, $append = true)
	{
		$chatId = (int)$chatId;
		if (!is_array($userId))
		{
			$userIds = Array($userId);
		}
		else
		{
			$userIds = $userId;
		}
		if ($chatId <= 0 || empty($userIds))
		{
			return false;
		}

		$folderModel = self::getFolderModel($chatId, false);
		if (!$folderModel)
		{
			return false;
		}

		$resChat = IM\Model\ChatTable::getById($chatId);
		if (!$chat = $resChat->fetch())
		{
			return false;
		}

		$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;

		if ($append)
		{
			$accessProvider->addChatCodes($chatId, $userIds);
			$result = true;
		}
		else
		{
			$accessProvider->deleteChatCodes($chatId, $userIds);

			// keep removing disk access codes `Uxxx` for previous access scheme
			$accessCodes = Array();
			if ($chat['TYPE'] == \IM_MESSAGE_OPEN)
			{
				$users = \CIMContactList::GetUserData(array(
					'ID' => array_values($userIds),
					'DEPARTMENT' => 'N',
					'SHOW_ONLINE' => 'N',
				));
				foreach ($users['users'] as $userData)
				{
					if ($userData['extranet'])
					{
						$accessCodes[] = 'U'.$userData['id'];
					}
				}
			}
			else
			{
				foreach ($userIds as $uid)
				{
					$uid = (int)$uid;
					if ($uid <= 0)
					{
						continue;
					}

					$accessCodes[] = 'U'.$uid;
				}
			}
			$driver = \Bitrix\Disk\Driver::getInstance();
			$rightsManager = $driver->getRightsManager();
			$result = $rightsManager->revokeByAccessCodes($folderModel, $accessCodes);
		}

		return $result;
	}

	/**
	 * @param int|null $userId
	 * @return \Bitrix\Disk\Folder|null
	 */
	public static function GetBackgroundFolderModel($userId = null)
	{
		if (!self::Enabled())
		{
			return null;
		}

		$userId = IM\Common::getUserId($userId);
		if (!$userId)
		{
			return null;
		}

		$storageModel = self::GetStorage();
		if (!$storageModel)
		{
			return null;
		}

		$folderModel = $storageModel->getSpecificFolderByCode('CALL_BACKGROUND_'.$userId);
		if ($folderModel)
		{
			return $folderModel;
		}

		$backgroundFolderModel = $storageModel->getSpecificFolderByCode('CALL_BACKGROUND');
		if (!$backgroundFolderModel)
		{
			$backgroundFolderModel = $storageModel->addFolder([
				'NAME' => 'CALL_BACKGROUND',
				'CODE' => 'CALL_BACKGROUND',
				'CREATED_BY' => \Bitrix\Disk\SystemUser::SYSTEM_USER_ID,
			], [], true);
		}
		if (!$backgroundFolderModel)
		{
			return null;
		}

		$rightsManager = \Bitrix\Disk\Driver::getInstance()->getRightsManager();

		$folderModel = $backgroundFolderModel->addSubFolder(
			[
				'NAME' => 'CALL_BACKGROUND_'.$userId,
				'CODE' => 'CALL_BACKGROUND_'.$userId,
				'CREATED_BY' => $userId,
			],
			[
				// allow only for user, access code `Uxxx`
				['ACCESS_CODE' => 'U'.$userId, 'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_FULL)],
			],
			true
		);

		return $folderModel;
	}

	/**
	 * @param int $userId
	 * @param int $fileId
	 * @return bool
	 */
	public static function CommitBackgroundFile($userId, $fileId)
	{
		$folderModel = self::GetBackgroundFolderModel($userId);
		if (!$folderModel)
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId);
		if (!$fileModel || $fileModel->getParentId() != $folderModel->getId())
		{
			return false;
		}

		$fileModel->increaseGlobalContentVersion();

		return true;
	}
/**
	 * @param int $userId
	 * @param int $fileId
	 * @return bool
	 */
	public static function DeleteBackgroundFile($userId, $fileId)
	{
		$folderModel = self::GetBackgroundFolderModel($userId);
		if (!$folderModel)
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId);
		if (!$fileModel || $fileModel->getParentId() != $folderModel->getId())
		{
			return false;
		}

		$fileModel->delete($userId);

		return true;
	}

	/**
	 * @param int|null $userId
	 * @return \Bitrix\Disk\Folder|false|null
	 */
	public static function GetLocalDiskSavedModel($userId = null)
	{
		if (!self::Enabled())
		{
			return false;
		}

		$userId = IM\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$storageModel = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($userId);
		if (!$storageModel)
		{
			return false;
		}

		return $storageModel->getFolderForSavedFiles();
	}

	/**
	 * @return int
	 */
	public static function GetStorageId()
	{
		return (int)\Bitrix\Main\Config\Option::get('im', 'disk_storage_id', 0);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public static function SetStorageId($id)
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			return false;
		}

		$oldId = self::GetStorageId();
		if ($oldId > 0 && $oldId != $id)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$connection->queryExecute("UPDATE b_im_chat SET DISK_FOLDER_ID = 0");
			$connection->queryExecute("DELETE FROM b_im_message_param WHERE PARAM_NAME = 'FILE_ID'");
		}

		\Bitrix\Main\Config\Option::set('im', 'disk_storage_id', $id);

		return true;
	}

	/**
	 * @return string
	 */
	public static function GetLocalDiskFolderPath()
	{
		if (!self::Enabled())
		{
			return '';
		}

		$folderModel = self::GetLocalDiskSavedModel();
		if (!$folderModel)
		{
			return '';
		}

		return \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlFocusController('openFolderList', array('folderId' => $folderModel->getId()));
	}

	/**
	 * @param int $fileId
	 * @return string
	 */
	public static function GetLocalDiskFilePath($fileId = 0)
	{
		if (!self::Enabled())
		{
			return '';
		}

		$fileId = (int)$fileId;

		return \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlFocusController('showObjectInGrid', array('objectId' => $fileId? $fileId: '_FILE_ID_'));
	}

	/**
	 * @param string $type
	 * @param \Bitrix\Disk\File $fileModel
	 * @param bool $checkContentVersion
	 * @return string
	 */
	public static function GetPublicPath($type, \Bitrix\Disk\File $fileModel, $checkContentVersion = true)
	{
		$result = '';

		if (!in_array($type, Array(self::PATH_TYPE_DOWNLOAD, self::PATH_TYPE_SHOW, self::PATH_TYPE_PREVIEW)))
		{
			return $result;
		}

		if ($checkContentVersion && $fileModel->getGlobalContentVersion() <= 1)
		{
			return $result;
		}

		$urlManager = \Bitrix\Main\Engine\UrlManager::getInstance();

		$isImage = \Bitrix\Disk\TypeFile::isImage($fileModel->getName());
		$isVideo = \Bitrix\Disk\TypeFile::isVideo($fileModel->getName());

		if ($type == self::PATH_TYPE_SHOW)
		{
			if ($isImage)
			{
				$result = $urlManager->create('disk.api.file.showImage', [
					'humanRE' => 1,
					'fileId' => $fileModel->getId(),
					'fileName' => $fileModel->getName()
				])->getUri();
			}
			else
			{
				$result = $urlManager->create('disk.api.file.download', [
					'humanRE' => 1,
					'fileId' => $fileModel->getId(),
					'fileName' => $fileModel->getName()
				])->getUri();
			}
		}
		else if ($type == self::PATH_TYPE_PREVIEW)
		{
			if (!($isImage || $isVideo))
			{
				return $result;
			}

			if ($fileModel->getView()->getPreviewData())
			{
				$linkType = 'disk.api.file.showPreview';
				$fileName = 'preview.jpg';
			}
			else if ($isImage)
			{
				$linkType = 'disk.api.file.showImage';
				$fileName = $fileModel->getName();
			}
			else
			{
				return $result;
			}

			$result = $urlManager->create($linkType, [
				'humanRE' => 1,
				'width' => 640,
				'height' => 640,
				'signature' => \Bitrix\Disk\Security\ParameterSigner::getImageSignature($fileModel->getId(), 640, 640),
				'fileId' => $fileModel->getId(),
				'fileName' => $fileName
			])->getUri();
		}
		else if ($type == self::PATH_TYPE_DOWNLOAD)
		{
			$result = $urlManager->create('disk.api.file.download', [
				'humanRE' => 1,
				'fileId' => $fileModel->getId(),
				'fileName' => $fileModel->getName()
			])->getUri();
		}

		return $result;
	}

	/**
	 * @param \Bitrix\Disk\File $fileModel
	 * @return false|string
	 */
	public static function GetFileLink(\Bitrix\Disk\File $fileModel)
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return false;
		}

		$fileId = $fileModel->getId();

		$signer = new \Bitrix\Main\Security\Sign\Signer;
		$signKey = self::GetFileLinkSign();
		if (is_string($signKey))
		{
			$signer->setKey($signKey);
		}
		$signedValue = $signer->sign($fileId);

		$urlManager = \Bitrix\Main\Engine\UrlManager::getInstance();
		$host = $urlManager->getHostUrl();
		$isImage = \Bitrix\Disk\TypeFile::isImage($fileModel->getName());

		$link = $host.'/pub/im.file.php?FILE_ID='.$fileId.'&SIGN='.$signedValue;
		if ($isImage)
		{
			$link .= '&img=y';
		}

		$shortLink = $host. \CBXShortUri::GetShortUri($link);
		if ($isImage)
		{
			$shortLink .= '#img.'.$fileModel->getExtension();
		}

		return $shortLink;
	}

	/**
	 * @return string
	 */
	public static function GetFileLinkSign()
	{
		$key = \Bitrix\Main\Config\Option::get('im', 'file_link_default_key', null);
		if (!$key)
		{
			$key = \Bitrix\Main\Config\Option::get('main', 'signer_default_key', null);
			if (is_string($key))
			{
				\Bitrix\Main\Config\Option::set('im', 'file_link_default_key', $key);
			}
		}
		return $key;
	}

	/**
	 * @return string
	 */
	public static function RemoveTmpFileAgent()
	{
		$storageModel = self::GetStorage();
		if (!$storageModel)
		{
			return "CIMDisk::RemoveTmpFileAgent();";
		}
		$date = new \Bitrix\Main\Type\DateTime();
		$date->add('YESTERDAY');

		$fileModels = \Bitrix\Disk\File::getModelList(Array(
			'filter' => Array(
				'GLOBAL_CONTENT_VERSION' => 1,
				'=TYPE' => \Bitrix\Disk\Internals\FileTable::TYPE,
				'STORAGE_ID' => $storageModel->getId(),
				'<CREATE_TIME' => $date
			),
			'limit' => 200
		));
		foreach ($fileModels as $fileModel)
		{
			$fileModel->delete(\Bitrix\Disk\SystemUser::SYSTEM_USER_ID);
		}

		return "CIMDisk::RemoveTmpFileAgent();";
	}

	/**
	 * @return int
	 */
	public static function GetUserId()
	{
		/** global \CUser $USER */
		global $USER;
		return $USER instanceOf \CUser ? (int)$USER->getId() : 0;
	}

	/**
	 * @return bool
	 */
	public static function EnabledExternalLink()
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return false;
		}

		return \Bitrix\Disk\Configuration::isEnabledExternalLink();
	}

	/**
	 * @param bool $flag
	 * @return bool
	 */
	public static function SetEnabledExternalLink($flag = true)
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return false;
		}

		if (!\CIMMessenger::IsAdmin())
		{
			return false;
		}

		\Bitrix\Main\Config\Option::set('disk', 'disk_allow_use_external_link', $flag ? 'Y': 'N');

		return true;
	}

	/**
	 * @return false|string
	 */
	public static function GetTopDepartmentCode()
	{
		if (!\Bitrix\Main\Loader::includeModule("iblock"))
		{
			return false;
		}

		$code = false;
		$res = \CIBlock::GetList(array(), array("CODE" => "departments"));
		if ($iblock = $res->Fetch())
		{
			$res = \CIBlockSection::GetList(
				array(),
				array(
					"SECTION_ID" => 0,
					"IBLOCK_ID" => $iblock["ID"]
				)
			);
			if ($department = $res->Fetch())
			{
				$code = "DR".$department['ID'];
			}
		}

		return $code;
	}

	/**
	 * @param int $fileId
	 * @param int $userId
	 * @param array $fileParams
	 * @return bool
	 */
	public static function OnAfterDeleteFile($fileId, $userId, $fileParams = Array())
	{
		if (!isset($fileParams['STORAGE_ID']) || $fileParams['STORAGE_ID'] != self::GetStorageId())
		{
			return true;
		}

		$messageId = \CIMMessageParam::GetMessageIdByParam('FILE_ID', $fileId);
		\CIMMessageParam::DeleteByParam('FILE_ID', $fileId);
		(new IM\V2\Link\File\FileService())->deleteFilesByDiskFileId($fileId);
		\CIMMessageParam::SendPull($messageId, Array('FILE_ID'));

		return true;
	}
}
