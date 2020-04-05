<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Posting;

use Bitrix\Main;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\DB;
use Bitrix\Main\Type;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;

use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\PostingTable;
use Bitrix\Sender\Entity\Letter;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Message\Adapter;


Loc::loadMessages(__FILE__);

/**
 * Class Sender
 * @package Bitrix\Sender\Posting
 */
class Sender
{
	const RESULT_NONE = 0;
	const RESULT_SENT = 1;
	const RESULT_CONTINUE = 2;
	const RESULT_ERROR = 3;

	/** @var  Letter $letter Letter. */
	protected $letter;
	/** @var  Adapter $message Message. */
	protected $message;

	/** @var  integer|null $timeout Timeout. */
	protected $timeout;
	/** @var  integer|null $timeAtStart Time at start. */
	protected $timeAtStart;

	/** @var  integer|null $limit Limit. */
	protected $limit;
	/** @var  integer $sentCount Count of sent. */
	protected $sentCount = 0;

	/** @var  integer $checkStatusStep Step for status checking. */
	protected $checkStatusStep = 25;
	/** @var  integer $checkStatusCounter Counter for status checking. */
	protected $checkStatusCounter = 0;

	/** @var  boolean $isPrevented Is prevented. */
	protected $isPrevented = false;

	/** @var  boolean $isTrigger Is trigger. */
	protected $isTrigger = false;

	/** @var  boolean $isReiterate Is reiterate. */
	protected $isReiterate = false;

	/** @var  integer $mailingId Campaign ID. */
	protected $mailingId;
	/** @var  integer $postingId Posting ID. */
	protected $postingId;
	/** @var  integer $letterId Letter ID. */
	protected $letterId;
	/** @var  string $status Status. */
	protected $status;
	/** @var  integer $sendCount Count of send. */
	protected $sendCount = 0;
	/** @var  string $resultCode Code of result. */
	protected $resultCode = self::RESULT_NONE;

	/**
	 * Sender constructor.
	 * @param Letter $letter Letter.
	 */
	public function __construct(Letter $letter)
	{
		$this->letter = $letter;
		$this->checkStatusStep = (int) Option::get('sender', 'send_check_status_step', $this->checkStatusStep);

		$this->message = $letter->getMessage();
	}

	/**
	 * Load posting.
	 *
	 * @param integer $postingId Posting ID.
	 * @return void
	 */
	public function load($postingId)
	{
		$postingDb = PostingTable::getList(array(
			'select' => array(
				'ID',
				'STATUS',
				'MAILING_ID',
				'MAILING_CHAIN_ID',
				'MAILING_CHAIN_REITERATE' => 'MAILING_CHAIN.REITERATE',
				'MAILING_CHAIN_IS_TRIGGER' => 'MAILING_CHAIN.IS_TRIGGER',
				'COUNT_SEND_ALL'
			),
			'filter' => array(
				'=ID' => $postingId,
				'=MAILING.ACTIVE' => 'Y',
				'=MAILING_CHAIN.STATUS' => array(
					Model\LetterTable::STATUS_SEND,
					Model\LetterTable::STATUS_PLAN
				),
			)
		));
		if ($postingData = $postingDb->fetch())
		{
			$this->postingId = $postingData['ID'];
			$this->status = $postingData['STATUS'];

			$this->mailingId = $postingData['MAILING_ID'];
			$this->letterId = $postingData['MAILING_CHAIN_ID'];
			$this->sendCount = $postingData['COUNT_SEND_ALL'];

			$this->isReiterate = $postingData['MAILING_CHAIN_REITERATE'] == 'Y';
			$this->isTrigger = $postingData['MAILING_CHAIN_IS_TRIGGER'] == 'Y';
		}
	}

	/**
	 * Set limit.
	 *
	 * @param integer $limit Limit.
	 * @return $this
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set timeout.
	 *
	 * @param integer $timeout Timeout.
	 * @return $this
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Start time watch.
	 *
	 * @return void
	 */
	public function startTime()
	{
		if (!$this->timeout)
		{
			return;
		}

		$this->timeAtStart = getmicrotime();
		@set_time_limit(0);
	}

	/**
	 * Check timeout.
	 *
	 * @return bool
	 */
	public function isTimeout()
	{
		if (!$this->timeout)
		{
			return false;
		}

		return (getmicrotime() - $this->timeAtStart >= $this->timeout);
	}

	/**
	 * Check limits.
	 *
	 * @return bool
	 */
	public function isLimitExceeded()
	{
		if (!$this->limit)
		{
			return false;
		}

		return ($this->sentCount > $this->limit);
	}

	/**
	 * Check transport limits.
	 *
	 * @return bool
	 */
	public function isTransportLimitsExceeded()
	{
		return $this->message->getTransport()->isLimitsExceeded($this->message);
	}

	/**
	 * Lock posting for preventing double sending
	 *
	 * @param integer $id ID.
	 * @return bool
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Exception
	 */
	public static function lock($id)
	{
		$id = intval($id);

		$uniqueSalt = self::getLockUniqueSalt();
		$connection = Application::getInstance()->getConnection();
		if($connection instanceof DB\MysqlCommonConnection)
		{
			$lockDb = $connection->query(
				"SELECT GET_LOCK('" . $uniqueSalt . "_sendpost_" . $id . "', 0) as L",
				false,
				"File: " . __FILE__ . "<br>Line: " . __LINE__
			);
			$lock = $lockDb->fetch();
			if($lock["L"] == "1")
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return false;
	}

	/**
	 * UnLock posting that was locking for preventing double sending
	 *
	 * @param integer $id ID.
	 * @return bool
	 */
	public static function unlock($id)
	{
		$id = intval($id);

		$connection = Application::getInstance()->getConnection();
		if($connection instanceof DB\MysqlCommonConnection)
		{
			$uniqueSalt = self::getLockUniqueSalt(false);
			if(!$uniqueSalt)
			{
				return false;
			}

			$lockDb = $connection->query("SELECT RELEASE_LOCK('" . $uniqueSalt . "_sendpost_" . $id . "') as L");
			$lock = $lockDb->fetch();
			if($lock["L"] == "0")
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		return false;
	}

	protected static function getLockUniqueSalt($generate = true)
	{
		$uniqueSalt = Option::get("main", "server_uniq_id", "");
		if($uniqueSalt == '' && $generate)
		{
			$uniqueSalt = md5(uniqid(rand(), true));
			Option::set("main", "server_uniq_id", $uniqueSalt);
		}

		return $uniqueSalt;
	}

	/**
	 * Apply recipient data to message.
	 *
	 * @param Adapter $message Message.
	 * @param array $recipient Recipient.
	 * @param bool $isTest Is test.
	 * @return void
	 */
	public static function applyRecipientToMessage(Adapter $message, array $recipient, $isTest = false)
	{
		$message->getReadTracker()
			->setModuleId('sender')
			->setFields(array('RECIPIENT_ID' => $recipient["ID"]))
			->setHandlerUri(Option::get('sender', 'read_link'));
		$message->getClickTracker()
			->setModuleId('sender')
			->setFields(array('RECIPIENT_ID' => $recipient["ID"]))
			->setUriParameters(array('bx_sender_conversion_id' => $recipient["ID"]))
			->setHandlerUri(Option::get('sender', 'click_link'));
		$message->getUnsubTracker()
			->setModuleId('sender')
			->setFields(array(
				'RECIPIENT_ID' => $recipient["ID"],
				'MAILING_ID' => isset($recipient['CAMPAIGN_ID']) ? $recipient['CAMPAIGN_ID'] : 0,
				'EMAIL' => $message->getRecipientCode(),
				'CODE' => $message->getRecipientCode(),
				'TEST' => $isTest ? 'Y' : 'N'
			))
			->setHandlerUri(Option::get('sender', 'unsub_link'));

		$fields = self::prepareRecipientFields($recipient);
		$message->setFields($fields);
		$message->setRecipientId($recipient['ID']);
		$message->setRecipientCode($recipient['CONTACT_CODE']);
		$message->setRecipientType(Recipient\Type::getCode($recipient['CONTACT_TYPE_ID']));
		$message->setRecipientData($recipient);
	}

	protected function sendToRecipient($recipient)
	{
		self::applyRecipientToMessage($this->message, $recipient);

		try
		{
			$sendResult = $this->message->send();
		}
		catch(Main\Mail\StopException $e)
		{
			$sendResult = false;
			$this->prevent();
		}

		return $sendResult;
	}

	protected function initRecipients()
	{
		// if posting in new status, then import recipients from groups
		// and set right status for sending

		if(!$this->postingId)
		{
			return;
		}

		if($this->isTrigger)
		{
			return;
		}

		if($this->status != PostingTable::STATUS_NEW)
		{
			return;
		}

		Builder::create()->run($this->postingId);
	}

	protected function changeStatusToPart()
	{
		if(!$this->postingId)
		{
			return;
		}

		if($this->status == PostingTable::STATUS_PART)
		{
			return;
		}

		if ($this->status != PostingTable::STATUS_NEW && !$this->isTrigger)
		{
			return;
		}

		$this->status = PostingTable::STATUS_PART;
		Model\PostingTable::update($this->postingId, ['STATUS' => $this->status]);
	}

	/**
	 * Get result code.
	 *
	 * @return int
	 */
	public function getResultCode()
	{
		return $this->resultCode;
	}

	/**
	 * Send.
	 *
	 * @return void
	 * @throws DB\Exception
	 */
	public function send()
	{
		$this->load($this->letter->get('POSTING_ID'));

		if(!$this->postingId)
		{
			$this->resultCode = self::RESULT_ERROR;
			return;
		}

		$this->startTime();
		$this->initRecipients();
		$this->changeStatusToPart();

		// posting not in right status
		if($this->status != PostingTable::STATUS_PART)
		{
			$this->resultCode = static::RESULT_ERROR;
			return;
		}

		// lock posting for exclude double parallel sending
		if(static::lock($this->postingId) === false)
		{
			throw new DB\Exception(Loc::getMessage('SENDER_POSTING_MANAGER_ERR_LOCK'));
		}

		if ($this->isTransportLimitsExceeded())
		{
			$this->resultCode = static::RESULT_CONTINUE;
			return;
		}

		$recipients = $this->getRecipients();
		if ($recipients->getSelectedRowsCount() > 0)
		{
			$this->message->getTransport()->setSendCount($this->sendCount);
			if (!$this->message->getTransport()->start())
			{
				$this->prevent();
			}
		}

		foreach ($recipients as $recipient)
		{

			if ($this->isPrevented())
			{
				break;
			}

			if ($this->isStoppedOnRun())
			{
				break;
			}

			$this->setPostingDateSend();

			if (
				empty($recipient['CONTACT_CODE']) ||
				$recipient['CONTACT_BLACKLISTED'] === 'Y' ||
				$recipient['CONTACT_UNSUBSCRIBED'] === 'Y'
			)
			{
				$sendResult = false;
			}
			else
			{
				$sendResult = $this->sendToRecipient($recipient);
				if ($this->isPrevented())
				{
					break;
				}
			}

			$sendResultStatus = $sendResult ? PostingRecipientTable::SEND_RESULT_SUCCESS : PostingRecipientTable::SEND_RESULT_ERROR;
			Model\Posting\RecipientTable::update(
				$recipient["ID"],
				[
					'STATUS' => $sendResultStatus,
					'DATE_SENT' => new Type\DateTime()
				]
			);

			// send event
			$eventData = array(
				'SEND_RESULT' => $sendResult,
				'RECIPIENT' => $recipient,
				'POSTING' => array(
					'ID' => $this->postingId,
					'STATUS' => $this->status,
					'MAILING_ID' => $this->mailingId,
					'MAILING_CHAIN_ID' => $this->letterId,
				)
			);
			$event = new Event('sender', 'OnAfterPostingSendRecipient', array($eventData, $this->letter));
			$event->send();

			Integration\EventHandler::onAfterPostingSendRecipient($eventData, $this->letter);

			// limit executing script by time
			if ($this->isTimeout() || $this->isLimitExceeded() || $this->isTransportLimitsExceeded())
			{
				break;
			}

			// increment sending statistic
			$this->sentCount++;
		}

		$this->message->getTransport()->end();

		// unlock posting for exclude double parallel sending
		self::unlock($this->postingId);

		// update status of posting
		$status = self::updateActualStatus($this->postingId, $this->isPrevented());

		// set result code to continue or end of sending
		$isContinue = $status == PostingTable::STATUS_PART;
		$this->resultCode = $isContinue ? static::RESULT_CONTINUE : static::RESULT_SENT;
	}

	protected function setPostingDateSend()
	{
		if ($this->letter->get('DATE_SEND'))
		{
			return;
		}

		Model\PostingTable::update($this->postingId, ['DATE_SEND' => new Type\DateTime()]);
	}

	/**
	 * Update actual status.
	 *
	 * @param int $postingId Posting ID.
	 * @param bool $isPrevented Is sending prevented.
	 * @return string
	 */
	public static function updateActualStatus($postingId, $isPrevented = false)
	{
		//set status and delivered and error emails
		$statusList = PostingTable::getRecipientCountByStatus($postingId);
		$hasStatusError = array_key_exists(PostingRecipientTable::SEND_RESULT_ERROR, $statusList);
		$hasStatusNone = array_key_exists(PostingRecipientTable::SEND_RESULT_NONE, $statusList);
		if($isPrevented)
		{
			$status = PostingTable::STATUS_ABORT;
		}
		elseif(!$hasStatusNone)
		{
			$status = $hasStatusError ? PostingTable::STATUS_SENT_WITH_ERRORS : PostingTable::STATUS_SENT;
		}
		else
		{
			$status = PostingTable::STATUS_PART;
		}

		$postingUpdateFields = array(
			'STATUS' => $status,
			'DATE_SENT' => $status == PostingTable::STATUS_PART ? null : new Type\DateTime(),
			'COUNT_SEND_ALL' => 0
		);

		$recipientStatusToPostingFieldMap = PostingTable::getRecipientStatusToPostingFieldMap();
		foreach($recipientStatusToPostingFieldMap as $recipientStatus => $postingFieldName)
		{
			if(!array_key_exists($recipientStatus, $statusList))
			{
				$postingCountFieldValue = 0;
			}
			else
			{
				$postingCountFieldValue = $statusList[$recipientStatus];
			}

			$postingUpdateFields['COUNT_SEND_ALL'] += $postingCountFieldValue;
			$postingUpdateFields[$postingFieldName] = $postingCountFieldValue;
		}

		Model\PostingTable::update($postingId, $postingUpdateFields);

		return $status;
	}

	protected function getRecipients()
	{
		// select all recipients of posting, only not processed
		$recipients = PostingRecipientTable::getList(array(
			'select' => array(
				'*',
				'NAME' => 'CONTACT.NAME',
				'CONTACT_CODE' => 'CONTACT.CODE',
				'CONTACT_TYPE_ID' => 'CONTACT.TYPE_ID',
				'CONTACT_IS_SEND_SUCCESS' => 'CONTACT.IS_SEND_SUCCESS',
				'CONTACT_BLACKLISTED' => 'CONTACT.BLACKLISTED',
				'CONTACT_UNSUBSCRIBED' => 'MAILING_SUB.IS_UNSUB',
				'CAMPAIGN_ID' => 'POSTING.MAILING_ID'
			),
			'filter' => array(
				'=POSTING_ID' => $this->postingId,
				'=STATUS' => PostingRecipientTable::SEND_RESULT_NONE
			),
			'runtime' => [
				new ReferenceField(
					'MAILING_SUB',
					'Bitrix\\Sender\\MailingSubscriptionTable',
					[
						'=this.CONTACT_ID' => 'ref.CONTACT_ID',
						'=this.POSTING.MAILING_ID' => 'ref.MAILING_ID'
					],
					['join_type' => 'LEFT']
				)
			],
			'limit' => $this->limit
		));
		$recipients->addFetchDataModifier(
			function ($row)
			{
				$row['FIELDS'] = is_array($row['FIELDS']) ? $row['FIELDS'] : array();
				return $row;
			}
		);

		return $recipients;
	}

	protected static function prepareRecipientFields($recipient)
	{
		// create name from email
		if(empty($recipient["NAME"]))
		{
			$recipient["NAME"] = Recipient\Field::getDefaultName();
		}

		$senderChainId = (int)$recipient["MAILING_CHAIN_ID"] > 0 ? (int)$recipient["MAILING_CHAIN_ID"] : (int)$recipient['CAMPAIGN_ID'];

		// prepare params for send
		$fields = array(
			'EMAIL_TO' => $recipient['CONTACT_CODE'],
			'NAME' => $recipient['NAME'],
			'USER_ID' => $recipient["USER_ID"],
			'SENDER_CHAIN_ID' => $senderChainId,
			'SENDER_CHAIN_CODE' => 'sender_chain_item_' . $senderChainId
		);

		if(is_array($recipient['FIELDS']) && count($recipient) > 0)
		{
			$fields = $fields + $recipient['FIELDS'];
		}

		return $fields;
	}

	protected function isPrevented()
	{
		return $this->isPrevented;
	}

	protected function prevent()
	{
		return $this->isPrevented = true;
	}

	protected function isStoppedOnRun()
	{
		// check pause or stop status
		if(++$this->checkStatusCounter < $this->checkStatusStep)
		{
			return false;
		}

		$checkStatusDb = Model\LetterTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $this->letterId,
				'=STATUS' => Model\LetterTable::STATUS_SEND
			)
		));
		if(!$checkStatusDb->fetch())
		{
			return true;
		}

		$this->checkStatusCounter = 0;
		return false;
	}
}