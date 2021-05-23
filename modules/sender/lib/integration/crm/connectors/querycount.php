<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\Connectors;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
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
	 * Get count.
	 *
	 * @param Entity\Query $query Query.
	 * @param integer $dataTypeId Data type ID.
	 * @return array
	 */
	public static function getPreparedCount(
		Entity\Query $query,
		string $entityDbName,
		string $entityName,
		$dataTypeId = null
	)
	{
		self::prepare($query, $dataTypeId, $entityDbName, $entityName);
		return self::exec($query, $dataTypeId, $entityDbName, $entityName);
	}

	/**
	 * Execute query.
	 *
	 * @param Entity\Query $query Query.
	 * @param integer $dataTypeId Data type ID.
	 *
	 * @return array
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function exec(Entity\Query $query, $dataTypeId = null, $entityDbName = null, $entityName = null)
	{
		$result = array();
		$resultDb = Helper::prepareQuery($query, $dataTypeId, $entityDbName, $entityName)->exec();
		while ($row = $resultDb->fetch())
		{
			$ignoredTypes = [];
			foreach (self::getTypes() as $typeId => $field)
			{
				$fieldName = $field['COLUMN_ALIAS'] ? $field['COLUMN_ALIAS'] : 'COUNT_' . $field['DATA_COLUMN'];
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

				if ($field['IGNORE_TYPES'] && $row[$fieldName] > 0)
				{
					$ignoredTypes = array_merge($ignoredTypes, $field['IGNORE_TYPES']);
				}
			}
			foreach(array_unique($ignoredTypes) as $ignoreTypeId)
			{
				$ignoreType = Recipient\Type::getCode($ignoreTypeId);
				unset($result[$ignoreType]);
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
	private static function prepare(Entity\Query $query, $dataTypeId = null, $entityDbName = null, $entityName = null)
	{
		$fields = array();
		foreach (self::getTypes() as $typeId => $field)
		{
			if ($dataTypeId && $dataTypeId != $typeId)
			{
				continue;
			}

			$entityName = $entityName ?? mb_strtoupper($query->getEntity()->getName());
			$entityDbName = $entityDbName ?? "crm_".mb_strtolower($query->getEntity()->getName());

			$useEmptyValue = false;
			if (mb_strpos($field['DATA_COLUMN'], '.') > 0)
			{
				$refFieldName = array_shift(explode('.', $field['DATA_COLUMN']));
				if (!array_key_exists($refFieldName, $query->getRuntimeChains()))
				{
					$useEmptyValue = true;
				}
			}
			if (!empty($field['ENTITIES']) && !in_array($entityName, $field['ENTITIES']))
			{
				$useEmptyValue = true;
			}

			$fieldName = $field['COLUMN_ALIAS'] ? $field['COLUMN_ALIAS'] : 'COUNT_' . $field['DATA_COLUMN'];
			$fields[] = $fieldName;

			if ($field['HAS'])
			{
				$query->registerRuntimeField(new Entity\ExpressionField(
					$fieldName,
					"COUNT(DISTINCT CASE WHEN %s = 'Y' THEN `{$entityDbName}`.`ID` END)",
					$field['HAS']
				));
			}
			elseif ($useEmptyValue)
			{
				$query->registerRuntimeField(new Entity\ExpressionField(
					$fieldName,
					"0"
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
					"COUNT(DISTINCT CASE WHEN %s > 0 THEN `{$entityDbName}`.`ID` END)",
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
			Recipient\Type::CRM_CONTACT_ID => [
				'DATA_COLUMN' => 'CONTACT_ID',
				'HAS' => null
			],
			Recipient\Type::CRM_DEAL_PRODUCT_CONTACT_ID => [
				'DATA_COLUMN' => 'SGT_DEAL.ID',
				'COLUMN_ALIAS' => 'COUNT_CONTACT_DEAL_PRODUCT',
				'HAS' => null,
				'ENTITIES' => ['CONTACT']
			],
			Recipient\Type::CRM_ORDER_PRODUCT_CONTACT_ID => [
				'DATA_COLUMN' => 'PROD_CRM_ORDER.ID',
				'COLUMN_ALIAS' => 'COUNT_CONTACT_ORDER_PRODUCT',
				'HAS' => null,
				'IGNORE_TYPES' => [Recipient\Type::CRM_CONTACT_ID],
				'ENTITIES' => ['CONTACT']
			],
			Recipient\Type::CRM_COMPANY_ID => [
				'DATA_COLUMN' => 'COMPANY_ID',
				'HAS' => null
			],
			Recipient\Type::CRM_DEAL_PRODUCT_COMPANY_ID => [
				'DATA_COLUMN' => 'SGT_DEAL.ID',
				'COLUMN_ALIAS' => 'COUNT_COMPANY_DEAL_PRODUCT',
				'HAS' => null,
				'ENTITIES' => ['COMPANY']
			],
			Recipient\Type::CRM_ORDER_PRODUCT_COMPANY_ID => [
				'DATA_COLUMN' => 'PROD_CRM_ORDER.ID',
				'COLUMN_ALIAS' => 'COUNT_COMPANY_ORDER_PRODUCT',
				'HAS' => null,
				'IGNORE_TYPES' => [Recipient\Type::CRM_COMPANY_ID],
				'ENTITIES' => ['COMPANY']
			],
		);
	}
}
