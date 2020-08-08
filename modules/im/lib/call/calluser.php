<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Model\CallUserTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;

class CallUser
{
	const LAST_SEEN_THRESHOLD = 75;
	const STATE_UNAVAILABLE = 'unavailable';
	const STATE_IDLE = 'idle';
	const STATE_CALLING = 'calling';
	const STATE_DECLINED = 'declined';
	const STATE_BUSY = 'busy';
	const STATE_READY = 'ready';

	protected $userId;
	protected $callId;
	protected $state;
	protected $lastSeen;
	protected $isMobile;

	public static function create(array $fields)
	{
		if(!isset($fields['USER_ID']) || !$fields['USER_ID'])
		{
			throw new ArgumentException('USER_ID should be positive integer');
		}
		$instance = new static();
		$instance->setFields($fields);
		return $instance;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		switch ($this->state)
		{
			case static::STATE_READY:
				return $this->isSeenRecently() ? static::STATE_READY : static::STATE_IDLE;
			default:
				return $this->state;
		}
	}

	public function isSeenRecently()
	{
		if(!($this->lastSeen instanceof DateTime))
		{
			return false;
		}
		$now = time();
		$delta = $now - $this->lastSeen->getTimestamp();
		return $delta <= static::LAST_SEEN_THRESHOLD;
	}

	public function updateState($state)
	{
		$this->update(['STATE' => $state]);
	}

	/**
	 * @return DateTime
	 */
	public function getLastSeen()
	{
		return $this->lastSeen;
	}

	/**
	 * Update user's last seen date.
	 *
	 * @param DateTime $lastSeen
	 */
	public function updateLastSeen(DateTime $lastSeen)
	{
		$this->update(['LAST_SEEN' => $lastSeen]);
	}

	/**
	 * Returns true if the user is an active participant of the call and false otherwise.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		$seenRecently = false;

		if($this->lastSeen instanceof DateTime)
		{
			$now = time();
			$delta = $now - $this->lastSeen->getTimestamp();
			$seenRecently = $delta <= static::LAST_SEEN_THRESHOLD;
		}

		return in_array($this->state, [static::STATE_READY, static::STATE_CALLING]) && $seenRecently;
	}

	public function isUaMobile()
	{
		return $this->isMobile;
	}

	public function setFields(array $fields)
	{
		$this->userId = array_key_exists('USER_ID', $fields) ? $fields['USER_ID'] : $this->userId;
		$this->callId = array_key_exists('CALL_ID', $fields) ? $fields['CALL_ID'] : $this->callId;
		$this->state = array_key_exists('STATE', $fields) ? $fields['STATE'] : $this->state;
		$this->lastSeen = array_key_exists('LAST_SEEN', $fields) ? $fields['LAST_SEEN'] : $this->lastSeen;
		if(array_key_exists('IS_MOBILE', $fields))
		{
			$this->isMobile = $fields['IS_MOBILE'] === 'Y';
		}
	}

	public function save()
	{
		CallUserTable::merge([
			'USER_ID' => $this->userId,
			'CALL_ID' => $this->callId,
			'STATE' => $this->state,
			'LAST_SEEN' => $this->lastSeen,
			'IS_MOBILE' => is_bool($this->isMobile) ? $this->isMobile : null
		]);
	}

	public function update(array $fields)
	{
		$updateResult = CallUserTable::update(['CALL_ID' => $this->callId, 'USER_ID' => $this->userId], $fields);

		if($updateResult->isSuccess())
		{
			$updateData = $updateResult->getData();
			$this->setFields($updateData);
		}
		$this->setFields($fields);
	}

	public static function delete($callId, $userId)
	{
		CallUserTable::delete([
			'CALL_ID' => $callId,
			'USER_ID' => $userId
		]);
	}
}