<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowTemplateService;

use Bitrix\Bizproc\Result;

final class ImportTemplateResponse extends Result
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
}
