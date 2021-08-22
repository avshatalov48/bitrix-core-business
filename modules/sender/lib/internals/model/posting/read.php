<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model\Posting;

use Bitrix\Main;
use Bitrix\Sender\Internals\Model;


/**
 * Class ReadTable
 *
 * @package Bitrix\Sender\Internals\Model\Posting
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Read_Query query()
 * @method static EO_Read_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Read_Result getById($id)
 * @method static EO_Read_Result getList(array $parameters = array())
 * @method static EO_Read_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Read createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Read wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Read_Collection wakeUpCollection($rows)
 */
class ReadTable extends Main\Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_read';
	}

	/**
	 * Get map.
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
			'POSTING_ID' => array(
				'data_type' => 'integer',
			),
			'RECIPIENT_ID' => array(
				'data_type' => 'integer',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new Main\Type\DateTime(),
			),
		);
	}
}