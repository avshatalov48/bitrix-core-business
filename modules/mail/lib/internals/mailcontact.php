<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class MailContactTable
 * @package Bitrix\Mail
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailContact_Query query()
 * @method static EO_MailContact_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailContact_Result getById($id)
 * @method static EO_MailContact_Result getList(array $parameters = array())
 * @method static EO_MailContact_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MailContact createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MailContact_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MailContact wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MailContact_Collection wakeUpCollection($rows)
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
	public static function addContactsBatch($contactsData): Main\Result
	{
		$result = new Main\Result();

		if (empty($contactsData))
		{
			return $result;
		}

		static $checkedUserEmails = [];

		$contactsToCheck = [];

		foreach ($contactsData as $item)
		{
			$item['EMAIL'] = trim($item['EMAIL']);
			$contactsToCheck[$item['USER_ID']][] = $item;
		}

		$alreadyAdded = [];
		$contactsToAdd = [];

		foreach ($contactsToCheck as $userId => $items)
		{
			if (!isset($checkedUserEmails[$userId]))
			{
				$checkedUserEmails[$userId] = [];
			}

			$emailsToCheck = array_diff(array_column($items, 'EMAIL'), $checkedUserEmails[$userId]);

			if (!empty($emailsToCheck))
			{
				$alreadyAdded = static::query()
					->addSelect('EMAIL', 'EMAIL')
					->addSelect('ID', 'ID')
					->addSelect('NAME', 'NAME')
					->where('USER_ID', $userId)
					->whereIn('EMAIL', $emailsToCheck)
					->exec()
					->fetchAll();
			}

			$alreadyAddedEmail = array_merge(array_column($alreadyAdded, 'EMAIL'), $checkedUserEmails[$userId]);
			$checkedUserEmails[$userId] = array_merge($checkedUserEmails[$userId], $emailsToCheck);

			foreach ($items as $item)
			{
				if (!in_array($item['EMAIL'], $alreadyAddedEmail, true))
				{
					$contactsToAdd[$item['EMAIL']] = $item;
				}
			}
		}

		if (empty($contactsToAdd))
		{
			$result->addError(new Main\Error(
				'All contacts have already been added to the database',
				'ALL_CONTACTS_ALREADY_ADDED',
				[
					'lastFound' => $alreadyAdded,
				]
			));

			return $result;
		}

		$sqlHelper = Application::getConnection()->getSqlHelper();
		$values = [];

		foreach ($contactsToAdd as $item)
		{
			$item = [
				'USER_ID' => intval($item['USER_ID']),
				'NAME' => "'" . $sqlHelper->forSql(trim($item['NAME'])) . "'",
				'ICON' => $item['ICON'] !== null ? "'" . $sqlHelper->forSql(serialize($item['ICON'])) . "'" : "''",
				'EMAIL' => "'" . $sqlHelper->forSql(trim($item['EMAIL'])) . "'",
				'ADDED_FROM' => "'" . $sqlHelper->forSql($item['ADDED_FROM']) . "'",
			];
			$values[] = implode(", ", $item);
		}

		$values = implode('), (', $values);

		$tableName = static::getTableName();

		Application::getConnection()->query($sqlHelper->getInsertIgnore($tableName, "(USER_ID, NAME, ICON, EMAIL, ADDED_FROM)", " VALUES($values)"));

		return $result;
	}

	/**
	 * @param string $email
	 * @param int $userId
	 * @return array
	 */
	public static function getContactByEmail(string $email, int $userId): array
	{
		$contact = [
			'NAME' => '',
			'ID' => 0,
		];

		$email = trim(mb_strtolower($email));

		if (!check_email($email))
		{
			return $contact;
		}

		$contactResult = self::getList(
			[
				'select' => [
					'ID',
					'NAME',
				],
				'filter' => [
					'=USER_ID' => $userId,
					'=EMAIL' => $email,
				],
			]
		)->fetch();

		if (isset($contactResult['ID']))
		{
			$contact = [
				'NAME' => $contactResult['NAME'],
				'ID' => (int) $contactResult['ID'],
			];
		}

		return $contact;
	}
}
