<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\Security\Random;

/**
 * Short calls for runtime fields.
 * @see ConditionTree::where()
 *
 * @package    bitrix
 * @subpackage main
 */
class Expression
{
	/** @var ?string Name for expression */
	public $alias;

	public function setAlias(?string $alias): static
	{
		$this->alias = $alias;
		return $this;
	}

	/**
	 * Expression with COUNT(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function count($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('COUNT');
		return new ExpressionField($alias, 'COUNT(%s)', $columnName);
	}

	/**
	 * Expression with COUNT(DISTINCT ...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function countDistinct($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('COUNT_DISTINCT');
		return new ExpressionField($alias, 'COUNT(DISTINCT %s)', $columnName);
	}

	/**
	 * Expression with SUM(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function sum($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('SUM');
		return new ExpressionField($alias, 'SUM(%s)', $columnName);
	}

	/**
	 * Expression with MIN(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function min($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('MIN');
		return new ExpressionField($alias, 'MIN(%s)', $columnName);
	}

	/**
	 * Expression with AVG(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function avg($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('AVG');
		return new ExpressionField($alias, 'AVG(%s)', $columnName);
	}

	/**
	 * Expression with MAX(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function max($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('MAX');
		return new ExpressionField($alias, 'MAX(%s)', $columnName);
	}

	/**
	 * Expression with LENGTH(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function length($columnName)
	{
		$helper = Application::getConnection()->getSqlHelper();
		$alias = $this->alias ?: static::getTmpName('LENGTH');

		return new ExpressionField($alias, $helper->getLengthFunction('%s'), $columnName);
	}

	/**
	 * Expression with LOWER(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function lower($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('LOWER');
		return new ExpressionField($alias, 'LOWER(%s)', $columnName);
	}

	/**
	 * Expression with UPPER(...) function.
	 *
	 * @param $columnName
	 *
	 * @return ExpressionField
	 */
	public function upper($columnName)
	{
		$alias = $this->alias ?: static::getTmpName('UPPER');
		return new ExpressionField($alias, 'UPPER(%s)', $columnName);
	}

	/**
	 * Expression with CONCAT(...) function.
	 *
	 * @param array $columns
	 *
	 * @return ExpressionField
	 */
	public function concat()
	{
		$helper = Application::getConnection()->getSqlHelper();
		$columns = func_get_args();
		$alias = $this->alias ?: static::getTmpName('CONCAT');

		// get ... format as well as single array
		if (count($columns) == 1 && is_array($columns[0]))
		{
			$columns = $columns[0];
		}

		$holders = array_fill(0, count($columns), '%s');
		$expr = call_user_func_array([$helper, 'getConcatFunction'], $holders);

		return new ExpressionField($alias, $expr, $columns);
	}

	/**
	 * Random name for Expression. Real alias is expected in Query when set the Expression.
	 *
	 * @param $postfix
	 *
	 * @return string
	 */
	protected static function getTmpName($postfix)
	{
		return 'A'.strtoupper(Random::getString(6).'_'.$postfix);
	}
}
