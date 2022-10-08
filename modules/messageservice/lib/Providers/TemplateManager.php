<?php

namespace Bitrix\MessageService\Providers;

interface TemplateManager
{
	public function getTemplatesList(array $context = null): array;
	public function prepareTemplate($templateData): array;
	public function isTemplatesBased(): bool;
	public function getConfigComponentTemplatePageName(): string;
}