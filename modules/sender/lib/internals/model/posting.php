<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main;
use Bitrix\Sender;

/**
 * Class PostingTable
 *
 * @package Bitrix\Sender\Internals\Model
 */
class PostingTable extends Main\Entity\DataManager
{
	const STATUS_NEW = 'N';
	const STATUS_PART = 'P';
	const STATUS_SENT = 'S';
	const STATUS_SENT_WITH_ERRORS = 'E';
	const STATUS_ABORT = 'A';

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting';
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
			'CAMPAIGN_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'LETTER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new Main\Type\DateTime(),
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new Main\Type\DateTime(),
			),
			'STATUS' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => self::STATUS_NEW,
			),
			'DATE_SEND' => array(
				'data_type' => 'datetime',
			),
			'DATE_PAUSE' => array(
				'data_type' => 'datetime',
			),
			'DATE_SENT' => array(
				'data_type' => 'datetime',
			),
			'COUNT_READ' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'COUNT_CLICK' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'COUNT_UNSUB' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'COUNT_SEND_ALL' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'COUNT_SEND_NONE' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'COUNT_SEND_ERROR' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'COUNT_SEND_SUCCESS' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'COUNT_SEND_DENY' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'LETTER' => array(
				'data_type' => LetterTable::class,
				'reference' => array('=this.MAILING_CHAIN_ID' => 'ref.ID'),
			),
			'MAILING' => array(
				'data_type' => Sender\MailingTable::class,
				'reference' => array('=this.MAILING_ID' => 'ref.ID'),
			),
			'MAILING_CHAIN' => array(
				'data_type' => Sender\MailingChainTable::class,
				'reference' => array('=this.MAILING_CHAIN_ID' => 'ref.ID'),
			),
			'POSTING_RECIPIENT' => array(
				'data_type' => Posting\RecipientTable::class,
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
			'POSTING_READ' => array(
				'data_type' => Posting\ReadTable::class,
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
			'POSTING_CLICK' => array(
				'data_type' => Posting\ClickTable::class,
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
			'POSTING_UNSUB' => array(
				'data_type' => Posting\UnsubTable::class,
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
		);
	}

	/**
	 * Handler of event `onDelete`.
	 *
	 * @param Main\Entity\Event $event Event.
	 * @return Main\Entity\EventResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onDelete(Main\Entity\Event $event)
	{
		$result = new Main\Entity\EventResult;
		$data = $event->getParameters();


		$listId = array();
		if(array_key_exists('ID', $data['primary']))
		{
			$listId[] = $data['primary']['ID'];
		}
		else
		{
			$filter = array();
			foreach($data['primary'] as $primKey => $primVal)
			{
				$filter[$primKey] = $primVal;
			}

			$tableDataList = static::getList(array(
				'select' => array('ID'),
				'filter' => $filter
			));
			while($tableData = $tableDataList->fetch())
			{
				$listId[] = $tableData['ID'];
			}

		}

		foreach($listId as $primaryId)
		{
			$primary = array('POSTING_ID' => $primaryId);
			Sender\PostingReadTable::delete($primary);
			Sender\PostingClickTable::delete($primary);
			Sender\PostingUnsubTable::delete($primary);
			Sender\PostingRecipientTable::delete($primary);
		}


		return $result;
	}
}