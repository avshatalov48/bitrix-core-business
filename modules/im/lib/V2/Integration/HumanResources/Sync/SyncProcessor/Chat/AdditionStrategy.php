<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\SyncProcessor\Chat;

use Bitrix\HumanResources\Item\Node;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChannelChat;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\Reason;

class AdditionStrategy extends Strategy
{
	public function processUsers(Chat $chat, array $userIds): void
	{
		$this->addUsers($chat, $userIds);
	}

	protected function addUsers(Chat $chat, array $userIds): void
	{
		$chat->addUsers($userIds, new AddUsersConfig(hideHistory: false, withMessage: false, reason: Reason::STRUCTURE));
	}

	public function getFinishMessageCode(Chat $chat, ?Node $node): ?string
	{
		if ($chat instanceof ChannelChat)
		{
			return match ($node->isTeam())
			{
				true => 'IM_HR_INTEGRATION_CHANNEL_FINISH_ADD_TEAM',
				false => 'IM_HR_INTEGRATION_CHANNEL_FINISH_ADD',
			};
		}

		return match ($node->isTeam())
		{
			true => 'IM_HR_INTEGRATION_CHAT_FINISH_ADD_TEAM',
			false => 'IM_HR_INTEGRATION_CHAT_FINISH_ADD_MSGVER_2',
		};
	}
}
