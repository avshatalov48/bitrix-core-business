<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Query;
use Bitrix\Main\ORM\Fields\ExpressionField;

/**
 * Class description
 * @package    bitrix
 * @subpackage main
 */
class NosqlPrimarySelector
{
	/**
	 * @param \Bitrix\Main\Data\Connection $connection
	 * @param Query                        $query
	 *
	 * @return bool
	 */
	public static function checkQuery(\Bitrix\Main\Data\Connection $connection, Query $query)
	{
		// check interface
		if (!($connection instanceof INosqlPrimarySelector))
		{
			return false;
		}

		// no expressions in select
		foreach ($query->getSelectChains() as $selectChain)
		{
			if ($selectChain->getLastElement()->getValue() instanceof ExpressionField)
			{
				return false;
			}
		}

		// skip empty select - useless case for nosql api
		if (empty($query->getSelect()))
		{
			return false;
		}

		// if empty joinmap, group, order and simple filter
		if (!count($query->getJoinMap()) && !count($query->getGroupChains()) && !count($query->getOrderChains()) && !count($query->getHavingChains()))
		{
			$entityPrimary = $query->getEntity()->getPrimary();

			// check for primary singularity
			if (!is_array($entityPrimary))
			{
				// check if only primary is in filter
				if (count($query->getFilterChains()) == 1 && key($query->getFilterChains()) === $entityPrimary)
				{
					$passFilter = true;

					// check if only equality operations & 1-level filter
					if ($query->getFilter())
					{
						foreach ($query->getFilter() as $filterElement => $filterValue)
						{
							if (is_numeric($filterElement) && is_array($filterValue))
							{
								// filter has subfilters. not ok
								$passFilter = false;
								break;
							}

							// no multiple values for HSPHP
							if (is_array($filterValue))
							{
								$passFilter = false;
								break;
							}

							// skip system keys
							if ($filterElement === 'LOGIC')
							{
								continue;
							}

							$operation = substr($filterElement, 0, 1);

							if ($operation !== '=')
							{
								// only equal operation allowed. not ok
								$passFilter = false;
								break;
							}
						}
					}
					elseif ($query->getFilterHandler()->hasConditions())
					{
						foreach ($query->getFilterHandler()->getConditions() as $condition)
						{
							if ($condition->getOperator() !== '=' || !is_scalar($condition->getValue()))
							{
								$passFilter = false;
								break;
							}
						}
					}


					// fine!
					if ($passFilter)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	public static function relayQuery(\Bitrix\Main\Data\Connection $connection, Query $query)
	{
		// prepare select
		$select = array();

		foreach ($query->getSelectChains() as $selectChain)
		{
			$select[] = $selectChain->getLastElement()->getValue()->getName();
		}

		// prepare filter
		$filter = array();

		if ($query->getFilter())
		{
			foreach ($query->getFilter() as $filterElem)
			{
				if (is_array($filterElem))
				{
					$filter = array_merge($filter, $filterElem);
				}
				else
				{
					$filter[] = $filterElem;
				}
			}
		}
		elseif ($query->getFilterHandler()->hasConditions())
		{
			foreach ($query->getFilterHandler()->getConditions() as $condition)
			{
				$filter[] = $condition->getValue();
			}
		}

		$result = $connection->getEntityByPrimary($query->getEntity(), $filter, $select);

		return $result;
	}
}
