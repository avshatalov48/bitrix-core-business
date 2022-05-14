<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Query\Chain;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\SystemException;

/**
 * Expr field is to describe dynamic fields by expression, e.g. we have PRICE_USD field and need to count price in EUR
 * then we define expression field PRICE_EUR with expression = array('%s * 1.25', 'PRICE_USD')
 * @package bitrix
 * @subpackage main
 */
class ExpressionField extends Field implements IReadable
{
	/**
	 * @var string
	 */
	protected $expression;

	/**
	 * Full expression, recursively includes expressions from buildFrom fields
	 * @var string
	 */
	protected $fullExpression;

	/** @var string Scalar field class name */
	protected $valueType;

	/**
	 * @var ScalarField
	 */
	protected $valueField;

	/**
	 * @var array
	 */
	protected $buildFrom;

	/** @var \Bitrix\Main\ORM\Query\Chain[] */
	protected $buildFromChains;

	protected $isAggregated;

	protected $hasSubquery;

	protected static
		$aggrFunctionsMYSQL = array('AVG', 'BIT_AND', 'BIT_OR', 'BIT_XOR', 'COUNT',
			'GROUP_CONCAT', 'MAX', 'MIN', 'STD', 'STDDEV_POP', 'STDDEV_SAMP',
			'STDDEV', 'SUM', 'VAR_POP', 'VAR_SAMP', 'VARIANCE'
		),
		$aggrFunctionsMSSQL = array('AVG', 'MIN', 'CHECKSUM_AGG', 'OVER', 'COUNT',
			'ROWCOUNT_BIG', 'COUNT_BIG', 'STDEV', 'GROUPING', 'STDEVP',
			'GROUPING_ID', 'SUM', 'MAX', 'VAR', 'VARP'
		),
		$aggrFunctionsORACLE = array('AVG', 'COLLECT', 'CORR', 'CORR_S', 'CORR_K',
			'COUNT', 'COVAR_POP', 'COVAR_SAMP', 'CUME_DIST', 'DENSE_RANK', 'FIRST',
			'GROUP_ID', 'GROUPING', 'GROUPING_ID', 'LAST', 'MAX', 'MEDIAN', 'MIN',
			'PERCENTILE_CONT', 'PERCENTILE_DISC', 'PERCENT_RANK', 'RANK',
			'REGR_SLOPE', 'REGR_INTERCEPT', 'REGR_COUNT', 'REGR_R2', 'REGR_AVGX',
			'REGR_AVGY', 'REGR_SXX', 'REGR_SYY', 'REGR_SXY', 'STATS_BINOMIAL_TEST',
			'STATS_CROSSTAB', 'STATS_F_TEST', 'STATS_KS_TEST', 'STATS_MODE',
			'STATS_MW_TEST', 'STATS_ONE_WAY_ANOVA', 'STATS_T_TEST_ONE',
			'STATS_T_TEST_PAIRED', 'STATS_T_TEST_INDEP', 'STATS_T_TEST_INDEPU',
			'STATS_WSR_TEST', 'STDDEV', 'STDDEV_POP', 'STDDEV_SAMP', 'SUM',
			'VAR_POP', 'VAR_SAMP', 'VARIANCE'
		),
		$aggrFunctions;

	/**
	 * All fields in expression should be placed as %s (or as another placeholder for sprintf),
	 * and the real field names being carrying in $buildFrom array (= args for sprintf)
	 *
	 * @param string            $name
	 * @param string            $expression
	 * @param array|string|null $buildFrom
	 * @param array             $parameters deprecated, use configure* and add* instead
	 *
	 * @throws SystemException
	 */
	public function __construct($name, $expression, $buildFrom = null, $parameters = array())
	{
		if (!isset($parameters['data_type']))
		{
			$parameters['data_type'] = 'string'; // deprecated

			$this->valueType = StringField::class;
		}

		parent::__construct($name, $parameters);

		$this->expression = $expression;

		if (!is_array($buildFrom) && $buildFrom !== null)
		{
			$buildFrom = array($buildFrom);
		}
		elseif ($buildFrom === null)
		{
			$buildFrom = array();
		}

		$this->buildFrom = $buildFrom;
	}

	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->valueField, $name), $arguments);
	}

	/**
	 * @return mixed
	 */
	public function getTypeMask()
	{
		return FieldTypeMask::EXPRESSION;
	}

	/**
	 * @param $class
	 *
	 * @return $this
	 */
	public function configureValueType($class)
	{
		$this->valueType = $class;
		return $this;
	}

	/**
	 * @param ScalarField $field
	 * @return $this
	 */
	public function configureValueField($field)
	{
		$this->valueField = $field;
		$this->valueType = get_class($field);

		return $this;
	}

	/**
	 * @param Entity $entity
	 *
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function setEntity(Entity $entity)
	{
		parent::setEntity($entity);

		$parameters = $this->initialParameters;
		unset($parameters['expression']);

		if ($this->valueType !== null)
		{
			if ($this->valueField === null)
			{
				/** @var ScalarField $valueField */
				$valueField = new $this->valueType($this->name, $parameters);
				$this->valueField = $this->entity->initializeField($this->name, $valueField);
			}
		}
		else
		{
			// deprecated - old format with parameters and data_type
			$this->valueField = $this->entity->initializeField($this->name, $parameters);
			$this->valueType = get_class($this->valueField);
		}

		if (!($this->valueField instanceof ScalarField))
		{
			throw new SystemException('expression field can only be a scalar type.');
		}
	}

	public function getExpression()
	{
		return $this->expression;
	}

	/**
	 * @return array
	 */
	public function getBuildFrom()
	{
		return $this->buildFrom;
	}

	/**
	 * @return mixed|string
	 * @throws SystemException
	 */
	public function getFullExpression()
	{
		if (!isset($this->fullExpression))
		{
			$SQLBuildFrom = array();
			$buildFromChains = $this->getBuildFromChains();

			foreach ($this->buildFrom as $element)
			{
				if ($element instanceof \Closure)
				{
					/** @var SqlExpression $sqlExpression */
					// no need to get real value. also it may [] to false positive check in hasAggregation or hasSubquery
					//$sqlExpression = $element();
					//$SQLBuildFrom[] = $sqlExpression->compile();
					$SQLBuildFrom[] = '';
				}
				else
				{
					$chain = array_shift($buildFromChains);

					if ($chain->getLastElement()->getValue() instanceof ExpressionField)
					{
						$SQLBuildFrom[] = $chain->getLastElement()->getValue()->getFullExpression();
					}
					else
					{
						$SQLBuildFrom[] = '%s';
					}
				}
			}

			$this->fullExpression = call_user_func_array('sprintf', array_merge(array($this->expression), $SQLBuildFrom));
		}

		return $this->fullExpression;
	}

	/**
	 * @return bool
	 * @throws SystemException
	 */
	public function isAggregated()
	{
		if (!isset($this->isAggregated))
		{
			$this->isAggregated = (bool) self::checkAggregation($this->getFullExpression());
		}

		return $this->isAggregated;
	}

	/**
	 * @return bool
	 * @throws SystemException
	 */
	public function hasSubquery()
	{
		if (!isset($this->hasSubquery))
		{
			$this->hasSubquery = (bool) self::checkSubquery($this->getFullExpression());
		}

		return $this->hasSubquery;
	}

	public function isConstant()
	{
		return empty($this->buildFrom);
	}

	/**
	 * @return \Bitrix\Main\ORM\Query\Chain[]
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getBuildFromChains()
	{
		if (is_null($this->buildFromChains))
		{
			$this->buildFromChains = array();

			foreach ($this->buildFrom as $elem)
			{
				if (!($elem instanceof \Closure))
				{
					// validate if build from scalar or expression
					$chain = Chain::getChainByDefinition($this->entity, $elem);
					$field = $chain->getLastElement()->getValue();

					if ($field instanceof ScalarField || $field instanceof ExpressionField)
					{
						$this->buildFromChains[] = $chain;
					}
					else
					{
						throw new SystemException(sprintf(
							'Expected ScalarField or ExpressionField in `%s` build_from, but `%s` was given.',
							$this->name, is_object($field) ? get_class($field).':'.$field->getName() : gettype($field)
						));
					}
				}
			}
		}

		return $this->buildFromChains;
	}

	public static function checkAggregation($expression)
	{
		if (empty(self::$aggrFunctions))
		{
			self::$aggrFunctions = array_unique(array_merge(
				self::$aggrFunctionsMYSQL, self::$aggrFunctionsMSSQL, self::$aggrFunctionsORACLE
			));
		}

		// should remove subqueries from expression here: EXISTS(..(..)..), (SELECT ..(..)..)
		$expression = static::removeSubqueries($expression);

		// then check for aggr functions
		preg_match_all('/(?:^|[^a-z0-9_])('.join('|', self::$aggrFunctions).')[\s\(]+/i', $expression, $matches);

		return isset($matches[1]) ? $matches[1] : null;
	}

	public static function checkSubquery($expression)
	{
		return (preg_match('/(?:^|[^a-zA-Z0-9_])EXISTS\s*\(/i', $expression) || preg_match('/(?:^|[^a-zA_Z0-9_])\(\s*SELECT/i', $expression));
	}

	public static function removeSubqueries($expression)
	{
		// remove double slashes
		$expression = str_replace('\\\\\\\\', '', $expression);

		// remove strings
		$expression = static::removeStrings('"', $expression);
		$expression = static::removeStrings("'", $expression);

		// remove subqueries' bodies
		$clear = static::removeSubqueryBody($expression);

		while ($clear !== $expression)
		{
			$expression = $clear;
			$clear = static::removeSubqueryBody($expression);
		}

		return $clear;
	}

	protected static function removeStrings($quote, $expression)
	{
		// remove escaped quotes
		$expression = str_replace('\\' . $quote, '', $expression);

		// remove quoted strings
		$expression = preg_replace('/' . $quote . '.*?' . $quote . '/', '', $expression);

		return $expression;
	}

	protected static function removeSubqueryBody($query)
	{
		$subqPattern = '\(\s*SELECT\s+';

		$matches = null;
		preg_match('/' . $subqPattern . '/i', $query, $matches);

		if (!empty($matches))
		{
			$substring = $matches[0];

			$subqPosition = mb_strpos($query, $substring);
			$subqStartPosition = $subqPosition + mb_strlen($substring);

			$bracketsCount = 1;
			$currentPosition = $subqStartPosition;

			// until initial bracket is closed
			while ($bracketsCount > 0)
			{
				$symbol = mb_substr($query, $currentPosition, 1);

				if ($symbol == '')
				{
					// end of string
					break;
				}

				if ($symbol == '(')
				{
					$bracketsCount++;
				}
				elseif ($symbol == ')')
				{
					$bracketsCount--;
				}

				$currentPosition++;
			}

			$query = mb_substr($query, 0, $subqPosition).mb_substr($query, $currentPosition);
		}

		return $query;
	}

	/**
	 * @deprecated
	 * @return null|string
	 */
	public function getDataType()
	{
		return $this->valueField->getDataType();
	}

	/**
	 * @return string
	 */
	public function getValueType()
	{
		return $this->valueType;
	}

	/**
	 * @return ScalarField
	 */
	public function getValueField()
	{
		return $this->valueField;
	}

	public function __clone()
	{
		$this->buildFromChains = null;
		$this->fullExpression = null;
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function cast($value)
	{
		$valueField = $this->valueField;
		return $valueField->cast($value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function convertValueFromDb($value)
	{
		$valueField = $this->valueField;
		return $valueField->convertValueFromDb($value);
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function convertValueToDb($value)
	{
		/** @var IStorable $valueField */
		$valueField = $this->valueField;
		return $valueField->convertValueToDb($value);
	}

	public function validateValue($value, $primary, $row, Result $result)
	{
		throw new NotImplementedException;
	}
}


