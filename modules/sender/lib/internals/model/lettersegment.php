<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class LetterSegmentTable
 * @package Bitrix\Sender
 */
class LetterSegmentTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_chain_group';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'LETTER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'column_name' => 'CHAIN_ID',
			),
			'SEGMENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'column_name' => 'GROUP_ID',
			),
			'INCLUDE' => array(
				'data_type' => 'boolean',
				'values' => array(false, true),
				'required' => true,
			),
			'LETTER' => array(
				'data_type' => 'Bitrix\Sender\MailingChainTable',
				'reference' => array('=this.LETTER_ID' => 'ref.ID'),
			),
			'SEGMENT' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.SEGMENT_ID' => 'ref.ID'),
			),
		);
	}
}