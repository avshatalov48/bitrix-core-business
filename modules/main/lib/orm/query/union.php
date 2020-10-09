<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;

/**
 * UNION handler for Query.
 *
 * @package    bitrix
 * @subpackage main
 */
class Union
{
	/** @var UnionCondition[] */
	protected $queries;

	/** @var array */
	protected $order;

	/** @var int */
	protected $limit;

	/** @var int */
	protected $offset;

	/** @var Connection */
	protected $connection;

	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @param UnionCondition $query
	 *
	 * @return $this
	 */
	public function addQuery(UnionCondition $query)
	{
		$this->queries[] = $query;
		return $this;
	}

	/**
	 * @return UnionCondition[]
	 */
	public function getQueries()
	{
		return $this->queries;
	}

	/**
	 * @param mixed $order
	 *
	 * @return $this
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
	 * @param string $definition
	 * @param string $order
	 *
	 * @return $this
	 * @throws ArgumentException
	 */
	public function addOrder($definition, $order = 'ASC')
	{
		$order = mb_strtoupper($order);

		if (!in_array($order, array('ASC', 'DESC'), true))
		{
			throw new ArgumentException(sprintf('Invalid order "%s"', $order));
		}

		$helper = $this->connection->getSqlHelper();

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
	 * @return array
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param $offset
	 *
	 * @return $this
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}
}
