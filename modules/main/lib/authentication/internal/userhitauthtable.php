<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Internal;

use Bitrix\Main;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class UserHitAuthTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserHitAuth_Query query()
 * @method static EO_UserHitAuth_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserHitAuth_Result getById($id)
 * @method static EO_UserHitAuth_Result getList(array $parameters = [])
 * @method static EO_UserHitAuth_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserHitAuth createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserHitAuth wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserHitAuth_Collection wakeUpCollection($rows)
 */
class UserHitAuthTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_user_hit_auth';
	}

	public static function getMap()
	{
		return [
			(new Fields\IntegerField('ID'))
				->configurePrimary(true)
				->configureAutocomplete(true),

			(new Fields\IntegerField('USER_ID'))
				->addValidator(new Fields\Validators\ForeignValidator(Main\UserTable::getEntity()->getField('ID'))),

			(new Fields\StringField('HASH')),

			(new Fields\StringField('URL')),

			(new Fields\StringField('SITE_ID')),

			(new Fields\DatetimeField('TIMESTAMP_X')),

			(new Fields\DatetimeField('VALID_UNTIL')),

			(new Fields\Relations\Reference(
				'USER',
				Main\UserTable::class,
				Join::on('this.USER_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		];
	}
}
