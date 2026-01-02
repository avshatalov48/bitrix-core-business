<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Main\DB\Order;
use Bitrix\Rest\V3\Exceptions\InvalidFilterException;
use Bitrix\Rest\V3\Interaction\Request\TailRequest;
use Bitrix\Rest\V3\Interaction\Response\ListResponse;
use Bitrix\Rest\V3\Structures\Filtering\Condition;
use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;
use Bitrix\Rest\V3\Structures\Filtering\Operator;
use Bitrix\Rest\V3\Structures\Ordering\OrderStructure;
use Bitrix\Rest\V3\Structures\PaginationStructure;

trait TailOrmActionTrait
{
	use OrmActionTrait;

	final public function tailAction(TailRequest $request): ListResponse
	{
		if ($request->filter && in_array($request->cursor->getField(), $request->filter->getFields(), true))
		{
			throw new InvalidFilterException('Cursor field ' . $request->cursor->getField() . ' cannot be used at filter.');
		}

		if (!$request->filter)
		{
			$request->filter = new FilterStructure();
		}

		$field = 'id';
		$order = Order::Asc->value;
		$value = 0;
		$limit = PaginationStructure::DEFAULT_LIMIT;
		if ($request->cursor)
		{
			$field = $request->cursor->getField();
			$order = $request->cursor->getOrder()->value;
			$value = $request->cursor->getValue();
			$limit = $request->cursor->getLimit();
		}
		$operator = $order === Order::Asc->value ? Operator::Greater : Operator::Less;
		if ($request->cursor)
		{
			$condition = new Condition($field, $operator, $value);
			$request->filter->addCondition($condition);
		}

		$orderStructure = OrderStructure::create(
			[$field => $order],
			$request->getDtoClass(),
			$request
		);
		$paginationStructure = PaginationStructure::create(['limit' => $limit]);

		$collection = $this
			->getOrmRepositoryByRequest($request)
			->getAll(
				$request->select,
				$request->filter,
				$orderStructure,
				$paginationStructure
			);

		return new ListResponse($collection);
	}
}
