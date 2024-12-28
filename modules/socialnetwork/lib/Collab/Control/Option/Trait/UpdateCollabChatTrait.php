<?php

namespace Bitrix\Socialnetwork\Collab\Control\Option\Trait;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Integration\IM;

trait UpdateCollabChatTrait
{
	protected function updateChat(Collab $collab, array $fields): Result
	{
		$result = new Result();

		if (empty($fields))
		{
			return $result;
		}

		$updateService = IM\Messenger::getUpdateService($collab->getChatId(), $fields);

		if ($updateService === null)
		{
			$result->addError(new Error('Update service not found'));

			return $result;
		}

		$updateResult = $updateService->updateChat();
		if (!$updateResult->isSuccess())
		{
			$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}
}