<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model\Posting;

use Bitrix\Main;
use Bitrix\Sender;
use Bitrix\Sender\Internals\Model;

/**
 * Class RecipientTable
 *
 * @package Bitrix\Sender\Internals\Model\Posting
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Recipient_Query query()
 * @method static EO_Recipient_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Recipient_Result getById($id)
 * @method static EO_Recipient_Result getList(array $parameters = array())
 * @method static EO_Recipient_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Recipient createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Recipient wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\Posting\EO_Recipient_Collection wakeUpCollection($rows)
 */
class RecipientTable extends Main\Entity\DataManager
{
	const SEND_RESULT_NONE = 'Y';
	const SEND_RESULT_SUCCESS = 'N';
	const SEND_RESULT_ERROR = 'E';
	const SEND_RESULT_WAIT = 'W';
	const SEND_RESULT_DENY = 'D';
	const SEND_RESULT_WAIT_ACCEPT = 'A';

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_recipient';
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
			'STATUS' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => static::SEND_RESULT_NONE,
			),
			'DATE_SENT' => array(
				'data_type' => 'datetime',
			),
			'DATE_DENY' => array(
				'data_type' => 'datetime',
			),
			'CONTACT_ID' => array(
				'required' => true,
				'data_type' => 'integer',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'FIELDS' => array(
				'data_type' => 'text',
				'serialized' => true,
			),
			'ROOT_ID' => array(
				'data_type' => 'integer',
			),
			'IS_READ' => array(
				'data_type' => 'string',
			),
			'IS_CLICK' => array(
				'data_type' => 'string',
			),
			'IS_UNSUB' => array(
				'data_type' => 'string',
			),
			'CONTACT' => array(
				'data_type' => Sender\ContactTable::class,
				'reference' => array('=this.CONTACT_ID' => 'ref.ID'),
			),
			'POSTING' => array(
				'data_type' => Model\PostingTable::class,
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
			'POSTING_READ' => array(
				'data_type' => ReadTable::class,
				'reference' => array('=this.ID' => 'ref.RECIPIENT_ID'),
			),
			'POSTING_CLICK' => array(
				'data_type' => ClickTable::class,
				'reference' => array('=this.ID' => 'ref.RECIPIENT_ID'),
			),
			'POSTING_UNSUB' => array(
				'data_type' => UnsubTable::class,
				'reference' => array('=this.ID' => 'ref.RECIPIENT_ID'),
			),
		);
	}
}