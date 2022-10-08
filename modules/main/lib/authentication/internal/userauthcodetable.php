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
 * Class UserAuthCodeTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserAuthCode_Query query()
 * @method static EO_UserAuthCode_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserAuthCode_Result getById($id)
 * @method static EO_UserAuthCode_Result getList(array $parameters = [])
 * @method static EO_UserAuthCode_Entity getEntity()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserAuthCode createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection createCollection()
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserAuthCode wakeUpObject($row)
 * @method static \Bitrix\Main\Authentication\Internal\EO_UserAuthCode_Collection wakeUpCollection($rows)
 */
class UserAuthCodeTable extends Data\DataManager
{
	use Data\Internal\DeleteByFilterTrait;

	const TYPE_EMAIL = 'email';

	public static function getTableName()
	{
		return 'b_user_auth_code';
	}

	public static function getMap()
	{
		return array(
			new Fields\IntegerField("USER_ID", array(
				'primary' => true,
				'validation' => function()
				{
					return [
						new Fields\Validators\ForeignValidator(Main\UserTable::getEntity()->getField('ID')),
					];
				}
			)),

			new Fields\StringField("CODE_TYPE", array(
				'default_value' => self::TYPE_EMAIL,
				'primary' => true,
			)),

			new Fields\SecretField("OTP_SECRET", array(
				'crypto_enabled' => static::cryptoEnabled("OTP_SECRET"),
			)),

			new Fields\IntegerField("ATTEMPTS", array(
				"default_value" => 0,
			)),

			new Fields\DatetimeField("DATE_SENT"),

			new Fields\DatetimeField("DATE_RESENT"),

			(new Fields\Relations\Reference(
				'USER',
				Main\UserTable::class,
				Join::on('this.USER_ID', 'ref.ID')
			))->configureJoinType('inner'),
		);
	}
}
