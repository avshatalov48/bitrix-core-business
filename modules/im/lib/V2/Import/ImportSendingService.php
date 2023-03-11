<?php

namespace Bitrix\Im\V2\Import;

use Bitrix\Disk\File;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Error;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

class ImportSendingService
{
	private array $chat;
	private ?DateTime $lastDateCreate;

	public function __construct(array $chat)
	{
		$this->chat = $chat;
	}

	public function addMessages(array $messages): Result
	{
		$resultArray = [
			'SUCCESS_RESULT' => [],
			'ERROR_RESULT' => [],
		];

		$messages = $this->fillFiles($messages);

		$addedFiles = [];
		foreach ($messages as $index => $message)
		{
			$externalId = $message['externalId'] === '' ? $index : $message['externalId'];
			$addResult = $this->addMessage($message);
			if (!$addResult->isSuccess())
			{
				$sendStubResult = $this->sendStubMessage($message);
				if (!$sendStubResult->isSuccess())
				{
					return $sendStubResult;
				}
				$error = $addResult->getErrors()[0];
				$resultArray['ERROR_RESULT'][] = [
					'ID' => $sendStubResult->getResult(),
					'EXTERNAL_ID' => $externalId,
					'ERROR_CODE' => $error->getCode(),
					'ERROR_MESSAGE' => $error->getMessage(),
				];
			}
			else
			{
				$resultArray['SUCCESS_RESULT'][] = [
					'ID' => $addResult->getResult()['ID'],
					'EXTERNAL_ID' => $externalId,
				];
				$this->lastDateCreate = $this->getDateTimeFromAtom($message['dateCreate']);
				if (isset($message['fileId']))
				{
					$addedFiles[] = (int)$message['fileId'];
				}
			}
		}
		$this->increaseFilesVersion($addedFiles);

		return (new Result())->setResult($resultArray);
	}

	public function updateMessages(array $messages): Result
	{
		$result = new Result();
		$resultArray = [
			'SUCCESS_RESULT' => [],
			'ERROR_RESULT' => [],
		];

		$ids = array_column($messages, 'id');
		$chatIds = $this->getChatIdsByMessageIds($ids);
		$messages = $this->fillFiles($messages);

		$addedFiles = [];
		foreach ($messages as $message)
		{
			$message['chatId'] = $chatIds[(int)$message['id']] ?? null;
			$updateResult = $this->updateMessage($message);
			if (!$updateResult->isSuccess())
			{
				$error = $updateResult->getErrors()[0];
				$resultArray['ERROR_RESULT'][] = [
					'ID' =>(int)$message['id'],
					'ERROR_CODE' => $error->getCode(),
					'ERROR_MESSAGE' => $error->getMessage(),
				];
			}
			else
			{
				$resultArray['SUCCESS_RESULT'][] = $updateResult->getResult();
				if (isset($message['fileId']))
				{
					$addedFiles[] = (int)$message['fileId'];
				}
			}
		}
		$this->increaseFilesVersion($addedFiles);

		return $result->setResult($resultArray);
	}

	private function fillFiles(array $messages): array
	{
		$files = $this->getFiles($messages);

		foreach ($messages as $index => $message)
		{
			if (isset($message['fileId']))
			{
				$messages[$index]['file'] = $files[(int)$message['fileId']] ?? null;
			}
		}

		return $messages;
	}

	private function getFiles(array $messages): array
	{
		$fileIds = [];

		foreach ($messages as $message)
		{
			if (isset($message['fileId']) && (int)$message['fileId'] > 0)
			{
				$fileIds[] = (int)$message['fileId'];
			}
		}

		if (empty($fileIds))
		{
			return [];
		}

		$files = File::getModelList(['filter' => ['ID' => $fileIds]]);
		$filesById = [];

		foreach ($files as $file)
		{
			$filesById[$file->getId()] = $file;
		}

		return $filesById;
	}

	private function increaseFilesVersion(array $files): void
	{
		if (empty($files))
		{
			return;
		}

		$implodeFileId = implode(',', $files);
		$sql = "UPDATE b_disk_object SET GLOBAL_CONTENT_VERSION=2 WHERE ID IN ({$implodeFileId})";
		Application::getConnection()->query($sql);
	}

	private function addMessage(array $message): Result
	{
		$result = new Result();

		$validateResult = $this->validateFields($message);
		if (!$validateResult->isSuccess())
		{
			return $validateResult;
		}
		$message = $validateResult->getResult();
		$messageFieldsResult = $this->getMessageFields($message);
		$messageId = \CIMMessenger::Add($messageFieldsResult);

		if ($messageId === false)
		{
			return $result->addError($this->getErrorLegacy());
		}

		return $result->setResult(['ID' => $messageId]);
	}

	private function checkFileAccess(array $message): Result
	{
		$result = new Result();

		if (!isset($message['file']))
		{
			return $result->addError(new ImportError(ImportError::FILE_NOT_FOUND));
		}
		if ((int)$message['file']->getParentId() !== (int)$this->chat['DISK_FOLDER_ID'])
		{
			return $result->addError(new ImportError(ImportError::FILE_ACCESS_ERROR));
		}

		return $result;
	}

	private function updateMessage(array $message): Result
	{
		$result = new Result();
		$id = (int)$message['id'];
		if ((int)$this->chat['ID'] !== $message['chatId'])
		{
			return $result->addError(new MessageError(MessageError::MESSAGE_NOT_FOUND));
		}

		$validateResult = $this->validateParams($message);

		if (!$validateResult->isSuccess())
		{
			return $validateResult;
		}

		$params = $validateResult->getResult();

		if (!empty($params))
		{
			\CIMMessageParam::Set($id, $params);
		}

		if (isset($message['message']))
		{
			$urlPreview = !(isset($message['urlPreview']) && $message['urlPreview'] === "N");
			$isSuccessUpdate = \CIMMessenger::Update($id, $message['message'], $urlPreview, false, null, false, true);
			if (!$isSuccessUpdate)
			{
				return $result->addError(new ImportError(ImportError::UPDATE_MESSAGE_ERROR));
			}
		}

		return $result->setResult(['ID' => $id]);
	}

	private function getErrorLegacy(): Error
	{
		global $APPLICATION;
		$error = $APPLICATION->GetException();
		if ($error instanceof \CAdminException)
		{
			$errorCode = $error->messages[0]['id'] ?? ImportError::ADD_MESSAGE_ERROR;
			$errorMessage = $error->messages[0]['text'] ?? '';
		}
		else
		{
			$errorCode = $error->GetID();
			$errorMessage = $error->GetString();
		}

		return new Error($errorCode, $errorMessage);
	}

	private function getChatIdsByMessageIds(array $messageIds): array
	{
		$result = [];
		if (empty($messageIds))
		{
			return [];
		}

		$messages = MessageTable::query()
			->setSelect(['ID', 'CHAT_ID'])
			->whereIn('ID', $messageIds)
			->fetchCollection()
		;

		foreach ($messages as $message)
		{
			$result[$message->getId()] = $message->getChatId();
		}

		return $result;
	}

	private function sendStubMessage(array $originalMessage): Result
	{
		$result = new Result();
		$chatId = (int)$this->chat['ID'];
		$originalDate = $this->getDateTimeFromAtom($originalMessage['dateCreate']);
		if (isset($originalDate) && !$this->hasDateError())
		{
			$date = $originalDate;
		}
		else
		{
			$date = $this->getLastDateCreate();
		}

		if (!isset($date))
		{
			return $result->addError(new ImportError(ImportError::DATETIME_FORMAT_ERROR_FIRST));
		}

		$messageId = \CIMMessenger::Add([
			'MESSAGE' => Loc::getMessage('IM_IMPORT_BROKEN_MESSAGE'),
			'MESSAGE_DATE' => $date->toString(),
			'FROM_USER_ID' => $originalMessage['authorId'] ?? 0,
			'TO_CHAT_ID' => $chatId,
			'MESSAGE_TYPE' => $this->chat['MESSAGE_TYPE'],
			'SYSTEM' => $originalMessage['system'],
			'URL_PREVIEW' => 'N',
			'PUSH' => 'N',
			'RECENT_ADD' => 'N',
			'SKIP_COMMAND' => 'Y',
			'SKIP_USER_CHECK' => 'Y',
			'CONVERT' => 'Y'
		]);

		return $result->setResult($messageId);
	}

	private function getLastDateCreate(): ?DateTime
	{
		if (isset($this->lastDateCreate))
		{
			return $this->lastDateCreate;
		}

		$result = MessageTable::query()
			->setSelect(['DATE_CREATE'])
			->where('CHAT_ID', (int)$this->chat['ID'])
			->setOrder(['DATE_CREATE' => 'DESC'])
			->setLimit(1)
			->fetch()
		;

		$this->lastDateCreate = $result ? $result['DATE_CREATE'] : null;

		return $this->lastDateCreate;
	}

	private function hasDateError(): bool
	{
		global $APPLICATION;
		$error = $APPLICATION->GetException();
		if ($error === false)
		{
			return false;
		}
		if ($error instanceof \CAdminException)
		{
			foreach ($error->messages as $message)
			{
				if ($message['id'] ?? '' === 'MESSAGE_DATE')
				{
					return true;
				}
			}
		}
		else
		{
			return $error->GetID() === 'MESSAGE_DATE';
		}

		return false;
	}

	private function validateFields(array $message): Result
	{
		$result = new Result();
		$dateCreate = $this->getDateTimeFromAtom($message['dateCreate']);
		if (!isset($dateCreate))
		{
			return $result->addError(new ImportError(ImportError::DATETIME_FORMAT_ERROR));
		}
		if ($this->getLastDateCreate() !== null && $dateCreate->getTimestamp() < $this->getLastDateCreate()->getTimestamp())
		{
			return $result->addError(new ImportError(ImportError::CHRONOLOGY_ERROR));
		}
		$validateParams = $this->validateParams($message);
		if (!$validateParams->isSuccess())
		{
			return $validateParams;
		}
		$params = $validateParams->getResult();

		$message['dateCreate'] = $dateCreate->toString();
		$message['keyboard'] = $params['KEYBOARD'] ?? null;
		$message['menu'] = $params['MENU'] ?? null;
		$message['attach'] = $params['ATTACH'] ?? null;
		$message['fileId'] = $params['FILE_ID'] ?? null;

		return $result->setResult($message);
	}

	private function validateParams(array $message): Result
	{
		$params = [];
		/** @var Result[] $results */
		$results = [
			'FILE_ID' => $this->getFileIdParams($message),
			'KEYBOARD' => $this->getKeyboard($message),
			'MENU' => $this->getMenu($message),
			'ATTACH' => $this->getAttach($message)
		];

		foreach ($results as $paramName => $result)
		{
			if (!$result->isSuccess())
			{
				return $result;
			}
			if ($result->getResult() !== null)
			{
				$params[$paramName] = $result->getResult();
			}
		}

		return (new Result())->setResult($params);
	}

	private function getMessageFields(array $message): array
	{
		return [
			'MESSAGE' => $message['message'],
			'MESSAGE_DATE' => $message['dateCreate'],
			'FROM_USER_ID' => (int)$message['authorId'],
			'TO_CHAT_ID' => (int)$this->chat['ID'],
			'MESSAGE_TYPE' => $this->chat['MESSAGE_TYPE'],
			'SYSTEM' => $message['system'],
			'ATTACH' => $message['attach'],
			'KEYBOARD' => $message['keyboard'],
			'MENU' => $message['menu'],
			'URL_PREVIEW' => 'N',
			'FILES' => $message['fileId'],
			'PUSH' => 'N',
			'RECENT_ADD' => 'N',
			'SKIP_COMMAND' => 'Y',
			'SKIP_USER_CHECK' => 'Y',
			'CONVERT' => 'Y'
		];
	}

	private function getDateTimeFromAtom(string $dateAtom): ?DateTime
	{
		return DateTime::tryParse($dateAtom, \DateTimeInterface::RFC3339);
	}

	private function getFileIdParams(array $message): Result
	{
		$result = new Result();
		$fileId = null;
		if (isset($message['fileId']))
		{
			$fileId = [];
			if ($message['fileId'] === 'N')
			{
				return $result->setResult([]);
			}
			$fileCheckResult = $this->checkFileAccess($message);
			if (!$fileCheckResult->isSuccess())
			{
				return $fileCheckResult;
			}
			$fileId = [(int)$message['fileId']];
		}

		return $result->setResult($fileId);
	}

	private function getKeyboard(array $message): Result
	{
		$result = new Result();
		$keyboard = null;
		if (isset($message['keyboard']))
		{
			if ($message['keyboard'] === 'N')
			{
				return $result->setResult('N');
			}
			$keyboard = [];
			if (!isset($message['keyboard']['BUTTONS']))
			{
				$keyboard['BUTTONS'] = $message['keyboard'];
			}
			else
			{
				$keyboard = $message['keyboard'];
			}
			$keyboard['BOT_ID'] = $message['botId'];
			$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
			if (!isset($keyboard))
			{
				return $result->addError(new ImportError('KEYBOARD_ERROR', 'Incorrect keyboard params'));
			}
			if (!$keyboard->isAllowSize())
			{
				return $result->addError(new ImportError('KEYBOARD_OVERSIZE', 'You have exceeded the maximum allowable size of keyboard'));
			}
		}

		return $result->setResult($keyboard);
	}

	private function getMenu(array $message): Result
	{
		$result = new Result();
		$menu = null;
		if (isset($message['menu']))
		{
			if ($message['menu'] === 'N')
			{
				return $result->setResult('N');
			}
			$menu = [];
			if (!isset($message['menu']['ITEMS']))
			{
				$menu['ITEMS'] = $message['menu'];
			}
			else
			{
				$menu = $message['menu'];
			}
			$menu['BOT_ID'] = $message['botId'];
			$menu = \Bitrix\Im\Bot\ContextMenu::getByJson($menu);
			if (!isset($menu))
			{
				return $result->addError(new ImportError('MENU_ERROR', 'Incorrect menu params'));
			}
			if (!$menu->isAllowSize())
			{
				return $result->addError(new ImportError('MENU_OVERSIZE', 'You have exceeded the maximum allowable size of menu'));
			}
		}

		return $result->setResult($menu);
	}

	private function getAttach(array $message): Result
	{
		$result = new Result();
		$attach = null;
		if (isset($message['attach']))
		{
			if ($message['attach'] === 'N')
			{
				return $result->setResult('N');
			}
			$attach = \CIMMessageParamAttach::GetAttachByJson($message['attach']);
			if (!isset($attach))
			{
				return $result->addError(new ImportError('ATTACH_ERROR', 'Incorrect attach params'));
			}
			if (!$attach->IsAllowSize())
			{
				return $result->addError(new ImportError('ATTACH_OVERSIZE', 'You have exceeded the maximum allowable size of attach'));
			}
		}

		return $result->setResult($attach);
	}
}