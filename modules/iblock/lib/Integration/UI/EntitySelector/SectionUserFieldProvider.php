<?php

namespace Bitrix\Iblock\Integration\UI\EntitySelector;

use Bitrix\Iblock\UserField\Types\SectionType;

class SectionUserFieldProvider extends BaseUserFieldProvider
{
	protected function getEntityId(): string
	{
		return 'iblock-section-user-field';
	}

	protected function getEnumTypeClass(): string
	{
		return SectionType::class;
	}
}
