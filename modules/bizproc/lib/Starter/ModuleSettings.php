<?php

namespace Bitrix\Bizproc\Starter;

abstract class ModuleSettings
{
	protected readonly array $complexType;

	public function __construct(array $complexDocumentType)
	{
		$this->complexType = $complexDocumentType;
	}

	abstract public function isAutomationFeatureEnabled(): bool;

	abstract public function isScriptFeatureEnabled(): bool;

	abstract public function isAutomationLimited(): bool;

	abstract public function isAutomationOverLimited(): bool;

	public function getDocumentStatusFieldName(): ?string
	{
		return null;
	}

	/**
	 * @return array<Document>
	 */
	public function getTriggerRelatedDocuments(string $triggerCode, ?Document $document = null): array
	{
		return $document ? [$document] : [];
	}
}
