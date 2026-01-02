<?php

namespace Bitrix\Im\V2\Link\Task;

use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Im\Model\LinkTaskTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChannelChat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\File\TemporaryFileService;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Entity\Task\TaskError;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\Message\Send\SendResult;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Tasks\Slider\Path\PathMaker;
use Bitrix\Tasks\Slider\Path\TaskPathMaker;

class TaskService
{
	use ContextCustomer;

	protected const SIGNATURE_SALT = 'task_service_salt';
	protected const ADD_TASK_EVENT = 'taskAdd';
	protected const UPDATE_TASK_EVENT = 'taskUpdate';
	protected const DELETE_TASK_EVENT = 'taskDelete';

	public function registerTask(Chat $chat, int $messageId, \Bitrix\Im\V2\Entity\Task\TaskItem $taskItem): Result
	{
		$result = new Result();

		$userId = $this->getContext()->getUserId();

		$taskLink = new TaskItem();
		$taskLink->setEntity($taskItem)->setChatId($chat->getId())->setAuthorId($userId);

		if ($messageId !== 0)
		{
			$taskLink->setMessageId($messageId);
		}

		if ($chat->needToSendTaskCreationMessage())
		{
			$sendMessageResult = $this->sendMessageAboutTask($taskLink, $chat);

			if (!$sendMessageResult->isSuccess())
			{
				$result->addErrors($sendMessageResult->getErrors());
			}

			$systemMessageId = $sendMessageResult->getMessageId();

			$taskLink->setMessageId($messageId ?: $systemMessageId);
		}

		$saveResult = $taskLink->save();

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		Push::getInstance()
			->setContext($this->context)
			->sendFull($taskLink, self::ADD_TASK_EVENT, ['RECIPIENT' => $taskItem->getMembersIds()])
		;

		return $result;
	}

	public function unregisterTaskByEntity(\Bitrix\Im\V2\Entity\Task\TaskItem $taskEntity, bool $saveDelete): Result
	{
		$taskItem = TaskItem::getByEntity($taskEntity);

		if ($taskItem === null)
		{
			return new Result();
		}

		return $this->unregisterTask($taskItem, $saveDelete);
	}

	public function unregisterTask(TaskItem $task, bool $saveDelete): Result
	{
		Push::getInstance()
			->setContext($this->context)
			->sendIdOnly($task, self::DELETE_TASK_EVENT, ['CHAT_ID' => $task->getChatId()])
		;
		if (!$saveDelete)
		{
			$task->delete();
			TaskItem::cleanCache($task->getEntityId() ?? 0);
		}

		return new Result();
	}

	public function updateTask(\Bitrix\Im\V2\Entity\Task\TaskItem $taskEntity): Result
	{
		$taskItem = TaskItem::getByEntity($taskEntity);
		if ($taskItem === null)
		{
			return new Result();
		}

		Push::getInstance()
			->setContext($this->context)
			->sendFull($taskItem, self::UPDATE_TASK_EVENT, ['RECIPIENT' => $taskEntity->getMembersIds()])
		;

		return new Result();
	}

	public function updateTaskLink(TaskItem $taskItem): Result
	{
		$result = new Result();

		$saveResult = $taskItem->save();

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		Push::getInstance()
			->setContext($this->context)
			->sendFull($taskItem, self::UPDATE_TASK_EVENT, ['RECIPIENT' => $taskItem->getEntity()->getMembersIds()])
		;

		return $result;
	}

	public function updateTaskLinks(TaskCollection $taskCollection): Result
	{
		$result = new Result();

		if ($taskCollection->count() === 0)
		{
			return $result;
		}

		$saveResult = $taskCollection->save();

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		$taskCollection->fillEntities();

		foreach ($taskCollection as $task)
		{
			$pushRecipient = ['RECIPIENT' => $task->getEntity()->getMembersIds()];
			Push::getInstance()
				->setContext($this->context)
				->sendFull($task, self::UPDATE_TASK_EVENT, $pushRecipient)
			;
		}

		return $result;
	}

	public function deleteLinkByTaskId(int $taskId): Result
	{
		LinkTaskTable::deleteByFilter(['=TASK_ID' => $taskId]);

		return new Result();
	}

	public function prepareDataForCreateSlider(Chat $chat, ?Message $message = null): Result
	{
		$result = new Result();

		if (!Loader::includeModule('tasks'))
		{
			return $result->addError(new TaskError(TaskError::TASKS_NOT_INSTALLED));
		}

		$userId = $this->getContext()->getUserId();

		$chat->setContext($this->context);

		$data = ['PARAMS' => []];

		$taskPath = (new TaskPathMaker(0, PathMaker::EDIT_ACTION, $userId))->makeEntityPath();
		$link = new Uri($taskPath);
		$link->addParams([
			'ta_sec' => 'chat',
			'ta_el' => 'comment_context_menu',
		]);

		$data['LINK'] = $link->getUri();
		$data['PARAMS']['RESPONSIBLE_ID'] = $userId;
		$data['PARAMS']['IM_CHAT_ID'] = $chat->getChatId();

		if ($chat->getEntityType() !== 'SONET_GROUP')
		{
			$data['PARAMS']['AUDITORS'] = implode(",", $this->getAuditors($chat));
		}

		if ($chat->getEntityType() === 'SONET_GROUP')
		{
			$data['PARAMS']['GROUP_ID'] = (int)$chat->getEntityId();
		}

		if ($chat instanceof Chat\OpenLineChat && Loader::includeModule('crm'))
		{
			$entityData = explode('|', $chat->getEntityData1() ?? '');
			if (isset($entityData[0], $entityData[1], $entityData[2]) && $entityData[0] === 'Y')
			{
				$crmType = \CCrmOwnerTypeAbbr::ResolveByTypeID(\CCrmOwnerType::ResolveID($entityData[1]));
				$data['PARAMS']['UF_CRM_TASK'] = $crmType.'_'.$entityData[2];
			}
		}

		if (isset($message))
		{
			$message->setContext($this->context);
			$data['PARAMS']['DESCRIPTION'] = \CIMShare::PrepareText([
				'CHAT_ID' => $chat->getChatId(),
				'MESSAGE_ID' => $message->getMessageId(),
				'MESSAGE_TYPE' => $chat->getType(),
				'MESSAGE' => $message->getMessage(),
				'AUTHOR_ID' => $message->getAuthorId(),
				'FILES' => $this->getFilesForPrepareText($message)
			]);

			$files = $this->getFilesDataForTaskFromMessage($message);

			if (!empty($files))
			{
				$fileIds = $this->getFilesIds($files);

				$diskFileUFCode = \Bitrix\Tasks\Integration\Disk\UserField::getMainSysUFCode();
				$data['PARAMS'][$diskFileUFCode] = $fileIds;
				$signer = new Signer();
				$data['PARAMS'][$diskFileUFCode . '_SIGN'] = $signer->sign(Json::encode($fileIds), static::SIGNATURE_SALT);
				$data['PARAMS'][$diskFileUFCode . '_DATA'] = $files;
			}

			$data['PARAMS']['IM_MESSAGE_ID'] = $message->getMessageId();
		}

		$data['PARAMS']['is_tasks_v2'] = $this->isTasksV2Form();

		if ($data['PARAMS']['is_tasks_v2'])
		{
			$data['PARAMS']['entityId'] = $chat->getChatId();
			$data['PARAMS']['subEntityId'] = $data['PARAMS']['IM_MESSAGE_ID'] ?? null;
			$data['PARAMS']['ta_sec'] = 'chat';
			$data['PARAMS']['ta_el'] = 'comment_context_menu';
			$data['PARAMS']['description'] = $data['PARAMS']['DESCRIPTION'] ?? null;
			$data['PARAMS']['auditors'] = $data['PARAMS']['AUDITORS'] ?? null;
			$data['PARAMS']['groupId'] = $data['PARAMS']['GROUP_ID'] ?? null;
		}

		return $result->setResult($data);
	}

	protected function sendMessageAboutTask(TaskItem $taskLink, Chat $chat): SendResult
	{
		$authorId = $this->getContext()->getUserId();
		$messageText = $this->getTaskMessageText($taskLink);

		$message =
			(new Message())
				->setAuthorId($authorId)
				->setChatId($chat->getId())
				->setMessage($messageText)
				->markAsSystem(true)
				->addParam(Params::STYLE_CLASS, 'bx-messenger-content-item-system')
		;

		$sendingConfig =
			(new SendingConfig())
				->disableGenerateUrlPreview()
				->enableSkipConnectorSend()
				->enableSkipCommandExecution()
				->enableKeepConnectorSilence()
				->enableSkipUrlIndex()
		;

		$result = $chat->sendMessage($message, $sendingConfig);

		if (!$result->isSuccess())
		{
			return $result->addError(new TaskError(TaskError::ADD_TASK_MESSAGE_FAILED));
		}

		return $result;
	}

	/**
	 * @param Message $message
	 * @return string[]
	 */
	protected function getFilesDataForTaskFromMessage(Message $message): array
	{
		$copies = $message->getFiles()->copyToOwnUploadedFiles()->getResult();
		if (!isset($copies))
		{
			return [];
		}

		$copies->addToTmp(TemporaryFileService::TASK_SOURCE);
		$files = [];

		foreach ($copies as $file)
		{
			$fileData = $file->toRestFormat();
			$files[] = [
				'id' => FileUserType::NEW_FILE_PREFIX . $file->getId(),
				'objectId' => $fileData['viewerAttrs']['objectId'],
				'name' => $fileData['name'],
				'type' => $fileData['extension'],
				'url' => $fileData['urlShow'],
				'height' => $fileData['image']['height'] ?? 0,
				'width' => $fileData['image']['width'] ?? 0,
				'preview_url' => $fileData['urlPreview'],
			];
		}

		return $files;
	}

	protected function getFilesIds(array $files): array
	{
		$fileIds = [];
		foreach ($files as $file)
		{
			if (isset($file['id']))
			{
				$fileIds[] = $file['id'];
			}
		}

		return $fileIds;
	}

	protected function getAuditors(Chat $chat): array
	{
		if ($chat instanceof ChannelChat)
		{
			return [];
		}

		return RelationCollection::find(
			[
				'ACTIVE' => true,
				'ONLY_INTERNAL_TYPE' => true,
				'CHAT_ID' => $chat->getId(),
				'IS_HIDDEN' => false,
				'ONLY_INTRANET' => true,
				'!USER_ID' => $this->getContext()->getUserId()
			],
			limit: 50,
			select: ['ID', 'USER_ID', 'CHAT_ID']
		)->getUserIds();
	}

	protected function getFilesForPrepareText(Message $message): array
	{
		$files = $message->getFiles();
		$filesForPrepare = [];

		foreach ($files as $file)
		{
			$filesForPrepare[] = ['name' => $file->getDiskFile()->getName()];
		}

		return $filesForPrepare;
	}

	protected function getTaskMessageText(TaskItem $task): string
	{
		$genderModifier = ($this->getContext()->getUser()->getGender() === 'F') ? '_F' : '';

		if ($task->getMessageId() !== null)
		{
			$text = (new Message($task->getMessageId()))->getQuotedMessage() . "\n";
			$text .= Loc::getMessage(
				'IM_CHAT_TASK_REGISTER_FROM_MESSAGE_NOTIFICATION' . $genderModifier . '_MSGVER_1',
				[
					'#LINK#' => $task->getEntity()->getUrl(),
					'#USER_ID#' => $this->getContext()->getUserId(),
					'#MESSAGE_ID#' => $task->getMessageId(),
					'#DIALOG_ID#' => Chat::getInstance($task->getChatId())->getDialogContextId(),
				]
			);

			return $text;
		}
		return Loc::getMessage(
			'IM_CHAT_TASK_REGISTER_FROM_CHAT_NOTIFICATION' . $genderModifier . '_MSGVER_1',
			[
				'#LINK#' => $task->getEntity()->getUrl(),
				'#USER_ID#' => $this->getContext()->getUserId(),
				'#TASK_TITLE#' => $task->getEntity()->getTitle(),
			]
		);
	}

	private function isTasksV2Form(): bool
	{
		return class_exists(\Bitrix\Tasks\V2\FormV2Feature::class)
			&& \Bitrix\Tasks\V2\FormV2Feature::isOn()
		;
	}
}
