<?php

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\Relation;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\SystemException;

class ChainElement
{
	/** @var array|Entity|Field|\Bitrix\Main\ORM\Fields\Relations\Reference|ScalarField|Relation */
	protected $value;

	protected $parameters;

	protected $type;

	protected $definition_fragment;

	protected $alias_fragment;

	/**
	 * Value format:
	 * 1. Field - normal scalar field
	 * 2. Reference - pointer to another entity
	 * 3. array(Base, Reference) - pointer from another entity to this
	 * 4. Base - all fields of entity
	 *
	 * @param Field|array|Entity $element
	 * @param array              $parameters
	 *
	 * @throws SystemException
	 */
	public function __construct($element, $parameters = array())
	{
		if ($element instanceof Reference)
		{
			$this->type = 2;
		}
		elseif (is_array($element)
			&& $element[0] instanceof Entity
			&& $element[1] instanceof Reference
		)
		{
			$this->type = 3;
		}
		elseif ($element instanceof Entity)
		{
			$this->type = 4;
		}
		elseif ($element instanceof Field)
		{
			$this->type = 1;
		}
		else
		{
			throw new SystemException(sprintf('Invalid value for QueryChainElement: %s.', $element));
		}

		$this->value = $element;
		$this->parameters = $parameters;
	}

	/**
	 * @return array|Entity|ExpressionField|\Bitrix\Main\ORM\Fields\Relations\Reference|ScalarField|Relation|OneToMany|ManyToMany
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function getParameter($name)
	{
		if (array_key_exists($name, $this->parameters))
		{
			return $this->parameters[$name];
		}

		return null;
	}

	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	 * @return string
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public function getDefinitionFragment()
	{
		if (is_null($this->definition_fragment))
		{
			if ($this->type == 2)
			{
				// skip uts entity
				if ($this->value->getRefEntity()->isUts())
				{
					$this->definition_fragment = '';
				}
				else
				{
					$this->definition_fragment = $this->value->getName();
				}
			}
			elseif ($this->type == 3)
			{
				// skip utm entity
				if ($this->value[0]->isUtm())
				{
					$this->definition_fragment = '';
				}
				else
				{
					$this->definition_fragment = $this->value[0]->getFullName()	. ':' . $this->value[1]->getName();
				}
			}
			elseif ($this->type == 4)
			{
				$this->definition_fragment = '*';
			}
			else
			{
				if (!empty($this->parameters['uField']))
				{
					$this->definition_fragment = $this->parameters['uField']->getName();
				}
				else
				{
					$this->definition_fragment = $this->value->getName();
				}
			}
		}

		return $this->definition_fragment;
	}

	/**
	 * @return string
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getAliasFragment()
	{
		if (is_null($this->alias_fragment))
		{
			if ($this->type == 2)
			{
				// skip uts entity
				if ($this->value->getRefEntity()->isUts())
				{
					$this->alias_fragment = '';
				}
				else
				{
					$this->alias_fragment = $this->value->getName();
				}
			}
			elseif ($this->type == 3)
			{
				// skip utm entity
				if ($this->value[0]->isUtm())
				{
					$this->alias_fragment = '';
				}
				else
				{
					$this->alias_fragment = $this->value[0]->getCode() . '_' . $this->value[1]->getName();
				}
			}
			elseif ($this->type == 4)
			{
				$this->alias_fragment = $this->value->getCode();
			}
			else
			{
				if (!empty($this->parameters['ufield']))
				{
					$this->alias_fragment = $this->parameters['ufield']->getName();
				}
				else
				{
					$this->alias_fragment = $this->value->getName();
				}
			}
		}

		return $this->alias_fragment;
	}

	/**
	 * @return mixed|string
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getSqlDefinition()
	{
		if (is_array($this->value) || $this->value instanceof Reference || $this->value instanceof Entity)
		{
			throw new SystemException(sprintf(
				'There is no SQL definition for Entity `%s`, please use a scalar field',
				$this->getAliasFragment()
			));
		}

		$helper = $this->value->getEntity()->getConnection()->getSqlHelper();

		if ($this->value instanceof ExpressionField)
		{
			$SQLBuildFrom = [];
			$buildFromChains = $this->value->getBuildFromChains();

			foreach ($this->value->getBuildFrom() as $element)
			{
				if ($element instanceof \Closure)
				{
					/** @var SqlExpression $sqlExpression */
					$sqlExpression = $element();

					if (!($sqlExpression instanceof SqlExpression))
					{
						throw new ArgumentException(sprintf(
							'Expected instance of %s, got %s instead.',
							SqlExpression::class, gettype($sqlExpression)
						));
					}

					$SQLBuildFrom[] = $sqlExpression->compile();
				}
				else
				{
					$chain = array_shift($buildFromChains);
					$SQLBuildFrom[] = $chain->getSQLDefinition();
				}
			}

			$expr = $this->value->getExpression();

			// insert talias
			if (strpos($expr, '%%TABLE_ALIAS') !== false)
			{
				$expr = str_replace('%%TABLE_ALIAS', $helper->quote($this->getParameter('talias')), $expr);
			}

			// join
			$sql = call_user_func_array('sprintf', array_merge([$expr], $SQLBuildFrom));
		}
		else
		{
			$sql = $helper->quote($this->getParameter('talias')) . '.';
			$sql .= $helper->quote($this->value->getColumnName());
		}

		return $sql;
	}

	/**
	 * @return bool
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function isBackReference()
	{
		if ($this->type === 3 || $this->value instanceof OneToMany || $this->value instanceof ManyToMany)
		{
			return true;
		}

		if ($this->value instanceof ExpressionField)
		{
			foreach ($this->value->getBuildFromChains() as $bfChain)
			{
				if ($bfChain->hasBackReference())
				{
					return true;
				}
			}
		}

		return false;
	}

	public function dump()
	{
		echo gettype($this->value).' ';

		if ($this->value instanceof Field)
		{
			echo get_class($this->value).' '.$this->value->getName();
		}
		elseif ($this->value instanceof Entity)
		{
			echo get_class($this->value).' '.$this->value->getFullName();
		}
		elseif (is_array($this->value))
		{
			echo '('.get_class($this->value[0]).', '.get_class($this->value[1]).' '.$this->value[1]->getName().')';
		}

		echo ' '.json_encode($this->parameters);
	}
}