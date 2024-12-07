<?php

namespace Bitrix\Main\Cli\Helper;

final class NamespaceGenerator
{
	public function generateNamespaceForModule(string $moduleId, string $postfix = null): string
	{
		$partnerModuleSeparator = '.';
		$parts = explode($partnerModuleSeparator, $moduleId);

		$isBitrixModule = count($parts) === 1;
		if ($isBitrixModule)
		{
			array_unshift($parts, 'bitrix');
		}

		$parts = array_map(
			static fn ($part) => ucfirst($part),
			$parts
		);

		$namespace = join('\\', $parts);
		if (!empty($postfix))
		{
			$namespace .= '\\' . trim($postfix, '\\');
		}

		return $namespace;
	}
}
