<?php

namespace Bitrix\Lists\Api\Response\WorkflowService;

use Bitrix\Lists\Api\Response\Response;

class GetParameterValuesResponse extends Response
{
	public function setParameters(array $parameters): static
	{
		$this->data['parameters'] = $parameters;

		return $this;
	}

	public function getParameters(): array
	{
		return $this->data['parameters'] ?? [];
	}

	public function hasTemplatesOnStartup(): bool
	{
		$parameters = $this->getParameters();

		return !empty($parameters);
	}
}
