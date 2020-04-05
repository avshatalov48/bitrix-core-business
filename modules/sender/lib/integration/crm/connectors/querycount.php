<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

/**
 * Class QueryCount
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class QueryCount
{
	/**
	 * Get unionized count.
	 *
	 * @param Entity\Query[] $queries Queries.
	 * @param integer $dataTypeId Data type ID.
	 * @return array
	 */
	public static function getUnionizedCount(array $queries, $dataTypeId = null)
	{
		foreach ($queries as $query)
		{
			self::prepare($query, $dataTypeId);
		}

		$query = array_pop($queries);
		foreach ($queries as $unionQuery)
		{
			$query->unionAll($unionQuery);
		}

		return self::exec($query, $dataTypeId);
	}

	/**
	 * Get count.
	 *
	 * @param Entity\Query $query Query.
	 * @param integer $dataTypeId Data type ID.
	 * @return array
	 */
	public static function getCount(Entity\Query $query, $dataTypeId = null)
	{
		self::prepare($query, $dataTypeId);
		return self::exec($query, $dataTypeId);
	}

	/**
	 * Execute query.
	 *
	 * @param Entity\Query $query Query.
	 * @param integer $dataTypeId Data type ID.
	 * @return array
	 */
	private static function exec(Entity\Query $query, $dataTypeId = null)
	{
		$result = array();
		$resultDb = Helper::prepareQuery($query, $dataTypeId)->exec();
		while ($row = $resultDb->fetch())
		{
			foreach (self::getTypes() as $typeId => $field)
			{
				$fieldName = 'COUNT_' . $field['DATA_COLUMN'];
				if (!isset($row[$fieldName]))
				{
					continue;
				}

				$type = Recipient\Type::getCode($typeId);
				if (!isset($result[$type]))
				{
					$result[$type] = 0;
				}
				$result[$type] += (int) $row[$fieldName];
			}
		}

		return $result;
	}

	/**
	 * Prepare query.
	 *
	 * @param Entity\Query $query Query.
	 * @param integer $dataTypeId Data type ID.
	 * @return Entity\Query
	 */
	private static function prepare(Entity\Query $query, $dataTypeId = null)
	{
		$fields = array();
		foreach (self::getTypes() as $typeId => $field)
		{
			if ($dataTypeId && $dataTypeId != $typeId)
			{
				continue;
			}

			$entityName = strtoupper($query->getEntity()->getName());

			$fieldName = 'COUNT_' . $field['DATA_COLUMN'];
			$fields[] = $fieldName;

			if ($field['HAS'])
			{
				$query->registerRuntimeField(new Entity\ExpressionField(
					$fieldName,
					"SUM(CASE WHEN %s = 'Y' THEN 1 ELSE 0 END)",
					$field['HAS']
				));
			}
			else
			{
				if ($typeId === Recipient\Type::CRM_COMPANY_ID && in_array($entityName, ['CONTACT']))
				{
					$field['DATA_COLUMN'] = 'CRM_COMPANY_ID';
				}

				$query->registerRuntimeField(new Entity\ExpressionField(
					$fieldName,
					"SUM(CASE WHEN %s > 0 THEN 1 ELSE 0 END)",
					$field['DATA_COLUMN']
				));
			}
		}
		$query->setSelect($fields);

		return $query;
	}

	private static function getTypes()
	{
		return array(
			Recipient\Type::EMAIL => ['DATA_COLUMN' => 'EMAIL', 'HAS' => 'HAS_EMAIL'],
			Recipient\Type::PHONE => ['DATA_COLUMN' => 'PHONE', 'HAS' => 'HAS_PHONE'],
			Recipient\Type::IM => ['DATA_COLUMN' => 'IMOL', 'HAS' => 'HAS_IMOL'],
			Recipient\Type::CRM_CONTACT_ID => ['DATA_COLUMN' => 'CONTACT_ID', 'HAS' => null],
			Recipient\Type::CRM_COMPANY_ID => ['DATA_COLUMN' => 'COMPANY_ID', 'HAS' => null],
		);
	}
}
