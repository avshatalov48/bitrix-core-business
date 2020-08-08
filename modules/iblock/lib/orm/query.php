<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage iblock
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Iblock\ORM;

use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query as BaseQuery;

/**
 * Separate query to fix PropertyValue emulation in 2.0
 * Collapses similar joins to b_iblock_element_prop_sX table into one join
 *
 * @package    bitrix
 * @subpackage iblock
 */
class Query extends BaseQuery
{
	protected function buildJoin()
	{
		// collapse v2.0 single value joins into one
		if ($this->entity instanceof ElementV2Entity)
		{
			// table alias for all the records (cut b_ prefix)
			$propSTable = $this->entity->getSingleValueTableName();
			$commonAlias = mb_substr($propSTable, 2);

			// found first join and changed alias. all other joins to be removed
			$changed = false;

			// table aliases to be changed in chains
			$replacedAliases = [];

			// 1. collapse joins
			foreach ($this->join_map as $k => $join)
			{
				// check join
				if ($join['table'] === $propSTable && $join['type'] === Join::TYPE_INNER)
				{
					/** @var ConditionTree[]|string[] $join */
					$conditions = $join['reference']->getConditions();

					// check on condition
					if (count($conditions) === 1 && $conditions[0]->getColumn() === 'ID' && $conditions[0]->getOperator() === '=')
					{
						// collect table aliases to rewrite in chains
						$replacedAliases[$join['alias']] = true;

						if (!$changed)
						{
							// make the only one change
							$this->join_map[$k]['alias'] = $commonAlias;

							$changed = true;
						}
						else
						{
							// remove from registry
							unset($this->join_map[$k]);
						}

					}
				}
			}

			// 2. rewrite aliases
			foreach ($this->global_chains as $chain)
			{
				if (!empty($replacedAliases[$chain->getLastElement()->getParameter('talias')]))
				{
					$chain->getLastElement()->setParameter('talias', $commonAlias);
				}
			}
		}

		return parent::buildJoin();
	}
}
