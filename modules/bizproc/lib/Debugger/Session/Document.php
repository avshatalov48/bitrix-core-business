<?php

namespace Bitrix\Bizproc\Debugger\Session;

class Document extends Entity\EO_DebuggerSessionDocument
{
	public function getParameterDocumentId(): array
	{
		$this->fillSession();
		$session = $this->getSession();

		return [
			$session->getModuleId(),
			$session->getEntity(),
			$this->getDocumentId(),
		];
	}

	public function getSignedDocument(): string
	{
		$this->fillSession();
		$session = $this->getSession();

		return \CBPDocument::signParameters([
			$session->getParameterDocumentType(),
			$this->getRealCategoryId(),
			$this->getDocumentId(),
		]);
	}

	public function getRealCategoryId()
	{
		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService('DocumentService');

		$this->fillSession();
		$session = $this->getSession();

		return $documentService->getFieldValue(
			$this->getParameterDocumentId(),
			'CATEGORY_ID',
			$session->getParameterDocumentType()
		);
	}

	public function toArray(): array
	{
		return [
			'Id' => $this->getId(),
			'SessionId' => $this->getSessionId(),
			'DocumentId' => $this->getDocumentId(),
			'DocumentCategoryId' => $this->getRealCategoryId(),
			'DateExpire' => $this->getDateExpire() ? $this->getDateExpire()->getTimestamp() : null,
		];
	}
}