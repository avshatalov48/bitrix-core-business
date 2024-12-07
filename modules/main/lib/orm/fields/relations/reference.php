<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Fields\Relations;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\ORM\Query\Filter\ConditionTree as Filter;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

/**
 * Reference field describes relation 1-to-1 or 1-to-many between two entities
 * @package bitrix
 * @subpackage main
 */
class Reference extends Relation
{
	/** @var array|Filter */
	protected $reference;

	protected $joinType = Join::TYPE_LEFT;

	protected $cascadeSavePolicy = CascadePolicy::NO_ACTION;

	protected $cascadeDeletePolicy = CascadePolicy::NO_ACTION; // follow | no_action

	const ELEMENTAL_THIS = 1;
	const ELEMENTAL_REF = 2;
	const ELEMENTAL_BOTH = 3;

	/**
	 * @param string        $name
	 * @param string|Entity $referenceEntity
	 * @param array|Filter  $referenceFilter
	 * @param array         $parameters deprecated, use configure* and add* methods instead
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function __construct($name, $referenceEntity, $referenceFilter, $parameters = array())
	{
		parent::__construct($name);

		if ($referenceEntity instanceof Entity)
		{
			$this->refEntity = $referenceEntity;
			$this->refEntityName = $referenceEntity->getFullName();
		}
		else
		{
			// this one could be without leading backslash and/or with Table-postfix
			$this->refEntityName = Entity::normalizeName($referenceEntity);
		}

		if (empty($referenceFilter))
		{
			throw new ArgumentException('Reference for `'.$name.'` shouldn\'t be empty');
		}

		$this->reference = $referenceFilter;

		if (isset($parameters['join_type']))
		{
			$join_type = strtoupper($parameters['join_type']);

			if (in_array($join_type, Join::getTypes(), true))
			{
				$this->joinType = $join_type;
			}
		}
	}

	public function getTypeMask()
	{
		return FieldTypeMask::REFERENCE;
	}

	/**
	 * @param        $value
	 * @param        $primary
	 * @param        $row
	 * @param Result $result
	 *
	 * @return Result
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function validateValue($value, $primary, $row, Result $result)
	{
		$remoteObjectClass = $this->getRefEntity()->getObjectClass();

		if ($value !== null && !($value instanceof $remoteObjectClass))
		{
			$result->addError(new Error(sprintf(
				'Expected instance of `%s`, got `%s` instead', $remoteObjectClass, get_class($value)
			)));
		}

		return parent::validateValue($value, $primary, $row, $result);
	}

	public function getDataType()
	{
		return $this->refEntityName;
	}

	public function getReference()
	{
		return $this->reference;
	}

	/**
	 * Returns set of strictly linked fields of this and ref entities [localFieldName => remoteFieldName]
	 *
	 * @return array|bool
	 */
	public function getElementals()
	{
		if (!($this->reference instanceof Filter))
		{
			return false;
		}

		$elemental = [];

		foreach ($this->reference->getConditions() as $condition)
		{
			if (!($condition->getValue() instanceof ColumnExpression)
				|| $condition->getOperator() != '='
			)
			{
				continue;
			}

			// ok, we have a column filter. one should be `this.` and another one `ref.`
			$col1 = $condition->getColumn();
			$col2 = $condition->getValue()->getDefinition();

			$col1Flag = static::getElementalFlag($col1);
			$col2Flag = static::getElementalFlag($col2);

			if (($col1Flag + $col2Flag) == static::ELEMENTAL_BOTH)
			{
				// we have this and ref link
				$key = ($col1Flag == static::ELEMENTAL_THIS) ? $col1 : $col2;
				$value = ($col1Flag == static::ELEMENTAL_REF) ? $col1 : $col2;

				// cut .this and .ref from the start of definitions
				$key = substr($key, 5);
				$value = substr($value, 4);

				$elemental[$key] = $value;
			}
		}

		return $elemental;
	}

	protected static function getElementalFlag($definition)
	{
		if (substr_count($definition, '.') == 1)
		{
			if (str_starts_with($definition, 'this.'))
			{
				return static::ELEMENTAL_THIS;
			}
			elseif (str_starts_with($definition, 'ref.'))
			{
				return static::ELEMENTAL_REF;
			}
		}

		return 0;
	}
}


