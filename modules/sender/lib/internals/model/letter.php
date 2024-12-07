<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Sender;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Sender\FileTable;
use Bitrix\Sender\MailingChainTable;
use Bitrix\Sender\Message\iBase;

Loc::loadMessages(__FILE__);

/**
 * Class LetterTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Letter_Query query()
 * @method static EO_Letter_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Letter_Result getById($id)
 * @method static EO_Letter_Result getList(array $parameters = array())
 * @method static EO_Letter_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_Letter createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_Letter_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_Letter wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_Letter_Collection wakeUpCollection($rows)
 */
class LetterTable extends Entity\DataManager
{
	const STATUS_NEW = 'N';
	const STATUS_READY = 'R';
	const STATUS_SEND = 'S';
	const STATUS_PAUSE = 'P';
	const STATUS_WAIT = 'W';
	const STATUS_HALT = 'H';
	const STATUS_PLAN = 'T';
	const STATUS_END = 'Y';
	const STATUS_CANCEL = 'C';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_chain';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'CAMPAIGN_ID' => array(
				'data_type' => 'integer',
				'column_name' => 'MAILING_ID',
				'required' => true,
			),

			'MESSAGE_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => function ()
				{
					return iBase::CODE_MAIL;
				},
			),
			'MESSAGE_ID' => array(
				'data_type' => 'string',
			),

			'TEMPLATE_TYPE' => array(
				'data_type' => 'string',
			),
			'TEMPLATE_ID' => array(
				'data_type' => 'string',
			),

			'POSTING_ID' => array(
				'data_type' => 'integer',
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
			),
			'UPDATED_BY' => array(
				'data_type' => 'integer',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new Type\DateTime(),
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'default_value' => new Type\DateTime(),
			),
			'STATUS' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => static::STATUS_NEW,
			),
			'REITERATE' => array(
				'data_type' => 'boolean',
				'default_value' => 'N',
				'values' => array('N', 'Y')
			),
			'IS_TRIGGER' => array(
				'data_type' => 'boolean',
				'default_value' => 'N',
				'values' => array('N', 'Y')
			),
			'IS_ADS' => array(
				'data_type' => 'boolean',
				'default_value' => 'N',
				'values' => array('N', 'Y')
			),
			'LAST_EXECUTED' => array(
				'data_type' => 'datetime',
			),
			'TITLE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_TITLE1'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),

			'AUTO_SEND_TIME' => array(
				'data_type' => 'datetime',
			),
			'DAYS_OF_WEEK' => array(
				'data_type' => 'string',
			),
			'DAYS_OF_MONTH' => array(
				'data_type' => 'string',
			),
			'MONTHS_OF_YEAR' => array(
				'data_type' => 'string',
			),
			'TIMES_OF_DAY' => array(
				'data_type' => 'string',
			),
			'TIME_SHIFT' => array(
				'data_type' => 'integer',
			),

			'ERROR_MESSAGE' => array(
				'data_type' => 'string',
			),

			'SEARCH_CONTENT' => array(
				'data_type' => 'text',
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),

			'MESSAGE' => array(
				'data_type' => MessageTable::class,
				'reference' => array('=this.MESSAGE_ID' => 'ref.ID'),
			),
			'CAMPAIGN' => array(
				'data_type' => 'Bitrix\Sender\MailingTable',
				'reference' => array('=this.CAMPAIGN_ID' => 'ref.ID'),
			),
			'CURRENT_POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
			'POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.ID' => 'ref.MAILING_CHAIN_ID'),
			),
			'CREATED_BY_USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.CREATED_BY' => 'ref.ID'),
			),
			'WAITING_RECIPIENT' => array(
				'data_type' => 'boolean',
				'default_value' => 'N',
				'values' => array('N', 'Y')
			),
		);
	}


	/**
	 * After add event handler.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onAfterAdd(Entity\Event $event)
	{
		return MailingChainTable::onAfterAdd($event);
	}

	/**
	 * On after update event handler.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onBeforeUpdate(Entity\Event $event)
	{
		return MailingChainTable::onBeforeUpdate($event);
	}

	/**
	 * On after update event handler.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onAfterUpdate(Entity\Event $event)
	{
		return MailingChainTable::onAfterUpdate($event);
	}

	/**
	 * On delete event handler.
	 *
	 * @param Entity\Event $event Event.
	 * @return Entity\EventResult
	 */
	public static function onDelete(Entity\Event $event)
	{
		$data = $event->getParameters();
		$fields = static::getRowById($data['primary']['ID']);
		if ($fields)
		{
			$fileQuery = MessageFieldTable::getById([
				'MESSAGE_ID' => $fields['MESSAGE_ID'],
				'CODE' => 'ATTACHMENT',
			]);

			if($row = $fileQuery->fetch())
			{
				$files = explode(",", $row['VALUE']);

				foreach ($files as $file)
				{
					if((int)$file)
					{
						$hasFiles = Sender\FileTable::getList([
								'select' => ['ID'],
								'filter' => [
									'=FILE_ID' => (int)$file,
									'!=ENTITY_ID' => $fields['MESSAGE_ID'],
									'=ENTITY_TYPE' => FileTable::TYPES['LETTER'],
								],
								'limit' => 1,
							]
						)->fetch();

						if (!$hasFiles)
						{
							\CFile::Delete((int)$file);
							FileInfoTable::delete((int)$file);
						}
					}
				}
			}

			$messageQuery = MessageFieldTable::getById([
				'MESSAGE_ID' => $fields['MESSAGE_ID'],
				'CODE' => 'MESSAGE',
			]);

			if($row = $messageQuery->fetch())
			{
				FileTable::syncFiles($data['primary']['ID'], 0, $row['VALUE'], true, true);
			}

			MessageTable::delete($fields['MESSAGE_ID']);
		}

		return MailingChainTable::onDelete($event);
	}

	/**
	 * @param Entity\Event $event
	 * @return void
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		MailingChainTable::onAfterDelete($event);
	}
}