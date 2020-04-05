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