<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\Model\ReactionTable;
use Bitrix\Im\V2\Collection;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\ORM\Query\Query;

/**
 * @implements \IteratorAggregate<int,ReactionItem>
 * @method ReactionItem offsetGet($key)
 */
class ReactionCollection extends Collection implements RestConvertible, PopupDataAggregatable
{
	use ContextCustomer;

	public static function getCollectionElementClass(): string
	{
		return ReactionItem::class;
	}

	public static function find(array $filter, array $order, ?int $limit = null, ?Context $context = null): self
	{
		$reactionOrder = ['ID' => 'DESC'];

		if (isset($order['ID']))
		{
			$reactionOrder['ID'] = $order['ID'];
		}

		$query = ReactionTable::query()
			->setSelect(['ID', 'CHAT_ID', 'MESSAGE_ID', 'USER_ID', 'DATE_CREATE', 'REACTION'])
		;

		if ($reactionOrder['ID'] !== 'DESC')
		{
			$query->setOrder($reactionOrder);
		}

		if (isset($limit))
		{
			$query->setLimit($limit);
		}

		static::processFilters($query, $filter, $reactionOrder);

		return new static($query->fetchCollection());
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return new PopupData([new UserPopupItem($this->getUserIds())], $excludedList);
	}

	public static function getRestEntityName(): string
	{
		return 'reactions';
	}

	public function toRestFormat(array $option = []): array
	{
		$rest = [];

		foreach ($this as $reaction)
		{
			$rest[] = $reaction->toRestFormat($option);
		}

		return $rest;
	}

	protected static function processFilters(Query $query, array $filter, array $order): void
	{
		if (isset($filter['LAST_ID']))
		{
			$operator = $order['ID'] === 'DESC' ? '<' : '>';
			$query->where('ID', $operator, $filter['LAST_ID']);
		}

		if (isset($filter['REACTION']))
		{
			$query->where('REACTION', $filter['REACTION']);
		}

		if (isset($filter['MESSAGE_ID']))
		{
			$query->where('MESSAGE_ID', $filter['MESSAGE_ID']);
		}
	}

	/**
	 * @return array<int,int>
	 */
	private function getUserIds(): array
	{
		$userIds = [];

		foreach ($this as $reaction)
		{
			$userIds[$reaction->getUserId()] = $reaction->getUserId();
		}

		return $userIds;
	}
}