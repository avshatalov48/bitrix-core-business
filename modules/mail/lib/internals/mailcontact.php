<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class MailContactTable
 * @package Bitrix\Mail
 */
class MailContactTable extends Entity\DataManager
{
	const ADDED_TYPE_FROM = 'FROM';
	const ADDED_TYPE_CC   = 'CC';
	const ADDED_TYPE_BCC  = 'BCC';
	const ADDED_TYPE_TO   = 'TO';
	const ADDED_TYPE_REPLY_TO   = 'REPLY_TO';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_contact';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'EMAIL' => [
				'data_type' => 'string',
			],
			'NAME' => [
				'data_type' => 'string',
			],
			'ICON' => [
				'data_type' => 'string',
				'serialized' => true,
			],
			'FILE_ID' => [
				'data_type' => 'integer',
			],
			'USER_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'ADDED_FROM' => [
				'data_type' => 'string',
			],
		];
	}

	/**
	 * @param $contactsData
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function addContactsBatch($contactsData)
	{
		if (empty($contactsData))
		{
			return;
		}
		$contactsToCheck = [];
		foreach ($contactsData as $index => $item)
		{
			$item['EMAIL'] = trim($item['EMAIL']);
			$contactsToCheck[$item['USER_ID']][] = $item;
		}
		foreach ($contactsToCheck as $userId => $items)
		{
			$alreadyAdded = static::query()
				->addSelect('EMAIL', 'EMAIL')
				->where('USER_ID', $userId)
				->whereIn('EMAIL', array_column($items, 'EMAIL'))
				->exec()
				->fetchAll();
			$alreadyAdded = array_column($alreadyAdded, 'EMAIL');
			foreach ($items as $item)
			{
				if (!in_array($item['EMAIL'], $alreadyAdded, true))
				{
					$contactsToAdd[$item['EMAIL']] = $item;
				}
			}
		}
		if (empty($contactsToAdd))
		{
			return;
		}
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$values = [];
		foreach ($contactsToAdd as $item)
		{
			$item = [
				'USER_ID' => intval($item['USER_ID']),
				'NAME' => "'" . $sqlHelper->forSql(trim($item['NAME'])) . "'",
				'ICON' => "'" . $sqlHelper->forSql(serialize($item['ICON'])) . "'",
				'EMAIL' => "'" . $sqlHelper->forSql(trim($item['EMAIL'])) . "'",
				'ADDED_FROM' => "'" . $sqlHelper->forSql($item['ADDED_FROM']) . "'",
			];
			$values[] = implode(", ", $item);
		}
		$keys = implode(', ', array_keys(reset($contactsToAdd)));
		$values = implode('), (', $values);

		$tableName = static::getTableName();
		$sql = "INSERT IGNORE $tableName($keys) VALUES($values)";
		Application::getConnection()->query($sql);
	}
}