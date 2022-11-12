<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\Im\Chat;
use Bitrix\Im\Internals\ChatIndex;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\User;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\Content;
use Bitrix\Main\UserIndexTable;
use Bitrix\Main\UserTable;

class ChatUserProvider extends ChatProvider
{
	protected const MAX_CHATS_IN_SAMPLE = 20;

	public function isAvailable(): bool
	{
		if (!Loader::includeModule('intranet'))
		{
			return false;
		}

		return parent::isAvailable();
	}

	public static function getChats(array $options = []): array
	{
		$options['searchQuery'] = trim($options['searchQuery']);
		if (
			!isset($options['searchableChatTypes'])
			|| !is_array($options['searchableChatTypes'])
			|| mb_strlen($options['searchQuery']) < Filter\Helper::getMinTokenSize()
		)
		{
			return [];
		}

		$chatTypeList = [];
		foreach (static::getSearchableChatTypes() as $chatType)
		{
			if (static::shouldSearchChatType($chatType, $options))
			{
				$chatTypeList[] = $chatType;
			}
		}
		if (empty($chatTypeList))
		{
			return [];
		}
		
		$options['order'] ??= ['LAST_MESSAGE_ID' => 'DESC'];
		$chatIdList = static::getChatIdList($options['searchQuery'], $chatTypeList, $options['order']);
		if (empty($chatIdList))
		{
			return [];
		}

		$query = ChatTable::query();
		$query
			->addSelect('*')
			->addSelect('RELATION.USER_ID', 'RELATION_USER_ID')
			->addSelect('RELATION.NOTIFY_BLOCK', 'RELATION_NOTIFY_BLOCK')
			->addSelect('RELATION.COUNTER', 'RELATION_COUNTER')
			->addSelect('RELATION.START_COUNTER', 'RELATION_START_COUNTER')
			->addSelect('RELATION.LAST_ID', 'RELATION_LAST_ID')
			->addSelect('RELATION.STATUS', 'RELATION_STATUS')
			->addSelect('RELATION.UNREAD_ID', 'RELATION_UNREAD_ID')
			->addSelect('ALIAS.ALIAS', 'ALIAS_NAME')
		;
		$query->registerRuntimeField(
			'RELATION',
			new Reference(
				'RELATION',
				RelationTable::class,
				Join::on('this.ID', 'ref.CHAT_ID'),
				['join_type' => Join::TYPE_INNER]
			)
		);

		$query->where('RELATION.USER_ID', User::getInstance()->getId());
		$query->whereIn('ID', $chatIdList);
		$query->setOrder($options['order']);
		$query->setLimit(static::MAX_CHATS_IN_SAMPLE);

		return $query->fetchAll();
	}

	protected static function getChatIdList(string $searchQuery, array $chatTypeList, array $order): array
	{
		$query = ChatTable::query();
		$query->addSelect('ID');
		$query->registerRuntimeField(
			'RELATION',
			new Reference(
				'RELATION',
				RelationTable::class,
				Join::on('this.ID', 'ref.CHAT_ID'),
				['join_type' => Join::TYPE_INNER]
			)
		);
		$query->where('RELATION.USER_ID', User::getInstance()->getId());
		$query->whereIn('RELATION.CHAT_ID', static::getSubQuery($chatTypeList, $searchQuery));
		$query->setOrder($order);
		$query->setLimit(static::MAX_CHATS_IN_SAMPLE);

		$chatIdList = [];
		foreach ($query->exec() as $row)
		{
			$chatIdList[] = (int)$row['ID'];
		}

		return $chatIdList;
	}

	protected static function getSearchableChatTypes(): array
	{
		return [
			Chat::TYPE_GROUP,
			Chat::TYPE_OPEN,
		];
	}

	protected static function getEntityId(): string
	{
		return 'im-chat-user';
	}

	protected static function addFilterBySearchQuery(Filter\ConditionTree $filter, string $searchQuery): void
	{
		$searchText = ChatIndex::matchAgainstWildcard(Content::prepareStringToken($searchQuery) , '');

		if ($searchText === '')
		{
			$filter->whereLike('USER_INDEX.SEARCH_USER_CONTENT', $searchQuery . '%');

			return;
		}

		$filter->whereMatch('USER_INDEX.SEARCH_USER_CONTENT', $searchText);
	}

	private static function getSubQuery($chatTypeList, $searchQuery)
	{
		$subQuery = RelationTable::query();

		$subQuery->addSelect('CHAT_ID');

		$subQuery->registerRuntimeField(
			'USER',
			new Reference(
				'USER',
				UserTable::class,
				Join::on('this.USER_ID', 'ref.ID'),
				['join_type' => Join::TYPE_INNER]
			)
		);
		$subQuery->registerRuntimeField(
			'USER_INDEX',
			new Reference(
				'USER_INDEX',
				UserIndexTable::class,
				Join::on('this.USER_ID', 'ref.USER_ID'),
				['join_type' => Join::TYPE_INNER]
			)
		);

		$subQuery->whereIn('MESSAGE_TYPE', $chatTypeList);
		$subQuery->where('USER.IS_REAL_USER', 'Y');

		$matchFilter = Query::filter();
		static::addFilterBySearchQuery($matchFilter, $searchQuery);
		$subQuery->where($matchFilter);

		$subQuery->addGroup('CHAT_ID');

		return $subQuery;
	}

}