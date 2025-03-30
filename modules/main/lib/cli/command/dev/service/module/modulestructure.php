<?php

declare(strict_types=1);

namespace Bitrix\Main\Cli\Command\Dev\Service\Module;

final class ModuleStructure
{
	public const STRUCTURE = [
		'Access',
		'Command',
		'Controller',
		'Entity',
		'Internals' => [
			'Exception',
			'Integration',
			'Model',
			'Repository',
			'Service',
		],
		'Provider' => [
			'Params',
		],
		'Service',
	];

	public function __construct(private readonly string $path)
	{

	}

	public function getStructure(array $structure = self::STRUCTURE, string $path = ''): array
	{
		$result = [];
		foreach ($structure as $key => $value)
		{
			$currentPath = $this->path . $path;
			if (is_array($value))
			{
				$result = array_merge($result, $this->getStructure($value, $path . $key . '/'));
			}
			else
			{
				$result[] = $currentPath . $value;
			}
		}

		return $result;
	}
}
