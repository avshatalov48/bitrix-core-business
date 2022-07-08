<?php

namespace Bitrix\MessageService\DTO;

class StatusUpdate
{
	public $internalId;
	public $externalId;
	public $providerStatus;
	public $deliveryStatus;
	public $deliveryError;

	public function __construct(array $fields = null)
	{
		if ($fields !== null)
		{
			$this->hydrate($fields);
		}
	}

	public function hydrate(array $fields)
	{
		$this->internalId = $fields['internalId'] ?? $this->internalId;
		$this->externalId = $fields['externalId'] ?? $this->externalId;
		$this->providerStatus = $fields['providerStatus'] ?? $this->providerStatus;
		$this->deliveryStatus = $fields['deliveryStatus'] ?? $this->deliveryStatus;
		$this->deliveryError = $fields['deliveryError'] ?? $this->deliveryError;
	}
}