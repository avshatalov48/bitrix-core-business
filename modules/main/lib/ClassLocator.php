<?php

namespace Bitrix\Main;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;

final class ClassLocator
{
	private static array $loadedClasses = [];

	public static function getClassesByNamespace(string $namespace): array
	{
		$result = [];
		$directories = self::getDirectoriesByNamespace($namespace);
		foreach ($directories as $directory)
		{
			$classes = self::getClassesByPath($directory);
			if (!empty($classes))
			{
				$result = array_merge($result, $classes);
			}
		}

		return $result;
	}

	private static function getDirectoriesByNamespace(string $namespaceDirectory): array
	{
		$originalNamespaceDirectory = trim($namespaceDirectory, '\\');
		$namespaceDirectory = self::normalizeNamespace($namespaceDirectory);

		$namespaces = Loader::getNamespaces();

		$namespaceDirectories = $namespaces[$namespaceDirectory] ?? [];

		$directories = [];

		if (empty($namespaceDirectories))
		{
			$namespaceParts = explode('\\', trim($namespaceDirectory, '\\'));
			$originalNamespaceParts = explode('\\', $originalNamespaceDirectory);
			for ($i = count($namespaceParts); $i >= 0; $i--)
			{
				$partNamespace = join('\\', array_slice($namespaceParts, 0, $i)) . '\\';
				$partNamespaceDirectories = $namespaces[$partNamespace] ?? null;
				if (empty($partNamespaceDirectories))
				{
					continue;
				}

				foreach ($partNamespaceDirectories as $partNamespaceDirectoriesItem)
				{
					$originalStylePath =
						$partNamespaceDirectoriesItem['path']
						. DIRECTORY_SEPARATOR
						. join(DIRECTORY_SEPARATOR, array_slice($originalNamespaceParts, $i))
					;
					if (is_dir($originalStylePath))
					{
						$directories[] = $originalStylePath;
						continue;
					}

					$lowerStylePath =
						$partNamespaceDirectoriesItem['path']
						. DIRECTORY_SEPARATOR
						. join(DIRECTORY_SEPARATOR, array_slice($namespaceParts, $i))
					;
					if (is_dir($lowerStylePath))
					{
						$directories[] = $lowerStylePath;
						continue;
					}
				}
			}

		}
		else
		{
			foreach ($namespaceDirectories as $namespaceItem)
			{
				$directories[] = $namespaceItem['path'];
			}
		}

		$directories = array_unique($directories);

		return array_unique($directories);
	}

	private static function getClassesByPath($path): array
	{
		if (isset(self::$loadedClasses[$path]))
		{
			return self::$loadedClasses[$path];
		}

		$pathFiles = self::getFilesByPath($path);
		foreach ($pathFiles as $pathFile)
		{
			$classesNames = self::getClassesNamesFromFilePath($pathFile);
			foreach ($classesNames as $className)
			{
				if (class_exists($className))
				{
					self::$loadedClasses[$path][] = $className;
				}
			}
		}

		return self::$loadedClasses[$path] ?? [];
	}

	private static function getFilesByPath(string $path): array
	{
		$files = [];
		$directory = new Directory($path);
		if ($directory->isExists())
		{
			foreach ($directory->getChildren() as $child)
			{
				if ($child instanceof File)
				{
					$files[] = $child->getPath();
				}
				elseif ($child instanceof Directory)
				{
					foreach (self::getFilesByPath($child->getPath()) as $file)
					{
						$files[] = $file;
					}
				}
			}
		}
		else
		{
			throw new ArgumentException('Invalid directory path');
		}

		return $files;
	}

	private static function getClassesNamesFromFilePath(string $filePath): array
	{
		$classes = [];
		$file = new File($filePath);
		if ($file->isExists() && $file->getExtension() === 'php')
		{
			$namespace = '';
			$tokens = token_get_all($file->getContents());
			$tokenCount = count($tokens);

			for ($i = 0; $i < $tokenCount; $i++)
			{
				if (!is_array($tokens[$i]))
				{
					continue;
				}

				$tokenType = $tokens[$i][0];

				if ($tokenType === T_NAMESPACE)
				{
					$namespace = '';
					for ($j = $i + 1; $j < $tokenCount; $j++)
					{
						if ($tokens[$j] === ';')
						{
							break;
						}
						if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_NAME_QUALIFIED, T_STRING, T_NS_SEPARATOR]))
						{
							$namespace .= $tokens[$j][1];
						}
					}
					continue;
				}

				if ($tokenType === T_CLASS)
				{
					$classNameToken = $i + 1;
					while (
						isset($tokens[$classNameToken]) &&
						is_array($tokens[$classNameToken]) &&
						$tokens[$classNameToken][0] === T_WHITESPACE
					)
					{
						$classNameToken++;
					}

					if (
						isset($tokens[$classNameToken]) &&
						is_array($tokens[$classNameToken]) &&
						$tokens[$classNameToken][0] === T_STRING
					)
					{
						$className = $tokens[$classNameToken][1];
						$classes[] = $namespace ? $namespace . '\\' . $className : $className;
					}
				}
			}
		}

		return $classes;
	}

	private static function normalizeNamespace($namespace): string
	{
		return trim(strtolower($namespace), '\\') . '\\';
	}
}