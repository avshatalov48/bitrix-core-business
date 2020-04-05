<?php

namespace Bitrix\Mail\Internals;

use Bitrix\Mail\Internals\Entity\UserSignature;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Entity;

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