<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Mail\Internals\Entity\UserSignature;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Entity;

/**
 * Class UserSignatureTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserSignature_Query query()
 * @method static EO_UserSignature_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_UserSignature_Result getById($id)
 * @method static EO_UserSignature_Result getList(array $parameters = array())
 * @method static EO_UserSignature_Entity getEntity()
 * @method static \Bitrix\Mail\Internals\Entity\UserSignature createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\Internals\EO_UserSignature_Collection createCollection()
 * @method static \Bitrix\Mail\Internals\Entity\UserSignature wakeUpObject($row)
 * @method static \Bitrix\Mail\Internals\EO_UserSignature_Collection wakeUpCollection($rows)
 */
class UserSignatureTable extends DataManager
{
	const TYPE_ADDRESS = 'address';
	const TYPE_SENDER = 'sender';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_mail_user_signature';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new Entity\IntegerField('USER_ID', [
				'required' => true,
			]),
			new Entity\TextField('SIGNATURE'),
			new Entity\StringField('SENDER'),
		];
	}

	/**
	 * @return \Bitrix\Main\ORM\Objectify\EntityObject|string
	 */
	public static function getObjectClass()
	{
		return UserSignature::class;
	}
}