<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Anchor;

use Bitrix\Im\Model\AnchorTable;
use Bitrix\Im\V2\Common\Collection\GroupActionTrait;
use Bitrix\Im\V2\Collection;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\ORM\Query\Query;

/**
 * @method null|array getIdList();
 * @method null|array getChatIdList();
 * @method null|array getMessageIdList();
 * @method null|array getUserIdList();
 * @method null|array getFromUserIdList();
 * @method null|array getTypeList();
 * @method null|array getSubTypeList();
 *
 * @method self setParentChatId(int $parentChatId);
 * @method self setParentMessageId(int $parentMessageId);
 */
final class AnchorCollection extends Collection implements RestConvertible
{
	use GroupActionTrait;

	public static function getCollectionElementClass(): string
	{
		return AnchorItem::class;
	}

	public static function find(array $filter, array $order = ['MESSAGE_ID' => 'desc'], ?int $limit = null, ?Context $context = null): self
	{
		$query = AnchorTable::query()
			->setSelect(['ID', 'CHAT_ID', 'MESSAGE_ID', 'USER_ID', 'FROM_USER_ID', 'TYPE', 'SUB_TYPE'])
			->setOrder($order)
		;

		if (isset($limit))
		{
			$query->setLimit($limit);
		}

		self::processFilters($query, $filter);


		return new self($query->fetchAll());
	}

	public static function getRestEntityName(): string
	{
		return 'anchors';
	}

	public function toRestFormat(array $option = []): ?array
	{
		$rest = [];

		foreach ($this as $anchorItem)
		{
			/** @var AnchorItem $anchorItem */
			$rest[] = $anchorItem->toRestFormat($option);
		}

		return $rest;
	}

	public function fillParents(array $map): void
	{
		foreach ($this as $anchorItem)
		{
			/** @var AnchorItem $anchorItem */
			$parent = $map[$anchorItem->getChatId()] ?? null;
			if (empty($parent))
			{
				continue;
			}

			$parentChatId = (int)($parent['PARENT_CHAT_ID'] ?? 0);
			$parentMessageId = (int)($parent['PARENT_MESSAGE_ID'] ?? 0);

			$anchorItem->setParentChatId($parentChatId);
			$anchorItem->setParentMessageId($parentMessageId);
		}
	}

	public function getCommonMessageId(): ?int
	{
		$messageIds = $this->getMessageIdList();
		if (empty($messageIds))
		{
			return null;
		}

		$messageIds = array_unique($messageIds);
		if (count($messageIds) === 1)
		{
			return array_shift($messageIds);
		}

		return null;
	}

	private static function processFilters(Query $query, array $filter): void
	{
		if (isset($filter['USER_ID']))
		{
			if (is_array($filter['USER_ID']) && !empty($filter['USER_ID']))
			{
				$query->whereIn('USER_ID', $filter['USER_ID']);
			}
			else
			{
				$query->where('USER_ID', $filter['USER_ID']);
			}
		}

		if (isset($filter['FROM_USER_ID']))
		{
			$query->where('FROM_USER_ID', $filter['FROM_USER_ID']);
		}

		if (isset($filter['CHAT_ID']))
		{
			$query->where('CHAT_ID', $filter['CHAT_ID']);
		}

		if (isset($filter['MESSAGE_ID']))
		{
			if (is_array($filter['MESSAGE_ID']) && !empty($filter['MESSAGE_ID']))
			{
				$query->whereIn('MESSAGE_ID', $filter['MESSAGE_ID']);
			}
			else
			{
				$query->where('MESSAGE_ID', $filter['MESSAGE_ID']);
			}
		}

		if (isset($filter['TYPE']))
		{
			$query->where('TYPE', $filter['TYPE']);
		}
	}
}