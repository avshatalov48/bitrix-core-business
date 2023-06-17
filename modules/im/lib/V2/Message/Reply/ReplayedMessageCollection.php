<?php

namespace Bitrix\Im\V2\Message\Reply;

use Bitrix\Im\V2\Entity\File\FilePopupItem;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Link\Reminder\ReminderPopupItem;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Rest\PopupData;

class ReplayedMessageCollection extends MessageCollection
{
	public static function createByMessageCollection(MessageCollection $collection): self
	{
		$instance = new self();

		$replyIdList = $instance->getReplyIdList($collection);
		if (!empty($replyIdList))
		{
			$instance->load($replyIdList);
		}

		return $instance;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return new PopupData([
			new UserPopupItem($this->getUserIds()),
			new FilePopupItem($this->getFiles()),
			new ReminderPopupItem($this->getReminders())
		], $excludedList);
	}

	private function getReplyIdList(MessageCollection $messageCollection): array
	{
		$messageCollection->fillParams();
		$result = [];
		foreach ($messageCollection as $message)
		{
			if ($message->getParams()->isSet(Params::REPLY_ID))
			{
				$result[] = $message->getParams()->get(Params::REPLY_ID)->getValue();
			}
		}

		return $result;
	}

}