<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type as MainType;

use Bitrix\Sender\Posting\Builder as PostingBuilder;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\Model;


Loc::loadMessages(__FILE__);

class PostingTable extends Entity\DataManager
{
	const STATUS_NEW = 'N';
	const STATUS_PART = 'P';
	const STATUS_SENT = 'S';
	const STATUS_SENT_WITH_ERRORS = 'E';
	const STATUS_ABORT = 'A';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting';
	}

	/**
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
			'MAILING_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			),
			'MAILING_CHAIN_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new MainType\DateTime(),
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new MainType\DateTime(),
			),
			'STATUS' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => static::STATUS_NEW,
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
				'data_type' => 'Bitrix\Sender\Internals\Model\LetterTable',
				'reference' => array('=this.MAILING_CHAIN_ID' => 'ref.ID'),
			),
			'MAILING' => array(
				'data_type' => 'Bitrix\Sender\MailingTable',
				'reference' => array('=this.MAILING_ID' => 'ref.ID'),
			),
			'MAILING_CHAIN' => array(
				'data_type' => 'Bitrix\Sender\MailingChainTable',
				'reference' => array('=this.MAILING_CHAIN_ID' => 'ref.ID'),
			),
			'POSTING_RECIPIENT' => array(
				'data_type' => 'Bitrix\Sender\PostingRecipientTable',
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
			'POSTING_READ' => array(
				'data_type' => 'Bitrix\Sender\PostingReadTable',
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
			'POSTING_CLICK' => array(
				'data_type' => 'Bitrix\Sender\PostingClickTable',
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
			'POSTING_UNSUB' => array(
				'data_type' => 'Bitrix\Sender\PostingUnsubTable',
				'reference' => array('=this.ID' => 'ref.POSTING_ID'),
			),
		);
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
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
				$filter[$primKey] = $primVal;

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
			PostingReadTable::delete($primary);
			PostingClickTable::delete($primary);
			PostingUnsubTable::delete($primary);
			PostingRecipientTable::delete($primary);
		}


		return $result;
	}

	/**
	 * @param $ar
	 * @param bool $checkDuplicate
	 */
	public static function addRecipient($ar, $checkDuplicate = false)
	{
		if(!$checkDuplicate)
		{
			$needAdd = true;
		}
		else
		{
			$row = PostingRecipientTable::getRow(array(
				'select' => array('ID'),
				'filter' => array(
					'=CONTACT_ID' => $ar['CONTACT_ID'],
					'=POSTING_ID' => $ar['POSTING_ID']
				)
			));
			if(!$row)
			{
				$needAdd = true;
			}
			else
			{
				$needAdd = false;
			}
		}

		if($needAdd)
		{
			PostingRecipientTable::add($ar);
		}
	}


	/**
	 * @param $postingId
	 * @param bool $checkDuplicate
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function initGroupRecipients($postingId, $checkDuplicate = true)
	{
		PostingBuilder::create()->run($postingId, $checkDuplicate);

		return true;
	}

	/**
	 * @param $id
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getRecipientCountByStatus($id)
	{
		$statusList = array();

		$select = array('CNT', 'STATUS');
		$filter = array('POSTING_ID' => $id);
		$postingContactDb = PostingRecipientTable::getList(array(
			'select' => $select,
			'filter' => $filter,
			'runtime' => array(new Entity\ExpressionField('CNT', 'COUNT(*)')),
		));
		while($postingContact = $postingContactDb->fetch())
			$statusList[$postingContact['STATUS']] = intval($postingContact['CNT']);

		return $statusList;
	}

	/**
	 * @param $id
	 * @param string $status
	 * @return int
	 */
	public static function getRecipientCount($id, $status = '')
	{
		$count = 0;

		$ar = static::getRecipientCountByStatus($id);
		if ($status != '')
			$count = (array_key_exists($status, $ar) ? $ar[$status] : 0);
		else
			foreach ($ar as $k => $v) $count += $v;

		return $count;
	}

	/**
	 * Return send status of posting in percents by posting id.
	 *
	 * @param $id
	 * @return int
	 */
	public static function getSendPercent($id)
	{
		$ar = static::getRecipientCountByStatus($id);
		$count = 0;
		foreach ($ar as $k => $v)
		{
			$count += $v;
		}

		$countNew = 0;
		if(isset($ar[PostingRecipientTable::SEND_RESULT_NONE]))
		{
			$countNew = $ar[PostingRecipientTable::SEND_RESULT_NONE];
		}

		if($count > 0 && $countNew > 0)
		{
			return round(($count - $countNew) / $count, 2) * 100;
		}
		else
		{
			return 100;
		}
	}

	/**
	 * @return array
	 */
	public static function getRecipientStatusToPostingFieldMap()
	{
		return array(
			PostingRecipientTable::SEND_RESULT_NONE => 'COUNT_SEND_NONE',
			PostingRecipientTable::SEND_RESULT_ERROR => 'COUNT_SEND_ERROR',
			PostingRecipientTable::SEND_RESULT_SUCCESS => 'COUNT_SEND_SUCCESS',
			PostingRecipientTable::SEND_RESULT_DENY => 'COUNT_SEND_DENY',
		);
	}
}



class PostingReadTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_read';
	}

	/**
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
				'primary' => true,
				'data_type' => 'integer',
			),
			'RECIPIENT_ID' => array(
				'primary' => true,
				'data_type' => 'integer',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new MainType\DateTime(),
			),
		);
	}

	/**
	 * Handler of after add event
	 *
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		$data = $data['fields'];

		// update read flag of recipient
		Model\Posting\RecipientTable::update($data['RECIPIENT_ID'], ['IS_READ' => 'Y']);

		// update read counter of posting
		$resultDb = static::getList(array('filter' => array('RECIPIENT_ID' => $data['RECIPIENT_ID'])));
		if($resultDb->getSelectedRowsCount() == 1)
		{
			Model\PostingTable::update($data['POSTING_ID'], array(
				'COUNT_READ' => new \Bitrix\Main\DB\SqlExpression('?# + 1', 'COUNT_READ')
			));
		}

		return $result;
	}
}


class PostingClickTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_click';
	}

	/**
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
				'primary' => true,
				'data_type' => 'integer',
			),
			'RECIPIENT_ID' => array(
				'primary' => true,
				'data_type' => 'integer',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new MainType\DateTime(),
			),
			'URL' => array(
				'data_type' => 'string',
			),
			'POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Handler of after add event
	 *
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		$data = $data['fields'];

		// update click flag of recipient
		Model\Posting\RecipientTable::update($data['RECIPIENT_ID'], ['IS_CLICK' => 'Y']);

		// update click counter of posting
		$resultDb = static::getList(array('filter' => array('RECIPIENT_ID' => $data['RECIPIENT_ID'])));
		if($resultDb->getSelectedRowsCount() == 1)
		{
			Model\PostingTable::update($data['POSTING_ID'], array(
				'COUNT_CLICK' => new \Bitrix\Main\DB\SqlExpression('?# + 1', 'COUNT_CLICK')
			));
		}

		return $result;
	}
}

class PostingUnsubTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_unsub';
	}

	/**
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
				'primary' => true,
				'data_type' => 'integer',
			),
			'RECIPIENT_ID' => array(
				'primary' => true,
				'data_type' => 'integer',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new MainType\DateTime(),
			),
			'POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
			'POSTING_RECIPIENT' => array(
				'data_type' => 'Bitrix\Sender\PostingRecipientTable',
				'reference' => array('=this.RECIPIENT_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Handler of after add event
	 *
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		$data = $data['fields'];

		// update unsub flag of recipient
		Model\Posting\RecipientTable::update($data['RECIPIENT_ID'], ['IS_UNSUB' => 'Y']);

		// update unsub counter of posting
		$resultDb = static::getList(array('filter' => array('RECIPIENT_ID' => $data['RECIPIENT_ID'])));
		if($resultDb->getSelectedRowsCount() == 1)
		{
			Model\PostingTable::update($data['POSTING_ID'], array(
				'COUNT_UNSUB' => new \Bitrix\Main\DB\SqlExpression('?# + 1', 'COUNT_UNSUB')
			));
		}

		return $result;
	}
}

class PostingRecipientTable extends Entity\DataManager
{
	const SEND_RESULT_NONE = 'Y';
	const SEND_RESULT_SUCCESS = 'N';
	const SEND_RESULT_ERROR = 'E';
	const SEND_RESULT_WAIT = 'W';
	const SEND_RESULT_DENY = 'D';

	protected static $personalizeList = null;
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_posting_recipient';
	}

	/**
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
				'primary' => true,
				'data_type' => 'integer',
			),
			'STATUS' => array(
				'data_type' => 'string',
				'primary' => true,
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
				'data_type' => 'Bitrix\Sender\ContactTable',
				'reference' => array('=this.CONTACT_ID' => 'ref.ID'),
			),
			'POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
			'POSTING_READ' => array(
				'data_type' => 'Bitrix\Sender\PostingReadTable',
				'reference' => array('=this.ID' => 'ref.RECIPIENT_ID'),
			),
			'POSTING_CLICK' => array(
				'data_type' => 'Bitrix\Sender\PostingClickTable',
				'reference' => array('=this.ID' => 'ref.RECIPIENT_ID'),
			),
			'POSTING_UNSUB' => array(
				'data_type' => 'Bitrix\Sender\PostingUnsubTable',
				'reference' => array('=this.ID' => 'ref.RECIPIENT_ID'),
			),
		);
	}

	public static function setPersonalizeList(array $personalizeList = null)
	{
		static::$personalizeList = $personalizeList;
	}

	/**
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		$list = array(
			array(
				'CODE' => 'NAME',
				'NAME' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_NAME"),
				'DESC' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_NAME_DESC"),
			),
			array(
				'CODE' => 'EMAIL_TO',
				'NAME' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_EMAIL"),
				'DESC' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_EMAIL_DESC"),
			),
		);
		if (!Integration\Bitrix24\Service::isCloud())
		{
			$list[] = array(
				'CODE' => 'USER_ID',
				'NAME' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_USER_ID"),
				'DESC' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_USER_ID_DESC"),
			);
			$list[] = array(
				'CODE' => 'SITE_NAME',
				'NAME' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_SITE_NAME"),
				'DESC' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_SITE_NAME_DESC"),
			);
			$list[] = array(
				'CODE' => 'SENDER_CHAIN_CODE',
				'NAME' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_SENDER_CHAIN_ID"),
				'DESC' => Loc::getMessage("SENDER_POSTING_PERSONALIZE_FIELD_SENDER_CHAIN_ID_DESC"),
			);
		}

		return array_merge(
			$list,
			(static::$personalizeList ? static::$personalizeList : array())
		);
	}

	/**
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::SEND_RESULT_NONE => Loc::getMessage('SENDER_POSTING_RECIPIENT_STATUS_N'),
			self::SEND_RESULT_SUCCESS => Loc::getMessage('SENDER_POSTING_RECIPIENT_STATUS_S'),
			self::SEND_RESULT_ERROR => Loc::getMessage('SENDER_POSTING_RECIPIENT_STATUS_E')
		);
	}
}