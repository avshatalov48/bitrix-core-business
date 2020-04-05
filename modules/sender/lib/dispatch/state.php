<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Application;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;

use Bitrix\Sender\Posting;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Entity;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class State
 * @package Bitrix\Sender\Dispatch
 */
class State
{
	const NEWISH = 'N';
	const INIT = 'I';
	const READY = 'R';
	const SENDING = 'S';
	const WAITING = 'W';
	const PLANNED = 'T';
	const PAUSED = 'P';
	const SENT = 'Y';
	const STOPPED = 'X';
	const HALTED = 'H';

	/** @var Entity\Letter $letter Letter. */
	private $letter;

	/** @var DateTime $dateTime Date. */
	protected $dateTime;

	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter)
	{
		$this->letter = $letter;
	}

	/**
	 * Is ready to send.
	 *
	 * @return bool
	 */
	public function isReady()
	{
		return in_array($this->getCode(), array(self::NEWISH, self::INIT, self::READY));
	}

	/**
	 * Is sent.
	 *
	 * @return bool
	 */
	public function isSent()
	{
		return in_array($this->getCode(), array(self::SENT));
	}

	/**
	 * Is finished.
	 *
	 * @return bool
	 */
	public function isFinished()
	{
		return in_array($this->getCode(), Semantics::getFinishStates());
	}

	/**
	 * Is stopped.
	 *
	 * @return bool
	 */
	public function isStopped()
	{
		return in_array($this->getCode(), array(self::STOPPED));
	}

	/**
	 * Is sending started.
	 *
	 * @return bool
	 */
	public function isWaiting()
	{
		return in_array($this->getCode(), [self::WAITING]);
	}

	/**
	 * Is halted.
	 *
	 * @return bool
	 */
	public function isHalted()
	{
		return in_array($this->getCode(), [self::HALTED]);
	}

	/**
	 * Is sending started.
	 *
	 * @return bool
	 */
	public function isSending()
	{
		return in_array($this->getCode(), [self::SENDING]);
	}

	/**
	 * Is sending planned.
	 *
	 * @return bool
	 */
	public function isPlanned()
	{
		return in_array($this->getCode(), array(self::PLANNED));
	}

	/**
	 * Is sending paused.
	 *
	 * @return bool
	 */
	public function isPaused()
	{
		return in_array($this->getCode(), [self::PAUSED]);
	}

	/**
	 * Is sending was started.
	 *
	 * @return bool
	 */
	public function wasStartedSending()
	{
		return in_array(
			$this->getCode(),
			array(
				self::SENDING,
				self::WAITING,
				self::PAUSED,
				self::SENT,
				self::STOPPED,
				self::HALTED,
			));
	}

	/**
	 * Is posting was built.
	 *
	 * @return bool
	 */
	public function wasPostingBuilt()
	{
		return in_array(
			$this->getCode(),
			array(
				self::PLANNED,
				self::SENDING,
				self::PAUSED,
				self::SENT,
				self::STOPPED,
			));
	}

	/**
	 * Is sending limit exceeded.
	 *
	 * @return bool
	 */
	public function isSendingLimitExceeded()
	{
		if (!$this->isSending())
		{
			return false;
		}

		$message = $this->letter->getMessage();
		return $message->getTransport()->isLimitsExceeded($message);
	}

	/**
	 * Is sending limit exceeded.
	 *
	 * @return bool
	 * @deprecated
	 */
	public function isSendingPlanned()
	{
		if (!$this->isSending())
		{
			return false;
		}

		$plannedDateSend = $this->getPlannedDateSend();
		if (!$plannedDateSend)
		{
			return false;
		}
		$dateNow = new DateTime();

		return $plannedDateSend->getTimestamp() > $dateNow->getTimestamp();
	}

	/**
	 * Get sent date.
	 *
	 * @return string|DateTime|null
	 */
	public function getDateSend()
	{
		return $this->letter->get('DATE_SEND');
	}

	/**
	 * Get send date.
	 *
	 * @return string|DateTime|null
	 */
	public function getDatePause()
	{
		return $this->letter->get('DATE_PAUSE');
	}

	/**
	 * Get send date.
	 *
	 * @return string|DateTime|null
	 */
	public function getDateSent()
	{
		return $this->letter->get('DATE_SENT');
	}

	/**
	 * Get create date.
	 *
	 * @return string|DateTime|null
	 */
	public function getDateCreate()
	{
		return $this->letter->get('DATE_INSERT');
	}

	/**
	 * Get planned date send.
	 *
	 * @return string|DateTime|null
	 */
	public function getPlannedDateSend()
	{
		return $this->letter->get('AUTO_SEND_TIME');
	}

	/**
	 * Get last exec date.
	 *
	 * @return string|DateTime|null
	 */
	public function getLastExecutedDate()
	{
		return $this->letter->get('LAST_EXECUTED');
	}

	/**
	 * Update planed date send.
	 *
	 * @param Date $date Date.
	 * @return bool
	 */
	public function updatePlannedDateSend(Date $date)
	{
		\CTimeZone::disable();
		$result = Model\LetterTable::update($this->letter->getId(), array('AUTO_SEND_TIME' => $date));
		\CTimeZone::enable();
		if ($result->isSuccess())
		{
			$this->letter->set('AUTO_SEND_TIME', $date);
		}

		return $result->isSuccess();
	}

	/**
	 * Update send date.
	 *
	 * @return bool
	 */
	protected function updateDateSend()
	{
		return $this->updateDate('DATE_SEND');
	}

	/**
	 * Update pause date.
	 *
	 * @return bool
	 */
	protected function updateDatePause()
	{
		return $this->updateDate('DATE_PAUSE');
	}

	/**
	 * Update sent date.
	 *
	 * @return bool
	 */
	protected function updateDateSent()
	{
		return $this->updateDate('DATE_SENT');
	}

	/**
	 * Update date.
	 *
	 * @param string $name Name.
	 * @param DateTime|null $date Date.
	 * @return bool
	 */
	protected function updateDate($name, $date = null)
	{
		if (!$this->letter->get('POSTING_ID'))
		{
			return false;
		}
		\CTimeZone::disable();
		$result = Model\PostingTable::update(
			$this->letter->get('POSTING_ID'),
			array(
				$name => ($date ?: new DateTime())
			)
		);
		\CTimeZone::enable();

		return $result->isSuccess();
	}

	/**
	 * Get current state code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		$map = self::getStatusMap();
		$status = $this->letter->get('STATUS');

		if (($status && isset($map[$status])))
		{
			return $map[$status];
		}

		return self::NEWISH;
	}

	/**
	 * Get current state name.
	 *
	 * @return string
	 */
	public function getName()
	{
		/*
		if ($this->isSendingPlanned())
		{
			return Loc::getMessage('SENDER_DISPATCH_STATE_T');
		}
		*/

		return self::getStateName($this->getCode());
	}

	protected static function getStateName($code)
	{
		$code = $code === self::NEWISH ? self::READY : $code;
		return Loc::getMessage('SENDER_DISPATCH_STATE1_' . $code) ?: Loc::getMessage('SENDER_DISPATCH_STATE_' . $code);
	}

	/**
	 * Get states.
	 *
	 * @return array
	 */
	public static function getList()
	{
		$class = new \ReflectionClass(__CLASS__);
		$constants = $class->getConstants();

		$list = array();
		foreach ($constants as $id => $value)
		{
			if (in_array($value, array(self::INIT)))
			{
				continue;
			}

			$list[$value] = self::getStateName($value);
		}

		return $list;
	}

	/**
	 * Plan sending.
	 *
	 * @param Date $sendDate Send date.
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function plan(Date $sendDate)
	{
		return $this->changeState(self::PLANNED, $sendDate);
	}

	/**
	 * Change state to ready.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function ready()
	{
		return $this->changeState(self::READY);
	}

	/**
	 * Send.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function send()
	{
		return $this->changeState(self::SENDING);
	}

	/**
	 * Send errors.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function sendErrors()
	{
		if (!$this->canSendErrors())
		{
			throw new InvalidOperationException('Can not resend error letters.');
		}

		$postingId = $this->letter->get('POSTING_ID');
		$updateSql = 'UPDATE ' . PostingRecipientTable::getTableName() .
			" SET STATUS='" . PostingRecipientTable::SEND_RESULT_NONE . "'" .
			" WHERE POSTING_ID=" . intval($postingId) .
			" AND STATUS='" . PostingRecipientTable::SEND_RESULT_ERROR . "'";
		Application::getConnection()->query($updateSql);
		Posting\Sender::updateActualStatus($this->letter->get('POSTING_ID'));

		return $this->updateStatus(Model\LetterTable::STATUS_SEND, self::SENDING);
	}

	/**
	 * Pause.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function pause()
	{
		if ($this->changeState(self::PAUSED))
		{
			$this->updateDatePause(); //TODO: move to tablet!
			return true;
		}

		return false;
	}

	/**
	 * Halt.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function halt()
	{
		if ($this->changeState(self::HALTED))
		{
			$this->updateDatePause(); //TODO: move to tablet!
			return true;
		}

		return false;
	}

	/**
	 * Resume.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function resume()
	{
		if ($this->changeState(self::SENDING))
		{
			$this->updateDateSend(); //TODO: move to tablet!
			return true;
		}

		return false;
	}

	/**
	 * Stop.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function stop()
	{
		if ($this->changeState(self::STOPPED))
		{
			$this->updateDateSent(); //TODO: move to tablet!
			return true;
		}

		return false;
	}

	/**
	 * Init.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function init()
	{
		return $this->changeState(self::INIT);
	}

	/**
	 * Reset.
	 *
	 * @return bool
	 * @throws InvalidOperationException
	 * @throws NotImplementedException
	 */
	public function reset()
	{
		throw new NotImplementedException('init reset not implemented.');
	}

	/**
	 * Wait.
	 *
	 * @param Dispatch\MethodSchedule|null $method Method.
	 * @return bool
	 * @throws InvalidOperationException
	 */
	public function wait(Dispatch\MethodSchedule $method = null)
	{
		return $this->changeState(self::WAITING, $method ? $method->getNextDate() : null);
	}

	/**
	 * Check ready possibility.
	 *
	 * @return bool
	 */
	public function canReady()
	{
		return $this->canChangeState(self::READY);
	}

	/**
	 * Check send possibility.
	 *
	 * @return bool
	 */
	public function canSend()
	{
		if ($this->getCode() === self::PAUSED)
		{
			return false;
		}

		return $this->canChangeState(self::SENDING);
	}

	/**
	 * Check send errors possibility.
	 *
	 * @return bool
	 */
	public function canSendErrors()
	{
		if (Integration\Bitrix24\Service::isCloud())
		{
			return false;
		}

		if ($this->letter->isTrigger() || $this->letter->isReiterate())
		{
			return false;
		}

		if (!$this->letter->isSupportHeatMap() || !$this->letter->get('POSTING_ID'))
		{
			return false;
		}

		if (!$this->isSent())
		{
			return false;
		}

		$postingData = $this->letter->getLastPostingData();
		return !empty($postingData['COUNT_SEND_ERROR']);
	}

	/**
	 * Check plan possibility.
	 *
	 * @return bool
	 */
	public function canPlan()
	{
		return $this->canChangeState(self::SENDING);
	}

	/**
	 * Check pause possibility.
	 *
	 * @return bool
	 */
	public function canPause()
	{
		return $this->canChangeState(self::PAUSED);
	}

	/**
	 * Check stop possibility.
	 *
	 * @return bool
	 */
	public function canStop()
	{
		return $this->canChangeState(self::STOPPED);
	}

	/**
	 * Check resume possibility.
	 *
	 * @return bool
	 */
	public function canResume()
	{
		if ($this->getCode() !== self::PAUSED)
		{
			return false;
		}

		return $this->canChangeState(self::SENDING);
	}

	/**
	 * Check reset possibility.
	 *
	 * @return bool
	 */
	public function canReset()
	{
		return $this->canChangeState(self::NEWISH);
	}

	/**
	 * Check init possibility.
	 *
	 * @return bool
	 */
	public function canInit()
	{
		return $this->canChangeState(self::INIT);
	}

	/**
	 * Check wait possibility.
	 *
	 * @return bool
	 */
	public function canWait()
	{
		return $this->canChangeState(self::WAITING);
	}

	/**
	 * Check halt possibility.
	 *
	 * @return bool
	 */
	public function canHalt()
	{
		return $this->canChangeState(self::HALTED);
	}

	/**
	 * Get possible states.
	 *
	 * @return array
	 */
	protected function getPossibleStates()
	{
		switch ($this->getCode())
		{
			case self::NEWISH:
				return array(
					self::INIT,
					self::SENDING,
					self::PLANNED,
					self::WAITING,
				);
			case self::INIT:
				return array(
					self::READY,
				);
			case self::READY:
				return array(
					self::SENDING,
					self::PLANNED,
					self::WAITING,
				);
			case self::PLANNED:
				return array(
					self::READY,
					self::PLANNED,
					self::SENDING,
					self::SENT,
					self::STOPPED,
				);
			case self::SENDING:
				return array(
					self::PAUSED,
					self::SENT,
					self::STOPPED,
					self::WAITING,
				);
			case self::PAUSED:
				return array(
					self::SENDING,
					self::SENT,
					self::STOPPED,
					self::WAITING,
				);
			case self::WAITING:
				return array(
					self::SENT,
					self::WAITING,
					self::HALTED,
					self::STOPPED,
					self::READY,
				);
			case self::HALTED:
				return [
					self::WAITING,
					self::STOPPED,
				];
			case self::STOPPED:
			case self::SENT:
			default:
				return [];
		}
	}

	/**
	 * Return true if can change state.
	 *
	 * @param string $state State.
	 * @return bool
	 */
	private function canChangeState($state)
	{
		if (!$this->letter->getId())
		{
			return false;
		}

		return $this->isPossibleState($state);
	}

	/**
	 * Change state.
	 *
	 * @param string $state State.
	 * @param Date|null $sendDate Send date.
	 * @return bool
	 * @throws InvalidOperationException
	 */
	private function changeState($state, Date $sendDate = null)
	{
		if (!$this->canChangeState($state))
		{
			$messageText = Loc::getMessage('SENDER_DISPATCH_STATE_ERROR_CHANGE', array(
				'%old%' => $this->getName(),
				'%new%' => self::getStateName($state)
			));

			throw new InvalidOperationException($messageText);
		}

		$map = self::getStateMap();
		if ($map[$state])
		{
			return $this->updateStatus($map[$state], $state, $sendDate);
		}

		return false;
	}

	/**
	 * Change status.
	 *
	 * @param string $state State.
	 * @return bool
	 */
	private function isPossibleState($state)
	{
		$possibleStates = $this->getPossibleStates();

		//TODO: remove
		if (!$this->letter->isSupportReiterate() && !$this->letter->isTrigger())
		{
			$possibleStates = array_filter($possibleStates, function ($value)
			{
				return  !in_array($value, [self::WAITING, self::HALTED]);
			});
		}

		return in_array($state, $possibleStates);
	}

	/**
	 * Update status.
	 *
	 * @param string $status Status.
	 * @param string $state State.
	 * @param Date|null $sendDate Send date.
	 * @return bool
	 */
	private function updateStatus($status, $state, Date $sendDate = null)
	{
		$fields = array('STATUS' => $status);
		if ($state === self::READY && $this->letter->get('AUTO_SEND_TIME'))
		{
			$fields['AUTO_SEND_TIME'] = null;
		}
		if ($state === self::SENDING)
		{
			$fields['AUTO_SEND_TIME'] = $sendDate ?: new DateTime();
		}
		if ($state === self::PLANNED)
		{
			$fields['AUTO_SEND_TIME'] = $sendDate ?: new DateTime();
		}
		if ($state === self::WAITING && $sendDate)
		{
			$fields['AUTO_SEND_TIME'] = $sendDate;
		}

		\CTimeZone::disable();
		$result = Model\LetterTable::update($this->letter->getId(), $fields);
		\CTimeZone::enable();

		if ($result->isSuccess())
		{
			$this->letter->set('STATUS', $status);
			if (isset($fields['AUTO_SEND_TIME']))
			{
				$this->letter->set('AUTO_SEND_TIME', $fields['AUTO_SEND_TIME']);
			}
		}
		else
		{
			$this->letter->getErrorCollection()->add($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * Change status.
	 *
	 * @return array
	 */
	private static function getStateMap()
	{
		$map = array_flip(self::getStatusMap());
		$map[self::INIT] = Model\LetterTable::STATUS_NEW; // for init-operation

		return $map;
	}

	/**
	 * Change status.
	 *
	 * @return array
	 */
	private static function getStatusMap()
	{
		return array(
			Model\LetterTable::STATUS_NEW => self::NEWISH,
			Model\LetterTable::STATUS_PLAN => self::PLANNED,
			Model\LetterTable::STATUS_READY => self::READY,
			Model\LetterTable::STATUS_SEND => self::SENDING,
			Model\LetterTable::STATUS_WAIT => self::WAITING,
			Model\LetterTable::STATUS_HALT => self::HALTED,
			Model\LetterTable::STATUS_PAUSE => self::PAUSED,
			Model\LetterTable::STATUS_END => self::SENT,
			Model\LetterTable::STATUS_CANCEL => self::STOPPED,
		);
	}
}