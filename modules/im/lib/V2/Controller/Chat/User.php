<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Controller\BaseController;

class User extends BaseController
{
	/**
	 * @restMethod im.v2.Chat.User.list
	 */
	public function listAction(\Bitrix\Im\V2\Chat $chat, array $order = [], int $limit = self::DEFAULT_LIMIT): ?array
	{
		$relationOrder = $this->prepareRelationOrder($order);
		$limit = $this->getLimit($limit);
		$relationFilter = ['ACTIVE' => true];
		$relations = $chat->getRelations(['FILTER' => $relationFilter, 'ORDER' => $relationOrder, 'LIMIT' => $limit]);

		return $this->toRestFormat($relations->getUsers());
	}

	private function prepareRelationOrder(array $order): array
	{
		if (isset($order['id']))
		{
			return ['ID' => strtoupper($order['id'])];
		}
		if (isset($order['lastSendMessageId']))
		{
			return ['LAST_SEND_MESSAGE_ID' => strtoupper($order['lastSendMessageId'])];
		}
		if (isset($order['userId']))
		{
			return ['USER_ID' => strtoupper($order['userId'])];
		}

		return [];
	}
}