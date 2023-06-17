<?php

namespace Bitrix\Im\V2\Link\Task;

use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Im\Dialog;
use Bitrix\Im\Model\LinkTaskTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\File\TemporaryFileService;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Entity\Task\TaskError;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Web\Json;

class TaskService
{
	use ContextCustomer;

	protected const SIGNATURE_SALT = 'task_service_salt';
	protected const ADD_TASK_EVENT = 'taskAdd';
	protected const UPDATE_TASK_EVENT = 'taskUpdate';
	protected const DELETE_TASK_EVENT = 'taskDelete';

	public function registerTask(int $chatId, int $messageId, \Bitrix\Im\V2\Entity\Task\TaskItem $taskItem): Result
	{
		$result = new Result();

		$userId = $this->getContext()->getUserId();

		$taskLink = new TaskItem();
		$taskLink->setEntity($taskItem)->setChatId($chatId)->setAuthorId($userId);

		if ($messageId !== 0)
		{
			$taskLink->setMessageId($messageId);
		}

		$sendMessageResult = $this->sendMessageAboutTask($taskLink, $chatId);

		if (!$sendMessageResult->isSuccess())
		{
			$result->addErrors($sendMessageResult->getErrors());
		}

		$systemMessageId = $sendMessageResult->getResult();

		$taskLink->setMessageId($messageId ?: $systemMessageId);
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

		$data['LINK'] = str_replace(
			['#USER_ID#', '#ID#'],
			$userId,
			\Bitrix\Main\Config\Option::get('intranet', 'search_user_url', SITE_DIR . 'company/personal/user/#USER_ID#/')
		) . 'tasks/task/edit/0/';

		$chatTypeModifier = ($chat->getType() === \IM_MESSAGE_PRIVATE) ? 'PRIVATE_' : '';
		$from = isset($message) ? 'MESSAGE' : 'CHAT';
		$from = $chatTypeModifier . $from;

		$data['PARAMS']['TITLE'] = Loc::getMessage(
			"IM_CHAT_TASK_SERVICE_FROM_{$from}_NEW_TITLE",
			["#CHAT_TITLE#" => $chat->getTitle()]
		);

		$data['PARAMS']['RESPONSIBLE_ID'] = $userId;

		$userIds = $chat->getRelations(
			[
				'SELECT' => ['ID', 'USER_ID', 'CHAT_ID'],
				'FILTER' => ['ACTIVE' => true, 'ONLY_INTERNAL_TYPE' => true],
				'LIMIT' => 50,
			]
		)->getUsers()->filterExtranet()->getIds();
		unset($userIds[$userId]);
		$data['PARAMS']['AUDITORS'] = implode(",", $userIds);

		$data['PARAMS']['IM_CHAT_ID'] = $chat->getChatId();

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

			$fileIds = $this->getFilesIdsForTaskFromMessage($message);

			if (!empty($fileIds))
			{
				$diskFileUFCode = \Bitrix\Tasks\Integration\Disk\UserField::getMainSysUFCode();
				$data['PARAMS'][$diskFileUFCode] = $fileIds;
				$signer = new Signer();
				$data['PARAMS'][$diskFileUFCode . '_SIGN'] = $signer->sign(Json::encode($fileIds), static::SIGNATURE_SALT);
			}

			$data['PARAMS']['IM_MESSAGE_ID'] = $message->getMessageId();
		}

		return $result->setResult($data);
	}

	protected function sendMessageAboutTask(TaskItem $taskLink, int $chatId): Result
	{
		//todo: Replace with new API
		$dialogId = Dialog::getDialogId($chatId);
		$authorId = $this->getContext()->getUserId();

		$messageId = \CIMChat::AddMessage([
			'DIALOG_ID' => $dialogId,
			'SYSTEM' => 'Y',
			'MESSAGE' => $this->getMessageText($taskLink),
			'FROM_USER_ID' => $authorId,
			'PARAMS' => ['CLASS' => "bx-messenger-content-item-system"],
			'URL_PREVIEW' => 'N',
			'SKIP_CONNECTOR' => 'Y',
			'SKIP_COMMAND' => 'Y',
			'SILENT_CONNECTOR' => 'Y',
			'SKIP_URL_INDEX' => 'Y',
		]);

		$result = new Result();

		if ($messageId === false)
		{
			return $result->addError(new TaskError(TaskError::ADD_TASK_MESSAGE_FAILED));
		}

		return $result->setResult($messageId);
	}

	/**
	 * @param Message $message
	 * @return string[]
	 */
	protected function getFilesIdsForTaskFromMessage(Message $message): array
	{
		$copies = $message->getFiles()->getCopies();
		$copies->addToTmp(TemporaryFileService::TASK_SOURCE);
		$newIds = [];

		foreach ($copies as $copy)
		{
			$newIds[] = FileUserType::NEW_FILE_PREFIX . $copy->getId();
		}

		return $newIds;
	}

	protected function getFilesIdsByNewFilesIds(array $newIds): array
	{
		$fileIdList = array_map(static function($value) {
			return (
			preg_match('/^' . FileUserType::NEW_FILE_PREFIX . '(\d+)$/i', $value, $matches)
				? (int)$matches[1]
				: 0
			);
		}, $newIds);
		$fileIdList = array_filter($fileIdList, static function($value) {
			return ($value > 0);
		});
		$fileIdList = array_unique($fileIdList);

		return $fileIdList;
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

	protected function getMessageText(TaskItem $task): string
	{
		$genderModifier = ($this->getContext()->getUser()->getGender() === 'F') ? '_F' : '';

		if ($task->getMessageId() !== null)
		{
			$text = (new Message($task->getMessageId()))->getQuotedMessage() . "\n";
			$text .= Loc::getMessage(
				'IM_CHAT_TASK_REGISTER_FROM_MESSAGE_NOTIFICATION' . $genderModifier,
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
			'IM_CHAT_TASK_REGISTER_FROM_CHAT_NOTIFICATION' . $genderModifier,
			[
				'#LINK#' => $task->getEntity()->getUrl(),
				'#USER_ID#' => $this->getContext()->getUserId(),
				'#TASK_TITLE#' => $task->getEntity()->getTitle(),
			]
		);
	}
}