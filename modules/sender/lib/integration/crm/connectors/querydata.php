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
use Bitrix\Main\DB\Result;

use Bitrix\Sender\Recipient;
use Bitrix\Sender\UI\PageNavigation;

Loc::loadMessages(__FILE__);

/**
 * Class QueryData
 * @package Bitrix\Sender\Integration\Crm\Connectors
 */
class QueryData
{
	/**
	 * Get unionized data.
	 *
	 * @param Entity\Query[] $queries Queries.
	 * @param integer $dataTypeId Data type ID.
	 * @param PageNavigation $nav Nav.
	 * @return Entity\Query
	 */
	public static function getUnionizedQuery(array $queries, $dataTypeId = null, PageNavigation $nav = null)
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

		if ($nav)
		{
			if (empty($queries))
			{
				$query->setOffset($nav->getOffset());
				$query->setLimit($nav->getLimit());
			}
			else
			{
				$query->setUnionOffset($nav->getOffset());
				$query->setUnionLimit($nav->getLimit());
			}
		}

		return $query;
	}

	/**
	 * Get unionized data.
	 *
	 * @param Entity\Query $query Query.
	 * @return Result
	 */
	public static function getUnionizedData(Entity\Query $query)
	{
		return self::exec($query);
	}

	private static function prepare(Entity\Query $query, $dataTypeId = null)
	{
		$fields = self::getSelectFields();
		foreach ($fields as $alias => $field)
		{
			if (is_numeric($alias))
			{
				$alias = '';
			}

			$query->addSelect($field, $alias);
		}

		return Helper::prepareQuery($query, $dataTypeId);
	}

	private static function exec(Entity\Query $query)
	{
		$result = $query->exec();
		$result->addFetchDataModifier(
			function ($data)
			{
				if (!isset($data['EMAIL']) || !$data['EMAIL'])
				{
					if (isset($data['EMAIL_HOME']) && $data['EMAIL_HOME'])
					{
						$data['EMAIL'] = $data['EMAIL_HOME'];
					}
					else if (isset($data['EMAIL_WORK']) && $data['EMAIL_WORK'])
					{
						$data['EMAIL'] = $data['EMAIL_WORK'];
					}
				}

				if (!isset($data['PHONE']) || !$data['PHONE'])
				{
					if (isset($data['PHONE_MOBILE']) && $data['PHONE_MOBILE'])
					{
						$data['PHONE'] = $data['PHONE_MOBILE'];
					}
					else if (isset($data['PHONE_WORK']) && $data['PHONE_WORK'])
					{
						$data['PHONE'] = $data['PHONE_WORK'];
					}
				}

				return $data;
			}
		);

		return $result;
	}

	/**
	 * Get data.
	 *
	 * @param Entity\Query $query Query.
	 * @param integer $dataTypeId Data type ID.
	 * @return Result
	 */
	public static function getData(Entity\Query $query, $dataTypeId = null)
	{
		self::prepare($query, $dataTypeId);
		return self::exec($query);
	}

	protected static function getSelectFields($dataTypeId = null)
	{
		$fields = array();

		$map = array(
			Recipient\Type::EMAIL => array('EMAIL'), //array('EMAIL_HOME', 'EMAIL_WORK'),
			Recipient\Type::PHONE => array('PHONE'), //array('PHONE_WORK', 'PHONE_MOBILE'),
			Recipient\Type::IM => array('IM' => 'IMOL'),
			Recipient\Type::CRM_CONTACT_ID => array('CRM_CONTACT_ID'),
			Recipient\Type::CRM_COMPANY_ID => array('CRM_COMPANY_ID'),
		);
		if ($dataTypeId)
		{
			if (isset($map[$dataTypeId]))
			{
				$fields = $map[$dataTypeId];
			}
		}
		else
		{
			$fields = call_user_func_array('array_merge', array_values($map));
		}

		return $fields;
	}
}
