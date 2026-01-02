<?php

namespace Bitrix\Im\V2\Integration\Socialnetwork\Collab;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Pull\Event;
use Bitrix\Socialnetwork\Collab\CollabFeature;
use Bitrix\Socialnetwork\Collab\Control\CollabService;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Control\Option\OptionFactory;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Collab\Requirement;
use Bitrix\Socialnetwork\Control\Decorator\AccessDecorator;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Provider\GroupProvider;

class Collab
{
	public static function isAvailable(): bool
	{
		return
			Loader::includeModule('socialnetwork')
			&& CollabFeature::isOn()
			&& CollabFeature::isFeatureEnabledInPortalSettings()
			&& Requirement::check()->isSuccess()
		;
	}

	public static function isCreationAvailable(): bool
	{
		$userId = User::getCurrent()->getId() ?? 0;

		return self::isAvailable() && Requirement::checkWithAccess($userId)->isSuccess();
	}

	public static function updateCollabOption(Chat\CollabChat $chat, string $name, string $value): Result
	{
		$result = new Result();

		if (!Loader::includeModule('socialnetwork'))
		{
			return $result;
		}

		$collabId = (int)$chat->getEntityId();
		$option = OptionFactory::createOption($name, $value);

		try
		{
			$command = (new CollabUpdateCommand())
				->setId($collabId)
				->setInitiatorId($chat->getContext()->getUserId())
				->addOption($option)
			;
		}
		catch (ArgumentException $e)
		{
			return $result->addError(Error::createFromThrowable($e));
		}

		$service = new CollabService();
		$updateResult = (new AccessDecorator($service))->update($command);

		if (!$updateResult->isSuccess())
		{
			$result->addErrors($updateResult->getErrors());
		}

		return $result;
	}

	public static function onEntityCountersUpdate(int $collabId, array $counters, string $entityType): void
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return;
		}

		$collab = CollabProvider::getInstance()->getCollab($collabId);
		if ($collab === null)
		{
			return;
		}

		$chatId = $collab->getChatId();
		$chat = Chat::getInstance($chatId);
		if (!$chat instanceof Chat\CollabChat)
		{
			return;
		}

		$dialogId = $chat->getDialogId();

		if (!Loader::includeModule('pull'))
		{
			return;
		}

		if (empty($counters))
		{
			return;
		}

		foreach ($counters as $userId => $counter)
		{
			Event::add($userId, [
				'module_id' => 'im',
				'command' => 'updateCollabEntityCounter',
				'params' => [
					'dialogId' => $dialogId,
					'chatId' => $chatId,
					'entity' => $entityType,
					'counter' => $counter,
				],
				'extra' => \Bitrix\Im\Common::getPullExtra(),
			]);
		}
	}

	public static function isCollab(int $collabId): bool
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return false;
		}
		$type = GroupProvider::getInstance()->getGroupType($collabId);

		return $type === Type::Collab;
	}
}
