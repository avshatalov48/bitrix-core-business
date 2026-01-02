<?php

namespace Bitrix\Bizproc\Api\Response\WorkflowTemplateService;

use Bitrix\Bizproc\Result;

final class SaveTemplateDraftResponse extends Result
{
	public function getTemplateDraftId(): int
	{
		return $this->data['templateDraftId'] ?? 0;
	}

	public function setTemplateDraftId(int $templateDraftId): self
	{
		$this->data['templateDraftId'] = $templateDraftId;

		return $this;
	}
}
