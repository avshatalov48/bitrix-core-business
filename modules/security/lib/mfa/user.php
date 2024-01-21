<?php

namespace Bitrix\Security\Mfa;

use Bitrix\Main\ORM;
use Bitrix\Main\Type;

/**
 * Class UserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_User_Query query()
 * @method static EO_User_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_User_Result getById($id)
 * @method static EO_User_Result getList(array $parameters = [])
 * @method static EO_User_Entity getEntity()
 * @method static \Bitrix\Security\Mfa\EO_User createObject($setDefaultValues = true)
 * @method static \Bitrix\Security\Mfa\EO_User_Collection createCollection()
 * @method static \Bitrix\Security\Mfa\EO_User wakeUpObject($row)
 * @method static \Bitrix\Security\Mfa\EO_User_Collection wakeUpCollection($rows)
 */
class UserTable	extends ORM\Data\DataManager
{
	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sec_user';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => '\Bitrix\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
				'join_type' => 'INNER',
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default' => 'N'
			),
			'SECRET' => array(
				'data_type' => 'string',
				'format' => '#^[a-z0-9]{0,64}$#iD'
			),
			'PARAMS' => array(
				'data_type' => 'text'
			),
			'TYPE' => array(
				'data_type' => 'string',
				'values' => array(Otp::TYPE_TOTP, Otp::TYPE_HOTP),
				'default' => Otp::TYPE_DEFAULT
			),
			'ATTEMPTS' => array(
				'data_type' => 'integer',
				'default' => 0
			),
			'INITIAL_DATE' => array(
				'data_type' => 'datetime',
				'default' => new Type\DateTime
			),
			'SKIP_MANDATORY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default' => 'N'
			),
			'DEACTIVATE_UNTIL' => array(
				'data_type' => 'datetime'
			),
			(new ORM\Fields\ArrayField('INIT_PARAMS')),
		);
	}

	/**
	 * Clear recovery codes after delete user
	 *
	 * @param ORM\Event $event Our event.
	 * @return void
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public static function onAfterDelete(ORM\Event $event)
	{
		$primary = $event->getParameter('primary');
		RecoveryCodesTable::clearByUser($primary['USER_ID']);
	}
}
