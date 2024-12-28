<?php
namespace Bitrix\Socialservices;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class ContactTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional default 'CURRENT_TIMESTAMP'
 * <li> USER_ID int mandatory
 * <li> CONTACT_USER_ID int optional
 * <li> CONTACT_XML_ID int optional
 * <li> CONTACT_NAME string(255) optional
 * <li> CONTACT_LAST_NAME string(255) optional
 * <li> CONTACT_PHOTO string(255) optional
 * <li> NOTIFY bool optional default 'N'
 * </ul>
 *
 * @package Bitrix\Socialservices
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Contact_Query query()
 * @method static EO_Contact_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Contact_Result getById($id)
 * @method static EO_Contact_Result getList(array $parameters = array())
 * @method static EO_Contact_Entity getEntity()
 * @method static \Bitrix\Socialservices\EO_Contact createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialservices\EO_Contact_Collection createCollection()
 * @method static \Bitrix\Socialservices\EO_Contact wakeUpObject($row)
 * @method static \Bitrix\Socialservices\EO_Contact_Collection wakeUpCollection($rows)
 */

class ContactTable extends Main\Entity\DataManager
{
	const NOTIFY = 'Y';
	const DONT_NOTIFY = 'N';

	const NOTIFY_CONTACT_COUNT = 3;
	const NOTIFY_POSSIBLE_COUNT = 3;

	const POSSIBLE_LAST_AUTHORIZE_LIMIT = '-1 weeks';
	const POSSIBLE_RESET_TIME = 2592000; // 86400 * 30
	const POSSIBLE_RESET_TIME_KEY = "_ts";

	protected static $notifyStack = array();

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_socialservices_contact';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'CONTACT_USER_ID' => array(
				'data_type' => 'integer',
			),
			'CONTACT_XML_ID' => array(
				'data_type' => 'integer',
			),
			'CONTACT_NAME' => array(
				'data_type' => 'string',
			),
			'CONTACT_LAST_NAME' => array(
				'data_type' => 'string',
			),
			'CONTACT_PHOTO' => array(
				'data_type' => 'string',
			),
			'LAST_AUTHORIZE' => array(
				'data_type' => 'datetime',
			),
			'NOTIFY' => array(
				'data_type' => 'boolean',
				'values' => array(static::DONT_NOTIFY, static::NOTIFY),
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'CONTACT_USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.CONTACT_USER_ID' => 'ref.ID'),
			),
		);
	}

	public static function onBeforeUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult();
		$data = $event->getParameter("fields");

		if(!isset($data['TIMESTAMP_X']))
		{
			$data['TIMESTAMP_X'] = new DateTime();
			$result->modifyFields($data);
		}
	}

	public static function getConnectId($connect)
	{
		return $connect["CONNECT_TYPE"].$connect["CONTACT_PROFILE_ID"];
	}
}