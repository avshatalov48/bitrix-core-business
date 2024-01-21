<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Link\Task\TaskService;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Rest\RestError;
use Bitrix\Main\Engine\AutoWire\ExactParameter;

class Task extends BaseController
{
	/**
	 * @restMethod im.v2.Chat.Task.prepare
	 */
	public function prepareAction(?Chat $chat = null, ?\Bitrix\Im\V2\Message $message = null): ?array
	{
		if (isset($message)) {
			$chat = $message->getChat();
			if (!$chat->hasAccess())
			{
				$this->addError(new RestError(RestError::ACCESS_ERROR));

				return null;
			}
		}

		if (!isset($chat))
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::TASK_ACCESS_ERROR));

			return null;
		}

		$taskService = new TaskService();
		$result = $taskService->prepareDataForCreateSlider($chat, $message);
		if (!$result->isSuccess())
		{
			$error = $result->getErrors()[0];
			if (isset($error))
			{
				$this->addError($error);

				return null;
			}
		}

		return [
			'link' => $result->getResult()['LINK'],
			'params' => $result->getResult()['PARAMS']
		];
	}
}