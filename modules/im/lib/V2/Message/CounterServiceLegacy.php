<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Main\SystemException;

class CounterServiceLegacy extends CounterService
{
	protected const CACHE_PATH = '/bx/im/counter/';

	protected const DEFAULT_COUNTERS = [
		'TYPE' => [
			'ALL' => 0,
			'NOTIFY' => 0,
			'CHAT' => 0,
			'LINES' => 0,
			'DIALOG' => 0,
		],
		'CHAT' => [],
		'CHAT_MUTED' => [],
		'CHAT_UNREAD' => [],
		'LINES' => [],
		'DIALOG' => [],
		'DIALOG_UNREAD' => [],
	];

	protected function countUnreadMessages(?array $chatIds = null): void
	{
		$counters = $this->getCountersForEachChat($chatIds);

		$privateChatIds = [];
		foreach ($counters as $counter)
		{
			if ($counter['CHAT_TYPE'] === \IM_MESSAGE_PRIVATE)
			{
				$privateChatIds[] = $counter['CHAT_ID'];
			}
		}

		$chatIdToDialogId = $this->getMapChatToDialog($privateChatIds);

		foreach ($counters as $counter)
		{
			$chatId = (int)$counter['CHAT_ID'];
			$count = (int)$counter['COUNT'];
			if ($counter['IS_MUTED'] === 'Y')
			{
				$this->setFromMutedChat($chatId, $count);
			}
			else if ($counter['CHAT_TYPE'] === \IM_MESSAGE_SYSTEM)
			{
				$this->setFromNotify($count);
			}
			else if ($counter['CHAT_TYPE'] === \IM_MESSAGE_OPEN_LINE)
			{
				$this->setFromLine($chatId, $count);
			}
			else if ($counter['CHAT_TYPE'] === \IM_MESSAGE_PRIVATE && isset($chatIdToDialogId[$chatId]))
			{
				$this->setFromDialog($chatIdToDialogId[$chatId], $count);
			}
			else if ($counter['CHAT_TYPE'] === Chat::IM_TYPE_COPILOT)
			{
				// nothing
			}
			else
			{
				$this->setFromChat($chatId, $count);
			}
			$this->countersByChatIds[$chatId] = $count;
		}
	}
	protected function getUnreadChats(?bool $isMuted = null): array
	{
		$query = RecentTable::query()
			->setSelect(['CHAT_ID' => 'ITEM_CID', 'IS_MUTED' => 'RELATION.NOTIFY_BLOCK', 'DIALOG_ID' => 'ITEM_ID', 'ITEM_TYPE'])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('UNREAD', true)
		;
		if (isset($isMuted))
		{
			$query->where('IS_MUTED', $isMuted);
		}

		return $query->fetchAll();
	}

	protected function countUnreadChats(): void
	{
		$unreadChats = $this->getUnreadChats();

		foreach ($unreadChats as $unreadChat)
		{
			if ($unreadChat['ITEM_TYPE'] === \IM_MESSAGE_PRIVATE)
			{
				$this->setUnreadDialog((int)$unreadChat['DIALOG_ID']);
			}
			else
			{
				$this->setUnreadChat((int)$unreadChat['CHAT_ID'], $unreadChat['IS_MUTED'] === 'Y');
			}
		}
	}

	protected function setUnreadDialog(int $id): void
	{
		$this->counters['TYPE']['ALL']++;
		$this->counters['TYPE']['DIALOG']++;
		$this->counters['DIALOG_UNREAD'][] = $id;
	}

	protected function setFromDialog(int $id, int $count): void
	{
		$this->counters['TYPE']['ALL'] += $count;
		$this->counters['TYPE']['DIALOG'] += $count;
		$this->counters['DIALOG'][$id] = $count;
	}

	protected function getMapChatToDialog(array $privateChatIds)
	{
		if (empty($privateChatIds))
		{
			return [];
		}

		$result = RelationTable::query()
			->setSelect(['USER_ID', 'CHAT_ID'])
			->whereNot('USER_ID', $this->getContext()->getUserId())
			->whereIn('CHAT_ID', $privateChatIds)
			->fetchAll()
		;

		$map = [];

		foreach ($result as $row)
		{
			$map[$row['CHAT_ID']] = $row['USER_ID'];
		}

		return $map;
	}
}