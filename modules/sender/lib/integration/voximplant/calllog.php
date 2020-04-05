<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Integration\VoxImplant;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;

Loc::loadMessages(__FILE__);

/**
 * Class CallLogTable
 * @package Bitrix\Sender\Integration\VoxImplant
 */
class CallLogTable extends Entity\DataManager
{
	/**
	 * Get recipient id by call id.
	 *
	 * @return integer|null
	 */
	public static function getRecipientIdByCallId($callId)
	{
		$row = static::getRowById(array('CALL_ID' => $callId));
		return $row ? $row['RECIPIENT_ID'] : null;
	}

	/**
	 * Get actual call count.
	 *
	 * @return int
	 */
	public static function getActualCallCount()
	{
		return static::getCount(
			array('>DATE_INSERT' => static::getFilterCurrentDate()),
			array('ttl' => 900)
		);
	}

	protected static function getFilterCurrentDate()
	{
		$dateTime = new Type\DateTime();
		return $dateTime->add('-15 minutes');
	}

	/**
	 * Remove by call id.
	 *
	 * @param integer $callId Call ID.
	 * @return void
	 */
	public static function removeByCallId($callId = null)
	{
		$list = static::getList(array(
			'select' => array('CALL_ID'),
			'filter' => array(
				'LOGIC' => 'OR',
				'=CALL_ID' => $callId,
				'<DATE_INSERT' => static::getFilterCurrentDate(),
			)
		));
		foreach ($list as $item)
		{
			static::delete(array('CALL_ID' => $item['CALL_ID']));
		}
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_call_log';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CALL_ID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'RECIPIENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new Type\DateTime(),
			),
		);
	}
}