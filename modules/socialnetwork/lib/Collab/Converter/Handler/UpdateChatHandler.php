<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Handler;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Collab\Integration\IM\ChatType;
use Bitrix\Socialnetwork\Collab\Integration\IM\Messenger;
use Bitrix\Socialnetwork\Integration\Im\ChatFactory;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

class UpdateChatHandler extends AbstractHandler
{
	public function execute(AbstractConverterCommand $command): Result
	{
		$result = new Result();
		$group = GroupRegistry::getInstance()->get($command->getGroup()->getId());

		$type = $group->getType();
		$chatId = $group->getChatId();

		if (!$type instanceof Type)
		{
			return $result->addError(new Error('Incorrect group type'));
		}

		if ($chatId <= 0)
		{
			return $result->addError(new Error('Group has no chat'));
		}

		$fields = $this->prepareChatFields($group);
		$updateService = Messenger::getUpdateService($group->getChatId(), $fields);

		if (!$updateService)
		{
			return $result->addError(new Error('Chat update service not found'));
		}

		$chatUpdateResult = $updateService->updateChat();

		if (!$chatUpdateResult->isSuccess())
		{
			return $result->addErrors($chatUpdateResult->getErrors());
		}

		return $result;
	}

	private function prepareChatFields(Workgroup $group): array
	{
		$type = $group->getType();
		$fields = ChatFactory::getUniqueFieldsByType($type);
		$fields['TITLE'] = ChatFactory::getChatTitle($group->getName(), $type);
		$fields['TYPE'] = ChatType::getChatTypeByGroupType($type);

		return $fields;
	}
}
