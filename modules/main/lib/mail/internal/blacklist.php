<?php

namespace Bitrix\Main\Mail\Internal;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class BlacklistTable
 *
 * @package Bitrix\Main\Mail\Internal
 */
class BlacklistTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_main_mail_blacklist';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required'  => new DateTime(),
			),
		);
	}

	/**
	 * Return true if table has rows.
	 *
	 * @return bool
	 */
	public static function hasBlacklistedEmails()
	{
		static $hasEmails = null;
		if ($hasEmails === null)
		{
			$row = static::getRow([
				'select' => ['ID'],
				'limit' => 1,
				'order' => ['ID' => 'ASC'],
				'cache' => ['ttl' => 36000]
			]);

			$hasEmails = $row !== null;
		}

		return $hasEmails;
	}

	/**
	 * Insert batch of emails.
	 *
	 * @param string[] $list List of emails.
	 * @return void
	 */
	public static function insertBatch(array $list)
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$tableName = static::getTableName();
		$dateNow = $sqlHelper->convertToDbDateTime(new DateTime());

		foreach (self::divideList($list) as $batchList)
		{
			$values = [];
			foreach ($batchList as $code)
			{
				$code = trim($code);
				if (!$code)
				{
					continue;
				}

				$code = $sqlHelper->forSql($code);
				$values[] = "$dateNow, \"$code\"";
			}

			if (count($values) === 0)
			{
				return;
			}

			$values = '(' . implode('), (', $values) . ')';
			$sql = "INSERT IGNORE INTO $tableName (DATE_INSERT, CODE) VALUES $values";
			Application::getConnection()->query($sql);

			static::getEntity()->cleanCache();
		}
	}

	protected static function divideList(array $list, $limit = 300)
	{
		$length = count($list);
		if ($length < $limit)
		{
			return array($list);
		}

		$result = array();
		$partsCount = ceil($length / $limit);
		for ($index = 0; $index < $partsCount; $index++)
		{
			$result[$index] = array_slice($list, $limit * $index, $limit);
		}

		return $result;
	}
}
