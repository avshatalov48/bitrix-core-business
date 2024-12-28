<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowService;

use Bitrix\Bizproc\Result;

final class PrepareParametersResponse extends Result
{
	public function setRawParameters(array $parameters): self
	{
		$this->data['rawParameters'] = $parameters;

		return $this;
	}

	public function getRawParameters(): array
	{
		$parameters = $this->data['rawParameters'] ?? [];

		return is_array($parameters) ? $parameters : [];
	}

	public function setParameters(array $parameters): self
	{
		$this->data['parameters'] = $parameters;

		return $this;
	}

	public function getParameters(): array
	{
		$parameters = $this->data['parameters'] ?? [];

		return is_array($parameters) ? $parameters : [];
	}
}
