<?php

namespace Bitrix\Socialnetwork\Access;

use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);
class VoteAccessResult
{
	private $result = false;
	private $errorType = 'ACCESS';
	private $message = 'RATING_ALLOW_VOTE_ACCESS';

	public function __construct()
	{

	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'RESULT' => $this->result,
			'ERROR_TYPE' => $this->errorType,
			'ERROR_MSG' => Loc::getMessage($this->message),
		];
	}

	/**
	 * @param bool $result
	 * @return $this
	 */
	public function setResult(bool $result): self
	{
		$this->result = $result;
		return $this;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setErrorType(string $type): self
	{
		$this->errorType = $type;
		return $this;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage(string $message): self
	{
		$this->message = $message;
		return $this;
	}
}