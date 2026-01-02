<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowTemplateService;

use Bitrix\Bizproc\Result;

final class SaveTemplateResponse extends Result
{
	public function getTemplateId(): int
	{
		return $this->data['templateId'] ?? 0;
	}

	public function setTemplateId(int $templateId): self
	{
		$this->data['templateId'] = $templateId;

		return $this;
	}

	public function getActivityErrors(): array
	{
		return $this->data['activityErrors'] ?? [];
	}

	public function setActivityErrors(array $errors): self
	{
		$this->data['activityErrors'] = $errors;

		return $this;
	}
}
