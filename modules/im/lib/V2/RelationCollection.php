<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\UserTable;

/**
 * @implements \IteratorAggregate<int,Relation>
 * @implements Registry<Relation>
 * @method Relation offsetGet($key)
 */
class RelationCollection extends Collection
{
	public const COMMON_FIELDS = ['ID', 'MESSAGE_TYPE', 'CHAT_ID', 'USER_ID', 'START_ID', 'LAST_FILE_ID', 'LAST_ID', 'UNREAD_ID', 'NOTIFY_BLOCK', 'MANAGER'];

	protected static array $startIdStaticCache = [];

	protected array $relationsByUserId = [];

	public static function getCollectionElementClass(): string
	{
		return Relation::class;
	}

	public static function find(
		array $filter,
		array $order = [],
		?int $limit = null,
		?Context $context = null,
		array $select = self::COMMON_FIELDS
	): self
	{
		$query = RelationTable::query()->setSelect($select);

		if (isset($limit))
		{
			$query->setLimit($limit);
		}

		static::processFilters($query, $filter, $order);

		return new static($query->fetchCollection());
	}

	public static function getStartId(int $userId, int $chatId): int
	{
		if (isset(self::$startIdStaticCache[$chatId][$userId]))
		{
			return self::$startIdStaticCache[$chatId][$userId];
		}

		$relation = static::find(['CHAT_ID' => $chatId, 'USER_ID' => $userId], [], 1)->getByUserId($userId, $chatId);

		if ($relation === null)
		{
			return 0;
		}

		return $relation->getStartId() ?? 0;
	}

	public function getByUserId(int $userId, int $chatId): ?Relation
	{
		return $this->relationsByUserId[$chatId][$userId] ?? null;
	}

	public function hasUser(int $userId, int $chatId): bool
	{
		return isset($this->relationsByUserId[$chatId][$userId]);
	}

	public function getUserIds(): array
	{
		$userIds = [];
		foreach ($this as $relation)
		{
			$userIds[$relation->getUserId()] = $relation->getUserId();
		}

		return $userIds;
	}


	public function getUsers(): UserCollection
	{
		return new UserCollection($this->getUserIds());
	}

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		$orderField = null;
		$relationOrder = [];

		if (isset($filter['CHAT_ID']))
		{
			$query->where('CHAT_ID', (int)$filter['CHAT_ID']);
		}

		if (isset($filter['MANAGER']))
		{
			$query->where('MANAGER', (string)$filter['MANAGER']);
		}

		if (isset($filter['USER_ID']))
		{
			if (is_array($filter['USER_ID']) && !empty($filter['USER_ID']))
			{
				$query->whereIn('USER_ID', $filter['USER_ID']);
			}
			else
			{
				$query->where('USER_ID', (int)$filter['USER_ID']);
			}
		}

		if (isset($filter['!USER_ID']))
		{
			if (is_array($filter['!USER_ID']) && !empty($filter['!USER_ID']))
			{
				$query->whereNotIn('USER_ID', $filter['!USER_ID']);
			}
			else
			{
				$query->whereNot('USER_ID', (int)$filter['!USER_ID']);
			}
		}

		if (isset($filter['MESSAGE_TYPE']))
		{
			$query->where('MESSAGE_TYPE', (string)$filter['MESSAGE_TYPE']);
		}

		foreach (['ID', 'USER_ID', 'LAST_SEND_MESSAGE_ID'] as $allowedFieldToOrder)
		{
			if (isset($order[$allowedFieldToOrder]))
			{
				$orderField = $allowedFieldToOrder;
				$relationOrder[$allowedFieldToOrder] = $order[$allowedFieldToOrder];
				break;
			}
		}

		if (isset($orderField))
		{
			$query->setOrder($relationOrder);
		}

		if (isset($filter['LAST_ID']))
		{
			$operator = '<';
			if (isset($orderField) && $relationOrder[$orderField] === 'ASC')
			{
				$operator = '>';
			}
			$query->where($orderField, $operator, (int)$filter['LAST_ID']);
		}

		if (isset($filter['ACTIVE']))
		{
			$query->where('USER.ACTIVE', $filter['ACTIVE']);
		}

		if (isset($filter['ONLY_INTERNAL_TYPE']) && $filter['ONLY_INTERNAL_TYPE'])
		{
			$query->where(
				Query::filter()
					->logic('or')
					->whereNotIn('USER.EXTERNAL_AUTH_ID', UserTable::getExternalUserTypes())
					->whereNull('USER.EXTERNAL_AUTH_ID')
			);
		}
	}

	public function offsetSet($key, $value): void
	{
		/** @var Relation $value */
		parent::offsetSet($key, $value);

		if ($value->getUserId() !== null && $value->getChatId() !== null)
		{
			$this->relationsByUserId[$value->getChatId()][$value->getUserId()] = $value;
			if ($value->getStartId() !== null)
			{
				static::$startIdStaticCache[$value->getChatId()][$value->getUserId()] = $value->getStartId();
			}
		}
	}
}