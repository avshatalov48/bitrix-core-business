<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Handler\Delete;

use Bitrix\Socialnetwork\Collab\Integration\IM\Chat;
use Bitrix\Socialnetwork\Control\Command\DeleteCommand;
use Bitrix\Socialnetwork\Control\Handler\Delete\DeleteHandlerInterface;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;

class DeleteChatHandler implements DeleteHandlerInterface
{
	public function delete(DeleteCommand $command, Workgroup $entityBefore): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$chatResult = Chat::deleteByChatId($entityBefore->getChatId());

		return $handlerResult->merge($chatResult);
	}
}