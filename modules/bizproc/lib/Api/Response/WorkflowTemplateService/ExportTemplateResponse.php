<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowTemplateService;

use Bitrix\Bizproc\Result;

final class ExportTemplateResponse extends Result
{
	public function getTemplateData(): ?string
	{
		return $this->data['templateData'] ?? null;
	}

	public function setTemplateData(string $templateData): self
	{
		$this->data['templateData'] = $templateData;

		return $this;
	}
}
