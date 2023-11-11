<?php

namespace Bitrix\Mail;

use Bitrix\Mail\Internals\Entity\BlacklistEmail;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization;
use Bitrix\Main\ORM\Query\Query;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class BlacklistTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Blacklist_Query query()
 * @method static EO_Blacklist_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Blacklist_Result getById($id)
 * @method static EO_Blacklist_Result getList(array $parameters = array())
 * @method static EO_Blacklist_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\Entity\BlacklistEmail createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\EO_Blacklist_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\Entity\BlacklistEmail wakeUpObject($row)
 * @method static \Bitrix\Mail\EO_Blacklist_Collection wakeUpCollection($rows)
 */
class BlacklistTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_blacklist';
	}

	/**
	 * @return \Bitrix\Main\ORM\Objectify\EntityObject|string
	 */
	public static function getObjectClass()
	{
		return BlacklistEmail::class;
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'SITE_ID' => [
				'data_type' => 'string',
				'required' => true,
			],
			'MAILBOX_ID' => [
				'data_type' => 'integer',
			],
			'USER_ID' => [
				'data_type' => 'integer',
			],
			'ITEM_TYPE' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'ITEM_VALUE' => [
				'data_type' => 'string',
				'required' => true,
				'fetch_data_modification' => function ()
				{
					return [
						function ($value, $query, $data)
						{
							if (Blacklist\ItemType::DOMAIN == $data['ITEM_TYPE'])
							{
								$value = sprintf('@%s', $value);
							}

							return $value;
						},
					];
				},
			],
		];
	}

	public static function replace($siteId, $mailboxId, array $list)
	{
		global $DB;

		if ($mailboxId > 0)
		{
			$DB->query(sprintf("DELETE FROM b_mail_blacklist WHERE MAILBOX_ID = %u", $mailboxId));
		}
		else
		{
			$DB->query(sprintf("DELETE FROM b_mail_blacklist WHERE SITE_ID = '%s' AND MAILBOX_ID = 0", $DB->forSql($siteId)));
		}

		if (!empty($list))
		{
			foreach ($list as $item)
			{
				static::add([
					'SITE_ID' => $siteId,
					'MAILBOX_ID' => $mailboxId,
					'ITEM_TYPE' => Blacklist\ItemType::resolveByValue($item),
					'ITEM_VALUE' => $item,
				]);
			}
		}
	}

	/**
	 * @param array $list
	 * @param array $mailbox
	 * @return int
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 */
	public static function addMailsBatch(array $list, $userId = null)
	{
		if (empty($list))
		{
			return 0;
		}
		if (is_null($userId))
		{
			$userId = 0;
		}
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$addList = [];
		foreach ($list as $index => $item)
		{
			$itemToAdd = [
				'SITE_ID' => SITE_ID,
				'MAILBOX_ID' => 0,
				'USER_ID' => $userId,
				'ITEM_TYPE' => Blacklist\ItemType::resolveByValue($item),
				'ITEM_VALUE' => $item,
			];
			$addList[] = $itemToAdd;
		}

		if (count($addList) === 0)
		{
			return 0;
		}
		$keys = implode(', ', array_keys(current($addList)));
		$values = [];
		foreach ($addList as $item)
		{
			$values[] = implode(
				", ",
				[
					"'" . $sqlHelper->forSql($item['SITE_ID']) . "'",
					(int)$item['MAILBOX_ID'],
					(int)$item['USER_ID'],
					$item['ITEM_TYPE'],
					"'" . $sqlHelper->forSql($item['ITEM_VALUE']) . "'",
				]
			);
		}
		$values = implode('), (', $values);

		$tableName = static::getTableName();
		$sql = $sqlHelper->getInsertIgnore($tableName, "($keys)", " VALUES($values)");
		Application::getConnection()->query($sql);
		return Application::getConnection()->getAffectedRowsCount();
	}

	/**
	 * @param $userId
	 * @param bool $includeAddressesForAllUsers
	 * @return \Bitrix\Main\ORM\Query\Query
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getUserAddressesListQuery($userId, $includeAddressesForAllUsers = true)
	{
		$filter = ['LOGIC' => 'OR'];
		$userMailboxes = \Bitrix\Mail\MailboxTable::getUserMailboxes();
		if (!empty($userMailboxes))
		{
			$mailboxesIds = array_column($userMailboxes, 'ID');
			if ($includeAddressesForAllUsers)
			{
				$mailboxesIds[] = 0;
			}
			$filter[] = [
				'@MAILBOX_ID' => $mailboxesIds,
				'=USER_ID' => 0,
			];
		}
		$newStyleAddressesFilter = [];
		$userIds = [$userId];
		$newStyleAddressesFilter['=MAILBOX_ID'] = 0;

		if ($includeAddressesForAllUsers)
		{
			$userIds[] = 0;
		}
		$newStyleAddressesFilter['@USER_ID'] = $userIds;
		$mailsQuery = \Bitrix\Mail\BlacklistTable::query()
			->addSelect('*');
		$filter[] = $newStyleAddressesFilter;
		return $mailsQuery->setFilter([$filter]);
	}

	public static function deleteList($filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();
		return $connection->query(sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql(
				$entity,
				$filter
			)
		));
	}
}
