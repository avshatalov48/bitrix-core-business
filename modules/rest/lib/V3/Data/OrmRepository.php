<?php

namespace Bitrix\Rest\V3\Data;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Order;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\V3\Attributes\JsonArray;
use Bitrix\Rest\V3\Attributes\OrmEntity;
use Bitrix\Rest\V3\Dto\Dto;
use Bitrix\Rest\V3\Dto\DtoCollection;
use Bitrix\Rest\V3\Dto\PropertyHelper;
use Bitrix\Rest\V3\Exceptions\ClassRequireAttributeException;
use Bitrix\Rest\V3\Exceptions\Internal\OrmSaveException;
use Bitrix\Rest\V3\Exceptions\InvalidPaginationException;
use Bitrix\Rest\V3\Exceptions\InvalidSelectException;
use Bitrix\Rest\V3\Exceptions\TooManyAttributesException;
use Bitrix\Rest\V3\Exceptions\UnknownDtoPropertyException;
use Bitrix\Rest\V3\Interaction\Request\ListRequest;
use Bitrix\Rest\V3\Structures\Aggregation\AggregationResultStructure;
use Bitrix\Rest\V3\Structures\Aggregation\AggregationSelectStructure;
use Bitrix\Rest\V3\Structures\Aggregation\AggregationType;
use Bitrix\Rest\V3\Structures\Aggregation\ResultItem;
use Bitrix\Rest\V3\Structures\Filtering\Condition;
use Bitrix\Rest\V3\Structures\Filtering\Expressions\ColumnExpression;
use Bitrix\Rest\V3\Structures\Filtering\Expressions\Expression;
use Bitrix\Rest\V3\Structures\Filtering\Expressions\LengthExpression;
use Bitrix\Rest\V3\Structures\Filtering\FilterStructure;
use Bitrix\Rest\V3\Structures\Ordering\OrderItem;
use Bitrix\Rest\V3\Structures\Ordering\OrderStructure;
use Bitrix\Rest\V3\Structures\PaginationStructure;
use Bitrix\Rest\V3\Structures\SelectStructure;
use Exception;
use ReflectionClass;
use ReflectionException;

class OrmRepository extends Repository
{
	protected string $dataClass;

	/**
	 * @param string $dtoClass
	 * @throws ReflectionException
	 */
	public function __construct(protected string $dtoClass)
	{
		$attributes = (new ReflectionClass($this->dtoClass))
			->getAttributes(OrmEntity::class);

		$attributesCount = count($attributes);

		if ($attributesCount > 1)
		{
			throw new TooManyAttributesException($this->dtoClass, OrmEntity::class, 1);
		}
		else if ($attributesCount < 1)
		{
			throw new ClassRequireAttributeException($this->dtoClass, OrmEntity::class);
		}

		$this->dataClass = $attributes[0]->newInstance()->entity;
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function getAllWithAggregate(AggregationSelectStructure $select, ?FilterStructure $filter = null): AggregationResultStructure
	{
		$queryMap = [];
		/** @var DataManager $dataClass */
		$dataClass = $this->dataClass;
		$query = $dataClass::query();
		foreach ($select as $function)
		{
			$queryMap[$function->aggregation->value][$function->field] = $function->alias;
			$aggregateFunction = self::mapAggregateFunction($function->aggregation->value);
			$aggregateParam = self::mapDtoPropertyToOrmField($function->field);
			$query->addSelect(Query::expr()->{$aggregateFunction}($aggregateParam), $function->alias);
		}

		if ($filter !== null)
		{
			$ormFilter = $this->prepareFilter($filter);
			if ($ormFilter !== null)
			{
				$query->where($ormFilter);
			}
		}

		$queryResult = $query->fetch();

		$aggregationResult = new AggregationResultStructure();
		foreach ($queryMap as $aggregation => $fields)
		{
			foreach ($fields as $field => $alias)
			{
				$aggregationType = AggregationType::from($aggregation);
				$aggregateItem = new ResultItem($aggregationType, $field, $queryResult[$alias]);
				$aggregationResult->add($aggregateItem);
			}
		}

		return $aggregationResult;
	}

	/**
	 * @throws ArgumentException
	 * @throws SystemException
	 * @throws ObjectPropertyException
	 */
	public function getAll(
		?SelectStructure $select = null,
		?FilterStructure $filter = null,
		?OrderStructure $order = null,
		?PaginationStructure $page = null,
	): DtoCollection {
		$query = $this->getQuery($select, $filter, $order, $page);

		return $this->mapCollectionToDto($query->fetchCollection());
	}

	/**
	 * @param SelectStructure|null $select
	 * @param FilterStructure|null $filter
	 * @param OrderStructure|null $order
	 * @param PaginationStructure|null $page
	 * @return Query
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getQuery(?SelectStructure $select, ?FilterStructure $filter = null, ?OrderStructure $order = null, ?PaginationStructure $page = null): Query
	{
		/** @var DataManager $dataClass */
		$dataClass = $this->dataClass;

		/** @var Collection $collection */
		$query = $dataClass::query();

		$query->setSelect($this->prepareSelect($select));

		if ($filter !== null)
		{
			$ormFilter = $this->prepareFilter($filter);
			if ($ormFilter !== null)
			{
				$query->where($ormFilter);
			}
		}

		$query->setOrder($this->prepareOrder($order));

		if ($page !== null)
		{
			$query
				->setLimit($page->getLimit())
				->setOffset($page->getOffset())
			;
		}
		else
		{
			// hard limit
			$query->setLimit(PaginationStructure::DEFAULT_LIMIT);
		}

		return $query;
	}

	/**
	 * @throws ArgumentException
	 * @throws Exception
	 */
	final protected function mapCollectionToDto(Collection $collection): DtoCollection
	{
		$dtoCollection = new DtoCollection($this->dtoClass);

		foreach ($collection as $object)
		{
			$dtoCollection->add($this->mapObjectToDto($object, $this->dtoClass));
		}

		return $dtoCollection;
	}

	/**
	 * @throws ArgumentException
	 */
	protected function mapObjectToDto(EntityObject $object, string $dtoClass): Dto
	{
		$dto = new $dtoClass();

		foreach ($object->collectValues() as $key => $value)
		{
			if (str_starts_with($key, 'UF_'))
			{
				$dto->{$key} = $value;

				continue;
			}
			if (str_starts_with($key, 'UTS_'))
			{
				continue;
			}
			$dtoProperty = self::mapOrmFieldToDtoProperty($key);
			if (property_exists($dto, $dtoProperty))
			{
				$dto->{$dtoProperty} = $value;
			}
		}

		return $dto;
	}

	protected function prepareSelect(?SelectStructure $select): array
	{
		if ($select === null)
		{
			return ['*'];
		}

		$ormFields = [];

		$dtoFields = $select->getList();

		foreach ($dtoFields as $field)
		{
			$ormFields[] = self::mapDtoPropertyToOrmField($field);
		}

		foreach ($select->getUserFields() as $field)
		{
			$ormFields[] = $field;
		}

		if (!empty($select->getRelationFields()))
		{
			$ormEntityRelationFields = [];

			foreach ($select->getRelationFields() as $field)
			{
				$ormEntityRelationFields[$field] = self::mapDtoPropertyToOrmField($field);
			}

			$ormFields = array_unique(array_merge($ormFields, $ormEntityRelationFields));
		}

		return $ormFields;
	}

	/**
	 * @param FilterStructure|null $filter
	 * @return ConditionTree|null
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	protected function prepareFilter(?FilterStructure $filter = null): ?ConditionTree
	{
		if ($filter !== null && $filter->getConditions())
		{
			$query = new ConditionTree();
			$query->logic($filter->logic()->value);
			$query->negative($filter->isNegative());

			foreach ($filter->getConditions() as $condition)
			{
				if ($condition instanceof Condition)
				{
					$query->where($this->convertFilterCondition($condition));
				}
				elseif ($condition instanceof FilterStructure)
				{
					$ormFilter = $this->prepareFilter($condition);
					if ($ormFilter !== null)
					{
						$query->where($ormFilter);
					}
				}
			}

			return $query;
		}

		return null;
	}

	protected function prepareOrder(?OrderStructure $order = null): array
	{
		$orderItems = $order !== null ? $order->getItems() : [new OrderItem('id', Order::Asc)];

		$ormOrder = [];

		foreach ($orderItems as $item)
		{
			$ormField = self::mapDtoPropertyToOrmField($item->getProperty());
			$ormOrder[$ormField] = $item->getOrder()->value;
		}

		return $ormOrder;
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	protected function convertFilterCondition(Condition $condition): \Bitrix\Main\ORM\Query\Filter\Condition
	{
		/** @var DataManager $dataClass */
		$dataClass = $this->dataClass;
		$leftOperand = $condition->getLeftOperand();
		$rightOperand = $condition->getRightOperand();

		$leftOperand = $leftOperand instanceof Expression ? $leftOperand : self::mapDtoPropertyToOrmField($leftOperand);

		$operands = [&$leftOperand, &$rightOperand];

		foreach ($operands as &$operand)
		{
			// columns
			if ($operand instanceof ColumnExpression)
			{
				$operand = new \Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression(
					self::mapDtoPropertyToOrmField($operand->getProperty()),
				);
			}

			// length expression
			if ($operand instanceof LengthExpression)
			{
				$ormFieldName = self::mapDtoPropertyToOrmField($operand->getProperty());
				$sqlHelper = $dataClass::getEntity()->getConnection()->getSqlHelper();

				$operand = new ExpressionField(
					\Bitrix\Main\ORM\Query\Expression::getTmpName('RST'),
					$sqlHelper->getLengthFunction('%s'),
					$ormFieldName,
				);
			}
		}

		return new \Bitrix\Main\ORM\Query\Filter\Condition(
			$leftOperand,
			$condition->getOperator()->value,
			$rightOperand,
		);
	}

	/**
	 * @throws OrmSaveException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function add(Dto $dto): int
	{
		/** @var DataManager $dataClass */
		$dataClass = $this->dataClass;

		/** @var EntityObject $ormObject */
		$ormObject = $dataClass::createObject();

		foreach ($dto->toArray(rawData: true) as $propertyName => $value)
		{
			if (str_starts_with($propertyName, 'UF_'))
			{
				$ormObject->set($propertyName, $value);
			}
			else
			{
				$ormFieldName = self::mapDtoPropertyToOrmField($propertyName);
				$ormObject->set($ormFieldName, $value);
			}
		}

		$result = $ormObject->save();

		if ($result->isSuccess())
		{
			return $ormObject->getId();
		}
		else
		{
			$messages = implode(',', $result->getErrorMessages());
			$internal = new Exception($messages);

			throw new OrmSaveException($internal);
		}
	}

	public function update(int $id, Dto $dto): bool
	{
		/** @var DataManager $dataClass */
		$dataClass = $this->dataClass;

		$ormFields = $this->getOrmFieldsByDto($dto);

		return $dataClass::update($id, $ormFields)->getError() === null;
	}

	public function updateMulti(FilterStructure $filter, Dto $dto): bool
	{
		/** @var DataManager $dataClass */
		$dataClass = $this->dataClass;

		$ids = $this->getIdsByFilter($filter);
		$ormFields = $this->getOrmFieldsByDto($dto);

		return $dataClass::updateMulti($ids, $ormFields)->getError() === null;
	}

	private function getOrmFieldsByDto(Dto $dto): array
	{
		$ormFields = [];

		foreach ($dto->toArray(rawData: true) as $propertyName => $value)
		{
			if (str_starts_with($propertyName, 'UF_'))
			{
				$ormFields[$propertyName] = $value;
			}
			else
			{
				$ormFields[self::mapDtoPropertyToOrmField($propertyName)] = $value;
			}
		}

		return $ormFields;
	}

	public function delete(int $id): bool
	{
		/** @var DataManager $dataClass */
		$dataClass = $this->dataClass;
		$dataClass::delete($id);

		return true;
	}

	public function deleteMulti(FilterStructure $filter): bool
	{
		$ids = $this->getIdsByFilter($filter);
		foreach ($ids as $id)
		{
			$this->delete($id);
		}

		return true;
	}

	protected static function mapOrmFieldToDtoProperty(string $field): string
	{
		return StringHelper::snake2camel($field, true);
	}

	public static function mapDtoPropertyToOrmField(string $property): string
	{
		return strtoupper(StringHelper::camel2snake($property));
	}

	protected static function mapAggregateFunction(string $value): string
	{
		$availableMethods = ['sum', 'avg', 'max', 'min', 'count', 'countDistinct'];

		if (in_array($value, $availableMethods, true))
		{
			return $value !== 'countDistinct' ? $value : 'CountDistinct';
		}
		throw new SystemException('Unsupported aggregation method: ' . $value . '. Use one of ' . join(', ', $availableMethods));
	}

	/**
	 * @throws UnknownDtoPropertyException
	 * @throws InvalidPaginationException
	 * @throws ArgumentException
	 * @throws InvalidSelectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getIdsByFilter(FilterStructure $filter): array
	{
		$query = $this->getQuery(
			select: SelectStructure::create(['id'], $this->dtoClass, new ListRequest($this->dtoClass)),
			filter: $filter,
			page: PaginationStructure::create(['limit' => PaginationStructure::MAX_LIMIT]),
		);

		$rowsCursor = $query->exec();

		$ids = [];

		foreach ($rowsCursor as $row)
		{
			if (isset($row['ID']))
			{
				$ids[] = (int) $row['ID'];
			}
		}

		return $ids;
	}
}
