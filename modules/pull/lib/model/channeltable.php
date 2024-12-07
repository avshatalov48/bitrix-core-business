<?php
namespace Bitrix\Pull\Model;

use Bitrix\Main;

/**
 * Class ChannelTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> CHANNEL_TYPE string(50) optional
 * <li> CHANNEL_ID string(50) mandatory
 * <li> LAST_ID int optional
 * <li> DATE_CREATE datetime mandatory
 * <li> USER reference to {@link \Bitrix\Main\UserTable}
 * </ul>
 *
 * @package Bitrix\Pull
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Channel_Query query()
 * @method static EO_Channel_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Channel_Result getById($id)
 * @method static EO_Channel_Result getList(array $parameters = array())
 * @method static EO_Channel_Entity getEntity()
 * @method static \Bitrix\Pull\Model\EO_Channel createObject($setDefaultValues = true)
 * @method static \Bitrix\Pull\Model\EO_Channel_Collection createCollection()
 * @method static \Bitrix\Pull\Model\EO_Channel wakeUpObject($row)
 * @method static \Bitrix\Pull\Model\EO_Channel_Collection wakeUpCollection($rows)
 */

class ChannelTable extends Main\Entity\DataManager
{
	use Main\ORM\Data\Internal\MergeTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_pull_channel';
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
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'CHANNEL_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateChannelType'),
			),
			'CHANNEL_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateChannelId'),
			),
			'CHANNEL_PUBLIC_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateChannelId'),
			),
			'LAST_ID' => array(
				'data_type' => 'integer',
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
				'join_type' => 'INNER',
			),
		);
	}
	/**
	 * Returns validators for CHANNEL_TYPE field.
	 *
	 * @return array
	 */
	public static function validateChannelType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for CHANNEL_ID field.
	 *
	 * @return array
	 */
	public static function validateChannelId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Return current date for DATE_CREATE field.
	 */
	public static function getCurrentDate(): Main\Type\DateTime
	{
		return new Main\Type\DateTime();
	}
}

class_alias("Bitrix\\Pull\\Model\\ChannelTable", "Bitrix\\Pull\\ChannelTable", false);