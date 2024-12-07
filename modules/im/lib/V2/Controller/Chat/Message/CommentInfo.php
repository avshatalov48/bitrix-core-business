<?php

namespace Bitrix\Im\V2\Controller\Chat\Message;

use Bitrix\Im\V2\Chat\Comment\CommentPopupItem;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\MessageCollection;

class CommentInfo extends BaseController
{
	/**
	 * @restMethod im.v2.Chat.Message.CommentInfo.list
	 */
	public function listAction(MessageCollection $messages): ?array
	{
		$commentInfo = new CommentPopupItem($messages->getCommonChatId(), $messages->getIds());

		return $this->toRestFormat($commentInfo);
	}
}