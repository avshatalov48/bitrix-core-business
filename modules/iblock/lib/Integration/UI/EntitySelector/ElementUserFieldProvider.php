<?php

namespace Bitrix\Iblock\Integration\UI\EntitySelector;

use Bitrix\Iblock\UserField\Types\ElementType;

class ElementUserFieldProvider extends BaseUserFieldProvider
{
	protected function getEntityId(): string
	{
		return 'iblock-element-user-field';
	}

	protected function getEnumTypeClass(): string
	{
		return ElementType::class;
	}
}
