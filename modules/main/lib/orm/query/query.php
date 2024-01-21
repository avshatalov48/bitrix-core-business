<?php

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Filter\ConditionTree as Filter;
use Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\StringHelper;

/**
 * Query builder for Entities.
 *
 * Virtual WHERE methods (proxy to Filter):
 *
 * @method $this where(...$filter)
 * @see Filter::where()
 *
 * @method $this whereNot(...$filter)
 * @see Filter::whereNot()
 *
 * @method $this whereColumn(...$filter)
 * @see Filter::whereColumn()
 *
 * @method $this whereNull($column)
 * @see Filter::whereNull()
 *
 * @method $this whereNotNull($column)
 * @see Filter::whereNotNull()
 *
 * @method $this whereIn($column, $values)
 * @see Filter::whereIn()
 *
 * @method $this whereNotIn($column, $values)
 * @see Filter::whereNotIn()
 *
 * @method $this whereBetween($column, $valueMin, $valueMax)
 * @see Filter::whereBetween()
 *
 * @method $this whereNotBetween($column, $valueMin, $valueMax)
 * @see Filter::whereNotBetween()
 *
 * @method $this whereLike($column, $value)
 * @see Filter::whereLike()
 *
 * @method $this whereNotLike($column, $value)
 * @see Filter::whereNotLike()
 *
 * @method $this whereExists($query)
 * @see Filter::whereExists()
 *
 * @method $this whereNotExists($query)
 * @see Filter::whereNotExists()
 *
 * @method $this whereMatch($column, $value)
 * @see Filter::whereMatch()
 *
 * @method $this whereNotMatch($column, $value)
 * @see Filter::whereNotMatch()
 *
 * @method $this whereExpr($expr, $arguments)
 * @see Filter::whereExpr()
 *
 * Virtual HAVING methods (proxy to Filter):
 *
 * @method $this having(...$filter)
 * @see Filter::where()
 *
 * @method $this havingNot(...$filter)
 * @see Filter::whereNot()
 *
 * @method $this havingColumn(...$filter)
 * @see Filter::whereColumn()
 *
 * @method $this havingNull($column)
 * @see Filter::whereNull()
 *
 * @method $this havingNotNull($column)
 * @see Filter::whereNotNull()
 *
 * @method $this havingIn($column, $values)
 * @see Filter::whereIn()
 *
 * @method $this havingNotIn($column, $values)
 * @see Filter::whereNotIn()
 *
 * @method $this havingBetween($column, $valueMin, $valueMax)
 * @see Filter::whereBetween()
 *
 * @method $this havingNotBetween($column, $valueMin, $valueMax)
 * @see Filter::whereNotBetween()
 *
 * @method $this havingLike($column, $value)
 * @see Filter::whereLike()
 *
 * @method $this havingNotLike($column, $value)
 * @see Filter::whereNotLike()
 *
 * @method $this havingExists($query)
 * @see Filter::whereExists()
 *
 * @method $this havingNotExists($query)
 * @see Filter::whereNotExists()
 *
 * @package Bitrix\Main\ORM
 */
class Query
{
	/** @var Entity */
	protected $entity;

	protected
		$select = array(),
		$group = array(),
		$order = array(),
		$limit = null,
		$offset = null,
		$countTotal = null;

	// deprecated array filter format
	protected
		$filter = array(),
		$where = array(),
		$having = array();

	/** @var Filter */
	protected $filterHandler;

	/** @var Filter */
	protected $whereHandler;

	/** @var Filter */
	protected $havingHandler;

	/**
	 * @var Chain[]
	 */
	protected					  // all chain storages keying by alias
		$select_chains = array(),
		$group_chains = array(),
		$order_chains = array();

	/**
	 * @var Chain[]
	 */
	protected
		$filter_chains = array(),
		$where_chains = array(),
		$having_chains = array();

	/**
	 * @var Chain[]
	 */
	protected
		$select_expr_chains = array(), // from select expr "build_from"
		$having_expr_chains = array(), // from having expr "build_from"
		$hidden_chains = array(); // all expr "build_from" elements;

	/**
	 * Fields in result that are visible for fetchObject, but invisible for array
	 * @var string[]
	 */
	protected $forcedObjectPrimaryFields;

	/** @var Chain[] */
	protected $runtime_chains;

	/** @var Chain[] */
	protected $global_chains = array(); // keying by both def and alias

	/** @var string[] */
	protected $query_build_parts;

	/** @var Expression */
	protected static $expressionHelper;

	/**
	 * Enable or Disable data doubling for 1:N relations in query filter
	 * If disabled, 1:N entity fields in filter will be transformed to exists() subquery
	 * @var bool
	 */
	protected $data_doubling_off = false;

	/**
	 * Enable or disable handling private fields
	 * @see ScalarField::$is_private
	 * @var bool
	 */
	protected $private_fields_on = false;

	/** @var string */
	protected $table_alias_postfix = '';

	/** @var string Custom alias for the table of the init entity  */
	protected $custom_base_table_alias = null;

	/** @var array */
	protected $join_map = array();

	/** @var array list of used joins */
	protected $join_registry;

	/** @var Union */
	protected $unionHandler;

	/** @var bool */
	protected $is_distinct = false;

	/** @var bool */
	protected $is_executing = false;

	/** @var string Last executed SQL query */
	protected static $last_query;

	/** @var array Replaced field aliases */
	protected $replaced_aliases = [];

	/** @var array Replaced table aliases */
	protected $replaced_taliases = [];

	/** @var int */
	protected $uniqueAliasCounter = 0;

	/** @var callable[] */
	protected $selectFetchModifiers = array();

	protected
		$cacheTtl = 0,
		$cacheJoins = false;

	/**
	 * @param Entity|Query|string $source
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function __construct($source)
	{
		if ($source instanceof $this)
		{
			$this->entity = Entity::getInstanceByQuery($source);
		}
		elseif ($source instanceof Entity)
		{
			$this->entity = clone $source;
		}
		elseif (is_string($source))
		{
			$this->entity = clone Entity::getInstance($source);
		}
		else
		{
			throw new Main\ArgumentException(sprintf(
				'Unknown source type "%s" for new %s', gettype($source), __CLASS__
			));
		}

		$this->filterHandler = static::filter();
		$this->whereHandler = static::filter();
		$this->havingHandler = static::filter();
	}

	/**
	 * @param $method
	 * @param $arguments
	 *
	 * @return $this
	 * @throws Main\SystemException
	 */
	public function __call($method, $arguments)
	{
		// where and having proxies
		if (substr($method, 0, 6) === 'having')
		{
			$method = str_replace('having', 'where', $method);
		}

		if (substr($method, 0, 5) === 'where')
		{
			if (method_exists($this->filterHandler, $method))
			{
				call_user_func_array(
					[$this->filterHandler, $method],
					$arguments
				);

				return $this;
			}
		}

		if (substr($method, 0, 4) === 'with')
		{
			$dataClass = $this->entity->getDataClass();

			if (method_exists($dataClass, $method))
			{
				// set query as first element
				array_unshift($arguments, $this);

				call_user_func_array(
					[$dataClass, $method],
					$arguments
				);

				return $this;
			}
		}

		throw new Main\SystemException("Unknown method `{$method}`");
	}

	/**
	 * Returns an array of fields for SELECT clause
	 *
	 * @return array
	 */
	public function getSelect()
	{
		return $this->select;
	}

	/**
	 * Sets a list of fields for SELECT clause
	 *
	 * @param array $select
	 * @return Query
	 */
	public function setSelect(array $select)
	{
		$this->select = $select;
		return $this;
	}

	/**
	 * Adds a field for SELECT clause
	 *
	 * @param mixed $definition Field
	 * @param string $alias Field alias like SELECT field AS alias
	 * @return $this
	 */
	public function addSelect($definition, $alias = '')
	{
		if($alias <> '')
		{
			$this->select[$alias] = $definition;
		}
		else
		{
			$this->select[] = $definition;
		}

		return $this;
	}

	/**
	 * Returns an array of filters for WHERE clause
	 *
	 * @return array
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * Sets a list of filters for WHERE clause
	 *
	 * @param array $filter
	 * @return $this
	 */
	public function setFilter(array $filter)
	{
		$this->filter = $filter;
		return $this;
	}

	/**
	 * Adds a filter for WHERE clause
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return $this
	 */
	public function addFilter($key, $value)
	{
		if (is_null($key) && is_array($value))
		{
			$this->filter[] = $value;
		}
		else
		{
			$this->filter[$key] = $value;
		}

		return $this;
	}

	/**
	 * @return Filter
	 */
	public function getFilterHandler()
	{
		return $this->filterHandler;
	}

	/**
	 * Returns an array of fields for GROUP BY clause
	 *
	 * @return array
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Sets a list of fields in GROUP BY clause
	 *
	 * @param mixed $group
	 * @return $this
	 */
	public function setGroup($group)
	{
		$group = !is_array($group) ? array($group) : $group;
		$this->group = $group;

		return $this;
	}

	/**
	 * Adds a field to the list of fields for GROUP BY clause
	 *
	 * @param $group
	 * @return $this
	 */
	public function addGroup($group)
	{
		$this->group[] = $group;
		return $this;
	}

	/**
	 * Returns an array of fields for ORDER BY clause
	 *
	 * @return array
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * Sets a list of fields for ORDER BY clause.
	 * Format:
	 *   setOrder('ID') -- ORDER BY `ID` ASC
	 *   setOrder(['ID' => 'DESC', 'NAME' => 'ASC]) -- ORDER BY `ID` DESC, `NAME` ASC
	 *
	 * @param mixed $order
	 *
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function setOrder($order)
	{
		$this->order = array();

		if (!is_array($order))
		{
			$order = array($order);
		}

		foreach ($order as $k => $v)
		{
			if (is_numeric($k))
			{
				$this->addOrder($v);
			}
			else
			{
				$this->addOrder($k, $v);
			}
		}

		return $this;
	}

	/**
	 * Adds a filed to the list of fields for ORDER BY clause
	 *
	 * @param string $definition
	 * @param string $order
	 *
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function addOrder($definition, $order = 'ASC')
	{
		$order = strtoupper($order);

		if (!in_array($order, array('ASC', 'DESC'), true))
		{
			throw new Main\ArgumentException(sprintf('Invalid order "%s"', $order));
		}

		$connection = $this->entity->getConnection();
		$helper = $connection->getSqlHelper();

		if ($order == 'ASC')
		{
			$order = $helper->getAscendingOrder();
		}
		else
		{
			$order = $helper->getDescendingOrder();
		}

		$this->order[$definition] = $order;

		return $this;
	}

	/**
	 * Returns a limit
	 *
	 * @return null|int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Sets a limit for LIMIT n clause
	 *
	 * @param int $limit
	 * @return $this
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Returns an offset
	 *
	 * @return null|int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * Sets an offset for LIMIT n, m clause

	 * @param int $offset
	 * @return $this
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	public function countTotal($count = null)
	{
		if ($count === null)
		{
			return $this->countTotal;
		}
		else
		{
			$this->countTotal = (bool) $count;
			return $this;
		}
	}

	/**
	 * Puts additional query to union with current.
	 * Accepts one ore more Query / SqlExpression.
	 *
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function union()
	{
		foreach (func_get_args() as $arg)
		{
			$this->getUnionHandler()->addQuery(new UnionCondition($arg, false));
		}

		return $this;
	}

	/**
	 * Puts additional query to union (all) with current.
	 * Accepts one ore more Query / SqlExpression.
	 *
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function unionAll()
	{
		foreach (func_get_args() as $arg)
		{
			$this->getUnionHandler()->addQuery(new UnionCondition($arg, true));
		}

		return $this;
	}

	/**
	 * General order for all the union queries.
	 * Has the same format as Query::setOrder().
	 * @see Query::setOrder()
	 *
	 * @param $order
	 *
	 * @return $this
	 * @throws Main\SystemException
	 */
	public function setUnionOrder($order)
	{
		$this->getUnionHandler()->setOrder($order);
		return $this;
	}

	/**
	 * General order for all the union queries.
	 * Has the same format as Query::addOrder().
	 * @see Query::addOrder()
	 *
	 * @param string $definition
	 * @param string $order
	 *
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function addUnionOrder($definition, $order = 'ASC')
	{
		$this->getUnionHandler()->addOrder($definition, $order);
		return $this;
	}

	/**
	 * General limit for all the union queries.
	 *
	 * @param $limit
	 *
	 * @return $this
	 * @throws Main\SystemException
	 */
	public function setUnionLimit($limit)
	{
		$this->getUnionHandler()->setLimit($limit);
		return $this;
	}

	/**
	 * General offset for all the union queries.
	 *
	 * @param $offset
	 *
	 * @return $this
	 * @throws Main\SystemException
	 */
	public function setUnionOffset($offset)
	{
		$this->getUnionHandler()->setOffset($offset);
		return $this;
	}

	/**
	 * @see disableDataDoubling
	 *
	 * @return $this
	 */
	public function enableDataDoubling()
	{
		$this->data_doubling_off = false;

		return $this;
	}

	/**
	 * Replaces all 1:N relations in filter to ID IN (subquery SELECT ID FROM <1:N relation>)
	 * Available for Entities with 1 primary field only
	 *
	 * @return $this
	 */
	public function disableDataDoubling()
	{
		if (count($this->entity->getPrimaryArray()) !== 1)
		{
			// mssql doesn't support constructions WHERE (col1, col2) IN (SELECT col1, col2 FROM SomeOtherTable)
			/* @see http://connect.microsoft.com/SQLServer/feedback/details/299231/add-support-for-ansi-standard-row-value-constructors */
			trigger_error(sprintf(
				'Disabling data doubling available for Entities with 1 primary field only. Number of primaries of your entity `%s` is %d.',
				$this->entity->getFullName(), count($this->entity->getPrimaryArray())
			), E_USER_WARNING);
		}
		else
		{
			$this->data_doubling_off = true;
		}

		return $this;
	}

	/**
	 * Allows private fields in query
	 *
	 * @return $this
	 */
	public function enablePrivateFields()
	{
		$this->private_fields_on = true;

		return $this;
	}

	/**
	 * Restricts private fields in query
	 *
	 * @return $this
	 */
	public function disablePrivateFields()
	{
		$this->private_fields_on = false;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isPrivateFieldsEnabled()
	{
		return $this->private_fields_on;
	}

	protected function checkForPrivateFields()
	{
		// check in filter
		foreach ($this->filter_chains as $chain)
		{
			if (static::isFieldPrivate($chain->getLastElement()->getValue()))
			{
				$columnField = $chain->getLastElement()->getValue();

				throw new SystemException(sprintf(
					'Private field %s.%s is restricted in filter',
					$columnField->getEntity()->getDataClass(),
					$columnField->getName()
				));
			}
		}

		// check in general
		if ($this->private_fields_on !== true)
		{
			foreach ($this->global_chains as $chain)
			{
				if (static::isFieldPrivate($chain->getLastElement()->getValue()))
				{
					$columnField = $chain->getLastElement()->getValue();

					throw new SystemException(sprintf(
						'Private field %s.%s is restricted in query, use Query::enablePrivateFields() to allow it',
						$columnField->getEntity()->getDataClass(),
						$columnField->getName()
					));
				}
			}
		}
	}

	/**
	 * @param Field|Main\ORM\Fields\IReadable $field
	 *
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws SystemException
	 */
	public static function isFieldPrivate($field)
	{
		if ($field instanceof ScalarField)
		{
			return $field->isPrivate();
		}
		elseif ($field instanceof ExpressionField)
		{
			foreach ($field->getBuildFromChains() as $chain)
			{
				if (static::isFieldPrivate($chain->getLastElement()->getValue()))
				{
					return  true;
				}
			}
		}

		return false;
	}

	/**
	 * Adds a runtime field (being created dynamically, opposite to being described statically in the entity map)
	 *
	 * @param string|null|Field $name
	 * @param array|Field $fieldInfo
	 *
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function registerRuntimeField($name, $fieldInfo = null)
	{
		if ($name instanceof Field && $fieldInfo === null)
		{
			// short call for Field objects
			$fieldInfo = $name;
			$name = $fieldInfo->getName();
		}
		elseif ((empty($name) || is_numeric($name)) && $fieldInfo instanceof Field)
		{
			$name = $fieldInfo->getName();
		}

		// clone field as long as Field object could be initialized only once
		// there is no need to initialize original object
		if ($fieldInfo instanceof Field)
		{
			$fieldInfo = clone $fieldInfo;
		}

		// attach field to the entity
		$this->entity->addField($fieldInfo, $name);

		// force chain creation for further needs
		$chain = $this->getRegisteredChain($name, true);
		$this->registerChain('runtime', $chain);

		if ($chain->getLastElement()->getValue() instanceof ExpressionField)
		{
			$this->collectExprChains($chain, array('hidden'));
		}

		return $this;
	}

	public function setTableAliasPostfix($postfix)
	{
		$this->table_alias_postfix = $postfix;
		return $this;
	}

	public function getTableAliasPostfix()
	{
		return $this->table_alias_postfix;
	}

	/**
	 * Sets a custom alias for the table of the init entity
	 *
	 * @param string $alias
	 *
	 * @return $this
	 */
	public function setCustomBaseTableAlias($alias)
	{
		$this->custom_base_table_alias = $alias;
		return $this;
	}

	/**
	 * Returns new instance of Filter.
	 *
	 * Usage:
	 *   Query::filter()->where(...)
	 *
	 * Alternatively short calls Query::where* can be used.
	 * @see Query::where()
	 *
	 * @return Filter
	 */
	public static function filter()
	{
		return new Filter;
	}

	/**
	 * Used to create ExpressionField in a short way.
	 * @see Filter::where()
	 *
	 * @return Expression
	 */
	public static function expr()
	{
		if (static::$expressionHelper === null)
		{
			static::$expressionHelper = new Expression;
		}

		return static::$expressionHelper;
	}

	/**
	 * Builds and executes the query and returns the result
	 *
	 * @return Result
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function exec()
	{
		$this->is_executing = true;

		$query = $this->buildQuery();

		$cacheId = "";
		$ttl = 0;
		$result = null;

		if($this->cacheTtl > 0 && (empty($this->join_map) || $this->cacheJoins == true))
		{
			$ttl = $this->entity->getCacheTtl($this->cacheTtl);
		}

		if($ttl > 0)
		{
			$cacheId = md5($query);
			$result = $this->entity->readFromCache($ttl, $cacheId, $this->countTotal);
		}

		if($result === null)
		{
			$result = $this->query($query);

			if($ttl > 0)
			{
				$result = $this->entity->writeToCache($result, $cacheId, $this->countTotal);
			}
		}

		$this->is_executing = false;

		$queryResult = new Result($this, $result);

		if (!empty($this->forcedObjectPrimaryFields))
		{
			$queryResult->setHiddenObjectFields($this->forcedObjectPrimaryFields);
		}

		return $queryResult;
	}

	/**
	 * Short alias for $result->fetch()
	 *
	 * @param Main\Text\Converter|null $converter
	 *
	 * @return array|false
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function fetch(\Bitrix\Main\Text\Converter $converter = null)
	{
		return $this->exec()->fetch($converter);
	}

	/**
	 * Short alias for $result->fetchAll()
	 *
	 * @param Main\Text\Converter|null $converter
	 *
	 * @return array
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function fetchAll(\Bitrix\Main\Text\Converter $converter = null)
	{
		return $this->exec()->fetchAll($converter);
	}

	/**
	 * Short alias for $result->fetchObject()
	 *
	 * @return null Actual type should be annotated by orm:annotate
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function fetchObject()
	{
		return $this->exec()->fetchObject();
	}

	/**
	 * Short alias for $result->fetchCollection()
	 *
	 * @return null Actual type should be annotated by orm:annotate
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function fetchCollection()
	{
		return $this->exec()->fetchCollection();
	}

	protected function ensurePrimarySelect()
	{
		// no auto primary for queries with group
		// it may change the result
		if ($this->hasAggregation() || $this->hasDistinct())
		{
			return;
		}

		$entities = [[$this->entity, '']];

		foreach ($this->join_map as $join)
		{
			$entities[] = [$join['entity'], $join];
		}

		// check for primaries in select
		foreach ($entities as list($entity, $join))
		{
			/** @var Entity $entity */
			foreach ($entity->getPrimaryArray() as $primary)
			{
				if (!empty($entity->getField($primary)->hasParameter('auto_generated')))
				{
					continue;
				}

				$needDefinition = !empty($join['definition']) ? $join['definition'].'.'.$primary : $primary;

				$chain = $this->getRegisteredChain($needDefinition, true);

				if (empty($this->select_chains[$chain->getAlias()]))
				{
					// set uniq alias
					$alias = $this->getUniqueAlias();
					$chain->setCustomAlias($alias);

					$this->registerChain('select', $chain);

					// remember to delete alias from array result
					$this->forcedObjectPrimaryFields[] = $alias;

					// set join alias
					!empty($join)
						? $chain->getLastElement()->setParameter('talias', $join['alias'])
						: $chain->getLastElement()->setParameter('talias', $this->getInitAlias());
				}
			}
		}
	}

	/**
	 * @param      $definition
	 * @param null $alias
	 *
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function addToSelectChain($definition, $alias = null)
	{
		if ($definition instanceof ExpressionField)
		{
			if (empty($alias))
			{
				$alias = $definition->getName();
			}

			$this->registerRuntimeField($alias, $definition);
			$chain = $this->getRegisteredChain($alias);

			// add
			$this->registerChain('select', $chain);

			// recursively collect all "build_from" fields
			if ($chain->getLastElement()->getValue() instanceof ExpressionField)
			{
				$this->collectExprChains($chain, array('hidden', 'select_expr'));
			}
		}
		elseif (is_array($definition))
		{
			// it is runtime field
			// now they are @deprecated in here
			throw new Main\ArgumentException(
				'Expression as an array in `select` section is no more supported due to security reason.'
				.' Please use `runtime` parameter, or Query->registerRuntimeField method, or pass ExpressionField object instead of array.'
			);
		}
		else
		{
			// localize definition (get last field segment e.g. NAME from REF1.REF2.NAME)
			$localDefinitionPos = strrpos($definition, '.');

			if ($localDefinitionPos !== false)
			{
				$localDefinition = substr($definition, $localDefinitionPos + 1);
				$localEntityDef = substr($definition, 0, $localDefinitionPos);
				$localChain = Chain::getChainByDefinition($this->entity, $localEntityDef.'.*');
				$lastElemValue = $localChain->getLastElement()->getValue();

				if ($lastElemValue instanceof Reference)
				{
					$localEntity = $lastElemValue->getRefEntity();
				}
				elseif (is_array($lastElemValue))
				{
					list($localEntity, ) = $lastElemValue;
				}
				else
				{
					$localEntity = $lastElemValue;
				}
			}
			else
			{
				$localDefinition = $definition;
				$localEntityDef = "";
				$dataClass = $this->entity->getDataClass();
				$localEntity = $dataClass::getEntity();
			}

			// if there is a shell pattern in final segment, run recursively
			if ((strlen($localDefinition) > 1 && strpos($localDefinition, '*') !== false)
				|| strpos($localDefinition, '?') !== false
			)
			{
				// get fields by pattern
				foreach ($localEntity->getFields() as $field)
				{
					if (
						($field instanceof ScalarField || $field instanceof ExpressionField)
						&& fnmatch($localDefinition, $field->getName())
					)
					{
						// skip private fields
						if ($field instanceof ScalarField && $field->isPrivate())
						{
							continue;
						}

						// skip uf utm single
						if (
							substr($field->getName(), 0, 3) == 'UF_' && substr($field->getName(), -7) == '_SINGLE'
							&& $localEntity->hasField(substr($field->getName(), 0, -7))
						)
						{
							continue;
						}


						// build alias
						$customAlias = null;

						if ($alias !== null)
						{
							// put alias as a prefix
							$customAlias = $alias.$field->getName();
						}

						// build definition
						$fieldDefinition = $field->getName();

						if (!empty($localEntityDef))
						{
							$fieldDefinition = $localEntityDef.'.'.$fieldDefinition;
						}

						$this->addToSelectChain($fieldDefinition, $customAlias);
					}
				}

				return $this;
			}

			// there is normal scalar field, or Reference, or Entity (all fields of)
			$chain = $this->getRegisteredChain($definition, true);

			if ($alias !== null)
			{
				// custom alias
				$chain = clone $chain;
				$chain->setCustomAlias($alias);
			}

			$last_elem = $chain->getLastElement();

			// fill if element is not scalar
			/** @var null|Entity $expand_entity */
			$expand_entity = null;

			if ($last_elem->getValue() instanceof Reference)
			{
				$expand_entity = $last_elem->getValue()->getRefEntity();
			}
			elseif (is_array($last_elem->getValue()))
			{
				list($expand_entity, ) = $last_elem->getValue();
			}
			elseif ($last_elem->getValue() instanceof Entity)
			{
				$expand_entity = $last_elem->getValue();
			}
			elseif ($last_elem->getValue() instanceof OneToMany)
			{
				$expand_entity = $last_elem->getValue()->getRefEntity();
			}
			elseif ($last_elem->getValue() instanceof ManyToMany)
			{
				$expand_entity = $last_elem->getValue()->getRefEntity();
			}

			if (!$expand_entity && $alias !== null)
			{
				// we have a single field, let's check its custom alias
				if (
					$this->entity->hasField($alias)
					&& (
						// if it's not the same field
						$this->entity->getFullName() !== $last_elem->getValue()->getEntity()->getFullName()
						||
						$last_elem->getValue()->getName() !== $alias
					)
				)
				{
					// deny aliases eq. existing fields
					throw new Main\ArgumentException(sprintf(
						'Alias "%s" matches already existing field "%s" of initial entity "%s". '.
						'Please choose another name for alias.',
						$alias, $alias, $this->entity->getFullName()
					));
				}
			}

			if ($expand_entity)
			{
				// add all fields of entity
				foreach ($expand_entity->getFields() as $exp_field)
				{
					// except for references and expressions
					if ($exp_field instanceof ScalarField)
					{
						// skip private fields
						if ($exp_field->isPrivate())
						{
							continue;
						}

						$exp_chain = clone $chain;
						$exp_chain->addElement(new ChainElement(
							$exp_field
						));

						// custom alias
						if ($alias !== null)
						{
							$fieldAlias = $alias . $exp_field->getName();

							// deny aliases eq. existing fields
							if ($this->entity->hasField($fieldAlias))
							{
								throw new Main\ArgumentException(sprintf(
									'Alias "%s" + field "%s" match already existing field "%s" of initial entity "%s". '.
									'Please choose another name for alias.',
									$alias, $exp_field->getName(), $fieldAlias, $this->entity->getFullName()
								));
							}

							$exp_chain->setCustomAlias($fieldAlias);
						}

						// add
						$this->registerChain('select', $exp_chain);
					}
				}
			}
			else
			{
				// scalar field that defined in entity
				$this->registerChain('select', $chain);

				// it would be nice here to register field as a runtime when it has custom alias
				// it will make possible to use aliased fields as a native init entity fields
				// e.g. in expressions or in data_doubling=off filter

				// collect buildFrom fields (recursively)
				if ($chain->getLastElement()->getValue() instanceof ExpressionField)
				{
					$this->collectExprChains($chain, array('hidden', 'select_expr'));
				}
			}
		}

		return $this;
	}

	/**
	 * @param        $filter
	 * @param string $section
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function setFilterChains(&$filter, $section = 'filter')
	{
		foreach ($filter as $filter_def => &$filter_match)
		{
			if ($filter_def === 'LOGIC')
			{
				continue;
			}

			if (!is_numeric($filter_def))
			{
				$sqlWhere = new \CSQLWhere();
				$csw_result = $sqlWhere->makeOperation($filter_def);
				list($definition, ) = array_values($csw_result);

				// do not register it in global chain registry - get it in a smuggled way
				// - we will do the registration later after UF rewriting and data doubling checking
				$chain = $this->getRegisteredChain($definition);

				if (!$chain)
				{
					// try to find it in filter chains if it is 2nd call of method (when dividing filter for where/having)
					// and chain is still not registered in global (e.g. when forcesDataDoublingOff)
					$chain = $this->filter_chains[$definition] ?? Chain::getChainByDefinition($this->entity, $definition);
				}

				// dirty hack for UF multiple fields: replace text UF_SMTH by UF_SMTH_SINGLE
				$dstField = $chain->getLastElement()->getValue();
				$dstEntity = $dstField->getEntity();

				if ($dstField instanceof ExpressionField && count($dstField->getBuildFromChains()) == 1)
				{
					// hold entity, but get real closing field
					$dstBuildFromChains = $dstField->getBuildFromChains();

					/** @var Chain $firstChain */
					$firstChain = $dstBuildFromChains[0];
					$dstField = $firstChain->getLastElement()->getValue();
				}

				// check for base linking
				if (($dstField instanceof TextField || $dstField instanceof ArrayField)
						&& $dstEntity->hasField($dstField->getName().'_SINGLE'))
				{
					$utmLinkField = $dstEntity->getField($dstField->getName().'_SINGLE');

					if ($utmLinkField instanceof ExpressionField)
					{
						$buildFromChains = $utmLinkField->getBuildFromChains();

						// check for back-reference
						if (count($buildFromChains) == 1 && $buildFromChains[0]->hasBackReference())
						{
							$endField = $buildFromChains[0]->getLastElement()->getValue();

							// and final check for entity name
							if(strpos($endField->getEntity()->getName(), 'Utm'))
							{
								$expressionChain = clone $chain;
								$expressionChain->removeLastElement();
								$expressionChain->addElement(new ChainElement(clone $utmLinkField));
								$expressionChain->forceDataDoublingOff();

								$chain = $expressionChain;

								// rewrite filter definition
								unset($filter[$filter_def]);
								$filter[$filter_def.'_SINGLE'] = $filter_match;
								$definition .= '_SINGLE';
							}
						}
					}
				}

				// continue
				$registerChain = true;

				// if data doubling disabled and it is back-reference - do not register, it will be overwritten
				if ($chain->forcesDataDoublingOff() || ($this->data_doubling_off && $chain->hasBackReference()))
				{
					$registerChain = false;
				}

				if ($registerChain)
				{
					$this->registerChain($section, $chain, $definition);

					// fill hidden select
					if ($chain->getLastElement()->getValue() instanceof ExpressionField)
					{
						$this->collectExprChains($chain);
					}
				}
				else
				{
					// hide from global registry to avoid "join table"
					// but we still need it in filter chains
					$this->filter_chains[$chain->getAlias()] = $chain;
					$this->filter_chains[$definition] = $chain;

					// and we will need primary chain in filter later when overwriting data-doubling
					$this->getRegisteredChain($this->entity->getPrimary(), true);
				}
			}
			elseif (is_array($filter_match))
			{
				$this->setFilterChains($filter_match, $section);
			}
		}
	}

	/**
	 * @param Filter $where
	 * @param string $section
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function setFilterHandlerChains(Filter $where, $section = 'filter')
	{
		foreach ($where->getConditions() as $condition)
		{
			if ($condition instanceof Filter)
			{
				// subfilter
				$this->setFilterHandlerChains($condition, $section);
			}
			else
			{
				$definition = $condition->getDefinition();

				// check for runtime fields
				if ($definition instanceof Field)
				{
					// register runtime field
					$this->registerRuntimeField($definition);

					// rewrite definition in filter - replace field with its name
					$definition = $definition->getName();
					$condition->setDefinition($definition);
				}

				// check if it's a regular condition, not kind of boolean/exists expression
				if ($definition !== null)
				{
					// regular condition
					$chain = $this->getRegisteredChain($definition);

					if (!$chain)
					{
						// try to find it in filter chains if it is 2nd call of method (when dividing filter for where/having)
						// and chain is still not registered in global (e.g. when forcesDataDoublingOff)
						$chain = $this->filter_chains[$definition] ?? Chain::getChainByDefinition($this->entity, $definition);
					}

					// dirty hack for UF multiple fields: replace text UF_SMTH by UF_SMTH_SINGLE
					$dstField = $chain->getLastElement()->getValue();
					$dstEntity = $dstField->getEntity();

					if ($dstField instanceof ExpressionField && count($dstField->getBuildFromChains()) == 1)
					{
						// hold entity, but get real closing field
						$dstBuildFromChains = $dstField->getBuildFromChains();

						/** @var Chain $firstChain */
						$firstChain = $dstBuildFromChains[0];
						$dstField = $firstChain->getLastElement()->getValue();
					}

					// check for base linking
					if (($dstField instanceof TextField || $dstField instanceof ArrayField)
						&& $dstEntity->hasField($dstField->getName().'_SINGLE'))
					{
						$utmLinkField = $dstEntity->getField($dstField->getName().'_SINGLE');

						if ($utmLinkField instanceof ExpressionField)
						{
							$buildFromChains = $utmLinkField->getBuildFromChains();

							// check for back-reference
							if (count($buildFromChains) == 1 && $buildFromChains[0]->hasBackReference())
							{
								$endField = $buildFromChains[0]->getLastElement()->getValue();

								// and final check for entity name
								if(strpos($endField->getEntity()->getName(), 'Utm'))
								{
									$expressionChain = clone $chain;
									$expressionChain->removeLastElement();
									$expressionChain->addElement(new ChainElement(clone $utmLinkField));
									$expressionChain->forceDataDoublingOff();

									$chain = $expressionChain;

									// rewrite filter definition
									$definition .= '_SINGLE';
									$condition->setDefinition($definition);
								}
							}
						}
					}

					// continue
					$registerChain = true;

					// if data doubling disabled and it is back-reference - do not register, it will be overwritten
					if ($chain->forcesDataDoublingOff() || ($this->data_doubling_off && $chain->hasBackReference()))
					{
						$registerChain = false;
					}

					if ($registerChain)
					{
						$this->registerChain($section, $chain, $definition);

						// fill hidden select
						if ($chain->getLastElement()->getValue() instanceof ExpressionField)
						{
							$this->collectExprChains($chain);
						}
					}
					else
					{
						// hide from global registry to avoid "join table"
						// but we still need it in filter chains
						$this->filter_chains[$chain->getAlias()] = $chain;
						$this->filter_chains[$definition] = $chain;

						// and we will need primary chain in filter later when overwriting data-doubling
						$this->getRegisteredChain($this->entity->getPrimary(), true);
					}
				}

				// when compare with column, put it in the chains too
				foreach ($condition->getAtomicValues() as $value)
				{
					if ($value instanceof ColumnExpression)
					{
						$valueDefinition = $value->getDefinition();

						$chain = $this->filter_chains[$valueDefinition] ?? Chain::getChainByDefinition($this->entity, $valueDefinition);

						$this->registerChain($section, $chain, $valueDefinition);
					}

					// set connection to correct escaping in expressions
					if ($value instanceof Main\DB\SqlExpression)
					{
						$value->setConnection($this->entity->getConnection());
					}
				}
			}
		}
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function divideFilter()
	{
		// divide filter to where and having

		$logic = $this->filter['LOGIC'] ?? 'AND';

		if ($logic == 'OR')
		{
			// if has aggr then move all to having
			if ($this->checkFilterAggregation($this->filter))
			{
				$this->where = array();
				$this->where_chains = array();

				$this->having = $this->filter;
				$this->having_chains = $this->filter_chains;
			}
			else
			{
				$this->where = $this->filter;
				$this->where_chains = $this->filter_chains;

				$this->having = array();
				$this->having_chains = array();
			}
		}
		elseif ($logic == 'AND')
		{
			// we can separate root filters
			foreach ($this->filter as $k => $sub_filter)
			{
				if ($k === 'LOGIC')
				{
					$this->where[$k] = $sub_filter;
					$this->having[$k] = $sub_filter;

					continue;
				}

				$tmp_filter = array($k => $sub_filter);

				if ($this->checkFilterAggregation($tmp_filter))
				{
					$this->having[$k] = $sub_filter;
					$this->setFilterChains($tmp_filter, 'having');
				}
				else
				{
					$this->where[$k] = $sub_filter;
					$this->setFilterChains($tmp_filter, 'where');
				}
			}
		}

		// collect "build_from" fields from having
		foreach ($this->having_chains as $chain)
		{
			if ($chain->getLastElement()->getValue() instanceof ExpressionField)
			{
				$this->collectExprChains($chain, array('hidden', 'having_expr'));
			}
		}
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function divideFilterHandler()
	{
		$logic = $this->filterHandler->logic();

		if ($logic == 'or')
		{
			// if has aggr then move all to having
			if ($this->checkFilterHandlerAggregation($this->filterHandler))
			{
				$this->havingHandler = $this->filterHandler;
				$this->having_chains = $this->filter_chains;
			}
			else
			{
				$this->whereHandler = $this->filterHandler;
				$this->where_chains = $this->filter_chains;
			}
		}
		elseif ($logic == 'and')
		{
			// we can separate root filters
			foreach ($this->filterHandler->getConditions() as $condition)
			{
				$tmpFilter = static::filter()->addCondition($condition);

				if ($this->checkFilterHandlerAggregation($tmpFilter))
				{
					$this->havingHandler->addCondition($tmpFilter);
					$this->setFilterHandlerChains($tmpFilter, 'having');
				}
				else
				{
					$this->whereHandler->addCondition($condition);
					$this->setFilterHandlerChains($tmpFilter, 'where');
				}
			}
		}

		// collect "build_from" fields from having
		foreach ($this->having_chains as $chain)
		{
			if ($chain->getLastElement()->getValue() instanceof ExpressionField)
			{
				$this->collectExprChains($chain, array('hidden', 'having_expr'));
			}
		}
	}

	/**
	 * @param $filter
	 *
	 * @return bool
	 * @throws Main\SystemException
	 */
	protected function checkFilterAggregation($filter)
	{
		foreach ($filter as $filter_def => $filter_match)
		{
			if ($filter_def === 'LOGIC')
			{
				continue;
			}

			$is_having = false;
			if (!is_numeric($filter_def))
			{
				$sqlWhere = new \CSQLWhere();
				$csw_result = $sqlWhere->makeOperation($filter_def);
				list($definition, ) = array_values($csw_result);

				$chain = $this->filter_chains[$definition];
				$last = $chain->getLastElement();

				$is_having = $last->getValue() instanceof ExpressionField && $last->getValue()->isAggregated();
			}
			elseif (is_array($filter_match))
			{
				$is_having = $this->checkFilterAggregation($filter_match);
			}

			if ($is_having)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param Filter $filter
	 *
	 * @return bool
	 * @throws Main\SystemException
	 */
	protected function checkFilterHandlerAggregation(Filter $filter)
	{
		foreach ($filter->getConditions() as $condition)
		{
			$is_having = false;

			if ($condition instanceof Filter)
			{
				// subfilter
				$is_having = $this->checkFilterHandlerAggregation($condition);
			}
			else
			{
				// check if it is not a boolean/exists condition
				if ($condition->getDefinition() !== null)
				{
					// regular condition
					$chain = $this->filter_chains[$condition->getDefinition()];
					$last = $chain->getLastElement();

					$is_having = $last->getValue() instanceof ExpressionField && $last->getValue()->isAggregated();

					// check if value is a field and has aggregation
					if (!$is_having && $condition->getValue() instanceof ColumnExpression)
					{
						$chain = $this->filter_chains[$condition->getValue()->getDefinition()];
						$last = $chain->getLastElement();

						$is_having = $last->getValue() instanceof ExpressionField && $last->getValue()->isAggregated();

						// actually if it has happened, we need to add group by the first column
					}
				}
			}

			if ($is_having)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param Filter $filter
	 * @param        $section
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function rewriteDataDoubling(Filter $filter, $section)
	{
		foreach ($filter->getConditions() as $condition)
		{
			if ($condition instanceof Filter)
			{
				//subfilter
				$this->rewriteDataDoubling($condition, $section);
			}
			elseif ($condition->getDefinition() !== null)
			{
				// regular condition
				$chain = $this->filter_chains[$condition->getDefinition()];

				if ($chain->forcesDataDoublingOff() || ($this->data_doubling_off && $chain->hasBackReference()))
				{
					$primaryName = $this->entity->getPrimary();
					$uniquePostfix = '_TMP'.rand();

					// build subquery
					$dataClass = $this->entity->getDataClass();

					$subQuery = $dataClass::query()
						->addSelect($primaryName)
						->where(clone $condition)
						->setTableAliasPostfix(strtolower($uniquePostfix));

					// change condition
					$condition->setColumn($primaryName);
					$condition->setOperator('in');
					$condition->setValue($subQuery);

					// register primary's chain
					$idChain = $this->getRegisteredChain($primaryName);
					$this->registerChain($section, $idChain, $primaryName);
				}
			}
		}
	}

	/**
	 * @param $definition
	 *
	 * @throws Main\SystemException
	 */
	protected function addToGroupChain($definition)
	{
		$chain = $this->getRegisteredChain($definition, true);
		$this->registerChain('group', $chain);

		if ($chain->getLastElement()->getValue() instanceof ExpressionField)
		{
			$this->collectExprChains($chain);
		}
	}

	/**
	 * @param $definition
	 *
	 * @throws Main\SystemException
	 */
	protected function addToOrderChain($definition)
	{
		$chain = $this->getRegisteredChain($definition, true);
		$this->registerChain('order', $chain);

		if ($chain->getLastElement()->getValue() instanceof ExpressionField)
		{
			$this->collectExprChains($chain);
		}
	}

	/**
	 * @param null $chains
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function buildJoinMap($chains = null)
	{
		$connection = $this->entity->getConnection();
		$helper = $connection->getSqlHelper();

		$aliasLength = $helper->getAliasLength();

		if (empty($chains))
		{
			$chains = $this->global_chains;
		}

		foreach ($chains as $chain)
		{
			if ($chain->getLastElement()->getParameter('talias'))
			{
				// already been here
				continue;
			}

			// in NO_DOUBLING mode skip 1:N relations that presented in filter only
			if ($chain->forcesDataDoublingOff() || ($this->data_doubling_off && $chain->hasBackReference()))
			{
				$alias = $chain->getAlias();

				if (isset($this->filter_chains[$alias])
					&& !isset($this->select_chains[$alias]) && !isset($this->select_expr_chains[$alias])
					&& !isset($this->group_chains[$alias]) && !isset($this->order_chains[$alias])
				)
				{
					continue;
				}
			}

			$prev_alias = $this->getInitAlias(false);

			$map_key = '';

			/**
			 * elements after init entity
			 * @var $elements ChainElement[]
			 * */
			$elements = array_slice($chain->getAllElements(), 1);

			$currentDefinition = array();

			foreach ($elements as $element)
			{
				$table_alias = null;

				/**
				 * define main objects
				 * @var $ref_field Reference
				 * @var $dst_entity Entity
				 */
				if ($element->getValue() instanceof Reference)
				{
					// ref to another entity
					$ref_field = $element->getValue();
					$dst_entity = $ref_field->getRefEntity();
					$joinType = $ref_field->getJoinType();
				}
				elseif (is_array($element->getValue()))
				{
					// link from another entity to this
					list($dst_entity, $ref_field) = $element->getValue();
					$joinType = $ref_field->getJoinType();
				}
				elseif ($element->getValue() instanceof OneToMany)
				{
					// the same as back reference
					$dst_entity = $element->getValue()->getRefEntity();
					$ref_field = $element->getValue()->getRefField();
					$joinType = $element->getValue()->getJoinType() ?: $ref_field->getJoinType();
				}
				elseif ($element->getValue() instanceof ManyToMany)
				{
					$mtm = $element->getValue();

					// join mediator and remote entities in hidden mode
					// first, make new chain, remove everything after this mtm and remove mtm itself
					$tmpChain = clone $chain;
					$mtmDefinition = join('.', $currentDefinition);

					while ($tmpChain->getDefinition() != $mtmDefinition)
					{
						$tmpChain->removeLastElement();
					}

					// then add backReference to mediator - mediator entity and local reference
					$tmpChain->addElement(new ChainElement([
						$mtm->getMediatorEntity(), $mtm->getLocalReference()
					]));

					// then add reference from mediator to remote entity
					$tmpChain->addElement(new ChainElement($mtm->getRemoteReference()));

					// now join this chain
					$this->registerChain('global', $tmpChain);
					$this->buildJoinMap([$tmpChain]);

					// and finally remember table alias for mtm element
					$prev_alias = $tmpChain->getLastElement()->getParameter('talias');
					$element->setParameter('talias', $prev_alias);

					// skip any standard actions, continue with next element
					continue;
				}
				else
				{
					// scalar field
					// if it's a field of the init entity, use getInitAlias to use 'base' alias
					if ($prev_alias === $this->getInitAlias(false))
					{
						$element->setParameter('talias', $this->getInitAlias());
					}
					else
					{
						$element->setParameter('talias', $prev_alias.$this->table_alias_postfix);
					}

					continue;
				}

				// mapping
				if (empty($map_key))
				{
					$map_key = join('.', $currentDefinition);
				}

				$map_key .= '/' . $ref_field->getName() . '/' . $dst_entity->getName();

				$currentDefinition[] = $element->getDefinitionFragment();

				if (isset($this->join_registry[$map_key]))
				{
					// already connected
					$table_alias = $this->join_registry[$map_key];
				}
				else
				{
					// prepare reference
					$reference = $ref_field->getReference();

					if ($element->getValue() instanceof Reference)
					{
						// ref to another entity
						if (is_null($table_alias))
						{
							$table_alias = $prev_alias.'_'.strtolower($ref_field->getName());

							if (strlen($table_alias.$this->table_alias_postfix) > $aliasLength)
							{
								$old_table_alias = $table_alias;
								$table_alias = 'TALIAS_' . (count($this->replaced_taliases) + 1);
								$this->replaced_taliases[$table_alias] = $old_table_alias;
							}
						}

						$alias_this = $prev_alias;
						$alias_ref = $table_alias;

						$isBackReference = false;

						$definition_this = join('.', array_slice($currentDefinition, 0, -1));
						$definition_ref = join('.', $currentDefinition);
						$definition_join = $definition_ref;
					}
					elseif (is_array($element->getValue()) || $element->getValue() instanceof OneToMany)
					{
						if (is_null($table_alias))
						{
							$table_alias = StringHelper::camel2snake($dst_entity->getName()).'_'.strtolower($ref_field->getName());
							$table_alias = $prev_alias.'_'.$table_alias;

							if (strlen($table_alias.$this->table_alias_postfix) > $aliasLength)
							{
								$old_table_alias = $table_alias;
								$table_alias = 'TALIAS_' . (count($this->replaced_taliases) + 1);
								$this->replaced_taliases[$table_alias] = $old_table_alias;
							}
						}

						$alias_this = $table_alias;
						$alias_ref = $prev_alias;

						$isBackReference = true;

						$definition_this = join('.', $currentDefinition);
						$definition_ref = join('.', array_slice($currentDefinition, 0, -1));
						$definition_join = $definition_this;
					}
					else
					{
						throw new Main\SystemException(sprintf('Unknown reference element `%s`', $element->getValue()));
					}

					// replace this. and ref. to real definition
					if ($reference instanceof Filter)
					{
						$csw_reference = $this->prepareJoinFilterReference(
							$reference,
							$alias_this.$this->table_alias_postfix,
							$alias_ref.$this->table_alias_postfix,
							$definition_this,
							$definition_ref,
							$isBackReference
						);
					}
					else
					{
						$csw_reference = $this->prepareJoinReference(
							$reference,
							$alias_this.$this->table_alias_postfix,
							$alias_ref.$this->table_alias_postfix,
							$definition_this,
							$definition_ref,
							$isBackReference
						);
					}

					// double check after recursive call in prepareJoinReference
					if (!isset($this->join_registry[$map_key]))
					{
						$join = array(
							'type' => $joinType,
							'entity' => $dst_entity,
							'definition' => $definition_join,
							'table' => $dst_entity->getDBTableName(),
							'alias' => $table_alias.$this->table_alias_postfix,
							'reference' => $csw_reference,
							'map_key' => $map_key
						);

						$this->join_map[] = $join;
						$this->join_registry[$map_key] = $table_alias;
					}
				}

				// set alias for each element
				$element->setParameter('talias', $table_alias.$this->table_alias_postfix);

				$prev_alias = $table_alias;
			}
		}
	}

	protected function buildSelect()
	{
		$sql = [];

		$helper = $this->entity->getConnection()->getSqlHelper();
		$aliasLength = (int) $helper->getAliasLength();

		foreach ($this->select_chains as $chain)
		{
			$definition = $chain->getSqlDefinition();
			$alias = $chain->getAlias();

			if (strlen($alias) > $aliasLength)
			{
				// replace long aliases
				$newAlias = 'FALIAS_'.count($this->replaced_aliases);
				$this->replaced_aliases[$newAlias] = $alias;

				$alias = $newAlias;
			}

			$sql[] = $definition . ' AS ' . $helper->quote($alias);
		}

		// empty select (or select forced primary only)
		if (empty($sql) ||
			(!empty($this->forcedObjectPrimaryFields) && count($sql) == count($this->forcedObjectPrimaryFields))
		)
		{
			$sql[] = 1;
		}

		$strSql = join(",\n\t", $sql);

		if ($this->hasDistinct() && $this->is_distinct)
		{
			// distinct by query settings, not by field
			$strSql = 'DISTINCT '.$strSql;
		}

		return "\n\t".$strSql;
	}

	/**
	 * @return string
	 * @throws Main\SystemException
	 */
	protected function buildJoin()
	{
		$sql = array();
		$csw = new \CSQLWhere;

		$connection = $this->entity->getConnection();
		$helper = $connection->getSqlHelper();

		foreach ($this->join_map as $join)
		{
			// prepare csw fields
			$csw_fields = $this->getJoinCswFields($join['reference']);
			$csw->setFields($csw_fields);

			if ($join['reference'] instanceof Filter)
			{
				$joinConditionSql = $join['reference']->getSql($this->global_chains);
			}
			else
			{
				$joinConditionSql = trim($csw->getQuery($join['reference']));
			}

			// final sql
			$sql[] = sprintf('%s JOIN %s %s ON %s',
				$join['type'],
				$this->quoteTableSource($join['table']),
				$helper->quote($join['alias']),
				$joinConditionSql
			);
		}

		return "\n".join("\n", $sql);
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function buildWhere()
	{
		$sql = array();

		// old array filter
		if (!empty($this->where))
		{
			$csw = new \CSQLWhere;

			$csw_fields = $this->getFilterCswFields($this->where);
			$csw->setFields($csw_fields);

			$sql[] = trim($csw->getQuery($this->where));
		}

		// new QueryFilter
		if ($this->whereHandler && $this->whereHandler->hasConditions())
		{
			// rewrite data doubling
			$this->rewriteDataDoubling($this->whereHandler, 'where');

			$sql[] = $this->whereHandler->getSql($this->where_chains);
		}

		return join(' AND ', array_filter($sql));
	}

	/**
	 * @return string
	 * @throws Main\SystemException
	 */
	protected function buildGroup()
	{
		$sql = array();

		if ($this->hasAggregation())
		{
			// add non-aggr fields to group
			foreach ($this->global_chains as $chain)
			{
				$alias = $chain->getAlias();

				// skip constants
				if ($chain->isConstant())
				{
					continue;
				}

				if (isset($this->select_chains[$alias]) || isset($this->order_chains[$alias]) || isset($this->having_chains[$alias]))
				{
					if (isset($this->group_chains[$alias]))
					{
						// skip already grouped
						continue;
					}
					elseif (!$chain->hasAggregation() && !$chain->hasSubquery())
					{
						// skip subqueries and already aggregated
						$this->registerChain('group', $chain);
					}
					elseif (!$chain->hasAggregation() && $chain->hasSubquery() && $chain->getLastElement()->getValue() instanceof ExpressionField)
					{
						// but include build_from of subqueries
						$sub_chains = $chain->getLastElement()->getValue()->getBuildFromChains();

						foreach ($sub_chains as $sub_chain)
						{
							// build real subchain starting from init entity
							$real_sub_chain = clone $chain;

							foreach (array_slice($sub_chain->getAllElements(), 1) as $sub_chain_elem)
							{
								$real_sub_chain->addElement($sub_chain_elem);
							}

							// add to query
							$this->registerChain('group', $this->global_chains[$real_sub_chain->getAlias()]);
						}
					}
				}
				elseif (isset($this->having_expr_chains[$alias]))
				{
					if (!$chain->hasAggregation() && $chain->hasSubquery())
					{
						$this->registerChain('group', $chain);
					}
				}
			}
		}

		foreach ($this->group_chains as $chain)
		{
			$connection = $this->entity->getConnection();
			$sqlDefinition = $chain->getSqlDefinition();
			$valueField = $chain->getLastElement()->getValue();

			if ($valueField instanceof ExpressionField)
			{
				$valueField = $valueField->getValueField();
			}

			if (($connection instanceof Main\DB\OracleConnection || $connection instanceof Main\DB\MssqlConnection)
				&& $valueField instanceof TextField)
			{
				// softTextCast
				$sqlDefinition = $connection->getSqlHelper()->softCastTextToChar($sqlDefinition);
			}

			$sql[] = $sqlDefinition;
		}

		return join(', ', $sql);
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function buildHaving()
	{
		$sql = array();

		// old array filter
		if (!empty($this->having))
		{
			$csw = new \CSQLWhere;

			$csw_fields = $this->getFilterCswFields($this->having);
			$csw->setFields($csw_fields);

			$sql[] = trim($csw->getQuery($this->having));
		}

		// new QueryFilter
		if ($this->havingHandler && $this->havingHandler->hasConditions())
		{
			// rewrite data doubling
			$this->rewriteDataDoubling($this->havingHandler, 'having');

			$sql[] = $this->havingHandler->getSql($this->having_chains);
		}

		return join(' AND ', array_filter($sql));
	}

	/**
	 * @return string
	 * @throws Main\SystemException
	 */
	protected function buildOrder()
	{
		$sql = array();

		foreach ($this->order_chains as $chain)
		{
			$sort = isset($this->order[$chain->getDefinition()])
				? $this->order[$chain->getDefinition()]
				: ($this->order[$chain->getAlias()] ?? '');

			$connection = $this->entity->getConnection();

			// define value field
			$valueField = $chain->getLastElement()->getValue();
			if ($valueField instanceof ExpressionField)
			{
				$valueField = $valueField->getValueField();
			}

			// get final sql definition
			if (isset($this->select_chains[$chain->getAlias()]))
			{
				// optimization for fields that are in select already
				$alias = $chain->getAlias();

				if ($key = array_search($alias, $this->replaced_aliases))
				{
					// alias was replaced
					$alias = $key;
				}

				$sqlDefinition = $connection->getSqlHelper()->quote($alias);
			}
			else
			{
				$sqlDefinition = $chain->getSqlDefinition();
			}

			if (($connection instanceof Main\DB\OracleConnection || $connection instanceof Main\DB\MssqlConnection)
				&& $valueField instanceof TextField)
			{
				// softTextCast
				$sqlDefinition = $connection->getSqlHelper()->softCastTextToChar($sqlDefinition);
			}

			$sql[] = $sqlDefinition. ' ' . $sort;
		}

		return join(', ', $sql);
	}

	/**
	 * @param bool $forceObjectPrimary Add missing primaries to select
	 *
	 * @return mixed|string
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function buildQuery($forceObjectPrimary = true)
	{
		$connection = $this->entity->getConnection();
		$helper = $connection->getSqlHelper();

		if ($this->query_build_parts === null)
		{

			foreach ($this->select as $key => $value)
			{
				$this->addToSelectChain($value, is_numeric($key) ? null : $key);
			}

			$this->setFilterChains($this->filter);
			$this->divideFilter();

			// unconditional entity scope
			$this->entity->setDefaultScope($this);

			$this->setFilterHandlerChains($this->filterHandler);
			$this->divideFilterHandler();

			foreach ($this->group as $value)
			{
				$this->addToGroupChain($value);
			}

			foreach ($this->order as $key => $value)
			{
				$this->addToOrderChain($key);
			}

			$this->buildJoinMap();

			if ($forceObjectPrimary && empty($this->unionHandler))
			{
				$this->ensurePrimarySelect();
			}

			$sqlJoin = $this->buildJoin();

			$sqlSelect = $this->buildSelect();
			$sqlWhere = $this->buildWhere();
			$sqlGroup = $this->buildGroup();
			$sqlHaving = $this->buildHaving();
			$sqlOrder = $this->buildOrder();

			$sqlFrom = $this->quoteTableSource($this->entity->getDBTableName());

			$sqlFrom .= ' '.$helper->quote($this->getInitAlias());
			$sqlFrom .= ' '.$sqlJoin;

			$this->query_build_parts = array_filter(array(
				'SELECT' => $sqlSelect,
				'FROM' => $sqlFrom,
				'WHERE' => $sqlWhere,
				'GROUP BY' => $sqlGroup,
				'HAVING' => $sqlHaving,
				'ORDER BY' => $sqlOrder
			));

			// ensure there are no private fields in query
			$this->checkForPrivateFields();
		}

		$build_parts = $this->query_build_parts;

		foreach ($build_parts as $k => &$v)
		{
			$v = $k . ' ' . $v;
		}

		$query = join("\n", $build_parts);

		if ($this->limit > 0)
		{
			$query = $helper->getTopSql($query, $this->limit, $this->offset);
		}

		// union
		if (!empty($this->unionHandler))
		{
			if ($this->order || $this->limit)
			{
				$query = "({$query})";
			}

			foreach ($this->unionHandler->getQueries() as $union)
			{
				$query .= " ".$union->getSql();
			}

			// union sort
			if ($this->unionHandler->getOrder())
			{
				$sqlUnionOrder = array();
				foreach ($this->unionHandler->getOrder() as $definition => $sort)
				{
					$sqlDefinition = $connection->getSqlHelper()->quote(
						$this->global_chains[$definition]->getAlias()
					);

					$sqlUnionOrder[] = $sqlDefinition . ' ' . $sort;
				}

				$query .= ' ORDER BY ' . join(', ', $sqlUnionOrder);
			}

			// union limit
			if ($this->unionHandler->getLimit())
			{
				$query = $helper->getTopSql($query, $this->unionHandler->getLimit(), $this->unionHandler->getOffset());
			}
		}

		return $query;
	}

	/**
	 * @param $filter
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function getFilterCswFields(&$filter)
	{
		$fields = array();

		foreach ($filter as $filter_def => &$filter_match)
		{
			if ($filter_def === 'LOGIC')
			{
				continue;
			}

			if (!is_numeric($filter_def))
			{
				$sqlWhere = new \CSQLWhere();
				$csw_result = $sqlWhere->makeOperation($filter_def);
				list($definition, $operation) = array_values($csw_result);

				$chain = $this->filter_chains[$definition];
				$last = $chain->getLastElement();

				// need to create an alternative of CSQLWhere in D7.Entity
				$field_type = $last->getValue()->getDataType();
				$callback = null;

				// rewrite type & value for CSQLWhere
				if (in_array($operation, array('SE', 'SN'), true)
					&& in_array($filter_match, array(null, true, false), true)
				)
				{
					$field_type = 'callback';

					if ($filter_match === null)
					{
						$callback = array($this, 'nullEqualityCallback');
					}
					else
					{
						// just boolean expression, without operator
						// e.g. WHERE EXISTS(...)
						$callback = array($this, 'booleanStrongEqualityCallback');
					}
				}
				elseif ($field_type == 'integer')
				{
					$field_type = 'int';
				}
				elseif ($field_type == 'boolean')
				{
					$field_type = 'string';

					/** @var BooleanField $field */
					$field = $last->getValue();
					$values = $field->getValues();

					if (is_numeric($values[0]) && is_numeric($values[1]))
					{
						$field_type = 'int';
					}

					if (is_scalar($filter_match))
					{
						$filter_match = $field->normalizeValue($filter_match);
					}
				}
				elseif ($field_type == 'float')
				{
					$field_type = 'double';
				}
				elseif ($field_type == 'enum' || $field_type == 'text')
				{
					$field_type = 'string';
				}

				$sqlDefinition = $chain->getSqlDefinition();

				// data-doubling-off mode
				/** @see disableDataDoubling */
				if ($chain->forcesDataDoublingOff() || ($this->data_doubling_off && $chain->hasBackReference()))
				{
					$primaryName = $this->entity->getPrimary();
					$uniquePostfix = '_TMP'.rand();

					// build subquery
					$subQuery = new Query($this->entity);
					$subQuery->addSelect($primaryName);
					$subQuery->addFilter($filter_def, $filter_match);
					$subQuery->setTableAliasPostfix(strtolower($uniquePostfix));
					$subQuerySql = $subQuery->getQuery();

					// proxying subquery as value to callback
					$filter_match = $subQuerySql;
					$callback = array($this, 'dataDoublingCallback');

					$field_type = 'callback';

					// change sql definition
					$idChain = $this->getRegisteredChain($primaryName);
					$sqlDefinition = $idChain->getSqlDefinition();
				}

				// set entity connection to the sql expressions
				if ($filter_match instanceof Main\DB\SqlExpression)
				{
					$filter_match->setConnection($this->entity->getConnection());
				}

				//$is_having = $last->getValue() instanceof ExpressionField && $last->getValue()->isAggregated();

				// if back-reference found (Entity:REF)
				// if NO_DOUBLING mode enabled, then change getSQLDefinition to subquery exists(...)
				// and those chains should not be in joins if it is possible

				/*if (!$this->data_doubling && $chain->hasBackReference())
				{
					$field_type = 'callback';
					$init_query = $this;

					$callback = function ($field, $operation, $value) use ($init_query, $chain)
					{
						$init_entity = $init_query->getEntity();
						$init_table_alias = CBaseEntity::camel2snake($init_entity->getName()).$init_query->getTableAliasPostfix();

						$filter = array();

						// add primary linking with main query
						foreach ($init_entity->getPrimaryArray() as $primary)
						{
							$filter['='.$primary] = new CSQLWhereExpression('?#', $init_table_alias.'.'.$primary);
						}

						// add value filter
						$filter[CSQLWhere::getOperationByCode($operation).$chain->getDefinition()] = $value;

						// build subquery
						$query_class = __CLASS__;
						$sub_query = new $query_class($init_entity);
						$sub_query->setFilter($filter);
						$sub_query->setTableAliasPostfix('_sub');

						return 'EXISTS(' . $sub_query->getQuery() . ')';
					};
				}*/

				$fields[$definition] = array(
					'TABLE_ALIAS' => 'table',
					'FIELD_NAME' => $sqlDefinition,
					'FIELD_TYPE' => $field_type,
					'MULTIPLE' => '',
					'JOIN' => '',
					'CALLBACK' => $callback
				);
			}
			elseif (is_array($filter_match))
			{
				$fields = array_merge($fields, $this->getFilterCswFields($filter_match));
			}
		}

		return $fields;
	}

	/**
	 * @param $reference
	 * @param $alias_this
	 * @param $alias_ref
	 * @param $baseDefinition
	 * @param $refDefinition
	 * @param $isBackReference
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function prepareJoinReference($reference, $alias_this, $alias_ref, $baseDefinition, $refDefinition, $isBackReference)
	{
		$new = array();

		foreach ($reference as $k => $v)
		{
			if ($k === 'LOGIC')
			{
				$new[$k] = $v;
				continue;
			}

			if (is_numeric($k))
			{
				// subfilter, recursive call
				$new[$k] = $this->prepareJoinReference($v, $alias_this, $alias_ref, $baseDefinition, $refDefinition, $isBackReference);
			}
			else
			{
				// key
				$sqlWhere = new \CSQLWhere();
				$csw_result = $sqlWhere->makeOperation($k);
				list($field, $operation) = array_values($csw_result);

				if (strpos($field, 'this.') === 0)
				{
					// parse the chain
					$definition = str_replace(\CSQLWhere::getOperationByCode($operation).'this.', '', $k);
					$absDefinition = $baseDefinition <> ''? $baseDefinition.'.'.$definition : $definition;

					$chain = $this->getRegisteredChain($absDefinition, true);

					if (!$isBackReference)
					{
						// make sure these fields will be joined before the main join
						$this->buildJoinMap(array($chain));
					}
					else
					{
						$chain->getLastElement()->setParameter('talias', $alias_this);
					}

					// recursively collect all "build_from" fields
					if ($chain->getLastElement()->getValue() instanceof ExpressionField)
					{
						$this->collectExprChains($chain);
						$buildFrom = $chain->getLastElement()->getValue()->getBuildFromChains();

						foreach ($buildFrom as $bf)
						{
							// set base chain
							$baseChain = clone $chain;

							// remove the last one - expression itself
							$baseChain->removeLastElement();

							// remove parent entity for this child
							$bf->removeFirstElement();

							// set new parents
							$bf->prepend($baseChain);
						}

						$this->buildJoinMap($buildFrom);
					}

					$k = \CSQLWhere::getOperationByCode($operation).$chain->getSqlDefinition();
				}
				elseif (strpos($field, 'ref.') === 0)
				{
					$definition = str_replace(\CSQLWhere::getOperationByCode($operation).'ref.', '', $k);

					if (strpos($definition, '.') !== false)
					{
						throw new Main\ArgumentException(sprintf(
							'Reference chain `%s` is not allowed here. First-level definitions only.', $field
						));
					}

					$absDefinition = $refDefinition <> ''? $refDefinition.'.'.$definition : $definition;
					$chain = $this->getRegisteredChain($absDefinition, true);

					if ($isBackReference)
					{
						// make sure these fields will be joined before the main join
						$this->buildJoinMap(array($chain));
					}
					else
					{
						$chain->getLastElement()->setParameter('talias', $alias_ref);
					}

					// recursively collect all "build_from" fields
					if ($chain->getLastElement()->getValue() instanceof ExpressionField)
					{
						$this->collectExprChains($chain);
						$this->buildJoinMap($chain->getLastElement()->getValue()->getBuildFromChains());
					}

					$k = \CSQLWhere::getOperationByCode($operation).$chain->getSqlDefinition();
				}
				else
				{
					throw new Main\SystemException(sprintf('Unknown reference key `%s`, it should start with "this." or "ref."', $k));
				}

				// value
				if (is_array($v))
				{
					// field = expression
					$v = new \CSQLWhereExpression($v[0], array_slice($v, 1));
				}
				elseif ($v instanceof Main\DB\SqlExpression)
				{
					// set entity connection
					$v->setConnection($this->entity->getConnection());
				}
				elseif (!is_object($v))
				{
					if (strpos($v, 'this.') === 0)
					{
						$definition = str_replace('this.', '', $v);
						$absDefinition = $baseDefinition <> ''? $baseDefinition.'.'.$definition : $definition;

						$chain = $this->getRegisteredChain($absDefinition, true);

						if (!$isBackReference)
						{
							// make sure these fields will be joined before the main join
							$this->buildJoinMap(array($chain));
						}
						else
						{
							$chain->getLastElement()->setParameter('talias', $alias_this);
						}

						// recursively collect all "build_from" fields
						if ($chain->getLastElement()->getValue() instanceof ExpressionField)
						{
							$this->collectExprChains($chain);
							$buildFrom = $chain->getLastElement()->getValue()->getBuildFromChains();

							foreach ($buildFrom as $bf)
							{
								// set base chain
								$baseChain = clone $chain;

								// remove the last one - expression itself
								$baseChain->removeLastElement();

								// remove parent entity for this child
								$bf->removeFirstElement();

								// set new parents
								$bf->prepend($baseChain);
							}

							$this->buildJoinMap($buildFrom);
						}

						$field_def = $chain->getSqlDefinition();
					}
					elseif (strpos($v, 'ref.') === 0)
					{
						$definition = str_replace('ref.', '', $v);

						if (strpos($definition, '.') !== false)
						{
							throw new Main\ArgumentException(sprintf(
								'Reference chain `%s` is not allowed here. First-level definitions only.', $v
							));
						}

						$absDefinition = $refDefinition <> ''? $refDefinition.'.'.$definition : $definition;
						$chain = $this->getRegisteredChain($absDefinition, true);

						if ($isBackReference)
						{
							// make sure these fields will be joined before the main join
							$this->buildJoinMap(array($chain));
						}
						else
						{
							$chain->getLastElement()->setParameter('talias', $alias_ref);
						}

						$this->buildJoinMap(array($chain));

						// recursively collect all "build_from" fields
						if ($chain->getLastElement()->getValue() instanceof ExpressionField)
						{
							// here could be one more check "First-level definitions only" for buildFrom elements
							$buildFromChains = $this->collectExprChains($chain);

							// set same talias to buildFrom elements
							foreach ($buildFromChains as $buildFromChain)
							{
								if (!$isBackReference && $buildFromChain->getSize() > $chain->getSize())
								{
									throw new Main\ArgumentException(sprintf(
										'Reference chain `%s` is not allowed here. First-level definitions only.',
										$buildFromChain->getDefinition()
									));
								}

								if ($buildFromChain->getSize() === $chain->getSize())
								{
									// same entity, same table
									$buildFromChain->getLastElement()->setParameter('talias', $alias_ref);
								}
							}

							$this->buildJoinMap($buildFromChains);
						}

						$field_def = $chain->getSqlDefinition();
					}
					else
					{
						throw new Main\SystemException(sprintf('Unknown reference value `%s`', $v));
					}

					$v = new \CSQLWhereExpression($field_def);
				}
				else
				{
					throw new Main\SystemException(sprintf('Unknown reference value `%s`, it should start with "this." or "ref."', $v));
				}

				$new[$k] = $v;
			}
		}

		return $new;
	}

	/**
	 * @param Filter $reference
	 * @param        $alias_this
	 * @param        $alias_ref
	 * @param        $baseDefinition
	 * @param        $refDefinition
	 * @param        $isBackReference
	 * @param        $firstCall
	 *
	 * @return Filter
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	protected function prepareJoinFilterReference(Filter $reference, $alias_this, $alias_ref, $baseDefinition, $refDefinition, $isBackReference, $firstCall = true)
	{
		// do not make an impact on original reference object
		if ($firstCall)
		{
			$reference = clone $reference;
		}

		foreach ($reference->getConditions() as $condition)
		{
			if ($condition instanceof Filter)
			{
				// subfilter, recursive call
				$this->prepareJoinFilterReference(
					$condition,
					$alias_this,
					$alias_ref,
					$baseDefinition,
					$refDefinition,
					$isBackReference,
					false
				);
			}
			else
			{
				// regular condition
				$field = $condition->getDefinition();

				if (strpos($field, 'this.') === 0)
				{
					// parse the chain
					$definition = str_replace('this.', '', $field);
					$absDefinition = $baseDefinition <> ''? $baseDefinition.'.'.$definition : $definition;

					$chain = $this->getRegisteredChain($absDefinition, true);

					if (!$isBackReference)
					{
						// make sure these fields will be joined before the main join
						$this->buildJoinMap(array($chain));
					}
					else
					{
						$chain->getLastElement()->setParameter('talias', $alias_this);
					}

					// recursively collect all "build_from" fields
					if ($chain->getLastElement()->getValue() instanceof ExpressionField)
					{
						$this->collectExprChains($chain);
						$buildFrom = $chain->getLastElement()->getValue()->getBuildFromChains();

						foreach ($buildFrom as $bf)
						{
							// set base chain
							$baseChain = clone $chain;

							// remove the last one - expression itself
							$baseChain->removeLastElement();

							// remove parent entity for this child
							$bf->removeFirstElement();

							// set new parents
							$bf->prepend($baseChain);
						}

						$this->buildJoinMap($buildFrom);
					}

					$condition->setColumn($absDefinition);
				}
				elseif (strpos($field, 'ref.') === 0)
				{
					$definition = str_replace('ref.', '', $field);

					if (strpos($definition, '.') !== false)
					{
						throw new Main\ArgumentException(sprintf(
							'Reference chain `%s` is not allowed here. First-level definitions only.', $field
						));
					}

					$absDefinition = $refDefinition <> ''? $refDefinition.'.'.$definition : $definition;
					$chain = $this->getRegisteredChain($absDefinition, true);

					if ($isBackReference)
					{
						// make sure these fields will be joined before the main join
						$this->buildJoinMap(array($chain));
					}
					else
					{
						$chain->getLastElement()->setParameter('talias', $alias_ref);
					}

					// recursively collect all "build_from" fields
					if ($chain->getLastElement()->getValue() instanceof ExpressionField)
					{
						$this->collectExprChains($chain);
						$this->buildJoinMap($chain->getLastElement()->getValue()->getBuildFromChains());
					}

					$condition->setColumn($absDefinition);
				}
				else
				{
					throw new Main\SystemException(sprintf('Unknown reference key `%s`, it should start with "this." or "ref."', $field));
				}

				// value
				$v = $condition->getValue();

				if ($v instanceof Main\DB\SqlExpression)
				{
					// set entity connection
					$v->setConnection($this->entity->getConnection());
				}
				elseif ($v instanceof ColumnExpression)
				{
					if (strpos($v->getDefinition(), 'this.') === 0)
					{
						$definition = str_replace('this.', '', $v->getDefinition());
						$absDefinition = $baseDefinition <> ''? $baseDefinition.'.'.$definition : $definition;

						$chain = $this->getRegisteredChain($absDefinition, true);

						if (!$isBackReference)
						{
							// make sure these fields will be joined before the main join
							$this->buildJoinMap(array($chain));
						}
						else
						{
							$chain->getLastElement()->setParameter('talias', $alias_this);
						}

						// recursively collect all "build_from" fields
						if ($chain->getLastElement()->getValue() instanceof ExpressionField)
						{
							$this->collectExprChains($chain);
							$buildFrom = $chain->getLastElement()->getValue()->getBuildFromChains();

							foreach ($buildFrom as $bf)
							{
								// set base chain
								$baseChain = clone $chain;

								// remove the last one - expression itself
								$baseChain->removeLastElement();

								// remove parent entity for this child
								$bf->removeFirstElement();

								// set new parents
								$bf->prepend($baseChain);
							}

							$this->buildJoinMap($buildFrom);
						}

						$v->setDefinition($absDefinition);
					}
					elseif (strpos($v->getDefinition(), 'ref.') === 0)
					{
						$definition = str_replace('ref.', '', $v->getDefinition());

						if (strpos($definition, '.') !== false)
						{
							throw new Main\ArgumentException(sprintf(
								'Reference chain `%s` is not allowed here. First-level definitions only.', $v->getDefinition()
							));
						}

						$absDefinition = $refDefinition <> ''? $refDefinition.'.'.$definition : $definition;
						$chain = $this->getRegisteredChain($absDefinition, true);

						if ($isBackReference)
						{
							// make sure these fields will be joined before the main join
							$this->buildJoinMap(array($chain));
						}
						else
						{
							$chain->getLastElement()->setParameter('talias', $alias_ref);
						}

						$this->buildJoinMap(array($chain));

						// recursively collect all "build_from" fields
						if ($chain->getLastElement()->getValue() instanceof ExpressionField)
						{
							// here could be one more check "First-level definitions only" for buildFrom elements
							$buildFromChains = $this->collectExprChains($chain);

							// set same talias to buildFrom elements
							foreach ($buildFromChains as $buildFromChain)
							{
								if (!$isBackReference && $buildFromChain->getSize() > $chain->getSize())
								{
									throw new Main\ArgumentException(sprintf(
										'Reference chain `%s` is not allowed here. First-level definitions only.',
										$buildFromChain->getDefinition()
									));
								}

								if ($buildFromChain->getSize() === $chain->getSize())
								{
									// same entity, same table
									$buildFromChain->getLastElement()->setParameter('talias', $alias_ref);
								}
							}

							$this->buildJoinMap($buildFromChains);
						}

						$v->setDefinition($absDefinition);
					}
				}
			}
		}

		return $reference;
	}

	protected function getJoinCswFields($reference)
	{
		$fields = array();

		foreach ($reference as $k => $v)
		{
			if ($k === 'LOGIC')
			{
				continue;
			}

			if (is_numeric($k))
			{
				$fields = array_merge($fields, $this->getJoinCswFields($v));
			}
			else
			{
				// key
				$sqlWhere = new \CSQLWhere();
				$csw_result = $sqlWhere->makeOperation($k);
				list($field, ) = array_values($csw_result);

				$fields[$field] = array(
					'TABLE_ALIAS' => 'alias',
					'FIELD_NAME' => $field,
					'FIELD_TYPE' => 'string',
					'MULTIPLE' => '',
					'JOIN' => ''
				);

				// no need to add values as csw fields
			}
		}

		return $fields;
	}

	/**
	 * @param $chain
	 *
	 * @return bool
	 * @throws Main\SystemException
	 */
	protected function checkChainsAggregation($chain)
	{
		/** @var Chain[] $chains */
		$chains = is_array($chain) ? $chain : array($chain);

		foreach ($chains as $chain)
		{
			$last = $chain->getLastElement();
			$is_aggr = $last->getValue() instanceof ExpressionField && $last->getValue()->isAggregated();

			if ($is_aggr)
			{
				return true;
			}
		}

		return false;
	}

	protected function checkChainsDistinct($chain)
	{
		/** @var Chain[] $chains */
		$chains = is_array($chain) ? $chain : array($chain);

		foreach ($chains as $chain)
		{
			$field = $chain->getLastElement()->getValue();

			if ($field instanceof ExpressionField)
			{
				$expression = $field->getFullExpression();
				$expression = ExpressionField::removeSubqueries($expression);

				preg_match_all('/(?:^|[^a-z0-9_])(DISTINCT)[\s\(]+/i', $expression, $matches);

				if (!empty($matches[1]))
				{
					return true;
				}
			}
		}

		return false;
	}

	public function hasAggregation()
	{
		return !empty($this->group_chains) || !empty($this->having_chains)
			|| $this->checkChainsAggregation($this->select_chains)
			|| $this->checkChainsAggregation($this->order_chains);
	}

	public function setDistinct($distinct = true)
	{
		$this->is_distinct = (bool) $distinct;

		return $this;
	}

	public function hasDistinct()
	{
		$distinctInSelect = $this->checkChainsDistinct($this->select_chains);

		if ($distinctInSelect && $this->is_distinct)
		{
			// to avoid double distinct
			$this->is_distinct = false;
		}

		return ($distinctInSelect || $this->is_distinct);
	}

	/**
	 * The most magic method. Do not edit without strong need, and for sure run tests after.
	 *
	 * @param Chain $chain
	 * @param array $storages
	 *
	 * @return Chain[]
	 * @throws Main\SystemException
	 */
	protected function collectExprChains(Chain $chain, $storages = array('hidden'))
	{
		$last_elem = $chain->getLastElement();
		$bf_chains = $last_elem->getValue()->getBuildFromChains();

		$pre_chain = clone $chain;
		//$pre_chain->removeLastElement();
		$scopedBuildFrom = [];

		foreach ($bf_chains as $bf_chain)
		{
			// collect hidden chain
			$tmp_chain = clone $pre_chain;

			// exclude init entity
			/** @var ChainElement[] $bf_elements */
			$bf_elements = array_slice($bf_chain->getAllElements(), 1);

			// add elements
			foreach ($bf_elements as $bf_element)
			{
				$tmp_chain->addElement($bf_element);
			}

			//if (!($bf_chain->getLastElement()->getValue() instanceof ExpressionField))
			{
				foreach ($storages as $storage)
				{
					$reg_chain = $this->registerChain($storage, $tmp_chain);
				}

				// replace "build_from" chain end by registered chain end
				// actually it's better and more correctly to replace the whole chain
				$bf_chain->removeLastElement();
				/** @var Chain $reg_chain */
				$bf_chain->addElement($reg_chain->getLastElement());

				// return buildFrom elements with original start of chain for this query
				$scoped_bf_chain = clone $pre_chain;
				$scoped_bf_chain->removeLastElement();

				// copy tail from registered chain
				$tail = array_slice($reg_chain->getAllElements(), $pre_chain->getSize());

				foreach ($tail as $tailElement)
				{
					$scoped_bf_chain->addElement($tailElement);
				}

				$scopedBuildFrom[] = $scoped_bf_chain;
			}

			// check elements to recursive collect hidden chains
			foreach ($bf_elements as $bf_element)
			{
				if ($bf_element->getValue() instanceof ExpressionField)
				{
					$this->collectExprChains($tmp_chain);
				}
			}
		}

		return $scopedBuildFrom;
	}

	/**
	 * @return Union
	 * @throws Main\SystemException
	 */
	protected function getUnionHandler()
	{
		if ($this->unionHandler === null)
		{
			$this->unionHandler = new Union($this->entity->getConnection());
		}

		return $this->unionHandler;
	}

	public function registerChain($section, Chain $chain, $opt_key = null)
	{
		$alias = $chain->getAlias();

		if (isset($this->global_chains[$alias]))
		{
			if ($this->global_chains[$alias]->getDefinition() == $chain->getDefinition())
			{
				$reg_chain = $this->global_chains[$alias];
			}
			else
			{
				// we have a collision
				// like book.author_id and book.author.id have the same aliases, but different definitions
				// in most of the cases it's not a problem, there would be the same expected data
				// but we need register this chain separately to be available for internal usage
				$reg_chain = $chain;

				$this->global_chains[$reg_chain->getDefinition()] = $chain;

				// or should we make unique alias and register with it?
				$alias = $this->getUniqueAlias();
				$chain->setCustomAlias($alias);
				$this->global_chains[$alias] = $chain;
			}
		}
		else
		{
			$reg_chain = $chain;
			$def = $reg_chain->getDefinition();

			$this->global_chains[$alias] = $chain;
			$this->global_chains[$def] = $chain;
		}

		$storage_name = $section . '_chains';

		// in case of collision do not rewrite by alias
		if (!isset($this->{$storage_name}[$alias]))
		{
			$this->{$storage_name}[$alias] = $reg_chain;
			// should we store by definition too?
		}

		if (!is_null($opt_key))
		{
			$this->{$storage_name}[$opt_key] = $reg_chain;
		}

		return $reg_chain;
	}

	/**
	 * @param      $key
	 * @param bool $force_create
	 *
	 * @return Chain|bool
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function getRegisteredChain($key, $force_create = false)
	{
		if (isset($this->global_chains[$key]))
		{
			return $this->global_chains[$key];
		}

		if ($force_create)
		{
			$chain = Chain::getChainByDefinition($this->entity, $key);
			$this->registerChain('global', $chain);

			return $chain;
		}

		return false;
	}

	protected function getUniqueAlias()
	{
		return 'UALIAS_'.($this->uniqueAliasCounter++);
	}

	public function booleanStrongEqualityCallback($field, $operation, $value)
	{
		$value = ($operation == 'SE') ? $value : !$value;
		return ($value ? '' : 'NOT ') . $field;
	}

	public function nullEqualityCallback($field, $operation, /** @noinspection PhpUnusedParameterInspection */ $value)
	{
		return $field.' IS '.($operation == 'SE' ? '' : 'NOT ') . 'NULL';
	}

	public function dataDoublingCallback($field, /** @noinspection PhpUnusedParameterInspection */ $operation, $value)
	{
		return $field.' IN ('.$value.')';
	}

	/**
	 * @param $query
	 *
	 * @return Main\DB\ArrayResult|Main\DB\Result
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	protected function query($query)
	{
		// check nosql configuration
		$connection = $this->entity->getConnection();
		$configuration = $connection->getConfiguration();

		/** @var Main\DB\Result $result */
		$result = null;

		if (isset($configuration['handlersocket']['read']))
		{
			// optimize through nosql
			$nosqlConnectionName = $configuration['handlersocket']['read'];

			$nosqlConnection = Main\Application::getInstance()->getConnectionPool()->getConnection($nosqlConnectionName);
			$isNosqlCapable = NosqlPrimarySelector::checkQuery($nosqlConnection, $this);

			if ($isNosqlCapable)
			{
				$nosqlResult = NosqlPrimarySelector::relayQuery($nosqlConnection, $this);
				$result = new Main\DB\ArrayResult($nosqlResult);

				// add data converters
				if (!empty($nosqlResult))
				{
					/** @var callable[] $converters */
					$converters = [];

					foreach ($this->getSelectChains() as $selectChain)
					{
						$field = $selectChain->getLastElement()->getValue();

						if ($field instanceof ScalarField)
						{
							$converter = $connection->getSqlHelper()->getConverter($field);

							if (is_callable($converter))
							{
								$converter[$selectChain->getAlias()] = $converter;
							}
						}
					}

					if (!empty($converters))
					{
						$result->setConverters($converters);
					}
				}
			}
		}

		if ($result === null)
		{
			// regular SQL query
			$result = $connection->query($query);
			$result->setReplacedAliases($this->replaced_aliases);

			if($this->countTotal)
			{
				if ($this->limit && ($result->getSelectedRowsCount() < $this->limit))
				{
					// optimization for first and last pages
					$result->setCount((int) $this->offset + $result->getSelectedRowsCount());
				}
				elseif (empty($this->limit))
				{
					// optimization for queries without limit
					$result->setCount($result->getSelectedRowsCount());
				}
				else
				{
					// dedicated query
					$result->setCount($this->queryCountTotal());
				}
			}

			static::$last_query = $query;
		}

		if ($this->isFetchModificationRequired())
		{
			$result->addFetchDataModifier(array($this, 'fetchDataModificationCallback'));
		}

		return $result;
	}

	public function queryCountTotal()
	{
		if ($this->query_build_parts === null)
		{
			$this->buildQuery();
		}

		$buildParts = $this->query_build_parts;

		//remove order
		unset($buildParts['ORDER BY']);

		//remove select
		$buildParts['SELECT'] = "1 cntholder";

		foreach ($buildParts as $k => &$v)
		{
			$v = $k . ' ' . $v;
		}

		$cntQuery = join("\n", $buildParts);

		// select count
		$cntQuery = /** @lang text */
			"SELECT COUNT(cntholder) AS TMP_ROWS_CNT FROM ({$cntQuery}) xxx";

		return $this->entity->getConnection()->queryScalar($cntQuery);
	}

	/**
	 * Being called in Db\Result as a data fetch modifier
	 * @param $data
	 */
	public function fetchDataModificationCallback(&$data)
	{
		// entity-defined callbacks
		foreach ($this->selectFetchModifiers as $alias => $modifiers)
		{
			foreach ($modifiers as $modifier)
			{
				$data[$alias] = call_user_func_array($modifier, array($data[$alias], $this, $data, $alias));
			}
		}
	}

	/**
	 * Check if fetch data modification required, also caches modifier-callbacks
	 * @return bool
	 * @throws Main\SystemException
	 */
	public function isFetchModificationRequired()
	{
		$this->selectFetchModifiers = array();

		foreach ($this->select_chains as $chain)
		{
			if ($chain->getLastElement()->getValue()->getFetchDataModifiers())
			{
				$this->selectFetchModifiers[$chain->getAlias()] = $chain->getLastElement()->getValue()->getFetchDataModifiers();
			}
		}

		return !empty($this->selectFetchModifiers) || !empty($this->files);
	}

	/**
	 * @deprecated
	 * @param $query
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	protected function replaceSelectAliases($query)
	{
		$connection = $this->entity->getConnection();
		$helper = $connection->getSqlHelper();

		$length = (int) $helper->getAliasLength();
		$leftQuote = $helper->getLeftQuote();
		$rightQuote = $helper->getRightQuote();

		$replaced = array();

		preg_match_all(
			'/ AS '.preg_quote($leftQuote).'([a-z0-9_]{'.($length+1).',})'.preg_quote($rightQuote).'/i',
			$query, $matches
		);

		if (!empty($matches[1]))
		{
			foreach ($matches[1] as $alias)
			{
				$newAlias = 'FALIAS_'.count($replaced);
				$replaced[$newAlias] = $alias;

				$query = str_replace(
					' AS ' . $helper->quote($alias),
					' AS ' . $helper->quote($newAlias) . '/* '.$alias.' */',
					$query
				);
			}
		}

		return array($query, $replaced);
	}

	/**
	 * @param $source
	 *
	 * @return string
	 * @throws Main\SystemException
	 */
	public function quoteTableSource($source)
	{
		// don't quote subqueries
		if (!preg_match('/\s*\(\s*SELECT.*\)\s*/is', $source))
		{
			$source =  $this->entity->getConnection()->getSqlHelper()->quote($source);
		}

		return $source;
	}

	public function __clone()
	{
		$this->entity = clone $this->entity;

		$this->filterHandler = clone $this->filterHandler;
		$this->whereHandler = clone $this->whereHandler;
		$this->havingHandler = clone $this->havingHandler;

		foreach ($this->select as $k => $v)
		{
			if ($v instanceof ExpressionField)
			{
				$this->select[$k] = clone $v;
			}
		}
	}

	/**
	 * @return bool
	 * @throws Main\SystemException
	 */
	public function hasBackReference()
	{
		if (empty($this->global_chains))
		{
			throw new Main\SystemException('Query has not been executed or built');
		}

		foreach ($this->global_chains as $chain)
		{
			if ($chain->hasBackReference())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array|Chain[]
	 */
	public function getChains()
	{
		return $this->global_chains;
	}

	/**
	 * @return array|Chain[]
	 */
	public function getGroupChains()
	{
		return $this->group_chains;
	}

	/**
	 * @return array
	 */
	public function getHiddenChains()
	{
		return $this->hidden_chains;
	}

	/**
	 * @return array|Chain[]
	 */
	public function getHavingChains()
	{
		return $this->having_chains;
	}

	/**
	 * @return array|Chain[]
	 */
	public function getFilterChains()
	{
		return $this->filter_chains;
	}

	/**
	 * @return array|Chain[]
	 */
	public function getOrderChains()
	{
		return $this->order_chains;
	}

	/**
	 * @return array|Chain[]
	 */
	public function getSelectChains()
	{
		return $this->select_chains;
	}

	/**
	 * @return array|Chain[]
	 */
	public function getWhereChains()
	{
		return $this->where_chains;
	}

	/**
	 * @return Chain[]
	 */
	public function getRuntimeChains()
	{
		return $this->runtime_chains;
	}

	public function getJoinMap()
	{
		return $this->join_map;
	}

	/**
	 * Builds and returns SQL query string
	 *
	 * @param bool $forceObjectPrimary
	 *
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function getQuery($forceObjectPrimary = false)
	{
		return $this->buildQuery($forceObjectPrimary);
	}

	/**
	 * Returns last executed query string
	 *
	 * @return string
	 */
	public static function getLastQuery()
	{
		return static::$last_query;
	}

	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * Builds SQL filter conditions for WHERE.
	 * Useful for external calls: building SQL for mass UPDATEs or DELETEs
	 *
	 * @param Entity       $entity
	 * @param array|Filter $filter the same format as for setFilter/where
	 *
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function buildFilterSql(Entity $entity, $filter)
	{
		$query = new static($entity);

		if ($filter instanceof Filter)
		{
			// new object filter
			$query->where($filter);
		}
		else
		{
			// old array filter
			$query->setFilter($filter);
		}

		$query->setCustomBaseTableAlias($entity->getDBTableName())->buildQuery();

		return $query->query_build_parts['WHERE'];
	}

	/**
	 * @param bool $withPostfix
	 *
	 * @return string
	 * @throws Main\SystemException
	 */
	public function getInitAlias($withPostfix = true)
	{
		if ($this->custom_base_table_alias !== null)
		{
			return $this->custom_base_table_alias;
		}

		$init_alias = strtolower($this->entity->getCode());

		// add postfix
		if ($withPostfix)
		{
			$init_alias .= $this->table_alias_postfix;
		}

		// check length
		$connection = $this->entity->getConnection();
		$aliasLength = $connection->getSqlHelper()->getAliasLength();

		if (strlen($init_alias) > $aliasLength)
		{
			$init_alias = 'base';

			// add postfix
			if ($withPostfix)
			{
				$init_alias .= $this->table_alias_postfix;
			}
		}

		return $init_alias;
	}

	public function getReplacedAliases()
	{
		return $this->replaced_aliases;
	}

	/*
	 * Sets cache TTL in seconds.
	 * @param int $ttl
	 * @return $this
	 */
	public function setCacheTtl($ttl)
	{
		$this->cacheTtl = (int)$ttl;
		return $this;
	}

	/**
	 * Enables or disables caching of queries with joins.
	 * @param bool $mode
	 * @return $this
	 */
	public function cacheJoins($mode)
	{
		$this->cacheJoins = (bool)$mode;
		return $this;
	}

	public function dump()
	{
		echo '<pre>';

		echo 'last query: ';
		var_dump(static::$last_query);
		echo PHP_EOL;

		echo 'size of select_chains: '.count($this->select_chains);
		echo PHP_EOL;
		foreach ($this->select_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		echo 'size of where_chains: '.count($this->where_chains);
		echo PHP_EOL;
		foreach ($this->where_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		echo 'size of group_chains: '.count($this->group_chains);
		echo PHP_EOL;
		foreach ($this->group_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		echo 'size of having_chains: '.count($this->having_chains);
		echo PHP_EOL;
		foreach ($this->having_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		echo 'size of filter_chains: '.count($this->filter_chains);
		echo PHP_EOL;
		foreach ($this->filter_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		echo 'size of select_expr_chains: '.count($this->select_expr_chains);
		echo PHP_EOL;
		foreach ($this->select_expr_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		echo 'size of hidden_chains: '.count($this->hidden_chains);
		echo PHP_EOL;
		foreach ($this->hidden_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		echo 'size of global_chains: '.count($this->global_chains);
		echo PHP_EOL;
		foreach ($this->global_chains as $num => $chain)
		{
			echo '  chain ['.$num.'] has '.$chain->getSize().' elements: '.PHP_EOL;
			$chain->dump();
			echo PHP_EOL;
		}

		echo PHP_EOL.PHP_EOL;

		var_dump($this->join_map);

		echo '</pre>';
	}
}
