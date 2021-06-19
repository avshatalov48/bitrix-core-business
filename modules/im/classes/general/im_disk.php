<?
IncludeModuleLangFile(__FILE__);

use \Bitrix\Im as IM;

class CIMDisk
{
	const MODULE_ID = 'im';

	const PATH_TYPE_SHOW = 'show';
	const PATH_TYPE_PREVIEW = 'preview';
	const PATH_TYPE_DOWNLOAD = 'download';

	public static function GetStorage()
	{
		if (!self::Enabled())
			return false;

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
			$data['NAME'] = GetMessage('IM_DISK_STORAGE_TITLE');
			$data['USE_INTERNAL_RIGHTS'] = 1;
			$data['MODULE_ID'] = self::MODULE_ID;
			$data['ENTITY_TYPE'] = IM\Disk\ProxyType\Im::className();
			$data['ENTITY_ID'] = self::MODULE_ID;

			$driver = \Bitrix\Disk\Driver::getInstance();

			$rightsManager = $driver->getRightsManager();
			$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);

			$storageModel = $driver->addStorageIfNotExist($data, array(
				array(
					'ACCESS_CODE' => 'AU',
					'TASK_ID' => $fullAccessTaskId,
				),
			));
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

	public static function UploadFileRegister($chatId, $files, $text = '', $linesSilentMode = false)
	{
		if (intval($chatId) <= 0)
			return false;

		$chatRelation = CIMChat::GetRelationById($chatId);
		if (!$chatRelation[self::GetUserId()])
			return false;

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
			return false;

		$result['FILE_ID'] = Array();
		$messageFileId = Array();
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
		$arChat = CIMChat::GetChatData(Array('ID' => $chatId));
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
		$messageId = CIMMessage::Add($ar);
		if ($messageId)
		{
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

	public static function UploadFile($hash, &$file, &$package, &$upload, &$error)
	{
		$post = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->toArray();
		$post['PARAMS'] = CUtil::JsObjectToPhp($post['REG_PARAMS']);
		$post['PARAMS'] = \Bitrix\Main\Text\Encoding::convertEncoding($post['PARAMS'], 'UTF-8', LANG_CHARSET);
		$post['MESSAGE_HIDDEN'] = $post['REG_MESSAGE_HIDDEN'] == 'Y'? 'Y': 'N';
		$post['PARAMS']['TEXT'] = $post['PARAMS']['TEXT']? trim($post['PARAMS']['TEXT']): '';

		$chatId = intval($post['CHAT_ID']);
		if (intval($chatId) <= 0)
		{
			$error = GetMessage('IM_DISK_ERR_UPLOAD').' (E100)';
			return false;
		}

		$chat = \Bitrix\Im\Chat::getById($chatId, ['CHECK_ACCESS' => 'Y']);
		if (!$chat)
		{
			$error = GetMessage('IM_DISK_ERR_UPLOAD').' (E101)';
			return false;
		}

		$chatRelation = CIMChat::GetRelationById($chatId);
		if (!$chatRelation[self::GetUserId()])
		{
			$error = GetMessage('IM_DISK_ERR_UPLOAD').' (E102)';
			return false;
		}

		if ($chat['ENTITY_TYPE'] === 'ANNOUNCEMENT' && $chatRelation[self::GetUserId()]['MANAGER'] !== 'Y')
		{
			$error = GetMessage('IM_DISK_ERR_UPLOAD').' (E103)';
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			$error = GetMessage('IM_DISK_ERR_UPLOAD').' (E104)';
			return false;
		}

		if (!$file["files"]["default"])
		{
			$error = GetMessage('IM_DISK_ERR_UPLOAD').' (E106)';
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
			$error = GetMessage('IM_DISK_ERR_UPLOAD').' (E107)';
			return false;
		}

		$fileTmpId = $file["id"];
		$messageTmpId = $file["regTmpMessageId"];
		$isMessageHidden = $file["regHiddenMessageId"] === 'Y';

		if (!$fileTmpId || !$messageTmpId)
		{
			$error = "exemplarId is not defined";
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
			return true;
		}

		$fileModel = $folderModel->getChild(['ID' => $fileModel->getId()]);

		$file['fileParams'] = self::GetFileParams($chatId, $fileModel);
		$file['fileParams']['date'] = date('c', $file['fileParams']['date']->getTimestamp());

		foreach(GetModuleEvents("im", "OnAfterFileUpload", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [[
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

	public static function UploadFileUnRegister($chatId, $files, $messages)
	{
		if (intval($chatId) <= 0)
			return false;

		$chatRelation = CIMChat::GetRelationById($chatId);
		if (!$chatRelation[self::GetUserId()])
		{
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return false;
		}

		$result['CHAT_ID'] = $chatId;
		$result['FILE_ID'] = Array();
		$result['MESSAGE_ID'] = Array();
		foreach ($files as $fileTmpId => $fileId)
		{
			$fileModel = \Bitrix\Disk\File::getById($fileId);
			if (
				!$fileModel || $fileModel->getParentId() != $folderModel->getId()
				|| $fileModel->getCreatedBy() != self::GetUserId())
			{
				continue;
			}
			$fileModel->delete(self::GetUserId());
			$result['FILE_ID'][$fileTmpId] = $fileId;
		}
		foreach ($messages as $fileTmpId => $messageId)
		{
			if (!isset($result['FILE_ID'][$fileTmpId]))
				continue;

			$CIMMessage = new CIMMessage();
			$arMessage = $CIMMessage->GetMessage($messageId);
			if ($arMessage['AUTHOR_ID'] != self::GetUserId())
			{
				continue;
			}
			CIMMessage::Delete($messageId);
			$result['MESSAGE_ID'][$fileTmpId] = $messageId;
		}
		if (empty($result['FILE_ID']) && empty($result['MESSAGE_ID']))
			return false;

		if (CModule::IncludeModule('pull'))
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
			if ($chat['TYPE'] == IM_MESSAGE_OPEN || $chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
			{
				CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
			}
		}

		return $result;
	}

	public static function DeleteFile($chatId, $fileId)
	{
		if (intval($chatId) <= 0)
			return false;

		$chatRelation = CIMChat::GetRelationById($chatId);
		if (!$chatRelation[self::GetUserId()])
			return false;

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
			return false;


		$fileModel = \Bitrix\Disk\File::getById($fileId);
		if (!$fileModel || $fileModel->getParentId() != $folderModel->getId())
		{
			return false;
		}

		if ($fileModel->getCreatedBy() == self::GetUserId())
		{
			$fileModel->delete(self::GetUserId());
		}
		else
		{
			$driver = \Bitrix\Disk\Driver::getInstance();
			$rightsManager = $driver->getRightsManager();
			$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);

			$accessCodes[] = array(
				'ACCESS_CODE' => 'U'.self::GetUserId(),
				'TASK_ID' => $fullAccessTaskId,
				'NEGATIVE' => 1,
			);
			$rightsManager->append($fileModel, $accessCodes);

			$chatRelation = Array(
				Array('USER_ID' => self::GetUserId())
			);
		}

		if (CModule::IncludeModule('pull'))
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
			\Bitrix\Pull\Event::add(array_keys($chatRelation), $pullMessage);

			$orm = \Bitrix\Im\Model\ChatTable::getById($chatId);
			$chat = $orm->fetch();
			if ($chat['TYPE'] == IM_MESSAGE_OPEN || $chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
			{
				CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
			}
		}

		return true;
	}

	/**
	 * @param $chatId
	 * @param $files
	 * @param string $text
	 * @param array $options
	 * @param bool $robot
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function UploadFileFromDisk($chatId, $files, $text = '', $options = [], $robot = false)
	{
		if (intval($chatId) <= 0)
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

		$skipUserCheck = $options['SKIP_USER_CHECK'] === true;
		$linesSilentMode = $options['LINES_SILENT_MODE'] === true;
		$makeSymlink = $options['SYMLINK'] === true;
		$templateId = $options['TEMPLATE_ID'] <> ''? $options['TEMPLATE_ID']: '';
		$fileTemplateId = $options['FILE_TEMPLATE_ID'] <> ''? $options['FILE_TEMPLATE_ID']: '';

		$chatRelation = CIMChat::GetRelationById($chatId);

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

		$result['FILES'] = Array();
		$result['DISK_ID'] = Array();
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
							if ($relation['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
								break;

							if ($userId == $relation['USER_ID'])
								continue;

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
			"PARAMS" => [
				'FILE_ID' => $result['DISK_ID']
			],
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

		$text = trim($text);
		if ($text)
		{
			$ar["MESSAGE"] = $text;
		}

		$messageId = CIMMessage::Add($ar);
		if ($messageId)
		{
			$result['MESSAGE_ID'] = $messageId;
		}

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

			if ($chat['ENTITY_TYPE'] == 'LIVECHAT' && CModule::IncludeModule('imopenlines'))
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
			else if ($chat['ENTITY_TYPE'] == 'LINES')
			{
				[$connectorId, $lineId, $connectorChatId] = explode("|", $chat['ENTITY_ID']);
				if ($connectorId == 'livechat')
				{
					$uploadResult = self::UploadFileFromDisk(
						$connectorChatId,
						$fileIds,
						$text,
						['USER_ID' => $userId],
						true
					);
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

				$event = new \Bitrix\Main\Event("imopenlines", "OnLivechatUploadFile", ['FILES' => $uploadResult['DISK_ID']]);
				$event->send();
			}
		}

		return $result;
	}

	public static function UploadFileFromMain($chatId, $files)
	{
		if (intval($chatId) <= 0)
		{
			return false;
		}

		$chatRelation = CIMChat::GetRelationById($chatId);
		if (!$chatRelation)
		{
			return false;
		}

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return false;
		}

		$result['FILE_ID'] = Array();
		$messageFileId = Array();
		foreach ($files as $fileId)
		{
			$res = \CFile::GetByID($fileId);
			$file = $res->Fetch();
			if(!$file)
			{
				continue;
			}

			if(empty($file['ORIGINAL_NAME']))
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
		if (empty($messageFileId))
		{
			return false;
		}

		return !empty($messageFileId)? $messageFileId: false;
	}

	public static function SaveToLocalDisk($fileId)
	{
		if (!self::Enabled())
			return false;

		if (intval($fileId) <= 0)
			return false;

		$fileModel = \Bitrix\Disk\File::getById($fileId, array('STORAGE'));
		if (!$fileModel)
			return false;

		$storageModel = $fileModel->getStorage();

		if(!$fileModel->canRead($storageModel->getCurrentUserSecurityContext()))
			return false;

		$folderModel = self::GetLocalDiskSavedModel();
		if (!$folderModel)
			return false;

		$newFileModel = $fileModel->copyTo($folderModel, self::GetUserId(), true);
		if (!$newFileModel)
			return false;

		return [
			'FILE' => $newFileModel,
			'FOLDER' => $folderModel,
		];
	}

	public static function IncreaseFileVersionDisk($chatId, $fileId, int $userId = null)
	{
		if (!self::Enabled())
		{
			return false;
		}

		if (intval($fileId) <= 0)
		{
			return false;
		}

		if (intval($chatId) <= 0)
		{
			return false;
		}

		$fileModel = \Bitrix\Disk\File::getById($fileId, array('STORAGE'));
		if (!$fileModel)
		{
			return false;
		}

		$storageModel = $fileModel->getStorage();

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

	public static function SaveFromLocalDisk($chatId, $fileId, bool $symlink = false, int $userId = null)
	{
		if (!self::Enabled())
			return false;

		if (intval($fileId) <= 0)
			return false;

		if (intval($chatId) <= 0)
			return false;

		$fileModel = \Bitrix\Disk\File::getById($fileId, array('STORAGE'));
		if (!$fileModel)
			return false;

		$storageModel = $fileModel->getStorage();

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

		if (false && $symlink)
		{
			$newFileModel = $folderModel->addFileLink($fileModel, [
				'CREATED_BY' => (int)$userId,
				'GLOBAL_CONTENT_VERSION' => 1
			], [], true);
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

	public static function UploadAvatar($hash, &$file, &$package, &$upload, &$error)
	{
		$post = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->toArray();

		$chatId = intval($post['CHAT_ID']);
		if ($chatId <= 0)
			return false;

		$chat = IM\Model\ChatTable::getById($chatId)->fetch();
		if (!$chat)
			return false;

		$relationError = true;
		$chatRelation = CIMChat::GetRelationById($chatId);
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
			$error = GetMessage('IM_DISK_ERR_AVATAR_1');
			return false;
		}

		if ($chat['ENTITY_TYPE'] === 'ANNOUNCEMENT' && $chatRelation[self::GetUserId()]['MANAGER'] !== 'Y')
		{
			return false;
		}

		$file["files"]["default"]["MODULE_ID"] = "im";
		$fileId = CFile::saveFile($file["files"]["default"], self::MODULE_ID);
		if ($fileId > 0)
		{
			if ($chat['AVATAR'] > 0)
			{
				CFile::DeLete($chat['AVATAR']);
			}
			IM\Model\ChatTable::update($chatId, Array('AVATAR' => $fileId));

			$file['chatId'] = $chatId;
			$file['chatAvatar'] = CIMChat::GetAvatarImage($fileId);

			if ($chat["ENTITY_TYPE"] != 'CALL')
			{
				CIMChat::AddSystemMessage(Array(
					'CHAT_ID' => $chatId,
					'USER_ID' => self::GetUserId(),
					'MESSAGE_CODE' => 'IM_DISK_AVATAR_CHANGE_'
				));
			}

			if (CModule::IncludeModule('pull'))
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
				if ($chat['TYPE'] == IM_MESSAGE_OPEN  || $chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
				}
			}
		}
		else
		{
			return false;
		}

		return true;
	}

	public static function UpdateAvatarId($chatId, $fileId, $userId = null)
	{
		$chatId = intval($chatId);
		$fileId = intval($fileId);
		if ($chatId <= 0 || $fileId <= 0)
			return false;

		$chat = IM\Model\ChatTable::getById($chatId)->fetch();
		if (!$chat || in_array($chat['TYPE'], Array(IM_MESSAGE_PRIVATE, IM_MESSAGE_SYSTEM)))
			return false;

		$relationError = true;
		$chatRelation = CIMChat::GetRelationById($chatId);
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
			CFile::DeLete($chat['AVATAR']);
		}
		IM\Model\ChatTable::update($chatId, Array('AVATAR' => $fileId));

		$file['chatId'] = $chatId;
		$file['chatAvatar'] = CIMChat::GetAvatarImage($fileId);

		if ($chat["ENTITY_TYPE"] != 'CALL')
		{
			CIMChat::AddSystemMessage(Array(
				'CHAT_ID' => $chatId,
				'USER_ID' => \Bitrix\Im\Common::getUserId($userId),
				'MESSAGE_CODE' => 'IM_DISK_AVATAR_CHANGE_'
			));
		}

		if (CModule::IncludeModule('pull'))
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

			if ($chat['TYPE'] == IM_MESSAGE_OPEN || $chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
			{
				CPullWatch::AddToStack('IM_PUBLIC_'.$chat['ID'], $pullMessage);
			}
		}

		return true;
	}

	public static function GetHistoryFiles($chatId, $historyPage = 1)
	{
		$fileArray = Array();
		if (!self::Enabled())
			return $fileArray;

		if (intval($chatId) <= 0)
			return $fileArray;

		$offset = intval($historyPage)-1;
		if ($offset < 0)
			return $fileArray;


		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return $fileArray;
		}

		$filter = Array(
			'PARENT_ID' => $folderModel->getId(),
			'STORAGE_ID' => $folderModel->getStorageId()
		);

		$relation = CIMChat::GetRelationById($chatId, self::GetUserId());
		if (!$relation)
			return $fileArray;

		if ($relation['LAST_FILE_ID'] > 0)
		{
			$filter['>ID'] = $relation['LAST_FILE_ID'];
		}

		/*
		 * See details \Bitrix\Im\Disk\ProxyType\Im::getSecurityContextByUser
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

	public static function GetHistoryFilesByName($chatId, $name)
	{
		$fileArray = Array();
		if (!self::Enabled())
			return $fileArray;

		if (intval($chatId) <= 0)
			return $fileArray;

		$name = trim($name);
		if ($name == '')
			return $fileArray;

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
		{
			return $fileArray;
		}

		$filter = Array(
			'PARENT_ID' => $folderModel->getId(),
			'STORAGE_ID' => $folderModel->getStorageId(),
			'%=NAME' => str_replace("%", '', $name)."%",
		);

		$relation = CIMChat::GetRelationById($chatId, self::GetUserId());
		if (!$relation)
			return $fileArray;

		if ($relation['LAST_FILE_ID'] > 0)
		{
			$filter['>ID'] = $relation['LAST_FILE_ID'];
		}

		/*
		 * See details \Bitrix\Im\Disk\ProxyType\Im::getSecurityContextByUser
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

	public static function GetMaxFileId($chatId)
	{
		$maxId = 0;
		if (!self::Enabled())
			return $maxId;

		if (intval($chatId) <= 0)
			return $maxId;

		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
			return $maxId;

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
			$maxId = $data['MAX_ID'];

		return intval($maxId);
	}

	public static function GetFiles($chatId, $fileId = false, $checkPermission = true)
	{
		$fileArray = Array();
		if (!self::Enabled())
			return $fileArray;

		if (intval($chatId) <= 0)
			return $fileArray;

		if ($fileId === false || $fileId === null)
		{
			if (!is_array($fileId))
			{
				$fileId = Array($fileId);
			}
			foreach ($fileId as $key => $value)
			{
				$fileId[$key] = intval($value);
			}
		}
		if (empty($fileId))
		{
			return $fileArray;
		}
		$folderModel = self::GetFolderModel($chatId);
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

	public static function GetFileParams($chatId, $fileModel)
	{
		if (!self::Enabled())
			return false;

		if ($fileModel instanceof \Bitrix\Disk\File)
		{
		}
		else if (intval($fileModel) > 0)
		{
			$fileModel = \Bitrix\Disk\File::getById($fileModel);
		}
		else
		{
			return false;
		}

		if ($fileModel->getId() <= 0)
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
			$imageParams = Array(
				'width' => (int)$params['WIDTH'],
				'height' => (int)$params['HEIGHT'],
			);
		}
		else if (\Bitrix\Disk\TypeFile::isVideo($fileModel->getName()))
		{
			$contentType = 'video';
			$params = $fileModel->getView()->getPreviewData();
			$imageParams = Array(
				'width' => (int)$params['WIDTH'],
				'height' => (int)$params['HEIGHT'],
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

		try
		{
			$viewerType = \Bitrix\Disk\Ui\FileAttributes::buildByFileId($fileModel->getFileId(), $fileData['urlDownload'])
				->setObjectId($fileModel->getId())
				->setGroupBy($chatId)
				->setTitle($fileModel->getName())
				->addAction([
					'type' => 'download',
				])
				->addAction([
					'type' => 'copyToMe',
					'text' => GetMessage('IM_DISK_ACTION_SAVE_TO_OWN_FILES'),
					'action' => 'BXIM.disk.saveToDiskAction',
					'params' => [
						'fileId' => $fileModel->getId(),
					],
					'extension' => 'disk.viewer.actions',
					'buttonIconClass' => 'ui-btn-icon-cloud',
				]);
			;
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

		return $fileData;
	}

	public static function Enabled()
	{
		if (!CModule::IncludeModule('pull') || !CPullOptions::GetNginxStatus())
			return false;

		if (!CModule::IncludeModule('disk'))
			return false;

		if (!\Bitrix\Disk\Driver::isSuccessfullyConverted())
			return false;

		return true;
	}

	public static function GetFolderModel($chatId)
	{
		if (!self::Enabled())
			return false;

		$folderModel = false;

		$result = IM\Model\ChatTable::getById($chatId);
		if (!$chat = $result->fetch())
			return false;

		$folderId = intval($chat['DISK_FOLDER_ID']);
		$chatType = $chat['TYPE'];
		if ($folderId > 0)
		{
			$folderModel = \Bitrix\Disk\Folder::getById($folderId);
			if (!$folderModel || $folderModel->getStorageId() != self::GetStorageId())
			{
				$folderId = 0;
			}
		}

		if (!$folderId)
		{
			$driver = \Bitrix\Disk\Driver::getInstance();
			$storageModel = self::GetStorage();
			if (!$storageModel)
			{
				return false;
			}

			$rightsManager = $driver->getRightsManager();
			$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);

			$accessCodes = array();
			$accessCodes[] = Array(
				'ACCESS_CODE' => 'AU',
				'TASK_ID' => $fullAccessTaskId,
				'NEGATIVE' => 1
			);

			$chatRelation = CIMChat::GetRelationById($chatId);
			if ($chatType == IM_MESSAGE_OPEN)
			{
				$departmentCode = self::GetTopDepartmentCode();
				if ($departmentCode)
				{
					$accessCodes[] = Array(
						'ACCESS_CODE' => $departmentCode,
						'TASK_ID' => $fullAccessTaskId
					);
				}
				$users = CIMContactList::GetUserData(array(
					'ID' => array_keys($chatRelation),
					'DEPARTMENT' => 'N',
					'SHOW_ONLINE' => 'N',
				));
				foreach ($users['users'] as $userData)
				{
					if ($userData['extranet'])
					{
						$accessCodes[] = Array(
							'ACCESS_CODE' => 'U'.$userData['id'],
							'TASK_ID' => $fullAccessTaskId
						);
					}
				}
			}
			else
			{
				foreach ($chatRelation as $relation)
				{
					$accessCodes[] = Array(
						'ACCESS_CODE' => 'U'.$relation['USER_ID'],
						'TASK_ID' => $fullAccessTaskId
					);
				}
			}

			$folderModel = $storageModel->addFolder(array('NAME' => 'chat'.$chatId, 'CREATED_BY' => self::GetUserId()), $accessCodes, true);
			if ($folderModel)
				IM\Model\ChatTable::update($chatId, Array('DISK_FOLDER_ID' => $folderModel->getId()));
		}

		return $folderModel;
	}

	public static function ChangeFolderMembers($chatId, $userId, $append = true)
	{
		$folderModel = self::GetFolderModel($chatId);
		if (!$folderModel)
			return false;

		$result = IM\Model\ChatTable::getById($chatId);
		if (!$chat = $result->fetch())
			return false;

		if (!is_array($userId))
			$userIds = Array($userId);
		else
			$userIds = $userId;

		$driver = \Bitrix\Disk\Driver::getInstance();
		$rightsManager = $driver->getRightsManager();
		if ($append)
		{
			$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);

			$accessCodes = Array();
			if ($chat['TYPE'] == IM_MESSAGE_OPEN)
			{
				$users = CIMContactList::GetUserData(array(
					'ID' => array_values($userIds),
					'DEPARTMENT' => 'N',
					'SHOW_ONLINE' => 'N',
				));
				foreach ($users['users'] as $userData)
				{
					if ($userData['extranet'])
					{
						$accessCodes[] = Array(
							'ACCESS_CODE' => 'U'.$userData['id'],
							'TASK_ID' => $fullAccessTaskId
						);
					}
				}
			}
			else
			{
				foreach ($userIds as $userId)
				{
					$userId = intval($userId);
					if ($userId <= 0)
						continue;

					$accessCodes[] = array(
						'ACCESS_CODE' => 'U'.$userId,
						'TASK_ID' => $fullAccessTaskId,
						'NEGATIVE' => 0
					);
				}
			}
			if (count($accessCodes) <= 0)
				return false;

			$result = $rightsManager->append($folderModel, $accessCodes);
		}
		else
		{
			$accessCodes = Array();
			if ($chat['TYPE'] == IM_MESSAGE_OPEN)
			{
				$users = CIMContactList::GetUserData(array(
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
				foreach ($userIds as $userId)
				{
					$userId = intval($userId);
					if ($userId <= 0)
						continue;

					$accessCodes[] = 'U'.$userId;
				}
			}
			$result = $rightsManager->revokeByAccessCodes($folderModel, $accessCodes);
		}

		return $result;
	}

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
		$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);

		$folderModel = $backgroundFolderModel->addSubFolder([
			'NAME' => 'CALL_BACKGROUND_'.$userId,
			'CODE' => 'CALL_BACKGROUND_'.$userId,
			'CREATED_BY' => $userId,
		], [
			['ACCESS_CODE' => 'AU', 'TASK_ID' => $fullAccessTaskId, 'NEGATIVE' => 1],
			['ACCESS_CODE' => 'U'.$userId, 'TASK_ID' => $fullAccessTaskId],
		], true);

		return $folderModel;
	}

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

	public static function GetStorageId()
	{
		return COption::GetOptionInt('im', 'disk_storage_id', 0);
	}

	public static function SetStorageId($id)
	{
		$id = intval($id);
		if ($id <= 0)
			return false;

		$oldId = self::GetStorageId();
		if ($oldId > 0 && $oldId != $id)
		{
			global $DB;
			$DB->Query("UPDATE b_im_chat SET DISK_FOLDER_ID = 0");
			$DB->Query("DELETE FROM b_im_message_param WHERE PARAM_NAME = 'FILE_ID'");
		}

		COption::SetOptionInt('im', 'disk_storage_id', $id);

		return true;
	}

	public static function GetLocalDiskFolderPath()
	{
		if (!self::Enabled())
			return '';

		$folderModel = self::GetLocalDiskSavedModel();
		if (!$folderModel)
			return '';

		return \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlFocusController('openFolderList', array('folderId' => $folderModel->getId()));
	}

	public static function GetLocalDiskFilePath($fileId = 0)
	{
		if (!self::Enabled())
			return '';

		$fileId = intval($fileId);

		return \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlFocusController('showObjectInGrid', array('objectId' => $fileId? $fileId: '_FILE_ID_'));
	}

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

		$shortLink = $host.\CBXShortUri::GetShortUri($link);
		if ($isImage)
		{
			$shortLink .= '#img.'.$fileModel->getExtension();
		}

		return $shortLink;
	}

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

	public static function GetUserId()
	{
		global $USER;
		return is_object($USER)? intval($USER->GetID()): 0;
	}


	public static function EnabledExternalLink()
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
			return false;

		return \Bitrix\Disk\Configuration::isEnabledExternalLink();
	}

	public static function SetEnabledExternalLink($flag = true)
	{
		if (!\Bitrix\Main\Loader::includeModule('disk'))
			return false;

		if (!CIMMessenger::IsAdmin())
			return false;

		\Bitrix\Main\Config\Option::set('disk', 'disk_allow_use_external_link', $flag? 'Y': 'N');

		return true;
	}

	public static function GetTopDepartmentCode()
	{
		if (!CModule::IncludeModule("iblock"))
			return false;

		$code = false;
		$res = CIBlock::GetList(array(), array("CODE" => "departments"));
		if ($iblock = $res->Fetch())
		{
			$res = CIBlockSection::GetList(
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

	public static function OnAfterDeleteFile($fileId, $userId, $fileParams = Array())
	{
		if (!isset($fileParams['STORAGE_ID']) || $fileParams['STORAGE_ID'] != self::GetStorageId())
		{
			return true;
		}

		$messageId = \CIMMessageParam::GetMessageIdByParam('FILE_ID', $fileId);
		\CIMMessageParam::DeleteByParam('FILE_ID', $fileId);
		\CIMMessageParam::SendPull($messageId, Array('FILE_ID'));

		return true;
	}
}
?>
