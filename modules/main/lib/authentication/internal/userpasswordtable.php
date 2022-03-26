<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */

namespace Bitrix\Main\Authentication\Internal;

use Bitrix\Main;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;

/**
 * Class UserPasswordTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserPassword_Query query()
 * @method static EO_UserPassword_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserPassword_Result getById($id)
 * @method static EO_UserPassword_Result getList(array $parameters = [])
 * @method static EO_UserPassword_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserPassword createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserPassword wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserPassword_Collection wakeUpCollection($rows)
 */
class UserPasswordTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_user_password';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField("ID"))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new Fields\IntegerField("USER_ID"))
				->addValidator(new Fields\Validators\ForeignValidator(Main\UserTable::getEntity()->getField('ID'))),

			(new Fields\StringField("PASSWORD")),

			(new Fields\DatetimeField("DATE_CHANGE")),

			(new Fields\Relations\Reference(
				'USER',
				Main\UserTable::class,
				Join::on('this.USER_ID', 'ref.ID')
			))->configureJoinType('inner'),
		];
	}

	public static function deleteByFilter(array $filter)
	{
		$entity = static::getEntity();

		$where = Main\ORM\Query\Query::buildFilterSql($entity, $filter);

		if($where <> '')
		{
			$where = " WHERE ".$where;
		}
		else
		{
			throw new Main\ArgumentException("Deleting by empty filter is not allowed, use truncate (b_user_password).", "filter");
		}

		$entity->getConnection()->queryExecute("delete from b_user_password".$where);

		$entity->cleanCache();
	}

	public static function passwordExpired($userId, $days)
	{
		$date = new Main\Type\DateTime();
		$date->add("-{$days}D");

		$prevPassword = static::query()
			->where("USER_ID", $userId)
			->where("DATE_CHANGE", ">", $date)
			->setLimit(1)
			->fetch();

		return !$prevPassword;
	}

	public static function getUserPasswords($userId, $limit)
	{
		return static::query()
			->addSelect("PASSWORD")
			->where("USER_ID", $userId)
			->addOrder("ID", "DESC")
			->setLimit($limit)
			->fetchAll();
	}
}
