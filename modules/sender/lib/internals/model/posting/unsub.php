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
 * Class UnsubTable
 *
 * @package Bitrix\Sender\Internals\Model\Posting
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Unsub_Query query()
 * @method static EO_Unsub_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Unsub_Result getById($id)
 * @method static EO_Unsub_Result getList(array $parameters = array())
 * @method static EO_Unsub_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Unsub createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Unsub wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Unsub_Collection wakeUpCollection($rows)
 */
class UnsubTable extends Main\Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_unsub';
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
			'POSTING' => array(
				'data_type' => Model\PostingTable::class,
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
			'POSTING_RECIPIENT' => array(
				'data_type' => RecipientTable::class,
				'reference' => array('=this.RECIPIENT_ID' => 'ref.ID'),
			),
		);
	}
}