<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;

/**
 * UNION container to be used in Query.
 *
 * @package    bitrix
 * @subpackage main
 */
class UnionCondition
{
	/** @var Query|SqlExpression */
	protected $subQuery;

	/** @var bool */
	protected $all;

	/**
	 * @param Query|SqlExpression $subQuery
	 * @param bool                $unionAll
	 *
	 * @throws ArgumentException
	 */
	public function __construct($subQuery, $unionAll = false)
	{
		if (!($subQuery instanceof Query) && !($subQuery instanceof SqlExpression))
		{
			throw new ArgumentException("Query or SqlExpression expected, `".gettype($subQuery)."` found.");
		}

		$this->subQuery = $subQuery;
		$this->all = $unionAll;
	}

	/**
	 * @return SqlExpression|Query
	 */
	public function getSubQuery()
	{
		return $this->subQuery;
	}

	/**
	 * @param SqlExpression|Query $subQuery
	 */
	public function setSubQuery($subQuery)
	{
		$this->subQuery = $subQuery;
	}

	/**
	 * @return bool
	 */
	public function isAll()
	{
		return $this->all;
	}

	/**
	 * @param bool $all
	 */
	public function setAll($all)
	{
		$this->all = $all;
	}

	/**
	 * @param bool $forceObjectPrimary
	 *
	 * @return string
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getSql($forceObjectPrimary = false)
	{
		$sql = "UNION ";

		if ($this->all)
		{
			$sql .= "ALL ";
		}

		return $sql."({$this->getSubQuerySql($forceObjectPrimary)})";
	}

	/**
	 * @param bool $forceObjectPrimary
	 *
	 * @return string
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getSubQuerySql($forceObjectPrimary = false)
	{
		if ($this->subQuery instanceof Query)
		{
			return $this->subQuery->getQuery($forceObjectPrimary);
		}
		elseif ($this->subQuery instanceof SqlExpression)
		{
			return $this->subQuery->compile();
		}

		return null;
	}
}
