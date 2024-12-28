<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowService;

use Bitrix\Bizproc\Result;

final class PrepareStartParametersResponse extends Result
{
	public function setParameters(array $parameters): self
	{
		$this->data['parameters'] = $parameters;

		return $this;
	}

	public function getParameters(): array
	{
		$parameters = $this->data['parameters'] ?? null;

		return is_array($parameters) ? $parameters : [];
	}
}
