<?php
namespace Bitrix\Socialservices;

use Bitrix\Main,
	Bitrix\Main\Entity;

/**
 * Class ZoomMeetingTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENTITY_TYPE_ID string(10) mandatory
 * <li> ENTITY_ID int mandatory
 * <li> CONFERENCE_URL string(255) mandatory
 * <li> CONFERENCE_EXTERNAL_ID string(32) mandatory
 * <li> CONFERENCE_PASSWORD string(32) mandatory
 * <li> JOINED string(1) optional
 * <li> CONFERENCE_CREATED datetime optional
 * <li> CONFERENCE_ENDED datetime optional
 * </ul>
 *
 * @package Bitrix\Socialservices
 **/

class ZoomMeetingTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_socialservices_zoom_meeting';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap(): array
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new Entity\StringField('ENTITY_TYPE_ID', array(
				'required' => true,
				'size' => 10,
			)),
			new Entity\IntegerField('ENTITY_ID', array(
				'required' => true
			)),
			new Entity\StringField('CONFERENCE_URL', array(
				'required' => true,
				'size' => 255,
			)),
			new Entity\StringField('CONFERENCE_EXTERNAL_ID', array(
				'required' => true,
				'size' => 32,
			)),
			new Entity\StringField('CONFERENCE_PASSWORD', array(
				'required' => true,
				'size' => 32,
			)),
			new Entity\BooleanField('JOINED', array(
				'values' => array('N', 'Y')
			)),
			new Entity\DatetimeField('CONFERENCE_CREATED', array()),
			new Entity\DatetimeField('CONFERENCE_ENDED', array()),
		);
	}
}