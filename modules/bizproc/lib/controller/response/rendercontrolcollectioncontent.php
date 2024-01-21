<?php

namespace Bitrix\Bizproc\Controller\Response;

use Bitrix\Main\Engine\Response\ContentArea\ContentAreaInterface;

class RenderControlCollectionContent implements ContentAreaInterface
{
	private \CBPDocumentService $documentService;
	private array $rendered = [];

	public function __construct()
	{
		$this->documentService = \CBPRuntime::getRuntime()->getDocumentService();
	}

	public function addProperty(array $documentType, array $property, array $params): static
	{
		$this->rendered[] = $this->documentService->getFieldInputControl(
			$documentType,
			$property,
			$params['Field'],
			$params['Value'],
			(bool)$params['Als'],
			$params['RenderMode'] === 'public'
		);

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getRenderedProperties(): array
	{
		return $this->rendered;
	}

	public function getHtml(): string
	{
		// Rendered controls should be stored separately for now
		// e.g. in addition params of Bitrix\Main\Engine\Response\HtmlContent
		return '';
	}
}