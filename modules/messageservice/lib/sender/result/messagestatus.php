<?php
namespace Bitrix\MessageService\Sender\Result;

use Bitrix\Main\Result;

class MessageStatus extends Result
{
	protected $id;
	protected $externalId;

	protected $statusCode;
	protected $statusText;

	/**
	 * @param string $id
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = (string)$id;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $externalId
	 * @return $this
	 */
	public function setExternalId($externalId)
	{
		$this->externalId = (string)$externalId;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getExternalId()
	{
		return $this->externalId;
	}

	/**
	 * @param int|string $statusCode
	 * @return $this
	 */
	public function setStatusCode($statusCode)
	{
		$this->statusCode = $statusCode;
		return $this;
	}

	/**
	 * @return int|string
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * @param string $statusText
	 * @return $this
	 */
	public function setStatusText($statusText)
	{
		$this->statusText = (string)$statusText;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStatusText()
	{
		return $this->statusText;
	}
}