<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

class ChannelChat extends GroupChat
{
	private const MESSAGE_COMPONENT_START_CHANNEL = 'ChannelCreationMessage';

	public function extendPullWatch(): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		\CPullWatch::Add($this->getContext()->getUserId(), "IM_PUBLIC_COMMENT_{$this->getId()}", true);
	}

	protected function updateStateAfterUsersAdd(array $usersToAdd): Chat
	{
		$result = parent::updateStateAfterUsersAdd($usersToAdd);
		Recent::raiseChat($this, $this->getRelationsByUserIds($usersToAdd), new DateTime());

		return $result;
	}

	protected function sendGreetingMessage(?int $authorId = null)
	{
		$messageText = Loc::getMessage('IM_CHANNEL_CREATE');

		\CIMMessage::Add([
			'MESSAGE_TYPE' => $this->getType(),
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => 0,
			'MESSAGE' => $messageText,
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'SKIP_COUNTER_INCREMENTS' => 'Y',
			'PARAMS' => [
				'NOTIFY' => 'N',
			],
		]);
	}

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_CHANNEL;
	}

	public function getDefaultManageMessages(): string
	{
		return self::MANAGE_RIGHTS_MANAGERS;
	}

	public function getDefaultManageUI(): string
	{
		return self::MANAGE_RIGHTS_MANAGERS;
	}

	public function realAllComments(): void
	{
		$readComments = (new ReadService())->readChildren($this);

		if (empty($readComments) || !Loader::includeModule('pull'))
		{
			return;
		}

		$pushMessage = [
			'module_id' => 'im',
			'command' => 'readAllChannelComments',
			'params' => [
				'chatId' => $this->getChatId(),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];

		\Bitrix\Pull\Event::add($this->getContext()->getUserId(), $pushMessage);
	}

	protected function sendBanner(?int $authorId = null): void
	{
		\CIMMessage::Add([
			'MESSAGE_TYPE' => $this->getType(),
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => 0,
			'MESSAGE' => Loc::getMessage('IM_CHAT_CHANNEL_CREATE_WELCOME'),
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'PARAMS' => [
				'COMPONENT_ID' => self::MESSAGE_COMPONENT_START_CHANNEL,
			],
			'SKIP_COUNTER_INCREMENTS' => 'Y',
		]);
	}
}