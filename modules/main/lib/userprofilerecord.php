<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\Entity;

class UserProfileRecordTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_user_profile_record';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField("ID", array(
				'primary' => true,
				'autocomplete' => true,
			)),
			new Entity\IntegerField("HISTORY_ID", array(
				'required' => true,
			)),
			new Entity\StringField("FIELD"),
			new Entity\TextField('DATA', array(
				'serialized' => true
			)),
			new Entity\ReferenceField("HISTORY",
				'\Bitrix\Main\UserProfileHistoryTable',
				array('=this.HISTORY_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
		);
	}

	public static function deleteByUser($userId)
	{
		$userId = intval($userId);

		$entity = static::getEntity();
		$conn = $entity->getConnection();

		$conn->queryExecute("
			DELETE FROM b_user_profile_record 
			WHERE HISTORY_ID IN(
				SELECT ID FROM b_user_profile_history 
				WHERE USER_ID = {$userId}
			)"
		);

		$entity->cleanCache();
	}
}
