<?php

namespace Bitrix\UI\Integration\HumanResources;

use Bitrix\HumanResources\Config\Storage;
use Bitrix\HumanResources\Type\AccessCodeType;
use Bitrix\Main\Loader;
use COption;

final class HumanResources
{
	public const IS_CONVERTED_OPTION_NAME = 'entity_editor_config_access_codes_is_converted';

	public static function getInstance(): self
	{
		return new self();
	}

	private function __construct()
	{

	}

	public function buildAccessCode(string $value, int $nodeId): ?string
	{
		if ($this->isUsed())
		{
			return AccessCodeType::tryFrom($value)?->buildAccessCode($nodeId);
		}

		return null;
	}

	public function isUsed(): bool
	{
		return Loader::includeModule('humanresources') && Storage::instance()->isCompanyStructureConverted(false);
	}

	private function isEntityEditorConfigAccessCodesConverted(): bool
	{
		return COption::GetOptionString('ui', self::IS_CONVERTED_OPTION_NAME, 'Y') === 'Y';
	}

	public function isAccessCodesCanBeUsed(): bool
	{
		return $this->isUsed() && $this->isEntityEditorConfigAccessCodesConverted();
	}
}