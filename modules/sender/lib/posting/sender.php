<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Posting;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Sender\Consent\Consent;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Entity\Campaign;
use Bitrix\Sender\Entity\Letter;
use Bitrix\Sender\Integration;
use Bitrix\Sender\Integration\Bitrix24;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Internals\Model\PostingThreadTable;
use Bitrix\Sender\MailingTable;
use Bitrix\Sender\Message;
use Bitrix\Sender\Message\Adapter;
use Bitrix\Sender\Posting\ThreadStrategy\AbstractThreadStrategy;
use Bitrix\Sender\Posting\ThreadStrategy\IThreadStrategy;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\PostingTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Runtime\TimeLineJob;
use Bitrix\Sender\Transport\iLimiter;

Loc::loadMessages(__FILE__);

/**
 * Class Sender
 * @package Bitrix\Sender\Posting
 */
class Sender
{
	const RESULT_NONE     = 0;
	const RESULT_SENT     = 1;
	const RESULT_CONTINUE = 2;
	const RESULT_ERROR    = 3;
	const RESULT_WAIT     = 4;
	const RESULT_WAITING_RECIPIENT = 5;

	/** @var bool|null is consent supported by this posting*/
	protected $isConsentSupport;

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
	 * @var IThreadStrategy
	 */
	protected $threadStrategy;

	/**
	 * Sender constructor.
	 *
	 * @param Letter $letter Letter.
	 */
	public function __construct(Letter $letter)
	{
		$this->letter          = $letter;
		$this->checkStatusStep = (int)Option::get('sender', 'send_check_status_step', $this->checkStatusStep);

		$this->message = $letter->getMessage();
		$this->message->getConfiguration()->set('LETTER_ID', $this->letter->getId());
	}

	/**
	 * Set limit.
	 *
	 * @param integer $limit Limit.
	 *
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
	 *
	 * @return $this
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;

		return $this;
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

		if (!$this->postingId)
		{
			$this->resultCode = self::RESULT_ERROR;

			return;
		}

		if ($this->threadStrategy->isProcessLimited())
		{
			$this->resultCode = static::RESULT_CONTINUE;
			return;
		}

		$threadState = $this->threadStrategy->checkThreads();

		$this->startTime();

		$this->isConsentSupport = $this->letter
				->getMessage()
				->getConfiguration()
				->get('APPROVE_CONFIRMATION') === 'Y';

		$this->threadStrategy->setPostingId($this->postingId);

		if ($threadState === AbstractThreadStrategy::THREAD_NEEDED)
		{
			$this->threadStrategy->fillThreads();
		}

		$this->threadStrategy->lockThread();
		$threadId = $this->threadStrategy->getThreadId();

		// lock posting for exclude double parallel sending
		if (is_null($threadId))
		{
			$this->resultCode = static::RESULT_CONTINUE;
			return;
		}

		if (static::lock($this->postingId, $threadId) === false)
		{
			$this->threadStrategy->updateStatus(PostingThreadTable::STATUS_NEW);
			throw new DB\Exception(Loc::getMessage('SENDER_POSTING_MANAGER_ERR_LOCK'));
		}

		if ($this->status == PostingTable::STATUS_NEW && $threadId !== 0)
		{
			$this->resultCode = static::RESULT_CONTINUE;
			$this->threadStrategy->updateStatus(PostingThreadTable::STATUS_NEW);
			static::unlock($this->postingId, $threadId);
			return;
		}

		// posting not in right status
		if (!$this->initRecipients())
		{
			$this->resultCode = static::RESULT_WAITING_RECIPIENT;
			$this->threadStrategy->updateStatus(PostingThreadTable::STATUS_NEW);
			static::unlock($this->postingId, $threadId);
			return;
		}

		$this->changeStatusToPart();

		// posting not in right status
		if ($this->status != PostingTable::STATUS_PART)
		{
			$this->resultCode = static::RESULT_ERROR;
			$this->threadStrategy->updateStatus(PostingThreadTable::STATUS_NEW);
			static::unlock($this->postingId, $threadId);
			return;
		}

		if ($this->isTransportLimitsExceeded())
		{
			$this->resultCode = static::RESULT_CONTINUE;
			static::unlock($this->postingId, $threadId);

			return;
		}

		$threadRecipients = $this->threadStrategy->getRecipients($this->limit);
		$recipients = static::getRecipientsToSend($threadRecipients);
		if (($count = count($recipients))> 0)
		{
			$this->message->getTransport()->setSendCount($count);
			if (!$this->message->getTransport()->start())
			{
				$this->prevent();
			}
		}

		$this->sendToRecipients($recipients);


		$this->message->getTransport()->end();

		// unlock posting for exclude double parallel sending
		self::unlock($this->postingId, $threadId);
		// update status of posting
		$status = self::updateActualStatus(
			$this->postingId,
			$this->isPrevented(),
			$this->threadStrategy->hasUnprocessedThreads()
		);

		$threadStatus = (
			$threadRecipients->getSelectedRowsCount() === 0 ?
				PostingThreadTable::STATUS_DONE :
				PostingThreadTable::STATUS_NEW
		);

		$this->threadStrategy->updateStatus($threadStatus);

		if ($threadId < $this->threadStrategy->lastThreadId())
		{
			$this->resultCode = static::RESULT_CONTINUE;

			return;
		}

		if ($this->threadStrategy->hasUnprocessedThreads())
		{
			$this->resultCode = static::RESULT_CONTINUE;

			return;
		}

		if (!PostingRecipientTable::hasUnprocessed($this->postingId))
		{
			$onAfterEndResult = $this->message->onAfterEnd();
			if (!$onAfterEndResult->isSuccess())
			{
				$this->resultCode = static::RESULT_CONTINUE;

				return;
			}
			$errorMessage = implode(', ', $onAfterEndResult->getErrorMessages());
			if (strlen($errorMessage))
			{
				Model\LetterTable::update($this->letterId, ['ERROR_MESSAGE' => $errorMessage]);
			}
		}

		// set result code to continue or end of sending
		$isContinue       = $status == PostingTable::STATUS_PART;
		$this->resultCode = $isContinue ? static::RESULT_CONTINUE : static::RESULT_SENT;

		if ($this->resultCode == static::RESULT_SENT)
		{
			$this->resultCode = !$this->threadStrategy->finalize() ? static::RESULT_CONTINUE : static::RESULT_SENT;
			TimeLineJob::addEventAgent($this->letterId);
		}
	}

	/**
	 * Load posting.
	 *
	 * @param integer $postingId Posting ID.
	 *
	 * @return void
	 */
	public function load($postingId)
	{
		$postingDb = PostingTable::getList(
			[
				'select' => [
					'ID',
					'STATUS',
					'MAILING_ID',
					'MAILING_CHAIN_ID',
					'MAILING_CHAIN_REITERATE'  => 'MAILING_CHAIN.REITERATE',
					'MAILING_CHAIN_IS_TRIGGER' => 'MAILING_CHAIN.IS_TRIGGER',
					'COUNT_SEND_ALL'
				],
				'filter' => [
					'=ID'                   => $postingId,
					'=MAILING.ACTIVE'       => 'Y',
					'=MAILING_CHAIN.STATUS' => [
						Model\LetterTable::STATUS_SEND,
						Model\LetterTable::STATUS_PLAN,
					],
				]
			]
		);
		if ($postingData = $postingDb->fetch())
		{
			$this->postingId = $postingData['ID'];
			$this->status    = $postingData['STATUS'];

			$this->mailingId = $postingData['MAILING_ID'];
			$this->letterId  = $postingData['MAILING_CHAIN_ID'];
			$this->sendCount = $postingData['COUNT_SEND_ALL'];

			$this->isReiterate = $postingData['MAILING_CHAIN_REITERATE'] == 'Y';
			$this->isTrigger   = $postingData['MAILING_CHAIN_IS_TRIGGER'] == 'Y';
		}
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

	protected function initRecipients(): bool
	{
		// if posting in new status, then import recipients from groups
		// and set right status for sending

		if (!$this->postingId)
		{
			return true;
		}

		if ($this->isTrigger)
		{
			return true;
		}

		if (
			($this->status !== PostingTable::STATUS_NEW)
			&& !$this->isReiterate
			&& ($this->letter->getData()['WAITING_RECIPIENT'] !== 'N')
		)
		{
			return true;
		}

		return Builder::create()->run($this->postingId);
	}

	protected function changeStatusToPart()
	{
		if (!$this->postingId)
		{
			return;
		}

		if ($this->status == PostingTable::STATUS_PART)
		{
			return;
		}

		if (
			($this->status !== PostingTable::STATUS_NEW)
			&& !$this->isTrigger
			&& !$this->isReiterate
		)
		{
			return;
		}
		$onBeforeStartResult = $this->message->onBeforeStart();
		if ($onBeforeStartResult->isSuccess())
		{
			$this->status = PostingTable::STATUS_PART;
			Model\PostingTable::update($this->postingId, ['STATUS' => $this->status]);
		}
		else
		{
			static::updateActualStatus($this->postingId, true);
		}

		$errorMessage = implode(', ', $onBeforeStartResult->getErrorMessages());
		if($errorMessage <> '')
		{
			Model\LetterTable::update($this->letterId, ['ERROR_MESSAGE' => $errorMessage]);
		}
	}

	/**
	 * Update actual status.
	 *
	 * @param int $postingId Posting ID.
	 * @param bool $isPrevented Is sending prevented.
	 *
	 * @return string
	 */
	public static function updateActualStatus($postingId, $isPrevented = false, $awaitThread = false)
	{
		//set status and delivered and error emails
		$statusList = PostingTable::getRecipientCountByStatus($postingId,[
			'LOGIC' => 'OR',
			[
				'@STATUS' => [
					PostingRecipientTable::SEND_RESULT_DENY,
					PostingRecipientTable::SEND_RESULT_NONE,
					PostingRecipientTable::SEND_RESULT_SUCCESS,
					PostingRecipientTable::SEND_RESULT_ERROR,
				],
			],
			[
				'=STATUS' => PostingRecipientTable::SEND_RESULT_WAIT_ACCEPT,
				'>=DATE_SENT' => (new Type\DateTime())->add("- 1 week")
			]
		]);
		$hasStatusError = array_key_exists(PostingRecipientTable::SEND_RESULT_ERROR, $statusList);
		$hasStatusNone  = array_key_exists(PostingRecipientTable::SEND_RESULT_NONE, $statusList);
		$hasStatusWait = array_key_exists(PostingRecipientTable::SEND_RESULT_WAIT_ACCEPT, $statusList);
		if ($isPrevented)
		{
			$status = PostingTable::STATUS_ABORT;
		}
		elseif (!$hasStatusNone && !$awaitThread && !$hasStatusWait)
		{
			$status = $hasStatusError ? PostingTable::STATUS_SENT_WITH_ERRORS : PostingTable::STATUS_SENT;
		}
		else
		{
			$status = PostingTable::STATUS_PART;
		}

		$postingUpdateFields = [
			'STATUS'         => $status,
			'DATE_SENT'      => $status == PostingTable::STATUS_PART ? null : new Type\DateTime(),
			'COUNT_SEND_ALL' => 0
		];

		$recipientStatusToPostingFieldMap = PostingTable::getRecipientStatusToPostingFieldMap();
		foreach ($recipientStatusToPostingFieldMap as $recipientStatus => $postingFieldName)
		{
			if (!array_key_exists($recipientStatus, $statusList))
			{
				$statusList[$recipientStatus] = 0;
			}
			$postingUpdateFields[$postingFieldName] = $statusList[$recipientStatus];
		}
		$postingUpdateFields['COUNT_SEND_ALL'] = array_sum($statusList);
		Model\PostingTable::update($postingId, $postingUpdateFields);

		return $status;
	}

	/**
	 * Lock posting for preventing double sending
	 *
	 * @param integer $id ID.
	 * @param $threadId default 0
	 *
	 * @return bool
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	public static function lock($id, $threadId = 0)
	{
		$id       = intval($id);
		$threadId = intval($threadId);

		$uniqueSalt = self::getLockUniqueSalt();
		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof DB\MysqlCommonConnection)
		{
			$lockDb = $connection->query(
				sprintf(
					"SELECT GET_LOCK('%s_sendpost_%d_%d', 0) as L",
					$uniqueSalt,
					$id,
					$threadId
				),
				false,
				"File: ".__FILE__."<br>Line: ".__LINE__
			);
			$lock   = $lockDb->fetch();
			if ($lock["L"] == "1")
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

	protected static function getLockUniqueSalt($generate = true)
	{
		$uniqueSalt = Option::get("main", "server_uniq_id", "");
		if ($uniqueSalt == '' && $generate)
		{
			$uniqueSalt = md5(uniqid(rand(), true));
			Option::set("main", "server_uniq_id", $uniqueSalt);
		}

		return $uniqueSalt;
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
	 * Get transport exceeded limiter.
	 *
	 * @return iLimiter|null
	 */
	public function getExceededLimiter()
	{
		return $this->message->getTransport()->getExceededLimiter($this->message);
	}

	protected function prevent()
	{
		return $this->isPrevented = true;
	}

	/**
	 * @param $recipients
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function sendToRecipients($recipients)
	{
		$sendResult = null;
		$dataToInsert = [];
		try
		{
			foreach ($recipients as $recipient)
			{
				if ($this->isPrevented() || $this->isStoppedOnRun())
				{
					break;
				}

				$this->setPostingDateSend();
				$eventData = [];
				if ($this->canDenySendToRecipient($recipient))
				{
					$this->updateRecipientStatus($recipient['ID'],PostingRecipientTable::SEND_RESULT_ERROR);
					$sendResult = false;
					$eventData = $this->executeEvent($recipient, $sendResult);
				}
				elseif($this->canSendMessageToRecipient($recipient))
				{
					$sendResult = $this->sendToRecipient($recipient);
					if ($this->isPrevented())
					{
						break;
					}
					$sendResultStatus = ($sendResult ?
						PostingRecipientTable::SEND_RESULT_SUCCESS :
						PostingRecipientTable::SEND_RESULT_ERROR
					);
					$this->updateRecipientStatus($recipient['ID'],$sendResultStatus);
					$eventData = $this->executeEvent($recipient, $sendResult);
				}
				elseif ($this->canSendConsentToRecipient($recipient))
				{
					$sendResult = $this->executeConsentToRecipient($recipient);
					$sendResultStatus = (
						$sendResult ?
						PostingRecipientTable::SEND_RESULT_WAIT_ACCEPT :
						PostingRecipientTable::SEND_RESULT_ERROR
					);
					$this->updateRecipientStatus($recipient['ID'],$sendResultStatus);
				}

				$dataToInsert[] = $eventData;

				if (Bitrix24\Service::isCloud() && $eventData['SEND_RESULT'] && $this->letter->getMessage()->getCode() === Message\iBase::CODE_MAIL)
				{
					Bitrix24\Limitation\DailyLimit::increment();
				}
				// limit executing script by time
				if ($this->isTimeout() || $this->isLimitExceeded() || $this->isTransportLimitsExceeded())
				{
					break;
				}
				// increment sending statistic
				$this->sentCount++;
			}
		} catch(\Exception $e)
		{}

		try
		{
			if($dataToInsert)
			{
				Integration\EventHandler::onAfterPostingSendRecipientMultiple(
					$dataToInsert,
					$this->letter
				);
			}
		} catch(\Exception $e)
		{

		}

		return $sendResult;
	}

	protected function isPrevented()
	{
		return $this->isPrevented;
	}

	protected function isStoppedOnRun()
	{
		// check pause or stop status
		if (++$this->checkStatusCounter < $this->checkStatusStep)
		{
			return false;
		}

		$checkStatusDb = Model\LetterTable::getList(
			[
				'select' => ['ID'],
				'filter' => [
					'=ID'     => $this->letterId,
					'=STATUS' => Model\LetterTable::STATUS_SEND
				]
			]
		);
		if (!$checkStatusDb->fetch())
		{
			return true;
		}

		$this->checkStatusCounter = 0;

		return false;
	}

	protected function setPostingDateSend()
	{
		if ($this->letter->get('DATE_SEND'))
		{
			return;
		}

		Model\PostingTable::update($this->postingId, ['DATE_SEND' => new Type\DateTime()]);
	}

	protected function sendToRecipient($recipient)
	{
		self::applyRecipientToMessage($this->message, $recipient);

		// event before sending
		$eventSendParams = [
			'FIELDS'           => $this->message->getFields(),
			'TRACK_READ'       => $this->message->getReadTracker()->getArray(),
			'TRACK_CLICK'      => $this->message->getClickTracker()->getArray(),
			'MAILING_CHAIN_ID' => $this->letter->getId()
		];
		$linkDomain      = $this->message->getReadTracker()->getLinkDomain();
		if ($linkDomain)
		{
			$eventSendParams['LINK_DOMAIN'] = $linkDomain;
		}
		$event = new Main\Event('sender', 'OnBeforePostingSendRecipient', [$eventSendParams]);
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == Main\EventResult::ERROR)
			{
				return false;
			}

			if (is_array($eventResult->getParameters()))
			{
				$eventSendParams = array_merge($eventSendParams, $eventResult->getParameters());
			}
		}
		if (count($event->getResults()) > 0)
		{
			$this->message->setFields($eventSendParams['FIELDS']);
			$this->message->getReadTracker()->setArray($eventSendParams['TRACK_READ']);
			$this->message->getReadTracker()->setArray($eventSendParams['TRACK_CLICK']);
		}

		try
		{
			$sendResult = $this->message->send();
		}
		catch (Main\Mail\StopException $e)
		{
			$sendResult = false;
			$this->prevent();
		}

		return $sendResult;
	}

	/**
	 * Apply recipient data to message.
	 *
	 * @param Adapter $message Message.
	 * @param array $recipient Recipient.
	 * @param bool $isTest Is test.
	 *
	 * @return void
	 */
	public static function applyRecipientToMessage(Adapter $message, array $recipient, $isTest = false)
	{
		$siteId = MailingTable::getMailingSiteId($recipient['CAMPAIGN_ID'] ? : Campaign::getDefaultId(SITE_ID));
		$message->getReadTracker()->setModuleId('sender')->setFields(['RECIPIENT_ID' => $recipient["ID"]])
				->setHandlerUri(Option::get('sender', 'read_link'))->setSiteId($siteId);
		$message->getClickTracker()->setModuleId('sender')->setFields(['RECIPIENT_ID' => $recipient["ID"]])
				->setUriParameters(['bx_sender_conversion_id' => $recipient["ID"]])->setHandlerUri(
				Option::get('sender', 'click_link')
			)->setSiteId($siteId);
		$message->getUnsubTracker()->setModuleId('sender')->setFields(
			[
				'RECIPIENT_ID' => $recipient['ID'],
				'CONTACT_ID' => $recipient['CONTACT_ID'] ?? '',
				'MAILING_ID'   => $recipient['CAMPAIGN_ID'] ?? 0,
				'EMAIL'        => $message->getRecipientCode(),
				'CODE'         => $message->getRecipientCode(),
				'TEST'         => $isTest ? 'Y' : 'N'
			]
		)->setHandlerUri(Option::get('sender', 'unsub_link'))->setSiteId($siteId);

		$fields = self::prepareRecipientFields($recipient);
		$message->setFields($fields);
		$message->setRecipientId($recipient['ID']);
		$message->setRecipientCode($recipient['CONTACT_CODE']);
		$message->setRecipientType(Recipient\Type::getCode($recipient['CONTACT_TYPE_ID'] ?? ''));
		$message->setRecipientData($recipient);
	}

	protected static function prepareRecipientFields($recipient)
	{
		// create name from email
		if (empty($recipient["NAME"]))
		{
			$recipient["NAME"] = Recipient\Field::getDefaultName();
		}
		$recipient["MAILING_CHAIN_ID"] ??= 0;
		$senderChainId = (int)$recipient["MAILING_CHAIN_ID"] > 0 ? (int)$recipient["MAILING_CHAIN_ID"]
			: (int)$recipient['CAMPAIGN_ID'];

		// prepare params for send
		$fields = [
			'EMAIL_TO'          => $recipient['CONTACT_CODE'] ?? '',
			'NAME'              => $recipient['NAME'] ?? '',
			'USER_ID'           => $recipient["USER_ID"] ?? '',
			'SENDER_CHAIN_ID'   => $senderChainId,
			'SENDER_CHAIN_CODE' => 'sender_chain_item_'.$senderChainId
		];

		if (is_array($recipient['FIELDS']) && count($recipient) > 0)
		{
			$fields = $fields + $recipient['FIELDS'];
		}

		return $fields;
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
	 * UnLock posting that was locking for preventing double sending
	 *
	 * @param integer $id ID.
	 * @param int $threadId
	 *
	 * @return bool
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\SystemException
	 */
	public static function unlock($id, $threadId = 0)
	{
		$id       = intval($id);
		$threadId = intval($threadId);

		$connection = Application::getInstance()->getConnection();
		if ($connection instanceof DB\MysqlCommonConnection)
		{
			$uniqueSalt = self::getLockUniqueSalt(false);
			if (!$uniqueSalt)
			{
				return false;
			}

			$lockDb = $connection->query(
				sprintf(
					"SELECT RELEASE_LOCK('%s_sendpost_%d_%d') as L",
					$uniqueSalt,
					$id,
					$threadId
				)
			);
			$lock   = $lockDb->fetch();
			if ($lock["L"] == "0")
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

	/**
	 * @return IThreadStrategy
	 */
	public function getThreadStrategy(): IThreadStrategy
	{
		return $this->threadStrategy;
	}

	/**
	 * @param IThreadStrategy $threadStrategy
	 *
	 * @return Sender
	 */
	public function setThreadStrategy(IThreadStrategy $threadStrategy): Sender
	{
		$this->threadStrategy = $threadStrategy;
		return $this;
	}

	/**
	 * @return Adapter
	 */
	public function getMessage()
	: Adapter
	{
		return $this->message;
	}
	protected function canDenySendToRecipient($recipient) : bool
	{
		return (
			empty($recipient['CONTACT_CODE']) ||
			$recipient['CONTACT_BLACKLISTED'] === 'Y' ||
			$recipient['CONTACT_UNSUBSCRIBED'] === 'Y' ||
			$recipient['CONTACT_MAILING_UNSUBSCRIBED'] === 'Y' ||
			Consent::isUnsub(
				$recipient['CONTACT_CONSENT_STATUS'],
				$recipient['CONTACT_CONSENT_REQUEST'],
				$this->message->getTransport()->getCode()
			) &&
			$this->needConsent()
		);
	}
	protected function canSendConsentToRecipient($recipient) : bool
	{
		return (
			in_array($recipient['CONTACT_CONSENT_STATUS'], [
				ContactTable::CONSENT_STATUS_NEW,
				ContactTable::CONSENT_STATUS_WAIT]
			) &&
			!Consent::checkIfConsentRequestLimitExceeded(
				$recipient['CONTACT_CONSENT_REQUEST'],
				$this->message->getTransport()->getCode()
			) &&
			$this->needConsent()
		);
	}
	protected function canSendMessageToRecipient($recipient) : bool
	{
		return (
			$recipient['CONTACT_CONSENT_STATUS'] === ContactTable::CONSENT_STATUS_ACCEPT ||
			!$this->needConsent()
		);
	}
	protected function executeConsentToRecipient($recipient)
	{
		$sendResult = null;
		$sendResult = $this->message->getTransport()->sendConsent(
			$this->letter->getMessage(), $recipient + ['RECIPIENT_ID' => $recipient['ID'], 'SITE_ID' => SITE_ID]
		);

		if($sendResult)
		{
			ContactTable::updateConsentStatus(
				$recipient['CONTACT_ID'],
				ContactTable::CONSENT_STATUS_WAIT
			);

			if (Bitrix24\Service::isCloud())
			{
				Bitrix24\Limitation\DailyLimit::increment();
			}
		}
		return $sendResult;
	}

	protected function executeEvent($recipient, $success)
	{
		$eventData = [
			'SEND_RESULT' => $success,
			'RECIPIENT'   => $recipient,
			'POSTING'     => [
				'ID'               => $this->postingId,
				'STATUS'           => $this->status,
				'MAILING_ID'       => $this->mailingId,
				'MAILING_CHAIN_ID' => $this->letterId,
			]
		];

		return $eventData;
	}

	protected function needConsent(): bool
	{
		static $needConsentMessage;
		if (!isset($needConsentMessage))
		{
			$needConsentMessage = $this->isConsentSupport;
		}
		return $needConsentMessage;
	}

	protected function updateRecipientStatus($primary, $status)
	{
		Model\Posting\RecipientTable::update(
			$primary,
			[
				'STATUS' => $status,
				'DATE_SENT' => new Type\DateTime()
			]
		);
	}

	protected static function getRecipientsToSend(\Bitrix\Main\ORM\Query\Result $result)
	{
		return array_filter(iterator_to_array($result),
			function ($recipient)
			{
				return $recipient['STATUS'] === PostingRecipientTable::SEND_RESULT_NONE;
			});
	}
}
