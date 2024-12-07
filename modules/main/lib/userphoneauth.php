<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Localization\Loc;

/**
 * Class UserPhoneAuthTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserPhoneAuth_Query query()
 * @method static EO_UserPhoneAuth_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserPhoneAuth_Result getById($id)
 * @method static EO_UserPhoneAuth_Result getList(array $parameters = [])
 * @method static EO_UserPhoneAuth_Entity getEntity()
 * @method static \Bitrix\Main\EO_UserPhoneAuth createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_UserPhoneAuth_Collection createCollection()
 * @method static \Bitrix\Main\EO_UserPhoneAuth wakeUpObject($row)
 * @method static \Bitrix\Main\EO_UserPhoneAuth_Collection wakeUpCollection($rows)
 */
class UserPhoneAuthTable extends Data\DataManager
{
	public static function getTableName()
	{
		return 'b_user_phone_auth';
	}

	public static function getMap()
	{
		return array(
			new Fields\IntegerField("USER_ID", array(
				'primary' => true,
			)),

			new Fields\StringField("PHONE_NUMBER", array(
				'validation' => function() {
					return array(
						new Fields\Validators\LengthValidator(1, null, ["MIN" => Loc::getMessage("user_phone_auth_err_number")]),
						array(__CLASS__, 'validatePhoneNumber'),
						new Fields\Validators\UniqueValidator(Loc::getMessage("user_phone_auth_err_duplicte")),
					);
				}
			)),

			new Fields\SecretField("OTP_SECRET", array(
				'crypto_enabled' => static::cryptoEnabled("OTP_SECRET"),
			)),

			new Fields\IntegerField("ATTEMPTS", array(
				"default_value" => 0,
			)),

			new Fields\BooleanField("CONFIRMED", array(
				"values" => array("N", "Y"),
				"default_value" => "N",
			)),

			new Fields\DatetimeField("DATE_SENT"),

			(new Fields\Relations\Reference(
				'USER',
				UserTable::class,
				Join::on('this.USER_ID', 'ref.ID')
			))->configureJoinType('inner'),
		);
	}

	/**
	 * @param string $value
	 * @return bool|string
	 */
	public static function validatePhoneNumber($value)
	{
		$phoneNumber = PhoneNumber\Parser::getInstance()->parse($value);
		if($phoneNumber->isValid())
		{
			return true;
		}
		else
		{
			return Loc::getMessage("user_phone_auth_err_incorrect_number");
		}
	}

	public static function onBeforeAdd(ORM\Event $event)
	{
		return static::modifyFields($event);
	}

	public static function onBeforeUpdate(ORM\Event $event)
	{
		return static::modifyFields($event);
	}

	protected static function modifyFields(ORM\Event $event)
	{
		$fields = $event->getParameter('fields');
		$result = new ORM\EventResult();
		$modifiedFields = array();

		if(isset($fields["PHONE_NUMBER"]))
		{
			//normalize the number
			$modifiedFields["PHONE_NUMBER"] = static::normalizePhoneNumber($fields["PHONE_NUMBER"]);
		}

		$result->modifyFields($modifiedFields);

		return $result;
	}

	public static function normalizePhoneNumber($number, $defaultCountry = '')
	{
		$phoneNumber = PhoneNumber\Parser::getInstance()->parse($number, $defaultCountry);
		return $phoneNumber->format(PhoneNumber\Format::E164);
	}
}
