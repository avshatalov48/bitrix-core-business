<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2020 Bitrix
 */

namespace Bitrix\Iblock\Controller;

use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ORM\ElementEntity;
use Bitrix\Iblock\ORM\Fields\PropertyRelation;
use Bitrix\Iblock\ORM\ValueStorageEntity;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\ActionFilter\Scope;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Objectify\Values;
use Bitrix\Main\ORM\Query\Chain;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression;
use Bitrix\Main\UI\PageNavigation;

/**
 * @package    bitrix
 * @subpackage main
 */
class DefaultElement extends Controller
{
	const DEFAULT_LIMIT = 10;

	protected function getDefaultPreFilters()
	{
		return array_merge([new Scope(Scope::REST)], parent::getDefaultPreFilters());
	}

	public static function getAllowedList()
	{
		return [];
	}

	public static function getElementEntityAllowedList()
	{
		return [
			'ID',
			'NAME',
			'IBLOCK_SECTION_ID',
		];
	}

	public static function getPropertyEntityAllowedList()
	{
		return [
			'ID',
			'VALUE',
			'DESCRIPTION'
		];
	}

	/**
	 * @param Iblock $iblock
	 * @param int    $elementId
	 * @param array  $select
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAction($iblock, $elementId, array $select = ['*'])
	{
		$listResult = $this->listAction($iblock, $select, [['ID', $elementId]]);
		$element = !empty($listResult->getItems()[0]) ? $listResult->getItems()[0] : [];

		return ['element' => $element];
	}

	/**
	 * @param Iblock              $iblock
	 * @param array               $select
	 * @param array               $filter
	 * @param array               $order
	 * @param PageNavigation|null $pageNavigation
	 *
	 * @return Page
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listAction($iblock, $select = ['*'], $filter = [], $order = [], PageNavigation $pageNavigation = null)
	{
		$elementEntity = IblockTable::compileEntity($iblock);
		$elementDataClass = $elementEntity->getDataClass();

		if (!$elementEntity->getIblock()->fillRestOn())
		{
			throw new ArgumentException(sprintf('Restricted iblock ID:%s (%s)',
				$elementEntity->getIblock()->getId(),
				$elementEntity->getIblock()->getApiCode()
			));
		}

		$query = $elementDataClass::query();

		// prepare id select
		$query->addSelect(new ExpressionField('DISTINCT_ID', 'DISTINCT %s', 'ID'));

		// prepare filter
		if (!empty($filter))
		{
			$qFilter = static::prepareFilter($filter, $elementEntity);
			$query->where($qFilter);
		}

		// prepare order
		if (!empty($order))
		{
			$qOrder = static::prepareOrder($order, $elementEntity);
			$query->setOrder($qOrder);
		}

		// prepare limit
		$qLimit = $pageNavigation ? $pageNavigation->getLimit() : static::DEFAULT_LIMIT;
		$qOffset = $pageNavigation ? $pageNavigation->getOffset() : 0;

		// get elements
		$result = $query
			->setLimit($qLimit)
			->setOffset($qOffset)
			->exec();

		// count records
		if ($result->getSelectedRowsCount() < $qLimit)
		{
			// optimization for first and last pages
			$countTotal = $qOffset + $result->getSelectedRowsCount();
		}
		else
		{
			$countTotal = function () use ($query) {
				return $query->queryCountTotal();
			};
		}

		// get elements data
		$elements = [];
		$ids = [];

		foreach ($result->fetchAll() as $row)
		{
			$ids[] = $row['DISTINCT_ID'];
		}

		if (!empty($ids))
		{
			$query = $elementDataClass::query();

			$qSelect = static::prepareSelect($select, $elementEntity);
			$query->setSelect($qSelect);

			$query->whereIn('ID', $ids);

			$resultElements = [];

			foreach ($query->fetchCollection() as $elementObject)
			{
				/** @var EntityObject $elementObject */
				$resultElements[$elementObject->getId()] = $elementObject->collectValues(Values::ALL, FieldTypeMask::ALL, true);
			}

			// original sort
			foreach ($ids as $id)
			{
				$elements[] = $resultElements[$id];
			}
		}

		return new Page('elements', $elements, $countTotal);
	}

	protected static function prepareSelect($fields, Entity $entity)
	{
		return static::checkFields($fields, $entity);
	}


	protected static function prepareFilter($filter, Entity $entity)
	{
		$filter = ConditionTree::createFromArray($filter);
		$definitions = static::getFilterDefinitions($filter);

		static::checkFields($definitions, $entity);

		return $filter;
	}

	protected static function prepareOrder($order, Entity $entity)
	{
		static::checkFields(array_keys($order), $entity);

		return $order;
	}

	protected static function checkFields($fields, Entity $entity)
	{
		$propertyEntityAllowedList = static::getPropertyEntityAllowedList();
		$elementEntityAllowedList = static::getElementEntityAllowedList();
		$allowedList = array_merge($elementEntityAllowedList, static::getAllowedList());

		// replace REF.* for allowed fields REF.ALLOWED1, REF.ALLOWED2, etc.
		$chainReplacement = [];

		// analyze
		foreach ($fields as $definition)
		{
			// check allowed list
			if (in_array($definition, $allowedList, true))
			{
				continue;
			}

			// smart check for relations and property fields
			$chain = Chain::getChainByDefinition($entity, $definition);
			$currentDefinition = '';

			$elements = $chain->getAllElements();
			$lastElement = $chain->getLastElement();

			foreach ($elements as $element)
			{
				$isLastElement = ($element === $lastElement);

				// skip init entity
				if ($element->getValue() instanceof ElementEntity && !$isLastElement)
				{
					continue;
				}

				// append definition
				$currentDefinition = Chain::appendDefinition($currentDefinition, $element->getDefinitionFragment());

				// handle wildcard
				if ($currentDefinition === '*' && $isLastElement)
				{
					$chainReplacement[$definition] = [];

					foreach ($elementEntityAllowedList as $allowedFieldName)
					{
						if ($entity->hasField($allowedFieldName))
						{
							$chainReplacement[$definition][] = $allowedFieldName;
						}
					}

					continue;
				}

				// check access
				if (!($element->getValue() instanceof Field))
				{
					throw new ArgumentException(
						sprintf('Restricted field `%s`', $currentDefinition)
					);
				}

				$currentField = $element->getValue();
				$currentEntity = $currentField->getEntity();

				// case 1. iblock
				if ($currentEntity instanceof ElementEntity)
				{
					// case 1.1. iblock scalar
					if (in_array($currentField->getName(), $elementEntityAllowedList, true))
					{
						continue;
					}

					// case 1.2. iblock property
					if (!empty(class_uses($currentField)[PropertyRelation::class]))
					{
						if ($isLastElement)
						{
							// replace * with allowed fields
							$propEntity = $currentField->getRefEntity();
							$chainReplacement[$definition] = [];

							foreach ($propertyEntityAllowedList as $allowedFieldName)
							{
								if ($propEntity->hasField($allowedFieldName))
								{
									$chainReplacement[$definition][] = Chain::appendDefinition($currentDefinition, $allowedFieldName);
								}
							}
						}

						continue;
					}
				}

				// case 2. property entity
				if ($currentEntity instanceof ValueStorageEntity)
				{
					// case 2.1. property scalar
					if (in_array($currentField->getName(), $propertyEntityAllowedList, true))
					{
						continue;
					}

					// case 2.1. ref to another iblock
					if ($currentField instanceof Reference)
					{
						$refEntity = $currentField->getRefEntity();

						// check if remote iblock is readable
						if ($refEntity instanceof ElementEntity && $refEntity->getIblock()->fillRestOn())
						{
							if ($isLastElement)
							{
								// replace * with allowed fields
								$chainReplacement[$definition] = [];

								foreach ($elementEntityAllowedList as $allowedFieldName)
								{
									if ($refEntity->hasField($allowedFieldName))
									{
										$chainReplacement[$definition][] = Chain::appendDefinition($currentDefinition, $allowedFieldName);
									}
								}
							}

							continue;
						}
					}
				}

				// restricted by default
				throw new ArgumentException(
					sprintf('Restricted field `%s`', $currentDefinition)
				);
			}
		}

		// time to replace *
		foreach ($chainReplacement as $definition => $replacement)
		{
			// unset original
			$key = array_search($definition, $fields);
			unset($fields[$key]);

			// add replacement
			$fields = array_merge($fields, $replacement);
		}

		return $fields;
	}

	protected static function getFilterDefinitions(ConditionTree $filter)
	{
		$definitions = [];

		foreach ($filter->getConditions() as $condition)
		{
			if ($condition instanceof ConditionTree)
			{
				// add subfilter recursively
				$definitions = array_merge($definitions, static::getFilterDefinitions($condition));
			}
			else
			{
				// add column
				if ($condition->getColumn() !== null)
				{
					$definitions[] = $condition->getColumn();
				}

				// add value
				$values = $condition->getValue();
				if (!is_array($values))
				{
					$values = [$values];
				}

				foreach ($values as $subValue)
				{
					if ($subValue instanceof ColumnExpression)
					{
						$definitions[] = $subValue->getDefinition();
					}
				}
			}
		}

		return $definitions;
	}
}
