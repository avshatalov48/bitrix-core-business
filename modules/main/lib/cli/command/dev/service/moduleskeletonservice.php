<?php

declare(strict_types=1);

namespace Bitrix\Main\Cli\Command\Dev\Service;

use Bitrix\Main\Application;
use Bitrix\Main\Cli\Command\Dev\Service\Module\ModuleSkeletonDto;
use Bitrix\Main\Cli\Command\Dev\Service\Module\ModuleStructure;
use Bitrix\Main\IO\Directory;
use Symfony\Component\Console\Exception\InvalidArgumentException;

final class ModuleSkeletonService
{
	public function generateSkeleton(ModuleSkeletonDto $dto): void
	{
		$baseDirectoryPath = $this->getBaseDirectory($dto->module);

		$directoryPath = $baseDirectoryPath . $this->getSubDirectory($dto->directory);

		$directory = new Directory($directoryPath);

		$directory->create();

		$structure = new ModuleStructure($directoryPath);

		foreach ($structure->getStructure() as $path)
		{
			$subDirectory = new Directory($path);

			$subDirectory->create();
		}
	}

	private function getBaseDirectory(string $module): string
	{
		$bitrixPath = Application::getDocumentRoot() . Application::getPersonalRoot() . '/modules/' . $module . '/lib/';
		if (is_dir($bitrixPath))
		{
			return $bitrixPath;
		}

		$localPath = Application::getDocumentRoot() . '/local/modules/' . $module . '/lib/';
		if (is_dir($localPath))
		{
			return $localPath;
		}

		throw new InvalidArgumentException('No such module');
	}

	private function getSubDirectory(string $path): string
	{
		if ($path !== '')
		{
			if ($path[0] !== '/')
			{
				$path = '/' . $path;
			}
			if (!str_ends_with($path, '/'))
			{
				$path .= '/';
			}
		}

		$path = implode('/', array_map('ucfirst', explode('/', trim($path, '/'))));

		return $path . '/';
	}
}