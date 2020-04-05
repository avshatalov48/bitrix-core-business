<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Model\CallUserTable;
use Bitrix\Main\Type\DateTime;

class CallUser
{
	const LAST_SEEN_THRESHOLD = 30;
	const STATE_IDLE = 'idle';
	const STATE_CALLING = 'calling';
	const STATE_DECLINED = 'declined';
	const STATE_READY = 'ready';

	protected $userId;
	protected $callId;
	protected $state;
	protected $lastSeen;

	public static function create(array $fields)
	{
		$instance = new static();
		$instance->setFields($fields);
		return $instance;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		if($this->lastSeen instanceof DateTime)
		{
			$now = time();
			$delta = $now - $this->lastSeen->getTimestamp();
			$seenRecently = $delta <= static::LAST_SEEN_THRESHOLD;
		}

		return $seenRecently ? $this->state : static::STATE_IDLE;
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

	public function setFields(array $fields)
	{
		$this->userId = array_key_exists('USER_ID', $fields) ? $fields['USER_ID'] : $this->userId;
		$this->callId = array_key_exists('CALL_ID', $fields) ? $fields['CALL_ID'] : $this->callId;
		$this->state = array_key_exists('STATE', $fields) ? $fields['STATE'] : $this->state;
		$this->lastSeen = array_key_exists('LAST_SEEN', $fields) ? $fields['LAST_SEEN'] : $this->lastSeen;
	}

	public function save()
	{
		CallUserTable::merge([
			'USER_ID' => $this->userId,
			'CALL_ID' => $this->callId,
			'STATE' => $this->state,
			'LAST_SEEN' => $this->lastSeen
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
	}

	public static function delete($callId, $userId)
	{
		CallUserTable::delete([
			'CALL_ID' => $callId,
			'USER_ID' => $userId
		]);
	}
}