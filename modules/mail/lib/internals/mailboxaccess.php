<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Main\Entity;

/**
 * Class MailboxAccessTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailboxAccess_Query query()
 * @method static EO_MailboxAccess_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailboxAccess_Result getById($id)
 * @method static EO_MailboxAccess_Result getList(array $parameters = array())
 * @method static EO_MailboxAccess_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_MailboxAccess_Collection wakeUpCollection($rows)
 */
class MailboxAccessTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_mailbox_access';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type'    => 'integer',
				'primary'      => true,
				'autocomplete' => true,
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'TASK_ID' => array(
				'data_type' => 'integer',
			),
			'ACCESS_CODE' => array(
				'data_type' => 'string',
				'required'  => true,
			),
		);
	}

	/**
	 * @param int $mailboxId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getUserIdsWithAccessToTheMailbox(int $mailboxId): array
	{
		$accesses = self::getList([
			'filter' => [
				'=MAILBOX_ID' => $mailboxId,
				'TASK_ID' => 0,
			],
		]);

		$userIds = [];

		while ($item = $accesses->fetch())
		{
			if (preg_match('/^(U)(\d+)$/', $item['ACCESS_CODE'], $matches))
			{
				if ('U' == $matches[1])
				{
					$userIds[] = (int)$matches[2];
				}
			}
		}
		return $userIds;
	}

	/**
	 * @param int $mailboxId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getUsersDataWithAccessToTheMailbox(int $mailboxId): array
	{
		$userIds = self::getUserIdsWithAccessToTheMailbox($mailboxId);
		if (empty($userIds))
		{
			return [];
		}
		$users = \Bitrix\Main\UserTable::getList([
			'select' => [
				'ID',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'LOGIN',
			],
			'filter' => [
				'@ID' => $userIds,
			],
		]);

		$userCards = [];

		while ($user = $users->fetch())
		{
			$userCards[] = [
				'id' => (int) $user['ID'],
				'name' => trim(\CUser::formatName(\CSite::getNameFormat(), $user, true, false)),
			];
		}
		return $userCards;
	}

	/**
	 * Get mailbox users data by name
	 * There may be namesakes
	 *
	 * @param int $mailboxId
	 * @param $name
	 * @return array
	 */
	public static function getUsersDataByName(int $mailboxId, $name): array
	{
		$usersData = self::getUsersDataWithAccessToTheMailbox($mailboxId);
		$foundUsers = [];

		foreach ($usersData as $user)
		{
			if ($user['name'] === trim($name))
			{
				$foundUsers[] = $user;
			}
		}

		return $foundUsers;
	}

	/**
	 * @param int $mailboxId
	 * @param int $userId
	 * @return array
	 */
	public static function getUserDataById(int $mailboxId, int $userId): array
	{
		$usersData = self::getUsersDataWithAccessToTheMailbox($mailboxId);
		$foundUser = [];

		foreach ($usersData as $user)
		{
			if ($user['id'] === $userId)
			{
				$foundUser = $user;
				break;
			}
		}
		return $foundUser;
	}

}
