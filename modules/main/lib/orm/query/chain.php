<?php

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\SystemException;

class Chain
{
	/** @var ChainElement[] */
	protected $chain;

	protected $size = 0;

	/** @var string[] Definition caches ([0] - full length) */
	protected $definition;

	/** @var array Definition cache */
	protected $definitionParts;

	protected $alias;

	protected $custom_alias;

	/** @var boolean */
	protected $forcesDataDoublingOff = false;

	/** @var ChainElement */
	protected $last_element;

	public function __construct()
	{
		$this->chain = array();
	}

	/**
	 * @param ChainElement $element
	 *
	 * @throws SystemException
	 */
	public function addElement(ChainElement $element)
	{
		if (empty($this->chain) && !($element->getValue() instanceof Entity))
		{
			throw new SystemException('The first element of chain should be Entity only.');
		}

		$this->chain[] = $element;
		$this->definition = null;
		$this->definitionParts = null;
		$this->alias = null;

		$this->last_element = $element;
		$this->size++;
	}

	/**
	 * @param ChainElement $element
	 */
	public function prependElement(ChainElement $element)
	{
		$this->chain = array_merge(array($element), $this->chain);
		$this->definition = null;
		$this->definitionParts = null;
		$this->alias = null;

		$this->size++;
	}

	/**
	 * @param Chain $chain
	 */
	public function prepend(Chain $chain)
	{
		$elements = $chain->getAllElements();

		for ($i=count($elements)-1; $i>=0; $i--)
		{
			$this->prependElement($elements[$i]);
		}
	}

	public function getFirstElement()
	{
		return $this->chain[0];
	}

	/**
	 * @return ChainElement
	 */
	public function getLastElement()
	{
		return $this->last_element;
	}

	/**
	 * @return array|ChainElement[]
	 */
	public function getAllElements()
	{
		return $this->chain;
	}

	public function removeLastElement()
	{
		$this->chain = array_slice($this->chain, 0, -1);
		$this->definition = null;
		$this->definitionParts = null;
		$this->alias = null;

		$this->last_element = end($this->chain);
		$this->size--;
	}

	public function removeFirstElement()
	{
		$this->chain = array_slice($this->chain, 1);
		$this->definition = null;
		$this->definitionParts = null;
		$this->alias = null;

		$this->size--;
	}

	public function hasBackReference()
	{
		foreach ($this->chain as $element)
		{
			if ($element->isBackReference())
			{
				return true;
			}
		}

		return false;
	}

	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param int $elementsSlice Definition length, e.g. -1 would exclude last element.
	 *
	 * @return string
	 */
	public function getDefinition($elementsSlice = 0)
	{
		if (!isset($this->definition[$elementsSlice]))
		{
			$this->definition[$elementsSlice] = join('.',
				$elementsSlice == 0
					? $this->getDefinitionParts()
					: array_slice($this->getDefinitionParts(), 0, $elementsSlice)
			);
		}

		return $this->definition[$elementsSlice];
	}

	/**
	 * @return array
	 */
	public function getDefinitionParts()
	{
		if (is_null($this->definitionParts))
		{
			$this->definitionParts = static::getDefinitionPartsByChain($this);
		}

		return $this->definitionParts;
	}

	public function getAlias()
	{
		if ($this->custom_alias !== null)
		{
			return $this->custom_alias;
		}

		if ($this->alias === null)
		{
			$this->alias = self::getAliasByChain($this);
		}

		return $this->alias;
	}

	public function setCustomAlias($alias)
	{
		$this->custom_alias = $alias;
	}

	/**
	 * @param Entity $init_entity
	 * @param      $definition
	 *
	 * @return Chain
	 * @throws Main\ArgumentException
	 * @throws SystemException
	 */
	public static function getChainByDefinition(Entity $init_entity, $definition)
	{
		if (!is_string($definition))
		{
			throw new Main\ArgumentException('String expected, but `'.gettype($definition).'` is given.');
		}

		$chain = new Chain;
		$chain->addElement(new ChainElement($init_entity));

		$def_elements = explode('.', $definition);
		$def_elements_size = count($def_elements);

		$prev_entity  = $init_entity;

		$i = 0;

		foreach ($def_elements as &$def_element)
		{
			$is_last_elem  = (++$i == $def_elements_size);

			$not_found = false;

			// all elements should be a Reference field or Entity
			// normal (scalar) field can only be the last element

			if ($prev_entity->hasField($def_element))
			{
				// field has been found at current entity
				$field = $prev_entity->getField($def_element);

				if ($field instanceof Reference)
				{
					$prev_entity = $field->getRefEntity();
				}
				elseif ($field instanceof ExpressionField)
				{
					// expr can be in the middle too
				}
				elseif ($field instanceof OneToMany)
				{
					$prev_entity = $field->getRefEntity();
				}
				elseif ($field instanceof ManyToMany)
				{
					$prev_entity = $field->getRefEntity();
				}
				elseif (!$is_last_elem)
				{
					throw new SystemException(sprintf(
						'Normal fields can be only the last in chain, `%s` %s is not the last.',
						$field->getName(), get_class($field)
					));
				}

				if ($is_last_elem && $field instanceof ExpressionField)
				{
					// we should have own copy of build_from_chains to set join aliases there
					$field = clone $field;
				}

				$chain->addElement(new ChainElement($field));
			}
			elseif (Entity::isExists($def_element)
				&& Entity::getInstance($def_element)->getReferencesCountTo($prev_entity->getName()) == 1
			)
			{
				// def_element is another entity with only 1 reference to current entity
				// need to identify Reference field
				$ref_entity = Entity::getInstance($def_element);
				$field = end($ref_entity->getReferencesTo($prev_entity->getName()));

				$prev_entity = $ref_entity;

				$chain->addElement(new ChainElement(
					array($ref_entity, $field)
				));
			}
			elseif ( ($pos_wh = strpos($def_element, ':')) > 0 )
			{
				$ref_entity_name = substr($def_element, 0, $pos_wh);

				if (!str_contains($ref_entity_name, '\\'))
				{
					// if reference has no namespace, then it'is in the namespace of previous entity
					$ref_entity_name = $prev_entity->getNamespace().$ref_entity_name;
				}

				if (
					Entity::isExists($ref_entity_name)
					&& Entity::getInstance($ref_entity_name)->hasField($ref_field_name = substr($def_element, $pos_wh + 1))
					&& Entity::getInstance($ref_entity_name)->getField($ref_field_name) instanceof Reference
				)
				{
					/** @var Reference $reference */
					$reference = Entity::getInstance($ref_entity_name)->getField($ref_field_name);

					if (
						$reference->getRefEntity()->getFullName() == $prev_entity->getFullName() ||
						is_subclass_of(
							$prev_entity->getDataClass(),
							$reference->getRefEntity()->getDataClass()
						)
					)
					{
						// chain element is another entity with >1 references to current entity
						// def like NewsArticle:AUTHOR, NewsArticle:LAST_COMMENTER
						// NewsArticle - entity, AUTHOR and LAST_COMMENTER - Reference fields
						$chain->addElement(new ChainElement(array(
							Entity::getInstance($ref_entity_name),
							Entity::getInstance($ref_entity_name)->getField($ref_field_name)
						)));

						$prev_entity = Entity::getInstance($ref_entity_name);
					}
					else
					{
						$not_found = true;
					}
				}
				else
				{
					$not_found = true;
				}

			}
			elseif ($def_element == '*' && $is_last_elem)
			{
				continue;
			}
			else
			{
				// unknown chain
				$not_found = true;
			}

			if ($not_found)
			{
				throw new SystemException(sprintf(
					'Unknown field definition `%s` (%s) for %s Entity.',
					$def_element, $definition, $prev_entity->getFullName()
				), 100);
			}
		}

		return $chain;
	}

	public static function getDefinitionByChain(Chain $chain)
	{
		return join('.', static::getDefinitionPartsByChain($chain));
	}

	public static function getDefinitionPartsByChain(Chain $chain)
	{
		$def = array();

		// add members of chain except of init entity
		/** @var $elements ChainElement[] */
		$elements = array_slice($chain->getAllElements(), 1);

		foreach ($elements  as $element)
		{
			//if ($element->getValue() instanceof ExpressionField && $element !== end($elements))
			{
				// skip non-last expressions
				//continue;
			}

			$def[] = $element->getDefinitionFragment();
		}

		return $def;
	}

	public static function appendDefinition($currentDefinition, $newDefinitionPart)
	{
		if ($currentDefinition !== '')
		{
			$currentDefinition .= '.';
		}

		return $currentDefinition.$newDefinitionPart;
	}

	public static function getAliasByChain(Chain $chain)
	{
		$alias = array();

		$elements = $chain->getAllElements();

		// add prefix of init entity
		if (count($elements) > 2)
		{
			$alias[] = $chain->getFirstElement()->getAliasFragment();
		}

		// add other members of chain
		/** @var ChainElement[] $elements */
		$elements = array_slice($elements, 1);

		foreach ($elements  as $element)
		{
			$fragment = $element->getAliasFragment();

			if($fragment <> '')
			{
				$alias[] = $fragment;
			}
		}

		return join('_', $alias);
	}

	/**
	 * @param Entity $entity
	 * @param      $definition
	 *
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws SystemException
	 */
	public static function getAliasByDefinition(Entity $entity, $definition)
	{
		return self::getChainByDefinition($entity, $definition)->getAlias();
	}

	/**
	 * @return bool
	 * @throws SystemException
	 */
	public function hasAggregation()
	{
		$elements = array_reverse($this->chain);

		foreach ($elements as $element)
		{
			/** @var $element ChainElement */
			if ($element->getValue() instanceof ExpressionField && $element->getValue()->isAggregated())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 * @throws SystemException
	 */
	public function hasSubquery()
	{
		$elements = array_reverse($this->chain);

		foreach ($elements as $element)
		{
			/** @var $element ChainElement */
			if ($element->getValue() instanceof ExpressionField && $element->getValue()->hasSubquery())
			{
				return true;
			}
		}

		return false;
	}

	public function isConstant()
	{
		return ($this->getLastElement()->getValue() instanceof ExpressionField
			&& $this->getLastElement()->getValue()->isConstant());
	}

	public function forceDataDoublingOff()
	{
		$this->forcesDataDoublingOff = true;
	}

	public function forcesDataDoublingOff()
	{
		return $this->forcesDataDoublingOff;
	}

	/**
	 * @param bool $with_alias
	 *
	 * @return mixed|string
	 * @throws SystemException
	 */
	public function getSqlDefinition($with_alias = false)
	{
		$sql_def = $this->getLastElement()->getSqlDefinition();

		if ($with_alias)
		{
			$helper = $this->getLastElement()->getValue()->getEntity()->getConnection()->getSqlHelper();
			$sql_def .= ' AS ' . $helper->quote($this->getAlias());
		}

		return $sql_def;
	}

	public function __clone()
	{
		$this->custom_alias = null;
	}

	public function dump()
	{
		echo '  '.'   forcesDataDoublingOff: '.($this->forcesDataDoublingOff()?'true':'false');
		echo PHP_EOL;

		$i = 0;
		foreach ($this->chain as $elem)
		{
			echo '  '.++$i.'. ';
			$elem->dump();
			echo PHP_EOL;
		}
	}
}