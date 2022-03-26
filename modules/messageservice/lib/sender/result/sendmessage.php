<?php
namespace Bitrix\MessageService\Sender\Result;

use Bitrix\Main\Result;
use Bitrix\MessageService\DTO;
use Bitrix\MessageService\MessageStatus;

class SendMessage extends Result
{
	protected $id;
	protected $externalId;
	protected $status;
	protected $serviceRequest;
	protected $serviceResponse;

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
	 * @return mixed
	 */
	public function getExternalId()
	{
		return $this->externalId;
	}

	/**
	 * @param mixed $externalId
	 * @return SendMessage
	 */
	public function setExternalId($externalId)
	{
		$this->externalId = $externalId;
		return $this;
	}

	/**
	 * @return int Status id relative to MessageStatus constants.
	 * @see \Bitrix\MessageService\MessageStatus
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param int $status Status id relative to MessageStatus constants.
	 * @see \Bitrix\MessageService\MessageStatus
	 * @return SendMessage
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Helps us to set most used message status
	 * @see MessageStatus::ACCEPTED
	 * @return $this
	 */
	public function setAccepted()
	{
		$this->setStatus(MessageStatus::ACCEPTED);
		return $this;
	}

	/**
	 * @return ?DTO\Request
	 */
	public function getServiceRequest(): ?DTO\Request
	{
		return $this->serviceRequest;
	}

	/**
	 * @param DTO\Request $serviceRequest
	 */
	public function setServiceRequest(DTO\Request $serviceRequest): SendMessage
	{
		$this->serviceRequest = $serviceRequest;
		return $this;
	}

	/**
	 * @return ?DTO\Response
	 */
	public function getServiceResponse(): ?DTO\Response
	{
		return $this->serviceResponse;
	}

	/**
	 * @param DTO\Response $serviceResponse
	 */
	public function setServiceResponse(DTO\Response $serviceResponse): SendMessage
	{
		$this->serviceResponse = $serviceResponse;
		return $this;
	}
}