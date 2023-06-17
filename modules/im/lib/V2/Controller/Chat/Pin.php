<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Link\Pin\PinCollection;
use Bitrix\Im\V2\Link\Pin\PinService;
use Bitrix\Im\V2\Rest\RestAdapter;

class Pin extends BaseController
{
	/**
	 * @restMethod im.v2.Chat.Pin.tail
	 */
	public function tailAction(Chat $chat, array $filter = [], array $order = [], int $limit = 50): ?array
	{
		$pinFilter = [
			'LAST_ID' => $filter['lastId'] ?? null,
			'CHAT_ID' => $chat->getChatId(),
			'START_ID' => $chat->getStartId() ?: null,
		];
		$pinOrder = [
			'ID' => $order['id'] ?? 'DESC'
		];
		$pinLimit = $this->getLimit($limit);

		$pins = PinCollection::find($pinFilter, $pinOrder, $pinLimit);

		return (new RestAdapter($pins))->toRestFormat();
	}

	public function countAction(Chat $chat): ?array
	{
		return [
			'counter' => (new PinService())->getCount($chat->getChatId(), $chat->getStartId())
		];
	}
}