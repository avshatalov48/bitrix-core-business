<?php

namespace Bitrix\Im\V2\Controller\Import;

use Bitrix\Im\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Import\ImportError;
use Bitrix\Im\V2\Import\ImportService;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;

class Message extends Controller
{
	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new \Bitrix\Rest\Engine\ActionFilter\Scope('im.import'),
				new \Bitrix\Main\Engine\ActionFilter\Scope(\Bitrix\Main\Engine\ActionFilter\Scope::REST),
				new \Bitrix\Rest\Engine\ActionFilter\AuthType(\Bitrix\Rest\Engine\ActionFilter\AuthType::APPLICATION)
			]
		);
	}

	public function addAction(int $chatId, array $messages, CurrentUser $user): ?array
	{
		if (count($messages) > 2000)
		{
			$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

			return null;
		}

		$chat = Chat::getById($chatId, ['CHECK_ACCESS' => 'N']);
		if (!$chat)
		{
			$this->addError(new ChatError(ChatError::NOT_FOUND));

			return null;
		}

		$importService = new ImportService($chat, (int)$user->getId());

		if (!$importService->hasAccess())
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}

		$addResult = $importService->addMessages($messages);

		if (!$addResult->isSuccess())
		{
			$this->addErrors($addResult->getErrors());

			return null;
		}

		return $this->convertKeysToCamelCase($addResult->getResult());
	}

	public function updateAction(array $messages, int $chatId, CurrentUser $user): ?array
	{
		if (count($messages) > 2000)
		{
			$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

			return null;
		}

		$chat = Chat::getById($chatId, ['CHECK_ACCESS' => 'N']);
		if (!$chat)
		{
			$this->addError(new ChatError(ChatError::NOT_FOUND));

			return null;
		}

		$importService = new ImportService($chat, (int)$user->getId());

		if (!$importService->hasAccess())
		{
			$this->addError(new ImportError(ImportError::ACCESS_ERROR));

			return null;
		}

		$updateResult = $importService->updateMessages($messages);

		if (!$updateResult->isSuccess())
		{
			$this->addErrors($updateResult->getErrors());

			return null;
		}

		return $this->convertKeysToCamelCase($updateResult->getResult());
	}
}