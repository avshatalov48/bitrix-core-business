<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Anchor\Push;

use Bitrix\Im\Common;
use Bitrix\Im\V2\Anchor\AnchorCollection;
use Bitrix\Im\V2\Anchor\AnchorItem;
use Bitrix\Im\V2\Chat;
use Bitrix\Main\Loader;
use Bitrix\Pull\Event;

class PushService
{
	private const ADD_ANCHOR_EVENT = 'addAnchor';
	private const DELETE_ANCHOR_EVENT = 'deleteAnchor';
	private const DELETE_CHAT_ANCHORS_EVENT = 'deleteChatAnchors';
	private const DELETE_ALL_ANCHORS_EVENT = 'deleteAllAnchors';

	public function addMulti(AnchorCollection $anchorCollection): void
	{
		foreach ($anchorCollection as $anchorItem)
		{
			$this->send(self::ADD_ANCHOR_EVENT, $anchorItem);
		}
	}

	public function add(AnchorItem $anchorItem): void
	{
		$this->send(self::ADD_ANCHOR_EVENT, $anchorItem);
	}

	public function deleteMulti(AnchorCollection $anchorCollection): void
	{
		foreach ($anchorCollection as $anchorItem)
		{
			$this->delete($anchorItem);
		}
	}

	public function delete(AnchorItem $anchorItem): void
	{
		$this->send(self::DELETE_ANCHOR_EVENT, $anchorItem);
	}

	public function deleteByChat(int $chatId, int $userId): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		$chat = Chat::getInstance($chatId);

		if ($chat instanceof Chat\PrivateChat)
		{
			$this->deleteByPrivateChat($chatId, $userId);

			return;
		}

		$parameters = [
			'dialogId' => $chat->getDialogId(),
			'chatId' => $chatId,
			'userId' => $userId,
		];

		$pull = [
			'module_id' => 'im',
			'command' => static::DELETE_CHAT_ANCHORS_EVENT,
			'params' => $parameters,
			'extra' => Common::getPullExtra(),
		];

		Event::add([$userId], $pull);
	}

	public function deleteAll(int $userId): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		$pull = [
			'module_id' => 'im',
			'command' => static::DELETE_ALL_ANCHORS_EVENT,
			'params' => ['userId' => $userId],
			'extra' => Common::getPullExtra(),
		];

		Event::add($userId, $pull);
	}

	private function send(string $eventName, AnchorItem $anchorItem): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		$chat = Chat::getInstance($anchorItem->getChatId());

		$parameters = [...$anchorItem->toRestFormat()];

		if ($chat instanceof Chat\PrivateChat)
		{
			$this->sendToPrivateChat($parameters, $eventName, $anchorItem);

			return;
		}

		$parameters['dialogId'] = $chat->getDialogId();
		$pull = [
			'module_id' => 'im',
			'command' => $eventName,
			'params' => $parameters,
			'extra' => Common::getPullExtra(),
		];

		Event::add([$anchorItem->getUserId()], $pull);
	}

	private function sendToPrivateChat(array $parameters, string $eventName, AnchorItem $anchorItem): void
	{
		/** @var Chat\PrivateChat $chat */
		$chat = Chat::getInstance($anchorItem->getChatId());

		$recipientId = $anchorItem->getUserId();

		$parameters['dialogId'] = $chat->getCompanion($recipientId)->getId();
		Event::add($recipientId, [
			'module_id' => 'im',
			'command' => $eventName,
			'params' => $parameters,
			'extra' => Common::getPullExtra(),
		]);
	}

	private function deleteByPrivateChat(int $chatId, int $userId): void
	{
		/** @var Chat\PrivateChat $chat */
		$chat = Chat::getInstance($chatId);

		$parameters = [
			'dialogId' => $chat->getCompanion($userId)->getId(),
			'chatId' => $chatId,
			'userId' => $userId,
		];

		Event::add($userId, [
			'module_id' => 'im',
			'command' => static::DELETE_CHAT_ANCHORS_EVENT,
			'params' => $parameters,
			'extra' => Common::getPullExtra(),
		]);
	}
}