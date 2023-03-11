<?php

namespace Bitrix\Bizproc\Controller\Response;

use Bitrix\Main;

class RenderControlContent implements Main\Engine\Response\ContentArea\ContentAreaInterface
{
	protected array $documentType;
	protected array $property;
	protected array $params;

	public function __construct(array $documentType, array $property, array $params)
	{
		$this->documentType = $documentType;
		$this->property = $property;
		$this->params = $params;
	}

	public function getHtml()
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

		return $documentService->getFieldInputControl(
			$this->documentType,
			$this->property,
			$this->params['Field'],
			$this->params['Value'],
			(bool)$this->params['Als'],
			$this->params['RenderMode'] === 'public'
		);
	}
}
