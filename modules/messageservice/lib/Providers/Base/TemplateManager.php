<?php

namespace Bitrix\MessageService\Providers\Base;

class TemplateManager implements \Bitrix\MessageService\Providers\TemplateManager
{
	protected string $providerId;

	public function __construct(string $providerId)
	{
		$this->providerId = $providerId;
	}

	public function getTemplatesList(array $context = null): array
	{
		return [];
	}

	public function prepareTemplate($templateData): array
	{
		return $templateData;
	}

	public function isTemplatesBased(): bool
	{
		return false;
	}

	public function getConfigComponentTemplatePageName(): string
	{
		return $this->providerId;
	}
}