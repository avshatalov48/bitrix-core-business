<?php

namespace Bitrix\Location\Infrastructure;

use Bitrix\Location\Entity\Source\Factory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

/**
 * Class SourceCodePicker
 * @package Bitrix\Location\Infrastructure
 * @internal
 */
final class SourceCodePicker
{
	private const OPTION_NAME = 'location_default_source_code';

	/**
	 * @return string
	 */
	public static function getSourceCode(): string
	{
		return Option::get(
			'location',
			self::OPTION_NAME,
			(
				ModuleManager::isModuleInstalled('bitrix24')
				&& Loader::includeModule('bitrix24')
			)
				? Factory::OSM_SOURCE_CODE
				: Factory::GOOGLE_SOURCE_CODE

		);
	}

	/**
	 * @param string $sourceCode
	 */
	public static function setSourceCode(string $sourceCode): void
	{
		Option::set('location', self::OPTION_NAME, $sourceCode);
	}
}
